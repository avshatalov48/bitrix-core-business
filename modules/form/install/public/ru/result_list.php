<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Анкеты");
?>
<?
$WEB_FORM_NAME = $_REQUEST["WEB_FORM_NAME"];
if ($WEB_FORM_NAME == '') $WEB_FORM_NAME = "ANKETA";
?>
<?
$APPLICATION->IncludeFile("form/result_list/default.php", array(
	"WEB_FORM_ID"			=> $_REQUEST["WEB_FORM_ID"],	// ID веб-формы 
	"WEB_FORM_NAME"			=> $WEB_FORM_NAME,				// символьный код веб-формы
	"VIEW_URL"				=> "result_view.php",			// страница просмотра результатов
	"EDIT_URL"				=> "result_edit.php",			// страница редактирования результатов
	"NEW_URL"				=> "result_new.php",			// страница создания нового результата
	"SHOW_ADDITIONAL"		=> "N",							// показать дополнительные поля веб-формы в таблице результатов ?
	"SHOW_ANSWER_VALUE"		=> "N",							// показать значение ANSWER_VALUE в таблице результатов ?
	"SHOW_STATUS"			=> "Y",							// показать статус каждого результата в таблице результатов ?
	"NOT_SHOW_FILTER"		=> "",							// коды полей которые нельзя показывать в фильтре (через запятую)
	"NOT_SHOW_TABLE"		=> ""							// коды полей которые нельзя показывать в таблице (через запятую)
	));
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>