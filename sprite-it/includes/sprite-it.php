<?php
define('DEBUGGING', ((int)isset($_GET['debug'])));
define('PRINT_RESULT', 0);

function getNewDimensions($arrSize, $blnSizeUp = true) {

    if($arrSize[0] >= $arrSize[1]) {
        if(DEBUGGING) { echo 'Longest side: width<br>' . PHP_EOL; }
        $refLongestSide = &$arrSize[0];
        $refShortestSide = &$arrSize[1];
    }
    else if($arrSize[0] < $arrSize[1]) {
        if(DEBUGGING) { echo 'Longest side: height<br>' . PHP_EOL; }
        $refLongestSide = &$arrSize[1];
        $refShortestSide = &$arrSize[0];
    }

    if($refLongestSide > SPRITE_IMG_MAX_SIZE) {
        $fltProportion = $refLongestSide / $refShortestSide;
        if(DEBUGGING) { echo 'Proportion: ' . $fltProportion . '<br>' . PHP_EOL; }
        $refLongestSide = SPRITE_IMG_MAX_SIZE;
        $refShortestSide = (int) round($refLongestSide / $fltProportion);
    }
    else if($blnSizeUp) {
        $fltProportion = $refLongestSide / $refShortestSide;
        $refLongestSide = SPRITE_IMG_MAX_SIZE;
        $refShortestSide = (int) round($refLongestSide / $fltProportion);
    }
    return $arrSize;

}

function SpriteIt($strSpriteName, $arrSpriteImages, $intImageSize, $intSpacing = 10, $intFileWidth = 500, $blnRetinaSupport = false, $strSpriteImgPrefix = '') {

    if(DEBUGGING) { echo '<pre>'; }

    define('SPRITE_NAME', $strSpriteName);
    define('SPRITE_IMG_PREFIX', $strSpriteImgPrefix);
    define('RETINA_SUPPORT', (int) $blnRetinaSupport);
    define('SPRITE_IMG_SPACING', (int) $intSpacing);
    define('SPRITE_IMG_MAX_SIZE', (int) $intImageSize);
    define('SPRITE_WIDTH', (int) $intFileWidth);
    define('SPRITE_OUTPUT', 'sprite-output/');
    define('SPRITE_QUALITY', 9);

    $objImgSprite = imagecreate(SPRITE_WIDTH, 9999);
    $arrInsertPos = array('x' => 0, 'y' => 0);

    $objTransparentColor = imagecolorallocate($objImgSprite, 255, 255, 255);
    imagecolortransparent($objImgSprite, $objTransparentColor);

    $intCurrentWidth = 0;
    $intRowMaxHeight = 0;
    $arrPositions = array();
    foreach($arrSpriteImages as $strName => $strFile) {
        if($strFile === '.' || $strFile === '..') { continue; }
        if(DEBUGGING) { echo 'File: ' . $strFile . ' (' . $strName . ')<br>' . PHP_EOL; }
        switch(exif_imagetype($strFile)) {
            case IMAGETYPE_JPEG:
                $objImgTemp = imagecreatefromjpeg($strFile);
                break;
            case IMAGETYPE_PNG: 
                $objImgTemp = imagecreatefrompng($strFile);
                break;
            default:
                if(DEBUGGING) { echo 'Unknow file-type: ' . $strFile . '<br></pre>' . PHP_EOL; }
                return false;
                break;
        }
        if(strlen(SPRITE_IMG_PREFIX) > 0) {
            $strName = SPRITE_IMG_PREFIX . $strName;
        }
        if(is_numeric($strName) || is_numeric($strName[0])) {
            $strName = '_' . $strName;
        }
        
        $arrSize = getimagesize($strFile);

        if(DEBUGGING) { var_dump($arrSize); var_dump($arrInsertPos); }

        $arrNewSize = (SPRITE_IMG_MAX_SIZE > 0) ? getNewDimensions($arrSize) : $arrSize;

        if(DEBUGGING) { echo 'New size: ' . PHP_EOL; var_dump($arrNewSize); }

        $intNewWidth = $intCurrentWidth + $arrNewSize[0];
        if(RETINA_SUPPORT) {
            $arrNewSizeRetina = array($arrNewSize[0] * 2, $arrNewSize[1] * 2);
            $intNewWidth += SPRITE_IMG_SPACING + $arrNewSizeRetina[1];
        }

        if(DEBUGGING) { echo 'New width: ' . $intNewWidth . '<br>' . PHP_EOL; }

        if($intNewWidth > SPRITE_WIDTH) {

            if(DEBUGGING) { echo 'Start new row<br>' . PHP_EOL; }

            $arrInsertPos['x'] = 0;
            $arrInsertPos['y'] += SPRITE_IMG_SPACING + $intRowMaxHeight;
            $intCurrentWidth = 0;
            $intRowMaxHeight = 0;
        }

        if(RETINA_SUPPORT && $arrNewSizeRetina[1] > $intRowMaxHeight) {
            $intRowMaxHeight = $arrNewSizeRetina[1];
        }else if($arrNewSize[1] > $intRowMaxHeight) {
            $intRowMaxHeight = $arrNewSize[1];
        }

        if(SPRITE_IMG_MAX_SIZE > 0) {
            imagecopyresampled($objImgSprite, $objImgTemp, $arrInsertPos['x'], $arrInsertPos['y'], 0, 0, $arrNewSize[0], $arrNewSize[1],  $arrSize[0], $arrSize[1]);
        }
        else {
            imagecopy($objImgSprite, $objImgTemp, $arrInsertPos['x'], $arrInsertPos['y'], 0, 0, $arrSize[0], $arrSize[1]);
        }

        $arrPositions[] = array(
            'strImageName' => $strName
            , 'intImageX' => $arrInsertPos['x']
            , 'intImageY' => $arrInsertPos['y']
            , 'intImageWidth' => $arrNewSize[0]
            , 'intImageHeight' => $arrNewSize[1]
        );

        $intCurrentWidth += SPRITE_IMG_SPACING + $arrNewSize[0];
        $arrInsertPos['x'] += SPRITE_IMG_SPACING + $arrNewSize[0];

        if(RETINA_SUPPORT) {
            imagecopyresampled($objImgSprite, $objImgTemp, $arrInsertPos['x'], $arrInsertPos['y'], 0, 0, $arrNewSizeRetina[0], $arrNewSizeRetina[1],  $arrSize[0], $arrSize[1]);
            
            $arrPositions[] = array(
                'strImageName' => $strName . '_X2'
                , 'intImageX' => $arrInsertPos['x']
                , 'intImageY' => $arrInsertPos['y']
                , 'intImageWidth' => $arrNewSizeRetina[0]
                , 'intImageHeight' => $arrNewSizeRetina[1]
            );

            $intCurrentWidth += SPRITE_IMG_SPACING + $arrNewSizeRetina[0];
            $arrInsertPos['x'] += SPRITE_IMG_SPACING + $arrNewSizeRetina[0];
        }

        imagedestroy($objImgTemp);
    }

    if(DEBUGGING) { echo 'Positions: '; var_dump($arrPositions); }

    $intTotalHeight = $arrInsertPos['y'] + $intRowMaxHeight;

    if(DEBUGGING) { echo 'Total height: ' . $intTotalHeight . '<br>' . PHP_EOL; }
    $objImgSpriteResized = imagecreate(SPRITE_WIDTH, $intTotalHeight);
    imagecopy($objImgSpriteResized, $objImgSprite, 0, 0, 0, 0, SPRITE_WIDTH, $intTotalHeight);

    if(PRINT_RESULT) {
        if(DEBUGGING) { echo '</pre>'; }
        header("Content-type: image/png");
        imagepng($objImgSpriteResized);
        imagedestroy($objImgSprite);
        imagedestroy($objImgSpriteResized);
    }

    $strTime = date('d-m-Y_H-i-s', time());
    $strOutputFolder = SPRITE_OUTPUT . $strTime;
    if(!mkdir($strOutputFolder)) {
        echo 'Could not create output folder: ' . $strOutputFolder . '<br>' . PHP_EOL;
        return false;
    }

    imagealphablending($objImgSpriteResized, false);
    imagesavealpha($objImgSpriteResized, true);
    $strFileName = $strOutputFolder . '/sprite.png';
    $blnSaved = imagepng($objImgSpriteResized, $strFileName, SPRITE_QUALITY);

    imagedestroy($objImgSprite);
    imagedestroy($objImgSpriteResized);

    if(!$blnSaved) {
        echo 'Could not save sprite: ' . $strFileName . '<br>' . PHP_EOL;
        return false;
    }
    
    if(!file_exists('includes/sprite-output.tpl.html') || !file_exists('includes/sprite-output-image.tpl.html')) {
        echo 'Could not find template files!<br>' . PHP_EOL;
        return false;
    }
    $strSpriteOutputTpl = file_get_contents('includes/sprite-output.tpl.html');
    $strSpriteOutputImgTpl = file_get_contents('includes/sprite-output-image.tpl.html');
    
    $strSpriteImagesOverlay = '';
    foreach($arrPositions as $i => $arrData) {
        $strOutput = $strSpriteOutputImgTpl;
        foreach($arrData as $strKey => $strValue) {
            $strOutput = str_replace('[@' . $strKey . ']', $strValue, $strOutput);
        }
        $strSpriteImagesOverlay .= $strOutput;
    }

    $arrPlaceholders = array(
        'intSpriteWidth' => SPRITE_WIDTH
        , 'intSpriteHeight' => $intTotalHeight
        , 'strSpriteName' => SPRITE_NAME
        , 'strSpriteImages' => $strSpriteImagesOverlay
    );
    $strSpriteOutput = $strSpriteOutputTpl;
    foreach($arrPlaceholders as $strKey => $strValue) {
        $strSpriteOutput = str_replace('[@' . $strKey . ']', $strValue, $strSpriteOutput);
    }

    $strHtmlFile = $strOutputFolder . '/index.html';
    // TODO: Find out why fopen won't create a new file!
    $objHandle = fopen($strHtmlFile, 'x');
    if(!$objHandle) {
        echo 'Could not open/create file: ' . $strHtmlFile . '<br>' . PHP_EOL;
        return false;
    }
    if(!fwrite($objHandle, $strSpriteOutput)) {
        echo 'Could not write HTML file: ' . $strHtmlFile . '<br>' . PHP_EOL;
        return false;
    }
    fclose($objHandle);

    if(DEBUGGING) { echo '</pre>'; }

    return $strHtmlFile;

}