<?php

/******************************************************************************/
/* Ottoman Turkish project - view documents in multiple columns               */
/******************************************************************************/
/*  Developed by:  Steve Bagwell                                              */
/*                 Much of this code was borrowed from view.php and modified  */
/*                 Nov. 2016                                                  */
/******************************************************************************/

/**
  *
  * @param resource (msql_query results)
  * @param string $bibleKey
  * @param string $bookKey
  *
  * @return stdClass with the following attributes ...
  *  ->bibleTitleId, a string with the id of a sql row
  *  ->bibleTitleOptions, a string with html options for a select
  *  ->bookId, a string with the id of a sql row
  *  ->bookNameOptions, a string with html options for a select
  *  ->chapterOptions, a string with html options for a select
  *  ->notnData, a stdClass which, in turn, has the folloing attributes ...
  *     ->notations
  *     ->paragraphs
  *     ->quotes
  *     ->discussions
  *     ->recommendations
  *     ->detail
  *     ->chapterOptions
  *     ->jsAnnotations
  *     ->jsCoordinates
  *
  **/
function getViewData($bibleTitleSqlResults, $bibleKey, $bookKey) {
    $bibleTitle = getPostSetCookie($bibleKey, $_POST);
    $bookName = getPostSetCookie($bookKey, $_POST);

    if ( ! $bibleTitle and $_COOKIE[$bibleKey]) {
        $bibleTitle = $_COOKIE[$bibleKey];
        $bookName = $_COOKIE[$bookKey];
    }

    list($bibleTitle, $bibleTitleOptions) = getBibleTitleSelect(
        $bibleTitleSqlResults, $bibleTitle);
    $bibleId = getBibleId($bibleTitle);

    list($bookName, $bookNameOptions) = getBookNameSelect($bibleTitle, $bookName);
    $bookId = getBookId($bibleId, $bookName);

    $notnData = getNotationData($bibleId, $bookId);

    $chapterOptions = '';
    if ($notnData !== NULL) {
        $chapterOptions = $notnData->chapterOptions;
    }


    $viewData = new stdClass();
    $viewData->notnData = $notnData;
    $viewData->bibleTitleId = $bibleId;
    $viewData->bibleTitle = $bibleTitle;
    $viewData->bibleTitleOptions = $bibleTitleOptions;
    $viewData->bookId = $bookId;
    $viewData->bookNameOptions = $bookNameOptions;
    $viewData->chapterOptions = $chapterOptions;
    return $viewData;
}


function getViewPHPData($bibleTitleSqlResults, $bibleKey, $bookKey) {
    $bibleId = $_GET['bibleTitleId'];
    $bookId = $_GET['bookId'];

    if ( empty($bibleId) || empty($bookId)) {
        return getViewData($bibleTitleSqlResults, $bibleKey, $bookKey);
    }
    
    $bibleTitle = getBibleTitleFromId($bibleId);

    if (empty($bibleTitle)) {
        return getViewData($bibleTitleSqlResults, $bibleKey, $bookKey);
    }

    $notnData = getNotationData($bibleId, $bookId);

    $chapterOptions = '';
    if ($notnData !== NULL) {
        $chapterOptions = $notnData->chapterOptions;
    }

    $viewData = new stdClass();
    $viewData->notnData = $notnData;
    $viewData->bibleTitleId = $bibleId;
    $viewData->bibleTitle = $bibleTitle;
    $viewData->bibleTitleOptions = "";
    $viewData->bookId = $bookId;
    $viewData->bookNameOptions = "";
    $viewData->chapterOptions = "";
    return $viewData;
}




/**
  *
  * @param string $dataKey
  * @param array $post
  *
  * @return string 
  *
  **/
function getDataFromPost($dataKey, $post) {
    $result = '';
    if (isset($post[$dataKey])) {
        $result = $post[$dataKey];
    }
    return $result;
}

/**
  *
  * @param string $dataKey
  * @param array $post
  *
  * @return string
  *
  **/
function getPostSetCookie($dataKey, $post) {
    $result = '';
    if (isset($post[$dataKey])) {
        $result = $post[$dataKey];
        setcookie($dataKey, $result);
    }
    return $result;
}


/**
  *
  * @param string $isoCode
  *
  * @return resource: mysql_query results
  *
  **/
function getBibleTitleSqlResults($isoCode) {
    $query =
        'SELECT * FROM `bibleTitles` ' . 
        'WHERE `code` = "' . $isoCode . '" ' .
        'ORDER BY `title`';
       
    $result = mysql_query($query) or die (
        '<pre>Error 201611250928: ' . $query.mysql_error() . '</pre>');
    return $result;
}

/**
  *
  * @param string $bibleTitle
  *
  * @return resource, mysql_query results
  *
  **/
function getBibleFromDB($bibleTitle) {
    $query =
        'SELECT * FROM `bibleTitles` ' .
        'WHERE `title` = "' . mysql_real_escape_string($bibleTitle) .
        '" LIMIT 1';

    $result = mysql_query($query) or die(
        '<pre>Error 201611261008' . $query.mysql_error() . '</pre>');
    return $result;
}

/**
  *
  * @param string $bibleTitle
  *
  * @return string
  *
  **/
function getBibleId($bibleTitle) {
    $bibleId = '';
    if ($bibleTitle) {
        $bibleFromDB = getBibleFromDB($bibleTitle);
        $bibleRow = mysql_fetch_array($bibleFromDB);
        $bibleId = $bibleRow['id'];
    }
    return $bibleId;
}

/**
  *
  * @param resource, mysql_query results
  * @param string
  *
  * @return array with ...
  *   - a string with the bibleTitle (either the original one or the default one)
  *   - a string for html for all the select options for Bible Titles 
  *
  **/
function getBibleTitleSelect($bibleTitleSqlResults, $bibleTitle) {
    $bibleTitleOptions = '';
    mysql_data_seek($bibleTitleSqlResults, 0);

    while ($bibleRow = mysql_fetch_array($bibleTitleSqlResults)) {
        $selected = '';
        if($bibleRow['title'] == $bibleTitle) {
            $selected = 'selected';
        }

        if ( ! $bibleTitle and $bibleRow['displayDefault']==1) {
            $selected = 'selected';
            $bibleTitle = $bibleRow['title'];
            $_POST['bibleTitle'] = $bibleRow['title'];
        }

        $bibleTitleOptions .= '<option value="' .
                              $bibleRow['title'] . '" ' . $selected . '>' .
                              $bibleRow['title'] . '</option>';
    }
    mysql_data_seek($bibleTitleSqlResults, 0);

    return array($bibleTitle, $bibleTitleOptions);
}

/**
  *
  * @param string $bibleTitle
  *
  * @return resource, mysql_query results
  *
  **/
function getBookNameSqlResults($bibleTitle) {
    $bibleQuery =
        'SELECT * FROM `bibleTitles` ' .
        'WHERE `title` = "' . $bibleTitle . '"  LIMIT 1';
    $bibleResult = mysql_query($bibleQuery) or die (
        '<pre> Error 201611250930: ' . $bibleQuery.mysql_error() . '</pre>');
    $myrow=mysql_fetch_array($bibleResult);

    $bibleTitleId = $myrow['id'];

    $bookQuery =
         'SELECT * FROM `books` ' .
         'WHERE `bibleTitleId` = "' . $bibleTitleId . '" ' .
         'ORDER BY `displayOrder`, `name`';
    $result = mysql_query($bookQuery) or die (
        '<pre> Error 201611250932: ' . $bookQuery.mysql_error() . '</pre>');
    return $result;
}

/**
  *
  * @param string $bibleId
  * @param string $bookName
  *
  * @return resource, mysql_query results
  *
  **/
function getBookFromDB($bibleId, $bookName) {
    $query =  
        'SELECT * FROM `books` ' .
        'WHERE `name` = "' . mysql_real_escape_string($bookName) .
        '" AND `bibleTitleId`  = "' . $bibleId . 
        '" LIMIT 1';
    $result = mysql_query($query) or die(
        '<pre>Error 201611261000: ' . $query.mysql_error() . '</pre>');
    return $result;
}

/**
  *
  * @param string $bibleId
  * @param string $bookName
  *
  * @return string for an 'id' of a book
  *
  **/
function getBookId($bibleId, $bookName) {
    $bookId = '';
    if ($bibleId && $bookName) {
        $bookFromDB = getBookFromDB($bibleId, $bookName);
        $bookRow = mysql_fetch_array($bookFromDB);
        $bookId = $bookRow['id'];
    }
    return $bookId;
}

/**
  *
  * @param string $bibleTitle
  * @param string $bookName
  *
  * @return array with ...
  *  - a string of the bookName (either the original one or the default)
  *  - a string with the html select options for the books of that bible
  *
  **/
function getBookNameSelect($bibleTitle, $bookName) {
    $bookNameOptions = '';

    if ($bibleTitle) {
        $bookNameResults = getBookNameSqlResults($bibleTitle);

        while ($bookRow = mysql_fetch_array($bookNameResults)) {
            $selected = '';
            if ($bookRow['name'] == $bookName) {
                $selected = 'selected';
            }

            if ( ! $bookName and $bookRow['displayDefault']==1) {
                $selected = 'selected';
                $bookName = $bookRow['name'];
                $_POST['bookName'] = $bookRow['name'];
            }

            $bookNameOptions .= '<option value="' .
                                $bookRow['name'] . '" ' . $selected . '>' . 
                                $bookRow['name'] . '</option>';
        }
    }

    return array($bookName, $bookNameOptions);
}

/**
  *
  * @param string $bibleId
  * @param string $bookId
  *
  * @return resource, mysql_query results
  *
  **/
function getNotationSqlResults($bibleId, $bookId) {
    $query =
        'SELECT * FROM `notations` ' .
        'WHERE `bibleTitleId` = "' . $bibleId . '" '.
        'AND   `bookId`       = "' . $bookId . '" ' .
        'AND   `inactive`    != "Y"  ORDER BY `key`';

    $result = mysql_query($query) or die(
        '<pre> Error 201611260850' . $query.mysql_error() . '</pre>');
    return $result;
}

/**
  *
  * @param string $bibleId
  *
  * @return string
  *
  **/
function getBibleTitleFromId($bibleId) {
    $query =
        'SELECT `title` FROM `bibleTitles` ' .
        'WHERE `id` = ' . $bibleId . ' limit 1';

    $sqlResults = mysql_query($query) or die (
        '<pre>Error 201811220928: ' . $query.mysql_error() . '</pre>');


    $results = mysql_fetch_array($sqlResults);

    if (! empty($results)) {
        return $results[0];
    }
    return '';
}


/**
  *
  * @param string $bibleId
  * @param string $bookId
  *
  * @return stdClass, which has the following attributes
  *   ->notations
  *   ->paragraphs
  *   ->quotes
  *   ->discussions
  *   ->recommendations
  *   ->detail
  *   ->chapterOptions
  *   ->jsAnnotations
  *   ->jsCoordinates
  *
  **/
function getNotationData($bibleId, $bookId) {
    // if either param is falsey, return NULL
    if ( ! ($bibleId && $bookId)) {
        return NULL;
    }

    $notationSqlResults = getNotationSqlResults($bibleId, $bookId);
    $notnData = new stdClass();

    while($notnRow=mysql_fetch_array($notationSqlResults)) {
        if ($notnRow['coordinates'] /*and $notnRow['quote']*/) {
            $js_coordinates .= "\"p" . $notnRow['key'] . "\":\"" .
                               $notnRow['coordinates'] . "\",";
        }
       
        $notations       = unserialize($notnRow['notation']);
        $paragraphs      = unserialize($notnRow['paragraph']);
        $quotes          = unserialize($notnRow['quote']);
        $discussions     = unserialize($notnRow['discussion']);
        $recommendations = unserialize($notnRow['recommendation']);
       
        $dnotation = '';
        if (isset($notations[0]))
        {
            $i = 0;
            foreach ($notations as $notation) {
                 if ($paragraphs[$i]=='Y') {
                     if ($i==0) {
                         $dnotation = "<p />".$dnotation;
                     } else {
                         $notation = "<div class=\"paragraph\"></div>&nbsp;&nbsp;" .
                                      $notation;
                     }
                 }
       
                 list($book, $chapter, $verse) = explode(".", $notnRow['key']);
                 $chapter = ltrim($chapter, '0');
                 $verse = ltrim($verse, '0');
       
                 if ($chapter and $verse) {
                     if ($chapter != $sav_chapter) {
                         $dnotation .= "\r\n<div id=\"chapter_" . $chapter . 
                                       "\"  class=\"chapter\">" . $chapter .
                                       "</div>\r\n";
                         $chapterOptions .= "<option value=\"chapter_" .
                                            $chapter . "\">" . $chapter . "</option>";
                     }
                     if ($verse != $sav_verse) {
                         $dnotation .= "\r\n<nobr><div id=\"verse_p" . 
                                        $notnRow['key'] . "\" class=\"verse\">" .
                                        $verse."</div>\r\n</nobr>";
                     }
                     $sav_chapter = $chapter;
                     $sav_verse   = $verse;
                 }

                 $dnotation .= $notation." ";
                 $i++;
             }
        }

        // normalize data to make matching work better
        $dnotation = Normalizer::normalize($dnotation, Normalizer::FORM_KC);

        $s = array("\"","\n");
        $r = array("\\\"","<br \>");

        // quotes
        if (isset($quotes[0])) {
            $limit = 1;
            $ii = 0;
            foreach($quotes as $quote) {
                // underline single instance of quote field
                $quote = Normalizer::normalize($quote, Normalizer::FORM_KC);
                // ensure no leftover initial spaces keep this link from being included
                $quote = ltrim($quote);
       
                if ($quote != "") {
                    $reg_from = '/' . preg_quote($quote, '/') . '/';
                    $dnotation = preg_replace($reg_from, 
                        "<div id=\"quote" . $notnRow['key'] . "_" . $ii . 
                        "\" class=\"quote\" onclick=\"setAnnotations('" .
                        $notnRow['key'] . "_" . $ii .
                        "')\"><a href=\"#\">" .
                        $quote."</a></div>", 
                        $dnotation, 1);
                    $quotes[$quote] = $notnRow['key'] . "_" . $ii;

                    $js_annotations .= "\"" . $notnRow['key'] . "_" . $ii . "\":\"" .
                        str_replace($s, $r, $quote) . "^" .
                        str_replace($s, $r, $discussions[$ii]) . "^" .
                        str_replace($s, $r, $recommendations[$ii]) . "\",";
                }

                $ii++;
            }
        }

        $specialChrs = array("î","ʿ");
       
        # add a narrow space between an f and an i that has a circumflex accent
        foreach ($specialChrs as $nextChr) {
            $dnotation = str_replace('f' . $nextChr,
                                     '<nobr class="nudge_right">f</nobr>' . 
                                         $nextChr,
                                     $dnotation);
        }
              
        $detail .= "\r\n<span id=\"notation_p" . $notnRow['key'] .
                   "\" class=\"notation\">" . $dnotation . "</span>\r\n";
    }

    $notnData->notations = $notations;       
    $notnData->paragraphs = $paragraphs;
    $notnData->quotes = $quotes;
    $notnData->discussions = $discussions;
    $notnData->recommendations = $recommendations;
    $notnData->detail = $detail;
    $notnData->chapterOptions = $chapterOptions;
    $notnData->jsAnnotations = rtrim($js_annotations, ",");
    $notnData->jsCoordinates = rtrim($js_coordinates, ",");

    return $notnData;
}
    
