<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arServices = Array(
	"main" => Array(
		"NAME" => GetMessage("SERVICE_MAIN_SETTINGS"),
		"STAGES" => Array(
			"files.php", // Copy bitrix files
			"property.php", // Create properties
			"template.php", // Install template
			"theme.php", // Install theme
			"group.php", // Install group
			"rating.php", // Install rating
			"settings.php",
			"options.php",
		),
	),

	"forum" => Array(
		"NAME" => GetMessage("SERVICE_FORUM"),
	),

	"iblock" => Array(
		"NAME" => GetMessage("SERVICE_IBLOCK_DEMO_DATA"),
		"STAGES" => Array(
			"types.php", //IBlock types
			"user_photogallery.php",
			"group_photogallery.php",
		),
	),

	"blog" => Array(
		"NAME" => GetMessage("SERVICE_BLOG"),
	),

	"socialnetwork" => Array(
		"NAME" => GetMessage("SERVICE_SOCIALNETWORK"),
	),
	"im" => Array(
		"NAME" => GetMessage("SERVICE_IM"),
	),
	"vote" => Array(
		"NAME" => GetMessage("SERVICE_VOTE"),
	)
);
?>