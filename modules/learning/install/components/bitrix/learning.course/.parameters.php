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
		"TEST" => array(
			"NAME" => GetMessage("TEST_DETAIL_GROUP"),
		),
		"TEST_LIST" => array(
			"NAME" => GetMessage("TEST_LIST_GROUP"),
		),
	),


	"PARAMETERS" => array(

		"VARIABLE_ALIASES" => Array(
			"COURSE_ID" => Array("NAME" => GetMessage("LEARN_COURSE_ID_DESC")),
			"INDEX" => Array("NAME" => GetMessage("LEARN_INDEX_ID_DESC")),
			"LESSON_ID" => Array("NAME" => GetMessage("LEARN_LESSON_ID_DESC")),
			"CHAPTER_ID" => Array("NAME" => GetMessage("LEARN_CHAPTER_ID_DESC")),
			"SELF_TEST_ID" => Array("NAME" => GetMessage("LEARN_SELF_TEST_DESC")),
			"TEST_ID" => Array("NAME" => GetMessage("LEARN_TEST_ID_DESC")),
			"TYPE" => Array("NAME" => GetMessage("LEARN_TYPE_ID_DESC")),
			"TEST_LIST" => Array("NAME" => GetMessage("LEARN_TEST_LIST_ID_DESC")),
			"GRADEBOOK" => Array("NAME" => GetMessage("LEARN_GRADEBOOK_ID_DESC")),
			"FOR_TEST_ID" => Array("NAME" => GetMessage("LEARN_FOR_TEST_ID_DESC")),
		),


		"SEF_MODE" => Array(
			"course.detail" => Array(
				"NAME" => GetMessage("LEARN_INDEX_DESC"),
				"DEFAULT" => "course#COURSE_ID#/index",
				"VARIABLES" => array()
			),

			"lesson.detail" => Array(
				"NAME" => GetMessage("LEARN_LESSON_DETAIL_DESC"),
				"DEFAULT" => "course#COURSE_ID#/lesson#LESSON_ID#/",
				"VARIABLES" => array("LESSON_ID")
			),

			"chapter.detail" => Array(
				"NAME" => GetMessage("LEARN_CHAPTER_DETAIL_DESC"),
				"DEFAULT" => "course#COURSE_ID#/chapter#CHAPTER_ID#/",
				"VARIABLES" => array("CHAPTER_ID")
			),

			"test.self" => Array(
				"NAME" => GetMessage("LEARN_TEST_SELF_DESC"),
				"DEFAULT" => "course#COURSE_ID#/selftest#SELF_TEST_ID#/",
				"VARIABLES" => array("SELF_TEST_ID")
			),

			"test" => Array(
				"NAME" => GetMessage("LEARN_TEST_DETAIL_DESC"),
				"DEFAULT" => "course#COURSE_ID#/test#TEST_ID#/",
				"VARIABLES" => array("TEST_ID")
			),

			"test.list" => Array(
				"NAME" => GetMessage("LEARN_TEST_LIST_DESC"),
				"DEFAULT" => "course#COURSE_ID#/examination/",
				"VARIABLES" => array()
			),

			"course.contents" => Array(
				"NAME" => GetMessage("LEARN_COURSE_CONTENTS_DESC"),
				"DEFAULT" => "course#COURSE_ID#/contents/",
				"VARIABLES" => array()
			),

			"gradebook" => Array(
				"NAME" => GetMessage("LEARN_GRADEBOOK_DESC"),
				"DEFAULT" => "course#COURSE_ID#/gradebook/",
				"VARIABLES" => array()
			),
		),

		"COURSE_ID" => Array(
			"NAME" => GetMessage("LEARNING_COURSE_ID_NAME"),
			"TYPE" => "LIST",
			"VALUES" => $arCourses, 
			"PARENT" => "BASE",
			"ADDITIONAL_VALUES"	=> "Y",
			"DEFAULT"=>'={$_REQUEST["COURSE_ID"]}',
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
		
		"PATH_TO_USER_PROFILE" => array(
			"NAME" => GetMessage("LEARN_PATH_TO_USER_PROFILE"),
			"TYPE"=>"STRING",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"DEFAULT"=> '/company/personal/user/#USER_ID#/',
			"COLS" => 45
		),
		

		"PAGE_WINDOW" => Array(
			"NAME"=>GetMessage("LEARNING_PAGE_WINDOW_NAME"),
			"TYPE"=>"STRING",
			"PARENT" => "TEST",
			"DEFAULT"=>'10'
		),

		"SHOW_TIME_LIMIT" => Array(
			"NAME"=>GetMessage("LEARNING_SHOW_TIME_LIMIT"),
			"TYPE"=>"CHECKBOX", 
			"PARENT" => "TEST",
			"DEFAULT"=>"Y", 
		),

		"PAGE_NUMBER_VARIABLE" => Array(
			"NAME"=>GetMessage("T_LEARNING_PAGE_NUMBER_VARIABLE"),
			"TYPE"=>"STRING",
			"PARENT" => "TEST",
			"DEFAULT"=>"PAGE"
		),

		"TESTS_PER_PAGE" => Array(
			"NAME"=>GetMessage("LEARNING_TESTS_PER_PAGE"), 
			"TYPE"=>"STRING",
			"PARENT" => "TEST_LIST",
			"MULTIPLE"=>"N",
			"DEFAULT"=>"20",
		),
		


		"SET_TITLE" => Array(),
		"CACHE_TIME" => Array("DEFAULT"=>"3600"),

	)
);
?>