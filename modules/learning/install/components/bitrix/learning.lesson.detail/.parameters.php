<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("learning"))
	return;

$arCourses = Array();
$courses = CCourse::GetList(Array("SORT"=>"ASC"));
while ($arRes = $courses->Fetch())
	$arCourses[$arRes["ID"]] = $arRes["NAME"]; 

$arYesNo = Array(
	"Y" => GetMessage("LEARNING_DESC_YES"),
	"N" => GetMessage("LEARNING_DESC_NO"),
);

$arComponentParameters = array(
	"PARAMETERS" => array(

		"COURSE_ID" => array(
			"NAME" => GetMessage("LEARNING_COURSE_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arCourses,
			"PARENT" => "BASE",
			"ADDITIONAL_VALUES"	=> "Y",
			"DEFAULT"=>'={$_REQUEST["COURSE_ID"]}'
		),

		"LESSON_ID" => array(
			"NAME" => GetMessage("LEARNING_LESSON_ID_NAME"),
			"TYPE"=>"STRING",
			"DEFAULT"=>'={$_REQUEST["LESSON_ID"]}',
			"PARENT" => "BASE",
			"COLS" => 45
		),

		"SELF_TEST_TEMPLATE" => array(
			"NAME" => GetMessage("LEARNING_SELF_TEST_TEMPLATE_NAME"),
			"TYPE"=>"STRING",
			"PARENT" => "URL_TEMPLATES",
			"DEFAULT"=>'self.php?COURSE_ID=#COURSE_ID#&LESSON_ID=#LESSON_ID#',
			"COLS" => 45
		),
		
		"PATH_TO_USER_PROFILE" => array(
			"NAME" => GetMessage("LEARNING_PATH_TO_USER_PROFILE"),
			"TYPE"=>"STRING",
			"PARENT" => "URL_TEMPLATES",
			"DEFAULT"=> '',
			"COLS" => 45
		),

		"CHECK_PERMISSIONS" => Array(
			"NAME"=>GetMessage("LEARNING_CHECK_PERMISSIONS"), 
			"TYPE"=>"LIST", 
			"MULTIPLE"=>"N",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT"=>"Y", 
			"VALUES"=>$arYesNo, 
			"ADDITIONAL_VALUES"=>"N"
		),

		"SET_TITLE" => Array(),
		"CACHE_TIME" => Array("DEFAULT"=>"3600"),
	)
);
?>