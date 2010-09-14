<?PHP
/*
Plugin Name: Stop Spammer Registrations Plugin
Plugin URI: http://www.BlogsEye.com/
Description: Uses the Stop Forum Spam DB to prevent spammers from registering
Version: 1.4
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
	
	// here we validate Just the email in this version. Later wei'll also validate the ip and username
	
	/*
	
	* http://www.stopforumspam.com/api?ip=91.186.18.61
    * http://www.stopforumspam.com/api?email=g2fsehis5e@mail.ru
    * http://www.stopforumspam.com/api?username=MariFoogwoogy

	*/
	// get the options
	$options=get_option('kpg_stop_sp_reg_options');
	if (empty($options)) $options=array();
	// cache bad cases
	$badips=array();
	$badems=array();
	$badids=array();
	$sphist=array();
	$spcount=0;
	if (array_key_exists('spcount',$options)) $spcount=$options['spcount'];
	if (array_key_exists('sphist',$options)) $sphist=$options['sphist'];
	if (array_key_exists('badems',$options)) $badems=$options['badems'];
	if (array_key_exists('badips',$options)) $badips=$options['badips'];
	if (array_key_exists('badids',$options)) $badids=$options['badids'];
	
	
	// first check the ip address
	$ip=$_SERVER['REMOTE_ADDR'];
	$username=''; // log
	if (!empty($_POST)) {
		// user_login usr_email 
		if (array_key_exists('user_login',$_POST)) $username=$_POST['user_login'];
	}
	//kpg_logit(" '$email', '$username', '$ip' \r\n"); // turn on only during debugging
	
	// check the data
	$deny=false;
	// build the check
	$em=urlencode($email);
	$username=urlencode($username);
	$query="http://www.stopforumspam.com/api?email=$em";
	if (!empty($ip)) {
		$query=$query."&ip=$ip";
	}
	if (!empty($username)) {
		$query=$query."&username=$username";
	}
	// check to see if the results have been cached
	$deny=false;
	if (array_key_exists($em,$badems)) {
		$badems[$em]=date("m/d/y h:i:s A");
		$deny=true;
	} 
	if (array_key_exists($ip,$badips)) {
		$badips[$ip]=date("m/d/y h:i:s A");
		$deny=true;
	} 
	if (array_key_exists($username,$badids)) {
		$badids[$username]=date("m/d/y h:i:s A");
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
	if (!$deny) return $email;
	
	// update the history files.
	// record the last few guys that have  tried to spam
	// add the bad spammer to the history list
	$spcount++;
	$sphist[count($sphist)]=$email.'|'.date("m/d/y h:i:s A").'|'.$ip.'|'.$username;
	if (count($sphist)>30) array_shift($sphist);
	$options['sphist']=$sphist;
	$options['spcount']=$spcount;
	// Cache the bad guy
	$badems[$em]=date("m/d/y h:i:s A");
	if (!empty($ip)) $badips[$ip]=date("m/d/y h:i:s A");
	if (!empty($username)) $badids[$username]=date("m/d/y h:i:s A");
	// sort the array by date so that the most recent date is last
	arsort($badems);
	arsort($badips);
	arsort($badids);
	// update the caches
	if (count($badems)>60) array_pop($badems);
	if (count($badips)>60) array_pop($badips);
	if (count($badids)>60) array_pop($badids);
	$options['badids']=$badids;
	$options['badips']=$badips;
	$options['badems']=$badems;
	update_option('kpg_stop_sp_reg_options', $options);
	
	return false;
	
?>

<?php
}


function kpg_stop_sp_reg_control()  {
// this is the display of information about the page.
	if(!current_user_can('manage_options')) {
		die('Access Denied');
	}
	if (array_key_exists('kpg_stop_clear',$_GET)) {
		// clear the cache
		$options=get_option('kpg_stop_sp_reg_options');
		if (empty($options)) $options=array();
		unset($options['badids']);
		unset($options['badips']);
		unset($options['badems']);
		update_option('kpg_stop_sp_reg_options', $options);
	}
?>

<div class="wrap">
<h2>Stop Spammer Registrations Plugin</h2>
<h4>The Stop Spammer Registrations Plugin is installed and working correctly.</h4>
<p>This plugin Uses the Stop Forum Spam DB to prevent spammers from registering or making comments.</p>
<p>There are no configurations options. The plugin is on when it is installed and enabled. To turn it off just disable the plugin from the plugin menu.. </p>
<p>If a registration is rejected because of a hit on the StopForumSpam.com db, this plugin caches the userid and IP. If you test the plugin using spammer credentials, it will remember that your IP address was associated with the spammer&apos;s email and deny future registrations from your IP. If you feel compelled to test the plugin, you may lock yourself out of comments and the registration form. If you do get into a problem where you have cached a valid IP, click here: <a href="<?php echo $_SERVER["REQUEST_URI"]; ?>&kpg_stop_clear=true">Clear the Cache.</a></p>
<p>Note: StopForumSpam.com limits checks to 5,000 per day for each IP so the plugin may stop validating on very busy sites. I have not seen this happen, yet. Results are cached in order to thwart repeated attempts. You may see your own email in the cache as spammers try to use it to leave comments. You may have to clear the cache to use your own email in that case.</p>
<hr/>
<h3>Recent activity</h3)
<?php
	$options=get_option('kpg_stop_sp_reg_options');
	if (empty($options)) $options=array();
	$spcount=0;
	if (array_key_exists('spcount',$options)) $spcount=$options['spcount'];
	$sphist=array();
	if (array_key_exists('sphist',$options)) $sphist=$options['sphist'];
	$badips=array();
	$badems=array();
	$badids=array();
	if (array_key_exists('badems',$options)) $badems=$options['badems'];
	if (array_key_exists('badips',$options)) $badips=$options['badips'];
	if (array_key_exists('badids',$options)) $badids=$options['badids'];

	if (empty($spcount)||empty($sphist)) {
		echo "<p>No activity Recorded.</p>";
	} else {
	echo "<p>Stop Spammer has stopped $spcount registrations.</p>
	<p>Recent blocked email registration attempts</p>";
	echo "<ul>";
	for ($j=0;$j<count($sphist);$j++) {
		$data=$sphist[$j];
		$ln=explode('|',$data);
		$em=trim($ln[0]);
		$dt=$ln[1];
		$ip=$ln[2];
		$un=$ln[3];
		$pw=$ln[4];
		if (!empty($em)) {
			echo "<li style=\"font-size:.8em;\"><a href=\"http://www.stopforumspam.com/search?q=$em\" target=\"_blank\">email: $em</a>";
			if (!empty($dt)) echo "; Date: $dt";
			if (!empty($ip)) echo "; <a href=\"http://www.stopforumspam.com/search?q=$ip\" target=\"_blank\">IP: $ip</a>";
			if (!empty($un)) echo "; <a href=\"http://www.stopforumspam.com/search?q=$un\" target=\"_blank\">User Name: $un</a>";
			echo "</li>";
		}
	}
	echo "</ul>";
	}

?>	<h3>Cached Values</h3>
<table  >
	<tr>
	<td  style="border: 1px solid black;font-size:.75em;"><?php
		foreach ($badems as $key => $value) {
        echo "Email: $key; Date: $value<br/>\r\n";
		}
	?></td>
	<td  style="border: 1px solid black;font-size:.75em;"><?php
		foreach ($badips as $key => $value) {
			echo "ip: $key; Date: $value<br/>\r\n";
		}
	?></td>
	<td  style="border: 1px solid black;font-size:.75em;"><?php
		foreach ($badids as $key => $value) {
			echo "User name: $key; Date: $value<br/>\r\n";
		}
	?></td>
	</tr>
</table>
<hr/>

<p>This plugin is free and I expect nothing in return. However, a link on your blog to one of my personal sites would be appreciated.</p>


<p>Keith Graham</p>
<p><a target="_blank" href="http://www.cthreepo.com/blog">Wandering Blog </a>(My personal Blog) <br />
  <a target="_blank"  href="http://www.cthreepo.com">Resources for Science Fiction</a> (Writing Science Fiction) <br />
  <a target="_blank"  href="http://www.jt30.com">The JT30 Page</a> (Amplified Blues Harmonica) <br />
  <a target="_blank"  href="http://www.harpamps.com">Harp Amps</a> (Vacuum Tube Amplifiers for Blues) <br />
  <a target="_blank"  href="http://www.blogseye.com">Blog&apos;s Eye</a> (PHP coding) <br />
  <a target="_blank"  href="http://www.cthreepo.com/bees">Bee Progress Beekeeping Blog</a> (My adventures as a new beekeeper) </p>
</div

<?php
}


function kpg_stop_sp_reg_init() {
   add_options_page('Stop Spammer Registrations', 'Stop Spammer Registrations', 'manage_options',__FILE__,'kpg_stop_sp_reg_control');
}
  
function kpg_stop_sp_reg_uninstall() {
	if(!current_user_can('manage_options')) {
		die('Access Denied');
	}
	delete_option('kpg_stop_sp_reg_options'); 
	return;
}  

add_filter('is_email','kpg_stop_sp_reg_fixup');	
add_action('admin_menu', 'kpg_stop_sp_reg_init');
if ( function_exists('register_uninstall_hook') ) {
	register_uninstall_hook(__FILE__, 'kpg_stop_sp_reg_uninstall');
}

function kpg_stop_sp_reg_getafile($f) {
   // uses fopen or curl depending on "allow_url_fopen = On" being avaialble
   //first test to see if the ini option allow_url_fopen is on or off
	if (ini_get('allow_url_fopen')) {
		// returns the string value of a file using 
		$rssfile=file($f);
		$rssfile=implode("\n",$rssfile); // in case it is now one long string
		return $rssfile;
	}
	// try using curl instead to see if it works
    $ch = curl_init($f);
	// Set cURL options
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
	$ansa = curl_exec($ch);
	curl_close($ch);
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