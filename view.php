<?php

/******************************************************************************/
/* Ottoman Turkish project - view documents                                   */
/******************************************************************************/
/*  Developed by:  Ken Sladaritz                                              */
/*                 Marshall Computer Service                                  */
/*                 2660 E End Blvd S, Suite 122                               */
/*                 Marshall, TX 75672                                         */
/*                 ken@marshallcomputer.net                                   */
/******************************************************************************/

require 'config.php';
require "viewUtils.php";
require "viewScript.php";


$bibleTitleResults = getBibleTitleSqlResults(
                                       mysql_real_escape_string($_GET['iso']));

$viewData = getViewData($bibleTitleResults,
                        'bibleTitle',
                        'bookName');

$bibleTitleId = $viewData->bibleTitleId;
$bibleTitleOptions = $viewData->bibleTitleOptions;
$bookId = $viewData->bookId;
$bookNameOptions = $viewData->bookNameOptions;
$chapterOptions = $viewData->chapterOptions;
$notationData = $viewData->notnData;

$notations       = $notationData->notations; // unserialize($myrow['notation']);
$paragraphs      = $notationData->paragraphs; // unserialize($myrow['paragraph']);
$quotes          = $notationData->quotes; // unserialize($myrow['quote']);
$discussions     = $notationData->discussions; // unserialize($myrow['discussion']);
$recommendations = $notationData->recommendations; // unserialize($myrow['recommendation']);


if($_POST['devent']) 
{
 foreach($_POST['cords_p'] as $key=>$coordinates)
 {
  $query =
  "UPDATE `notations` SET
   `coordinates`        = \"".$coordinates."\"
   WHERE `key`          = \"".$key."\"
   AND   `bibleTitleId` = \"".$bibleTitleId."\"
   AND   `bookId`       = \"".$bookId."\"
   LIMIT 1 
   ";
   mysql_query($query) or die ("<pre>".$query."</pre>".mysql_error()."</pre>");
 }
}


// get document image thumbnails
$docDir = 'docs/'.$bookId;

$js_imageFiles = '';
$files = scandir($docDir);
foreach($files as $file)
{
 list($filename, $ext) = explode('.', $file);
 if($ext=='jpg') {$js_imageFiles .= "\"".$docDir."/".$file."\",";}
}
$js_imageFiles = rtrim($js_imageFiles, ",");


$jsAnnotations = "
    annotations = {" .
      $notationData->jsAnnotations . "};
";

$jsCoordinates = "
    coordinates = {" .
      $notationData->jsCoordinates . "};
";

?>
<!DOCTYPE html>
<html>
<head>
 <meta content="text/html; charset=UTF-8" http-equiv="content-type">
 <title><?php echo translate('View documents', $st, 'sys'); ?></title>
 <link type="text/css" rel="stylesheet" href="style.css?d=20170210">

 <script language=JavaScript>
  <?php echo $jsCoordinates .
             $jsAnnotations .
             $jsSetAnnotations; ?>
    


  <?php echo " 
  imageFiles = [".$js_imageFiles."];
  docCount = 0;
  lastNotation = '';
  lastScrolledBy = '';

  function setDoc(p)
  {
   docCount += p;
   if(docCount>imageFiles.length-1) {docCount=imageFiles.length-1; return false;}
   if(docCount<0) {docCount=0; return false;}
 
   document.getElementById('viewDoc').innerHTML = \"<img class=textimage src='\"+imageFiles[docCount]+\"'>\";
   document.getElementById('viewDoc').scrollTop = 0;

   for(key in coordinates)
   {
    document.getElementById(\"viewAnnotationsDiv\").style.visibility='hidden';

    var c = coordinates[key].split(',');
    var file = c[0];
    var t    = c[1];
    var l    = c[2];
    var h    = c[3];
    var w    = c[4];

    if(file==imageFiles[docCount])
    {
     var ni = document.getElementById('viewDoc');
     var newdiv = document.createElement('div');
     var divIdName = 'marker_'+key;
     newdiv.setAttribute('id',divIdName);
//     newdiv.setAttribute('onclick','setAnnotations(\"'+key+'\");');
     newdiv.style.position = 'absolute';
     newdiv.style.top = t;
     newdiv.style.left = l;
     newdiv.style.width = w;
     newdiv.style.height = '5px';
     newdiv.style.zIndex = '2';
//     newdiv.style.background = '#ffffcc';
//     newdiv.style.filter = 'alpha(opacity=50)';
//     newdiv.style.MozOpacity = .5;
//     newdiv.style.opacity = .5;
     ni.appendChild(newdiv);
    }
   }
  }"; ?>


  <?php
    $styleTop = "(tempY-30-document.getElementById(\"viewAnnotationsDiv\").offsetHeight)+'px'";
    $setAnnotations = getSetAnnotationsText($styleTop, "'200px'");

    echo $setAnnotations;

  ?>


  // Detect if the browser is IE or not.
  // If it is not IE, we assume that the browser is NS.
  var IE = document.all?true:false;
  var tempX = 0;
  var tempY = 0;
  var stempY = 0;
  document.onmousemove = getMouseXY;
  function getMouseXY(e)
  {
   if (IE) { // grab the x-y pos.s if browser is IE
    if(event && document.body) { 
     stempY = event.clientY; 
     tempY = event.clientY;
     tempX = event.clientX;
    }
   } else {  // grab the x-y pos.s if browser is NS
    if(e) { 
     stempY = e.pageY; 
     tempY = e.pageY;
     tempX = e.pageX;
    } 
   }
   // catch possible negative values in NS4
   if (tempX < 0) {tempX = 0;}
   if (tempY < 0) {tempY = 0;} 
  }  

  function onload_func()
  {
   var el = '';
   var setDocBusy = 0;
   if(document.getElementById("bookName").value) {setDoc(0);}

   document.getElementById('viewDoc').onscroll = function() 
   {
   	 if (lastScrolledBy == 'text')
	 {
		lastScrolledBy = '';
		return;
	 }
	 lastScrolledBy = 'jpeg';

    if(stempY < document.getElementById('viewNotations').offsetTop)
    {
     scrollJPG();
     if(setDocBusy==0)
     {

//document.getElementById('temp').innerHTML = docCount+'~'+parseInt(document.getElementById('viewDoc').scrollTop) +'~'+ document.getElementById('viewDoc').scrollHeight

      if(parseInt(document.getElementById('viewDoc').scrollTop)+300 >= document.getElementById('viewDoc').scrollHeight)
      {
       docCount++;     
       if(docCount>imageFiles.length-1) {docCount=imageFiles.length-1; return false;}
       setDocBusy = 1;
       setDoc(0);

//document.getElementById('temp').innerHTML  = docCount;
//document.getElementById('temp').innerHTML += '<br>'+document.getElementById('viewDoc').scrollTop+'~'+document.getElementById('viewDoc').scrollHeight;

       document.getElementById('viewDoc').scrollTop = 1; 

//document.getElementById('temp').innerHTML += '<br>'+document.getElementById('viewDoc').scrollTop+'~'+document.getElementById('viewDoc').scrollHeight;


       setDocBusy = 0;
       displayChapter();
      }
      if(document.getElementById('viewDoc').scrollTop == 0)
      {
       docCount--;     
       if(docCount<0) {docCount=0; return false;}
       setDocBusy = 1;
       setDoc(0);   

//document.getElementById('temp').innerHTML  = docCount;
//document.getElementById('temp').innerHTML += '<br>'+document.getElementById('viewDoc').scrollTop+'~'+document.getElementById('viewDoc').scrollHeight;

       document.getElementById('viewDoc').scrollTop = parseInt(document.getElementById('viewDoc').scrollHeight)-310; 

//document.getElementById('temp').innerHTML += '<br>'+document.getElementById('viewDoc').scrollTop+'~'+document.getElementById('viewDoc').scrollHeight;


       setDocBusy = 0;
       displayChapter();
      }
     }
    }
   }

   document.getElementById('viewNotations').onscroll = function() 
   {
    if(stempY > document.getElementById('viewNotations').offsetTop)
    {
     scrollText();
    }
   }
  }
  window.onload = onload_func;

  <?php echo $jsAnnotationsOff; ?>


  function scrollJPG()
  {
     var children = document.getElementById('viewDoc').childNodes;
     for (i=0; i<children.length; i++)
     {
      if(document.getElementById('viewDoc').scrollTop < parseInt(children[i].style.top))
      {
       el = children[i].id;
       break;
      }
     }
     var sel = el.replace('marker', 'verse');     
     document.getElementById('viewNotations').scrollTop = document.getElementById(sel).offsetTop;
  }

  function scrollText()
  {
	 if (lastScrolledBy == 'jpeg')
	 {
		lastScrolledBy = '';
		return;
	 }
	 lastScrolledBy = 'text';
     displayChapter();

     var key = el.replace("notation_", "");

     var c = coordinates[key].split(',');
     var file = c[0];
     if(file!=imageFiles[docCount])
     {
      for(docCount=0; docCount<imageFiles.length; docCount++)
      {
       if(file==imageFiles[docCount]) 
       {
        setDoc(0);
        break;
       }
      }   
     } 

//document.getElementById('temp').innerHTML = '';
//document.getElementById('temp').innerHTML += '<br>'+el;

    var sel = el.replace('notation_', '');     
    var c = coordinates[sel].split(',');
    var t    = c[1];
    document.getElementById('viewDoc').scrollTop = t.replace('px','');

//document.getElementById('temp').innerHTML += '<br>'+el+' ~ '+t;

  }

  function setChapter(obj) 
  {
   document.getElementById('viewNotations').scrollTop = document.getElementById(obj.value).offsetTop;
   scrollText();
  } 

  function displayChapter()
  {
     var children = document.getElementById('viewNotations').childNodes;
     for (ii=0; ii<children.length; ii++)
     {
      if(children[ii].id)
      {
       if(children[ii].id.indexOf("notation_p") != -1)
       {
        var r = children[ii].id;
        var rr = r.replace("notation_", "verse_");
        if(document.getElementById(rr)) {r=rr;}
        var tt = document.getElementById(r).offsetTop;
        if(document.getElementById('viewNotations').scrollTop < parseInt(tt))
        {
         el = children[ii].id;
         var str = el.split('.')
         document.getElementById('chapter').value = 'chapter_'+parseInt(str[1],10)        
         break;
        }
       }
      }
     }

  }
  
  function popupSpecialLetters()
  {
	if (! window.focus)return true;
	window.open('specLetters.html', 'specialLetters', 'width=450,height=640,scrollbars=yes');
	return false;
  }

  function popupAbbreviations()
  {
	if (! window.focus)return true;
	window.open('languageAbbrevs.html', 'languageAbbreviations', 'width=450,height=640,scrollbars=yes');
	return false;
  }

 </script>

</head>

<form id="form1" name="form1" action="" method=post enctype="multipart/form-data">

<body onclick="annotationsOff();">
 <div>
    <div id="viewBookSelect">
     <table>
      <tr>
       <td>
        <?php echo translate('Bible or Testament name', $st, 'sys'); ?>
       </td>
       <td>
         <?php echo translate('Book name', $st, 'sys'); ?>
       </td>
       <td>
         <?php echo translate('Chapter', $st, 'sys'); ?>
       </td>
       <td class='column_button' rowspan='2'><input type='button' onclick='window.open("viewColumnsPublic.php?iso=<?php echo  $_GET['iso'] . "&st=" . $_GET['st']; ?>")'
            value='<?php echo translate('Parallel Columns', $st, 'sys'); ?>'/>
       </td>
      </tr>
      <tr valign="top">
       <td>
        <select name="bibleTitle" id="bibleTitle" onchange="submit();">
         <option value=""> -- <?php echo translate('Select a current title', $st, 'sys') . " -- 
         " . $bibleTitleOptions; ?>
        </select>
       </td>
       <td>
        <select name="bookName" id="bookName" onchange="submit();">
         <option value=""> -- <?php echo translate('Select a current name', $st, 'sys')." -- 
         " . $bookNameOptions; ?>
        </select>
       </td>
       <td>
        <select name="chapter" id="chapter" onchange="setChapter(this);">
         <option value=""></option> 
         <?php echo $chapterOptions; ?>
        </select>
       </td>
      </tr>
     </table>
    </div>

    <div id="viewAnnotationsDiv">
     <img style='position:absolute; top:0px; right:0px;' src='images/close.jpg'
      onclick="document.getElementById('viewAnnotationsDiv').style.visibility='hidden';
              if(lastNotation) {lastNotation.style.background='';}"
      title='<?php echo translate("Close", $st, "sys"); ?>'>
     <div id="viewAnnotations"></div>
    </div>

    <div id="viewDoc"></div>

    
    <div id="viewTitle">
     <span><?php echo translate('Transcription below. To see pop-up notes click on an underlined word.', $st, 'sys'); ?></span>
	 <span class=explanationButtons>
		<button type='button' onclick='return popupAbbreviations()'><?php echo translate('Languages', $st, 'sys'); ?></button>
		<button type='button' onclick='return popupSpecialLetters()'><?php echo translate('Special Letters', $st, 'sys'); ?></button>
	 </span>
    </div>

    <div id="viewNotations"><?php echo $notationData->detail; ?></div>

 <input name="devent" id="devent" type="hidden">


<!--
<div style=\"position:absolute; top:740px;\" id=\"temp\">temp</div>
-->

 </div>
</body>
</form>
</html>

