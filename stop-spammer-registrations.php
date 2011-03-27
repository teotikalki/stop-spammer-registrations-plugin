<?PHP
/*
Plugin Name: Stop Spammer Registrations Plugin
Plugin URI: http://www.BlogsEye.com/
Description: Uses the Stop Forum Spam DB to prevent spammers from registering
Version: 1.15
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
	
	$author='';
	if (array_key_exists('author',$_POST)) $author=$_POST['author'];
	$sname='.'.$_SERVER["SCRIPT_NAME"];
	$whodunnit='';
	$apikey='';
	if (
		(!strpos($sname,'wp-comments-post.php')) && 
		(!strpos($sname,'wp-login.php'))&& 
		(!strpos($sname,'wp-signup.php'))&& 
		(!strpos($sname,'xmlrpc.php'))&& 
		(!strpos($sname,'ms.php'))&& 
		(!strpos($sname,'edit-comments.php'))
	) {
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
	if (empty($options)||!is_array($options)) $options=array();
	// cache bad cases
	$badips=array();
	$badems=array();
	$sphist=array();
	$gdems=array();
	$wlist=array();
	$honeyapi='';
	$spcount=0;
	$accept='Y';
	$chkemail='Y';
	if (array_key_exists('spcount',$options)) $spcount=$options['spcount'];
	if (array_key_exists('accept',$options)) $accept=$options['accept'];
	if (array_key_exists('sphist',$options)) $sphist=$options['sphist'];
	if (array_key_exists('badems',$options)) $badems=$options['badems'];
	if (array_key_exists('badips',$options)) $badips=$options['badips'];
	if (array_key_exists('gdems',$options)) $gdems=$options['gdems'];
	if (array_key_exists('wlist',$options)) $wlist=$options['wlist'];
	if (array_key_exists('honeyapi',$options)) $honeyapi=$options['honeyapi'];
	if (array_key_exists('apikey',$options)) $apikey=$options['apikey'];
	if (array_key_exists('chkemail',$options)) $chkemail=$options['chkemail'];
	
	if ($accept!='Y') $accept='N';
	if ($chkemail!='Y') $chkemail='N';
	if (!is_array($badips)) $badips=array();
	if (!is_array($badems)) $badems=array();
	if (!is_array($sphist)) $sphist=array();
	if (!is_array($gdems)) $gdems=array();
	if (!is_array($wlist)) $wlist=array();
	if (empty($honeyapi)) $honeyapi='';
	if (empty($apikey)) $apikey='';
	
	if (!is_numeric($spcount)) $spcount=0;

	// clean cache - get rid of older cache items. Need to recheck to see if they have appeared on stopfurumspam
	$badems=kpg_clear_old_cache($badems);
	$badips=kpg_clear_old_cache($badips);
	$gdems=kpg_clear_old_cache($gdems);
	if (empty($spcount)&&(!empty($sphist))) $spcount=count($sphist);
	
	// first check the ip address
	$ip=$_SERVER['REMOTE_ADDR']; 
	$accept_head=false; 
	if (array_key_exists('HTTP_ACCEPT',$_SERVER)) $accept_head=true; // real browsers send HTTP_ACCEPT
	if (in_array($ip,$wlist)) {
		return $email;
	}
	if (in_array($email,$wlist)) {
		return $email;
	}

	//kpg_logit(" '$email', '$username', '$ip' \r\n"); // turn on only during debugging
	
	// check the data
	$deny=false;
	// build the check
	$em=urlencode($email);
	if (array_key_exists($em,$gdems)) {
		return $email;
	}
	if ($accept=='Y'&&!$accept_head) {
		// no accept header - real browsers send the HTTP_ACCEPT header
		$whodunnit='No Accept header';
		$deny=true;
	}
	$query="http://www.stopforumspam.com/api?ip=$ip";
	if ($chkemail=='Y') {
		$query=$query."&email=$email";
	}
	// check to see if the results have been cached
	$deny=false;
	if (array_key_exists($em,$badems)) {
		$badems[$em]=date("m/d/y H:i:s");
		$deny=true;
		$whodunnit='Cached bad email';
	} 
	if (array_key_exists($ip,$badips)) {
		$badips[$ip]=date("m/d/y H:i:s");
		$whodunnit='Cached bad ip';
		$deny=true;
	} 
	if (!$deny&&$apikey!='') {
		$check=kpg_stop_sp_reg_getafile($query);
		//kpg_logit(" '$query', '$check' \r\n"); // turn on only during debugging
		$n=strpos($check,'<appears>yes</appears>');
		if ($n) {
			// get lastseen and frequency
			// ip is always forst
			/*
				<response success="true">
					<type>ip</type>
					<appears>no</appears>
					<frequency>0</frequency>
					<type>email</type>
					<appears>yes</appears>

					<lastseen>2011-03-27 03:26:11</lastseen>
					<frequency>8</frequency>
				</response>

*/
			$k=strpos($check,'<lastseen>',$n);
			$k+=10;
			$j=strpos($check,'</lastseen>',$k);
			$lastseen='error';
			if (($j-$k)>12&&($j-$k)<24) $lastseen=substr($check,$k,$j-$k); // should be about 20 characters
			$k=strpos($check,'<frequency>',$n);
			$k+=11;
			$j=strpos($check,'</frequency',$k);
			$frequency='error';
			if (($j-$k)>0&&($j-$k)<7) $frequency=substr($check,$k,$j-$k); // should be a number greater than 0 and probablu no more than a few thousand.
			// have freqency and lastseen date - make these options in next release
				$deny=true;
				$whodunnit="SFS hit, last=$lastseen, freq=$frequency";
		} 
	} 
	if (!$deny&&$honeyapi!='') {
		// do a further check on project honeypot here
		$lookup = $honeyapi . '.' . implode('.', array_reverse(explode ('.', $ip ))) . '.dnsbl.httpbl.org';
		$result = explode( '.', gethostbyname($lookup));
		if ($result[0] == 127) {
			// query successful
			// 127 is a good lookup
			//  [3] = type of threat - we are only interested in comment spam at this point - if user demand I will change.
			// [2] is the threat level. 25 is recommended
			// [1] is numbr of days since last report
			//if ($result[2]>25&&$result[3]==4) { // 4 - comment spam, threat level 25 is average. 
			if ($result[1]<60&&$result[2]>5&&$result[3]>=4) { // 4 - comment spam, threat level 25 is average. 
				$deny=true;
				$whodunnit='HTTP:bl hit: Age:'.$result[1].', Threat Level:'.$result[2].', Threat Type '.$result[3];
			} 
		} 
	}
	
	// clean cache - get rid of older cache items. Need to recheck to see if they have appeared on stopfurumspam
	$badems=kpg_clear_old_cache($badems);
	$badips=kpg_clear_old_cache($badips);
	$gdems=kpg_clear_old_cache($gdems);
	
	// it appears that there is no problem with this login record as a good login
	if (!$deny) {
	    // add the check and report
		$chk=" <a href=\"http://www.stopforumspam.com/search?q=$ip\" target=\"_stopspam\">Check</a>";
		if (!empty($apikey) && !empty($author)) {
			$surl='email='.urlencode($email).'&ip_addr='.$ip.'&username='.urlencode($author).'&api_key='.$apikey;
			$chk.=', <a href="http://www.stopforumspam.com/add?'.$surl.'"  target="_stopspam">Report</a>';
		}
		$gdems[$em]=date("m/d/y H:i:s");// don't add the check in for now until we figure out how to sort the thing ---.$chk;
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
	$sphist[count($sphist)]=$email.'|'.date("m/d/y H:i:s").'|'.$ip.'|'.$_SERVER["SCRIPT_NAME"]." - $whodunnit";
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
		$dt=$value;
		if (is_array($value)) {
			$dt=$value['date'];
			$ip=$value['ip'];
			$usrid=$value['usrid'];
			$evidence=$value['evidence'];
		}
		$t=strtotime($dt);
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
		$cache[$key]=date("m/d/y H:i:s",$value);
	}
	return $cache;
}


function kpg_stop_sp_reg_control()  {
// this is the display of information about the page.
    $apikey='';
    $honeyapi='';
    $chkemail='Y';
	$wlist=array();
	if(!current_user_can('manage_options')) {
		die('Access Denied');
	}
	$nonce='';
	if (array_key_exists('nonce',$_POST)) $nonce=$_POST['nonce'];

	$options=get_option('kpg_stop_sp_reg_options');
	if (empty($options)) $options=array();
	$accept='Y';
	
	if (array_key_exists('kpg_stop_spammers_control',$_POST)
			&&wp_verify_nonce($_POST['kpg_stop_spammers_control'],'kpgstopspam_update')) { 
		if (array_key_exists('kpg_stop_clear_cache',$_POST)) {
			// clear the cache
			unset($options['badips']);
			unset($options['badems']);
			unset($options['gdems']);
			update_option('kpg_stop_sp_reg_options', $options);
			echo "<h2>Cache Cleared</h2>";
		}
		if (array_key_exists('kpg_stop_clear_hist',$_POST)) {
			// clear the cache
			unset($options['sphist']);
			unset($options['spcount']);
			update_option('kpg_stop_sp_reg_options', $options);
			echo "<h2>History Cleared</h2>";
		}
		if (array_key_exists('action',$_POST)) {
			// check the nonce
			//echo "got action<br/>";
			//echo $_POST['kpg_stop_spammers_control'];
			//echo "in action<br/>";
			
			if (array_key_exists('chkemail',$_POST)) {
				$chkemail=stripslashes($_POST['chkemail']);
			} else {
				$chkemail='N';
			}
			$options['chkemail']=$chkemail;
			if (array_key_exists('accept',$_POST)) {
				$accept=stripslashes($_POST['accept']);
			} else {
				$accept='N';
			}
			$options['accept']=$accept;
			if (array_key_exists('apikey',$_POST)) $apikey=stripslashes($_POST['apikey']);
			$options['apikey']=$apikey;
			if (array_key_exists('honeyapi',$_POST)) $honeyapi=stripslashes($_POST['honeyapi']);
			$options['honeyapi']=$honeyapi;
			if (array_key_exists('wlist',$_POST)) {
				$wlist=stripslashes($_POST['wlist']);
			    $wlist=str_replace("\r\n","\n",$wlist);
			    $wlist=str_replace("\r","\n",$wlist);
				$wlist=explode("\n",$wlist);
				$options['wlist']=$wlist;				
				for ($k=0;$k<count($wlist);$k++) {
					$wlist[$k]=trim($wlist[$k]);
				}	
			}
			update_option('kpg_stop_sp_reg_options',$options);
			echo "<h2>Options Updated</h2>";
		}
		
	}

	if (array_key_exists('accept',$options)) $accept=$options['accept'];
	if ($accept!='Y') $accept='N';
	if (array_key_exists('apikey',$options)) $apikey=$options['apikey'];
	if (array_key_exists('honeyapi',$options)) $honeyapi=$options['honeyapi'];
	if (array_key_exists('wlist',$options)) $wlist=$options['wlist'];
    $nonce=wp_create_nonce('kpgstopspam_update');

?>
<div class="wrap">
  <h2>Stop Spammers Plugin</h2>
  <h4>The Stop Spammers Plugin is installed and working correctly.</h4>
  <p>This plugin Uses the Stop Forum Spam DB to prevent spammers from registering or making comments.</p>
  <p>Watch the video! <a href="http://www.youtube.com/watch?v=EKrUX0hHAx8" target="_blank">http://www.youtube.com/watch?v=EKrUX0hHAx8</a>. The video shows one of my plugins that anti-spam cops use. They run honey pots or sites that do nothing but attract spammers. These sites report as many as 500 spammers per hour to the same database that this plugin checks.</p>
  <p>The plugin is on when it is installed and enabled. To turn it off just disable the plugin from the plugin menu.. </p>
  <p>If a registration or comment is rejected because of a hit on the StopForumSpam.com db, this plugin saves the email and IP. If you test the plugin using spammer credentials, it will remember that your IP address was associated with the spammer&apos;s email and deny future registrations and comments from your IP. If you feel compelled to test the plugin, you may lock yourself out of comments and the registration form. If you do get into a problem where you have cached a valid IP, click the &quot;Clear the Cache&quot; button.</p>
  <p>The plugin also caches good emails, so if a spammer is unknown to StopForumSpam.com it will be entered into the good guys cache. Cached results are kept for 24 hours and then deleted.</p>
  <p>Since the plugin caches the IP address used by a spammer, it is possible for the plugin to reject possible comments from a legitimate user who just happens to come from an ISP who tolerates spammers.</p>
  <p>The plugin will optionally stop spammers who do not send the HTTP_ACCEPT header from the browser. Since all normal browsers use this header, it is safe to assume that anyone who does not send it is hitting you with a robot.</p>
  <p>Note: StopForumSpam.com limits checks to 5,000 per day for each IP so the plugin may stop validating on very busy sites. I have not seen this happen, yet. Results are cached in order to thwart repeated attempts. You may see your own email in the cache as spammers try to use it to leave comments. You may have to clear the cache to use your own email in a comment if that is the case.</p>
  <p>
  <form method="post" action="">
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="kpg_stop_spammers_control" value="<?php echo $nonce;?>" />
    <p>Your StopForunSpam.com API Key:
      <input size="32" name="apikey" type="text" value="<?php echo $apikey; ?>"/>
      (optional)</p>
    <p>Project Honeypot API Key:
      <input size="32" name="honeyapi" type="text" value="<?php echo $honeyapi; ?>"/>
      (For HTTP:bl blacklist lookup, if not blank)</p>
    <p>Block Spam missing the HTTP_ACCEPT header:
      <input name="accept" type="checkbox" value="Y" <? if ($accept=='Y') echo  'checked="true"';?>/>
      Blocks users who have incomplete headers. (optional)</p>
   <p>Check email address in addition to IP at StopForumSpam:
      <input name="chkemail" type="checkbox" value="Y" <? if ($chkemail=='Y') echo  'checked="true"';?>/>
      Most spammers use random, faked or other people's email. (optional)</p>
	 <p>White List - put IP address or emails here that you don't want blocked. One email or IP to a line.<br/>
<textarea style="border:medium solid #66CCFF;" name="wlist" cols="40" rows="8"><?php 
    for ($k=0;$k<count($wlist);$k++) {
		echo $wlist[$k]."\r\n";
	}
	?>
</textarea>	 
	 </p>
	 <p class="submit"><input class="button-primary" value="Save Changes" type="submit"></p>

  </form>
  <p>I have added a link on the WordPress comments maintenance so you can check a comment against the StopForumSpam.com database.</p>
  <p>If you have a StopForumSpam.com API key you can report spam. You can easily get an API key after registering at <a href="http://www.StopForumSpam.com" target="_blank">StopForumSpam.com</a>.</p>
  <p>When you include the Project Honeypot API key each user will be validated against the HTTP:bl blacklist. You can get an api key at <a href="http://www.projecthoneypot.org" target="_blank">http://www.projecthoneypot.org</a></p>
  <?php
	$sphist=array();
	$badips=array();
	$badems=array();
	$gdems=array();
	$spcount=0;
	$options=get_option('kpg_stop_sp_reg_options');
	if (array_key_exists('spcount',$options)) $spcount=$options['spcount'];
	if (!is_numeric($spcount)) $spcount=0;

	if (array_key_exists('sphist',$options)) $sphist=$options['sphist'];
	if (array_key_exists('badems',$options)) $badems=$options['badems'];
	if (array_key_exists('badips',$options)) $badips=$options['badips'];
	if (array_key_exists('gdems',$options)) $gdems=$options['gdems'];
	if (!is_array($badips)) $badips=array();
	if (!is_array($badems)) $badems=array();
	if (!is_array($sphist)) $sphist=array();
	if (!is_array($gdems)) $gdems=array();

	
	if (empty($spcount)&&(!empty($sphist))) $spcount=count($sphist);
	if (!empty($sphist)) {
  ?>
  <hr/>
  <h3>Recent Activity</h3>
  <form method="post" action="">
    <input type="hidden" name="kpg_stop_spammers_control" value="<?php echo $nonce;?>" />
    <input type="hidden" name="kpg_stop_clear_hist" value="true" />
    <input value="Clear Recent Activity" type="submit">
  </form>
  </p>
  <?php

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
			echo "</li>";
		}
	}
	echo "</ul>";
	}
	$badems=kpg_clear_old_cache($badems);
	$badips=kpg_clear_old_cache($badips);
	//$gdems=kpg_clear_old_cache($gdems);
   }
   if (!(empty($badems)&&empty($badips)&&empty($gdems))) {
?>
  <h3>Cached Values (last 24 hours)</h3>
  <form method="post" action="">
    <input type="hidden" name="kpg_stop_spammers_control" value="<?php echo $nonce;?>" />
    <input type="hidden" name="kpg_stop_clear_cache" value="true" />
    <input value="Clear the Cache" type="submit">
  </form>
  <table align="center" width="95%"  >
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
			$dt=$value;
			if (is_array($value)) {
				$dt=$value['date'];
				$ip=$value['ip'];
				$usrid=$value['usrid'];
				$evidence=$value['evidence'];
			}
			$key=urldecode($key);
			echo "$key: $dt<br/>";
		}
	?></td>
    </tr>
  </table>
  <?PHP
    }
	
?>
  <hr/>
  <p>This plugin is free and I expect nothing in return. If you would like to support my programming, you can buy my book of short stories.<br/>
    <a target="_blank" href="http://www.amazon.com/gp/product/1456336584?ie=UTF8&tag=thenewjt30page&linkCode=as2&camp=1789&creative=390957&creativeASIN=1456336584">Error Message Eyes: A Programmer's Guide to the Digital Soul</a></p>
  <p>A link on your blog to one of my personal sites would be appreciated.</p>
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
	$ip=$comment->comment_author_IP;
	$action="<a target=\"_stopspam\" href=\"http://www.stopforumspam.com/search.php?q=$ip\">Check: StopFurumSpam</a> |
	 <a target=\"_stopspam\" href=\"http://www.projecthoneypot.org/search_ip.php?ip=$ip\">Check: Project Honeypot</a>";
	$actions['check_spam']=$action;
	return $actions;


}
function kpg_stop_sp_reg_report($actions,$comment) {
	// need to add a new action to the list
	$options=get_option('kpg_stop_sp_reg_options');
	if (empty($options)) $options=array();
	if (!is_array($options)) $options=array();
	$apikey='';
	if (array_key_exists('apikey',$options)) $apikey=$options['apikey'];
	$honeyapi='';
	if (array_key_exists('honeyapi',$options)) $honeyapi=$options['honeyapi'];

	$email=urlencode($comment->comment_author_email);
	$uname=urlencode($comment->comment_author);
	$ip=$comment->comment_author_IP;
	// code added as per Paul at sto Forum Spam
	$content=$comment->comment_content;
	
	$evidence=$comment->comment_author_url;
	if (empty($evidence)) $evidence='';
	preg_match_all('@((https?://)?([-\w]+\.[-\w\.]+)+\w(:\d+)?(/([-\w/_\.]*(\?\S+)?)?)*)@',$content, $post, PREG_PATTERN_ORDER);
	if (is_array($post)&&is_array($post[1])) $urls1 = array_unique($post[1]); else $urls1 = ''; 
	//bbcode
	preg_match_all('/\[url=(.+)\]/iU', $content, $post, PREG_PATTERN_ORDER);
	if (is_array($post)&&is_array($post[0])) $urls2 = array_unique($post[0]); else $urls2 = ''; 
    if (is_array($urls1)) $evidence.="\r\n".implode("\r\n",$urls1);	
    if (is_array($urls2)) $evidence.="\r\n".implode("\r\n",$urls2);	
	
	$evidence=urlencode(trim($evidence,"\r\n"));
	$action="<a target=\"_stopspam\" href=\"http://www.stopforumspam.com/add?username=$uname&email=$email&ip_addr=$ip&evidence=$evidence&api_key=$apikey\">Report to StopForumSpam</a>";
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
