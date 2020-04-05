<?php
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_admin_before.php");

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @global CUserTypeManager $USER_FIELD_MANAGER
 * @global CCacheManager $CACHE_MANAGER
 */

define('ADMIN_MODULE_NAME', 'seo');

use Bitrix\Main;
use Bitrix\Main\Text\Converter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Seo\Engine;
use Bitrix\Seo\Adv;
use Bitrix\Seo\Service;


Loc::loadMessages(dirname(__FILE__).'/../../main/tools.php');
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

if(!Main\Loader::includeModule('socialservices'))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(Loc::getMessage("SEO_ERROR_NO_MODULE_SOCSERV"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}

$engine = new Engine\YandexDirect();
$currentUser = $engine->getCurrentUser();
$bNeedAuth = !is_array($currentUser);

//get string of campaign CURRENCY name
try
{
	$clientsSettings = $engine->getClientsSettings();
	$clientCurrency = current($clientsSettings);
	$clientCurrency = Loc::getMessage('SEO_YANDEX_CURRENCY__'.$clientCurrency['Currency']);
}
catch(Engine\YandexDirectException $e)
{
	$seoproxyAuthError = new CAdminMessage(array(
		"TYPE" => "ERROR",
		"MESSAGE" => Loc::getMessage('SEO_YANDEX_SEOPROXY_AUTH_ERROR'),
		"DETAILS" => $e->getMessage(),
	));
}

$bReadOnly = $bNeedAuth;
$bAllowUpdate = !$bNeedAuth;

$message = null;

$request = Main\Context::getCurrent()->getRequest();

$back_url = isset($request["back_url"]) ? $request["back_url"] : '';

$ID = intval($request["ID"]);

$bShowStats = $ID > 0 && $bAllowUpdate;
$bShowOrderStats = $bShowStats
	&& Main\ModuleManager::isModuleInstalled('sale')
	&& Main\Loader::includeModule('currency');

if($ID > 0)
{
	$dbRes = Adv\YandexCampaignTable::getByPrimary($ID);
	$campaign = $dbRes->fetch();
	if($campaign)
	{
		$campaign['SETTINGS']['StartDate'] = ConvertTimeStamp(MakeTimeStamp($campaign['SETTINGS']['StartDate'], "YYYY-MM-DD"));
		$campaign['SETTINGS']['EmailNotification']['SendWarn'] = $campaign['SETTINGS']['EmailNotification']['SendWarn'] != Engine\YandexDirect::BOOL_NO;

		if($campaign['OWNER_ID'] != $currentUser['id'])
		{
			$bReadOnly = true;
			$bAllowUpdate = false;

			$message = new CAdminMessage(array(
				"TYPE" => "ERROR",
				"MESSAGE" => Loc::getMessage('SEO_CAMPAIGN_WRONG_OWNER', array("#USERINFO#" => "(".$campaign["OWNER_ID"].") ".$campaign["OWNER_NAME"]))
			));
		}
	}
	else
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

		$message = new CAdminMessage(array(
			"TYPE" => "ERROR",
			"DETAILS" => Loc::getMessage("SEO_ERROR_NO_CAMPAIGN"),
			"HTML" => true
		));
		echo $message->Show();

		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	}
}

if($bShowStats)
{
	if(
		isset($_REQUEST['date_from']) && isset($_REQUEST['date_to'])
		&& Main\Type\Date::isCorrect($_REQUEST['date_from'])
		&& Main\Type\Date::isCorrect($_REQUEST['date_to'])
	)
	{
		$statsDateStart = new Main\Type\Date($_REQUEST['date_from']);
		$statsDateFinish = new Main\Type\Date($_REQUEST['date_to']);
	}
	else
	{
		$statsDateStart = new Main\Type\Date();
		$statsDateStart->add("-".Engine\YandexDirect::MAX_STAT_DAYS_DELTA." days");

		$statsDateFinish = new Main\Type\Date();
	}
}

if($bShowStats)
{
	$tableID = "tbl_yandex_campaign_banner_stats";

	//hack to prevent browser history insertion
	if (isset($_REQUEST["mode"])&&($_REQUEST["mode"]=='list' || $_REQUEST["mode"]=='frame'))
	{
		$_REQUEST['admin_history'] = 'Y';
	}

	$oSort = new \CAdminSorting($tableID, "BANNER_CTR", "desc");
	$statsAdminList = new \CAdminList($tableID, $oSort);

	$campaignStats = Adv\YandexStatTable::getList(array(
		'order' => array($by => $order),
		'group' => array("BANNER_ID", "CURRENCY"),
		'filter' => array(
			"=CAMPAIGN_ID" => $campaign['ID'],
			">=DATE_DAY" => $statsDateStart,
			"<DATE_DAY" => $statsDateFinish,
		),
		'select' => array(
			"BANNER_ID", "CURRENCY",
			"BANNER_NAME" => "BANNER.NAME", "BANNER_XML_ID" => "BANNER.XML_ID",
			"BANNER_SUM", "BANNER_SHOWS", "BANNER_CLICKS", "BANNER_CTR",
		),
		"runtime" => array(
			new Main\Entity\ExpressionField('BANNER_SUM', 'SUM(SUM)'),
			new Main\Entity\ExpressionField('BANNER_SHOWS', 'SUM(SHOWS)'),
			new Main\Entity\ExpressionField('BANNER_CLICKS', 'SUM(CLICKS)'),
			new Main\Entity\ExpressionField('BANNER_CTR', '100*SUM(CLICKS)/SUM(SHOWS)'),
		),
	));

	$data = new \CAdminResult($campaignStats, $tableID);
	$data->NavStart();

	$statsAdminList->NavText($data->GetNavPrint(Loc::getMessage("PAGES")));

	$first = true;

	$statsBanners = array();
	$currency = $clientCurrency;
	while($banner = $data->NavNext())
	{
		$statsBanners[$banner['BANNER_ID']] = $banner;
		if($banner['CURRENCY'])
		{
			$currency = $banner['CURRENCY'];
		}
	}

	$arHeaders = array(
		array("id"=>"ID", "content"=>Loc::getMessage("SEO_BANNER_ID"), "sort"=>"BANNER_ID", "default"=>true),
		array("id"=>"NAME", "content"=>Loc::getMessage('SEO_BANNER_NAME'), "sort"=>"BANNER_NAME", "default"=>true),
		array("id"=>"XML_ID", "content"=>Loc::getMessage('SEO_BANNER_XML_ID'), "sort"=>"BANNER_XML_ID", "default"=>true),
		array("id"=>"BANNER_SUM", "content"=>Loc::getMessage('SEO_YANDEX_STATS_GRAPH_AXIS_SUM').', '.$currency, "sort" => "BANNER_SUM", "default"=>true, "align" => "right"),
		array("id"=>"BANNER_SHOWS", "content"=>Loc::getMessage('SEO_YANDEX_STATS_GRAPH_AXIS_SHOWS'), "sort" => "BANNER_SHOWS", "default"=>true, "align" => "right"),
		array("id"=>"BANNER_CLICKS", "content"=>Loc::getMessage('SEO_YANDEX_STATS_GRAPH_AXIS_CLICKS'), "sort" => "BANNER_CLICKS", "default"=>true, "align" => "right"),
		array("id"=>"BANNER_CTR", "content"=>Loc::getMessage('SEO_FORECAST_CTR'), "sort" => "BANNER_CTR", "default"=>true, "align" => "right"),
	);

	if($bShowOrderStats)
	{
		$arHeaders = array_merge(
			array_slice($arHeaders, 0, 3),
			array(
				array("id"=>"BANNER_SUM_ORDER", "content"=>Loc::getMessage('SEO_YANDEX_STATS_SUM_ORDER'), /*"sort" => "BANNER_SUM", */"default"=>true, "align" => "right"),
			),
			array_slice($arHeaders, 3)
		);
	}

	$statsAdminList->AddHeaders($arHeaders);

	if(count($statsBanners) > 0)
	{
		$dbRes = Adv\OrderTable::getList(array(
			'filter' => array(
				'@BANNER_ID' => array_keys($statsBanners),
				'=CAMPAIGN_ID' => $campaign['ID'],
				'=PROCESSED' => Adv\OrderTable::PROCESSED,
				">=TIMESTAMP_X" => $statsDateStart,
				"<TIMESTAMP_X" => $statsDateFinish,

			),
			'group' => array(
				'BANNER_ID'
			),
			'select' => array('BANNER_ID', 'BANNER_SUM'),
			'runtime' => array(
				new Main\Entity\ExpressionField('BANNER_SUM', 'SUM(SUM)'),
			),
		));
		while($realSale = $dbRes->fetch())
		{
			$statsBanners[$realSale['BANNER_ID']]['BANNER_SUM_ORDER'] = $realSale['BANNER_SUM'];
		}

		foreach($statsBanners as $banner)
		{
			$editUrl = "seo_search_yandex_direct_banner_edit.php?lang=".LANGUAGE_ID."&campaign=".$campaign['ID']."&ID=".$banner["BANNER_ID"];

			$row = &$statsAdminList->AddRow($banner["BANNER_ID"], $banner, $editUrl, Loc::getMessage("SEO_BANNER_EDIT_TITLE", array(
				"#ID#" => $banner["BANNER_ID"],
				"#XML_ID#" => $banner["BANNER_XML_ID"],
			)));

			$row->AddViewField("ID", $banner['BANNER_ID']);
			$row->AddField("NAME", '<a href="'.Converter::getHtmlConverter()->encode($editUrl).'" title="'.Loc::getMessage("SEO_BANNER_EDIT_TITLE", array(
					"#ID#" => $banner["BANNER_ID"],
					"#XML_ID#" => $banner["BANNER_XML_ID"],
				)).'">'.Converter::getHtmlConverter()->encode($banner['BANNER_NAME']).'</a>');


			$row->AddViewField('XML_ID', '<a href="https://direct.yandex.ru/registered/main.pl?cmd=showCampMultiEdit&bids='.$banner['BANNER_XML_ID'].'&cid='.$campaign['XML_ID'].'" target="_blank" title="'.Converter::getHtmlConverter()->encode(Loc::getMessage('SEO_CAMPAIGN_EDIT_EXTERNAL')).'">'.Loc::getMessage('SEO_YANDEX_DIRECT_LINK_TPL', array('#XML_ID#' => $banner['BANNER_XML_ID'])).'</a>');


			$row->AddViewField("BANNER_SUM", number_format($banner['BANNER_SUM'], 2, '.', ' '));

			$row->AddViewField("BANNER_SHOWS", $banner['BANNER_SHOWS']);
			$row->AddViewField("BANNER_CLICKS", $banner['BANNER_CLICKS']);
			$row->AddViewField("BANNER_CTR", number_format($banner['BANNER_CTR'], 2, '.', ' '));
			if($bShowOrderStats)
			{
				$row->AddViewField("BANNER_SUM_ORDER", \CCurrencyLang::CurrencyFormat(doubleval($banner['BANNER_SUM_ORDER']), \Bitrix\Currency\CurrencyManager::getBaseCurrency(), true));
			}
		}
	}

	$statsAdminList->checkListMode();
}

if($ID <= 0)
{
	$campaign = array(
		"SETTINGS" => array(
			"Name" => Loc::getMessage("SEO_CAMPAIGN_NAME_DEFAULT", array("#DATE#" => ConvertTimeStamp())),
			"FIO" => $currentUser['real_name'],
			"StartDate" => ConvertTimeStamp(),
			"EmailNotification" => array(
				"Email" => $USER->GetEmail()
					? $USER->GetEmail()
					: Main\Config\Option::get('main', 'email_from', ''),
				"SendWarn" => false,
				"MoneyWarningValue" => Adv\YandexCampaignTable::MONEY_WARNING_VALUE_DEFAULT,
				"WarnPlaceInterval" => Adv\YandexCampaignTable::MONEY_WARN_PLACE_INTERVAL_DEFAULT,
			),
			"Strategy" => array(
				"StrategyName" => Adv\YandexCampaignTable::STRATEGY_WEEKLY_BUDGET,
			),
			"MinusKeywords" => array(),
		),
	);
}

$strategyTitle = Loc::getMessage('SEO_CAMPAIGN_STRATEGY_'.$campaign["SETTINGS"]['Strategy']['StrategyName']);
if(!$strategyTitle)
{
	$strategyTitle = $campaign["SETTINGS"]['Strategy']['StrategyName'];
}

if(!$bReadOnly)
{
	if(!in_array($campaign["SETTINGS"]['Strategy']['StrategyName'], Adv\YandexCampaignTable::$supportedStrategy))
	{
		$bReadOnly = true;

		$message = new CAdminMessage(array(
			"TYPE" => "ERROR",
			"MESSAGE" => Loc::getMessage("SEO_CAMPAIGN_STRATEGY_NOT_SUPPORTED", array(
				"#STRATEGY#" => $strategyTitle,
			))
		));
	}
}

$aTabs = array(
	array(
		"DIV" => "edit_main",
		"TAB" => Loc::getMessage("SEO_CAMPAIGN_TAB_MAIN"),
		"TITLE" => Loc::getMessage("SEO_CAMPAIGN_TAB_MAIN_TITLE"),
	),
	array(
		"DIV" => "edit_strategy",
		"TAB" => Loc::getMessage("SEO_CAMPAIGN_TAB_STRATEGY"),
		"TITLE" => Loc::getMessage("SEO_CAMPAIGN_TAB_STRATEGY_TITLE"),
	),
);

if($ID > 0)
{
	$aTabs[] = array(
		"DIV" => "edit_stats",
		"TAB" => Loc::getMessage("SEO_CAMPAIGN_TAB_STATS"),
		"TITLE" => Loc::getMessage("SEO_CAMPAIGN_TAB_STATS_TITLE"),
		"ONSELECT" => "showStats()",
	);
}

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($request->isPost() && ($request["save"]<>'' || $request["apply"]<>'') && check_bitrix_sessid() && !$bReadOnly)
{
	$campaignSettings = $request["SETTINGS"];

	if(
		is_array($campaignSettings['Strategy'])
		&& is_array($request["STRATEGY_SETTINGS"][$campaignSettings['Strategy']['StrategyName']])
	)
	{
		$campaignSettings['Strategy'] = array_merge(
			$campaignSettings['Strategy'],
			$request["STRATEGY_SETTINGS"][$campaignSettings['Strategy']['StrategyName']]
		);
	}

	$campaignSettings['EmailNotification']['SendWarn'] = $campaignSettings['EmailNotification']['SendWarn'] == 'Y';

	$campaignSettings['MinusKeywords'] = preg_split("/[\\n,;]+\\s*/", $campaignSettings['MinusKeywords']);

	$campaignSettings['MinusKeywords'] = array_map('trim', $campaignSettings['MinusKeywords']);

	$campaignFields = array(
		"SETTINGS" => $campaignSettings
	);

	if($ID > 0)
	{
		$result = Adv\YandexCampaignTable::update($ID, $campaignFields);
	}
	else
	{
		$result = Adv\YandexCampaignTable::add($campaignFields);
	}

	if($result->isSuccess())
	{
		$ID = $result->getId();
		if($request["apply"]<>'')
		{
			LocalRedirect('/bitrix/admin/seo_search_yandex_direct_edit.php?lang='.LANGUAGE_ID.'&ID='.$ID.'&'.$tabControl->ActiveTabParam());
		}
		else
		{
			if($back_url == '')
			{
				LocalRedirect("/bitrix/admin/seo_search_yandex_direct.php?lang=".LANGUAGE_ID);
			}
			else
			{
				LocalRedirect($back_url);
			}
		}
	}
	else
	{
		$campaignFields["ID"] = $ID;
		$campaignFields["XML_ID"] = $campaign["XML_ID"];
		$campaign = $campaignFields;

		$message = new CAdminMessage(array(
			"TYPE" => "ERROR",
			"MESSAGE" => Loc::getMessage('SEO_CAMPAIGN_ERROR'),
			"DETAILS" => implode('<br>', $result->getErrorMessages()),
		));
	}
}

$campaign['SETTINGS']['MinusKeywords'] = $campaign['SETTINGS']['MinusKeywords'] ?
	implode(', ', $campaign['SETTINGS']['MinusKeywords']) :
	'';

$APPLICATION->SetTitle(
	$ID > 0
		? Loc::getMessage("SEO_CAMPAIGN_EDIT_TITLE", array(
			"#ID#" => $ID,
			"#XML_ID#" => $campaign["XML_ID"],
		))
		: Loc::getMessage("SEO_CAMPAIGN_NEW_TITLE")
);

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"ICON" => "btn_list",
		"TEXT" => Loc::getMessage("SEO_CAMPAIGN_LIST"),
		"LINK" => "seo_search_yandex_direct.php?lang=".LANGUAGE_ID
	)
);

if($ID > 0)
{
	$aMenu[] = array(
		"ICON" => "btn_new",
		"TEXT" => Loc::getMessage("SEO_CAMPAIGN_ADD"),
		"LINK" => "seo_search_yandex_direct_edit.php?lang=".LANGUAGE_ID
	);

	if(!$bReadOnly)
	{
		$aMenu[] = array(
			"ICON" => "btn_new",
			"TEXT" => Loc::getMessage("SEO_CAMPAIGN_BANNER_ADD_TITLE"),
			"LINK" => "seo_search_yandex_direct_banner_edit.php?lang=".LANGUAGE_ID.'&campaign='.$ID,
		);
	}

	$aMenu[] = array(
		"ICON" => "",
		"TEXT" => Loc::getMessage("SEO_BANNER_LIST"),
		"LINK" => "seo_search_yandex_direct_banner.php?lang=".LANGUAGE_ID.'&campaign='.$ID
	);

	if(!$bReadOnly)
	{
		$aMenu[] = array(
			"TEXT" => Loc::getMessage("MAIN_DELETE"),
			"ICON" => "btn_delete",
			"LINK" => "seo_search_yandex_direct.php?lang=".LANGUAGE_ID."&ID=".$ID."&action=delete&".bitrix_sessid_get()
		);
	}
}

if(!defined('BX_PUBLIC_MODE') || !BX_PUBLIC_MODE)
{
	require_once("tab/seo_search_yandex_direct_auth.php");
}

if(isset($seoproxyAuthError))
	echo $seoproxyAuthError->Show();

$context = new CAdminContextMenu($aMenu);
$context->Show();

if ($message)
	echo $message->Show();

?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>&amp;ID=<?=$ID?>" name="form1" id="form1">
<?

$tabControl->Begin();

// main settings tab
$tabControl->BeginNextTab();
?>
<?
if($ID > 0):

	$active = 'grey';
	$active_title = $campaign['SETTINGS']['Status'];

	if($campaign['SETTINGS']['IsActive'] == Engine\YandexDirect::BOOL_YES)
	{
		$active = 'green';
	}
	elseif(
		$campaign['SETTINGS']['StatusModerate'] == Engine\YandexDirect::BOOL_YES
		&& $campaign['SETTINGS']['StatusShow'] == Engine\YandexDirect::BOOL_YES
	)
	{
		if($campaign['SETTINGS']['StatusActivating'] == Engine\YandexDirect::BOOL_YES)
		{
			$active = 'red';
		}
		else
		{
			$active = 'yellow';
		}
	}

?>
	<tr>
		<td><?=Loc::getMessage("SEO_CAMPAIGN_STATUS")?>:</td>
		<td>
			<div class="lamp-<?=$active?>" style="display:inline-block;"></div>&nbsp;<?=$active_title?>
<?
	if($bAllowUpdate)
	{
?>
			&nbsp;&nbsp;<a href="javascript:void(0)" onclick="updateCampaign(this, '<?=$ID?>')"><?=Loc::getMessage("SEO_CAMPAIGN_LIST_UPDATE");?></a>
<?
	}
?>
		</td>
	</tr>

<?
endif;
?>
	<tr class="adm-detail-required-field">
		<td width="40%"><?=Loc::getMessage("SEO_CAMPAIGN_NAME")?>:</td>
		<td width="60%"><?if(!$bReadOnly):?><input type="text" name="SETTINGS[Name]" value="<?=Converter::getHtmlConverter()->encode($campaign['SETTINGS']['Name']);?>" size="53" maxlength="255"><?else:?><?=Converter::getHtmlConverter()->encode($campaign['SETTINGS']['Name']);?><?endif;?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?=Loc::getMessage("SEO_CAMPAIGN_FIO")?>:</td>
		<td><?if(!$bReadOnly):?><input type="text" name="SETTINGS[FIO]" value="<?=Converter::getHtmlConverter()->encode($campaign['SETTINGS']['FIO']);?>" size="53" maxlength="255"><?else:?><?=Converter::getHtmlConverter()->encode($campaign['SETTINGS']['FIO']);?><?endif;?></td>
	</tr>

	<tr class="adm-detail-required-field">
		<td><?=Loc::getMessage("SEO_CAMPAIGN_START_DATE")?>:</td>
		<td><?if(!$bReadOnly):?><?=CalendarDate("SETTINGS[StartDate]", $campaign['SETTINGS']['StartDate'])?><?else:?><?=Converter::getHtmlConverter()->encode($campaign['SETTINGS']['StartDate']);?><?endif;?></td>
	</tr>

	<tr class="adm-detail-required-field">
		<td>
			<?=Loc::getMessage("SEO_CAMPAIGN_EMAIL")?>:
			<div style="font-weight: normal; font-size: 11px; margin-top: 5px; color: gray;"><?=Loc::getMessage('SEO_CAMPAIGN_EMAIL_HINT')?></div>
		</td>
		<td valign="top"><?if(!$bReadOnly):?><input type="text" name="SETTINGS[EmailNotification][Email]" value="<?=Converter::getHtmlConverter()->encode($campaign['SETTINGS']['EmailNotification']['Email']);?>" size="42" maxlength="255"><?else:?><?=Converter::getHtmlConverter()->encode($campaign['SETTINGS']['EmailNotification']['Email']);?><?endif;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="BX.toggle(BX('seo_email_details'));"><?=Loc::getMessage('SEO_CAMPAIGN_EMAIL_SETTINGS')?></a></td>
	</tr>
<?
// email settings subtable
?>
	<tr>
		<td colspan="2" align="center"><div id="seo_email_details" <?=empty($message)?' style="display: none;"' : ''?>><table class="internal" cellspacing="0" cellpadding="0" border="0" style="width:80%;">
		<tr class="heading">
			<td colspan="2"><b><?=Loc::getMessage('SEO_CAMPAIGN_EMAIL_SETTINGS_TITLE')?></b></td>
		</tr>
		<tr>
			<td width="50%" align="right"><?=Loc::getMessage("SEO_CAMPAIGN_EMAIL_MONEY_WARNING_VALUE")?></td>
			<td width="50%"><?if(!$bReadOnly):?><input type="text" name="SETTINGS[EmailNotification][MoneyWarningValue]" value="<?=intval($campaign["SETTINGS"]["EmailNotification"]["MoneyWarningValue"])?>" size="3" maxlength="2">&nbsp;<?else:?><b><?=intval($campaign["SETTINGS"]["EmailNotification"]["MoneyWarningValue"])?></b><?endif;?><?=Loc::getMessage('SEO_CAMPAIGN_EMAIL_MONEY_WARNING_VALUE_HINT')?></td>
		</tr>
<?
if(!$bReadOnly):
?>
		<tr>
			<td align="right"><?=Loc::getMessage("SEO_CAMPAIGN_EMAIL_SENDWARN")?></td>
			<td><input type="hidden" name="SETTINGS[EmailNotification][SendWarn]" value="N"><input type="checkbox" name="SETTINGS[EmailNotification][SendWarn]" value="Y" id="seo_sendwarn" <?=$campaign["SETTINGS"]["EmailNotification"]["SendWarn"]?' checked="checked"':''?> onclick="BX('seo_warn_interval').disabled=!this.checked;">&nbsp;<label for="seo_sendwarn"><?=Loc::getMessage("SEO_CAMPAIGN_EMAIL_MONEY_WARN_PLACE_INTERVAL")?></label>&nbsp;<select name="SETTINGS[EmailNotification][WarnPlaceInterval]" id="seo_warn_interval"<?=$campaign["SETTINGS"]["EmailNotification"]["SendWarn"]?'':' disabled="disabled"'?>>
<?
	$v = intval($campaign["SETTINGS"]["EmailNotification"]["WarnPlaceInterval"]);
	foreach(Adv\YandexCampaignTable::$allowedWarnPlaceIntervalValues as $value):
?>

		<option value="<?=$value?>"<?=$v===$value?' selected="selected"':''?>><?=$value?></option>
<?
	endforeach;
?>

			</select>&nbsp;<?=Loc::getMessage('SEO_CAMPAIGN_EMAIL_MONEY_WARN_PLACE_INTERVAL_HINT')?></td>
		</tr>
<?
elseif($campaign["SETTINGS"]["EmailNotification"]["SendWarn"]):
?>

		<tr>
			<td align="right"><?=Loc::getMessage("SEO_CAMPAIGN_EMAIL_SENDWARN")?></td>
			<td><?=Loc::getMessage("SEO_CAMPAIGN_EMAIL_MONEY_WARN_PLACE_INTERVAL")?>&nbsp;<b><?=intval($campaign["SETTINGS"]["EmailNotification"]["WarnPlaceInterval"])?></b>&nbsp;<?=Loc::getMessage('SEO_CAMPAIGN_EMAIL_MONEY_WARN_PLACE_INTERVAL_HINT')?></td>
		</tr>

<?
endif;
?>
	</table></div></td>
	</tr>

<tr class="heading">
	<td colspan="2"><?=Loc::getMessage('SEO_CAMPAIGN_MINUS_KEYWORDS')?></td>
</tr>
<tr>
	<td colspan="2">
<?
if($bReadOnly):
	echo Converter::getHtmlConverter()->encode($campaign["SETTINGS"]["MinusKeywords"]);
else:
?>
		<textarea id="minus_text" style="width: 99%;" rows="3" name="SETTINGS[MinusKeywords]"><?=Converter::getHtmlConverter()->encode($campaign["SETTINGS"]["MinusKeywords"])?></textarea>
<?
endif;
?>
	</td>
</tr>

<?
// strategy settings tab
$tabControl->BeginNextTab();
?>
<tr class="adm-detail-required-field">
	<td width="40%" valign="top"><?=Loc::getMessage("SEO_CAMPAIGN_STRATEGY")?>: </td>
	<td width="60%">
<?
$strategyKey = array_search($campaign["SETTINGS"]["Strategy"]["StrategyName"], Adv\YandexCampaignTable::$supportedStrategy);

if(!$bReadOnly):
	foreach(Adv\YandexCampaignTable::$supportedStrategy as $key => $strategy):
?>
		<input type="radio" name="SETTINGS[Strategy][StrategyName]" value="<?=$strategy?>" id="seo_str<?=$key?>" <?=$campaign["SETTINGS"]["Strategy"]["StrategyName"] == $strategy ? ' checked="checked"' : ''?> onclick="showStrategyParams('<?=$key?>')"><label for="seo_str<?=$key?>"><?=GetMessage('SEO_CAMPAIGN_STRATEGY_'.$strategy)?></label><br />

<?
	endforeach;
else:
	echo $strategyTitle;

endif;
?>
	</td>
</tr>
<tr id="tr_SEO_CAMPAIGN_STRATEGY_PARAMS" class="heading"><td colspan="2"><?=Loc::getMessage('SEO_CAMPAIGN_STRATEGY_PARAMS')?></td></tr>

<?
if(!$bReadOnly)
{
	foreach(Adv\YandexCampaignTable::$supportedStrategy as $key => $strategy)
	{
?>
<tbody id="strategy_params_<?=$key?>" style="<?=$key == $strategyKey ? 'display:table-row-group' : 'display:none'?>;">
<?
		foreach(Adv\YandexCampaignTable::$strategyConfig[$strategy] as $param => $config)
		{
			$v = $campaign['SETTINGS']['Strategy'][$param];
			$v = $config['type'] == 'float' ? doubleval($v) : intval($v);
?>
	<tr<?=$config['mandatory'] ? ' class="adm-detail-required-field"' : ''?>>
		<td><?=Loc::getMessage('SEO_CAMPAIGN_STRATEGY_PARAM_'.ToUpper($param))?></td>
		<td><input type="text" name="STRATEGY_SETTINGS[<?=$strategy?>][<?=$param?>]"
				value="<?=$v === 0 ? '' : $v?>" size="5" id="param_<?=$key?>_<?=$param?>"> <?=($config['type'] === 'float')?$clientCurrency:'';?></td>
	</tr>
<?
		}
?>
</tbody>
<?
	}
}
elseif($strategyKey)
{
	$strategy = $campaign['SETTINGS']['Strategy']['StrategyName'];
	foreach(Adv\YandexCampaignTable::$strategyConfig[$strategy] as $param => $config)
	{
		$v = $campaign['SETTINGS']['Strategy'][$param];
		$v = $config['type'] == 'float' ? doubleval($v) : intval($v);
?>
		<tr<?=$config['mandatory'] ? ' class="adm-detail-required-field"' : ''?>>
			<td><?=Loc::getMessage('SEO_CAMPAIGN_STRATEGY_PARAM_'.ToUpper($param))?></td>
			<td><b><?=$v === 0 ? '' : $v?></b></td>
		</tr>
<?
	}
}
else
{
	foreach($campaign['SETTINGS']['Strategy'] as $param => $value)
	{
		if($param != 'StrategyName')
		{
?>
		<tr>
			<td><?=Converter::getHtmlConverter()->encode($param);?></td>
			<td><b><?=Converter::getHtmlConverter()->encode($campaign['SETTINGS']['Strategy'][$param])?></b></td>
		</tr>
<?
		}
	}
}
?>

<?
if($ID > 0)
{
	// stats tab
	$tabControl->BeginNextTab();
?>
	<tr class="heading">
		<td colspan="3"><?=Loc::getMessage('SEO_YANDEX_STATS_GENERAL');?></td>
	</tr>
	<tr>
		<td width="50%" colspan="2"><?ShowJSHint(Loc::getMessage('SEO_CAMPAIGN_SUM_HINT'))?> <?=Loc::getMessage('SEO_CAMPAIGN_SUM')?>, <?=$clientCurrency?>:</td>
		<td width="50%"><?=doubleval($campaign['SETTINGS']['Sum']);?></td>
	</tr>
	<tr>
		<td colspan="2"><?ShowJSHint(Loc::getMessage('SEO_CAMPAIGN_REST_HINT'))?> <?=Loc::getMessage('SEO_CAMPAIGN_REST')?>, <?=$clientCurrency?>:</td>
		<td><?=doubleval($campaign['SETTINGS']['Rest']);?></td>
	</tr>
	<tr>
		<td colspan="2"><?ShowJSHint(Loc::getMessage('SEO_CAMPAIGN_SHOWS_HINT'))?> <?=Loc::getMessage('SEO_CAMPAIGN_SHOWS')?>:</td>
		<td><?=doubleval($campaign['SETTINGS']['Shows']);?></td>
	</tr>
	<tr>
		<td colspan="2"><?ShowJSHint(Loc::getMessage('SEO_CAMPAIGN_CLICKS_HINT'))?> <?=Loc::getMessage('SEO_CAMPAIGN_CLICKS')?>:</td>
		<td><?=doubleval($campaign['SETTINGS']['Clicks']);?></td>
	</tr>
<?
	if($bShowStats)
	{
		CJSCore::Init(array('amcharts_serial'));

		$statsData = Adv\YandexStatTable::getCampaignStat(
			$campaign['ID'],
			$statsDateStart,
			$statsDateFinish
		);

		$gaps = Adv\YandexStatTable::getMissedPeriods($statsData, $statsDateStart, $statsDateFinish);

		$graphData = array();

		$currency = $clientCurrency;
		foreach($statsData as $date => $dayData)
		{
			if($dayData['CURRENCY'] != '')
			{
				$currency = $dayData['CURRENCY'];
			}

			$graphData[] = array(
				'date' => $date,
				'CURRENCY' => $dayData['CURRENCY'],
				'SUM' => round($dayData['CAMPAIGN_SUM'], 2),
				'SUM_SEARCH' => round($dayData['CAMPAIGN_SUM_SEARCH'], 2),
				'SUM_CONTEXT' => round($dayData['CAMPAIGN_SUM_CONTEXT'], 2),
				'SHOWS' => $dayData['CAMPAIGN_SHOWS'],
				'SHOWS_SEARCH' => $dayData['CAMPAIGN_SHOWS_SEARCH'],
				'SHOWS_CONTEXT' => $dayData['CAMPAIGN_SHOWS_CONTEXT'],
				'CLICKS' => $dayData['CAMPAIGN_CLICKS'],
				'CLICKS_SEARCH' => $dayData['CAMPAIGN_CLICKS_SEARCH'],
				'CLICKS_CONTEXT' => $dayData['CAMPAIGN_CLICKS_CONTEXT'],
			);
		}

		$bLoadStats = count($gaps) > 0;
		$bShowStats = count($graphData) > 0 || $bLoadStats;
?>
<style>
span.loading-message-text
{
	background: url('/bitrix/panel/main/images/waiter-small-white.gif') no-repeat left center;
	padding-left: 20px;
	margin: 0 0 0 15px;
	line-height: 15px;
	font-size: 11px;
	display: inline-block;
}
</style>
	<tr class="heading">
		<td colspan="3"><?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH');?></td>
	</tr>
	<tr>
		<td width="30%" class="adm-detail-required-field"><?=Loc::getMessage('SEO_YANDEX_STATS_PERIOD')?>:</td>
		<td width="70%" colspan="2">
			<span style="white-space: nowrap; display:inline-block;"><select name="period_sel" onchange="setGraphInterval(this.value)">
				<option value="interval"><?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_INTERVAL')?></option>
				<option value="week_ago"><?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_WEEK')?></option>
				<option value="month_ago"><?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_MONTH')?></option>
			</select>&nbsp;<span id="seo_graph_interval"><?=CalendarDate("date_from", $statsDateStart->toString(), 'form1', "4")?>&nbsp;&hellip;<?=CalendarDate("date_to", $statsDateFinish->toString(), 'form1', "4")?></span></span>&nbsp;&nbsp;<input type="button" value="<?=Loc::getMessage('SEO_YANDEX_STATS_PERIOD_APPLY')?>" onclick="loadGraphData()" id="stats_loading_button" name="template_preview"><span id="stats_wait" class="loading-message-text" style="display: none; margin-top: 5px;"><?=Loc::getMessage('SEO_YANDEX_STATS_WAIT')?></span>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TYPE')?>:</td>
		<td colspan="2"><select onchange="setGraph(this.value)">
				<option value="sum"><?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TYPE_SUM')?></option>
				<option value="shows"><?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TYPE_SHOWS')?></option>
				<option value="clicks"><?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TYPE_CLICKS')?></option>
			</select></td>
	</tr>
<tr>
	<td colspan="3">
		<div style="padding: 0 50px; text-align: center;">
			<div id="statschart" style="width: 100%; height: 600px;"></div>
		</div>

<script>
	var currentGraph = 'sum';

	function setGraphInterval(val)
	{
		BX('seo_graph_interval').style.display = val == 'interval' ? 'inline' : 'none';
	}

	function getGraph(id, valueField, title)
	{
		return {
			"bullet": "square",
			"bulletBorderAlpha": 1,
			"bulletBorderThickness": 1,
			"bulletSize": 10,
			"id": id,
			"lineThickness": 3,
			"title": title,
			"valueField": valueField
		}
	}

	function setGraph(graph)
	{
		currentGraph = graph;

		window.yandexChart.graphs = yandexGraphs[currentGraph];
		window.yandexChart.valueAxes = [yandexAxis[currentGraph]];

		window.yandexChart.validateData();
	}

	function setData(data)
	{
		window.yandexChart.dataProvider = data;
		window.yandexChart.validateData();

		if(data.length > 0)
		{
			yandexAxis['sum'].title = "<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_AXIS_SUM')?>, " + (data[0].CURRENCY || '<?=$clientCurrency?>');

			if (currentGraph == 'sum')
			{
				BX.defer(setGraph)(currentGraph);
			}
		}
	}

	function loadGraphData(session)
	{
		var queryData;

		if (!!session)
		{
			queryData = {
				action: 'campaign_stats',
				campaign: '<?=$campaign['ID']?>',
				loading_session: session,
				sessid: BX.bitrix_sessid()
			};
		}
		else
		{
			BX('stats_loading_button').disabled = true;
			BX('stats_wait').style.display = 'inline-block';
			BX('stats_wait').innerHTML = '<?=Loc::getMessage('SEO_YANDEX_STATS_WAIT')?>';

			queryData = {
				action: 'campaign_stats',
				campaign: '<?=$campaign['ID']?>',
				type: document.forms.form1.period_sel.value,
				date_from: document.forms.form1.date_from.value,
				date_to: document.forms.form1.date_to.value,
				sessid: BX.bitrix_sessid()
			};
		}

		BX.ajax.loadJSON(
			'/bitrix/tools/seo_yandex_direct.php',
			queryData,
			function (res)
			{
				if (!!res.session)
				{
					BX.defer(loadGraphData)(res.session);
					BX('stats_wait').innerHTML = '<?=Loc::getMessage('SEO_YANDEX_STATS_WAIT')?>: ' + Math.floor(100 - (res.left / res.amount) * 100) + '%';
				}
				else if (!!res.data)
				{
					setData(res.data);

					if (res.date_from && res.date_to)
					{
						BX.showWait(BX('yandex_sublist_layout'));
						window['tbl_yandex_campaign_banner_stats'].GetAdminList(
							BX.util.add_url_param(location.href.replace(/#.*/, ''), {
								date_from: res.date_from,
								date_to: res.date_to
							}),
							function ()
							{
								BX.closeWait(BX('yandex_sublist_layout'));
								BX('stats_loading_button').disabled = false;
								BX('stats_wait').style.display = 'none';
							}
						);
					}
					else
					{
						BX('stats_loading_button').disabled = false;
						BX('stats_wait').style.display = 'none';
					}

					if(!!res.error && res.error.code == '<?=Engine\YandexDirect::ERROR_NO_STATS?>')
					{
						res.error = null;
					}
				}

				if (!!res.error && (!!res.error.message || !!res.error.code))
				{
					BX('stats_loading_button').disabled = false;
					BX('stats_wait').style.display = 'none';

					alert(res.error.message || res.error.code);
				}
			}
		);
	}

	var yandexGraphs = {
		sum: [
			getGraph('yandex_sum', 'SUM', '<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TITLE_SUM')?>'),
			getGraph('yandex_sum_search', 'SUM_SEARCH', '<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TITLE_SUM_SEARCH')?>'),
			getGraph('yandex_sum_context', 'SUM_CONTEXT', '<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TITLE_SUM_CONTEXT')?>'),
		],
		shows: [
			getGraph('yandex_show', 'SHOWS', '<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TITLE_SHOWS')?>'),
			getGraph('yandex_shows_search', 'SHOWS_SEARCH', '<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TITLE_SHOWS_SEARCH')?>'),
			getGraph('yandex_show_context', 'SHOWS_CONTEXT', '<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TITLE_SHOWS_CONTEXT')?>'),
		],
		clicks: [
			getGraph('yandex_clicks', 'CLICKS', '<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TITLE_CLICKS')?>'),
			getGraph('yandex_clicks_search', 'CLICKS_SEARCH', '<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TITLE_CLICKS_SEARCH')?>'),
			getGraph('yandex_clicks_context', 'CLICKS_CONTEXT', '<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_TITLE_CLICKS_CONTEXT')?>'),
		]
	};

	var yandexAxis = {
		sum: {
			id: "Sum",
			title: "<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_AXIS_SUM')?>, <?=$currency?>"
		},
		shows: {
			id: "Shows",
			title: "<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_AXIS_SHOWS')?>"
		},
		clicks: {
			id: "Clicks",
			title: "<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_AXIS_CLICKS')?>"
		}
	};

	window.yandexChart = null;

	window.showStats = function(){
		window.yandexChart = AmCharts.makeChart("statschart", {
			"type": "serial",
			"theme": "light",
			"pathToImages": "http://cdn.amcharts.com/lib/3/images/",
			"zoomOutText": "<?=Loc::getMessage('SEO_YANDEX_STATS_GRAPH_SHOW_ALL')?>",
			"categoryField": "date",
			"dataDateFormat": "<?=FORMAT_DATE?>",
			"autoMarginOffset": 40,
			"marginRight": 60,
			"marginTop": 60,
			"fontSize": 13,
			"numberFormatter": {
				"decimalSeparator": ".",
				"thousandsSeparator": " ",
				"precision": 2
			},
			"categoryAxis": {
				"parseDates": true,
				"labelFunction": function(val, date)
				{
					return BX.date.format(
						BX.date.convertBitrixFormat('<?=FORMAT_DATE?>'), date
					);
				}
			},
			"chartCursor": {
				categoryBalloonFunction: function(date)
				{
					return BX.date.format(
						BX.date.convertBitrixFormat('<?=FORMAT_DATE?>'), date
					);
				}
			},
			"trendLines": [],
			"legend": {
				//"switchable": false
			},
			"graphs": yandexGraphs[currentGraph],
			"guides": [],
			"valueAxes": [
				yandexAxis[currentGraph]
			],
			"allLabels": [],
			"balloon": {},
			"titles": [],
			dataProvider: <?=Main\Web\Json::encode($graphData)?>

		});

<?
		if($bLoadStats):
?>
		loadGraphData();
<?
		endif;
?>

		window.showStats = BX.DoNothing;
	};
<?
		if($tabControl->selectedTab == "edit_stats"):
?>
	showStats();
<?
		endif;
?>
</script>
	</td>
</tr>
<tr>
	<td colspan="3" style="padding: 0 100px 20px; text-align: center;">
		<div id="yandex_sublist_layout">
<?
$statsAdminList->DisplayList();
?>
		</div>
	</td>
</tr>
<?
	}
}

$tabControl->EndTab();
if(!$bReadOnly)
{
	$tabControl->Buttons(array(
		"back_url" => $back_url ? $back_url : "seo_search_yandex_direct.php?lang=".LANGUAGE_ID,
	));
}
$tabControl->End();

if(!$bReadOnly):
?>
<?=bitrix_sessid_post();?>
<?
	if($back_url!=''):
?>
	<input type="hidden" name="back_url" value="<?echo Converter::getHtmlConverter()->encode($back_url)?>">
<?
	endif;
?>

	<input type="hidden" name="ID" value="<?=$ID?>">

<script type="text/javascript">
<?
if($bAllowUpdate):
?>
function updateCampaign(btn, campaignId)
{
	if(!!btn._innerHTML)
	{
		return;
	}

	//BX.addClass(btn, 'adm-btn-active');
	btn._innerHTML = btn.innerHTML;
	btn.innerHTML = '<?=Loc::getMessage('SEO_YANDEX_DIRECT_LOADING')?>';

	var url = '/bitrix/tools/seo_yandex_direct.php?action=campaign_update&campaign=' + BX.util.urlencode(campaignId);

	BX.ajax.loadJSON(url + '&sessid=' + BX.bitrix_sessid(), function(res){
		if(BX.hasClass(btn, 'adm-btn-active'))
		{
			//BX.removeClass(btn, 'adm-btn-active');
			btn.innerHTML = btn._innerHTML;
			delete btn._innerHTML;
		}

		if(!!res.error && (!!res.error.message || !!res.error.code))
		{
			alert(res.error.message||res.error.code);
		}
		else
		{
			BX.reload();
		}
	});
}
<?
endif;
?>

function showStrategyParams(key)
{
<?
	foreach (Adv\YandexCampaignTable::$supportedStrategy as $key => $strategy):
?>
	BX('strategy_params_<?=$key?>').style.display = key =='<?=$key?>' ? 'table-row-group' : 'none';
<?
	endforeach;
?>
}

BX.ready(function(){
	var i1 = BX('param_WEEKLY_PACKET_OF_CLICKS_MaxPrice'),
		i2 = BX('param_WEEKLY_PACKET_OF_CLICKS_AveragePrice');

	var f = function(){
		i2.disabled = i1.value > 0;
		if(!i2.disabled)
		{
			i1.disabled = i2.value > 0;
		}
	};

	BX.bind(i1, 'change', f);
	BX.bind(i2, 'change', f);
	f();

})
</script>
<?
endif;
?>
</form>
<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
