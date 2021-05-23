<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("LEARNING_STUDENT_TRANSCRIPT_NAME"),
	"DESCRIPTION" => GetMessage("LEARNING_STUDENT_TRANSCRIPT_DESC"),
	"ICON" => "/images/transcript.gif",
	"PATH" => array(
		"ID" => "service",
		"CHILD" => array(
			"ID" => "learning",
			"NAME" => GetMessage("LEARNING_SERVICE"),
			"CHILD" => array(
				"ID" => "student",
				"NAME" => GetMessage("LEARNING_STUDENT_SERVICE")
			),
		),
	)
);


?>