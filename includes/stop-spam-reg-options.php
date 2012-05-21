<?php
/*
	Stop Spammer Registrations Plugin 
	Options Setup Page
*/
?>

<div class="wrap">
  <h2>Stop Spammers Plugin Options</h2>
  <?php
	if ($nobuy=='N') {
?>
  div style="position:relative;float:right;width:35%;background-color:ivory;border:#333333 medium groove;padding:4px;margin-left:4px;">
    <p>This plugin is free and I expect nothing in return. If you would like to support my programming, you can buy my book of short stories.</p>
    <p>Some plugin authors ask for a donation. I ask you to spend a very small amount for something that you will enjoy. eBook versions for the Kindle and other book readers start at 99&cent;. The book is much better than you might think, and it has some very good science fiction writers saying some very nice things. <br/>
      <a target="_blank" href="http://www.blogseye.com/buy-the-book/">Error Message Eyes: A Programmer's Guide to the Digital Soul</a></p>
    <p>A link on your blog to one of my personal sites would also be appreciated.</p>
    <p><a target="_blank" href="http://www.WestNyackHoney.com">West Nyack Honey</a> (I keep bees and sell the honey)<br />
      <a target="_blank" href="http://www.cthreepo.com/blog">Wandering Blog</a> (My personal Blog) <br />
      <a target="_blank" href="http://www.cthreepo.com">Resources for Science Fiction</a> (Writing Science Fiction) <br />
      <a target="_blank" href="http://www.jt30.com">The JT30 Page</a> (Amplified Blues Harmonica) <br />
      <a target="_blank" href="http://www.harpamps.com">Harp Amps</a> (Vacuum Tube Amplifiers for Blues) <br />
      <a target="_blank" href="http://www.blogseye.com">Blog&apos;s Eye</a> (PHP coding) <br />
      <a target="_blank" href="http://www.cthreepo.com/bees">Bee Progress Beekeeping Blog</a> (My adventures as a new beekeeper) </p>
  </div>
  <?php
	}
?>
<p><a href="options-general.php?page=stopspammerstats">View History and Cache</a>
</p>
  <?php
	$stats=kpg_sp_get_stats();
	extract($stats);
	$options=kpg_sp_get_options();
	extract($options);

	if(!current_user_can('manage_options')) {
		die('Access Denied');
	}
	$nonce='';
	if (array_key_exists('kpg_stop_spammers_control',$_POST)) $nonce=$_POST['kpg_stop_spammers_control'];
	if (wp_verify_nonce($nonce,'kpgstopspam_update')) { 
		if (array_key_exists('action',$_POST)) {
						
			if (array_key_exists('chkdisp',$_POST)) {
				$chkdisp=stripslashes($_POST['chkdisp']);
			} else {
				$chkdisp='N';
			}
			$options['chkdisp']=$chkdisp;
			
			if (array_key_exists('chkubiquity',$_POST)) {
				$chkubiquity=stripslashes($_POST['chkubiquity']);
			} else {
				$chkubiquity='N';
			}
			$options['chkubiquity']=$chkubiquity;
			if (array_key_exists('chkakismet',$_POST)) {
				$chkakismet=stripslashes($_POST['chkakismet']);
			} else {
				$chkakismet='N';
			}
			$options['chkakismet']=$chkakismet;
			
			if (array_key_exists('chkdnsbl',$_POST)) {
				$chkdnsbl=stripslashes($_POST['chkdnsbl']);
			} else {
				$chkdnsbl='N';
			}
			$options['chkdnsbl']=$chkdnsbl;
			
			if (array_key_exists('chkemail',$_POST)) {
				$chkemail=stripslashes($_POST['chkemail']);
			} else {
				$chkemail='N';
			}
			$options['chkemail']=$chkemail;
			
			
			if (array_key_exists('nobuy',$_POST)) {
				$nobuy=stripslashes($_POST['nobuy']);
			} else {
				$nobuy='N';
			}
			if ($nobuy!='Y') $nobuy='N';
			$options['nobuy']=$nobuy;
			
			
			if (array_key_exists('accept',$_POST)) {
				$accept=stripslashes($_POST['accept']);
			} else {
				$accept='N';
			}
			if ($accept!='Y') $accept='N';
			$options['accept']=$accept;
			
			if (array_key_exists('apikey',$_POST)) $apikey=stripslashes($_POST['apikey']);
			$options['apikey']=$apikey;
			if (array_key_exists('honeyapi',$_POST)) $honeyapi=stripslashes($_POST['honeyapi']);
			$options['honeyapi']=$honeyapi;
			if (array_key_exists('botscoutapi',$_POST)) $botscoutapi=stripslashes($_POST['botscoutapi']);
			$options['botscoutapi']=$botscoutapi;
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
			if (array_key_exists('blist',$_POST)) {
				$blist=stripslashes($_POST['blist']);
			    $blist=str_replace("\r\n","\n",$blist);
			    $blist=str_replace("\r","\n",$blist);
				$blist=explode("\n",$blist);
				$options['blist']=$blist;				
				for ($k=0;$k<count($blist);$k++) {
					$blist[$k]=trim($blist[$k]);
				}	
			}
			// update the freq and age options
			if (array_key_exists('sfsfreq',$_POST)) $sfsfreq=trim(stripslashes($_POST['sfsfreq']));
			if (array_key_exists('hnyage',$_POST)) $hnyage=trim(stripslashes($_POST['hnyage']));
			if (array_key_exists('botfreq',$_POST)) $botfreq=trim(stripslashes($_POST['botfreq']));
			if (array_key_exists('sfsage',$_POST)) $sfsage=trim(stripslashes($_POST['sfsage']));
			if (array_key_exists('hnylevel',$_POST)) $hnylevel=trim(stripslashes($_POST['hnylevel']));
			if (array_key_exists('botage',$_POST)) $botage=trim(stripslashes($_POST['botage']));
			if (array_key_exists('muswitch',$_POST)) $muswitch=trim(stripslashes($_POST['muswitch']));
			if (array_key_exists('rejectmessage',$_POST)) $rejectmessage=trim(stripslashes($_POST['rejectmessage']));
			
			if (array_key_exists('kpg_sp_cache',$_POST)) $kpg_sp_cache=trim(stripslashes($_POST['kpg_sp_cache']));
			if (array_key_exists('kpg_sp_hist',$_POST)) $kpg_sp_hist=trim(stripslashes($_POST['kpg_sp_hist']));
			// check for numerics in the fields
			if (!is_numeric($sfsfreq)) $sfsfreq=0; 
			if (!is_numeric($hnyage)) $hnyage=0;
			if (!is_numeric($botfreq)) $botfreq=0; 
			if (!is_numeric($hnylevel)) $hnylevel=5;
			if (!is_numeric($botage)) $botage=9999; 
			if (!is_numeric($sfsage)) $sfsage=9999;	
			if (!is_numeric($kpg_sp_cache)) $kpg_sp_cache=25;	
			if (!is_numeric($kpg_sp_hist)) $kpg_sp_hist=25;	
			$options['sfsfreq']=$sfsfreq;
			$options['hnyage']=$hnyage;
			$options['botfreq']=$botfreq;
			$options['sfsage']=$sfsage;
			$options['hnylevel']=$hnylevel;
			$options['botage']=$botage;
			$options['kpg_sp_cache']=$kpg_sp_cache;
			$options['kpg_sp_hist']=$kpg_sp_hist;
			if (empty($muswitch)) $muswitch='Y';
			if ($muswitch!='N') $muswitch='Y';
			$options['muswitch']=$muswitch;
			$options['rejectmessage']=$rejectmessage;
			if (function_exists('is_multisite') && is_multisite() && function_exists('kpg_ssp_global_unsetup') && function_exists('kpg_ssp_global_setup')) {
				if ($muswitch=='N') {
					kpg_ssp_global_unsetup();
				} else {
					kpg_ssp_global_setup();
				}
			}			
			update_option('kpg_stop_sp_reg_options',$options);
			echo "<h2>Options Updated</h2>";
		}
		extract($options);

	}
	if (function_exists('is_multisite') && is_multisite()) {
		global $blog_id;
		if (!isset($blog_id)||$blog_id!=1) {
			if ($muswitch=='Y') {
				?>
  <h3>Stop Spammers is configured so that settings are available only on the Main Blog.</h3>
  <?php
				return;
			}		
		}
	}
   $nonce=wp_create_nonce('kpgstopspam_update');
	if ($spmcount>0) {
?>
  <h3>Stop Spammers has stopped <?php echo $spmcount; ?> spammers since installation</h3>
  <?php 
}
	if ($spcount>0) {
?>
  <h3>Stop Spammers has stopped <?php echo $spcount; ?> spammers since last cleared</h3>
  <?php 

	} 
	$num_comm = wp_count_comments( );
	$num = number_format_i18n($num_comm->spam);
	if ($num_comm->spam>0) {	
?>
  <p>There are <a href='edit-comments.php?comment_status=spam'><?php echo $num; ?></a> spam comments waiting for you to report them</p>
  <?php 
	}
		$num_comm = wp_count_comments( );
	$num = number_format_i18n($num_comm->moderated);
	if ($num_comm->moderated>0) {	
?>
  <p>There are <a href='edit-comments.php?comment_status=moderated'><?php echo $num; ?></a> spam comments waiting to be moderated</p>
  <?php 
	}

?>
  <p style="font-weight:bold;">The Stop Spammers Plugin is installed and working correctly.</p>
  <p>Eliminates 99% of spam registrations and comments. Checks all attempts to leave spam against StopForumSpam.com, Project Honeypot, BotScout, DNSBL lists such as Spamhaus.org, Ubiquity Servers, disposable email addresses, and HTTP_ACCEPT header.</p>
  <p style="font-weight:bold;">New With Version 3.0: </p>
  <p>The Stop Spammer Registrations Plugin now checks for spammer IPs much earlier in the comment and registration process. When a spammer IP is detected, the plugin stops wordpress from completing any further operations and an access denied message is presented to the spammer. The text of the message can be edited.</p>
  <p style="font-weight:bold;">How the plugin works: </p>
  <p>This plugin checks against StopForumSpam.com, Project Honeypot and BotScout to to prevent spammers from registering or making comments. 
    The Stop Spammer Registrations plugin works by checking the IP address, email and user id of anyone who tries to register, login, or leave a comment. This effectively blocks spammers who try to register on blogs or leave spam. It checks a users credentials against up to three databases: <a href="http://www.stopforumspam.com/">Stop Forum Spam</a>, <a href="http://www.projecthoneypot.org/">Project Honeypot</a>, and <a href="http://www.botscout.com/">BotScout</a>. Optionally checks against Akismet for Logins and Registrations. </p>
  <p>Optionally the plugin will also check for disposable email addresses, check for the lack of a HTTP_ACCEPT header, and check against several DNSBL lists such as Spamhaus.org. It also checks against the Ubiquity Servers IP ranges, which is a major source of Spam Comments. </p>
  <p><span style="font-weight:bold;">Limitations: </span></p>
  <p>StopForumSpam.com limits checks to 10,000 per day for each IP so the plugin may stop validating on very busy sites. I have not seen this happen, yet. The plugin will not stop spam that has not been reported to the various databases. You will always get some comments from spammers who are not yet reported. You can help others and yourself by reporting spam. If you do not report spam, the spammer will keep hitting you. This plugin works best with Akismet. Akismet works well, but clutters the database with spam comments that need to be deleted regularly, and Akismet does not work with spammer registrations. </p>
  <p style="font-weight:bold;">API Keys: </p>
  <p> API Keys are NOT required for the plugin to work. Stop Forum Spam does not require a key so this plugin will work immediately without a key. The API key for<a href="http://www.stopforumspam.com/"> Stop Forum Spam</a> is only used for reporting spam. In order to use the <a href="http://www.projecthoneypot.org/">Project HoneyPot</a> or <a href="http://www.botscout.com/">BotScout</a> spam databases you will need to register at those sites and get a free API key. </p>
  <p><span style="font-weight:bold;">History: </span></p>
  <p>The Stop Spammer Registrations plugin keeps a count of the spammers that it has blocked and displays this on the WordPress dashboard. It also displays the last hits on email or IP and it also shows a history of the times it has made a check, showing rejections, passing emails and errors. When there is data to display there will also be a button to clear out the data. You can control the size of the list and clear the history. </p>
  <p><span style="font-weight:bold;">Cache: </span></p>
  <p>The Stop Spammer Registrations plugin keeps track of a number of spammer emails and IP addresses in a cache to avoid pinging databases more often than necessary. The results are saved and displayed. You can control the length of the cache list and clear it at any time. </p>
  <p><span style="font-weight:bold;">Reporting Spam : </span></p>
  <p>On the comments moderation page, the plugin adds extra options to check comments agains the various databases and to report to the Stop Forum Spam database. You will need a Stop Forum Spam API key in order to report spam/ </p>
  <p><span style="font-weight:bold;">Network MU Installation Option : </span></p>
  <p> If you are running a networked WPMU system of blogs, you can optionally control this plugin from the control panel of the main blog. By checking the 'Networked ON' radio button, the individual blogs will not see the options page. The API keyes will only have to entered in one place and the history will only appear in one place, making the plugin easier to use for administrating many blogs. The comments, however, still must be maintained from each blog. The Network buttons only appear if you have a Networked installation.</p>
  <p><span style="font-weight:bold;">Requirements : </span></p>
  <p>The plugin uses the WP_Http class to query the spam databases. Normally, if WordPress is working, then this class can access the databases. If, however, the system administrator has turned off the ability to open a url, then the plugin will not work. Sometimes placing a php.ini file in the blog's root directory with the line 'allow_url_fopen=On' will solve this.</p>
  <p>The Stop Spammer Registrations plugin is ON when it is installed and enabled. To turn it off just disable the plugin from the plugin menu.. </p>
  <p>You may see your own email in the cache as spammers try to use it to leave comments. You may have to white list your own email if that is the case, to keep the plugin from locking you out.</p>
  <p>Watch the <a href="http://www.youtube.com/watch?v=EKrUX0hHAx8" target="_blank">youtube spam trap video</a>! The video shows one of my plugins that anti-spam cops use. They run honey pots or sites that do nothing but attract spammers. These sites report as many as 500 spammers per hour to the same database that this plugin checks. </p>
  <hr/>
  <h4>For questions and support please check my website <a href="http://www.blogseye.com/i-make-plugins/stop-spammer-registrations-plugin/">BlogsEye.com</a>.</h4>
  <p>
  <form method="post" action="">
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="kpg_stop_spammers_control" value="<?php echo $nonce;?>" />
    <?php
		if (function_exists('is_multisite') && is_multisite()) {
	?>
    <fieldset style="width:95%;border: #888888 thin groove;margin-left:auto;margin-right:auto;padding-left:6px;">
    <legend>Network Blog Option:</legend>
    Select how you want to control options in a networked blog environment: <br />
    Networked ON:
    <input name="muswitch" type="radio" value='Y'  <?php if ($muswitch=='Y') echo "checked=\"true\""; ?> />
    | Networked OFF:
    <input name="muswitch" type="radio" value='N' <?php if ($muswitch!='Y') echo "checked=\"true\""; ?>  />
    <br />
    If you are running WPMU and want to control all options and logs through the main log admin panel, select on. If you select OFF, each blog will have to configure the plugin separately.
    </fieldset>
    <br/>
    <?php
		}
	?>
    <fieldset style="width:95%;border: #888888 thin groove;margin-left:auto;margin-right:auto;padding-left:6px;">
    <legend>API Keys:</legend>
    Your StopForunSpam.com API Key:
    <input size="32" name="apikey" type="text" value="<?php echo $apikey; ?>"/>
    (optional) <br/>
    Project Honeypot API Key:
    <input size="32" name="honeyapi" type="text" value="<?php echo $honeyapi; ?>"/>
    (For HTTP:bl blacklist lookup, if not blank) <br/>
    BotScout API Key:
    <input size="32" name="botscoutapi" type="text" value="<?php echo $botscoutapi; ?>"/>
    (For BotScout.com lookup, if not blank)
    </fieldset>
    <br/>
    <fieldset style="width:95%;border: #888888 thin groove;margin-left:auto;margin-right:auto;padding-left:6px;">
    <legend>Spam Limits:</legend>
    You can set the minimum settings to allow possible spammers to use your site. <br/>
    <br/>
    You may wish to forgive spammers with few incidents or no recent activity. I would recommend that to be on the safe side you should block users who appear on the spam database unless they specifically ask to be white listed. Allowed values are 0 to 9999. Only numbers are accepted. <br />
    <br/>
    Deny spammers found on Stop Forum Span with more than
    <input size="3" name="sfsfreq" type="text" value="<?php echo $sfsfreq; ?>"/>
    incidents, and occurring less than
    <input size="4" name="sfsage" type="text" value="<?php echo $sfsage; ?>"/>
    days ago. <br/>
    <br/>
    Deny spammers found on Project HoneyPot with incidents less than
    <input size="3" name="hnyage" type="text" value="<?php echo $hnyage; ?>"/>
    days ago, and with more than
    <input size="4" name="hnylevel" type="text" value="<?php echo $hnylevel; ?>"/>
    threat level. (25 threat level is average, threat level 5 is fairly low.) <br/>
    <br/>
    Deny spammers found on BotScout with more than
    <input size="3" name="botfreq" type="text" value="<?php echo $botfreq; ?>"/>
    incidents.
    </fieldset>
    <br/>
    <fieldset style="width:95%;border: #888888 thin groove;margin-left:auto;margin-right:auto;padding-left:6px;">
    <legend>Other Checks:</legend>
    Block Spam missing the HTTP_ACCEPT header:
    <input name="accept" type="checkbox" value="Y" <? if ($accept=='Y') echo  'checked="true"';?>/>
    Blocks users who have incomplete headers. (optional) <br/>
    <br/>
    Check email address in addition to IP at StopForumSpam:
    <input name="chkemail" type="checkbox" value="Y" <? if ($chkemail=='Y') echo  'checked="true"';?>/>
    Most spammers use random, faked or other people's email. (optional) <br/>
    <br/>
    Deny disposable email addresses:
    <input name="chkdisp" type="checkbox" value="Y" <? if ($chkdisp=='Y') echo  'checked="true"';?>/>
    Some real commenters might use disposable email, but probably not (optional) <br/>
    <br/>
    Check against DNSBL lists such as Spamhaus.org :
    <input name="chkdnsbl" type="checkbox" value="Y" <? if ($chkdnsbl=='Y') echo  'checked="true"';?>/>
    Primarily used for email spam, but might stop comment spam. (optional) <br/>
    <br/>
    Check against list of Ubiquity Server IPs:
    <input name="chkubiquity" type="checkbox" value="Y" <? if ($chkubiquity=='Y') echo  'checked="true"';?>/>
    Ubiquity Servers is the source of much Comment Spam (optional) <br/>
    <br/>
    Check IP against the Akismet database:
    <input name="chkakismet" type="checkbox" value="Y" <? if ($chkakismet=='Y') echo  'checked="true"';?>/>
    If Akismet is installed and the API key is set, then you may use Akismet to check logins or registrations, but not comments (optional) <br/>
    <br/>
    White List - put IP addresses or emails here that you don't want blocked. One email or IP to a line.<br/>
    <textarea style="border:medium solid #66CCFF;" name="wlist" cols="40" rows="8"><?php 
    for ($k=0;$k<count($wlist);$k++) {
		echo $wlist[$k]."\r\n";
	}
	?>
</textarea>
   <br/>
    Black List - put IP addresses or emails here that want blocked. One email or IP to a line.<br/>
    <textarea style="border:medium solid #66CCFF;" name="blist" cols="40" rows="8"><?php 
    for ($k=0;$k<count($blist);$k++) {
		echo $blist[$k]."\r\n";
	}
	?>
</textarea>
    </p>
    </fieldset>
    <br/>
    <fieldset style="width:95%;border: #888888 thin groove;margin-left:auto;margin-right:auto;padding-left:6px;">
    <legend>History and Cache Size:</legend>
    You can change the number of entries to keep in your history and cache. The size of these items is an issue and will cause problems with some WordPress installations. It is best to keep these small.<br/>
    Cache Size:
    <select name="kpg_sp_cache">
      <option value="10" <?php if ($kpg_sp_cache=='10') echo "selected=\"true\""; ?>>10</option>
      <option value="25" <?php if ($kpg_sp_cache=='25') echo "selected=\"true\""; ?>>25</option>
      <option value="50" <?php if ($kpg_sp_cache=='50') echo "selected=\"true\""; ?>>50</option>
      <option value="75" <?php if ($kpg_sp_cache=='75') echo "selected=\"true\""; ?>>75</option>
      <option value="100" <?php if ($kpg_sp_cache=='100') echo "selected=\"true\""; ?>>100</option>
    </select>
    <br/>
    History Size:
    <select name="kpg_sp_hist">
      <option value="10" <?php if ($kpg_sp_hist=='10') echo "selected=\"true\""; ?>>10</option>
      <option value="25" <?php if ($kpg_sp_hist=='25') echo "selected=\"true\""; ?>>25</option>
      <option value="50" <?php if ($kpg_sp_hist=='50') echo "selected=\"true\""; ?>>50</option>
      <option value="75" <?php if ($kpg_sp_hist=='75') echo "selected=\"true\""; ?>>75</option>
      <option value="100" <?php if ($kpg_sp_hist=='100') echo "selected=\"true\""; ?>>100</option>
    </select>
    <br/>
    </fieldset>
    <br/>
    <fieldset style="width:95%;border: #888888 thin groove;margin-left:auto;margin-right:auto;padding-left:6px;">
    <legend>Access Denied Message:</legend>
    This message is only visible to spammers. It only shows if spammers are rejected at the time login or comment form is displayed.
    <textarea id="rejectmessage" name="rejectmessage" cols="64" rows="5"><?php echo $rejectmessage; ?></textarea>
    </fieldset>
    <br/>
    <fieldset style="width:95%;border: #888888 thin groove;margin-left:auto;margin-right:auto;padding-left:6px;">
    <legend>Remove &quot;Buy The Book&quot;:</legend>
    <input type="checkbox" name ="nobuy" value="Y" <?php if ($nobuy=='Y') echo 'checked="true"'; ?> >
    <?php 
		if ($nobuy=='Y')  {
			echo "Thanks";		
		} else {
		?>
    Check if you are tired of seeing the <a target="_blank" href="http://www.blogseye.com/buy-the-book/">">Buy Keith's Book</a> links.
    <?php 
		}
	?>
    </fieldset>
    <br/>
    <p class="submit">
      <input class="button-primary" value="Save Changes" type="submit">
    </p>
  </form>
  <p>&nbsp;</p>
</div>
