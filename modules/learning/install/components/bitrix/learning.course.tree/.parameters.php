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

	"GROUPS" => array(
		"URL_TEMPLATES" => array(
			"NAME" => GetMessage("LEARNING_URL_TEMPLATE_GROUP"),
		),
	),

	"PARAMETERS" => array(

		"COURSE_ID" => Array(
			"NAME" => GetMessage("LEARNING_COURSE_ID_NAME"),
			"TYPE" => "LIST",
			"VALUES" => $arCourses, 
			"ADDITIONAL_VALUES"	=> "Y",
			"PARENT" => "BASE",
			"DEFAULT"=>'={$_REQUEST["COURSE_ID"]}',
			"COLS" => 45
		),

		"COURSE_DETAIL_TEMPLATE" => Array(
			"NAME"=>GetMessage("LEARNING_COURSE_DETAIL_TEMPLATE_NAME"), 
			"TYPE"=>"STRING", 
			"PARENT" => "URL_TEMPLATES",
			"DEFAULT"=>"course/index.php?COURSE_ID=#COURSE_ID#",
			"PARENT" => "URL_TEMPLATES",
			"COLS" => 45
		),


		"CHAPTER_DETAIL_TEMPLATE" => array(
			"NAME" => GetMessage("LEARNING_CHAPTER_DETAIL_TEMPLATE_NAME"),
			"TYPE"=>"STRING",
			"PARENT" => "URL_TEMPLATES",
			"DEFAULT"=>'chapter.php?CHAPTER_ID=#CHAPTER_ID#',
			"PARENT" => "URL_TEMPLATES",
			"COLS" => 45
		),

		"LESSON_DETAIL_TEMPLATE" => array(
			"NAME" => GetMessage("LEARNING_LESSON_DETAIL_TEMPLATE_NAME"),
			"TYPE"=>"STRING",
			"PARENT" => "URL_TEMPLATES",
			"DEFAULT"=>'lesson.php?LESSON_ID=#LESSON_ID#',
			"PARENT" => "URL_TEMPLATES",
			"COLS" => 45
		),

		"SELF_TEST_TEMPLATE" => array(
			"NAME" => GetMessage("LEARNING_SELF_TEST_TEMPLATE_NAME"),
			"TYPE"=>"STRING",
			"PARENT" => "URL_TEMPLATES",
			"DEFAULT"=>'self.php?LESSON_ID=#LESSON_ID#',
			"PARENT" => "URL_TEMPLATES",
			"COLS" => 45
		),

		"TESTS_LIST_TEMPLATE" => Array(
			"NAME"=>GetMessage("LEARNING_TESTS_LIST_TEMPLATE_NAME"),
			"TYPE"=>"STRING", 
			"PARENT" => "URL_TEMPLATES",
			"DEFAULT"=>'course/test_list.php?COURSE_ID=#COURSE_ID#',
			"PARENT" => "URL_TEMPLATES",
			"COLS" => 45
		),

		"TEST_DETAIL_TEMPLATE" => Array(
			"NAME"=>GetMessage("LEARNING_TEST_DETAIL_TEMPLATE_NAME"),
			"TYPE"=>"STRING", 
			"PARENT" => "URL_TEMPLATES",
			"DEFAULT"=>'course/test.php?COURSE_ID=#COURSE_ID#&TEST_ID=#TEST_ID',
			"PARENT" => "URL_TEMPLATES",
			"COLS" => 45
		),

		"CHECK_PERMISSIONS" => Array(
			"NAME"=>GetMessage("LEARNING_CHECK_PERMISSIONS"), 
			"TYPE"=>"LIST", 
			"PARENT" => "ADDITIONAL_SETTINGS",
			"MULTIPLE"=>"N", 
			"DEFAULT"=>"Y", 
			"VALUES"=>$arYesNo, 
			"ADDITIONAL_VALUES"=>"N"
		),

		"SET_TITLE" => Array(),

	)
);
?>