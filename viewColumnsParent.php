<?php

/******************************************************************************/
/* Ottoman Turkish project - view documents in multiple columns               */
/******************************************************************************/
/*  Developed by:  Steve Bagwell                                              */
/******************************************************************************/

require "config.php";
require "head.php"; 
require "viewScript.php";

echo "
    <script language=JavaScript>
       document.getElementById('content').id = 'viewColumnsContent';
    </script>
";

echo "
  <link type=\"text/css\" rel=\"stylesheet\" href=\"columns.css?v=20170106\">
   <table class=\"viewColumns\">
    <tr>
     <td>
      <iframe src='viewColumns.php?iso=" . $sec_code . "&st=" . $st ."' 
              width=100%; height=100%;></iframe>
     </td>
     <td>
      <iframe src='viewColumns.php?iso=" . $sec_code . "' width=100%; height=100%;></iframe>
     </td>
     <td>
      <iframe src='viewColumns.php?iso=" . $sec_code . "' width=100%; height=100%;></iframe>
     </td>
     <td>
      <iframe src='viewColumns.php?iso=" . $sec_code . "' width=100%; height=100%;></iframe>
     </td>
    </tr>
   </table>
 </div>
</body>

</html>
";

require "foot.php";
