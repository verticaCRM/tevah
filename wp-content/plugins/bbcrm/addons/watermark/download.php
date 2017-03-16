<?php
//watermark php 
require_once(dirname(__FILE__).'/WatermarkerTCPDF.php');

parse_str(base64_decode($_GET["dl"]),$filevars);
$uploadedBy=$filevars['uploadedBy'];
$fileName=$filevars['fileName'];
$path=$_SERVER['DOCUMENT_ROOT'].'crm/uploads/media/'.$uploadedBy.'/'.$fileName;
$firstName=$filevars['firstName'];
$lastName=$filevars['lastName'];
$company=$filevars['company'];
$date = date('m/d/Y h:i:s a');
$mimetype = mime_content_type($path);

$text = "This document provided to ".$firstName." ".$lastName." by ".$company." on ".$date." with IP address ".$_SERVER['REMOTE_ADDR'];

switch ($mimetype) {

	case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
	case "application/msword":
		exec("unoconv -f pdf ".$path);
		$parts = explode('.', $fileName);
		$last = array_pop($parts);
		$parts = implode('.', $parts);
		//print_r($parts);
		$fileName = $parts.".pdf";
		$path=$_SERVER['DOCUMENT_ROOT'].'crm/uploads/media/'.$uploadedBy.'/'.$fileName;
		$watermark = new WatermarkerTCPDF($path,$_SERVER['DOCUMENT_ROOT'].$fileName);
		$watermark->wmText($text);
		$watermark->doWaterMark();
		
		//send pdf to browser to download
		header('Content-type: application/pdf');
		header("Content-disposition: attachment; filename=".$fileName);
		header('Content-Description: File Transfer');
		readfile($fileName);
		
		//delete file
		unlink($fileName);
		break;

	case "application/pdf":	
		$watermark = new WatermarkerTCPDF($path,$_SERVER['DOCUMENT_ROOT'].$fileName);
		$watermark->wmText($text);
		$watermark->doWaterMark();
		
		//send pdf to browser to download
		header('Content-type: application/pdf');
		header("Content-disposition: attachment; filename=".$fileName);
		header('Content-Description: File Transfer');
		readfile($fileName);
		
		//delete file
		unlink($fileName);
		break;
		
	case "image/png":	
	
		//open image
		$jpg_image = imagecreatefrompng($path);
		
		//set font
		$font_path = 'OpenSans-Regular.ttf';
		
		//check font size and edit to fit the image
		$wdth=imagesx($jpg_image);		
		$box = imagettfbbox(20,0,$font_path,$text);
		$bboxWidth = abs($box[4] - $box[0]-10);
		$scale = $wdth / $bboxWidth;
		$fontsize=20*$scale;

		//set watermark color
		$color= imagecolorallocate($jpg_image, 146, 158, 158);
		
		
		//apply the watermark
		imagettftext($jpg_image, $fontsize, 0, 0, 20, $color, $font_path, $text);
		
		//send image to browser to download
		header('Content-type: image/jpeg');
		imagepng($jpg_image,$fileName);
		header("Content-disposition: attachment; filename=".$fileName);
		header('Content-Description: File Transfer');
		readfile($fileName);
		
		// delete the image resource
		imagedestroy($jpg_image);
		unlink($fileName);
		break;

	case "image/jpeg":
	
		//open image
		$jpg_image = imagecreatefromjpeg($path);
		//set watermark color
		$color= imagecolorallocate($jpg_image, 146, 158, 158);
		//set font
		$font_path = 'OpenSans-Regular.ttf';
		
		//check font size and edit to fit the image
		$box = imagettfbbox(20,0,$font_path,$text);
		$wdth=imagesx($jpg_image);
		$bboxWidth = abs($box[4] - $box[0]-10);
		$scale = $wdth / $bboxWidth;
		$fontsize=20*$scale;
		
		//apply the watermark
		imagettftext($jpg_image, $fontsize, 0, 0, 20, $color, $font_path, $text);
		
		//send image to browser to download
		header('Content-type: image/jpeg');
		imagejpeg($jpg_image,$fileName);
		header("Content-disposition: attachment; filename=".$fileName);
		header('Content-Description: File Transfer');
		readfile($fileName);
		
		
		// delete the image resource and watermarked image
		imagedestroy($jpg_image);
		unlink($fileName);
		break;
		
	default:
		//send file to browser to download
		header('Content-type: '.$mimetype);
		header("Content-disposition: attachment; filename=".$path);
		header('Content-Description: File Transfer');
		readfile($path);
}
?>
