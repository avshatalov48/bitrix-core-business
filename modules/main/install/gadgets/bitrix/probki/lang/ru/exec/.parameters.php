<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


$arCity = Array();

$arCity["c213"] = "Москва (Россия)";
$arCity["c2"] = "Санкт-Петербург (Россия)";
$arCity["c54"] = "Екатеринбург (Россия)";
$arCity["c143"] = "Киев (Украина)";


$arParameters = Array(
		"PARAMETERS"=> Array(
			"CACHE_TIME" => array(
				"NAME" => "Время кеширования, сек (0-не кешировать)",
				"TYPE" => "STRING",
				"DEFAULT" => "3600"
				),
		"SHOW_URL" => Array(
				"NAME" => "Показывать ссылку на подробную информацию",
				"TYPE" => "CHECKBOX",
				"MULTIPLE" => "N",
				"DEFAULT" => "N",
			),
		),
		"USER_PARAMETERS"=> Array(
			"CITY"=>Array(
				"NAME" => "Город",
				"TYPE" => "LIST",
				"MULTIPLE" => "N",
				"DEFAULT" => "c213",
				"VALUES"=>$arCity,
			),
		),
	);

?>
