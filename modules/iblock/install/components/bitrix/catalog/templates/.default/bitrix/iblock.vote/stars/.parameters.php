<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

$arTemplateParameters = array(
	"DISPLAY_AS_RATING" => array(
		"NAME" => GetMessage("TP_CBIV_DISPLAY_AS_RATING"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"rating" => GetMessage("TP_CBIV_RATING"),
			"vote_avg" => GetMessage("TP_CBIV_AVERAGE"),
		),
		"DEFAULT" => "rating",
	)
);
