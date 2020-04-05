<?
$DB->Query("DELETE FROM b_event_type WHERE EVENT_NAME in ('VOTE_NEW', 'VOTE_FOR')");
$DB->Query("DELETE FROM b_event_message WHERE EVENT_NAME in ('VOTE_NEW', 'VOTE_FOR')");
?>
