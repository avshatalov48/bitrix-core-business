<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = Array(
	"PARAMETERS" => Array(
		"COMMENT_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SLEM_COMMENT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#COMMENT_ID#}"
		),
		"LOG_ENTRY_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SLEM_LOG_ENTRY_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#LOG_ENTRY_ID#}"
		),
		"URL" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SLEM_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#URL#}",
		),
		"EMAIL_TO" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SLEM_EMAIL_TO"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#EMAIL_TO#}",
		),
		"RECIPIENT_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SLEM_RECIPIENT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#RECIPIENT_ID#}",
		),
		"AVATAR_SIZE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SLEM_AVATAR_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "58",
		),
		"AVATAR_SIZE_COMMENT" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SLEM_AVATAR_SIZE_COMMENT"),
			"TYPE" => "STRING",
			"DEFAULT" => "42",
		)
	)
);

?>