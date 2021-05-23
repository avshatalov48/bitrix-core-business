<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = Array(
	"PARAMETERS" => Array(
		"POST_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SBPSM_POST_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#POST_ID#}"
		),
		"COMMENTS_COUNT" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SBPSM_COMMENTS_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "5"
		),
		"URL" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SBPSM_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#URL#}",
		),
		"EMAIL_TO" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SBPSM_EMAIL_TO"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#EMAIL_TO#}",
		),
		"RECIPIENT_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SBPSM_RECIPIENT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#RECIPIENT_ID#}",
		),
		"AVATAR_SIZE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SBPSM_AVATAR_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "58",
		),
		"AVATAR_SIZE_COMMENT" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SBPSM_AVATAR_SIZE_COMMENT"),
			"TYPE" => "STRING",
			"DEFAULT" => "42",
		)
	)
);

?>