<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Map");
?><?$APPLICATION->IncludeComponent("bitrix:map.google.search", ".default", array(
	"INIT_MAP_TYPE" => "ROADMAP",
	"MAP_DATA" => "a:3:{s:10:\"google_lat\";s:7:\"40.7561\";s:10:\"google_lon\";s:8:\"-73.9869\";s:12:\"google_scale\";i:12;}",
	"MAP_WIDTH" => "600",
	"MAP_HEIGHT" => "500",
	"CONTROLS" => array(
		0 => "SMALL_ZOOM_CONTROL",
		1 => "TYPECONTROL",
		2 => "SCALELINE",
	),
	"OPTIONS" => array(
		0 => "ENABLE_SCROLL_ZOOM",
		1 => "ENABLE_DBLCLICK_ZOOM",
		2 => "ENABLE_DRAGGING",
		3 => "ENABLE_KEYBOARD",
	),
	"MAP_ID" => ""
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>