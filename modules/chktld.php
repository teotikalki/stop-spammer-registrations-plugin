<?php
// changed to look for the ending tld in all fields
if (!defined('ABSPATH')) exit;

class chktld { // change name
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
		// this checks the .xxx or .ru, etc in emails. Only works if there is an email
		$tld=$options['badTLDs'];
		if (empty($tld)) return false;		
		// look in tlds for the tld in the email
		foreach($post as $key=>$value) {
			foreach($tld as $ft) {
				//echo "1 $key, $value, $ft<br>";
				if (empty($key)) continue;
				if (strpos($value,'.')===false) continue;
				$ft=strtolower(trim($ft));
				if (empty($ft)) continue;
				//echo "2 $key, $value, $ft<br>";
				$t=explode('.',$value);			
				$tt=$t[count($t)-1];
				$tt='.'.trim(strtolower($tt));
				if ($ft==$tt) return "TLD blocked: $key:$value:$ft";
			}
		}
	
		return false;
	}
}
?>