<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

__IncludeLang(__DIR__."/lang/".LANGUAGE_ID."/lj.php");
$name = "lj";
$title = GetMessage("BOOKMARK_HANDLER_LJ");
$icon_url_template = "<script>\n".
	"if (__function_exists('lj_click') == false) \n".
	"{\n".
		"function lj_click(url, title) \n".
		"{ \n".
			"window.open('http://www.livejournal.com/update.bml?event='+encodeURIComponent(url)+'&subject='+encodeURIComponent(title),'sharer','toolbar=0,status=0,resizable=1,scrollbars=1,width=700,height=436'); \n".
			"return false; \n".
		"} \n".
	"}\n".
	"</script>\n".
	"<a href=\"http://www.livejournal.com/update.bml?event=#PAGE_URL#&subject=#PAGE_TITLE#\" onclick=\"return lj_click('#PAGE_URL#', '#PAGE_TITLE#');\" target=\"_blank\" class=\"lj\" title=\"".$title."\"></a>\n";
$sort = 500;
?>