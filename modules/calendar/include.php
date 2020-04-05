<?
/*patchlimitationmutatormark1*/
IncludeModuleLangFile(__FILE__);

if (!function_exists('array_column'))
{
	function array_column($input, $column_key, $index_key = null)
	{
		$arr = array_map(function($d) use ($column_key, $index_key)
		{
			if (!isset($d[$column_key]))
			{
				return null;
			}
			if ($index_key !== null)
			{
				return array($d[$index_key] => $d[$column_key]);
			}
			return $d[$column_key];
		}, $input);

		if ($index_key !== null)
		{
			$tmp = array();
			foreach ($arr as $ar)
			{
				$tmp[key($ar)] = current($ar);
			}
			$arr = $tmp;
		}
		return $arr;
	}
}

global $DBType;
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
		'/bitrix/js/calendar/userfield/crm_entity_editor_resourcebooking.js',
	),
	'css' => array('/bitrix/js/calendar/userfield/resourcebooking.css'),
	'lang' => '/bitrix/modules/calendar/lang/'.LANGUAGE_ID.'/lib/userfield/resourcebooking.php',
	'rel' => array('uf', 'popup')
));

$basePath = '/bitrix/js/calendar/new/';
CJSCore::RegisterExt('event_calendar', array(
	'js' => array(
		$basePath.'calendar-core.js',
		$basePath.'calendar-view.js',
		$basePath.'calendar-view-transition.js',
		$basePath.'calendar-entry.js',
		$basePath.'calendar-section.js',
		$basePath.'calendar-controls.js',
		$basePath.'calendar-dialogs.js',
		$basePath.'calendar-simple-popup.js',
		$basePath.'calendar-simple-view-popup.js',
		$basePath.'calendar-section-slider.js',
		$basePath.'calendar-settings-slider.js',
		$basePath.'calendar-edit-entry-slider.js',
		$basePath.'calendar-view-entry-slider.js',
		$basePath.'calendar-sync-slider.js',
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
	'rel' => array('date', 'dnd')
));
?>