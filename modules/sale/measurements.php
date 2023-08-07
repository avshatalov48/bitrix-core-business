<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
/*
MEASUREMENTS TABLE

U can customize it by copying to php_interface and changing what ya want. Don't forget about lang files.
Path to this file is taken from the module setting 'measurement_path'. 

NAMEs're customizing in a lang file. 
CATEGORIES (ex): "W"eight, "L"inear, "V"olume, "S"quare, etc... 
KOEF - to gram
*/

IncludeModuleLangFile(__FILE__);

$arMeasurementsTable = array(
	"MG" => array(
		"NAME" => GetMessage("MEASURE_MG"),
		"CATEGORY" => "W",
		"KOEF" => 0.001,
	),
	
	"G" => array(
		"NAME" => GetMessage("MEASURE_G"),
		"CATEGORY" => "W",
		"KOEF" => 1,
	),

	"KG" => array(
		"NAME" => GetMessage("MEASURE_KG"),
		"CATEGORY" => "W",
		"KOEF" => 1000,
	),
	
	"T" => array(
		"NAME" => GetMessage("MEASURE_T"),
		"CATEGORY" => "W",
		"KOEF" => 1000000,
	),
	
	"LBS" => array(
		"NAME" => GetMessage("MEASURE_LBS"),
		"CATEGORY" => "W",
		"KOEF" => 453.59,
	),
	
	"OZ" => array(
		"NAME" => GetMessage("MEASURE_OZ"),
		"CATEGORY" => "W",
		"KOEF" => 28.35,
	),
);
?>