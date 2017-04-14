<?php
/**
* @author Tomiwa Adefokun <tomiwa.adefokun@reinsys.com>
* @copyright 2016-2017 Reinsys
* @uses Facebook
*/
require_once 'constants.php';
$causeID = trim(strtolower($_GET['causeID']));
session_start();
require_once 'Facebook/autoload.php';

$fb = new Facebook\Facebook([
  'app_id' => FB_APP_ID,
  'app_secret' => FB_APP_SECRET,
  'default_graph_version' => 'v2.5',
  ]);


$helper = $fb->getRedirectLoginHelper();
try {
	$accessToken = $helper->getAccessToken(); 
	if($accessToken){
		$fb->setDefaultAccessToken($accessToken);
	  
		try{
			$response = $fb->get('/me');
			$userNode = $response->getGraphUser();
			$_SESSION['facebook_access_token'] = (string) $accessToken;
			$_SESSION['facebook_name'] = $userNode->getName();
			$_SESSION['facebook_id'] = $userNode->getId();
			if(strlen($causeID) > 0) header("location: index.php?causeID=".$causeID."");
			else header("location: index.php");
		}
		catch(Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			echo 'Facebook Login Failed. Click <a href="index.php">here</a> to go back.';
			exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			echo 'Facebook Login Failed. Click <a href="index.php">here</a> to go back.';
			exit;
		}
	}
	else {
		if(strlen($causeID) > 0) header("location: index.php?causeID=".$causeID."");
		else header("location: index.php");
	}

} catch(Facebook\Exceptions\FacebookResponseException $e) {
  // When Graph returns an error
  echo 'Facebook Login Failed. Click <a href="index.php">here</a> to go back.';
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook Login Failed. Click <a href="index.php">here</a> to go back.';
  exit;
}
