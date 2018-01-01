<?php

/******************************************************************************/
/* Ottoman Turkish project - view documents in multiple columns               */
/******************************************************************************/
/*  Developed by:  Steve Bagwell                                              */
/******************************************************************************/

require "config.php";
require "viewUtils.php";
require "viewScript.php";

$bibleTitleResults = getBibleTitleSqlResults(
                                       mysql_real_escape_string($_GET['iso']));

$viewData = getViewData($bibleTitleResults, 
                        'bibleTitle', 
                        'bookName');

$bibleTitleOptions = $viewData->bibleTitleOptions;
$bookNameOptions = $viewData->bookNameOptions;
$chapterOptions = $viewData->chapterOptions;
$notationData = $viewData->notnData;

$jsAnnotations = "
    annotations = {" . 
      $notationData->jsAnnotations . "};
";


?>

<!DOCTYPE html>
<html>
<head>
 <meta content="text/html; charset=UTF-8" http-equiv="content-type">
 <title><?php translate('View documents', $st, 'sys'); ?></title>
 <link type="text/css" rel="stylesheet" href="style.css?d=20161227"> 

 <script language=JavaScript>
  <?php echo $jsAnnotations . 
             $jsSetAnnotations . 
             $jsScrollFunctions; 

      echo getSetAnnotationsText();
  ?>

  <?php echo $jsAnnotationsOff; ?>

 </script>
</head>

<body class="viewColumns" onclick="annotationsOff();" >
 <div>

  <div id="viewAnnotationsDiv" class="viewColumns">
   <img style='position:absolute; top:0px; right:0px;' src='images/close.jpg'
    onclick="document.getElementById('viewAnnotationsDiv').style.visibility='hidden';
         if(lastNotation) {lastNotation.style.background='';}"
    title="<?php echo translate('Close', $st, 'sys'); ?>">
   <div id="viewAnnotations"></div>
  </div>

  <table>
   <tr>
      <td>
       <form id="formViewCols" action="" method=post enctype="mulitpart/form-data">
        <select id="bibleTitle" name="bibleTitle" onchange="submit();">
        <option value=""> -- <?php echo translate('Select a current title', $st, 'sys'); ?> -- </option>
         <?php echo $bibleTitleOptions; ?>
        </select>
        <br>
        <select class="book_name" name="bookName" id="bookName" onchange="submit();">
         <option value=""> -- <?php echo translate('Select a current name', $st, 'sys'); ?> --</option>
         <?php echo $bookNameOptions; ?>
        </select>
        
        <select name=chapter" id="chapter" onchange="scroll2Chapter(this);">       
         <option value=""></option>
         <?php echo $chapterOptions; ?>
        </select>
     </form>
    </td>    
   </tr>

   <tr>
    <?php
         echo '<td class="bookColumn" id="bookContents">';
         echo ' <div id="viewNotations" class="viewColumns">' . 
                   $notationData->detail . '</div>';
         echo '</td>';
    ?>
   </tr>

  </table>
 </div>
</body>
</html>

