<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заполнение анкеты");
?>
<?
$APPLICATION->IncludeFile("form/result_new/default.php", array(
	"WEB_FORM_ID"		=> $_REQUEST["WEB_FORM_ID"],		// ID веб-формы
	"LIST_URL"			=> "result_list.php",				// страница списка результатов
	"EDIT_URL"			=> "result_edit.php",				// страница редактирования результата
	"CHAIN_ITEM_TEXT"	=> "Список анкет",					// дополнительный пункт в навигационную цепочку
	"CHAIN_ITEM_LINK"	=> "result_list.php?WEB_FORM_ID=".$_REQUEST["WEB_FORM_ID"], // ссылка на доп. пункте в навигационной цепочке
	));
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>