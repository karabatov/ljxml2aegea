<?php
// Yuri Karabatov karabatov@gmail.com

//error_reporting(E_ALL);

$config = simplexml_load_file('ljdump.config');

$e2server = $config -> e2server ? $config -> e2server : 'localhost';
$e2database = $config -> e2database ? $config -> e2database : 'e2db';
$e2user = $config -> e2user ? $config -> e2user : 'root';
$e2password = $config -> e2password ? $config -> e2password : '';
$e2tabprefix = $config -> e2tabprefix ? $config -> e2tabprefix : 'e2Blog';
$offset = $config -> offset ? $config -> offset : 0;
$addtags = $config -> addtags ? $config -> addtags : 'LiveJournal';
$ignoretag = $config -> ignoretag ? $config -> ignoretag : 'e2import';
$ljuser = $config -> username;

echo "Starting ljdump...\n";
$ljdump = shell_exec('`which python` ljdump.py');
echo "$ljdump\n";

// DB connection
@$connect = mysql_connect($e2server, $e2user, $e2password) or die("DB is not avalible");
mysql_select_db($e2database);
my_sql_query("set character_set_client='cp1251'");
my_sql_query("set character_set_results='cp1251'");
my_sql_query("set collation_connection='cp1251_general_ci'");
// Ensure future e2 content isn't overwritten
my_sql_query("ALTER TABLE " . $e2tabprefix . "Comments AUTO_INCREMENT = 500000");
// my_sql_query("ALTER TABLE " . $e2tabprefix . "Notes AUTO_INCREMENT = 1000000000");

echo "Connected to DB\n";

// Convert tags to ascii for URLs
function to_ascii($str, $delimiter='-') {

    $toreplace = array(
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
function ljclean($ljtext) {
    //echo "* in ljclean function\n";
    $userPA = '|<\s*lj\s+user\s*=\s*["\']([\w-]+)["\']\s*/?\s*>|i';
    $commPA = '|<\s*lj\s+comm\s*=\s*["\']([\w-]+)["\']\s*/?\s*>|i';
    $namedCutPA = '|<\s*lj-cut\s+text="([^"]*)"\s*>|is';
    $cutPA = '|<lj-cut>|i';
    $ccutPA = '|</lj-cut>|i';

    $userRE = '<span class="lj-user"><a href="http://$1.livejournal.com/profile"><img width="16" height="16" src="http://l-stat.livejournal.com/img/userinfo.gif"></a><a href="http://$1.livejournal.com">$1</a></span>';
    $commRE = '<span class="lj-comm"><a href="http://$1.livejournal.com/profile"><img width="16" height="16" src="http://l-stat.livejournal.com/img/userinfo.gif"></a><a href="http://$1.livejournal.com">$1</a></span>';

    $ljclean = preg_replace($userPA,$userRE,$ljtext);
    $ljclean = preg_replace($commPA,$commRE,$ljclean);
    $ljclean = preg_replace($namedCutPA,'$1',$ljclean);
    $ljclean = preg_replace($cutPA,'',$ljclean);
    $ljclean = preg_replace($ccutPA,'',$ljclean);

    return $ljclean;
}

function get_postdata($file_id) {

    $pd = array();

    // Load source file
    $entry = simplexml_load_file($file_id);

    // Post data
    $pd['date'] = $entry -> event_timestamp;
    $entry -> subject == '' ? $pd['subject'] = '(no title)' : $pd['subject'] = $entry -> subject;
    $pd['text'] = $entry -> event;
    if (($entry -> security == 'usemask') || ($entry -> security == 'private')) { // "usemask" or "private" => IsVisible = 0
        $pd['visible'] = 0;
    }
    else {
        $pd['visible'] = 1;
    }
    $pd['tags'] = $entry -> props -> taglist;
    $pd['postid'] = $entry -> ditemid;
    $pd['mood'] = $entry -> props -> current_mood;
    $pd['music'] = $entry -> props -> current_music;
    $pd['location'] = $entry -> props -> current_location;
    $pd['coords'] = $entry -> props -> current_coords;

    // Append mood, music & location info

    if ($pd['mood'] || $pd['music'] || $pd['location'])
        $pd['text'] .= PHP_EOL . PHP_EOL;

    // Location
    if ($pd['location']) {
        if ($pd['coords']) {
            $p_loctext = $pd['coords'] . ' (' . $pd['location'] . ')';
            $pd['text'] .= '<strong>Current location:</strong> <a href="http://maps.google.com/maps?q=' . urlencode($p_loctext) . '">' . $pd['location'] . '</a>' . PHP_EOL;
        } else {
            $pd['text'] .= PHP_EOL . PHP_EOL . '<strong>Current location:</strong> <a href="http://maps.google.com/maps?q=' . urlencode($pd['location']) . '">' . $pd['location'] . '</a>' . PHP_EOL;
        }
    }

    // Mood
    if ($pd['mood'])
        $pd['text'] .= '<strong>Current mood:</strong> ' . $pd['mood'] . PHP_EOL;

    // Music
    if ($pd['music'])
        $pd['text'] .= '<strong>Current music:</strong> ' . $pd['music'];

    // Clean up tags
    $pd['subject'] = strip_tags($pd['subject']);
    $pd['text'] = ljclean($pd['text']);

    return $pd;
}

function get_commentdata($file_id) {

    $cd = array();

    $c_file_id = preg_replace('/L-/', 'C-', $file_id);

    if (file_exists($c_file_id)) {
        $xml = simplexml_load_file($c_file_id);
        $i = 1;
        foreach ($xml -> comment as $comment) {
            $cd[$i]['id'] = $comment -> id;
            $cd[$i]['author'] = $comment -> user == '' ? 'anonymous' : $comment -> user;
            $cd[$i]['text'] = ljclean($comment -> body);
            $cd[$i]['parentid'] = $comment -> parentid;
            $cd[$i]['state'] = $comment -> state == 'S' ? 0 : 1;
            $cd[$i]['date'] = strtotime($comment -> date);
            $i++;
        }
    }

    return $cd;
}

function my_sql_query($query) {

    $result = mysql_query ($query);
    if (!$result) {
        echo "$query\n";
        die('Invalid query: ' . mysql_error() . '\n');
    }

    return $result;
}

function put_post_db($pd, $e2tabprefix, $offset) {

    // Convert to cp1251
    $pd['subject'] = iconv('UTF-8', 'Windows-1251//IGNORE', $pd['subject']);
    $pd['text'] = iconv('UTF-8', 'Windows-1251//IGNORE', $pd['text']);

    // ""
    $pd['text'] = mysql_escape_string($pd['text']);
    $pd['subject'] = mysql_escape_string($pd['subject']);

    // Find e2 post ID first
    $query = "SELECT ID,OriginalAlias FROM " . $e2tabprefix . "Notes WHERE OriginalAlias='" . $pd['postid'] . "'";
    $result = my_sql_query($query);
    $t_res = mysql_fetch_assoc($result);
    $e2_postid = $t_res['ID'] ? $t_res['ID'] : '';

    // Fix date
    $pd['date'] = $pd['date'] - $offset;

    // Put post into DB
    // ID = $e2_postid
    // Title = $pd['subject']
    // Text = $pd['text']
    // OriginalAlias = $pd['postid']
    // IsVisible = $pd['visible']
    // Stamp = $pd['date']
    // LastModified = $pd['date']
    // IsDST = 0
    // Offset = $offset
    $query = "INSERT INTO " . $e2tabprefix . "Notes (ID,Title,OriginalAlias,Text,IsPublished,IsVisible,Stamp,LastModified,IsDST,Offset,IP) VALUES ('$e2_postid','" . $pd['subject'] . "','" . $pd['postid'] . "','" . $pd['text'] . "',1," . $pd['visible'] . ",'" . $pd['date'] . "','" . $pd['date'] . "',0,$offset,'127.0.0.1') ON DUPLICATE KEY UPDATE ID='$e2_postid',Title='" . $pd['subject'] . "',OriginalAlias='" . $pd['postid'] . "',Text='" . $pd['text'] . "',IsPublished=1,IsVisible=" . $pd['visible'] . ",Stamp='" . $pd['date'] . "',LastModified='" . $pd['date'] . "',IsDST=0,Offset=$offset,IP='127.0.0.1'";
    $result = my_sql_query($query);

    // Check if Alias has been defined first
    if ($e2_postid != '') {
        $query = "SELECT ID,EntityID FROM " . $e2tabprefix . "Aliases WHERE EntityID='$e2_postid'";
        $result = my_sql_query($query);
        $t_res = mysql_fetch_assoc($result);
        $e2_aliasid = $t_res['ID'] ? $t_res['ID'] : '';
    } else { // Get the ID new post has received
        $query = "SELECT ID,OriginalAlias FROM " . $e2tabprefix . "Notes WHERE OriginalAlias='" . $pd['postid'] . "'";
        $result = my_sql_query($query);
        $t_res = mysql_fetch_assoc($result);
        $e2_postid = $t_res['ID'] ? $t_res['ID'] : '';
        $e2_aliasid = '';
    }
    // Assign alias to post
    // ID = $e2_aliasid
    // EntityID = $e2_postid
    // Alias = $pd['postid']
    // Stamp = $pd['date']
    if ($e2_postid != '') { // Don't bother if there's no new post ID
        $query = "INSERT INTO " . $e2tabprefix . "Aliases (ID,EntityID,Alias,Stamp) VALUES ('$e2_aliasid','$e2_postid','" . $pd['postid'] . "','" . $pd['date'] . "') ON DUPLICATE KEY UPDATE ID='$e2_aliasid',EntityID='$e2_postid',Alias='" . $pd['postid'] . "',Stamp='" . $pd['date'] . "'";
        $result = my_sql_query($query);
    }

    return $e2_postid;
}

function put_comments_db($cd, $e2_postid, $ljuser, $e2tabprefix, $offset) {

    // Put comments into DB
    // ID = 100000 + $cd['id']
    // NoteID = $e2_postid
    // AuthorName = $cd['author']
    // AuthorEmail $cd['author'] + @livejournal.com
    // Text = $cd['text']
    // Reply
    // IsVisible = $cd['state']
    // IsReplyVisible
    // Stamp = $cd['date']
    // LastModified = $cd['date']
    // ReplyStamp
    // ReplyLastModified
    for ($i = 1; $i <= count($cd); $i++) {
        if ($cd[$i]['text'] == '')
            $cd[$i]['text'] = '(deleted comment)';

        // Convert encoding
        $cd[$i]['text'] = iconv('UTF-8', 'Windows-1251//IGNORE', $cd[$i]['text']);
        $cd[$i]['author'] = iconv('UTF-8', 'Windows-1251//IGNORE', $cd[$i]['author']);

        // ""
        $cd[$i]['text'] = mysql_escape_string($cd[$i]['text']);

        // Check if comment is a reply by journal author
        if (($cd[$i]['author'] == $ljuser) && ($cd[$i]['parentid'] != '')) {
            // Ensure existing e2 comments aren't overwritten
            $cd[$i]['parentid'] += 100000;
            $query = "INSERT INTO " . $e2tabprefix . "Comments (ID,IsReplyVisible,Reply,ReplyStamp,ReplyLastModified) VALUES (" . $cd[$i]['parentid'] . ",1,'" . $cd[$i]['text'] . "','" . $cd[$i]['date'] . "','" . $cd[$i]['date']. "') ON DUPLICATE KEY UPDATE IsReplyVisible=1,Reply='" . $cd[$i]['text'] . "',ReplyStamp='" . $cd[$i]['date'] . "',ReplyLastModified='" . $cd[$i]['date'] . "'";
            $result = my_sql_query($query);
        } else {
            // Ensure existing e2 comments aren't overwritten
            $cd[$i]['id'] += 100000;
            $query = "INSERT INTO " . $e2tabprefix . "Comments (ID,NoteID,AuthorName,AuthorEmail,Text,IsVisible,Stamp,LastModified) VALUES (" . $cd[$i]['id'] . ",'$e2_postid','" . $cd[$i]['author'] . "','" . $cd[$i]['author'] . "@livejournal.com','" . $cd[$i]['text'] . "','" . $cd[$i]['state'] . "','" . $cd[$i]['date'] . "','" . $cd[$i]['date'] . "') ON DUPLICATE KEY UPDATE NoteID='$e2_postid',AuthorName='" . $cd[$i]['author'] . "',AuthorEmail='" . $cd[$i]['author'] . "@livejournal.com',Text='" . $cd[$i]['text'] . "',IsVisible='" . $cd[$i]['state'] . "',Stamp='" . $cd[$i]['date'] . "',LastModified='" . $cd[$i]['date'] . "';"; 
            $result = my_sql_query($query);
        }
    }

    return true;
}

// Get crossposted e2 post ID from LJ imported post
function get_e2_postid($e2tabprefix, $pd_text) {

    $e2_postid = '';

    // This is very dumb but I haven't come up w/anything better yet
    $matchnum = preg_match_all('|href="([^"]+)/all/([^"/]+)/?"|i', $pd_text, $matches, PREG_SET_ORDER);

    if ($matchnum > 0) {
        $alias = $matches[count($matches) - 1][2];

        $query = "SELECT EntityID FROM " . $e2tabprefix . "Aliases WHERE Alias='$alias'";
        $result = my_sql_query($query);
        $t_res = mysql_fetch_assoc($result);
        $e2_postid = $t_res['EntityID'];
    }

    return $e2_postid;
}

// XML files parser
function xmlparser($file_id, $current_post, $ljuser, $e2tabprefix, $offset, $addtags_id, $ignoretag) {

    // Get post data
    $pd = get_postdata($file_id);

    // Get comment data
    $cd = get_commentdata($file_id);

    // Check if post has "ignore tag"
    $tag_pos = mb_stripos($pd['tags'], $ignoretag);
    if ($tag_pos === false) {
        // Put post into DB
        $e2_postid = put_post_db($pd, $e2tabprefix, $offset);

        if ($e2_postid != '') {

            // Put tags into DB
            $p_addtags_id = create_tags($e2tabprefix, $pd['tags']);
            assign_tags($e2tabprefix, $e2_postid, $addtags_id);
            assign_tags($e2tabprefix, $e2_postid, $p_addtags_id);

            // Put comments into DB
            put_comments_db($cd, $e2_postid, $ljuser, $e2tabprefix, $offset);
        }

    } else {
        // Parse post text for e2 link
        $cross_postid = get_e2_postid($e2tabprefix, $pd['text']);
        if ($cross_postid != '')
            put_comments_db($cd, $cross_postid, $ljuser, $e2tabprefix, $offset);
    }

    return true;
}

function create_tags($e2tabprefix, $addtags) {
    $tags = preg_split('~\s*,\s*~', $addtags);
    $id_tags = array();
    foreach ($tags as $tag) {
        if ($tag != '') {
            $t_key = iconv('UTF-8', 'Windows-1251', $tag);
            $t_key = mysql_escape_string($t_key);
            $t_url = to_ascii($tag);
            $query = "SELECT ID,Keyword,URLName FROM " . $e2tabprefix . "Keywords WHERE URLName='$t_url'";
            $result = my_sql_query($query);
            if (mysql_num_rows($result) == 0){
                $query = "INSERT INTO " . $e2tabprefix . "Keywords (ID,Keyword,URLName) VALUES ('','$t_key','$t_url')";
                $result = my_sql_query($query);

                $query = "SELECT ID FROM " . $e2tabprefix . "Keywords WHERE URLName='$t_url'";
                $result = my_sql_query($query);
                $t_res = mysql_fetch_assoc($result);
                $id_tags[$t_url]['ID'] = $t_res['ID'];
                $id_tags[$t_url]['URLName'] = $t_url;
            } else {
                $query = "SELECT ID FROM " . $e2tabprefix . "Keywords WHERE URLName='$t_url'";
                $result = my_sql_query($query);
                $t_res = mysql_fetch_assoc($result);
                $id_tags[$t_url]['ID'] = $t_res['ID'];
                $id_tags[$t_url]['URLName'] = $t_url;
            }
        }
    }
    return $id_tags;
}

function assign_tags($e2tabprefix, $p_postid, $tags_id) {
    foreach($tags_id as $tag) {
        $t_id = $tag['ID'];
        $query = "SELECT ID,NoteID,KeywordID FROM " . $e2tabprefix . "NotesKeywords WHERE NoteID='$p_postid' AND KeywordID='$t_id'";
        $result = my_sql_query($query);
        if (mysql_num_rows($result) == 0) {
            $query = "INSERT INTO " . $e2tabprefix . "NotesKeywords (ID,NoteID,KeywordID) VALUES ('','$p_postid','$t_id')";
            $result = my_sql_query($query);
        }
    }
    return true;
}

$files = glob("./$ljuser/L-*");
natsort($files);

foreach($files as $file_id) {

    $current_post = preg_replace("/(.*)L-(.*)/", "$2", $file_id);

    // Update global tags
    $addtags_id = create_tags($e2tabprefix, $addtags);

    // Parse and upload posts
    xmlparser($file_id, $current_post, $ljuser, $e2tabprefix, $offset, $addtags_id, $ignoretag);

    echo ".";
    if ($current_post % 80 == 0) echo "\n";
}
echo "\nPosts processed: " . count($files) . "\n";
?>
