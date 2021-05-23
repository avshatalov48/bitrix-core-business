<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("LEARNING_LESSON_DETAIL_NAME"),
	"DESCRIPTION" => GetMessage("LEARNING_LESSON_DETAIL_DESC"),
	"ICON" => "/images/lesson_detail.gif",
	"PATH" => array(
		"ID" => "service",
		"CHILD" => array(
			"ID" => "learning",
			"NAME" => GetMessage("LEARNING_SERVICE"),
			"CHILD" => array(
				"ID" => "content",
				"NAME" => GetMessage("LEARNING_CONTENT_SERVICE")
			),
		),
	)
);


?>