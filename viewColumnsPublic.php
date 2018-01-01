<?php

/******************************************************************************/
/* Ottoman Turkish project - view documents in multiple columns               */
/******************************************************************************/
/*  Developed by:  Steve Bagwell                                              */
/******************************************************************************/

require "config.php";
require "viewScript.php";

$iso = $_GET['iso'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" dir="ltr">

<head>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 <title><?php echo $system_title; ?></title>
 <link REL="SHORTCUT ICON" HREF="images/favicon.ico">
 <link type="text/css" rel="stylesheet" href="style.css">
 <link type="text/css" rel="stylesheet" href="columns.css?v=20170107">

 <script language=JavaScript>
  <?php echo $showColumn; ?> 
 </script>



</head>

<body>
 <div id="viewColumnsContent">
   <table class="viewColumns">
    <tr>
     <td>
      <iframe src='viewColumns.php?iso=<?php echo $iso; ?>' width=100%; height=100%;></iframe>
     </td>
     <td>
      <iframe src='viewColumns.php?iso=<?php echo $iso; ?>' width=100%; height=100%;></iframe>
     </td>
     <td id="column3"><input type="button" id="show_column3" value="<?php
         echo translate('Add Column', $st); ?>" 
         onclick="showColumn(3, '<?php echo $iso; ?>', '<?php echo $st; ?>', '<?php echo translate('Add Column', $st); ?>')">
     </td>
     <td id="column4">
     </td>
    </tr>
   </table>
 </div>
</body>

</html>

