<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("SNBPA_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("SNBPA_DESCR_DESCR"),
	"TYPE" => array("activity", "robot_activity"),
	"CLASS" => "SocnetBlogPostActivity",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "interaction",
	),
	'ROBOT_SETTINGS' => array(
		'CATEGORY' => 'employee',
		'RESPONSIBLE_PROPERTY' => 'UsersTo'
	)
);