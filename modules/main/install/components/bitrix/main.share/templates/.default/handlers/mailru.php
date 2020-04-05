<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

__IncludeLang(dirname(__FILE__)."/lang/".LANGUAGE_ID."/mailru.php");
$name = "mailru";
$title = GetMessage("BOOKMARK_HANDLER_MAILRU");
$icon_url_template = "<script>\n".
	"if (__function_exists('mailru_click') == false) \n".
	"{\n".
		"function mailru_click(url) \n".
		"{ \n".
			"window.open('http://connect.mail.ru/share?share_url='+encodeURIComponent(url),'sharer','toolbar=0,status=0,resizable=1,scrollbars=1,width=626,height=436'); \n".
			"return false; \n".
		"} \n".
	"}\n".
	"</script>\n".
	"<a href=\"http://connect.mail.ru/share?share_url=#PAGE_URL#\" onclick=\"return mailru_click('#PAGE_URL#');\" target=\"_blank\" class=\"mailru\" title=\"".$title."\"></a>\n";
$sort = 600;
?>