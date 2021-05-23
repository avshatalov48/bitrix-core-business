<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => GetMessage("EVENT_CALENDAR2"),
	"DESCRIPTION" => GetMessage("EVENT_CALENDAR_DESCRIPTION"),
	"ICON" => "/images/icon.gif",
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