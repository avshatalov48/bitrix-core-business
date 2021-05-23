<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("BX_CAT_CDI_CMP_NAME"),
	"DESCRIPTION" => GetMessage("BX_CAT_CDI_CMP_DESCR"),
	"ICON" => "/images/discount.gif",
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "e-store",
		"NAME" => GetMessage('CMP_BX_SECT_TITLE_ESTORE'),
		"CHILD" => array(
			"ID" => "sale_personal",
			"NAME" => GetMessage("CMP_VWS_SECT_TITLE_SALE_PERSONAL")
		)
	),
);
?>