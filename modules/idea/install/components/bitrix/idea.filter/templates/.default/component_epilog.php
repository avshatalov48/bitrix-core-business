<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if($_REQUEST["order"] <> '')
{
	$_SESSION["IDEA_SORT_ORDER"] = $_REQUEST["order"];
	LocalRedirect($APPLICATION->GetCurPageParam("", array("order")));
}
?>