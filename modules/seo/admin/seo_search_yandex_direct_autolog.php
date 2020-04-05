<?
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_admin_before.php");

/**
 * Bitrix vars
 * @global $by
 * @global $order
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @global CUserTypeManager $USER_FIELD_MANAGER
 * @global CCacheManager $CACHE_MANAGER
 */

define('ADMIN_MODULE_NAME', 'seo');

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Text\Converter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Seo\Engine;
use Bitrix\Seo\Adv;
use Bitrix\Seo\Service;

Loc::loadMessages(dirname(__FILE__).'/../../main/tools.php');
Loc::loadMessages(dirname(__FILE__).'/menu.php');
Loc::loadMessages(dirname(__FILE__).'/seo_search.php');
Loc::loadMessages(dirname(__FILE__).'/seo_adv.php');

if (!$USER->CanDoOperation('seo_tools'))
{
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

if(!Main\Loader::includeModule('seo'))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(Loc::getMessage("SEO_ERROR_NO_MODULE"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}

$engine = new Engine\YandexDirect();

$tableID = "tbl_yandex_direct_auto_log";

$oSort = new \CAdminSorting($tableID, "ID", "desc");
$adminList = new \CAdminList($tableID, $oSort);

$arHeaders = array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"TIMESTAMP_X", "content"=>Loc::getMessage('SEO_AUTOLOG_TIMESTAMP_X'), "sort" => "TIMESTAMP_X", "default"=>true),
	array("id"=>"SUCCESS", "content"=>Loc::getMessage('SEO_AUTOLOG_SUCCESS'), "sort" => "SUCCESS", "default"=>true),
	array("id"=>"CAMPAIGN", "content"=>Loc::getMessage('SEO_AUTOLOG_CAMPAIGN'), "default"=>true),
	array("id"=>"BANNER", "content"=>Loc::getMessage('SEO_AUTOLOG_BANNER'), "default"=>true),
	array("id"=>"DESCRIPTION", "content"=>Loc::getMessage('SEO_AUTOLOG_DESCRIPTION'), "default"=>true),
);

$adminList->AddHeaders($arHeaders);

$logEntriesList = Adv\AutologTable::getList(array(
	'order' => array($by => $order),
	'filter' => array(
		'=ENGINE_ID' => $engine->getId(),
	),
));

$data = new \CAdminResult($logEntriesList, $tableID);
$data->NavStart();
$adminList->NavText($data->GetNavPrint(Loc::getMessage("PAGES")));

while($entry = $data->NavNext())
{
	$editUrl = "seo_search_yandex_direct_banner_edit.php?lang=".LANGUAGE_ID."&campaign=".$entry["CAMPAIGN_ID"]."&ID=".$entry["BANNER_ID"];

	$row = &$adminList->AddRow($log["ID"], $entry, $editUrl, Loc::getMessage("SEO_BANNER_EDIT_TITLE", array(
		"#ID#" => $entry["BANNER_ID"],
		"#XML_ID#" => $entry["BANNER_XML_ID"],
	)));

	$row->AddViewField("ID", $entry['ID']);
	$row->AddViewField('TIMESTAMP_X', $entry['TIMESTAMP_X']);

	$row->AddViewField('SUCCESS',
		$entry["SUCCESS"] == Adv\AutologTable::SUCCESS
			? '<div style="white-space:nowrap;"><div class="lamp-green" style="display:inline-block;"></div>&nbsp;'.Loc::getMessage("SEO_AUTOLOG_SUCCESS_".$entry["SUCCESS"]).'</div>'
			: '<div style="white-space:nowrap;"><div class="lamp-red" style="display:inline-block;"></div>&nbsp;'.Loc::getMessage("SEO_AUTOLOG_SUCCESS_".$entry["SUCCESS"]).'</div>'

	);

	$row->AddViewField("CAMPAIGN", '<a href="seo_search_yandex_direct_edit.php?lang='.LANGUAGE_ID.'&ID='.$entry["CAMPAIGN_ID"].'">'.$entry["CAMPAIGN_ID"].'</a> (<a href="https://direct.yandex.ru/registered/main.pl?cmd=editCamp&cid='.$entry['CAMPAIGN_XML_ID'].'" target="_blank" title="'.Converter::getHtmlConverter()->encode(Loc::getMessage('SEO_CAMPAIGN_EDIT_EXTERNAL')).'">'.Loc::getMessage('SEO_YANDEX_DIRECT_LINK_TPL', array('#XML_ID#' => $entry['CAMPAIGN_XML_ID'])).'</a>)');

	$row->AddViewField("BANNER", '<a href="'.Converter::getHtmlConverter()->encode($editUrl).'">'.$entry["BANNER_ID"].'</a> (<a href="https://direct.yandex.ru/registered/main.pl?cmd=showCampMultiEdit&bids='.$entry['BANNER_XML_ID'].'&cid='.$entry['CAMPAIGN_XML_ID'].'" target="_blank" title="'.Converter::getHtmlConverter()->encode(Loc::getMessage('SEO_CAMPAIGN_EDIT_EXTERNAL')).'">'.Loc::getMessage('SEO_YANDEX_DIRECT_LINK_TPL', array('#XML_ID#' => $entry['BANNER_XML_ID'])).'</a>)');

	$row->AddViewField('DESCRIPTION', Loc::getMessage("SEO_AUTOLOG_ACTION_".$entry["CAUSE_CODE"], array(
		"#BANNER_ID#" => $entry["BANNER_ID"],
		"#BANNER_XML_ID#" => $entry["BANNER_XML_ID"],
		"#CAMPAIGN_ID#" => $entry["CAMPAIGN_ID"],
		"#CAMPAIGN_XML_ID#" => $entry["CAMPAIGN_XML_ID"],
	)));
}

$adminList->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("SEO_MENU_ADV_AUTOLOG"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$adminList->DisplayList();

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
