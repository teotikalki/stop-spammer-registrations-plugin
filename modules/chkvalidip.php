<?php
// returns false if the IP is valid - returns reason if ip is invalid

if (!defined('ABSPATH')) exit;

class chkvalidip {
	public function process($ip,&$stats=array(),&$options=array(),&$post=array()) {
		if (defined('AF_INET6')&&strpos($ip,'.')===false) {
			try {
				if (!inet_pton($ip)) return 'invalid ip: '.$ip;
			} catch ( Exception $e) {
				return 'invalid ip: '.$ip;
			}
		}
		// check ip4 for local private ip addresses
		if ($ip=='127.0.0.1') {
			return 'Accessing site through localhost';
		}
		$priv=array(
		array('10.0.0.0','10.255.255.255'),
		array('172.16.0.0','172.31.255.255'),
		array('192.168.0.0','192.168.255.255')
		);
		foreach($priv as $ips) {
			if ($ip>=$ips[0] && $ip<=$ips[1]) return 'local IP address:'.$ip;
			if ($ip<$ips[1]) break; // sorted so we can bail
		}
		// check fb ipv6
		if (substr($ip,0,2)=='FB'||substr($ip,0,2)=='fb') 'local IP address:'.$ip;
		// see if server and browser are running on same server.
		$lip=$_SERVER["SERVER_ADDR"];
		if ($ip==$lip) return 'ip same as server:'.$ip;
		// we can do this with ip4 addresses - check if same /24 subnet
		$j=strrpos($ip,'.');
		if ($j===false) return false;
		$k=strrpos($lip,'.');
		if ($k===false) return false;
		if (substr($ip,0,$j)==substr($lip,0,$k)) return 'ip same /24 subnet as server'.$ip;
		return false;
	}
}
?>