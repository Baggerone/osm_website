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

if (!$_POST['bibleTitle'] and $_COOKIE['bibleTitle'])
{
	$_POST['bibleTitle'] = $_COOKIE['bibleTitle'];
	$_POST['bookName'] = $_COOKIE['bookName'];
}

// get bible title options
$bibleTitle_options = '';
$query =
"SELECT * FROM `bibleTitles`
 WHERE `code` = \"".mysql_real_escape_string($_GET['iso'])."\"
 ORDER BY `title`
";
$result=mysql_query($query) or die ("<pre>".$query.mysql_error()."</pre>");
while($myrow=mysql_fetch_array($result))
{
 $selected = '';
 if($myrow['title']==$_POST['bibleTitle'])
 {$selected = 'selected';}
 if(!$_POST['bibleTitle'] and $myrow['displayDefault']==1)  
 {
  $selected = 'selected';
  $_POST['bibleTitle'] = $myrow['title'];
 }
 $bibleTitle_options .= "<option value=\"".$myrow['title']."\" ".$selected.">".$myrow['title'];
}

if($_POST['bibleTitle'])
{
 setcookie('bibleTitle', $_POST['bibleTitle']);
 $query =
 "SELECT * FROM `bibleTitles`
  WHERE `title` = \"".mysql_real_escape_string($_POST['bibleTitle'])."\"
  LIMIT 1 
 ";
 $result=mysql_query($query) or die ("<pre>".$query.mysql_error()."</pre>");
 $myrow=mysql_fetch_array($result);
 $bibleTitleId = $myrow['id']; 
}

// get book name options
$bookName_options = '';
$query =
"SELECT * FROM `books`
 WHERE `bibleTitleId` = \"".$bibleTitleId."\"
 ORDER BY `displayOrder`, `name`
";
$result=mysql_query($query) or die ("<pre>".$query.mysql_error()."</pre>");
while($myrow=mysql_fetch_array($result))
{
 $selected = '';
 if($myrow['name']==$_POST['bookName'])
 {$selected = 'selected';}
 if(!$_POST['bookName'] and $myrow['displayDefault']==1)  
 {
  $selected = 'selected';
  $_POST['bookName'] = $myrow['name'];
 }
 $bookName_options .= "<option value=\"".$myrow['name']."\" ".$selected.">".$myrow['name'];
}

if($_POST['bookName'])
{
 setcookie('bookName', $_POST['bookName']);
 $query =
 "SELECT * FROM `books`
  WHERE `name`          = \"".mysql_real_escape_string($_POST['bookName'])."\"
  AND   `bibleTitleId`  = \"".$bibleTitleId."\"
  LIMIT 1 
 ";
 $result=mysql_query($query) or die ("<pre>".$query.mysql_error()."</pre>");
 $myrow=mysql_fetch_array($result);
 if($myrow) 
 $bookId = $myrow['id'];
} 


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

$js_coordinates = '';
$js_annotations = '';
$quotes = array();
$s = array("\"","\n");
$r = array("\\\"","<br \>");
$detail = '';

$query =
"SELECT * FROM `notations`
 WHERE `bibleTitleId` = \"".$bibleTitleId."\"
 AND   `bookId`       = \"".$bookId."\"
 AND   `inactive`    != \"Y\"
 ORDER BY `key`
";
$result=mysql_query($query) or die ("<pre>".$query.mysql_error()."</pre>");
while($myrow=mysql_fetch_array($result))
{
 if($myrow['coordinates'] /*and $myrow['quote']*/) 
 {
  $js_coordinates .= "\"p".$myrow['key']."\":\"".
  $myrow['coordinates']."\",";
 }

 $notations       = unserialize($myrow['notation']);
 $paragraphs      = unserialize($myrow['paragraph']);
 $quotes          = unserialize($myrow['quote']);
 $discussions     = unserialize($myrow['discussion']);
 $recommendations = unserialize($myrow['recommendation']);

 $dnotation = '';
 if (isset($notations[0]))
 {
	 $i = 0;
	 foreach($notations as $notation) 
	 {
	  if($paragraphs[$i]=='Y') 
	  {
	   if($i==0) {$dnotation = "<p />".$dnotation;}
	   else {$notation = "<div class=\"paragraph\"></div>&nbsp;&nbsp;".$notation;}
	  }

	  list($book, $chapter, $verse) = explode(".", $myrow['key']);
	  $chapter = ltrim($chapter, '0');
	  $verse = ltrim($verse, '0');

	  if($chapter and $verse)
	  {
	   if($chapter != $sav_chapter) 
	   {
		$dnotation .= "\r\n<div id=\"chapter_".$chapter."\"  class=\"chapter\">".$chapter."</div>\r\n";
		$chapter_options .= "<option value=\"chapter_".$chapter."\">".$chapter;
	   }
	   if($verse   != $sav_verse)  
	   {$dnotation .= "\r\n<nobr><div id=\"verse_p".$myrow['key']."\" class=\"verse\">".$verse."</div>\r\n</nobr>";}
	   $sav_chapter = $chapter;   
	   $sav_verse   = $verse;
	  }

	  $dnotation .= $notation." ";
	  $i++;
	 }
 }

 // normalize data to make matching work better
 $dnotation = Normalizer::normalize($dnotation, Normalizer::FORM_KC);
 
 // quotes
 if (isset($quotes[0]))
 {
        $limit = 1;
	$ii = 0;
	foreach($quotes as $quote) 
	{
		// underline single instance of quote field
		$quote = Normalizer::normalize($quote, Normalizer::FORM_KC);
                // ensure no leftover initial spaces keep this link from being included
                $quote = ltrim($quote);

		if ($quote != "")
		{
			$reg_from = '/'.preg_quote($quote, '/').'/';
			$dnotation = preg_replace($reg_from, "<div id=\"quote".$myrow['key']."_".$ii."\" class=\"quote\" onclick=setTimeout(\"setAnnotations('".$myrow['key']."_".$ii."')\",250)><a href=\"#\">".$quote."</a></div>", $dnotation, 1);
			$quotes[$quote] = $myrow['key']."_".$ii;  

			$js_annotations .= "\"".$myrow['key']."_".$ii."\":\"".
			str_replace($s, $r, $quote)."^".
			str_replace($s, $r, $discussions[$ii])."^".
			str_replace($s, $r, $recommendations[$ii])."\",";
		}

		$ii++;
	}
 }

 $specialChrs = array("î","ʿ");

 # add a narrow space between an f and an i that has a circumflex accent
 foreach ($specialChrs as $nextChr) {
   $dnotation = str_replace('f' . $nextChr, 
                            '<nobr class="nudge_right">f</nobr>' . $nextChr,
                            $dnotation);
 }


 $detail .= "\r\n<span id=\"notation_p".$myrow['key']."\" class=\"notation\">".$dnotation."</span>\r\n";
}

$js_annotations = rtrim($js_annotations, ",");
$js_coordinates = rtrim($js_coordinates, ",");

echo "
<!DOCTYPE html>
<html>
<head>
 <meta content=\"text/html; charset=UTF-8\" http-equiv=\"content-type\">
 <title>".translate('View documents', $st, 'sys')."</title>
 <link type=\"text/css\" rel=\"stylesheet\" href=\"style.css?d=20170118\">

 <script language=JavaScript>

  coordinates = {".$js_coordinates."};
  annotations = {".$js_annotations."};

  // Language abbreviations that need to get a special style
  // Special characters are hard to deal with in vim, so separate them out.
  langAbbrevs = ['Akad.', 'Alm.', 'Ar.', 'Aram.', 'Bulg.', 'Fa.', 'Fra.', 'Lat.',
                 'Mac.', 'Macar.', 'Osm.',  
                 'Rum.', 'Skt.', 'E.Yun.', 'Yun.'];
  langAbbrevs.push('İbr.');
  langAbbrevs.push('İng.');
  langAbbrevs.push('İta.');
  langAbbrevs.push('İta.');
  langAbbrevs.push('Soğ.');
  langAbbrevs.push('E.Tü.');
  langAbbrevs.push('U.Tü.');
  langAbbrevs.push('Kz.Tü.');
  langAbbrevs.push('K.Tü.');
  langAbbrevs.push('T.Tü.');
  langAbbrevs.push('Y.Tü.');
  langAbbrevs.push('Tü.');
  langAbbrevs.push('Moğ.');
  langAbbrevs.push('Soğd.');
 
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
  }

  // Wrap a string (e.g. mec.) in a span with a class to make italicized
  function replaceLangAbbrLite(origString, replString) {
      spanClass = 'langAbbrLite';
      outString = origString;

      newReplString = '<span class=\"' + spanClass + '\">' + replString + '</span>';
      outString = outString.replace(replString, newReplString);

      return outString;
  }

  // Remove periods from a substring (e.g. Ar.) and wrap in a span with a class
  // to make it bold and italicized
  function replaceLangAbbrev(origString, replString) {
      spanClass = 'langAbbreviation';
      outString = origString;
      while (outString.indexOf(replString) >= 0 ) {
          // Start building the new replacement string
          newReplString = replString;

          // Remove the one or two periods
          newReplString = newReplString.replace('.', '');
          newReplString = newReplString.replace('.', '');

          // Wrap the replacement string in a span
          newReplString = '<span class=\"' + spanClass + '\">' + newReplString + '</span>'; 
          outString = outString.replace(replString, newReplString);
      }

      return outString;
  }


  function setAnnotations(key)
  {
    var a = annotations[key].split('^');
    var annDef = a[1];

    // Give the language abbreviations a different style via wrapping in a span
    annDef = replaceLangAbbrLite(annDef, 'mec.');

    for (index = 0; index < langAbbrevs.length; index++) {
        annDef = replaceLangAbbrev(annDef, langAbbrevs[index]);
    }
    document.getElementById(\"viewAnnotations\").innerHTML = '<b>' + a[0] + '</b> : ' + annDef;

    var links = a[2].split('\t');
	for (i =0; i < links.length; i++)
	{
		if(links[i].search('http'))
		{document.getElementById(\"viewAnnotations\").innerHTML += '<p />' + links[i];}
		else
		{
			if (i == 0)
			{document.getElementById(\"viewAnnotations\").innerHTML += '<p />';}
			else
			{document.getElementById(\"viewAnnotations\").innerHTML += '<br>';}
			document.getElementById(\"viewAnnotations\").innerHTML += '<a href=\"' + links[i]+ '\" target=\"_blank\">' + links[i]+ '</a>';
		}
	}

    document.getElementById(\"viewAnnotationsDiv\").style.visibility='visible';
    document.getElementById(\"viewAnnotationsDiv\").style.top  = (tempY-30-document.getElementById(\"viewAnnotationsDiv\").offsetHeight)+'px';
//    document.getElementById(\"viewAnnotationsDiv\").style.left = tempX+'px';
    document.getElementById(\"viewAnnotationsDiv\").style.left = '200px';
  }


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
   if(document.getElementById(\"bookName\").value) {setDoc(0);}

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

  function annotationsOff()
  {
   document.getElementById('viewAnnotationsDiv').style.visibility='hidden';
  } 

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

     var key = el.replace(\"notation_\", \"\");

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
       if(children[ii].id.indexOf(\"notation_p\") != -1)
       {
        var r = children[ii].id;
        var rr = r.replace(\"notation_\", \"verse_\");
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

<form id=\"form1\" name=\"form1\" action=\"\" method=post enctype=\"multipart/form-data\">

<body onclick=\"annotationsOff();\">
 <div>
    <div id=\"viewBookSelect\">
     <table>
      <tr>
       <td>
        ".translate('Bible or Testament name', $st, 'sys')."
       </td>
       <td>
        ".translate('Book name', $st, 'sys')."
       </td>
       <td>
        ".translate('Chapter', $st, 'sys')."
       </td>
       <td class='column_button' rowspan='2'><input type='button' onclick='window.open(\"viewColumnsPublic.php?iso=" . $_GET['iso'] . "&st=" . $_GET['st'] . "\")'
            value='" . translate('Parallel Columns', $st, 'sys') . "'/>
       </td>
      </tr>
      <tr valign=\"top\">
       <td>
        <select name=\"bibleTitle\" id=\"bibleTitle\" onchange=\"submit();\">
         <option value=\"\"> -- ".translate('Select a current title', $st, 'sys')." -- 
         ".$bibleTitle_options."
        </select>
       </td>
       <td>
        <select name=\"bookName\" id=\"bookName\" onchange=\"submit();\">
         <option value=\"\"> -- ".translate('Select a current name', $st, 'sys')." -- 
         ".$bookName_options."
        </select>
       </td>
       <td>
        <select name=\"chapter\" id=\"chapter\" onchange=\"setChapter(this);\">
         <option value=\"\"> 
         ".$chapter_options."
        </select>
       </td>
      </tr>
     </table>
    </div>

    <div id=\"viewAnnotationsDiv\">
     <img style='position:absolute; top:0px; right:0px;' src='images/close.jpg'
      onclick=\"document.getElementById('viewAnnotationsDiv').style.visibility='hidden';
              if(lastNotation) {lastNotation.style.background='';}\"
      title='".translate('Close', $st, 'sys')."'>
     <div id=\"viewAnnotations\"></div>
    </div>

    <div id=\"viewDoc\"></div>

    
    <div id=\"viewTitle\">
     <span>".translate('Transcription below. To see pop-up notes click on an underlined word.', $st, 'sys')."</span>
	 <span class=explanationButtons>
		<button type='button' onclick='return popupAbbreviations()'>".translate('Languages', $st, 'sys')."</button>
		<button type='button' onclick='return popupSpecialLetters()'>".translate('Special Letters', $st, 'sys')."</button>
	 </span>
    </div>

    <div id=\"viewNotations\">".$detail."</div>

 <input name=\"devent\" id=\"devent\" type=\"hidden\">


<!--
<div style=\"position:absolute; top:740px;\" id=\"temp\">temp</div>
-->

 </div>
</body>
</form>
</html>
";

?>
