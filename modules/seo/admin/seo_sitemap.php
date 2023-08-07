<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_admin_before.php");

define('ADMIN_MODULE_NAME', 'seo');

use Bitrix\Main;
use Bitrix\Main\Text\Converter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Seo\Sitemap\Internals\JobTable;
use Bitrix\Seo\Sitemap\Internals\SitemapTable;
use Bitrix\Seo\Sitemap\Internals\RuntimeTable;
use Bitrix\Seo\Sitemap\File;
use Bitrix\Seo\Sitemap\Job;

/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 * @global $by
 * @global $order
 **/

Loc::loadMessages(__DIR__ . '/seo_sitemap.php');

if (!$USER->CanDoOperation('seo_tools'))
{
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

if (!Main\Loader::includeModule('seo'))
{
	require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(Loc::getMessage("SEO_ERROR_NO_MODULE"));
	require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
}

Extension::load('seo.sitemap.generator');

$tableID = "tbl_sitemap";

$oSort = new CAdminSorting($tableID, "ID", "desc");
$adminList = new CAdminList($tableID, $oSort);

if (($arID = $adminList->GroupAction()))
{
	if ($_REQUEST['action_target'] == 'selected')
	{
		$arID = [];
		$rsData = SitemapTable::getList([
			"select" => ["ID"],
		]);

		while ($arRes = $rsData->fetch())
		{
			$arID[] = $arRes['ID'];
		}
	}

	foreach ($arID as $ID)
	{
		$ID = intval($ID);
		if ($ID <= 0)
		{
			continue;
		}

		switch ($_REQUEST['action'])
		{
			case "delete":
				SitemapTable::fullDelete($ID);
				break;
		}
	}
}

$dbSites = Bitrix\Main\SiteTable::getList([
	'order' => ['DEF' => 'DESC', 'NAME' => 'ASC'],
	'select' => ['NAME', 'LID'],
]);

$arSites = [];
while ($arRes = $dbSites->fetch(Converter::getHtmlConverter()))
{
	$arSites[$arRes['LID']] = $arRes;
}

$map = SitemapTable::getMap();
unset($map['SETTINGS']);

$sitemapRes = SitemapTable::getList([
	'order' => [$by => $order],
	"select" => array_keys($map),
]);
$data = new CAdminResult($sitemapRes, $tableID);
$data->NavStart();

$arHeaders = [
	["id" => "ID", "content" => Loc::getMessage("SITEMAP_ID"), "sort" => "ID", "default" => true],
	[
		"id" => "TIMESTAMP_X", "content" => Loc::getMessage('SITEMAP_TIMESTAMP_X'), "sort" => "TIMESTAMP_X",
		"default" => true,
	],
	["id" => "NAME", "content" => Loc::getMessage('SITEMAP_NAME'), "sort" => "NAME", "default" => true],
	//	array("id"=>"ACTIVE", "content"=>Loc::getMessage('SITEMAP_ACTIVE'), "sort"=>"ACTIVE", "default"=>true, "align" => "center"),
	["id" => "SITE_ID", "content" => Loc::getMessage('SITEMAP_SITE_ID'), "sort" => "SITE_ID", "default" => true],
	["id" => "DATE_RUN", "content" => Loc::getMessage('SITEMAP_DATE_RUN'), "sort" => "DATE_RUN", "default" => true],
	["id" => "RUN", "content" => "", "default" => true],
];

$adminList->AddHeaders($arHeaders);
$adminList->NavText($data->GetNavPrint(Loc::getMessage("PAGES")));
while ($sitemap = $data->NavNext())
{
	$id = intval($sitemap['ID']);

	$row = &$adminList->AddRow($sitemap["ID"], $sitemap, "seo_sitemap_edit.php?ID=".$sitemap["ID"]."&lang=".LANGUAGE_ID, Loc::getMessage("SITEMAP_EDIT_TITLE"));

	$row->AddViewField("ID", $sitemap['ID']);
	$row->AddViewField('TIMESTAMP_X', $sitemap['TIMESTAMP_X']);
	$row->AddViewField('DATE_RUN', $sitemap['DATE_RUN'] ? $sitemap['DATE_RUN'] : Loc::getMessage('SITEMAP_DATE_RUN_NEVER'));
	$row->AddViewField('SITE_ID', '<a href="site_edit.php?lang='.LANGUAGE_ID.'&amp;LID='.$sitemap['SITE_ID'].'">['.$sitemap['SITE_ID'].'] '.$arSites[$sitemap['SITE_ID']]['NAME'].'</a>');

	$row->AddField("NAME", '<a href="seo_sitemap_edit.php?ID='.$sitemap["ID"].'&amp;lang='.LANGUAGE_ID.'" title="'.Loc::getMessage("SITEMAP_EDIT_TITLE").'">'.Converter::getHtmlConverter()->encode($sitemap['NAME']).'</a>');
	$row->AddField("RUN", '<input type="button" class="adm-btn-save" value="'.Converter::getHtmlConverter()->encode(Loc::getMessage('SITEMAP_RUN')).'" onclick="generateSitemap('.$sitemap['ID'].')" name="save" id="sitemap_run_button_'.$sitemap['ID'].'" />');

	$row->AddActions([
		[
			"ICON" => "edit",
			"TEXT" => Loc::getMessage("SITEMAP_EDIT"),
			"ACTION" => $adminList->ActionRedirect("seo_sitemap_edit.php?ID="
				. $sitemap["ID"]
				. "&lang="
				. LANGUAGE_ID
			),
			"DEFAULT" => true,
		],
		[
			"ICON" => "move",
			"TEXT" => Loc::getMessage("SITEMAP_RUN"),
			"ACTION" => 'generateSitemap(' . $sitemap['ID'] . ');',
		],
		[
			"ICON" => "delete",
			"TEXT" => Loc::getMessage("SITEMAP_DELETE"),
			"ACTION" => "if(confirm('"
				. \CUtil::JSEscape(Loc::getMessage('SITEMAP_DELETE_CONFIRM'))
				. "')) "
				. $adminList->ActionDoGroup($id, "delete"),
		],
	]);
}

$arDDMenu = [];
$arDDMenu[] = [
	"HTML" => "<b>" . Loc::getMessage("SEO_ADD_SITEMAP_CHOOSE_SITE") . "</b>",
	"ACTION" => false,
];

foreach ($arSites as $arRes)
{
	$arDDMenu[] = [
		"HTML" => "[" . $arRes["LID"] . "] " . $arRes["NAME"],
		"LINK" => "seo_sitemap_edit.php?lang=" . LANGUAGE_ID . "&site_id=" . $arRes['LID'],
	];
}

$aContext = [];
$aContext[] = [
	"TEXT" => Loc::getMessage("SEO_ADD_SITEMAP"),
	"TITLE" => Loc::getMessage("SEO_ADD_SITEMAP_TITLE"),
	"ICON" => "btn_new",
	"MENU" => $arDDMenu,
];

$adminList->AddAdminContextMenu($aContext);
$adminList->AddGroupActionTable(["delete" => GetMessage("MAIN_ADMIN_LIST_DELETE")]);

$adminList->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("SEO_SITEMAP_TITLE"));
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$adminList->DisplayList();

// jobs
$sitemaps = [];
$resSitemaps = SitemapTable::getList(["select" => ["ID"]]);
while ($row = $resSitemaps->fetch())
{
	$sitemaps[] = $row['ID'];
}

$processedJobs = [];
$resJobs = JobTable::query()
	->setSelect(['ID', 'SITEMAP_ID'])
	->whereIn('SITEMAP_ID', $sitemaps)
	->where('STATUS', Job::STATUS_PROCESS)
	->exec()
;
while ($row = $resJobs->fetch())
{
	$job = Job::findJob($row['SITEMAP_ID']);
	if ($job)
	{
		$processedJobs[$row['SITEMAP_ID']] = $job->getData();
	}
}

// robots
$existedSitemaps = [];

if ($arSitemap['SETTINGS']['ROBOTS'] == 'Y')
{
	$sitemapUrl = $sitemapFile->getUrl();

	$robotsFile = new RobotsFile($arSitemap['SITE_ID']);
	$robotsFile->addRule(
		array(RobotsFile::SITEMAP_RULE, $sitemapUrl)
	);

	$arSitemapLinks = $robotsFile->getRules(RobotsFile::SITEMAP_RULE);
	if (count($arSitemapLinks) > 1) // 1 - just added rule
	{
		foreach ($arSitemapLinks as $rule)
		{
			if ($rule[1] != $sitemapUrl)
			{
				$existedSitemaps[] = $rule[1];
			}
		}
	}
}
?>
	<div id="sitemap_generator"></div>

	<script>

		BX.ready(() =>
		{
			const generator = new BX.Seo.Sitemap.Generator(BX('sitemap_generator'));

			<?php foreach ($processedJobs as $id => $data): ?>
				generator.add(<?= $id ?>, <?= \CUtil::PhpToJSObject($data) ?>);
			<?php endforeach; ?>

			BX.addCustomEvent(generator, 'onBeforeDo', event => {
				BX.adminPanel.showWait(BX('sitemap_run_button_' + event.data));
			});
			BX.addCustomEvent(generator, 'onAfterDo', event => {
				BX.adminPanel.closeWait(BX('sitemap_run_button_' + event.data));
			});
			BX.addCustomEvent(generator, 'onFinish', event => {
				window.<?= $tableID ?>.GetAdminList('/bitrix/admin/seo_sitemap.php?lang=<?=LANGUAGE_ID?>');
			});

			window.generateSitemap = function (sitemapId)
			{
				generator.add(sitemapId);
			}
		});
	</script>

<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>