<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

__IncludeLang(dirname(__FILE__)."/lang/".LANGUAGE_ID."/pinterest.php");
$name = "pinterest";
$title = GetMessage("BOOKMARK_HANDLER_PINTEREST");
$icon_url_template = "
<a
	href=\"https://www.pinterest.com/pin/create/button/?url=#PAGE_URL_ENCODED#&description=#PAGE_TITLE_UTF_ENCODED#\"
	data-pin-do=\"buttonPin\"
	data-pin-config=\"above\"
	onclick=\"window.open(this.href,'','toolbar=0,status=0,width=750,height=561');return false;\"
	target=\"_blank\"
	style=\"background: #CB2027\"
	class=\"fb\"
	title=\"".$title."\"
><i class=\"fa fa-pinterest\"></i></a>\n";
$sort = 500;
?>