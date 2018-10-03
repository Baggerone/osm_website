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

$viewData = getViewPHPData($bibleTitleResults, 
                           'bibleTitle', 
                           'bookName');

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
 <link type="text/css" rel="stylesheet" href="style.css?d=20170401"> 


</head>

<body class="viewPDF">
 <div>

  <table>
   <tr>
    <td class="info">
  Bu döküm nihai olmayıp devamlı olarak düzeltilmektedir. <br>
  Hata bulursanız lütfen <a href="mailto:iletisim@osmanlicakelam.net">iletisim@osmanlicakelam.net</a> adresine haber veriniz. <br>
  Asıl Osmanlıca sayfaları ve altları çizili kelimelere ait notları <a href="http://www.osmanlicakelam.net">www.osmanlicakelam.net</a> sitesinde görebilirsiniz. 
    </td>
   </tr>

   <tr>
    <td class="info">
  This is a provisional transcription. Corrections are continually made. <br>
  If you see any errors please write to
  <a href="mailto:iletisim@osmanlicakelam.net">iletisim@osmanlicakelam.net</a>. <br>
  To see the pop-up notes and the original Ottoman Turkish pages go to 
   <a href="http://www.osmanlicakelam.net">www.osmanlicakelam.net</a>.
    </td>
   </tr>



   <tr><td></td></tr>

   <tr>
     <td>
       <span class="bibleName">
         <?php echo($viewData->bibleTitle); ?> &nbsp;&nbsp;&nbsp;  <span style="font-size:.8em"><?php echo(date("Y-m-d")); ?></span>
       </span><br>
       <span class="viewPDFURL">
         <?php 
           $iso = $_GET['iso'];
           $st = $_GET['st'];
       
            echo("http://$_SERVER[HTTP_HOST]/viewPDF.php?bibleTitleId=$viewData->bibleTitleId&bookId=$viewData->bookId&iso=$iso&st=$st"); 
         ?>
       </span>

     </td>
   </tr>

   <tr>
    <?php
         echo '<td class="bookColumn" id="bookContents">';
         echo ' <div id="viewNoNotations" class="viewPDF">' . 
                   $notationData->detail . '</div>';
         echo '</td>';
    ?>
   </tr>

  </table>
 </div>
 <script language=JavaScript>
  alert("To save as PDF press CTRL-P. \nSelect 'Microsoft Print to PDF' \n" +
        "Note: for large books, it can take some time for this process to complete.");
 </script>

</body>
</html>
