<?php 
require_once 'constants.php';
require_once 'filter.php';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
		<meta property="og:url"                content="<?php echo FILTER_BASE_URL;?>/index.php?causeID=<?php echo $causeID;?>" />
		<meta property="og:type"               content="article" />
		<meta property="og:title"              content="<?php echo $cause->title;?>" />
		<meta property="og:description"        content="Show your support for <?php echo $cause->title;?> - use the <?php echo $cause->hash;?> filter on your profile picture." />
		<meta property="og:image"              content="<?php echo thumb320($cause->image, true);?>" />		
		<meta property="og:author" content="Reinsys">
		<meta property="fb:app_id" content="<?php echo FB_APP_ID;?>">
		
		<title><?php echo $cause->title; ?> Profile Picture Filter</title>
		<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
		<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
		<script>
			$(function(){
				document.images.selectedImage.src = im.src;
				<?php if(isset($pictureNode)):?>
				$('#set_user_profile').attr('disabled', false);
				$('#save_user_profile').attr('disabled', false);
				<?php endif;?>
				$('input:submit').click(function(){
					setTimeout(function(){
						$('input:submit').attr("disabled", true);
						}, 100);
				});

			});
			
			var im = new Image();
			im.src = 'watermarks/<?php echo $cause->image;?>';
			
			
			
		</script>
		<style>
			body {
			  padding-top: 70px;
			  padding-bottom: 30px;
			}

		</style>
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">

		<!-- Optional theme -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css" integrity="sha384-aUGj/X2zp5rLCbBxumKTCw2Z50WgIr1vs/PFN4praOTvYXWlVyh2UtNUU0KAUhAX" crossorigin="anonymous">

		<!-- Latest compiled and minified JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" integrity="sha512-K1qjQ+NcF2TYO/eI3M6v8EiNYZfA95pQumfvcVrTHtwQVDG+aHRqLi/ETn2uB+1JqwYqVG3LIvdm9lj6imS/pQ==" crossorigin="anonymous"></script>
	</head>
	<body style="background-color: #eeeeff">
    <nav class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
			<div class="nav nav-pills pull-right" style="padding-top: 15px; color: #ffffff;">
			<?php if (isset($facebookName)) :?>
				<?php echo $facebookName;?> | <a href="<?php echo FILTER_BASE_URL;?>/index.php?causeID=<?php echo $causeID;?>&logout=1">Logout</a>
			<?php endif;?>
			</div>
			<div class="navbar-header">
			  <a class="navbar-brand" href="<?php echo FILTER_BASE_URL;?>/index.php?causeID=<?php echo $causeID;?>">Filter.i.ng&trade; - Identify with your Belief.</a> 
			</div>
		</div>
    </nav>
		<div class="container theme-showcase" role="main">
			<?php if(isset($outputMessage)) echo $outputMessage;?>
			
			<form  enctype="multipart/form-data" method="post" action="<?php echo FILTER_BASE_URL;?>/index.php?causeID=<?php echo $causeID;?>" class="form-inline">
				<!--<input type="file" name="File1" />-->
				<div class="panel panel-primary">
					<div class="panel-heading">
						<strong><?php echo $cause->title; ?> Filter</strong>
					</div>
					<div class="panel-body">
						<div class="col-md-4" style="padding: 0px">
							<div style="background: #eeeeee; width: 360px; padding: 20px" >
							<?php if(isset($pictureNode) && $pictureNode->getProperty('url')):?>
								<img width="320" src="<?php echo $pictureNode->getProperty('url');?>" />
							<?php else:?>
							<img src="profile_placeholder.jpg" />
							<?php endif;?>
							<img width="320" name="selectedImage" src="watermark.png" style="position: absolute; top: 20px; left: 20px; opacity: <?php echo $cause->opacity/100;?>;"/>
							</div>
							<?php if (isset($pictureNode)) :?>
								<br />
							<?php else: ?>
								<div style="padding: 20px 20px 0px 20px; text-align: center">
									<a href="<?php echo $loginUrl; ?>"><img src="fblogin.png" width="200px"/></a>
								</div>
							<?php endif;?>
						</div>
						<div class="col-md-8">
							<div>
								<div id="slider"></div>
								<input type="hidden" name="watermark" value="<?php echo $cause->name;?>" />
							<?php if(isset($pictureNode)):?>
								<input type="submit" id="set_user_profile" disabled value="<?php echo (isset($cause->button)) ? $cause->button : 'Use as Facebook Profile Picture';?>" name="submit" class="btn btn-primary" />									
								<input type="submit" id="save_user_profile" disabled value="Save and Download" name="save" class="btn btn-primary" />
							<?php else:?>
								<input type="submit" disabled value="<?php echo (isset($cause->button)) ? $cause->button : 'Use as Facebook Profile Picture';?>" class="btn btn-primary" />
							<?php endif;?>
							</div>
							<?php if(isset($cause->contact)):?>
								<hr />
								Campaign Contact: <strong><?php echo $cause->contact;?></strong>
								<hr />
							<?php endif;?>
							<div  class="well" style="text-align: center; margin-top: 48px">
								<h4><a href="http://www.facebook.com/www.filter.i.ng" target="_blank">Tell us about a cause or event that matters to you and those in your community.</a></h4>
							</div>
						</div>
					</div>
				</div>
			</form>
			<footer>
				<br />
				&copy; <a href="http://www.reinsys.com" target="_blank">Reinsys</a> <?php echo date('Y');?> | <a href="mailto:info@reinsys.com">info@reinsys.com</a> <br /><br /><strong>Disclaimer:</strong> Filter images are added based on requests. They do not represent our views or stands and also do not express our support for the causes they represent. 
			</footer>
		</div>
	</body>
</html>
