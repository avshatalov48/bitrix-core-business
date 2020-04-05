<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = Array(
	"PARAMETERS" => Array(
		"POST_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SBPM_POST_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#POST_ID#}"
		),
		"URL" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SBPM_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#URL#}",
		),
		"EMAIL_TO" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SBPM_EMAIL_TO"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#EMAIL_TO#}",
		),
		"RECIPIENT_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SBPM_RECIPIENT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#RECIPIENT_ID#}",
		),
		"AVATAR_SIZE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SBPM_AVATAR_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "58",
		)
	)
);

?>