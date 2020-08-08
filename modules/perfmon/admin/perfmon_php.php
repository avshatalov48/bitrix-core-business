<?
use Bitrix\Main\Loader;

define("ADMIN_MODULE_NAME", "perfmon");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
Loader::includeModule('perfmon');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/prolog.php");

IncludeModuleLangFile(__FILE__);

$RIGHT = $APPLICATION->GetGroupRight("perfmon");
if ($RIGHT == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$data = array(
	"tuning" => array(
		"NAME" => GetMessage("PERFMON_PHP_TUNING_NAME"),
		"TITLE" => GetMessage("PERFMON_PHP_TUNING_TITLE"),
		"HEADERS" => array(
			array(
				"id" => "PARAMETER",
				"content" => GetMessage("PERFMON_PHP_TUNING_PARAMETER"),
				"default" => true,
			),
			array(
				"id" => "VALUE",
				"content" => GetMessage("PERFMON_PHP_TUNING_VALUE"),
				"align" => "right",
				"default" => true,
			),
			array(
				"id" => "RECOMMENDATION",
				"content" => GetMessage("PERFMON_PHP_TUNING_RECOMMENDATION"),
				"default" => true,
			),
		),
		"ITEMS" => array(),
	),
);

$php_version = phpversion();
$is_ok = version_compare($php_version, "5.3.0", ">=");
$data["tuning"]["ITEMS"][] = array(
	"PARAMETER" => GetMessage("PERFMON_PHP_VERSION"),
	"IS_OK" => $is_ok,
	"VALUE" => (
	$is_ok?
		$php_version:
		"<span class=\"errortext\">".$php_version."</span>"
	),
	"RECOMMENDATION" => GetMessage("PERFMON_PHP_VERSION_REC", array("#value#" => "5.3.0")),
);


$open_basedir = ini_get('open_basedir');
$is_ok = $open_basedir == '';
$data["tuning"]["ITEMS"][] = array(
	"PARAMETER" => "open_basedir",
	"IS_OK" => $is_ok,
	"VALUE" => "&nbsp;".$open_basedir,
	"RECOMMENDATION" => GetMessage("PERFMON_PHP_OPEN_BASEDIR_REC"),
);


if (version_compare($php_version, "5.1.0", ">="))
{
	$size = CPerfAccel::unformat(ini_get('realpath_cache_size'));
	$is_ok = ($size >= 4 * 1024 * 1024);
	$data["tuning"]["ITEMS"][] = array(
		"PARAMETER" => "realpath_cache_size",
		"IS_OK" => $is_ok,
		"VALUE" => ini_get('realpath_cache_size'),
		"RECOMMENDATION" => GetMessage("PERFMON_PHP_PATH_CACHE_REC2"),
	);
}

$arKnownAccels = array(
	'apc' => '<a href="http://pecl.php.net/package/APC">APC</a>',
	'xcache' => '<a href="http://xcache.lighttpd.net/">XCache</a>',
	'zend_accelerator' => '<a href="http://www.zend.com/products/platform">Zend Accelerator</a>',
	'wincache' => '<a href="http://learn.iis.net/page.aspx/678/using-windows-cache-extension-for-php/">Windows Cache Extension for PHP</a>',
	'zendopcache' => '<a href="http://pecl.php.net/package/ZendOpcache">ZendOpcache</a>',
);

$allAccelerators = CPerfomanceMeasure::GetAllAccelerators();
if (!$allAccelerators)
{
	$data["tuning"]["ITEMS"][] = array(
		"PARAMETER" => GetMessage("PERFMON_PHP_PRECOMPILER"),
		"IS_OK" => false,
		"VALUE" => GetMessage("PERFMON_PHP_PRECOMPILER_NOT_INSTALLED"),
		"RECOMMENDATION" => GetMessage("PERFMON_PHP_PRECOMPILER_REC")."<br>".implode("<br>", $arKnownAccels),
	);
}
else
{
	$workingAccel = null;
	foreach ($allAccelerators as $accel)
	{
		if ($accel->IsWorking())
		{
			$workingAccel = $accel;
			$arRecommendations = $accel->GetRecommendations();
			foreach ($arRecommendations as $i => $ar)
				$data["tuning"]["ITEMS"][] = $ar;
			break;
		}
	}
	
	if ($workingAccel === null)
	{
		foreach ($allAccelerators as $accel)
		{
			$arRecommendations = $accel->GetRecommendations();
			foreach ($arRecommendations as $i => $ar)
				$data["tuning"]["ITEMS"][] = $ar;
		}
	}
}

$sTableID = "tbl_perfmon_panel";

$APPLICATION->SetTitle(GetMessage("PERFMON_PHP_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

foreach ($data as $i => $arTable)
{
	$lAdmin = new CAdminList($sTableID.$i);

	$lAdmin->BeginPrologContent();
	if (array_key_exists("TITLE", $arTable))
		echo "<h4>".$arTable["TITLE"]."</h4>\n";
	$lAdmin->EndPrologContent();

	$lAdmin->AddHeaders($arTable["HEADERS"]);

	$rsData = new CDBResult;
	$rsData->InitFromArray($arTable["ITEMS"]);
	$rsData = new CAdminResult($rsData, $sTableID.$i);

	$j = 0;
	while ($arRes = $rsData->NavNext(true, "f_"))
	{
		$row =& $lAdmin->AddRow($j++, $arRes);
		$row->AddViewField("PARAMETER", $arRes["PARAMETER"]);
		if ($arRes["IS_OK"])
		{
			$row->AddViewField("VALUE", $arRes["VALUE"]."&nbsp;");
			$row->AddViewField("RECOMMENDATION", "&nbsp;");
		}
		else
		{
			$row->AddViewField("VALUE", "<span class=\"errortext\">".$arRes["VALUE"]."&nbsp;</span>");
			$row->AddViewField("RECOMMENDATION", $arRes["RECOMMENDATION"]);
		}
	}
	$lAdmin->CheckListMode();
	$lAdmin->DisplayList();
}

echo BeginNote(), "<a href=\"phpinfo.php?test_var1=AAA&amp;test_var2=BBB\">".GetMessage("PERFMON_PHP_SETTINGS")."</a>", EndNote();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
