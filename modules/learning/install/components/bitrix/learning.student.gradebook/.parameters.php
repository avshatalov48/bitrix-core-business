<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	"PARAMETERS" => array(

		"TEST_DETAIL_TEMPLATE" => Array(
			"NAME"=>GetMessage("LEARNING_TEST_DETAIL_URL_NAME"),
			"TYPE"=>"STRING",
			"PARENT" => "URL_TEMPLATES",
			"DEFAULT"=>'course/index.php?COURSE_ID=#COURSE_ID#&TEST_ID=#TEST_ID#',
			"COLS" => 45
		),

		"COURSE_DETAIL_TEMPLATE" => Array(
			"NAME"=>GetMessage("LEARNING_COURSE_DETAIL_URL_NAME"), 
			"TYPE"=>"STRING", 
			"PARENT" => "URL_TEMPLATES",
			"DEFAULT"=>"course/index.php?COURSE_ID=#COURSE_ID#&INDEX=Y",
			"COLS" => 45
		),

		"TEST_ID_VARIABLE" => Array(
			"NAME"=>GetMessage("LEARNING_TEST_ID_VARIABLE_NAME"), 
			"TYPE"=>"STRING", 
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT"=>"TEST_ID",
		),

		"SET_TITLE" => Array(),
	)
);
?>