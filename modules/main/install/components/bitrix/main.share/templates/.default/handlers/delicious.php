<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

__IncludeLang(dirname(__FILE__)."/lang/".LANGUAGE_ID."/delicious.php");
$name = "delicious";
$title = GetMessage("BOOKMARK_HANDLER_DELICIOUS");
$icon_url_template = "<script>\n".
	"if (__function_exists('delicious_click') == false)\n".
	"{\n".
		"function delicious_click(url, title) \n".
		"{\n". 
			"window.open('http://delicious.com/save?v=5&amp;noui&amp;jump=close&amp;url='+encodeURIComponent(url)+'&amp;title='+encodeURIComponent(title),'sharer','toolbar=0,status=0,width=626,height=550'); \n".
			"return false; \n".
		"}".
	"}".
	"</script>\n".
	"<a href=\"http://delicious.com/save\" onclick=\"return delicious_click('#PAGE_URL#', '#PAGE_TITLE#');\" target=\"_blank\" class=\"delicious\" title=\"".$title."\"></a>";
$sort = 300;
?>