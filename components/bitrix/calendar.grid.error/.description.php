<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => GetMessage("EVENT_CALENDAR_GRID_ERROR"),
	"DESCRIPTION" => GetMessage("EVENT_CALENDAR_GRID_ERROR_DESCRIPTION"),
	"ICON" => "/images/icon.gif",
	"COMPLEX" => "N",
	"PATH" => [
		"ID" => "content",
		"CHILD" => [
			"ID" => "event_calendar",
			"NAME" => GetMessage("EVENT_CALENDAR")
		],
	],
);
?>