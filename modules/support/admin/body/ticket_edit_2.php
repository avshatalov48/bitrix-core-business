<?
$APPLICATION->IncludeFile("support/ticket_edit/default.php", Array(
	"ID"						=>	$_REQUEST["ID"],			// ID обращения
	"TICKET_LIST_URL"			=>	"ticket_list.php",			// страница списка обращений
	"TICKET_MESSAGE_EDIT_URL"	=>	"ticket_message_edit.php"	// страница редактирования сообщения
	)
);
?>