<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => GetMessage("EVENT_CALENDAR_EDIT_FORM"),
	"DESCRIPTION" => GetMessage("EVENT_CALENDAR_EDIT_FORM_DESCRIPTION"),
	"COMPLEX" => "N",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "event_calendar",
			"NAME" => GetMessage("EVENT_CALENDAR")
		)
	),
);
?>