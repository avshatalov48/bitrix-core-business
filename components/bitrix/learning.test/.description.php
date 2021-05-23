<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("LEARNING_TEST_DETAIL_NAME"),
	"DESCRIPTION" => GetMessage("LEARNING_TEST_DETAIL_DESC"),
	"ICON" => "/images/test.gif",
	"PATH" => array(
		"ID" => "service",
		"CHILD" => array(
			"ID" => "learning",
			"NAME" => GetMessage("LEARNING_SERVICE"),
			"CHILD" => array(
				"ID" => "test",
				"NAME" => GetMessage("LEARNING_TEST_SERVICE")
			),
		),
	)
);


?>