<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("learning"))
	return;

$arCourses = Array();
$courses = CCourse::GetList(Array("SORT"=>"ASC"));
while ($arRes = $courses->Fetch())
	$arCourses[$arRes["ID"]] = $arRes["NAME"]; 

$arTests = Array();

if ($arCurrentValues["COURSE_ID"] && intval($arCurrentValues["COURSE_ID"]) > 0)
{
	$rsTest = CTest::GetList(
		Array("SORT" => "ASC"), 
		Array(
			"ACTIVE" => "Y",
			"COURSE_ID" => $arCurrentValues["COURSE_ID"],
		)
	);

	while ($arTest = $rsTest->GetNext())
		$arTests[$arTest["ID"]] = "[".$arTest["ID"]."] ".$arTest["NAME"];
}

$arComponentParameters = array(
	"PARAMETERS" => array(

		"COURSE_ID" => array(
			"NAME" => GetMessage("LEARNING_COURSE_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arCourses,
			"PARENT" => "BASE",
			"ADDITIONAL_VALUES"	=> "Y",
			"REFRESH" => "Y",
			"DEFAULT"=>'={$_REQUEST["COURSE_ID"]}'
		),

		"TEST_ID" => Array(
			"NAME"=>GetMessage("T_LEARNING_DETAIL_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arTests,
			"ADDITIONAL_VALUES"	=> "Y",
			"PARENT" => "BASE",
			"DEFAULT"=>'={$_REQUEST["TEST_ID"]}'
		),

		"PAGE_NUMBER_VARIABLE" => Array(
			"NAME"=>GetMessage("T_LEARNING_PAGE_NUMBER_VARIABLE"),
			"TYPE"=>"STRING",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT"=>'PAGE'
		),

		"GRADEBOOK_TEMPLATE" => Array(
			"NAME"=>GetMessage("LEARNING_GRADEBOOK_TEMPLATE_NAME"),
			"TYPE"=>"STRING",
			"PARENT" => "URL_TEMPLATES",
			"DEFAULT"=>'../gradebook.php?TEST_ID=#TEST_ID#',
			"COLS" => 45
		),

		"PAGE_WINDOW" => Array(
			"NAME"=>GetMessage("LEARNING_PAGE_WINDOW_NAME"),
			"TYPE"=>"STRING",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT"=>'10'
		),

		"SHOW_TIME_LIMIT" => Array(
			"NAME"=>GetMessage("LEARNING_SHOW_TIME_LIMIT"),
			"TYPE"=>"CHECKBOX", 
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT"=>"Y", 
		),

		/*
		"CHECK_PERMISSIONS" => Array(
			"NAME"=>GetMessage("LEARNING_CHECK_PERMISSIONS"), 
			"TYPE"=>"CHECKBOX", 
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT"=>"Y", 
		),*/

		"SET_TITLE" => Array(),
	)
);
?>