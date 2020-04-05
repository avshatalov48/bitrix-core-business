<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Редактирование анкеты");
?>
<?
$APPLICATION->IncludeFile("form/result_edit/default.php", array(
	"RESULT_ID"			=> $_REQUEST["RESULT_ID"],			// ID результата
	"EDIT_ADDITIONAL"	=> "N",								// выводить на редактирование дополнительные поля веб-формы ?
	"EDIT_STATUS"		=> "Y",								// выводить форму смены статуса ?
	"LIST_URL"			=> "result_list.php",				// страница со списком результатов
	"VIEW_URL"			=> "result_view.php",				// страница просмотра результата
	"CHAIN_ITEM_TEXT"	=> "Список анкет",					// дополнительный пункт в навигационную цепочку
	"CHAIN_ITEM_LINK"	=> "result_list.php?WEB_FORM_ID=".$_REQUEST["WEB_FORM_ID"], // ссылка на доп. пункте в навигационной цепочке
	));
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>