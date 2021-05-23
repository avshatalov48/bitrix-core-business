<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arSorts = Array(
	"ASC"=>GetMessage("LEARNING_DESC_ASC"), 
	"DESC"=>GetMessage("LEARNING_DESC_DESC")
);

$arYesNo = Array(
	"Y" => GetMessage("LEARNING_DESC_YES"),
	"N" => GetMessage("LEARNING_DESC_NO"),
);

$arSortFields = Array(
	"ID"=>GetMessage("LEARNING_DESC_FID"),
	"NAME"=>GetMessage("LEARNING_DESC_FNAME"),
	"ACTIVE_FROM"=>GetMessage("LEARNING_DESC_FACT"),
	"SORT"=>GetMessage("LEARNING_DESC_FSORT"),
	"TIMESTAMP_X"=>GetMessage("LEARNING_DESC_FTSAMP")
);

$arComponentParameters = array(
	"PARAMETERS" => array(
		"SORBY" => Array(
			"NAME"=>GetMessage("LEARNING_DESC_SORTBY"), 
			"TYPE"=>"LIST", 
			"DEFAULT"=>"SORT", 
			"VALUES"=>$arSortFields, 
			"PARENT" => "ADDITIONAL_SETTINGS",
			"ADDITIONAL_VALUES"=>"N"
		),

		"SORORDER" => Array(
			"NAME" => GetMessage("LEARNING_DESC_SORTORDER"), 
			"TYPE"=>"LIST", 
			"MULTIPLE"=>"N", 
			"DEFAULT"=>"ASC", 
			"PARENT" => "ADDITIONAL_SETTINGS",
			"VALUES"=>$arSorts, 
			"ADDITIONAL_VALUES"=>"N"
		),

		"COURSE_DETAIL_TEMPLATE" => Array(
			"NAME"=>GetMessage("LEARNING_COURSE_URL_NAME"), 
			"TYPE"=>"STRING",
			"PARENT" => "URL_TEMPLATES",
			"DEFAULT"=>"course/index.php?COURSE_ID=#COURSE_ID#&INDEX=Y",
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

		"COURSES_PER_PAGE" => Array(
			"NAME"=>GetMessage("LEARNING_COURSES_PER_PAGE"), 
			"TYPE"=>"STRING",
			"PARENT" => "ADDITIONAL_SETTINGS",
			"MULTIPLE"=>"N",
			"DEFAULT"=>"20",
		),

		"SET_TITLE" => Array(),

	)
);
?>
