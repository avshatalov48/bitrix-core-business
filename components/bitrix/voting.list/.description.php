<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("VOTE_LIST_NAME"),
	"DESCRIPTION" => GetMessage("VOTE_LIST_DESCRIPTION"),
	"ICON" => "/images/vote_list.gif",
	"PATH" => array(
		"ID" => "service",
		"CHILD" => array(
			"ID" => "voting",
			"NAME" => GetMessage("VOTING_SERVICE")
		)
	),
);
?>