<?php

require "config.php";
require "head.php";
require "upload_functions.php";

if($_POST['bibleTitle'])
{
 $query =
 "SELECT * FROM `bibleTitles`
  WHERE `title` = \"".mysql_real_escape_string($_POST['bibleTitle'])."\"
  LIMIT 1 
 ";
 $result=mysql_query($query) or die ("<pre>".$query.mysql_error()."</pre>");
 $myrow=mysql_fetch_array($result);
 if($myrow) {$bibleTitleId = $myrow['id'];} 
 else
 {
  $query =
  "INSERT INTO `bibleTitles` SET
   `title` = \"".mysql_real_escape_string($_POST['bibleTitle'])."\",
   `code`  = \"".$sec_code."\",
   `displayDefault` = 0
  ";
  mysql_query($query) or die ("<pre>".$query.mysql_error()."</pre>");
  $bibleTitleId = mysql_insert_id();
 }
}

if($_POST['bookName'])
{
 $query =
 "SELECT * FROM `books`
  WHERE `name`          = \"".mysql_real_escape_string($_POST['bookName'])."\"
  AND   `bibleTitleId`  = \"".$bibleTitleId."\"
  LIMIT 1 
 ";
 $result=mysql_query($query) or die ("<pre>".$query.mysql_error()."</pre>");
 $myrow=mysql_fetch_array($result);
 if($myrow) 
 {
  $bookId = $myrow['id'];
  $_POST['bookDisplayOrder'] = $myrow['displayOrder'];
 } 
 else
 {
  $query =
  "INSERT INTO `books` SET
   `bibleTitleId` = \"".$bibleTitleId."\",
   `name`         = \"".mysql_real_escape_string($_POST['bookName'])."\",
   `displayOrder` = ".$_POST['bookDisplayOrder'].",
   `displayDefault` = 0
  ";
  mysql_query($query) or die ("<pre>".$query.mysql_error()."</pre>");
  $bookId = mysql_insert_id();
 }
}

if($_POST['devent']=='reprocess_files')
{
 processUpload($bookId, $bibleTitleId);
}


// get bible title options
$bibleTitle_options = '';
$query =
"SELECT * FROM `bibleTitles`
 WHERE `code` = \"".mysql_real_escape_string($sec_code)."\"
 ORDER BY `title`
";
$result=mysql_query($query) or die ("<pre>".$query.mysql_error()."</pre>");
while($myrow=mysql_fetch_array($result))
{$bibleTitle_options .= "<option value=\"".$myrow['title']."\" ".$selected.">".$myrow['title'];}

// get book name options
$bookName_options = '';
$query =
"SELECT * FROM `books`
 WHERE `bibleTitleId` = \"".$bibleTitleId."\"
 ORDER BY `displayOrder`, `name`
";
$result=mysql_query($query) or die ("<pre>".$query.mysql_error()."</pre>");
while($myrow=mysql_fetch_array($result))
{$bookName_options .= "<option value=\"".$myrow['name']."\" ".$selected.">".$myrow['name'];}



echo "


 <script language=JavaScript>

  function reprocess_files()
  {
   errmsg = ''; 
   if(!document.getElementById('bibleTitle').value) {errmsg += '".translate('title is required', $st, 'sys')."\\n';}
   if(!document.getElementById('bookName').value)   {errmsg += '".translate('book name is required', $st, 'sys')."\\n';}
   if(errmsg)
   {
    alert(errmsg);
    return false;
   }
   document.getElementById('devent').value='reprocess_files';
   document.form1.submit();
  } 

 </script>

 <p />
  ".translate('Bible or Testament name', $st, 'sys')." <br>
  <input name=\"bibleTitle\" id=\"bibleTitle\" value=\"".$_POST['bibleTitle']."\" size=40>
  <select onchange=\"document.getElementById('bibleTitle').value=this.value; submit();\">
   <option value=\"\"> -- ".translate('All Titles', $st, 'sys')." -- 
   ".$bibleTitle_options."
  </select> 
  
 <p />
  ".translate('Book name', $st, 'sys')." <br />
  <input name=\"bookName\" id=\"bookName\" value=\"".$_POST['bookName']."\" size=40>
  <select onchange=\"document.getElementById('bookName').value=this.value; submit();\">
   <option value=\"\"> -- ".translate('All Books', $st, 'sys')." -- 
   ".$bookName_options."
  </select> 
  
 <p />
  <input type=button value=\"".translate('Reprocess files', $st, 'sys')."\" onclick=\"reprocess_files()\">

";

require "foot.php";

?>