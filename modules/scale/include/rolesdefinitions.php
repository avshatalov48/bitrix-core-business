<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$rolesDefinitions = array(

	//Special invisible role assigned to every server-node
	"mgmt" => array(
		"NAME" => Loc::getMessage("SCALE_RDEF_ROLE_MGMT"),
		"ACTIONS" => array(),
		"COLOR" => "orange",
		"HIDE_LOADBAR" => true,
		"HIDE_NOROLE" => true,
		"GRAPH_CATEGORIES" => array("NGINX")
	),

	"web" => array(
		"NAME" => "Apache",
		"ACTIONS" => array(),
		"COLOR" => "grey-blue",
		"LOADBAR_INFO" => "##HOSTNAME##-process_status_httpd-pcpu-g.rrd",
		"GRAPH_CATEGORIES" => array("APACHE"),
		"ROLE_ACTIONS" => array(
			"norole" => array("APACHE_ADD_ROLE"),
			"notype" => array("APACHE_DEL_ROLE")
		)
	),

	"memcached" => array(
		"NAME" => "Memcached",
		"ACTIONS" => array(),
		"COLOR" => "sky-blue",
		"LOADBAR_INFO" => "##HOSTNAME##-process_status_memcached-pcpu-g.rrd",
		"GRAPH_CATEGORIES" => array("MEMCACHED"),
		"ROLE_ACTIONS" => array(
			"norole" => array("MEMCACHED_ADD_ROLE"),
			"notype" => array("MEMCACHED_DEL_ROLE")
		),
		"MONITORING_CATEGORIES" => array("MEMCACHED")
	),

	"sphinx" => array(
		"NAME" => "Sphinx",
		"ACTIONS" => array(),
		"COLOR" => "red",
		"LOADBAR_INFO" => "##HOSTNAME##-process_status_searchd-pcpu-g.rrd",
		"GRAPH_CATEGORIES" => array("SPHINX"),
		"ONLY_ONE" => "Y",
		"ROLE_ACTIONS" => array(
			"norole" => array("SPHINX_ADD_ROLE"),
			"notype" => array("SPHINX_DEL_ROLE")
		)
	),

	"mysql" => array(
		"NAME" => "MySQL",
		"ACTIONS" => array(),
		"COLOR" => "green",
		"LOADBAR_INFO" => "##HOSTNAME##-process_status_mysqld-pcpu-g.rrd",
		"TYPES" => array(
			"master" => "M",
			"slave" => "S"
		),
		"ROLE_ACTIONS" => array(
			"master" => array("MYSQL_CHANGE_PASS"),
			"slave" => array("MYSQL_CHANGE_MASTER", "MYSQL_DEL_SLAVE", "MYSQL_CHANGE_PASS"),
			"norole" => array("MYSQL_ADD_SLAVE", "MYSQL_ADD_SLAVE_FIRST", "MYSQL_CHANGE_PASS"),
			"notype" => array()
		),
		"STATE_ACTIONS" => array(
			"active" => array("MYSQL_STOP"),
			"not_active" => array("MYSQL_START")
		),
		"GRAPH_CATEGORIES" => array("MYSQL")
	),

	"SERVER" => array(
		"NAME" => "server",
		"ACTIONS" => array("DEL_SERVER", "REBOOT", "UPDATE_BVM", "UPDATE_SYSTEM", "CHANGE_PASSWD", "CHANGE_PASSWD_BITRIX"),
		"COLOR" => "invisible",
		"MONITORING_CATEGORIES" => array("AVG_LOAD", "MEMORY", "HDD",  "NET", "HDDACT"),
		"GRAPH_CATEGORIES" => array("DISC", "NETWORK", "PROCESSES", "SYSTEM")
	),

	"push" => array(
		"NAME" => "Push",
		"ACTIONS" => array(),
		"COLOR" => "sky-blue",
		"ROLE_ACTIONS" => array(
			"norole" => array("PUSH_ADD_ROLE"),
			"notype" => array("PUSH_DEL_ROLE")
		)
	)
);