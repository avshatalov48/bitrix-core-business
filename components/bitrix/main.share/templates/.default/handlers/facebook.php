<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

__IncludeLang(__DIR__."/lang/".LANGUAGE_ID."/facebook.php");
$name = "facebook";
$title = GetMessage("BOOKMARK_HANDLER_FACEBOOK");
$icon_url_template = "<script>\n".
	"if (__function_exists('fbs_click') == false) \n".
	"{\n".
		"function fbs_click(url, title) \n".
		"{ \n".
			"window.open('http://www.facebook.com/share.php?u='+encodeURIComponent(url)+'&t='+encodeURIComponent(title),'sharer','toolbar=0,status=0,width=626,height=436'); \n".
			"return false; \n".
		"} \n".
	"}\n".
	"</script>\n".
	"<a href=\"http://www.facebook.com/share.php?u=#PAGE_URL#&t=#PAGE_TITLE#\" onclick=\"return fbs_click('#PAGE_URL#', '#PAGE_TITLE#');\" target=\"_blank\" class=\"facebook\" title=\"".$title."\"></a>\n";
$sort = 100;
?>