<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

IncludeTemplateLangFile(__FILE__);

$TEMPLATE["standard.php"] = Array("name"=>GetMessage("standart"), "sort"=>1);
$TEMPLATE["page_inc.php"] = Array("name"=>GetMessage("page_inc"), "sort"=>2);
$TEMPLATE["sect_inc.php"] = Array("name"=>GetMessage("sect_inc"), "sort"=>3);
?>