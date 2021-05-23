<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("VOTE_CURRENT_NAME"),
	"DESCRIPTION" => GetMessage("VOTE_CURRENT_DESCRIPTION"),
	"ICON" => "/images/current.gif",
	"COMPLEX" => "Y",
	"PATH" => array(
		"ID" => "service",
		"CHILD" => array(
			"ID" => "voting",
			"NAME" => GetMessage("VOTING_SERVICE")
		)
	),
);
?>