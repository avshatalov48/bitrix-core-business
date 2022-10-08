<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arParameters = Array(
		"USER_PARAMETERS"=> Array(
			"PORTAL_URI"=> Array(
				"NAME" => GetMessage("GD_PROFILE_PORTAL_URI"),
				"TYPE" => "STRING",
				"DEFAULT" => ".bitrix24.com",
			),
			"APP_ID"=> Array(
				"NAME" => GetMessage("GD_PROFILE_APP_ID"),
				"TYPE" => "STRING",
				"DEFAULT" => "",
			),
			"APP_SECRET"=> Array(
				"NAME" => GetMessage("GD_PROFILE_APP_SECRET"),
				"TYPE" => "STRING",
				"DEFAULT" => "",
			),
		),
	);
?>
