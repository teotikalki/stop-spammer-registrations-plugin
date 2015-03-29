<?php
// checks for invalid ips

if (!defined('ABSPATH')) exit;

class chkinvalidip {
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
		if (defined('AF_INET6')&&strpos($ip,'.')===false) {
				try {
					if (!inet_pton($ip)) return 'invalid ip: '.$ip;
				} catch ( Exception $e) {
					return 'invalid ip: '.$ip;
				}
			}
		
		
		// check ip4 for local private ip addresses
		//224.0.0.0 through 239.255.255.255
		if($ip>='224.0.0.0'&&$ip<='239.255.255.255') return 'IPv4 Multicast Address Space Registry';
		// Reserved for future use >=240.0.0.0
		if($ip>='240.0.0.0'&&$ip<='255.255.255.255') return 'Reserved for future use';
		return false;
	}
}
?>