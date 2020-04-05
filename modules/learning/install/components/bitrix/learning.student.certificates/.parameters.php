<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	"PARAMETERS" => array(

	"COURSE_DETAIL_TEMPLATE" => Array(
		"NAME"=>GetMessage("LEARNING_COURSE_DETAIL_TEMPLATE_NAME"), 
		"TYPE"=>"STRING", 
		"PARENT" => "URL_TEMPLATES",
		"COLS" => 45,
		"DEFAULT"=>"course/index.php?COURSE_ID=#COURSE_ID#&INDEX=Y"
	),
	
	"TESTS_LIST_TEMPLATE" => Array(
			"NAME"=>GetMessage("LEARNING_TESTS_LIST_TEMPLATE_NAME"),
			"TYPE"=>"STRING", 
			"COLS" => 45,
			"PARENT" => "URL_TEMPLATES",
			"DEFAULT"=>'course/index.php?COURSE_ID=#COURSE_ID#&TEST_LIST=Y'
	),

	"SET_TITLE" => Array(),
	)
);
?>