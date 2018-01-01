<?php

/******************************************************************************/
/* Ottoman Turkish project - script tag to view documents in multiple columns */
/******************************************************************************/
/*  Developed by:  Steve Bagwell                                              */
/******************************************************************************/

function getLangAbbrevs() {
  $langAbbrevs = array (
    'Akad.', 
    'Alm.', 
    'Ar.', 
    'Aram.', 
    'Bulg.', 
    'Fa.', 
    'Fra.', 
    'İbr.',  // Ruth 1:6, Allah (1665)
    'İng.',
    'İta.',
    'Lat.',
    'Mac.', 
    'Macar.', 
    'Moğ.',  // Mog  Apokalipsis 9:9, Ali 1665
    'Osm.',
    'Rum.', 
    'Skt.', 
    'Soğ.',
    'Soğd.',
    'E.Tü.',  // Ruth 1:1, avrat (1665)
    'K.Tü.',
    'Kz.Tü.',  // Ruth 2:21 bay (1665)
    'T.Tü.',
    'U.Tü.',  // Ruth 2:14 etmek (1665)
    'Y.Tü.',  
    'Tü.',  // Ruth 1:1, avrat (1665)
    'E.Yun.', 
    'Yun.'
  );

  return $langAbbrevs;
}

function getAbbrevs() { // italicized
  $abbrevs = array (
      'bk.', // Tevrat 1 1:11 note on tenbit
      'karş.', // Tevrat 1 32:18 note on peskes
      'mec.',    // Tevrat 1 24:48 note on tebarek
      '(mec)',  // Tevrat 1 2:25 not on hicab
  );

  return $abbrevs;
}


function getJsArray($jsName, $items) {
  $jsItems = "  " . $jsName . " = [
   ";

  foreach ($items as $nextItem) {
    $jsItems .= "'" . $nextItem . "',
   ";
  }

  $jsItems .= "
  ]
";

  return $jsItems;

}


function getSetAnnotationsText(
    $styleTop="'2px'",
    $styleLeft="'2px'"
  ) {


  $jsAbbrevs = getJsArray('abbrevs', getAbbrevs());
  $jsLangAbbrevs = getJsArray('langAbbrevs', getLangAbbrevs());


  $jsSetAnnotations = "

  lastNotation = '';

  // Abbreviations that get italicized
  " . $jsAbbrevs . "

  // Language abbreviations that need to get a special style
  // Special characters are hard to deal with in vim, so separate them out.
  " . $jsLangAbbrevs . "
 
   // Wrap a string (e.g. mec.) in a span with a class to make italicized
  // function replaceLangAbbrLite(origString, replString) {
  function replaceAbbrev(origString, replString) {
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


  function setAnnotations(key) {
      var a = annotations[key].split('^');
      var annDef = a[1];

      // Give the abbreviations and language abbreviations a different style via wrapping in a span
      for (index = 0; index < abbrevs.length; index++) {
          annDef = replaceAbbrev(annDef, abbrevs[index]);
      }


      for (index = 0; index < langAbbrevs.length; index++) {
          annDef = replaceLangAbbrev(annDef, langAbbrevs[index]);
      }

      document.getElementById(\"viewAnnotations\").innerHTML = '<b>' + a[0] + '</b> : ' + annDef;

      var links = a[2].split('\t');

      for (i =0; i < links.length; i++)
      {
          if (links[i].search('http')) {
              document.getElementById(\"viewAnnotations\").innerHTML += '<p />' + links[i];
          } else {
              if (i == 0) {
                  document.getElementById(\"viewAnnotations\").innerHTML += '<p />';
              } else {
                  document.getElementById(\"viewAnnotations\").innerHTML += '<br>';
              }
              document.getElementById(\"viewAnnotations\").innerHTML += '<a href=\"' + links[i]+ '\" target=\"_blank\">' + links[i]+ '</a>';
          }
      }

      document.getElementById(\"viewAnnotationsDiv\").style.visibility='visible';
      document.getElementById(\"viewAnnotationsDiv\").style.top = " . $styleTop . ";
      document.getElementById(\"viewAnnotationsDiv\").style.left = " . $styleLeft . ";
  }

  ";

  return $jsSetAnnotations;

}

$jsScrollFunctions = "

function scroll2Chapter(chapterSelect) {
    id = chapterSelect.value; 
    element = document.getElementById(id);
    element.scrollIntoView(true); // true => align with top
}

";


$showColumn = "
function showColumn(colNum, iso, st, addLabel) {
  element = document.getElementById('column' + colNum);
  element.innerHTML = \"<iframe src='viewColumns.php?iso=\" + iso + \"&st=\" + st + \"' width=100%; height=100%;></iframe>\";

  if (colNum == 3) {
    col4 = document.getElementById('column4');
    col4.innerHTML = \"<input type='button' id='show_column4' value='\" + addLabel + \"' " . 
                           "onclick='showColumn(4, &#39;\" + iso + \"&#39;)'>\";
  }

}
";


$jsAnnotationsOff = "
  var bodyClickCount = 0;

  function annotationsOff()
  {
   bodyClickCount += 1;

  //  The click on a link is counted as the first click. So, ignore that one.
   if (bodyClickCount>1) {
     document.getElementById('viewAnnotationsDiv').style.visibility='hidden';
     bodyClickCount = 0;
   }
  }

";
