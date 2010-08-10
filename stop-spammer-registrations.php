<?PHP
/*
Plugin Name: Stop Spammer Registrations Plugin
Plugin URI: http://www.BlogsEye.com/
Description: Uses the Stop Forum Spam DB to prevent spammers from registering
Version: 1.2
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
	// this is the Stop Spammer Registrations functionality.
	// email as the email to validate
	
	// here we validate Just the email in this version. Later wei'll also validate the ip and username
	
	/*
	
	* http://www.stopforumspam.com/api?ip=91.186.18.61
    * http://www.stopforumspam.com/api?email=g2fsehis5e@mail.ru
    * http://www.stopforumspam.com/api?username=MariFoogwoogy

	*/
	
	// fix the email 
	$em=urlencode($email);
	$query="http://www.stopforumspam.com/api?email=$em";
	$ansa=kpg_stop_sp_reg_getafile($query);
	if (empty($ansa)) return $email;
	if (strpos($ansa,'<appears>yes</appears>')) {
		// record the last few guys that have  tried to spam
		$options=get_option('kpg_stop_sp_reg_options');
		if (empty($options)) $options=array();
		$spcount=0;
		if (array_key_exists('spcount',$options)) $spcount=$options['spcount'];
		$spcount++;
		$sphist=array();
		if (array_key_exists('sphist',$options)) $sphist=$options['sphist'];
		// add the bad spammer to the history list
		$sphist[count($sphist)]=htmlentities($email);
		if (count($sphist)>20) array_shift($sphist);
		$options['sphist']=$sphist;
		$options['spcount']=$spcount;
		update_option('kpg_stop_sp_reg_options', $options);
		
		return false;
	}

	return $email;
	
?>

<?php
}
function kpg_stop_sp_reg_control()  {
// this is the display of information about the page.

?>

<div class="wrap">
<h2>Stop Spammer Registrations Plugin</h2>
<h4>The Stop Spammer Registrations Plugin is installed and working correctly.</h4>
<p>This plugin Uses the Stop Forum Spam DB to prevent spammers from registering.</p>
<p>There are no configurations options. The plugin is on when it is installed and enabled. To turn it off just disable the plugin from the plugin menu.. </p>

<hr/>
<h3>Recent activity</h3)
<?php
	$options=get_option('kpg_stop_sp_reg_options');
	if (empty($options)) $options=array();
	$spcount=0;
	if (array_key_exists('spcount',$options)) $spcount=$options['spcount'];
	$sphist=array();
	if (array_key_exists('sphist',$options)) $sphist=$options['sphist'];
	if (empty($spcount)||empty($sphist)) {
		echo "<p>No activity Recorded.</p>";
	} else {
	echo "<p>Stop Spammer has stopped $spcount registrations.</p>
	<p>Recent blocked email registration attempts</p>";
	echo "<ul>";
	for ($j=0;$j<count($sphist);$j++) {
		$em=$sphist[$j];
		echo "<li><a href=\"http://www.stopforumspam.com/search?q=$em\" target=\"_blank\">$em</a></li>";
	}
	echo "</ul>";
	}

?>	
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
// no unistall because I have not created any meta data to delete.
function kpg_stop_sp_reg_init() {
   add_options_page('Stop Spammer Registrations', 'Stop Spammer Registrations', 'manage_options',__FILE__,'kpg_stop_sp_reg_control');
}
  // Plugin added to Wordpress plugin architecture
add_filter('is_email','kpg_stop_sp_reg_fixup');	
add_action('admin_menu', 'kpg_stop_sp_reg_init');

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


?>