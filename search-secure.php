<?php 

 //Internal frame that appears within the popup
	require('config/db_connect.php');
	require("classes/cls.layer.php");
	require("classes/cls.ssshout.php");
	
	$ly = new cls_layer();
	$sh = new cls_ssshout();
	
	
	//We may have a possible user request in the case of receiving an email
	if(isset($_REQUEST['possible_user'])) {
	
		if(isset($_REQUEST['check'])) {
			if($sh->check_email_secure($_COOKIE['email'], $_REQUEST['check'])) {
				//Test if there is no password on this email account, and set ourselves as the logged in user
				$sh->new_user($_COOKIE['email'], '');
				$_SESSION['logged-email'] = $_COOKIE['email'];
			}
		}
	
	}
	
	
	
	function currentdir($url) {
    // note: anything without a scheme ("example.com", "example.com:80/", etc.) is a folder
    // remove query (protection against "?url=http://example.com/")
    if ($first_query = strpos($url, '?')) $url = substr($url, 0, $first_query);
    // remove fragment (protection against "#http://example.com/")
    if ($first_fragment = strpos($url, '#')) $url = substr($url, 0, $first_fragment);
    // folder only
    $last_slash = strrpos($url, '/');
    if (!$last_slash) {
        return '/';
    }
    // add ending slash to "http://example.com"
    if (($first_colon = strpos($url, '://')) !== false && $first_colon + 2 == $last_slash) {
        return $url . '/';
    }
    return substr($url, 0, $last_slash + 1);
}

	
	function urldir($relativeurl, $callerurl) {
	  return currentdir($callerurl) . $relativeurl;
	  //reduce callerurl from https://blahblah/dir/dir/script.xyz
	  // to https://blahblah/dir/dir/
	  // and then add a relative url to it
	  // eg. '../../dir/script.css'
	}
	
	//Optional params
	if(isset($_REQUEST['server'])) {
	    $server = $_REQUEST['server'];
	} else {
	    $server = "https://atomjump.com";
	}
	if(isset($_REQUEST['clientremoteurl'])) {
	    $clientremoteurl = $_REQUEST['clientremoteurl'];
	} else {
	    $clientremoteurl = "https://atomjump.com/index.html";
	}
	
	
	
	
	
	if((isset($_REQUEST['cssBootstrap']))&&($_REQUEST['cssBootstrap'] != '')) {
	    $cssBootstrap = urldir($_REQUEST['cssBootstrap'], $clientremoteurl);
	} else {
	    $cssBootstrap = "css/bootstrap.min.css";
	}
			
if((isset($_REQUEST['cssFeedback']))&&($_REQUEST['cssFeedback'] != '')) {
	    $cssFeedback = urldir($_REQUEST['cssFeedback'], $clientremoteurl);
	} else {
	    $cssFeedback = "css/comments-0.1.css";
	}
	
	
	
	
	
	//This is called from a different port 1444 rather than 443- which tells the proxy to not
	//Get new user in here, and set user IP address in session
	
	
	//Keep track of the number of views we have from this session - also reset if reloading
	$_SESSION['view-count'] = 0;
	
	
	//Ensure no caching
	header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	header("Pragma: no-cache"); // HTTP/1.0
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	
	
	
	
?><!DOCTYPE html>
<html lang="en">
  <head>
  	    <meta charset="utf-8">
		 <!--<meta name="viewport" content="width=device-width, user-scalable=no">-->
		 <title>AtomJump Loop - a feedback form for your site</title>
		 
		 <meta name="description" content="Offer your customers a clean feedback form, with live chat, public & private posts across any mobile or desktop device.">
		 
		 <meta name="keywords" content="Feedback Form, Live Chat, Customer Chat">
		 
			  <!-- Bootstrap core CSS -->
			<link rel="StyleSheet" href="<?php echo $cssBootstrap ?>" rel="stylesheet">
			
			<!-- AtomJump Feedback CSS -->
			<link rel="StyleSheet" href="<?php echo $cssFeedback ?>">
			
			<!-- Bootstrap HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
			<!--[if lt IE 9]>
			  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
			  <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
			<![endif]-->
			
			<!-- Include your version of jQuery here.  This is version 1.9.1 which is tested with AtomJump Feedback. -->
			<script type="text/javascript" src="https://code.jquery.com/jquery-1.11.0.min.js"></script> 
			<!-- Took from here 15 May 2014: http://ajax.googleapis.com/ajax/libs/jquery/1.9.1 -->
			
			
			<script>
				var initPort = ":1444";
				var portReset = true;
			
				var ajFeedback = {
					"uniqueFeedbackId" : "<?php echo $_REQUEST['uniqueFeedbackId'] ?>",
					"myMachineUser" : "<?php echo $_REQUEST['myMachineUser'] ?>",
					"server" : "<?php echo $_REQUEST['server'] ?>"
				}
				
				<?php if(($_SESSION['logged-user'])||($staging == true)) { 
					//We know who we already are. Or we are on staging and don't want a secure ver
					?>
					var port = "";
				<?php } else { 
					//we don't yet know our ip address - need to make an initial first insecure request 
					?>
					var port = initPort;
				<?php } ?>
				
				
				

				
			</script>
			<script type="text/javascript" src="<?php echo $root_server_url ?>/js/chat-inner-1.01.js"></script> <!-- TODO - keep path as js/chat.js -->
			<!--<script type="text/javascript" src="<?php echo $root_server_url ?>/js/adapter.js"></script>--> <!-- For video chat -->
			
	</head>
	<body class="comment-popup-body">
		 <div id="comment-popup-content" class="comment-inner-style" style="width: <?php echo $_REQUEST['width'] ?>px; height: <?php echo $_REQUEST['height'] ?>px;">
			<div style="clear: both;"></div>
			
			<?php
				//Width of video  
				$maxratio = 1.25; 
				$maxheight = intval($_REQUEST['height']*0.7); 
				if($maxheight <390) $maxheight = 390;	
				$maxwidth = $maxratio*$maxheight;
				$width = $_REQUEST['width'] - 14;
				if($width > $maxwidth) $width = $maxwidth;
			?>
			<div id="video-chat-container" style="position: relative; width: <?php echo $width ?>px; margin-left: auto; margin-right: auto; display: none; margin-bottom: 10px; display: none; background-color: #444;">
				<div id="video-chat-iframe-container" style="width:<?php echo $width; ?>px; height: <?php echo $maxheight ?>px;"><iframe id="video-chat" style="z-index: 1000;" width="100%" height="100%" frameborder="0"></iframe></div>
				<div style="position: relative; float: right; padding: 5px;  z-index: 5000;"><a href="javascript:" title="Go Fullscreen" onclick="toggleVideoFullScreen(); return false;"><img src="images/largerscreen.svg"></a></div>
				<div style="position: relative; margin-top: 6px; margin-bottom: 6px; float: right; margin-right: 10px; text-align: right; color: white; opacity: 0.7; background-color: black; padding: 5px;border-radius: 5px; z-index: 5000;">Link: <?php echo $_REQUEST['clientremoteurl'] ?></div> 
				<div style="clear: both; height: 2px;"></div>
			</div>
			
			
			 <!--TODO <video id="video-chat" autoplay style="display:none; width: <?php echo $_REQUEST['width'] ?>px; height: <?php echo $_REQUEST['height'] ?>px;"></video> left: <?php echo $width - 260; ?>px; width: 260px;-->
			
			<script>
				
			
				var startedFullScreen = false;
				var pfx = ["webkit", "moz", "ms", "o", ""];
				function RunPrefixMethod(obj, method) {

					var p = 0, m, t;
					while (p < pfx.length && !obj[m]) {
						m = method;
						if (pfx[p] == "") {
							m = m.substr(0,1).toLowerCase() + m.substr(1);
						}
						m = pfx[p] + m;
						t = typeof obj[m];
						if (t != "undefined") {
							pfx = [pfx[p]];
							return (t == "function" ? obj[m]() : obj[m]);
						}
						p++;
					}

				}
				
				function showVid()
				{
				
				
					//'use strict';
					// variables in global scope so available to console
					/*TODO var video = document.querySelector('video');
					var constraints = {
						audio: false,
						video: true
					};
					navigator.getUserMedia = navigator.getUserMedia ||
					navigator.webkitGetUserMedia || navigator.mozGetUserMedia;
					function successCallback(stream) {
						window.stream = stream; // stream available to console
						if (window.URL) {
							video.src = window.URL.createObjectURL(stream);
						} else {
							video.src = stream;
						}
					}
					function errorCallback(error) {
						console.log('navigator.getUserMedia error: ', error);
					}
					navigator.getUserMedia(constraints, successCallback, errorCallback);
					*/
				
					var iframe = document.getElementById("video-chat");
					var roomName = "aj-<?php echo $_REQUEST['uniqueFeedbackId'] ?>";
					appearin.addRoomToIframe(iframe, roomName);
					
					
					
					
					
					
					$('#video-chat-container').slideToggle();
					return false;
			
				}
				
				
				$(document).on("webkitfullscreenchange mozfullscreenchange fullscreenchange",function(){
       				 //Monitor exiting
       				 e = document.getElementById("video-chat-container");
       				 if (RunPrefixMethod(document, "FullScreen") || RunPrefixMethod(document, "IsFullScreen")) {
       				 	$('#video-chat-container').width(screen.width);
					       	$('#video-chat-iframe-container').width("100%");
						      $('#video-chat-iframe-container').height(screen.height - 60);
       				 	
       				 	
						
       				 } else {
       				 
       				 	$('#video-chat-container').width("<?php echo $width ?>px");
						      $('#video-chat-iframe-container').width("<?php echo $width; ?>px");
					       	$('#video-chat-iframe-container').height("<?php echo $maxheight ?>px");
						
						      window.focus();
					     }
						
											//resize to fit the new screen size
				});
					
				
				
				
				function toggleVideoFullScreen()
				{
				
					
					
					e = document.getElementById("video-chat-container");
					
					if (RunPrefixMethod(document, "FullScreen") || RunPrefixMethod(document, "IsFullScreen") || startedFullScreen == true) {
						
						
						startedFullScreen = false;
						RunPrefixMethod(document, "CancelFullScreen");
					
						//exit fullscreen
					
					
						
						
						
						//Duplicate of chat.js functionality
						
						
						
					}
					else {
						//start fullscreen
						
						startedFullScreen = true;
						RunPrefixMethod(e, "RequestFullScreen");
						
					
					}
				}
				
				
				
				var appearin;
				
				
				
			</script>
	
	
			<div id="comment-chat-form" class="container" >
				   <form id="comment-input-frm" class="form form-inline" role="form" action="" onsubmit="return mg.commitMsg(true);"  method="GET">
							<input type="hidden" name="action" value="ssshout">
							<input type="hidden" id="lat" name="lat" value="">
							<input type="hidden" id="lon" name="lon" value="">
							<input type="hidden" id="whisper_to" name="whisper_to" value="">
							<input type="hidden" id="whisper_site" name="whisper_site" value="">
							<input type="hidden" id="name-pass" name="your_name" value="<?php echo $_COOKIE['your_name']; ?>">
							<input type="hidden" name="passcode" id="passcode-hidden" value="<?php echo $_REQUEST['uniqueFeedbackId'] ?>">
							<input type="hidden" id="reading" name="reading" value="">
							<input type="hidden" name="remoteapp" value="true">
							<input type="hidden" id="clientremoteurl" name="clientremoteurl" value="<?php echo $_REQUEST['clientremoteurl'] ?>">
							<input type="hidden" id="remoteurl" name="remoteurl" value="">
							<input type="hidden" id="units" name="units" value="mi">
								<input type="hidden" id="short-code" name="short_code" value="">
								<input type="hidden" id="public-to" name="public_to" value="">
						<input type="hidden" id="volume" name="volume" value="1.00">
							<input type="hidden" id="ses" name="ses" value="<?php if(isset($_COOKIE['ses'])) { echo $_COOKIE['ses']; } else { echo ''; } ?>">
							<input type="hidden" name="cs" value="21633478">
							<input type="hidden" id="typing-now" name="typing" value="off">
							<input type="hidden" id="shout-id" name="shout_id" value="">
					  		<input type="hidden" id="msg-id" name="msg_id" value="">
					   		<input type="hidden" id="message" name="message" value="">
							<input type="hidden" id="email" name="email" value="<?php if(isset($_COOKIE['email'])) { echo $_COOKIE['email']; } else { echo ''; } ?>">
							<input type="hidden" id="phone" name="phone" value="<?php if(isset($_COOKIE['phone'])) { echo $_COOKIE['phone']; } else { echo ''; } ?>">
							<div class="form-group col-xs-12 col-sm-12 col-md-7 col-lg-8">
							  <div class="">
								<input id="shouted" name="shouted" type="text" class="form-control" maxlength="510" placeholder="Enter your comment" autocomplete="off"> 
							  </div>
							</div>
							<div class="form-group col-xs-12 col-sm-12 col-md-5 col-lg-4">
								<button id="private-button"  class="btn btn-info" style="margin-bottom:3px;">Send Privately</button>
								<button type="submit" onclick="return mg.commitMsg(false);" class="btn btn-primary" style="margin-bottom:3px;">Send Publicly</button>
								<a href="javascript:" onclick="return showVid();" style="margin-bottom:3px;"><img id="video-button" src="<?php echo $root_server_url ?>/images/video.svg" title="Video Chat" style="width: 48px; height: 32px;"></a>
							</div>
					</form>
			</div>
			<div id="comment-prev-messages">
			</div>
		</div>
		<div id="comment-options" style="width: <?php echo $_REQUEST['width'] ?>px; height: <?php echo $_REQUEST['height'] ?>px;">
				<h4>Comment Settings</h4>
				
				<div style="float: right;" id="comment-logout" <?php if($_SESSION['logged-user']) { ?>style="display: block;"<?php } else { ?>style="display: none;"<?php } ?>>
				
					
					<a id="comment-logout-text" href="javascript:" onclick="$.get( '<?php echo $root_server_url ?>/logout.php', function( data ) { logout(); } );" <?php if($_COOKIE['email'] == $_SESSION['logged-email']) { ?>style="display: block;"<?php } else { ?>style="display: none;"<?php } ?>>Logout</a>
					
					<span id="comment-not-signed-in" <?php if($_COOKIE['email'] == $_SESSION['logged-email']) { ?>style="display: none;"<?php } else { ?>style="display: block;"<?php } ?>>Not Signed In</span>
				</div>
					
				 <form id="options-frm" class="form" role="form" action="" onsubmit="return set_options_cookie();"  method="POST">
				 				 <input type="hidden" name="passcode" id="passcode-options-hidden" value="<?php echo $_REQUEST['uniqueFeedbackId'] ?>">
				 				 <div class="form-group">
				 						<div>Your name</div>
							 			<input id="your-name-opt" name="your-name-opt" type="text" class="form-control" placeholder="Enter your name" value="<?php if(isset($_COOKIE['your_name'])) { echo $_COOKIE['your_name']; } else { echo ''; } ?>" >
								</div>
								 <div class="form-group">
		 									<div>Your email <a href="javascript:" onclick="$('#email-explain').slideToggle();" title="We only use this to forward private messages from this forum.">optional</a> <span id="email-explain" style="display: none;  color: #f88374;">We only use this to forward private messages from this forum.</span></div>
						  					<input id="email-opt" name="email-opt" type="text" class="form-control" placeholder="Enter your e-mail" value="<?php if(isset($_COOKIE['email'])) { echo $_COOKIE['email']; } else { echo ''; } ?>">
								</div>
								<div><a id="comment-show-password" href="javascript:">More</a></div>
								<div id="comment-password-vis" style="display: none;">
									<div  class="form-group">
										<div>Your password <a href="javascript:" onclick="$('#password-explain').slideToggle();" title="We use this to provide security if you require private messages. Enter this, and click save to login.">optional</a>, <a id='clear-password' href="javascript:" onclick="return clearPass();">reset</a> <span id="password-explain" style="display: none; color: #f88374;">We use this to provide security if you require private messages. Enter this, and click save to login. </span></div>
						  				<input  id="password-opt" name="pd" type="password" class="form-control" placeholder="Enter your password" value="">
									</div>
									<div  class="form-group">
										<div>Your full mobile <a href="javascript:" onclick="$('#mobile-explain').slideToggle();" title="We use this to text you if you receive messages on this feedback forum. Note the cost is 16c per message.  We bill on a monthly basis.  Please include your full country code eg. 4412345678">optional, 16c/text</a>  <span id="mobile-explain" style="display: none;  color: #f88374;">We use this to text you if you receive messages on this feedback forum. Note the cost is 16c per message.  We bill on a monthly basis.  Please include your full country code eg. 4412345678</span></div>
										 <input  id="phone-opt" name="ph" type="text" class="form-control" placeholder="Enter your mobile (ignore +)" value="<?php if(isset($_COOKIE['phone'])) { echo $_COOKIE['phone']; } else { echo ''; } ?>">
									</div>
									<div style="float: right;">
						  					<a id="comment-user-code" href="javascript:">Advanced</a>
						  			</div>
						  			<div id="group-users-form" class="form-group" style="display:none;">
										<div>This forum's private owners <a href="javascript:" onclick="$('#users-explain').slideToggle();" title="A list of the users who see the private messages, separated by commas.">optional</a>  <span id="users-explain" style="display: none;  color: #f88374;">A list of the users who see the private messages, separated by commas, with an optional sms message sent to each user E.g. 92.27.10.17:62,92.27.10.17:7:sms</span></div>
										 <input  id="group-users" name="users" type="text" class="form-control" placeholder="Enter your users string" value="">
									</div>
									
						  			</div>
								<div  class="form-group">
		 									<div style="display: none; color: red;" id="comment-messages"></div>
								</div>
							 <button type="submit" class="btn btn-primary" style="margin-bottom:3px;">Save Settings</button>
							<br/>
							<br/>
							 <div>Tip: Click a person's name to talk privately to them.</div>
							 <br/>
							 <div>Get your own <a target="_blank" href="http://atomjump.com">AtomJump Loop</a> widget.</div>
				 </form>
		</div>
		<div id="comment-upload" style="width: <?php echo $_REQUEST['width'] ?>px; height: <?php echo $_REQUEST['height'] ?>px;">
				<h4>Upload</h4>
				
				
					
				 <form id="upload-frm" class="form" role="form" action="" onsubmit="return upload();"  method="POST">
				 				 <div class="form-group">
				 						<div>Select Image</div>
							 			<input id="image" name="fileToUpload" type="file" accept=".jpg,.jpeg," class="form-control" placeholder="Select your image file" >
								</div>
								<div id="uploading-wait" style="display: none; margin-bottom: 10px;">Uploading, please wait... <img src="images/ajax-loader.gif"></div>
								<div id="uploading-msg" style="display: none; color: #900; margin-bottom: 10px;"></div>
								
											 <button type="submit" class="btn btn-primary" style="margin-bottom:3px;" name="submit">Upload</button>
						  	<br/>
							 <br/>
							 <div>Max size 10MB. Only .jpgs are accepted.</div>
							 <br/>
								 
								 	<h4>Download</h4>
								  <div>Download messages spreadsheet <small>(must log in)</small>. 	</div>
								  <br/>
						    <div><a href="download.php?format=excel&uniqueFeedbackId=<?php echo $_REQUEST['uniqueFeedbackId'] ?>" class="btn btn-primary" role="button">Download</a></div>
							 	 <br/>
							 <br/>
					
							 <div>Get your own <a target="_blank" href="http://atomjump.com">AtomJump Loop</a> widget.</div>
				 </form>
		</div>
		
		
		
		<script>
    		var ie8 = false;
		</script>

		<!--[if IE 8]>
			<script>
				ie8 = true;
			</script>
		<![endif]-->
		<script>
			function clearPass()
			{
				var ur = "clear-pass.php";
				
				var email = $('#email-opt').val();
				if(email != '') {
					ur = ur + '?email=' + email;
					
					//Also save this cookie
					document.cookie = 'email=' + email + '; path=/; expires=' + cookieOffset() + ';';
				}
				
			
			  $.get(ur, function(response) { 
			  		 
			       $('#clear-password').html(response);
			       
			  });
			  
			  return false;
		 }
		
				function vidDeactivate()
				{
					$('#video-button').attr("src", "<?php echo $root_server_url ?>/images/no-video.svg");
					$('#video-button').attr("title","Supported platforms: Recent Android, iPhone, iPad, Chrome, Firefox, Opera");
					$('#video-button').parent().attr("onclick", "return false;");
				}
				
				function vidDeactivateIE8()
				{
					$('#video-button').hide();
					}
					
			
				function decideVideo()
				{
					//Appear.in
					
					var AppearIn = window.AppearIn;
					appearin = new AppearIn();
					
					var isWebRtcCompatible = appearin.isWebRtcCompatible();
					if(isWebRtcCompatible == true) {
					
					} else {
						var iOS = ( navigator.userAgent.match(/(iPad|iPhone|iPod)/g) ? true : false );
						if(iOS == false) {
							vidDeactivate();
						} else {
							//we want iOS to still popup with the app version
						}
						
					}
				
				}
			
			
				$(document).ready(function(){
					if(ie8 == false) {
						jQuery.getScript( "//developer.appear.in/scripts/appearin-sdk.0.0.4.min.js", function() { 
							decideVideo();
						});
					} else {
					
						vidDeactivateIE8();
					}
					
					
					
					
				});
				
			
		</script>
		
	</body>
</html>