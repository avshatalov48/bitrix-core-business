<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(strlen($_REQUEST["order"])>0)
{
	$_SESSION["IDEA_SORT_ORDER"] = $_REQUEST["order"];
	LocalRedirect($APPLICATION->GetCurPageParam("", array("order")));
}
?>