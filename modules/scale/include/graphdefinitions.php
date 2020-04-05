<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$graphics = array(
	"APACHE" => array(
		"NAME" => "Apache",
		"ITEMS" => array(
			"process_status_httpd",
			"apache_accesses",
			"apache_processes",
			"apache_volume"
		)
	),
	"DISC" => array(
		"NAME" => Loc::getMessage("SCALE_GDEF_CATEGORY_DISC"),
		"ITEMS" => array(
			"diskstats_iops",
			"diskstats_latency",
			"df",
			"iostat",
			"df_inode",
			"diskstats_throughput"
		)
	),
	"MYSQL" => array(
		"NAME" => "Mysql",
		"ITEMS" => array(
			"process_status_mysqld",
			"mysql_queries",
			"mysql_slowqueries",
			"mysql_threads",
			"mysql_bytes"
		)
	),
	"NETWORK" => array(
		"NAME" => Loc::getMessage("SCALE_GDEF_CATEGORY_NET"),
		"ITEMS" => array(
			"fw_conntrack",
			"fw_packets",
			"netstat",
			"fw_forwarded_local"
		)
	),
	"NGINX" => array(
		"NAME" => "Nginx",
		"ITEMS" => array(
			"process_status_nginx",
			"nginx_status",
			"nginx_request"
		)
	),
	"PROCESSES" => array(
		"NAME" => Loc::getMessage("SCALE_GDEF_CATEGORY_PROCESSES"),
		"ITEMS" => array(
			"forks",
			"threads",
			"processes",
			"vmstat"
		)
	),
	"SYSTEM" => array(
		"NAME" => Loc::getMessage("SCALE_GDEF_CATEGORY_SYSTEM"),
		"ITEMS" => array(
			"cpu",
			"open_files",
			"open_inodes",
			"load",
			"memory",
			"swap",
			"uptime"
		)
	),
	"MEMCACHED" => array(
		"NAME" => "Memcached",
		"ITEMS" => array(
			"process_status_memcached",
			"memcached_bytes",
			"memcached_counters",
			"memcached_rates"
		)
	),
	"SPHINX" => array(
		"NAME" => "Sphinx",
		"ITEMS" => array(
			"process_status_searchd"
		)
	)
);
