<?PHP
/*
Plugin Name: Stop Spammer Registrations Plugin
Plugin URI: http://www.BlogsEye.com/
Description: The Stop Spammer Registrations Plugin checks against Spam Databases to to prevent spammers from registering or making comments.
Version: 4.1
Author: Keith P. Graham
Author URI: http://www.BlogsEye.com/

This software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/************************************************************
* 	Set the hooks and filters
*	Primary hook is is_email()
*	other hooks:  pre_user_email, user_registration_email 
*	The theory being I'll catch somebody on one of them.
*	each hook has to remove the other hooks to prevent multiple entries into the code 
*
*************************************************************/
// just to be absolutely safe
if (!defined('ABSPATH')) exit;
// try to make this work with bbpress
if (function_exists('bbpress')) {
	add_action('bbp_loaded','kpg_load_all_checks',99); // try hooking into bbpress loaded
} else {
	add_action('init','kpg_load_all_checks'); // loads up normally
}
function kpg_load_spam_widget() {
	require_once('includes/stop-spam-reg-widget.php');
}
add_action( 'widgets_init', 'kpg_load_spam_widget' );
function kpg_load_all_checks() {
	// remove the hooks so we don't recurse.
	if (function_exists('bbpress')) {
		remove_action('bbp_loaded','kpg_load_all_checks',99); // try hooking into bbpress loaded
	} else {
		remove_action('init','kpg_load_all_checks'); // loads up normally
	}

	// check the Post to see if we load the checks. This is for the validattions only
	if (function_exists('is_user_logged_in')) { // no available in bbpress
		if(is_user_logged_in()) {
			return;
		}
	}
	// get a session to set the timer
	if (!session_id()) {
		session_start();
	}
	kpg_load_all_checks_no_post();

	if(!isset($_POST)) { // no post defined
		$_SESSION['kpg_stop_spammers_time']=time();
		return;
	}
	if (empty($_POST)) { // no post sent
		$_SESSION['kpg_stop_spammers_time']=time();
		return;
	}
	// I am using a plugin with your-email, your-name fields - might as well test them, too.
	$postfields=array('akismet_comment_nonce','bbp_anonymous_email','email','user_email','user_login',
			'author','your-name','your-email','log','psw','pass1','your_username','post_password',
			'your_email','signup_username','signup_email','user_name','blogname','signup_for',
			'excerpt','blog_name','url'); // last few are for trackbacks.
	// future merge this with the options custom trigger fields
	$is_in_post=false;
	foreach ($postfields as $postf) {
		if (array_key_exists($postf,$_POST)||array_key_exists(strtoupper($postf),$_POST)) {
			$is_in_post=true;
			break;
		}
	}
	if (!$is_in_post) {
		// did not find it. Check more generally
		foreach($_POST as $key=>$data) {
		    $key=strtolower($key);
			if (stripos($key,'name')!==false||
				stripos($key,'signup')!==false||
				stripos($key,'user')!==false||
				stripos($key,'pass')!==false||
				stripos($key,'pwd')!==false||
				stripos($key,'psw')!==false||
				stripos($key,'email')!==false) {
				$is_in_post=true;
				break;
			}
		}
	}
	if (!$is_in_post) {
		// did not find it.
		$_SESSION['kpg_stop_spammers_time']=time();
		return;
	}
	// here we can check to see if the posted data is correct
	
	// get the email author and ip
	$em='';
	if (array_key_exists('email',$_POST)) {
		$em=$_POST['email'];
	} else if (array_key_exists('user_email',$_POST)) {
		$em=$_POST['user_email'];
	} else if (array_key_exists('signup_email',$_POST)) {
		$em=$_POST['signup_email'];
	} else if (array_key_exists('bbp_anonymous_email',$_POST)) {
		$em=$_POST['bbp_anonymous_email'];
	} else if (array_key_exists('your-email',$_POST)) {
		$em=$_POST['your-email'];
	} else if (array_key_exists('your_email',$_POST)) {
		$em=$_POST['your_email'];
	}
	//echo "\r\n<!--\r\n step 3 \r\n-->\r\n";
	
	if (strpos($em,'@')===false) { // not an email, but a username (or some other crap)
		$em='';
	}
	// see if they have an author or username
	$author='';
	$pwd='';
	// final fix for bbPress from Rob Cain - thanks
	if (array_key_exists('author',$_POST)) {
		$author=$_POST['author'];
	} else if (array_key_exists('user_name',$_POST)) {
		$author=$_POST['user_name'];
	} else if (array_key_exists('your-name',$_POST)) {
		$author=$_POST['your-name'];
	} else if (array_key_exists('your_name',$_POST)) {
		$author=$_POST['your_name'];
	} else if (array_key_exists('user_login',$_POST)) {
		$author=$_POST['user_login'];
		if (array_key_exists('pass1',$_POST)) {
			$pwd=$_POST['pass1'];
		}
	} else if (array_key_exists('your_username',$_POST)) {
		$author=$_POST['your_username'];
	} else if (array_key_exists('signup_username',$_POST)) {
		$author=$_POST['signup_username'];
	} else if (array_key_exists('log',$_POST)) {
		$author=$_POST['log'];
		if (array_key_exists('pwd',$_POST)) {
			$pwd=$_POST['pwd'];
		}
	} // add your_username your_email
	//echo "\r\n<!--\r\n step 4 \r\n-->\r\n";
	// get the ip 
	$ip=kpg_get_ip();
	//  this is called once in "init" no need to call it ever again
	sfs_errorsonoff();
	kpg_sfs_check_load();
    $ansa=kpg_sfs_check($em,$author,$ip,$pwd);
	sfs_errorsonoff('off');
	
	return;
}
function kpg_load_all_checks_no_post() {
	add_action( 'template_redirect', 'kpg_sfs_check_404s' ); // check if bogus search for wp-login
	// optional checks
	$options=kpg_sp_get_options();
	if (array_key_exists('chkwpmail',$options)&&$options['chkwpmail']=='Y'){
		add_filter('wp_mail','kpg_sfs_reg_check_send_mail');
	}
	if (array_key_exists('redherring',$options)&&$options['redherring']=='Y') {
		add_action('comment_form_before','kpg_sfs_red_herring_comment'); // moved to comment form before
		add_filter('login_message','kpg_sfs_red_herring_login');	
		add_filter('before_signup_form','kpg_sfs_red_herring_signup');
	}
	if (array_key_exists('chkjscript',$options)&&$options['chkjscript']=='Y') {
		add_action('comment_form_before_fields','kpg_sfs_javascript');
	}
	return;
}


function load_sfs_mu() {
// check to see if this is an MU installation
	if (function_exists('is_multisite') && is_multisite() && !function_exists('kpg_ssp_global_setup')) {
		// install the global hooks to globalize the options
		$muswitch='Y';
		global $blog_id;
		// check blog 1 for the main copy of options
		switch_to_blog(1);
		$ansa=get_option('kpg_stop_sp_reg_options');
		restore_current_blog();
		if (empty($ansa)) $ansa=array();
		if (!is_array($ansa)) $ansa=array();
		if (array_key_exists('muswitch',$ansa)) $muswitch=$ansa['muswitch'];
		if ($muswitch!='N') $muswitch='Y';
		if ($muswitch=='Y') { // if it is true then the global options need to be installed.
			load_sfs_mu_options_file();
			kpg_ssp_global_setup();
		}
	}
}
/************************************************************
*
* show a bogus form. If the form is hit then this is a spammer
*
*************************************************************/
function kpg_sfs_red_herring_comment($query) {
	remove_action('comment_form_before','kpg_sfs_red_herring_comment');
    if (is_feed()) return $query;
	$sname=kpg_sfs_get_SCRIPT_URI();
	if (empty($sname)) return;
	if (strpos($sname,'/feed')) return $query;
   $rhnonce=wp_create_nonce('kpgstopspam_redherring');
?>
<div style="position:absolute;width:1px;height:1px;left:-1000px;top:-1000px;overflow:hidden;display:none;">
<br/>
<br/>
<br/>
<form action="<?php echo site_url( '/wp-comments-post.php' ); ?>" method="post" id="commentform1">
<p><input name="author" id="author" value="" size="22"  aria-required="true" type="text">
<label for="author"><small>Name (required)</small></label></p>

<p><input name="email" id="email" value="" size="22"  aria-required="true" type="text">
<label for="email"><small>Mail (will not be published) (required)</small></label></p>

<p><input name="url" id="url" value="" size="22" type="text">
<label for="url"><small>Website</small></label></p>
<p><textarea name="comment" id="comment" cols="58" rows="10" ></textarea></p>
<p><input name="submit" id="submit" value="Submit Comment" type="submit">
<input name="comment_post_ID" value="<?php echo get_the_ID();?>" id="comment_post_ID" type="hidden">
<input name="comment_parent" id="comment_parent" value="0" type="hidden">
</p>
<p style="display: none;"><input id="akismet_comment_nonce" name="akismet_comment_nonce" value="<?php echo $rhnonce;?>" type="hidden"></p>
</form>
</div>
<?php
	return $query;
}


/************************************************************
*
* show a bogus form. If the form is hit then this is a spammer
*
*************************************************************/
function kpg_sfs_red_herring_signup() {
	remove_filter('before_signup_form','kpg_sfs_red_herring_signup');	 
	$rhnonce=wp_create_nonce('kpgstopspam_redherring');
	// put a bugus signup form with the akismet nonce - maybe doesn't work but it might
	$errors = new WP_Error();
?>
<div style="position:absolute;width:1px;height:1px;left:-1000px;top:-1000px;overflow:hidden;">
<br/>
<br/>
<br/>
<form id="setupform1" method="post" action="wp-signup.php">

		<input type="hidden" name="stage" value="validate-user-signup" />
		<?php do_action( 'signup_hidden_fields' ); ?>
<p style="display: none;"><input id="akismet_comment_nonce" name="akismet_comment_nonce" value="<?php echo $rhnonce;?>" type="hidden"></p>		
		<?php show_user_form('', '', $errors); ?>
		<p>
					<input id="signupblog" type="radio" name="signup_for" value="blog"  checked='checked' />
			<label class="checkbox" for="signupblog">Gimme a site!</label>
			<br />
			<input id="signupuser" type="radio" name="signup_for" value="user"  />
			<label class="checkbox" for="signupuser">Just a username, please.</label>
				</p>

		<p class="submit"><input type="submit" name="submit" class="submit" value="Next" /></p>
</form>
</div>

<?php
	return;
} // end if red herring signup
/************************************************************
*
* add javascript to a form to fill a hidden field onsubmit
*
*************************************************************/
function kpg_sfs_javascript() {
	//echo "\r\n\r\n<!-- Made it to comment_form_before_fields -->\r\n\r\n";
	remove_filter('comment_form_before_fields','kpg_sfs_javascript');	 
	$jsnonce=wp_create_nonce('kpgstopspam_javascript');
	$badjsnonce=wp_create_nonce('kpgstopspam_javascript_bad');
// place some javascript on the page so that only humans using javascript use it
?>
<p style="display: none;">
<input id="kpg_jscript" name="kpg_jscript" value="<?php echo $badjsnonce;?>" type="hidden">
</p>
<script type="text/javascript" >
	var kpg_jscript_id=document.getElementById('kpg_jscript');
	kpg_jscript_id.value='<?php echo $jsnonce;?>';
</script>
<?php

}
/************************************************************
*
* show a bogus form. If the form is hit then this is a spammer
*
*************************************************************/
function kpg_sfs_red_herring_login($message) {
	remove_filter('login_message','kpg_sfs_red_herring_login');	
   $rhnonce=wp_create_nonce('kpgstopspam_redherring');
?>
<div style="position:absolute;width:1px;height:1px;left:-1000px;top:-1000px;overflow:hidden;">
<br/>
<br/>
<br/>


<form name="loginform1" id="loginform1" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>?redir=<?php echo $rhnonce; ?>" method="post">
	<p>
		<label for="user_login">User Name<br />
		<input type="text" name="log"  value="" size="20"  /></label>
	</p>
	<p>
		<label for="user_pass">Password<br />
		<input type="password" name="pwd"  value="" size="20"  /></label>
	</p>
<?php do_action('login_form'); ?>
	<p class="forgetmenot"><label for="rememberme"><input name="rememberme" type="checkbox" checked="checked"  value="<?php echo $rhnonce; ?>"  />Remember Me</label></p>
	<p class="submit">
		<input type="submit" name="wp-submit"  value="Log In"  />
		<input type="hidden" name="testcookie" value="1" />
	</p>
	<input id="akismet_comment_nonce" name="akismet_comment_nonce" value="<?php echo $rhnonce;?>" type="hidden">
</form>



</div>
<?php
	return $message;
}


/************************************************************
* 	kpg_sfs_reg_check_send_mail()
*	Hooked from wp_mail
*	this returns the params
*************************************************************/
function kpg_sfs_reg_check_send_mail($stuff) {
	if(is_user_logged_in()) {
		return $stuff;
	}
	$email='';
	$header=array();
	if (is_array($stuff)&&array_key_exists('header',$stuff)) $header=$stuff['header'];
	if (is_array($header)&&array_key_exists('from',$stuff)) $email=$stuff['from'];
	$from_name='';
	$from_email=$email;
	if ( strpos($email, '<' ) !== false ) {
		$from_name = substr( $email, 0, strpos( $email, '<' ) - 1 );
		$from_name = str_replace( '"', '', $from_name );
		$from_name = trim( $from_name );
		$from_email = substr( $email, strpos( $email, '<' ) + 1 );
		$from_email = str_replace( '>', '', $from_email );
		$from_email = trim( $from_email );
	}
	// get the ip 
	$ip=kpg_get_ip();
	// now call the generic checker
	sfs_errorsonoff();
	kpg_sfs_check_load();
    kpg_sfs_check($from_email,$from_name,$ip); 
	sfs_errorsonoff('off');
	return $stuff;

}
function kpg_sfs_get_SCRIPT_URI() {
	$sname='';
	if (array_key_exists("SCRIPT_URI",$_SERVER)) {
		$sname=$_SERVER["SCRIPT_URI"];	
	}
	if (empty($sname)) {
		$sname=$_SERVER["REQUEST_URI"];	
	}
	return $sname;

}
/************************************************************
* 	kpg_sfs_check_404s()
*	
*	If there is a 404 error on wp-login it is a spammer 
*   This just caches badips for spiders trolling for a login
*************************************************************/
function kpg_sfs_check_404s() {
	sfs_errorsonoff();
    kpg_sfs_check_404();
	sfs_errorsonoff('off');
    return;
}
function kpg_sfs_check_404() {
	// fix request_uri on IIS
	if (!isset($_SERVER['REQUEST_URI'])) {
		$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'],1 );
		if (isset($_SERVER['QUERY_STRING'])) { 
			$_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING']; 
		}
	}	
	if (!array_key_exists('SCRIPT_URI',$_SERVER)) {
		$sname=$_SERVER["REQUEST_URI"];
		if (strpos($sname,'?')!==false) $sname=substr($sname,0,strpos($sname,'?'));
		$_SERVER['SCRIPT_URI']=$sname;
	}
	if (!is_404()) return;
	remove_action('template_redirect', 'kpg_sfs_check_404s');
	$plink = $_SERVER['REQUEST_URI']; 
	if (strpos($plink,'?')!==false)  $plink=substr($plink,0,strpos($plink,'?'));
	if (strpos($plink,'#')!==false)  $plink=substr($plink,0,strpos($plink,'#'));
	$plink=basename($plink);
	if (strpos($plink."\t","wp-signup.php\t")===false 
		&& strpos($plink."\t","wp-register.php\t")===false // where is this?
		&& strpos($plink."\t","wp-comments-post.php\t")===false
		&& strpos($plink."\t","xmlrpc.php\t")===false) {
			return;
	}

	
	// check to see if we should even be here
	$options=kpg_sp_get_options();
	if (!array_key_exists('chkwplogin',$options) || $options['chkwplogin']!='Y') return;	
	
	$ip=kpg_get_ip();
    // check the white lists to prevent accidental blockage
	$wlist=$options['wlist'];
	if ((kpg_sp_searchi($ip,$wlist))) {
		return;
	}
	
	
	$stats=kpg_sp_get_stats();

	// have a bogus hit on a login or signup
	// register the bad ip
	$now=date('Y/m/d H:i:s',time() + ( get_option( 'gmt_offset' ) * 3600 ));
	$badips=$stats['badips'];
	if (!empty($ip)) $badips[$ip]=$now;
	asort($badips);
	$stats['badips']=$badips;
	// put into the history list
	$blog='';
	if (function_exists('is_multisite') && is_multisite()) {
		global $blog_id;
		if (!isset($blog_id)||$blog_id!=1) {
			$blog=$blog_id;
		}
	}
	$hist=$stats['hist'];
	$hist[$now]=array($ip,'-','-',$plink,"404 on $plink, added to reject cache.",$blog);
	$hist[$now][4]="404 on $plink, added to reject cache.";
	$stats['hist']=$hist;
    update_option('kpg_stop_sp_reg_stats',$stats);
    return;
}


/************************************************************
*  function kpg_sfs_check_admin()
* Checks to see if the current admin can login
*************************************************************/
register_activation_hook( __FILE__, 'kpg_sfs_check_admin' );
$sfs_check_activation=substr(md5(uniqid(rand(), true)), 16, 16);
function kpg_sfs_check_admin() {
	global $sfs_check_activation;
	$checkonactivate=false;
	// this confirms that the the current user is able to login
	// it refuses to install the plugin if the user fails spam tests
	if ($checkonactivate) { // no longer checking on activation - too many problems - Lock them out
		$ip=kpg_get_ip();
		//echo "Checking IP address for spam conflicts<br/>";
		$sfs_check_activation=substr(md5(uniqid(rand(), true)), 16, 16);
		kpg_sfs_check_load();
		if (kpg_sfs_check($sfs_check_activation,'Activation test',$ip)===false) {
			// break the installation
			echo "<br/>Your current configuration reports that you will be denied access as a spammer.<br/>
			Do not use this plugin until you can resolve this issue.
			If you are not a spammer, please copy the information above and leave it as a comment at http://www.blogseye.com
			<br/>
			This message is from the 'stop-spammer-registrations' plugin<br/>
			";
			die();
		}
	}
	$options=kpg_sp_get_options();
	$options['firsttime']='Y';
	kpg_sfs_reg_add_user_to_whitelist($options); // also saves options

}	

// this checks to see if there is an ip forwarded involved here and corrects the IP
function kpg_get_ip() {
	$ip=$_SERVER['REMOTE_ADDR'];
	if (array_key_exists('HTTP-X-FORWARDED-FOR',$_SERVER)) {
		$ip=$_SERVER('HTTP-X-FORWARDED-FOR');
	} else if (array_key_exists('X-FORWARDED-FOR',$_SERVER)) {
		$ip=$_SERVER('X-FORWARDED-FOR');
	} else {
		// search for lower case versions
		if (function_exists('getallheaders')) {
			$hlist=getallheaders();
			foreach ($hlist as $key => $data) {
				// can be X-FORWARDED-FOR or HTTP-X-FORWARDED-FOR upper or lower case
				if (strpos(strtoupper($key),'X-FORWARDED-FOR')!==false) { 
					$ip=$data;
					break;
				}
			}
		}
	}
	// some of these return a list of ips with commas - check for a list
	$ip=trim($ip);
	$ip=trim($ip,',');
	if (strpos($ip,',')!==false) {
		$ips=explode(',',$ip);
		$ip=trim($ips[count($ips)-1]); // gets the last ip - most likely to be spoofed?
	}
	if (empty($ip)) $ip=$_SERVER['REMOTE_ADDR']; // in case I screwed it up
	return $ip;
}
// still getting errors from bad data. I am now stripping all but ascii characters from 32 to 126
// email and user ideas are now plain 7 bit ascii as our founding fathers intended.
// there has to be a built-in php function to do this, but I did not find it. 
// There is an MB_ convert, but it did not work on all of my php hosts, so I think it may not be part of a standard install
function really_clean($s) {
	// try to get all non 7-bit things out of the string
	if (empty($s)) return $s;
	$ss=array_slice(unpack("c*", "\0".$s), 1);
	if (empty($ss)) return $s;
	$s='';
	for ($j=0;$j<count($ss);$j++) {
		if ($ss[$j]<127&&$ss[$j]>31) $s.=pack('C',$ss[$j]);
	}
	return $s;
}

function kpg_sfs_reg_check($actions,$comment) {
	$email=urlencode($comment->comment_author_email);
	$ip=$comment->comment_author_IP;
	$action="<a title=\"Check Stop Forum Spam (SFS)\" target=\"_stopspam\" href=\"http://www.stopforumspam.com/search.php?q=$ip\">Check SFS</a> |
	 <a title=\"Check Project HoneyPot\" target=\"_stopspam\" href=\"http://www.projecthoneypot.org/search_ip.php?ip=$ip\">Check HoneyPot</a>";
	$actions['check_spam']=$action;
	return $actions;
}
function kpg_sfs_reg_report($actions,$comment) {
	// need to add a new action to the list
	$email=urlencode($comment->comment_author_email);
	if (empty($email)){
		return $actions;
	}
	$options=kpg_sp_get_options();
	extract($options);
	
    $ID=$comment->comment_ID;
	$email=urlencode($comment->comment_author_email);
	$exst='';
	$uname=urlencode($comment->comment_author);
	$ip=$comment->comment_author_IP;
	// code added as per Paul at sto Forum Spam
	$content=$comment->comment_content;
	
	$evidence=$comment->comment_author_url;
	if (empty($evidence)) $evidence='';
	preg_match_all('@((https?://)?([-\w]+\.[-\w\.]+)+\w(:\d+)?(/([-\w/_\.]*(\?\S+)?)?)*)@',$content, $post, PREG_PATTERN_ORDER);
	if (is_array($post)&&is_array($post[1])) $urls1 = array_unique($post[1]); else $urls1 = array(); 
	//bbcode
	preg_match_all('/\[url=(.+)\]/iU', $content, $post, PREG_PATTERN_ORDER);
	if (is_array($post)&&is_array($post[0])) $urls2 = array_unique($post[0]); else $urls2 = array(); 
	$urls3=array_merge($urls1,$urls2);
    if (is_array($urls3)) $evidence.="\r\n".implode("\r\n",$urls3);	
 	$evidence=urlencode(trim($evidence,"\r\n"));
	if (strlen($evidence)>128) $evidence=substr($evidence,0,125).'...';
	$target=" target=\"_blank\" ";
	$href="href=\"http://www.stopforumspam.com/add.php?username=$uname&email=$email&ip_addr=$ip&evidence=$evidence&api_key=$apikey\" ";
	if (!empty($apikey)) {
		//$target="target=\"kpg_sfs_reg_if1\"";
		// make this the xlsrpc call.
		$href="href=\"#\"";
		$onclick="onclick=\"sfs_ajax_report_spam(this,'$ID','$blog_id','$ajaxurl');return false;\"";
	}
	if (!empty($email)) {
		$action="<a $exst title=\"Report to Stop Forum Spam (SFS)\" $target $href $onclick class='delete:the-comment-list:comment-$ID::delete=1 delete vim-d vim-destructive'>Report to SFS</a>";
		$actions['report_spam']=$action;
	}
	return $actions;
}
// hook the comment list with a "report Spam" filater
add_action('admin_menu', 'kpg_sfs_reg_admin_menus');
add_action('network_admin_menu', 'kpg_sfs_reg_net_admin_menus');

function kpg_sfs_reg_net_admin_menus() {
	if(!current_user_can('manage_network_options')) return;
	$options=kpg_sp_get_options();
    $muswitch=$options['muswitch'];
	kpg_sfs_reg_add_user_to_whitelist($options);
	// now install the admin stuff
	// if the muswitch is "Y" then we are in a network environment
	// it is a network, the muswitch is on and we can manage the network
	// this means we can install the options page on the network options page.
	
  add_submenu_page('settings.php', 'Stop Spammers', 'Stop Spammers', 'manage_options', 'adminstopspammersoptions', 'kpg_sfs_reg_control');
  add_submenu_page('settings.php', 'Stop Spammers History', 'Spammer History', 'manage_options', 'adminstopspammerstats', 'kpg_sfs_reg_stats_control');
 
	
	
	//add_options_page('Stop Spammers', 'Stop Spammers', 'manage_options','adminstopspammersoptions','kpg_sfs_reg_control');
	//add_options_page('Stop Spammers History', 'Spammer History', 'manage_options','adminstopspammerstats','kpg_sfs_reg_stats_control');
	add_action('mu_rightnow_end','kpg_sp_rightnow');
	add_filter('plugin_action_links', 'kpg_sp_plugin_action_links', 10, 2 );
	add_filter('comment_row_actions','kpg_sfs_reg_check',1,2);	
	add_filter('comment_row_actions','kpg_sfs_reg_report',1,2);	
}
function kpg_sfs_reg_admin_menus() {
	$options=kpg_sp_get_options();
    $muswitch=$options['muswitch'];
	if(!current_user_can('manage_options')) return;
	kpg_sfs_reg_add_user_to_whitelist($options);
	// now install the admin stuff
	// if the muswitch is "Y" then we are in a network environment and do not install
	if ($muswitch=='Y') {
		// we are in the normal admin menu
		// I am not sure that the muswitch can be turned on
		//echo "<!-- \r\n\r\n the muswitch is on! \r\n\r\n -->";
		return; // a network - only the admin can do it.
	}
	// this means we can install the options page on the network options page.
	add_options_page('Stop Spammers', 'Stop Spammers', 'manage_options','stopspammersoptions','kpg_sfs_reg_control');
	add_options_page('Stop Spammers History', 'Spammer History', 'manage_options','stopspammerstats','kpg_sfs_reg_stats_control');
	add_action('rightnow_end', 'kpg_sp_rightnow');
	add_filter( 'plugin_action_links', 'kpg_sp_plugin_action_links', 10, 2 );
	add_filter('comment_row_actions','kpg_sfs_reg_check',1,2);	
	add_filter('comment_row_actions','kpg_sfs_reg_report',1,2);	
}

function kpg_sfs_reg_add_user_to_whitelist($options) {
	$addtowhitelist=$options['addtowhitelist'];
	$wlist=$options['wlist'];
	$ip=kpg_get_ip();
	if ($addtowhitelist=='Y'&&!in_array($ip,$wlist)) {
		// add this ip to your white list
		$wlist[count($wlist)]=$ip;
		$options['wlist']=$wlist;
	}
	update_option('kpg_stop_sp_reg_options',$options);

}


function kpg_sp_plugin_action_links( $links, $file ) {
	$options=kpg_sp_get_options();
	extract($options);
	$muswitch=$options['muswitch'];
	if ( basename($file) == basename(__FILE__))  {
		$me=admin_url('options-general.php?page=stopspammersoptions');
		if (function_exists('is_multisite') && is_multisite() && $muswitch=='Y') {
			switch_to_blog(1);
			$me=get_admin_url( 1,'network/settings.php?page=adminstopspammerstats');
			restore_current_blog();
		}
		$links[] = "<a href=\"$me\">".__('Settings').'</a>';
	}
	return $links;
}

  
function kpg_sfs_reg_uninstall() {
	if(!current_user_can('manage_options')) {
		die('Access Denied');
	}
	delete_option('kpg_stop_sp_reg_options'); 
	delete_option('kpg_stop_sp_reg_stats'); 
	return;
}  



if ( function_exists('register_uninstall_hook') ) {
	register_uninstall_hook(__FILE__, 'kpg_sfs_reg_uninstall');
}


function kpg_sfs_reg_getafile($f) {
	// try this using Wp_Http
	if( !class_exists( 'WP_Http' ) )
		include_once( ABSPATH . WPINC. '/class-http.php' );
	$request = new WP_Http;
	$result = $request->request( $f );
	// see if there is anything there
	if (empty($result)) return '';
	
	if (is_array($result)) {
		$ansa=$result['body']; 
		return $ansa;
	}
	if (is_object($result) ) {
		$ansa='ERR: '.$result->get_error_message();
	}
	return '';
}

// special request to add to "right now section of the admin page
// WP 2.5+
function kpg_sp_rightnow() {
	$options=kpg_sp_get_options();
	extract($options);
	$muswitch=$options['muswitch'];
	$stats=kpg_sp_get_stats();
	extract($stats);
 	$me=admin_url('options-general.php?page=stopspammerstats');
    if (function_exists('is_multisite') && is_multisite() && $muswitch=='Y') {
		switch_to_blog(1);
		$me=get_admin_url( 1,'network/settings.php?page=adminstopspammerstats');
		restore_current_blog();
	}
	if ($spmcount>0) {
		// steal the akismet stats css format 
		// get the path to the plugin
		echo "<p><a style=\"font-style:italic;\" href=\"$me\">Stop Spammer Registrations</a> has prevented $spmcount spammers from registering or leaving comments.";
		if ($nobuy=='N' && $spmcount>10000) echo "  <a style=\"font-style:italic;\" href=\"http://www.blogseye.com/buy-the-book/\">Buy Keith Graham&apos;s Science Fiction Book</a>";
		echo"</p>";
	} else {
		echo "<p><a style=\"font-style:italic\" href=\"$me\">Stop Spammer Registrations</a> has not stopped any spammers, yet.";
		echo"</p>";
	}
}


function kpg_sp_searchi($needle,$haystack) {
	// ignore case in_array
	if (empty($needle)) return false;
	if (empty($haystack)) return false;
	if (!is_array($haystack)) return false;
	foreach($haystack as $val) {
		if (strtolower($val)==strtolower($needle)) return true;
	}
	return false;
}

function kpg_sp_search_ip($needle,$haystack) {
	// ignore case in_array
	if (empty($needle)) return false;
	if (empty($haystack)) return false;
	if (!is_array($haystack)) return false;
	foreach($haystack as $sip=>$val) {
		if (strtolower($sip)==strtolower($needle)) return true;
		if (kpg_ip_range($sip,$needle)) return true;
	}
	return false;
}
function kpg_sp_searchi_ip($needle,$haystack) {
	// ignore case in_array
	if (empty($needle)) return false;
	if (empty($haystack)) return false;
	if (!is_array($haystack)) return false;
	foreach($haystack as $sip) {
		if (strtolower($sip)==strtolower($needle)) return true;
		if (kpg_ip_range($sip,$needle)) return true;
	}
	return false;
}

function kpg_ip_range($ipr,$ip) {
	if (count(explode('.',$ip))!=4) return false;
	if (strpos($ipr,'/')===false) return false;
	$ips=explode('/',$ipr);
	if (count($ips)!=2) return false;
	$ip1=$ips[0];
	$m=$ips[1];
	if (count(explode('.',$ip1))!=4) return false;
	$mask=(pow(2,32)-1)-(pow(2,32-$m)-1);
	$i=abs(ip2long($ip));
	$r=abs(ip2long($ip1) & ($mask));
	$mask=$mask ^ -1;
	$r2=abs(ip2long($ip1) | ($mask));
	if ($r<$r2) { // since the result can be negative when converted to an integer and get screwed up
		if ($i>=$r&&$i<=$r2) return true;
		return false;
	}
	if ($i>=$r2&&$i<=$r) return true;
	return false;
}

function kpg_sp_searchK_i_del($needle,$haystack) {
	// ignore case in_array
	if (empty($needle)) return false;
	if (empty($haystack)) return false;
	if (!is_array($haystack)) return false;
	foreach($haystack as $key=>$value) {
		if (strtolower($key)==strtolower($needle)) return true;
	}
	return false;
}
function kpg_sp_searchL($needle,$haystack) {
	// search the end of a string case insensitive
	if (empty($needle)) return false;
	if (empty($haystack)) return false;
    foreach($haystack as $val) {
	    if (strpos(strtolower($val).'\t',strtolower($needle).'\t')!==false)  return true;
	}
	return false;
}
function kpg_sp_get_stats() {
	// check to see if we need to load the option redirector
	load_sfs_mu();
	$stats=get_option('kpg_stop_sp_reg_stats');
	if (empty($stats)||!is_array($stats)) $stats=array();
	$options=array(
		'badips'=>array(),
		'badems'=>array(),
		'goodips'=>array(),
		'hist'=>array(),
		
		'spcount'=>0,
		'spmcount'=>0,
				
		'cntjscript'=>0,
		'cntsfs'=>0,
		'cntreferer'=>0,
		
		'cntdisp'=>0,
		'cntrh'=>0,
		'cntdnsbl'=>0,
		
		'cntubiquity'=>0,
		'cntakismet'=>0,		
		'cntspamwords'=>0,
		
		'cntsession'=>0,
		'cntlong'=>0,
		'cntagent'=>0,
		
		'cnttld'=>0,
		'cntemdom'=>0,		
		'cntcacheip'=>0,

		'cntcacheem'=>0,
		'cnthp'=>0,		
		'cntbotscout'=>0,

		'cntblem'=>0,		
		'cntlongauth'=>0,
		'cntblip'=>0,

		'cntaccept'=>0,
		
		'cntpassed'=>0,		
		'cntwhite'=>0,	
		'cntgood'=>0,	
		
		'autoload'=>'N',
		'spmdate'=>'installation',

		'spdate'=>'last cleared'
	);
	$ansa=array_merge($options,$stats);
	if (!is_array($ansa['badips'])) $ansa['badips']=array();
	if (!is_array($ansa['badems'])) $ansa['badems']=array();
	if (!is_array($ansa['hist'])) $ansa['hist']=array();
	if (!is_array($ansa['goodips'])) $ansa['goodips']=array();
	if (!is_numeric($ansa['spcount'])) $ansa['spcount']=0;
	if (!is_numeric($ansa['spmcount'])) $ansa['spmcount']=0;
	if ($ansa['spcount']==0) {
		$ansa['spdate']=date('Y/m/d',time() + ( get_option( 'gmt_offset' ) * 3600 ));
		update_option('kpg_stop_sp_reg_stats',$ansa);
	}
	if ($ansa['spmcount']==0) {
		$ansa['spmdate']=date('Y/m/d',time() + ( get_option( 'gmt_offset' ) * 3600 ));
		update_option('kpg_stop_sp_reg_stats',$ansa);
	}
	if ($ansa['autoload']=='N') {
		delete_option('kpg_stop_sp_reg_stats');
		$ansa['autoload']='Y';
		add_option('kpg_stop_sp_reg_stats',$ansa, 0, 'no' );
	}

	return $ansa;
}

/*


*/
function kpg_sp_get_options() {
	// first see if we need to load the option redirecor
	load_sfs_mu();
	$opts=get_option('kpg_stop_sp_reg_options');
	if (empty($opts)||!is_array($opts)) $opts=array();
	$options=array(
		'wlist'=>array(),
		'blist'=>array(),
		'baddomains'=>array(),
		'badTLDs'=>array(),
		'apikey'=>'',
		'honeyapi'=>'',
		'botscoutapi'=>'',
		'accept'=>'Y',
		'nobuy'=>'N',
		'chkemail'=>'Y',
		'chkip'=>'Y',
		'chkjscript'=>'N',
		'chksfs'=>'Y',
		'chkreferer'=>'Y',
		'chkdisp'=>'Y',
		'redherring'=>'Y',
		'chkdnsbl'=>'Y',
		'chkubiquity'=>'Y',
		'chkakismet'=>'Y',
		'chkcomments'=>'Y',
		'chkspamwords'=>'N',
		'chklogin'=>'Y',
		'chksession'=>'Y',
		'sesstime'=>4,
		'chksignup'=>'Y',
		'chklong'=>'Y',
		'chkagent'=>'Y',
		'chkxmlrpc'=>'Y',
		'chkwpmail'=>'Y',
		'chkwplogin'=>'N',
		'chkadmin'=>'Y',
		'chk404'=>'Y',
		'addtowhitelist'=>'Y',
		'muswitch'=>'N',
		'sfsfreq'=>0,
		'hnyage'=>9999,
		'botfreq'=>0,
		'sfsage'=>9999,
		'hnylevel'=>5,
		'botage'=>9999,
		'kpg_sp_cache'=>25,
		'kpg_sp_cache_em'=>10,
		'kpg_sp_hist'=>25,
		'kpg_sp_good'=>2,
		'redirurl'=>'', 
		'redir'=>'N',
		'sleep'=>10,
		'logfilesize'=>50000,
		'autoload'=>'N',
		'firsttime'=>'Y',
		'rejectmessage'=>"Access Denied<br/>
This site is protected by the Stop Spammer Registrations Plugin.<br/>",
		'spamwords'=>array("-online","4u","4-u","adipex","advicer","baccarrat","blackjack","bllogspot","booker","byob","car-rental-e-site","car-rentals-e-site","carisoprodol","casino","chatroom","cialis","coolhu","credit-card-debt","credit-report","cwas","cyclen","cyclobenzaprine","dating-e-site","day-trading","debt-consolidation","debt-consolidation","discreetordering","duty-free","dutyfree","equityloans","fioricet","flowers-leading-site","freenet-shopping","freenet","gambling-","hair-loss","health-insurancedeals","homeequityloans","homefinance","holdem","hotel-dealse-site","hotele-site","hotelse-site","incest","insurance-quotes","insurancedeals","jrcreations","levitra","macinstruct","mortgagequotes","online-gambling","onlinegambling","ottawavalleyag","ownsthis","paxil","penis","pharmacy","phentermine","poker-chip","poze","pussy","rental-car-e-site","ringtones","roulette ","shemale","slot-machine","thorcarlson","top-site","top-e-site","tramadol","trim-spa","ultram","valeofglamorganconservatives","viagra","vioxx","xanax","zolus","ambien","poker","bingo","allstate","insurnce","work-at-home","workathome","home-based","homebased","weight-loss","weightloss","additional-income","extra-income","email-marketing","sibutramine","seo-","fast-cash")
		);
	$ansa=array_merge($options,$opts);
	if (!is_array($ansa['wlist'])) $ansa['wlist']=array();
	if (!is_array($ansa['blist'])) $ansa['blist']=array();
	if (!is_array($ansa['baddomains'])) $ansa['baddomains']=array();
	if (!is_array($ansa['badTLDs'])) $ansa['badTLDs']=array();
	if (empty($ansa['apikey'])) $ansa['apikey']='';
	if (empty($ansa['honeyapi'])) $ansa['honeyapi']='';
	if (empty($ansa['botscoutapi'])) $ansa['botscoutapi']='';
	if ($ansa['accept']!='Y') $ansa['accept']='N';
	if ($ansa['nobuy']!='Y') $ansa['nobuy']='N';
	if ($ansa['chkemail']!='Y') $ansa['chkemail']='N';
	if ($ansa['chkip']!='Y') $ansa['chkip']='N';
	if ($ansa['chkdisp']!='Y') $ansa['chkdisp']='N';
	if ($ansa['chksfs']!='Y') $ansa['chksfs']='N';
	if ($ansa['chkdnsbl']!='Y') $ansa['chkdnsbl']='N';
	if ($ansa['chkubiquity']!='Y') $ansa['chkubiquity']='N';
	if ($ansa['chkakismet']!='Y') $ansa['chkakismet']='N';
	if ($ansa['chkcomments']!='Y') $ansa['chkcomments']='N';
	if ($ansa['chklogin']!='Y') $ansa['chklogin']='N';
	if ($ansa['chkadmin']!='Y') $ansa['chklogin']='N';
	if ($ansa['chksignup']!='Y') $ansa['chksignup']='N';
	if ($ansa['chkxmlrpc']!='Y') $ansa['chkxmlrpc']='N';
	if ($ansa['chkwplogin']!='Y') $ansa['chkwplogin']='N';
	if ($ansa['muswitch']!='Y') $ansa['muswitch']='N';
	if (empty($ansa['kpg_sp_cache'])) $ansa['kpg_sp_cache']=25;
	if (empty($ansa['kpg_sp_cache_em'])) $ansa['kpg_sp_cache_em']=10;
	if (empty($ansa['kpg_sp_hist'])) $ansa['kpg_sp_hist']=25;
	if (empty($ansa['kpg_sp_good'])) $ansa['kpg_sp_good']=2;
    if (!is_numeric($ansa['kpg_sp_good'])) $ansa['kpg_sp_good']=2;
    if (!is_numeric(trim($ansa['logfilesize']))) $ansa['logfilesize']=50000;
	if (!is_array($ansa['spamwords'])) $ansa['spamwords']=array();
    if (!is_numeric($ansa['sesstime'])) $ansa['sesstime']=4;
	if ($ansa['autoload']=='N') {
		delete_option('kpg_stop_sp_reg_options');
		$ansa['autoload']='Y';
		add_option('kpg_stop_sp_reg_options',$ansa, 0, 'no' );
	}
	// need to check to see if the mu option has been set
	if (function_exists('is_multisite') && is_multisite()) {
		switch_to_blog(1);
		$options=get_option('kpg_stop_sp_reg_options');
		restore_current_blog();
		$muswitch=$options['muswitch'];
		$ansa['muswitch']=$muswitch;
	} else {
		$ansa['muswitch']='N';
	}
	return $ansa;
}
function sfs_handle_ajax_check($data) {
	// this does a call to the sfs site to check a known spammer
	// returns success or not
	$query="http://www.stopforumspam.com/api?ip=91.186.18.61";
	$check='';
	$check=kpg_sfs_reg_getafile($query);
	if (!empty($check)) {
	    $check=trim($check);
	    $check=trim($check,'0');
		if (substr($check,0,4)=="ERR:") {
			echo "Access to the Stop Forum Spam Database shows errors\r\n";
			echo "response was $check\r\n";
		}
		//Access to the Stop Forum Spam Database is working
		$n=strpos($check,'<response success="true">');
		if ($n===false) {
			echo "Access to the Stop Forum Spam Database is not working\r\n";
			echo "response was\r\n $check\r\n";
		} else {
			echo "Access to the Stop Forum Spam Database is working";
		}
	} else {
		echo "No response from the Stop Forum Spam AP Call\r\n";
	}
	return;
}
function sfs_handle_ajax_sub($data) {
	// get the stuff from the $_GET and call stop forum spam
	// this tages the stuff from the get and uses it to do the get from sfs
	// get the configuration items
	//kpg_ssp_global_setup();
	$options=kpg_sp_get_options();
	if (empty($options)) { // can't happen?
		echo "No Options set";
		exit();
	}
	//print_r($options);
	extract($options);
	// get the comment_id parameter	
	$comment_id=urlencode($_GET['comment_id']);
	if (empty($comment_id)) {
		echo "No comment id found";
		exit();
	}
	// need to pass the blog id also
	$blog='';
	$blog=$_GET['blog_id'];
	if ($blog!='') {
		switch_to_blog($blog);
	} 
	// get the comment
	$comment=get_comment( $comment_id, ARRAY_A );
	if (empty($comment)) {
		echo "No comment found for $comment_id";
		exit();
	}
	//print_r($comment);
	$email=urlencode($comment['comment_author_email']);
	$uname=urlencode($comment['comment_author']);
	$ip_addr=$comment['comment_author_IP'];
	// code added as per Paul at sto Forum Spam
	$content=$comment['comment_content'];
	$evidence=$comment['comment_author_url'];
	if ($blog!='') {
		restore_current_blog();
	}

	if (empty($evidence)) $evidence='';
	preg_match_all('@((https?://)?([-\w]+\.[-\w\.]+)+\w(:\d+)?(/([-\w/_\.]*(\?\S+)?)?)*)@',$content, $post, PREG_PATTERN_ORDER);
	$urls1=array();
	$urls2=array();
	if (is_array($post)&&is_array($post[1])) $urls1 = array_unique($post[1]); else $urls1 = array(); 
	//bbcode
	preg_match_all('/\[url=(.+)\]/iU', $content, $post, PREG_PATTERN_ORDER);
	if (is_array($post)&&is_array($post[0])) $urls2 = array_unique($post[0]); else $urls2 = array(); 
	$urls3=array_merge($urls1,$urls2);
    if (is_array($urls3)) $evidence.="\r\n".implode("\r\n",$urls3);	
 	$evidence=urlencode(trim($evidence,"\r\n"));
	if (strlen($evidence)>128) $evidence=substr($evidence,0,125).'...';
	
	if (empty($apikey)) {
		echo "Cannot Report Spam without API Key";
		exit();
	}
$hget="http://www.stopforumspam.com/add.php?ip_addr=$ip_addr&api_key=$apikey&email=$email&username=$uname&evidence=$evidence";
//echo $hget;
   $ret=@kpg_sfs_reg_getafile($hget);
	if (stripos($ret,'data submitted successfully')!==false) {
 		echo $ret;
	} else if (stripos($ret,'recent duplicate entry')!==false) {
 		echo ' recent duplicate entry ';
	} else {
 		echo $ret;
	}
}
	add_action('wp_ajax_nopriv_sfs_sub', 'sfs_handle_ajax_sub');	
	add_action('wp_ajax_sfs_sub', 'sfs_handle_ajax_sub');	
	add_action('wp_ajax_sfs_check', 'sfs_handle_ajax_check');	// used to check if ajax reporting works
/******************************************
* try ajax version of reporting
* right out of the api playbook
******************************************/
	add_action('admin_head', 'sfs_handle_ajax_new');
	function sfs_handle_ajax_new() {
		// this is the call that handles the call to ajax
		// step 1: Create the script that handles the action
?>
<script type="text/javascript" >
var sfs_ajax_who=null; //use this to update the message in the click
function sfs_ajax_report_spam(t,id,blog,url) {
	sfs_ajax_who=t;
	
	var data= {
		action: 'sfs_sub',
		blog_id: blog,
		comment_id: id,
		ajax_url: url
	}
	jQuery.get(ajaxurl, data, sfs_ajax_return_spam);
}
function sfs_ajax_return_spam(response) {
    sfs_ajax_who.innerHTML="Spam reported";
	sfs_ajax_who.style.color="green";
	sfs_ajax_who.style.fontWeight="bolder";
	//alert(response);
	if (response.indexOf('data submitted successfully')>0) {
		return false;
	}
	if (response.indexOf('recent duplicate entry')>0) {
		sfs_ajax_who.innerHTML="Spam Already reported";
		sfs_ajax_who.style.color="brown";
		sfs_ajax_who.style.fontWeight="bolder";
		return false;
	}
	sfs_ajax_who.innerHTML="Error reporting spam";
	sfs_ajax_who.style.color="red";
	sfs_ajax_who.style.fontWeight="bolder";
	alert(response);
	return false;
}
</script>
<?php		

	}
	
	
	
// debug functions
// change the debug=false to debug=true to start debugging.
// the plugin will drop a file sfs_debug_output.txt in the current directory (root, wp-admin, or network) 
// directory must be writeable or plugin will crash.

function sfs_errorsonoff($old=null) {
	$debug=true;  // change to true to debug, false to stop debugging.
	if (!$debug) return;
	if (empty($old)) return set_error_handler("sfs_ErrorHandler");
	restore_error_handler();
}
function sfs_ErrorHandler($errno, $errmsg, $filename, $linenum, $vars) {
	// write the answers to the file
	// we are only conserned with the errors and warnings, not the notices
	//if ($errno==E_NOTICE || $errno==E_WARNING) return false;
	if ($errno==2048) return; // wordpress throws deprecated all over the place.
	$serrno="";
	if (
	(strpos($filename,'stop-spam')===false)
	&&(strpos($filename,'sfr_mu')===false)
	&&(strpos($filename,'settings.php')===false)
	&&(strpos($filename,'options-general.php')===false)
	//&&(!function_exists('bbpress'))
	) return false;
	switch ($errno) {
		case E_ERROR: 
			$serrno="Fatal run-time errors. These indicate errors that can not be recovered from, such as a memory allocation problem. Execution of the script is halted. ";
			break;
		case E_WARNING: 
			$serrno="Run-time warnings (non-fatal errors). Execution of the script is not halted. ";
			break;
		case E_NOTICE: 
			$serrno="Run-time notices. Indicate that the script encountered something that could indicate an error, but could also happen in the normal course of running a script. ";
			break;
		default;
			$serrno="Unknown Error type $errno";
	}
	if (strpos($errmsg,'modify header information')) return false;
 
	$msg="
	Error number: $errno
	Error type: $serrno
	Error Msg: $errmsg
	File name: $filename
	Line Number: $linenum
	---------------------
	";
	// write out the error
	$f=fopen(dirname(__FILE__)."/sfs_debug_output.txt",'a');
	fwrite($f,$msg);
	fclose($f);
	return false;
}
// in bbpress the verify nonce function is not available for use in the red herring form.
// Red herring will not work in bbpress.
function kpg_verify_nonce($a,$b) {
	if (function_exists('wp_verify_nonce')) {
		return wp_verify_nonce($a, $b);
	}
	return false;
}

// load the optional files
// use includes so as to make the core plugin smaller when not working

function kpg_sfs_check_load() {
	// these are the spam checking functions
	require_once('includes/stop-spam-reg-checks.php');
}
function load_sfs_mu_options_file() {
	// the MU functions should not load if not a multisite install
	sfs_errorsonoff();
	require_once('includes/sfr-mu-options.php');
	sfs_errorsonoff('off');
}
function kpg_sfs_reg_control()  {
// loads when user needs to change the options
	sfs_errorsonoff();
	require_once("includes/stop-spam-reg-options.php");
	sfs_errorsonoff('off');
}
function kpg_sfs_reg_stats_control() {
// this displays the history and cache 
	sfs_errorsonoff();
	require_once("includes/stop-spam-reg-stats.php");
	sfs_errorsonoff('off');
}
function kpg_append_file($filename,$content) {
	// this writes content to a file in the uploads director in the 'stop-spammer-registrations' directory
	$path=WP_CONTENT_DIR.'/stop-spammer-registrations';
	if (!file_exists($path)) {
		mkdir($path);
		if (!file_exists($path)) return false;
	}
	$file=$path.'/'.$filename;
	$f=fopen($file,'a');
	fwrite($f,$content);
	fclose($f);
	return true;
}
function kpg_read_file($filename) {
	// read file
	$file=WP_CONTENT_DIR.'/stop-spammer-registrations/'.$filename;
	if (file_exists($file)) {
		return file_get_contents($file);
	}
	return "File not found";
}
function kpg_file_exists($filename) {
	$file=WP_CONTENT_DIR.'/stop-spammer-registrations/'.$filename;
	if (!file_exists($file)) return false;
	return filesize($file);
}
function kpg_file_delete($filename) {
	$file=WP_CONTENT_DIR.'/stop-spammer-registrations/'.$filename;
	
	return unlink($file);
}
?>