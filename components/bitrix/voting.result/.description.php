<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("VOTE_RESULT_NAME"),
	"DESCRIPTION" => GetMessage("VOTE_RESULT_DESCRIPTION"),
	"ICON" => "/images/vote_result.gif",
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "service",
		"CHILD" => array(
			"ID" => "voting",
			"NAME" => GetMessage("VOTING_SERVICE")
		)
	),
);
?>