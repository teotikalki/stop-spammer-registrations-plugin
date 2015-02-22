<?php
// Allow List - returns false if not found

if (!defined('ABSPATH')) exit;

class chkcloudflare extends be_module {
// if the cloudflare plugin is not installed then the ip will be cloudflare's can't check this.
		public $searchname='CloudFlare';
		public $searchlist=array(
		array('104.16.0.0','104.31.255.255'),
		array('108.162.192.0','108.162.255.255'),
		array('162.158.0.0','162.159.255.255'),
		array('173.245.48.0','173.245.63.255'),
		array('198.41.128.0','198.41.255.255'),
		array('199.27.128.0','199.27.135.255'),
		array('2606:4700::','2606:4700:FFFF:FFFF:FFFF:FFFF:FFFF:FFFF')
		);
}
?>