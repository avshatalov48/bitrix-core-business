<?
$DB->Query("DELETE FROM b_event_type WHERE EVENT_NAME in (
	'NEW_LEARNING_TEXT_ANSWER',
)");

$DB->Query("DELETE FROM b_event_message WHERE EVENT_NAME in (
	'NEW_LEARNING_TEXT_ANSWER',
)");
?>