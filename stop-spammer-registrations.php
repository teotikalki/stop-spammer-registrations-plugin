<?PHP
/*
Plugin Name: Stop Spammer Registrations Plugin
Plugin URI: http://www.BlogsEye.com/
Description: Uses the Stop Forum Spam DB to prevent spammers from registering
Version: 1.10
Author: Keith P. Graham
Author URI: http://www.BlogsEye.com/

This software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/************************************************************
* 	kpg_stop_sp_reg_fixup()
*	Uses the Stop Forum Spam DB to prevent spammers from registering
*
*************************************************************/
function kpg_stop_sp_reg_fixup($email) {
	if (empty($email)) return $email;
	// this is the Stop Spammer Registrations functionality.
	// email as the email to validate
	
	// only check in wp-comments-post.php or wp-login.php
	
	
	
	$sname=$_SERVER["SCRIPT_NAME"];
	
	if ((!strpos($sname,'wp-comments-post.php')) && (!strpos($sname,'wp-login.php'))&& (!strpos($sname,'wp-signup.php'))&& (!strpos($sname,'edit-comments.php'))) {
		return $email;
	}
	// here we validate 
	
	/*
	
	* http://www.stopforumspam.com/api?ip=91.186.18.61
    * http://www.stopforumspam.com/api?email=g2fsehis5e@mail.ru
    * http://www.stopforumspam.com/api?username=MariFoogwoogy
    *
	* combined
	*  http://www.stopforumspam.com/api?email=g2fsehis5e@mail.ru&ip=91.186.18.61
	
	*/
	// get the options
	$options=get_option('kpg_stop_sp_reg_options');
	if (empty($options)) $options=array();
	// cache bad cases
	$badips=array();
	$badems=array();
	$sphist=array();
	$gdems=array();
	$spcount=0;
	if (array_key_exists('spcount',$options)) $spcount=$options['spcount'];
	if (array_key_exists('sphist',$options)) $sphist=$options['sphist'];
	if (array_key_exists('badems',$options)) $badems=$options['badems'];
	if (array_key_exists('badips',$options)) $badips=$options['badips'];
	if (array_key_exists('gdems',$options)) $gdems=$options['gdems'];
	if (!is_numeric($spcount)) $spcount=0;
	
	// clean cache - get rid of older cache items. Need to recheck to see if they have appeared on stopfurumspam
	$badems=kpg_clear_old_cache($badems);
	$badips=kpg_clear_old_cache($badips);
	$gdems=kpg_clear_old_cache($gdems);
	if (empty($spcount)&&(!empty($sphist))) $spcount=count($sphist);
	
	// first check the ip address
	$ip=$_SERVER['REMOTE_ADDR']; 
	
	//kpg_logit(" '$email', '$username', '$ip' \r\n"); // turn on only during debugging
	
	// check the data
	$deny=false;
	// build the check
	$em=urlencode($email);
	if (array_key_exists($em,$gdems)) {
		return $email;
	}
	$query="http://www.stopforumspam.com/api?email=$em&ip=$ip";
	if (!empty($ip)) {
		$query=$query."&ip=$ip";
	}
	// check to see if the results have been cached
	$deny=false;
	if (array_key_exists($em,$badems)) {
		$badems[$em]=date("m/d/y H:i:s");
		$deny=true;
	} 
	if (array_key_exists($ip,$badips)) {
		$badips[$ip]=date("m/d/y H:i:s");
		$deny=true;
	} 
	if (!$deny) {
		$check=kpg_stop_sp_reg_getafile($query);
		//kpg_logit(" '$query', '$check' \r\n"); // turn on only during debugging
		if (strpos($check,'<appears>yes</appears>')) {
			$deny=true;
		} else {
			$deny=false;
		}
	} else {
		//kpg_logit(" found in cache \r\n");// turn on only during debugging
	}
	
	// clean cache - get rid of older cache items. Need to recheck to see if they have appeared on stopfurumspam
	$badems=kpg_clear_old_cache($badems);
	$badips=kpg_clear_old_cache($badips);
	$gdems=kpg_clear_old_cache($gdems);
	
	// it appears that there is no problem with this login record as a good login
	if (!$deny) {
		$gdems[$em]=date("m/d/y H:i:s");
		$options['badips']=$badips;
		$options['badems']=$badems;
		$options['gdems']=$gdems;
		update_option('kpg_stop_sp_reg_options', $options);
		return $email;
	}
	
	// update the history files.
	// record the last few guys that have  tried to spam
	// add the bad spammer to the history list
	$spcount++;
	$sphist[count($sphist)]=$email.'|'.date("m/d/y H:i:s").'|'.$ip.'|'.$_SERVER["SCRIPT_NAME"];
	if (count($sphist)>30) array_shift($sphist);
	$options['sphist']=$sphist;
	$options['spcount']=$spcount;
	// Cache the bad guy
	$badems[$em]=date("m/d/y H:i:s");
	if (!empty($ip)) $badips[$ip]=date("m/d/y H:i:s");
	// sort the array by date so that the most recent date is last
	$options['badips']=$badips;
	$options['badems']=$badems;
	update_option('kpg_stop_sp_reg_options', $options);
	
	sleep(5); // sleep for 5 seconds to annoy spammers and maybe delay next hit on stopforumspam.com
	return false;
}
function kpg_clear_old_cache($cache) {
	// the caches are an array that is limited to 60 users and 24 hours
	// it is int form of $cache[$key]=date;
	// unfortunately I made it date("m/d/y H:i:s");
	// it was a mistake storing the string date in the array and someday I will fix it. But for now I need to 
	// sort by the string. I will brute force it to integer to get it done
	foreach($cache as $key=>$value) {
		$t=strtotime($value);
		$cache[$key]=$t;
		if ($t<time()-24*60*60) {
			unset($cache[$key]);
		}
	}
	// sort the array by the time
	arsort($cache);
	while (count($cache)>60) {
		array_shift($cache);	
	}
	foreach($cache as $key=>$value) {
		$cache[$key]=date("m/d/y H:i:s",$cache[$key]);
	}
	return $cache;
}


function kpg_stop_sp_reg_control()  {
// this is the display of information about the page.
	if(!current_user_can('manage_options')) {
		die('Access Denied');
	}
	if (array_key_exists('kpg_stop_clear_cache',$_GET)) {
		// clear the cache
		$options=get_option('kpg_stop_sp_reg_options');
		if (empty($options)) $options=array();
		unset($options['badips']);
		unset($options['badems']);
		unset($options['gdems']);
		update_option('kpg_stop_sp_reg_options', $options);
	}
	if (array_key_exists('kpg_stop_clear_hist',$_GET)) {
		// clear the cache
		$options=get_option('kpg_stop_sp_reg_options');
		if (empty($options)) $options=array();
		unset($options['sphist']);
		unset($options['spcount']);
		update_option('kpg_stop_sp_reg_options', $options);
	}
	if (array_key_exists('kpg_stop_wl',$_GET)) {
		// add to whitelist
		$j=$_GET['kpg_stop_wl'];
		$options=get_option('kpg_stop_sp_reg_options');
		if (empty($options)) $options=array();
		$wlist=array();
		if (array_key_exists('wlist',$options)) $wlist=$options['wlist'];
		$sphist=array();
		if (array_key_exists('sphist',$options)) $sphist=$options['sphist'];
		$data=$sphist[$j];
		$ln=explode('|',$data);
		$em=trim($ln[0]);
		$dt=$ln[1];
		$ip=$ln[2];
		$ff=$ln[3];
		$id=$ln[4];
		$wlist[$em]=$ip;
		$options['wlist']=$wlist;
		$unset($sphist[$j]); //remove from history
		// now get the ip and the email out of the caches
		$badips=array();
		$gdems=array();
		if (array_key_exists('badems',$options)) $badems=$options['badems'];
		if (array_key_exists('badips',$options)) $badips=$options['badips'];
		unset($badems[$em]);
		unset($badips[$ip]);
		$options['badems']=$badems;
		$options['badips']=$badips;
		update_option('kpg_stop_sp_reg_options', $options);
	}

	$options=get_option('kpg_stop_sp_reg_options');
	if (empty($options)) $options=array();

?>

<div class="wrap">
  <h2>Stop Spammers  Plugin</h2>
  <h4>The Stop Spammers Plugin is installed and working correctly.</h4>
  <p>This plugin Uses the Stop Forum Spam DB to prevent spammers from registering or making comments.</p>
  <p>There are no configurations options. The plugin is on when it is installed and enabled. To turn it off just disable the plugin from the plugin menu.. </p>
  <p>If a registration or comment is rejected because of a hit on the StopForumSpam.com db, this plugin saves the email and IP. If you test the plugin using spammer credentials, it will remember that your IP address was associated with the spammer&apos;s email and deny future registrations and comments from your IP. If you feel compelled to test the plugin, you may lock yourself out of comments and the registration form. If you do get into a problem where you have cached a valid IP, click here: <a href="<?php echo $_SERVER["REQUEST_URI"]; ?>&kpg_stop_clear_cache=true">Clear the Cache.</a> </p>
  <p>The plugin also caches good emails, so if a spammer is unknown to StopForumSpam.com it will be entered into the good guys cache. Cached results are kept for 24 hours and then deleted.</p>
  <p>Since the plugin caches the IP address used by a spammer, it is possible for the plugin to reject possible comments from a legitimate user who just happens to come from an ISP who tolerates spammers.</p>
  <p>Click here to <a href="<?php echo $_SERVER["REQUEST_URI"]; ?>&kpg_stop_clear_hist=true">Clear History.</a>
  <p>Note: StopForumSpam.com limits checks to 5,000 per day for each IP so the plugin may stop validating on very busy sites. I have not seen this happen, yet. Results are cached in order to thwart repeated attempts. You may see your own email in the cache as spammers try to use it to leave comments. You may have to clear the cache to use your own email in a comment if that is the case.</p>
  
  <p>I have added a link on the WordPress comments maintenance so you can check a comment against the StopForumSpam.com database.</p> 

  <p>If you have a StopForumSpam.com API key you can report spam. This requires that you click the link where it will pre-fill the form for you. At that point you can enter your API key and submit. If you have previously logged in, it will fill in the API key for you and then you can submit the spam. You can easily get an API key after registering at StopForumSpam.com.</p>
   <hr/>
  <h3>Recent Activity</h3>
  <?php
	$options=get_option('kpg_stop_sp_reg_options');
	if (empty($options)) $options=array();
	$spcount=0;
	if (array_key_exists('spcount',$options)) $spcount=$options['spcount'];
	if (!is_numeric($spcount)) $spcount=0;

	$sphist=array();
	if (array_key_exists('sphist',$options)) $sphist=$options['sphist'];
	$badips=array();
	$badems=array();
	$gdems=array();
	if (array_key_exists('badems',$options)) $badems=$options['badems'];
	if (array_key_exists('badips',$options)) $badips=$options['badips'];
	if (array_key_exists('gdems',$options)) $gdems=$options['gdems'];
	if (empty($spcount)&&(!empty($sphist))) $spcount=count($sphist);

	if (empty($sphist)) {
		echo "<p>No activity Recorded.</p>";
	} else {
	echo "<p>Stop Spammers has stopped $spcount registration or comment attempts.</p>
	<p>Recent blocked email registration or comment attempts</p>";
	echo "<ul>";
	for ($j=0;$j<count($sphist);$j++) {
		$data=$sphist[$j];
		$ln=explode('|',$data);
		$em=trim($ln[0]);
		$dt=$ln[1];
		$ip=$ln[2];
		$ff=$ln[3];
		$id=$ln[4];
		if (!empty($em)) {
			echo "<li style=\"font-size:.8em;\"><a href=\"http://www.stopforumspam.com/search?q=$em\" target=\"_stopspam\">$em</a>";
			if (!empty($dt)) echo "; $dt";
			if (!empty($ip)) echo "; <a href=\"http://www.stopforumspam.com/search?q=$ip\" target=\"_stopspam\">$ip</a>";
			if (!empty($ff)) echo "; $ff";
			if (!empty($id)) echo "; <a href=\"".$_SERVER["REQUEST_URI"]."&kpg_stop_wl=$j\">white list</a>";
			echo "</li>";
		}
	}
	echo "</ul>";
	}
	$badems=kpg_clear_old_cache($badems);
	$badips=kpg_clear_old_cache($badips);
	$gdems=kpg_clear_old_cache($gdems);
   if (!(empty($badems)&&empty($badips)&&empty($gdems))) {

?>
  <h3>Cached Values (last 24 hours)</h3>
  <table align="center" width="80%"  >
    <tr>
      <td width="35%" align="center">Rejected Emails</td>
      <td width="30%" align="center">Rejected IPs</td>
      <td width="35%" align="center">Passed Emails</td>
    </tr>
    <tr>
      <td  style="border: 1px solid black;font-size:.75em;padding:3px;" valign="top"><?php
		foreach ($badems as $key => $value) {
			//echo "$key; Date: $value<br/>\r\n";
			$key=urldecode($key);
			echo "<a href=\"http://www.stopforumspam.com/search?q=$key\" target=\"_stopspam\">$key: $value</a><br/>";
		}
	?></td>
      <td  style="border: 1px solid black;font-size:.75em;padding:3px;" valign="top"><?php
		foreach ($badips as $key => $value) {
			//echo "$key; Date: $value<br/>\r\n";
			echo "<a href=\"http://www.stopforumspam.com/search?q=$key\" target=\"_stopspam\">$key: $value</a><br/>";
		}
	?></td>
      <td  style="border: 1px solid black;font-size:.75em;padding:3px;" valign="top"><?php
		foreach ($gdems as $key => $value) {
			//echo "$key; $value<br/>\r\n";
			$key=urldecode($key);
			echo "<a href=\"http://www.stopforumspam.com/search?q=$key\" target=\"_stopspam\">$key: $value</a><br/>";
		}
	?></td>
    </tr>
  </table>
  <?PHP
    }
	
?>
  <hr/>
  <p>This plugin is free and I expect nothing in return. However, a link on your blog to one of my personal sites would be appreciated.</p>
  <p>Keith Graham</p>
  <p><a target="_blank" href="http://www.WestNyackHoney.com">West Nyack Honey</a> (I keep bees and sell the honey)<br />
   <a target="_blank" href="http://www.cthreepo.com/blog">Wandering Blog </a> (My personal Blog) <br />
    <a target="_blank"  href="http://www.cthreepo.com">Resources for Science Fiction</a> (Writing Science Fiction) <br />
    <a target="_blank"  href="http://www.jt30.com">The JT30 Page</a> (Amplified Blues Harmonica) <br />
    <a target="_blank"  href="http://www.harpamps.com">Harp Amps</a> (Vacuum Tube Amplifiers for Blues) <br />
    <a target="_blank"  href="http://www.blogseye.com">Blog&apos;s Eye</a> (PHP coding) <br />
    <a target="_blank"  href="http://www.cthreepo.com/bees">Bee Progress Beekeeping Blog</a> (My adventures as a new beekeeper) </p>
</div>
<?php
}
function kpg_stop_sp_reg_check($actions,$comment) {
	$email=urlencode($comment->comment_author_email);
	$action="<a target=\"_stopspam\" href=\"http://www.stopforumspam.com/search.php?q=$email\">Check at StopFurumSpam</a>";
	$actions['check_spam']=$action;
	return $actions;


}
function kpg_stop_sp_reg_report($actions,$comment) {
	// need to add a new action to the list
	$email=urlencode($comment->comment_author_email);
	$uname=urlencode($comment->comment_author);
	$ip=$comment->comment_author_IP;
	$evidence=get_bloginfo('url');
	$evidence=urlencode($evidence);
	$action="<a target=\"_stopspam\" href=\"http://www.stopforumspam.com/add?username=$uname&email=$email&ip_addr=$ip&evidence=$evidence\">Report to StopForumSpam</a>";
	$actions['report_spam']=$action;
	return $actions;

}




function kpg_stop_sp_reg_init() {
   add_options_page('Stop Spammers', 'Stop Spammers', 'manage_options',__FILE__,'kpg_stop_sp_reg_control');
}
  
function kpg_stop_sp_reg_uninstall() {
	if(!current_user_can('manage_options')) {
		die('Access Denied');
	}
	delete_option('kpg_stop_sp_reg_options'); 
	return;
}  

// hook the comment list with a "report Spam" filater
add_filter('comment_row_actions','kpg_stop_sp_reg_check',1,2);	
add_filter('comment_row_actions','kpg_stop_sp_reg_report',1,2);	

add_filter('is_email','kpg_stop_sp_reg_fixup');	
add_action('admin_menu', 'kpg_stop_sp_reg_init');
if ( function_exists('register_uninstall_hook') ) {
	register_uninstall_hook(__FILE__, 'kpg_stop_sp_reg_uninstall');
}


function kpg_stop_sp_reg_getafile($f) {
	// try this using Wp_Http
	if( !class_exists( 'WP_Http' ) )
		include_once( ABSPATH . WPINC. '/class-http.php' );
	$request = new WP_Http;
	$result = $request->request( $f );
	// see if there is anything there
	if (empty($result)) return '';
	$ansa=$result['body']; 
	return $ansa;
}

// used only during debugging - please ignore the man behind the curtain.
/* function kpg_logit($line) {
	$f=fopen('log.txt','a');
	fwrite($f,$line);
	fclose($f);
	return;

}
*/
?>