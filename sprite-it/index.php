<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sprite IT</title>
    <base href="http://localhost/playground/sprite-it/">
    <link href="main.css" rel="stylesheet" />
</head>
<body>
<?php
    if(isset($_POST['submit'])) {

        $arrFiles = array_key_exists('sprite-files', $_FILES) ? $_FILES['sprite-files'] : false;
        $blnNoImageResize = array_key_exists('sprite-img-no-resize', $_POST);
        $intImageSize = !$blnNoImageResize && array_key_exists('sprite-img-size', $_POST) ? (int) $_POST['sprite-img-size'] : false;
        $intSpacing = array_key_exists('sprite-img-spacing', $_POST) ? (int) $_POST['sprite-img-spacing'] : false;
        $intFileWidth = array_key_exists('sprite-width', $_POST) ? (int) $_POST['sprite-width'] : false;
        $blnRetinaSupport = array_key_exists('sprite-retina', $_POST);
        $strSpriteName = array_key_exists('sprite-name', $_POST) ? $_POST['sprite-name'] : false;
        $strSpriteImgPrefix = array_key_exists('sprite-img-prefix', $_POST) ? $_POST['sprite-img-prefix'] : '';


        if(
            is_array($arrFiles) 
            && (is_int($intImageSize) || $blnNoImageResize)
            && is_int($intSpacing) 
            && is_int($intFileWidth) 
            && is_bool($blnRetinaSupport)
            && is_string($strSpriteName)
        ) {
            //echo '<pre>'; var_dump($arrFiles); echo '</pre>';
            $arrSpriteImages = array();
            foreach($arrFiles['name'] as $i => $strFileName) {
                if(strlen($strFileName) > 1) {
                    list($strImgName) = explode('.', $strFileName);
                    if($strImgName == '') { continue; }
                    $arrSpriteImages[$strImgName] = $arrFiles['tmp_name'][$i];
                }
            }
            require_once('includes/sprite-it.php');
            $strSpriteOutput = SpriteIt($strSpriteName, $arrSpriteImages, $intImageSize, $intSpacing, $intFileWidth, $blnRetinaSupport, $strSpriteImgPrefix);
            if(!$strSpriteOutput) {
                echo 'An error occurred when generating sprite!<br>' . PHP_EOL;
            }
            else {
                echo '<a href="' . $strSpriteOutput . '" target="_blank">View your sprite</a><br>' . PHP_EOL;
                //echo '<img src="' . $strSpriteOutput . '">';
            }
        }
    }
?>  
    <h1>Sprite it!</h1>
    <form action="" method="POST" enctype="multipart/form-data">
        <label for="sprite-files">Files to make sprite from:</label>
        <input id="sprite-files" name="sprite-files[]" type="file" webkitdirectory mozdirectory directory multiple required>

        <label for="sprite-img-size">Size of images in sprite (px):</label>
        <input id="sprite-img-size" name="sprite-img-size" type="number" min="10" max="500" step="1" value="20">

        <p>or</p>

        <input id="sprite-img-no-resize" name="sprite-img-no-resize" type="checkbox">
        <label for="sprite-img-no-resize">Don't resize images</label>

        <label for="sprite-img-spacing">Spacing between images in sprite (px):</label>
        <input id="sprite-img-spacing" name="sprite-img-spacing" type="number" min="0" max="50" step="1" value="10" required>

        <label for="sprite-width">Max width of sprite (px):</label>
        <input id="sprite-width" name="sprite-width" type="number" min="100" max="1000" step="50" value="500" required>

        <input id="sprite-retina" name="sprite-retina" type="checkbox">
        <label for="sprite-retina">Add support for retina displays?</label>

        <label for="sprite-name">CSS class name for sprite:</label>
        <input id="sprite-name" name="sprite-name" type="text" value="tip-icon" required>

        <label for="sprite-img-prefix">Prefix for images in sprite:</label>
        <input id="sprite-img-prefix" name="sprite-img-prefix" type="text" placeholder="(optional)" value="tip-icon-">

        <input type="submit" name="submit" value="Sprite it!">
    </form>
    <script src="main.js"></script>
</body>