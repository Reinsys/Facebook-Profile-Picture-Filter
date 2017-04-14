<?php
/**
* @author Tomiwa Adefokun <tomiwa.adefokun@reinsys.com>
* @copyright 2016-2017 Reinsys
* @uses Facebook
*/

define('FB_APP_ID', '');
define('FB_APP_SECRET', '');
define('UPLOADED_IMAGE_DESTINATION', 'originals/');
define('PROCESSED_IMAGE_DESTINATION', 'images/');

$causes = array(
	'quebec' => array('size'=>600, 'opacity' => 60, 'name' => 'flag_quebec', 'image' => 'flag_quebec.png', 'hash' => '#Pray4Quebec', 'title'=>'Pray for Quebec','button'=>'Post to timeline and then set as profile picture', 'contact'=>'postmediapr@gmail.com')
);

$causeID = trim(strtolower($_GET['causeID']));
if(!in_array($causeID, array_keys($causes))) {
	die('Cause not found.');
	exit;
}
else{
	$cause = (object) $causes[$causeID];
}
session_start();
require_once 'Facebook/autoload.php';

$fb = new Facebook\Facebook([
  'app_id' => FB_APP_ID,
  'app_secret' => FB_APP_SECRET,
  'default_graph_version' => 'v2.5',
  ]);
  

if($_GET['logout']){
	unset($_SESSION['facebook_access_token']);
	unset($_SESSION['facebook_name']);
	unset($_SESSION['facebook_id']);
}

if(isset($_SESSION['facebook_access_token']) ) {
	$accessToken = $_SESSION['facebook_access_token'];
	$facebookName = $_SESSION['facebook_name'];
	$facebookId = $_SESSION['facebook_id'];
	
	$fb->setDefaultAccessToken($accessToken);
	
	try {
	  $response = $fb->get('/me/picture/?redirect=false&type=square&width='.$cause->size.'&height='.$cause->size.'');
	  $pictureNode = $response->getGraphObject();
	  
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
	  // When Graph returns an error
	 // echo 'Graph returned an error: ' . $e->getMessage();
	  $outputMessage = '<div class="alert alert-warning" role="alert">An error occurred during processing.</div>';
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
	  // When validation fails or other local issues
	  //echo 'Facebook SDK returned an error: ' . $e->getMessage();
	  $outputMessage = '<div class="alert alert-warning" role="alert">An error occurred during processing.</div>';
	}
}
else{	
	$helper = $fb->getRedirectLoginHelper();
	$permissions = ['email', 'user_likes', 'publish_actions']; // optional
	$loginUrl = $helper->getLoginUrl(FILTER_BASE_URL.'/login-callback.php?causeID='.$causeID, $permissions);	
}
if(isset($_POST['watermark'])) define('WATERMARK_OVERLAY_IMAGE', 'watermarks/'. $_POST['watermark'] . '.png');
define('WATERMARK_OVERLAY_OPACITY', $cause->opacity);
define('WATERMARK_OUTPUT_QUALITY', 100);

function create_watermark($source_file_path, $output_file_path)
{
    list($fb_width, $fb_height, $source_type) = getimagesize($source_file_path);
	$source_width = min($fb_width, $fb_height);
	$source_height = min($fb_width, $fb_height);
	
    if ($source_type === NULL) {
        return false;
    }
    switch ($source_type) {
        case IMAGETYPE_GIF:
            $source_gd_image = imagecreatefromgif($source_file_path);
            break;
        case IMAGETYPE_JPEG:
            $source_gd_image = imagecreatefromjpeg($source_file_path);
            break;
        case IMAGETYPE_PNG:
            $source_gd_image = imagecreatefrompng($source_file_path);
            break;
        default:
            return false;
    }
    $overlay_gd_image = imagecreatefrompng(WATERMARK_OVERLAY_IMAGE);
    $overlay_width = imagesx($overlay_gd_image);
    $overlay_height = imagesy($overlay_gd_image);    
	
	//$overlay_width = imagesx($source_gd_image);
    //$overlay_height = imagesy($source_gd_image);
	
	$new_overlay_gd_image=ImageCreateTrueColor($source_width,$source_height);

	// Transparent Background
	$transparency = imagecolorallocatealpha($new_overlay_gd_image, 0, 0, 0, 127);
	imagefill($new_overlay_gd_image, 0, 0, $transparency);
	imagecolortransparent($new_overlay_gd_image, $transparency);
    imagecopyresampled($new_overlay_gd_image,$overlay_gd_image,0,0,0,0,$source_width,$source_height,$overlay_width,$overlay_height); 
	
    imagecopymerge(
        $source_gd_image,
        $new_overlay_gd_image,
        0,
        0,
        0,
        0,
        $source_width,
        $source_height,
        WATERMARK_OVERLAY_OPACITY
    );
    imagejpeg($source_gd_image, $output_file_path, WATERMARK_OUTPUT_QUALITY);
    imagedestroy($source_gd_image);
    imagedestroy($overlay_gd_image);
    imagedestroy($new_overlay_gd_image);
}

/**
 * Uploaded file processing function
 */


function process_image($facebookId, $url)
{
    $temp_file_name = time() . '_' . $facebookId . '.jpg';
	
	$url = $url;
	$uploaded_file_path = UPLOADED_IMAGE_DESTINATION . $temp_file_name;
	file_put_contents($uploaded_file_path, file_get_contents($url));

    list(, , $temp_type) = getimagesize($uploaded_file_path);
    if ($temp_type === NULL) {
        return false;
    }
    switch ($temp_type) {
        case IMAGETYPE_GIF:
            break;
        case IMAGETYPE_JPEG:
            break;
        case IMAGETYPE_PNG:
            break;
        default:
            return false;
    }
    $processed_file_path = PROCESSED_IMAGE_DESTINATION . preg_replace('/\\.[^\\.]+$/', '.jpg', $temp_file_name);
	
	
	
    $result = create_watermark($uploaded_file_path, $processed_file_path);
    if ($result === false) {
        return false;
    } else {
        return array($uploaded_file_path, $processed_file_path, $temp_file_name);
    }
}
function thumb320($filename, $watermark){
	$directory = ($watermark === true) ? 'watermarks/' : 'images/';
	$thumbDirectory = $directory . 'thumb/';
	$path = ($watermark === true) ? FILTER_BASE_URL .'/watermarks/' : FILTER_BASE_URL .'/images/';
	$src_img_dir = $directory . $filename;
	
	if(!file_exists($path . 'thumb/' .  $filename)){	
		$system = explode('.',$filename);
		
		if (preg_match('/png|PNG/',end($system))){
			$src_img=imagecreatefrompng($src_img_dir);
		}
		else $src_img=imagecreatefromjpeg($src_img_dir);
		
		$old_x = imagesx($src_img);
		$old_y = imagesy($src_img);
		
		$thumbImage=ImageCreateTrueColor(320,320);
		$white = imagecolorallocate($thumbImage, 255, 255, 255);
		imagefill($thumbImage, 0, 0, $white);
		imagecopyresampled($thumbImage,$src_img,0,0,0,0,320,320,$old_x,$old_y); 
		
		if (preg_match("/png|PNG/",end($system)))
		{
			imagepng($thumbImage, $thumbDirectory . $filename); 
		} else {
			imagejpeg($thumbImage, $thumbDirectory .  $filename); 
		}
		imagedestroy($thumbImage); 
		imagedestroy($src_img); 
		
		return $path . 'thumb/' .  $filename;
	}
	else return $path . 'thumb/' .  $filename;
}
/*
 * Here is how to call the function(s)
 */
if((isset($_POST['submit']) || isset($_POST['save'])) && isset($_POST['watermark']) && $pictureNode && $pictureNode->getProperty('url')){
	
	$result = process_image($facebookId, $pictureNode->getProperty('url'));
	if ($result === false) {
		$outputMessage = '<div class="alert alert-warning" role="alert"><strong>Posting Failed!</strong> An error occurred during file processing.</div>';

	} else {
		if(isset($_POST['submit'])){
			try{
				$response = $fb->post('/me/photos/?url='. FILTER_BASE_URL .'/' . $result[1]);
				$photoNode = $response->getGraphObject();

				$publish = $fb->post('/me/feed/?link='. FILTER_BASE_URL .'/causes.php?causeID='.$causeID.'&caption=Use Filter Now&picture='. thumb320($result[2], false) .'');
				$publishNode = $publish->getGraphObject();
				
				$photoUrl = 'https://www.facebook.com/photo.php?fbid='.$photoNode->getProperty('id').'&makeprofile=1&profile_id='.$facebookId.'&pp_source=photo_view';
				echo '<script>window.open("'.$photoUrl.'")</script>';
				
				$outputMessage = '<div class="alert alert-success" role="alert"><strong>Posted Successfully!</strong> Go to your <a href="http://facebook.com" target="_blank" class="alert-link">Facebook Page</a> to set it as your profile picture.</div>';
			}
			catch(Exceptions\FacebookResponseException $e){
				$outputMessage = '<div class="alert alert-warning" role="alert"><strong>Posting Failed!</strong> We are unable to post to Facebook at the moment. Click <a href="'. FILTER_BASE_URL .'/' . $result[1] .'" target="_blank">here</a> to get your customized profile picture.</div>';
			}
			catch(Facebook\Exceptions\FacebookSDKException $e){
				$outputMessage = '<div class="alert alert-warning" role="alert"><strong>Posting Failed!</strong> We are unable to post to Facebook at the moment. Click <a href="'. FILTER_BASE_URL .'/' . $result[1] .'" target="_blank">here</a> to get your customized profile picture.</div>';
			}
		}
		else if(isset($_POST['save'])){
			$outputMessage = '<div class="alert alert-success" role="alert"><strong>Saved Successfully!</strong> Click <a href="'. FILTER_BASE_URL .'/' . $result[1] .'" target="_blank">here</a> to get your customized profile picture.</div>';
		}
		@unlink($result[0]);
	}

}
?>
