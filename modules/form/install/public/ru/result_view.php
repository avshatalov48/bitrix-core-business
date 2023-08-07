<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Просмотр анкеты");
?>
<?
$APPLICATION->IncludeFile("form/result_view/default.php", array(
	"RESULT_ID"				=> $_REQUEST["RESULT_ID"],	// ID результата
	"SHOW_ADDITIONAL"		=> "N",						// показать дополнительные поля веб-формы ?
	"SHOW_ANSWER_VALUE"		=> "N",						// показать значение параметра ANSWER_VALUE ?
	"SHOW_STATUS"			=> "Y",						// показать текущий статус результата ?
	"EDIT_URL"				=> "result_edit.php",		// страница редактирования результата
	"CHAIN_ITEM_TEXT"		=> "Список анкет",			// дополнительный пункт в навигационную цепочку
	"CHAIN_ITEM_LINK"		=> "result_list.php?WEB_FORM_ID=".$_REQUEST["WEB_FORM_ID"], // ссылка на доп. пункте в навигационной цепочке
	));
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>