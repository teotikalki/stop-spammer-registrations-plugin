<?php

if (!defined('ABSPATH')) exit;

class chktld { // change name
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
		// this cheks the .xxx or .ru, etc in emails. Only works if there is an email
		if (!array_key_exists('email',$post)) return false;
		$email=$post['email'];
		if (empty($email)) return false;
		if (strpos($email,'@')===false) return false;
		if (strpos($email,'.')===false) return false;
		$tld=$options['badTLDs'];
		if (empty($tld)) return false;
		$t=explode('.',$email);
		$tt=$t[count($t)-1];
		$tt='.'.strtoupper($tt);
		
		// look in tlds for the tld in the email
		foreach($tld as $ft) {
			$ft=strtoupper(trim($ft));
			if (empty($ft)) continue;
			if ($ft==$tt) return "TLD blocked: $email:$ft";
		}
	
		return false;
	}
}
?>