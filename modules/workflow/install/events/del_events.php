<?
$str = "'WF_STATUS_CHANGE', 'WF_NEW_DOCUMENT', 'WF_IBLOCK_STATUS_CHANGE', 'WF_NEW_IBLOCK_ELEMENT'";
$DB->Query("DELETE FROM b_event_type WHERE EVENT_NAME in ($str)");
$DB->Query("DELETE FROM b_event_message WHERE EVENT_NAME in ($str)");
?>