<?php 
/*
    SIU : Safe Image Upload
    Image redrawing and uploading class

    @author     Kim Dehmlow <kim@bettercollective.com>
    @version    09:00   06-11-2012
    Updated     11:47   30-10-2012

//////////////////////////////////////////////////////////////////////////////////////
//
// Defaults:
// Saves as .jpg in quality 75  - use $objSIU->SaveAsPNG($intQuality) to change it
// Maximum filesize 4MB (4194304 bytes)
// 
//////////////////////////////////////////////////////////////////////////////////////
*/

class SIU {
    private $blnSaveAsPNG = false;
    private $blnAcceptBase64 = false;
    private $intImageQuality = 75;
    private $arrDimensions = array(0, 0);
    private $blnForceResize = false;
    private $intMaxFilesize;
    private $strUploadPath;
    private $strTempPath;    
    private $arrApprovedTypes;
    
    /*
        Construct the SIU class and set defaults
        @param strPath (string) A complete path to and existing destination folder on the server
        @param arrTypes (array) Optional, An array containg the image type as key and file extension as value
        @param intSize (int) Optional, The maximum allowed filesize in bytes
    */
    function __construct($strPath, $arrTypes = array(), $intFilesize = 0) {
        $this->intMaxFilesize = ($intFilesize > 0 ? $intFilesize : 4194304);
        $this->strUploadPath = $strPath;
        $this->strTempPath = $strPath . 'temp/';
        
        if(count($arrTypes) > 0) {
            $this->arrApprovedTypes = $arrTypes;
        } else {
            $this->arrApprovedTypes = array(
                'image/png' => 'png', 
                'image/jpeg' => 'jpg', 
                'image/jpg' => 'jpg'
            );
        }
    }
    
    /*
        SIUs main function which combines all other functions into one quick call
        @return arrData Array containing: (string)'filename', (int)'status', (bool)'success' and (string)'error'
    */
    public function SafeSave() {
        $arrData = array(
            'filename' => '',
            'status' => 0,
            'success' => false,
            'error' => ''
        );
        
        $arrImageData = $this->CheckImageData();
        if(isset($arrImageData['error'])) {
            $arrData['status'] = 6;
            $arrData['error'] = $arrImageData['error'];
            return $arrData;
        } else {
            $objImage = $arrImageData['data'];
            $strType = $arrImageData['type'];
            $strExt = $arrImageData['ext'];
        }

        if($this->CheckImageSize($arrImageData)) {
            $strName = $this->SaveTemporaryImage($objImage, $strType, $strExt);
            if($strName) {
                $objNewImage = $this->RedrawImage($strName, $strExt);
                if($objNewImage) {
                    $strFilename = $this->SaveNewImage($objNewImage, $strName);
                    if($strFilename) {
                        if($this->DeleteTemporaryImage($strName, $strExt)) {
                            $arrData['filename'] = $strFilename;
                            $arrData['success'] = true;
                        } else {
                            $arrData['status'] = 1;
                            $arrData['error'] = 'The temporary image could not be removed';
                        }
                    } else {
                        $arrData['status'] = 2;
                        $arrData['error'] = 'The new image could not be saved';
                    }
                } else {
                    $arrData['status'] = 3;
                    $arrData['error'] = 'Image processing failed';
                }
            } else {
                $arrData['status'] = 4;
                $arrData['error'] = 'The temporary image could not be saved';
            }
        } else {
            $arrData['status'] = 5;
            $arrData['error'] = 'Image is too large';
        }
        return $arrData;
    }
    
    /*
        Checks if the recieved image is a valid filetype
        @return arrReturn Success: Array[(object)'data', (string)'type', (string)'ext'] - Error: Array[(string)'error']
    */
    private function CheckImageData() {
        if(isset($_REQUEST['base64']) && $this->blnAcceptBase64) {
            $strBase64 = $_REQUEST['base64'];
            $strBase64 = explode(",", $strBase64);
            $strBase64 = $strBase64[1];
            if(base64_decode($strBase64)) {
                $strType = 'base64';
                $objImage = base64_decode($strBase64);
                $strTempExt = 'jpg';
            } else {
                $strError = 'Error: Not valid base64 image';
            }
        } else if(isset($_FILES['image'])) {
            if(isset($this->arrApprovedTypes[$_FILES['image']['type']])) {
                $strType = 'file';
                $objImage = $_FILES['image'];
                $strTempExt = $this->arrApprovedTypes[$objImage['type']];
            } else {
                $strError = 'Error: Filetype not allowed';
            }
        } else {
            $strError = 'Error: Recieved data not recognised';
        }
        if(isset($strError)) {
            $arrReturn = array(
                'error' => $strError
            );
        } else {
            $arrReturn = array(
                'data' => $objImage,
                'type' => $strType,
                'ext' => $strTempExt
            );
        }
        return $arrReturn;
    }
    
    /*
        Checks if the recieved image is too large
        @param arrData (array) Data on the image to check ('data', 'type')
        @return blnSizeAccept Success: (bool)true - Error: (bool)false
    */    
    public function CheckImageSize($arrData) {
        if($arrData['type'] == 'base64') {
            $intSize = strlen($arrData['data']);
        } else if($arrData['type'] == 'file') {
            $intSize = $arrData['data']['size'];
        } else {
            return false;
        }
        $blnSizeAccept = ($intSize < $this->intMaxFilesize ? true : false);
        return $blnSizeAccept;
    }
    
    /*
        Checks if the recieved image is too large
        @param objImage (object) Image that was recieved
        @param strType (string) The type of the recieved image
        @param strTempExt (string) The file extension of the recieved image
        @return strName Success: (string) Clean filename without extension - Error: (bool) false
    */    
    public function SaveTemporaryImage($objImage, $strType, $strTempExt) {
        $intTimestamp = time();
        $intRandom = rand(5, 15);
        $strName = $strType . '-' . $intTimestamp . '-' . $intRandom;
        $strTempFile = $this->FormatTempPath($strName, $strTempExt);
        if($strType == 'base64') {
            file_put_contents($strTempFile, $objImage);
        } else {
            move_uploaded_file($objImage['tmp_name'], $strTempFile);
        }
        if(file_exists($strTempFile)) {
            return $strName;
        } else {
            return false;
        }
    }
    
    /*
        Processes the temporary image: Redraw every pixel and resize
        @param strName (string) Clean filename that was saved in the temporary folder
        @param strTempExt (string) The file extension of the image
        @return objFinalImage Success: (object) A gd imageobject - Error: (bool) false
    */
    public function RedrawImage($strName, $strTempExt) {
        $strTempFile = $this->FormatTempPath($strName, $strTempExt);
        list($intTempWidth, $intTempHeight) = getimagesize($strTempFile);
        if($strTempExt == 'png') {
            $objImage = imagecreatefrompng($strTempFile);
        } else if($strTempExt == 'jpg') {
            $objImage = imagecreatefromjpeg($strTempFile);
        } else if($strTempExt == 'gif') {
            $objImage = imagecreatefromgif($strTempFile);
        } else if($strTempExt == 'gd') {
            $objImage = imagecreatefromgd($strTempFile);
        } else {
            return false;
        }
        
        $objNewImage = imagecreatetruecolor($intTempWidth+10, $intTempHeight+10);
        imagealphablending($objNewImage, false);
        imagesavealpha($objNewImage, true);
        for($x = 0; $x < $intTempWidth; $x += 1) {
            for($y = 0; $y < $intTempHeight; $y += 1) {
                $intColorIndex = imagecolorat($objImage, $x, $y);
                $objTempColor = imagecreatetruecolor(1, 1);
                $arrColors = imagecolorsforindex($objImage, $intColorIndex);                        
                $intColor = imagecolorallocatealpha($objTempColor, $arrColors['red'], $arrColors['green'], $arrColors['blue'], $arrColors['alpha']);
                imagesetpixel($objNewImage, $x+10, $y+10, $intColor);
            }
        }
        
        $arrNewSize = $this->CalculateSize($intTempWidth, $intTempHeight);
        $objFinalImage = imagecreatetruecolor($arrNewSize['width'], $arrNewSize['height']);
        imagecopyresampled($objFinalImage, $objNewImage, 0, 0, 10, 10, $arrNewSize['width'], $arrNewSize['height'], $intTempWidth, $intTempHeight);
        
        return $objFinalImage;
    }
    
    /*
        Saves the processed image to the upload path
        @param objNewImage (object) A gd imageobject
        @param strName (string) Clean filename (no extension)
        @return strFilename Success: (string) Filename with extension - Error: (bool) false
    */
    public function SaveNewImage($objNewImage, $strName) {
        if($this->blnSaveAsPNG === true) {
            $strExt = '.png';
            $strFilePath = $this->FormatUploadPath($strName, $strExt);
            imagepng($objNewImage, $strFilePath, $this->intImageQuality);
        } else {
            $strExt = '.jpg';
            $strFilePath = $this->FormatUploadPath($strName, $strExt);
            imagejpeg($objNewImage, $strFilePath, $this->intImageQuality);
        }
        if(file_exists($strFilePath)) {
            $strFilename = $strName . $strExt;
            return $strFilename;
        } else {
            return false;
        }
    }
    
    /*
        Sets the maximum dimensions of the image
        @param arrSize (array) Containing 'width' and 'height' as key
        @return Success: (bool) true - Error: (bool) false
    */
    public function SetMaxDimensions($arrSize) {
        if(isset($arrSize['width']) && isset($arrSize['height'])) {
            $this->arrDimensions = array(
                'width' => (int)$arrSize['width'],
                'height' => (int)$arrSize['height']
            );
            return true;
        } else {
            return false;
        }
    }
    
    /*
        Calculates the width and height to fit the maximum dimensions if they are set
        @param intTempWidth (int) Width of the temporary image
        @param intTempHeight (int) Height of the temporary image
        @return arrReturn Array[(int)'width', (int)'height']
    */
    private function CalculateSize($intTempWidth, $intTempHeight) {
        $intConfigWidth = $this->arrDimensions['width'];
        $intConfigHeight = $this->arrDimensions['height'];
        $intRatio = $intTempWidth / $intTempHeight;
        
        if($intConfigWidth > 0 || $intConfigHeight > 0) {
            if($this->blnForceResize) {
                if($intConfigWidth > 0 && $intConfigHeight === 0) {
                    $arrReturn['width'] = $intConfigWidth;
                    $arrReturn['height'] = round($intConfigWidth / $intRatio);
                } else if($intConfigWidth === 0 && $intConfigHeight > 0) {
                    $arrReturn['width'] = round($intConfigHeight * $intRatio);
                    $arrReturn['height'] = $intConfigHeight;
                } else if($intConfigWidth > 0 && $intConfigHeight > 0) {
                    $arrReturn['width'] = $intConfigWidth;
                    $arrReturn['height'] = $intConfigHeight;
                } else {
                    return false;
                }
                return $arrReturn;
            } else if($intConfigWidth > 0 && $intTempWidth > $intConfigWidth) {
                $arrReturn['width'] = $intConfigWidth;
                $arrReturn['height'] = round($intConfigWidth / $intRatio);
            } else if($intConfigHeight > 0 && $intTempHeight > $intConfigHeight) {
                $arrReturn['width'] = round($intConfigHeight * $intRatio);
                $arrReturn['height'] = $intConfigHeight;
            } else {
                $arrReturn['width'] = $intTempWidth;
                $arrReturn['height'] = $intTempHeight;
            }
        } else {
            $arrReturn['width'] = $intTempWidth;
            $arrReturn['height'] = $intTempHeight;
        }
        return $arrReturn;
    }
    
    /*
        Deletes the temporary image file
        @param strName (string) Clean filename that was saved in the temporary folder
        @param strExt (string) The file extension of the image
        @return Success: (bool) true - Error: (bool) false
    */
    private function DeleteTemporaryImage($strName, $strExt) {
        $strFilepath = $this->FormatTempPath($strName, $strExt);
        unlink($strFilepath);
        if(!file_exists($strFilepath)) {
            return true;
        } else {
            return false;
        }
    }
    
    /*
        Get the complete filepath for the temporary image
        @param strName (string) Clean filename (no extension)
        @param strExt (string) The file extension of the image
        @return strFilepath (string)Path to the image on the server
    */
    public function FormatTempPath($strName, $strExt) {
        $strFilepath = $this->strTempPath . $strName . '.' . $strExt;
        return $strFilepath;
    }
    
    /*
        Get the complete filepath for the final image
        @param strName (string) Clean filename (no extension)
        @param strExt (string) The file extension of the image
        @return strFilepath (string)Path to the image on the server
    */
    public function FormatUploadPath($strName, $strExt) {
        $strFilepath = $this->strUploadPath . $strName . '.' . $strExt;
        return $strFilepath;
    }
    
    /*
        Change the temporary folder
        @param strPath (string) Complete path to an existing folder
        @return false
    */
    public function SetTemporaryPath($strPath) {
        $this->strTempPath = $strPath;
        return false;
    }
    
    /*
        Set the final image format to PNG
        @param intQuality Optional: (int) Compression valid values: 0 - 9
        @return false
    */
    public function SaveAsPNG($intQuality = 7) {
        $this->blnSaveAsPNG = true;
        $this->intImageQuality = $intQuality;
        return false;
    }
    
    /*
        Set the final image format to JPG
        @param intQuality Optional: (int) Compression valid values: 0 - 100
        @return false
    */
    public function SaveAsJPG($intQuality = 75) {
        $this->blnSaveAsPNG = false;
        $this->intImageQuality = $intQuality;
        return false;
    }
    
    /*
        Set image processing to resie every image to the maximum dimensions (will ignore ratio if both width and height are set!)
        @param blnForce (bool)
        @return false
    */
    public function SetForceResize($blnForce) {
        $this->blnForceResize = $blnForce;
        return false;
    }
    
    /*
        Set to allow/disallow base64 upload
        @param blnAccept (bool)
        @return false
    */
    public function SetBase64($blnAccept) {
        $this->blnAcceptBase64 = $blnAccept;
        return false;
    }
}

?>