<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CBXFeatures::IsFeatureEnabled('SaleReports'))
	return false;

$arDescription = Array(
	"NAME" =>GetMessage("GD_ORDERS_NAME"),
	"DESCRIPTION" =>GetMessage("GD_ORDERS_DESC"),
	"ICON"	=>"",
	"GROUP" => Array("ID"=>"admin_store"),
	"TITLE_ICON_CLASS" => "bx-gadgets-no-padding",
	"AI_ONLY" => true,
	"SALE_ONLY" => true
);
?>
