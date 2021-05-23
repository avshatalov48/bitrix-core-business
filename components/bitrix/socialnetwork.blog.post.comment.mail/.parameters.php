<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = Array(
	"PARAMETERS" => Array(
		"COMMENT_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SBPCM_COMMENT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#COMMENT_ID#}"
		),
		"POST_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SBPCM_POST_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#POST_ID#}"
		),
		"URL" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SBPCM_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#URL#}",
		),
		"EMAIL_TO" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SBPCM_EMAIL_TO"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#EMAIL_TO#}",
		),
		"RECIPIENT_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SBPCM_RECIPIENT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#RECIPIENT_ID#}",
		),
		"AVATAR_SIZE_COMMENT" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("SBPCM_AVATAR_SIZE_COMMENT"),
			"TYPE" => "STRING",
			"DEFAULT" => "42",
		)
	)
);

?>