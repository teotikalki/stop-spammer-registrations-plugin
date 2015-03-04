<?php
// Allow List various services. Returns name if found, false if not found

if (!defined('ABSPATH')) exit;

class chkmiscallowlist extends be_module { 
	public $searchname='VaultPress';
	public $searchlist=array(
	'VaultPress', // testing out checks for aws 
	array('207.198.112.0','207.198.113.255'),
	'RssGrafitti', // testing out checks for aws 
	array('23.21.82.184','23.21.82.184'),
	array('54.235.100.22','54.235.100.22'),
	array('54.235.94.95','54.235.94.95'),
	array('54.235.97.10','54.235.97.10'),
	array('54.235.98.169','54.235.98.169')
	);
}
?>