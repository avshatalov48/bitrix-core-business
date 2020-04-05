<?
$arr["EVENT_NAME"] = "NEW_FORUM_MESSAGE";
$arr["LID"] = LANGUAGE_ID;
$arr["EMAIL_FROM"] = "#DEFAULT_EMAIL_FROM#";
$arr["EMAIL_TO"] = "#DEFAULT_EMAIL_FROM#";
$arr["SUBJECT"] = "#SITE_NAME#: ".GetMessage("F_USER_REGISTERED");
$arr["MESSAGE"] = GetMessage("F_USER_REGISTERED_TEXT");
$arTemplates[] = $arr;
?>