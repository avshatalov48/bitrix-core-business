<?
$DB_test = CDatabase::GetModuleConnection("statistic", true);
if(!is_object($DB_test))
	return false;

IncludeModuleLangFile(__FILE__);

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/filter_tools.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/stat_tools.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/ip_tools.php");
/*patchlimitationmutatormark1*/
$dbType = strtolower($DB_test->type);
CModule::AddAutoloadClasses(
	"statistic",
	array(
		"CKeepStatistics" => "classes/general/keepstatistic.php",
		"CAllStatistics" => "classes/general/statistic.php",
		"CStatistics" => "classes/".$dbType."/statistic.php",
		"CAdv" => "classes/".$dbType."/adv.php",
		"CGuest" => "classes/".$dbType."/guest.php",
		"CTraffic" => "classes/".$dbType."/traffic.php",
		"CUserOnline" => "classes/".$dbType."/useronline.php",
		"CStoplist" => "classes/".$dbType."/stoplist.php",
		"CHit" => "classes/general/hit.php",
		"CSession" => "classes/".$dbType."/session.php",
		"CReferer" => "classes/general/referer.php",
		"CPhrase" => "classes/general/phrase.php",
		"CSearcher" => "classes/".$dbType."/searcher.php",
		"CSearcherHit" => "classes/general/searcherhit.php",
		"CPage" => "classes/".$dbType."/page.php",
		"CStatEvent" => "classes/".$dbType."/statevent.php",
		"CStatEventType" => "classes/".$dbType."/stateventtype.php",
		"CAutoDetect" => "classes/".$dbType."/autodetect.php",
		"CCountry" => "classes/general/country.php",
		"CCity" => "classes/general/city.php",
		"CStatRegion" => "classes/general/city.php",
		"CCityLookup" => "classes/general/city.php",
		"CCityLookup_geoip_mod" => "tools/geoip_mod.php",
		"CCityLookup_geoip_extension" => "tools/geoip_extension.php",
		"CCityLookup_geoip_pure" => "tools/geoip_pure.php",
		"CCityLookup_stat_table" => "tools/stat_table.php",
		"CPath" => "classes/general/path.php",

		"CStat" => "classes/general/statistic_old.php",
		"CVisit" => "classes/general/statistic_old.php",
		"CStatCountry" => "classes/general/statistic_old.php",
		"CAllStatistic" => "classes/general/statistic_old.php",
		"CStatistic" => "classes/general/statistic_old.php",
		"CStatResult" => "classes/general/statresult.php",
		"statistic" => "install/index.php",
	)
);
