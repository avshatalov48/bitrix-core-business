<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPC_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPC_DESCR_DESCR"),
	"TYPE" => "condition",
	"FILTER" => array(
		'EXCLUDE' => CBPHelper::DISTR_B24
	),
);
?>