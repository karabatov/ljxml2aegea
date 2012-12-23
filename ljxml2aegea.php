<?php
// Yuri Karabatov karabatov@gmail.com

//error_reporting(E_ALL);

// DB connection
include("config.inc.php");
echo "ljuser=$ljuser\n";
@$connect = mysql_connect($e2server,$e2user,$e2password) or die("DB is not avalible");
mysql_select_db ($e2database);
mysql_query ("set character_set_client='cp1251'");
mysql_query ("set character_set_results='cp1251'");
mysql_query ("set collation_connection='cp1251_general_ci'");

echo "connected to sql\n";

// LJ tag cleaner
function ljclean($ljtext)
{
    echo "* in ljclean function\n";
    $userPA = '/<lj user="(.*?)" ?\/?>/i';
    $commPA = '/<lj comm="(.*?)" ?\/?>/i';
    $namedCutPA = '/<lj-cut +text="(.*?)" ?\/?>/i';
    $cutPA = '/<lj-cut>/i';
    $ccutPA = '/<\/lj-cut>/i';

    $userRE = '<a href="http:\/\/www.livejournal.com/users\/\\1" class="lj-user">\\1<\/a>';
    $commRE = '<a href="http:\/\/community.livejournal.com\/\\1/" class="lj-comm">\\1<\/a>';

    $ljclean = preg_replace($userPA,$userRE,$ljtext);
    $ljclean = preg_replace($commPA,$commRE,$ljtext);
    $ljclean = preg_replace($namedCutPA,'\\1',$ljtext);
    $ljclean = preg_replace($cutPA,'',$ljtext);
    $ljclean = preg_replace($ccutPA,'',$ljtext);

    return $ljclean;
}

// XML files parser
function xmlparser($file_id,$total_post,$ljuser,$e2tabprefix,$offset,$addtags)
{
    echo "in xmlparser function\n";
    echo "file_id = $file_id\n";
    // Load source file
    $entry = simplexml_load_file($file_id);

    // Post data
    $p_date=$entry->event_timestamp;
    $p_subj=$entry->subject;
    $p_text=ljclean($entry->event);
    if (($entry->security=='usemask')||($entry->security=='private')){ // "usemask" or "private" => IsVisible = 0
        $p_private=0;
    }
    else {
        $p_private=1;
    }
    //$p_tag=$entry->taglist;
    $p_postid=$entry->ditemid;

    // If post has no title, take first 50 characters
    if ($p_subj==''){
        // Strip tags
        $p_subj_tmp = preg_split ("/</", $p_text);
        $p_subj=substr($p_subj_tmp[0],0,50);
        $p_subj.='...';
        }

    // Comments
    // Store all comments for now
    $total_comm=0;
    if (file_exists("./".$ljuser."/C-".$total_post)){
        $xml = simplexml_load_file("./".$ljuser."/C-".$total_post);
        $i=1;
        foreach ($xml->comment as $comment){
            echo "i = $i, total_comm = $total_comm\n";
            print_r($comment);
            $c_id[$i]=$comment->id;
            $c_author[$i]=$comment->user=='' ? 'anonymous' : $comment->user;
            $c_text[$i]=ljclean($comment->body);
            $c_parentid[$i]=$comment->parentid;
            $c_state[$i]=$comment->state=='S' ? 0 : 1;
            $c_date[$i]=strtotime($comment->date);
            $total_comm++;
            $i++;
        }
    }
    // Convert to cp1251
    $p_subj = iconv('UTF-8','Windows-1251',$p_subj);
    $p_text = iconv('UTF-8','Windows-1251',$p_text);

    // ""
    $p_text=mysql_escape_string($p_text);
    $p_subj=mysql_escape_string($p_subj);

    // Put post into DB
    // ID = $p_postid
    // Title = $p_subj
    // Text = $p_text
    // OriginalAlias = $p_postid
    // IsVisible = $p_private
    // Stamp = $p_date
    // LastModified = $p_date
    // IsDST = 0
    // Offset = $offset
    $query = "INSERT INTO ".$e2tabprefix."Notes (ID,Title,OriginalAlias,Text,IsPublished,IsVisible,Stamp,LastModified,IsDST,Offset,IP) VALUES ($p_postid,'$p_subj','$p_postid','$p_text',1,$p_private,'$p_date','$p_date',0,$offset,'127.0.0.1') ON DUPLICATE KEY UPDATE ID=$p_postid,Title='$p_subj',OriginalAlias='$p_postid',Text='$p_text',IsPublished=1,IsVisible=$p_private,Stamp='$p_date',LastModified='$p_date',IsDST=0,Offset=$offset,IP='127.0.0.1';";
    $result = mysql_query ($query);
    if (!$result) {
        echo "$query\n";
        die('Invalid query: ' . mysql_error() . '\n');
    }
    // Assign alias to post
    // ID = $p_postid
    // EntityID = $p_postid
    // Alias = $p_postid
    // Stamp = $p_date
    $query = "INSERT INTO ".$e2tabprefix."Aliases (ID,EntityID,Alias,Stamp) VALUES ($p_postid,$p_postid,'$p_postid','$p_date') ON DUPLICATE KEY UPDATE ID=$p_postid,EntityID=$p_postid,Alias='$p_postid',Stamp='$p_date';";
    $result = mysql_query ($query);
    if (!$result) {
        echo "$query\n";
        die('Invalid query: ' . mysql_error() . '\n');
    }

    // Put comments into DB
    // ID = 100000 + $c_id
    // NoteID = $p_postid
    // AuthorName = $c_author
    // AuthorEmail $c_author + livejournal.com
    // Text = $c_text
    // Reply
    // IsVisible = $c_state
    // IsReplyVisible
    // Stamp = $c_date
    // LastModified = $c_date
    // ReplyStamp
    // ReplyLastModified
    for ($i=1;$i<=$total_comm;$i++){
        if ($c_text[$i]==''){$c_text[$i]='(deleted comment)';};
        $c_text[$i] = iconv('UTF-8','Windows-1251',$c_text[$i]);
        $c_author[$i] = iconv('UTF-8','Windows-1251',$c_author[$i]);
        // ""
        $c_text[$i]=mysql_escape_string($c_text[$i]);
        echo "---------\n";
        echo "i = $i, total_comm = $total_comm\n";
        echo "c_id = $c_id[$i]\n";
        echo "c_parentid = $c_parentid[$i]\n";
        echo "p_postid = $p_postid\n";
        echo "c_author = $c_author[$i]\n";
        echo "c_text = $c_text[$i]\n";
        if (($c_author[$i]==$ljuser)&&($c_parentid[$i]!='')){
            $c_parentid[$i]=$c_parentid[$i]+100000;
            $query = "INSERT INTO ".$e2tabprefix."Comments (ID,IsReplyVisible,Reply,ReplyStamp,ReplyLastModified) VALUES ($c_parentid[$i],1,'$c_text[$i]','$c_date[$i]','$c_date[$i]') ON DUPLICATE KEY UPDATE IsReplyVisible=1,Reply='$c_text[$i]',ReplyStamp='$c_date[$i]',ReplyLastModified='$c_date[$i]';";
            $result = mysql_query ($query);
            if (!$result) {
                echo "$query\n";
                die('Invalid query: ' . mysql_error() . '\n');
            }
        } else {
            $c_id[$i]=$c_id[$i]+100000;
            $query = "INSERT INTO ".$e2tabprefix."Comments (ID,NoteID,AuthorName,AuthorEmail,Text,IsVisible,Stamp,LastModified) VALUES ($c_id[$i],$p_postid,'$c_author[$i]','".$c_author[$i]."@livejournal.com','$c_text[$i]','$c_state[$i]','$c_date[$i]','$c_date[$i]') ON DUPLICATE KEY UPDATE NoteID=$p_postid,AuthorName='$c_author[$i]',AuthorEmail='".$c_author[$i]."@livejournal.com',Text='$c_text[$i]',IsVisible='$c_state[$i]',Stamp='$c_date[$i]',LastModified='$c_date[$i]';"; 
            $result = mysql_query ($query);
            if (!$result) {
                echo "$query\n";
                die('Invalid query: ' . mysql_error() . '\n');
            }

        }
    }
return true;
}

$files = glob("./".$ljuser."/L-*");
foreach($files as $file_id){
    $total_post=preg_replace("/(.*)L-(.*)/","\\2",$file_id);
    xmlparser($file_id,$total_post,$ljuser,$e2tabprefix,$offset,$addtags);
}
echo "Posts processed: ".count($files)."\n";
?>
