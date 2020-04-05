<?
$APPLICATION->IncludeFile("support/ticket_message_edit/default.php", array(
	"ID"						=> $_REQUEST["ID"],				// ID сообщения
	"TICKET_ID"					=> $_REQUEST["TICKET_ID"],		// ID обращения
	"TICKET_LIST_URL"			=> "ticket_list.php",			// страница списка обращений
	"TICKET_EDIT_URL"			=> "ticket_edit.php",			// страница редактирования обращения
	));
?>