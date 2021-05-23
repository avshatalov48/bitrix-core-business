<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("learning"))
	return;

$arYesNo = Array(
	"Y" => GetMessage("LEARNING_DESC_YES"),
	"N" => GetMessage("LEARNING_DESC_NO"),
);

$arCourses = Array();
$courses = CCourse::GetList(Array("SORT"=>"ASC"));
while ($arRes = $courses->Fetch())
	$arCourses[$arRes["ID"]] = $arRes["NAME"]; 

$arComponentParameters = array(
	"PARAMETERS" => array(
			"COURSE_ID" => Array(
			"NAME" => GetMessage("LEARNING_COURSE_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arCourses, 
			"ADDITIONAL_VALUES"	=> "Y",
			"PARENT" => "BASE",
			"DEFAULT"=>'={$_REQUEST["COURSE_ID"]}',
			"COLS" => 45
		),

		"TEST_DETAIL_TEMPLATE" => Array(
			"NAME"=>GetMessage("LEARNING_TEST_TEMPLATE_NAME"),
			"TYPE"=>"STRING", 
			"PARENT" => "URL_TEMPLATES",
			"DEFAULT"=>'test.php?COURSE_ID=#COURSE_ID#&TEST_ID=#TEST_ID#',
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

		"TESTS_PER_PAGE" => Array(
			"NAME"=>GetMessage("LEARNING_TESTS_PER_PAGE"), 
			"TYPE"=>"STRING",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"MULTIPLE"=>"N",
			"DEFAULT"=>"20",
		),

		"SET_TITLE" => Array(),
	)
);
?>