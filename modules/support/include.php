<?
global $DB, $APPLICATION;
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin_tools.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/filter_tools.php");
IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/errors.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/support_tools.php");

$db_type = mb_strtolower($DB->type);
CModule::AddAutoloadClasses(
	"support",
	array(
		"CTicket" => "classes/".$db_type."/support.php",
		"CTicketDictionary" => "classes/".$db_type."/dictionary.php",
		"CTicketSLA" => "classes/".$db_type."/sla.php",
		"CTicketReminder" => "classes/".$db_type."/reminder.php",
		"CSupportSuperCoupon" => "classes/general/coupons.php",
		"CSupportEMail" => "classes/general/email.php",
		"CSupportUserGroup" => "classes/general/usergroup.php",
		"CSupportUser2UserGroup" => "classes/general/usertousergroup.php",
		"CSupportTableFields" => "classes/general/tablefields.php",
		"CSupportTimetable" => "classes/general/timetable.php",
		"CSupportTools" => "classes/general/tools.php",
		"CSupportHolidays" => "classes/general/holidays.php",
		"CSupportTimetableCache" => "classes/general/timetablecache.php",
		"CSupportSearch" => "classes/general/search.php",
	)
);

?>