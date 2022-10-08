<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

__IncludeLang(__DIR__."/lang/".LANGUAGE_ID."/vk.php");
$name = "vk";
$title = GetMessage("BOOKMARK_HANDLER_VK");
$icon_url_template = "
<a
	href=\"http://vkontakte.ru/share.php?url=#PAGE_URL_ENCODED#&title=#PAGE_TITLE_UTF_ENCODED#\"
	onclick=\"window.open(this.href,'','toolbar=0,status=0,width=626,height=436');return false;\"
	target=\"_blank\"
	style=\"background: #446690\"
	class=\"vk\"
	title=\"".$title."\"
><i class=\"fa fa-vk\"></i></a>\n";
$sort = 100;
?>