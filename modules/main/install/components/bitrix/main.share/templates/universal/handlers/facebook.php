<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

__IncludeLang(dirname(__FILE__)."/lang/".LANGUAGE_ID."/facebook.php");
$name = "facebook";
$title = GetMessage("BOOKMARK_HANDLER_FACEBOOK");
$icon_url_template = "
<a
	href=\"http://www.facebook.com/share.php?u=#PAGE_URL_ENCODED#&t=#PAGE_TITLE_UTF_ENCODED#\"
	onclick=\"window.open(this.href,'','toolbar=0,status=0,width=611,height=231');return false;\"
	target=\"_blank\"
	class=\"main-share-facebook\"
	rel=\"nofollow\"
	title=\"".$title."\"
></a>\n";
$sort = 200;
?>