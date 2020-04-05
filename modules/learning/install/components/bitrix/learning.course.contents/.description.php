<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("LEARNING_COURSE_CONTENTS_NAME"),
	"DESCRIPTION" => GetMessage("LEARNING_COURSE_CONTENTS_DESC"),
	"ICON" => "/images/course_contents.gif",
	"PATH" => array(
		"ID" => "service",
		"CHILD" => array(
			"ID" => "learning",
			"NAME" => GetMessage("LEARNING_SERVICE"),
			"CHILD" => array(
				"ID" => "course",
				"NAME" => GetMessage("LEARNING_COURSE_SERVICE")
			),
		),
	)
);


?>