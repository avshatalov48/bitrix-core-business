<?php
/*patchlimitationmutatormark1*/
IncludeModuleLangFile(__FILE__);


CModule::AddAutoloadClasses(
	"calendar",
	array(
		"CCalendar" => "classes/general/calendar.php",
		"CCalendarSceleton" => "classes/general/calendar_sceleton.php",
		"CCalendarEvent" => "classes/general/calendar_event.php",
		"CCalendarSect" => "classes/general/calendar_sect.php",
		"CCalendarType" => "classes/general/calendar_type.php",
		"CCalendarPlanner" => "classes/general/calendar_planner.php",
		"CCalendarWebService" => "classes/general/calendar_webservice.php",
		"CCalendarNotifySchema" => "classes/general/calendar_notify_schema.php",
		"CCalendarPullSchema" => "classes/general/calendar_notify_schema.php",
		"CCalendarEventHandlers" => "classes/general/calendar_event_handlers.php",
		"CCalendarRestService" => "classes/general/calendar_restservice.php",
		"CCalendarLiveFeed" => "classes/general/calendar_livefeed.php",
		"CCalendarRequest" => "classes/general/calendar_request.php",
		"CCalendarNotify" => "classes/general/calendar_notify.php",
		"CCalendarUserSettings" => "classes/general/calendar_user_settings.php",
		"CCalendarSync" => "classes/general/calendar_sync.php",
		"CCalendarReminder" => "classes/general/calendar_reminder.php",
		"CCalendarLocation" => "classes/general/calendar_location.php",
	)
);

/*patchlimitationmutatormark2*/

CJSCore::RegisterExt('userfield_resourcebooking', array(
	'js' => array(
		'/bitrix/js/calendar/userfield/resourcebooking.js',
		'/bitrix/js/calendar/userfield/resourcebooking-webform-field.js',
		'/bitrix/js/calendar/userfield/resourcebooking-webform-live.js',
		'/bitrix/js/calendar/userfield/resourcebooking-webform-settings.js',
		'/bitrix/js/calendar/userfield/resourcebooking-crm-entity-editor.js',
	),
	'css' => array('/bitrix/js/calendar/userfield/resourcebooking.css'),
	'lang' => '/bitrix/modules/calendar/lang/'.LANGUAGE_ID.'/lib/userfield/resourcebooking.php',
	'rel' => array('ui.design-tokens', 'ui.fonts.opensans', 'uf', 'popup', 'translit', 'date', 'ajax',)
));

$basePath = '/bitrix/js/calendar/new/';
CJSCore::RegisterExt('event_calendar', array(
	'js' => array(
		$basePath.'calendar-core.js',
		$basePath.'calendar-view.js',
		$basePath.'calendar-view-day-week.js',
		$basePath.'calendar-view-month.js',
		$basePath.'calendar-view-list.js',
		$basePath.'calendar-view-custom.js',
		$basePath.'calendar-view-transition.js',
		$basePath.'calendar-entry.js',
		$basePath.'calendar-section.js',
		$basePath.'calendar-controls.js',
		$basePath.'calendar-util.js',
		$basePath.'calendar-search.js'
	),
	'lang' => '/bitrix/modules/calendar/classes/general/calendar_js.php',
	'css' => array(
		$basePath.'calendar.css',
		'/bitrix/components/bitrix/calendar.grid/templates/.default/style.css'
	),
	'rel' => array('ajax', 'window', 'popup', 'access', 'date', 'viewer', 'socnetlogdest', 'dnd')
));

CJSCore::RegisterExt('calendar_planner', array(
	'js' => array(
		'/bitrix/js/calendar/planner.js'
	),
	'css' => '/bitrix/js/calendar/planner.css',
	'lang' => '/bitrix/modules/calendar/classes/general/calendar_planner.php',
	'rel' => array('date', 'dnd', 'helper', 'ui.fonts.opensans')
));
