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

// Convert tags to ascii for URLs
function to_ascii($str, $delimiter='-') {

    $toreplace  = array(
        "Ä","ä","Æ","æ","Ǽ","ǽ","Å","å","Ǻ","ǻ","À","Á","Â","Ã","à","á","â","ã","Ā","ā","Ă","ă","Ą","ą","Ǎ","ǎ","Ạ","Ạ","ạ","Ả","ả","Ấ","ấ","Ầ","ầ","Ẩ","ẩ","Ẫ","ẫ","Ậ","ậ","Ắ","ắ","Ằ","ằ","Ẳ","ẳ","Ẵ","ẵ","Ặ","ặ",
        "Ç","ç","Ć","ć","Ĉ","ĉ","Ċ","ċ","Č","č",
        "Ð","ð","Ď","ď","Đ","đ",
        "È","É","Ê","Ë","è","é","ê","ë","Ē","ē","Ĕ","ĕ","Ė","ė","Ę","ę","Ě","ě","Ẹ","ẹ","Ẻ","ẻ","Ẽ","Ế","ế","Ề","ề","Ể","ể","ễ","Ệ","ệ","Ə","ə",
        "ſ","ſ",
        "Ĝ","ĝ","Ğ","ğ","Ġ","ġ","Ģ","ģ",
        "Ĥ","ĥ","Ħ","ħ",
        "Ì","Í","Î","Ï","ì","í","î","ï","Ĩ","ĩ","Ī","ī","Ĭ","ĭ","Į","į","İ","ı","Ǐ","ǐ","Ỉ","ỉ","Ị","ị",
        "Ĳ","ĳ",
        "ﬁ","ﬂ",
        "Ĵ","ĵ",
        "Ķ","ķ","ĸ",
        "Ĺ","ĺ","Ļ","ļ","Ľ","ľ","Ŀ","ŀ","Ł","ł",
        "Ñ","ñ","Ń","ń","Ņ","Ň","ň","ŉ","Ŋ","ŋ",
        "Ö","ö","Ø","ø","Ǿ","ǿ","Ò","Ó","Ô","Õ","ò","ó","ô","õ","Ō","ō","Ŏ","ŏ","Ő","ő","Ǒ","ǒ","Ọ","ọ","Ỏ","ỏ","Ố","ố","Ồ","ồ","Ổ","ổ","Ỗ","ỗ","Ộ","ộ","Ớ","ớ","Ờ","ờ","Ở","ở","Ỡ","ỡ","Ợ","ợ","Ơ","ơ",
        "Œ","œ",
        "Ŕ","ŕ","Ŗ","ŗ","Ř","ř",
        "Ś","ś","Ŝ","Ş","ş","Š","š",
        "Ţ","ţ","Ť","ť","Ŧ","ŧ",
        "Ü","ü","Ù","Ú","Û","ù","ú","û","Ụ","ụ","Ủ","ủ","Ứ","ứ","Ừ","ừ","Ữ","ữ","Ự","ự","Ũ","ũ","Ū","ū","Ŭ","ŭ","Ů","ů","Ű","ű","Ų","ų","Ǔ","ǔ","ǖ","ǘ","Ǚ","ǚ","Ǜ","ǜ","Ư","ư",
        "Ŵ","ŵ","Ẁ","ẁ","Ẃ","ẃ","Ẅ","ẅ",
        "Ý","ý","ÿ","Ŷ","ŷ","Ÿ","Ỳ","ỳ","Ỵ","ỵ","Ỷ","ỷ","Ỹ","ỹ",
        "Þ","þ","ß",
        "Ź","ź","Ż","ż","Ž","ž",
        "А", "Б", "В", "Г", "Д", "Е", "Ё", "Ж", "З", "И", "Й", "К", "Л", "М", "Н", "О", "П", "Р", "С",
        "Т", "У", "Ф", "Х", "Ц", "Ч", "Ш", "Щ", "Ъ", "Ы", "Ь", "Э", "Ю", "Я",
        "а", "б", "в", "г", "д", "е", "ё", "ж", "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с",
        "т", "у", "ф", "х", "ц", "ч", "ш", "щ", "ъ", "ы", "ь", "э", "ю", "я",
        );
    $replacement = array(
        "ae","ae","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a",
        "c","c","c","c","c","c","c","c","c","c",
        "d","d","d","d","d","d",
        "e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e","e",
        "f","f",
        "g","g","g","g","g","g","g","g",
        "h","h","h","h",
        "i","i","i","i","i","i","i","i","i","i","i","i","i","i","i","i","i","i","i","i","i","i","i","i",
        "ij","ij",
        "fi","fl",
        "j","j",
        "k","k","k",
        "l","l","l","l","l","l","l","l","l","l",
        "n","n","n","n","n","n","n","n","n","n",
        "oe","oe","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o",
        "oe","oe",
        "r","r","r","r","r","r",
        "s","s","s","s","s","s","s",
        "t","t","t","t","t","t",
        "ue","ue","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u","u",
        "w","w","w","w","w","w","w","w",
        "y","y","y","y","y","y","y","y","y","y","y","y","y","y",
        "th","th","ss",
        "z","z","z","z","z","z",
        "a", "b", "v", "g", "d", "e", "e", "zh", "z", "i", "j", "k", "l", "m", "n", "o", "p", "r", "s",
        "t", "u", "f", "h", "ts", "ch", "sh", "sch", "", "y", "", "e", "yu", "ya",
        "a", "b", "v", "g", "d", "e", "e", "zh", "z", "i", "j", "k", "l", "m", "n", "o", "p", "r", "s",
        "t", "u", "f", "h", "ts", "ch", "sh", "sch", "", "y", "", "e", "yu", "ya",
    );

    $clean = str_replace($toreplace,$replacement,$str);
    $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
    $clean = strtolower(trim($clean, '-'));
    $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

    return $clean;
}


// LJ tag cleaner
function ljclean($ljtext)
{
    //echo "* in ljclean function\n";
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
function xmlparser($file_id,$total_post,$ljuser,$e2tabprefix,$offset,$addtags_id)
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
    $p_addtags=$entry->taglist;
    $p_postid=$entry->ditemid;
    $p_mood=$entry->props->current_mood;
    $p_music=$entry->props->current_music;
    $p_location=$entry->props->current_location;
    $p_coords=$entry->props->current_coords;
    echo "p_location\n";
    print_r($p_location);
    echo "p_music\n";
    print_r($p_music);
    echo "p_coords\n";
    print_r($p_coords);

    // If post has no title, take first 50 characters
    if ($p_subj==''){
        // Strip tags
        $p_subj_tmp = preg_split ("/</", $p_text);
        $p_subj=substr($p_subj_tmp[0],0,50);
        $p_subj.='...';
        }

    // Append mood, music & location info
    $p_text.=PHP_EOL.PHP_EOL;
    // Location
    if ($p_location){
        if ($p_coords){
            $p_loctext=$p_coords . ' (' . $p_location . ')';
            $p_text.='<strong>Current location:</strong> <a href="http://maps.google.com/maps?q='.urlencode($p_loctext).'">'.$p_location.'</a>'.PHP_EOL;
        } else {
            $p_text.='<strong>Current location:</strong> <a href="http://maps.google.com/maps?q='.urlencode($p_location).'">'.$p_location.'</a>'.PHP_EOL;
        }
    }
    // Mood
    if ($p_mood){
        $p_text.='<strong>Current mood:</strong> '.$p_mood.PHP_EOL;
    }
    // Music
    if ($p_music){
        $p_text.='<strong>Current music:</strong> '.$p_music;
    }

    // Comments
    // Store all comments for now
    $total_comm=0;
    if (file_exists("./".$ljuser."/C-".$total_post)){
        $xml = simplexml_load_file("./".$ljuser."/C-".$total_post);
        $i=1;
        foreach ($xml->comment as $comment){
            //echo "i = $i, total_comm = $total_comm\n";
            //print_r($comment);
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

    // Put tags into DB
    $p_addtags_id = create_tags($e2tabprefix,$p_addtags);
    assign_tags($e2tabprefix,$p_postid,$addtags_id);
    assign_tags($e2tabprefix,$p_postid,$p_addtags_id);

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
        $c_text[$i] = iconv('UTF-8','Windows-1251//IGNORE',$c_text[$i]);
        $c_author[$i] = iconv('UTF-8','Windows-1251//IGNORE',$c_author[$i]);
        // ""
        $c_text[$i]=mysql_escape_string($c_text[$i]);
        //echo "---------\n";
        //echo "i = $i, total_comm = $total_comm\n";
        //echo "c_id = $c_id[$i]\n";
        //echo "c_parentid = $c_parentid[$i]\n";
        //echo "p_postid = $p_postid\n";
        //echo "c_author = $c_author[$i]\n";
        //echo "c_text = $c_text[$i]\n";
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

function create_tags($e2tabprefix,$addtags){
    $tags = preg_split('~\s*,\s*~', $addtags);
    $id_tags = array();
    foreach ($tags as $tag){
        if ($tag != ''){
            $t_key = iconv('UTF-8','Windows-1251',$tag);
            $t_key = mysql_escape_string($t_key);
            $t_url = to_ascii($tag);
            $query = "SELECT ID,Keyword,URLName FROM " . $e2tabprefix . "Keywords WHERE URLName='$t_url'";
            $result = mysql_query($query);
            if (!$result) {
                echo "$query\n";
                die('Invalid query: ' . mysql_error() . '\n');
            }
            if (mysql_num_rows($result) == 0){
                $query = "INSERT INTO " . $e2tabprefix . "Keywords (ID,Keyword,URLName) VALUES ('','$t_key','$t_url')";
                $result = mysql_query($query);
                if (!$result) {
                    echo "$query\n";
                    die('Invalid query: ' . mysql_error() . '\n');
                }
                $query = "SELECT ID FROM " . $e2tabprefix . "Keywords WHERE URLName='$t_url'";
                $result = mysql_query($query);
                if (!$result) {
                    echo "$query\n";
                    die('Invalid query: ' . mysql_error() . '\n');
                }
                $t_res = mysql_fetch_assoc($result);
                $id_tags[$t_url]['ID'] = $t_res['ID'];
                $id_tags[$t_url]['URLName'] = $t_url;
            } else {
                $query = "SELECT ID FROM " . $e2tabprefix . "Keywords WHERE URLName='$t_url'";
                $result = mysql_query($query);
                if (!$result) {
                    echo "$query\n";
                    die('Invalid query: ' . mysql_error() . '\n');
                }
                $t_res = mysql_fetch_assoc($result);
                $id_tags[$t_url]['ID'] = $t_res['ID'];
                $id_tags[$t_url]['URLName'] = $t_url;
            }
        }
    }
    return $id_tags;
}

function assign_tags($e2tabprefix,$p_postid,$tags_id){
    foreach($tags_id as $tag){
        $t_id = $tag['ID'];
        $query = "SELECT ID,NoteID,KeywordID FROM " . $e2tabprefix . "NotesKeywords WHERE NoteID='$p_postid' AND KeywordID='$t_id'";
        $result = mysql_query($query);
        if (!$result) {
            echo "$query\n";
            die('Invalid query: ' . mysql_error() . '\n');
        }
        if (mysql_num_rows($result) == 0){
            $query = "INSERT INTO " . $e2tabprefix . "NotesKeywords (ID,NoteID,KeywordID) VALUES ('','$p_postid','$t_id')";
            $result = mysql_query($query);
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
    // Update global tags
    $addtags_id = create_tags($e2tabprefix,$addtags);
    // Parse and upload posts
    xmlparser($file_id,$total_post,$ljuser,$e2tabprefix,$offset,$addtags_id);
}
echo "Posts processed: ".count($files)."\n";
?>
