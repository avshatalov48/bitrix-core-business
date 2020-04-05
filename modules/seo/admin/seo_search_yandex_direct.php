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
	$clientCurrency = ', '.Loc::getMessage('SEO_YANDEX_CURRENCY__'.$clientCurrency['Currency']);
}
catch(Engine\YandexDirectException $e)
{
	$seoproxyAuthError = new CAdminMessage(array(
		"TYPE" => "ERROR",
		"MESSAGE" => Loc::getMessage('SEO_YANDEX_SEOPROXY_AUTH_ERROR'),
		"DETAILS" => $e->getMessage(),
	));
}

$request = Main\Context::getCurrent()->getRequest();

$archive = isset($request['archive']) && $request['archive'] == 1;

$tableID = "tbl_yandex_direct_campaign";

$oSort = new \CAdminSorting($tableID, "ID", "desc");
$adminList = new \CAdminList($tableID, $oSort);

if(!$bNeedAuth && ($campaignIDs = $adminList->GroupAction()))
{
	// we have no group actions here
	$campaignId = intval($campaignIDs[0]);
	if($campaignId > 0)
	{
		if($_REQUEST['action'] == 'delete')
		{
			try
			{
				$result = Adv\YandexCampaignTable::delete($campaignId);

				if(!$result->isSuccess())
				{
					$errorsList = $result->getErrorMessages();
					foreach($errorsList as $errorMessage)
					{
						$adminList->AddGroupError($errorMessage, $campaignId);
					}
				}
			}
			catch(Engine\YandexDirectException $e)
			{
				// if we got an error from yandex - kill only local mirror
				Adv\YandexCampaignTable::setSkipRemoteUpdate(true);
				Adv\YandexCampaignTable::delete($campaignId);
				Adv\YandexCampaignTable::setSkipRemoteUpdate(false);
			}
		}
		else
		{
			$filter = array(
				'=ID' => $campaignId,
				'=ENGINE_ID' => $engine->getId(),
				'=ACTIVE' => $archive ? Adv\YandexCampaignTable::INACTIVE : Adv\YandexCampaignTable::ACTIVE,
			);

			$dbRes = Adv\YandexCampaignTable::getList(array(
				'filter' => $filter,
				'select' => array('ID', 'XML_ID'),
			));

			$campaign = $dbRes->fetch();
			if($campaign)
			{
				try
				{
					switch($_REQUEST['action'])
					{
						case 'archive':
							$engine->stopCampaign($campaign['XML_ID']);
							$engine->archiveCampaign($campaign['XML_ID']);

							break;

						case 'unarchive':
							$engine->unArchiveCampaign($campaign['XML_ID']);

							break;

						case 'stop':
							$engine->stopCampaign($campaign['XML_ID']);

							break;

						case 'resume':
							$engine->resumeCampaign($campaign['XML_ID']);

							break;
					}

					$campaignInfo = $engine->getCampaign(array($campaign['XML_ID']));
					$campaignInfo = $campaignInfo[0];

					Adv\YandexCampaignTable::setSkipRemoteUpdate(true);
					$result = Adv\YandexCampaignTable::update(
						$campaignId, array(
							"SETTINGS" => $campaignInfo,
						)
					);
					Adv\YandexCampaignTable::setSkipRemoteUpdate(false);
					if(!$result->isSuccess())
					{
						$errorsList = $result->getErrorMessages();
						foreach($errorsList as $errorMessage)
						{
							$adminList->AddGroupError($errorMessage, $campaignId);
						}
					}
				}
				catch(Engine\YandexDirectException $e)
				{
					$adminList->AddGroupError($e->getCode().': '.$e->getMessage());
				}
			}
		}
	}
}

$arHeaders = array(
	array("id"=>"ID", "content"=>Loc::getMessage("SEO_CAMPAIGN_ID"), "sort"=>"ID", "default"=>true),
	//array("id"=>"ONLINE", "content"=>"", "default"=>true),
	array("id"=>"STATUS", "content"=>Loc::getMessage('SEO_CAMPAIGN_STATUS'), "default"=>true),
	array("id"=>"NAME", "content"=>Loc::getMessage('SEO_CAMPAIGN_NAME'), "sort"=>"NAME", "default"=>true),
	array("id"=>"XML_ID", "content"=>Loc::getMessage('SEO_CAMPAIGN_XML_ID'), "sort"=>"XML_ID", "default"=>true),
	array("id"=>"STRATEGY", "content"=>Loc::getMessage('SEO_CAMPAIGN_STRATEGY'),"default"=>true),
	array("id"=>"LAST_UPDATE", "content"=>Loc::getMessage('SEO_CAMPAIGN_LAST_UPDATE'), "sort"=>"LAST_UPDATE", "default"=>true),
	array("id"=>"BANNER_CNT", "content"=>Loc::getMessage('SEO_CAMPAIGN_BANNER_CNT'), /*"sort"=>"BANNER_CNT", */"default"=>true),

	array("id"=>"SHOW", "content"=>Loc::getMessage('SEO_STATUS_SHOW'), "default"=>true),
	array("id"=>"SHOW", "content"=>Loc::getMessage('SEO_STATUS_SHOW'), "default"=>true),
	array("id"=>"SUM", "content"=>Loc::getMessage('SEO_CAMPAIGN_SUM').$clientCurrency, "default"=>true),
	array("id"=>"REST", "content"=>Loc::getMessage('SEO_CAMPAIGN_REST').$clientCurrency, "default"=>true),
	array("id"=>"SHOWS", "content"=>Loc::getMessage('SEO_CAMPAIGN_SHOWS'), "default"=>true),
	array("id"=>"CLICKS", "content"=>Loc::getMessage('SEO_CAMPAIGN_CLICKS'), "default"=>true),
);

if($request["mode"]!='excel')
{
	$arHeaders[] = array("id"=>"UPDATE", "content"=>"", "default"=>true);
}

$adminList->AddHeaders($arHeaders);

$campaignList = Adv\YandexCampaignTable::getList(array(
	'order' => array($by => $order),
	'filter' => array(
		"=ENGINE_ID" => $engine->getId(),
		'=ACTIVE' => $archive ? Adv\YandexCampaignTable::INACTIVE : Adv\YandexCampaignTable::ACTIVE,
	),
	"select" => array("ID", "BANNER_CNT"),
	'runtime' => array(
		new Entity\ExpressionField(
			'BANNER_CNT',
			'COUNT(%s)',
			"\\Bitrix\\Seo\\Adv\\YandexBannerTable:CAMPAIGN.ID"
		),
	)
));

$data = new \CAdminResult($campaignList, $tableID);
$data->NavStart();
$adminList->NavText($data->GetNavPrint(Loc::getMessage("PAGES")));

$campaignAdminList = array();
while($campaign = $data->NavNext())
{
	$bannerCnt = $campaign["BANNER_CNT"];

	$campaignDetail = Adv\YandexCampaignTable::getList(array(
		'filter' => array(
			"=ID" => $campaign["ID"],
		),
	));
	$campaign = $campaignDetail->fetch();

	$editUrl = "seo_search_yandex_direct_edit.php?lang=".LANGUAGE_ID."&ID=".$campaign["ID"];

	$row = &$adminList->AddRow($campaign["ID"], $campaign, $editUrl, Loc::getMessage("SEO_CAMPAIGN_EDIT_TITLE", array(
		"#ID#" => $campaign["ID"],
		"#XML_ID#" => $campaign["XML_ID"],
	)));

	$bOwner = $campaign['OWNER_ID'] == $currentUser['id'];

	$row->AddViewField("ID", $campaign['ID']);

	$row->AddField("NAME", '<a href="'.Converter::getHtmlConverter()->encode($editUrl).'" title="'.Loc::getMessage("SEO_CAMPAIGN_EDIT_TITLE", array(
		"#ID#" => $campaign["ID"],
		"#XML_ID#" => $campaign["XML_ID"],
	)).'">'.Converter::getHtmlConverter()->encode($campaign['NAME']).'</a>');

	$row->AddViewField('LAST_UPDATE', $campaign['LAST_UPDATE'] ? $campaign['LAST_UPDATE'] : Loc::getMessage('SEO_UPDATE_NEVER'));

	$row->AddViewField('SHOW', Loc::getMessage('SEO_YANDEX_STATUS_'.$campaign['SETTINGS']['StatusShow']));

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
	elseif($campaign['SETTINGS']['StatusModerate'] == Engine\YandexDirect::BOOL_NO)
	{
		$active = 'red';
	}

	$row->AddViewField('STATUS', '<div style="white-space:nowrap;"><div class="lamp-'.$active.'" style="display:inline-block;"></div>&nbsp;'.$active_title.'</div>'/*.'<pre>'.print_r(array(
	'IsActive' => $campaign['SETTINGS']['IsActive'],
	'StatusShow' => $campaign['SETTINGS']['StatusShow'],
	'StatusModerate' => $campaign['SETTINGS']['StatusModerate'],
	'StatusActivating' => $campaign['SETTINGS']['StatusActivating'],
), 1).'</pre>'*/);

	$row->AddViewField('SUM', $campaign['SETTINGS']['Sum']);
	$row->AddViewField('REST', $campaign['SETTINGS']['Rest']);
	$row->AddViewField('SHOWS', $campaign['SETTINGS']['Shows']);
	$row->AddViewField('CLICKS', $campaign['SETTINGS']['Clicks']);

	$bStrategySupported = in_array($campaign["SETTINGS"]['Strategy']['StrategyName'], Adv\YandexCampaignTable::$supportedStrategy);
	$strategyTitle = Loc::getMessage('SEO_CAMPAIGN_STRATEGY_'.$campaign["SETTINGS"]['Strategy']['StrategyName']);
	if(!$strategyTitle)
	{
		$strategyTitle = $campaign["SETTINGS"]['Strategy']['StrategyName'];
	}

	if(!$bStrategySupported)
	{
		$strategyTitle = '<span style="color: #A0A0A0" title="'.Converter::getHtmlConverter()->encode(Loc::getMessage('SEO_CAMPAIGN_STRATEGY_NOT_SUPPORTED', array('#STRATEGY#' => $strategyTitle))).'">'.$strategyTitle.'</span>';
	}

	$row->AddViewField('STRATEGY', $strategyTitle);

	/*
		if($bOwner)
		{
			$row->AddField("ONLINE", '<div class="lamp-green"></div>');
		}
		else
		{
			$row->AddField("ONLINE", '<div class="lamp-red" onmouseover="BX.hint(this, \''.Converter::getHtmlConverter()->encode(CUtil::JSEscape(Loc::getMessage('SEO_CAMPAIGN_WRONG_OWNER', array("#USERINFO#" => "(".$campaign["OWNER_ID"].") ".$campaign["OWNER_NAME"])))).'\');"></div>');
		}
	*/

	$row->AddField("UPDATE", '<input type="button" '.($bOwner ? '' : 'disabled="disabled"').' class="adm-btn-save" value="'.Converter::getHtmlConverter()->encode(Loc::getMessage('SEO_CAMPAIGN_UPDATE')).'" onclick="updateCampaign(this, '.$campaign['ID'].')" name="save" id="campaign_update_button_'.$campaign['ID'].'"'.($bNeedAuth ? ' disabled="disabled"' : '').' />');

	$row->AddViewField('XML_ID', '<a href="https://direct.yandex.ru/registered/main.pl?cmd=editCamp&cid='.$campaign['XML_ID'].'" target="_blank" title="'.Converter::getHtmlConverter()->encode(Loc::getMessage('SEO_CAMPAIGN_EDIT_EXTERNAL')).'">'.Loc::getMessage('SEO_YANDEX_DIRECT_LINK_TPL', array('#XML_ID#' => $campaign['XML_ID'])).'</a>');

	if($campaign['SETTINGS']['StatusArchive'] == Engine\YandexDirect::BOOL_YES)
	{
		$row->AddViewField('BANNER_CNT', '<a href="seo_search_yandex_direct_banner.php?lang='.LANGUAGE_ID.'&amp;campaign='.$campaign['ID'].'&amp;archive=1" title="'.Converter::getHtmlConverter()->encode(Loc::getMessage('SEO_CAMPAIGN_BANNER_CNT_TITLE')).'">'.$bannerCnt.'</a>');
	}
	else
	{
		$row->AddViewField('BANNER_CNT', '<a href="seo_search_yandex_direct_banner.php?lang='.LANGUAGE_ID.'&amp;campaign='.$campaign['ID'].'" title="'.Converter::getHtmlConverter()->encode(Loc::getMessage('SEO_CAMPAIGN_BANNER_CNT_TITLE')).'">'.$bannerCnt.'</a>'.($bStrategySupported ? ' [<a href="seo_search_yandex_direct_banner_edit.php?lang='.LANGUAGE_ID.'&amp;campaign='.$campaign['ID'].'" title="'.Converter::getHtmlConverter()->encode(Loc::getMessage('SEO_CAMPAIGN_BANNER_ADD_TITLE')).'">+</a>]' : ''));
	}

	if(!$bNeedAuth)
	{
		$actionsList = array(
			array(
				"ICON" => ($bOwner && $bStrategySupported) ? "edit" : 'view',
				"TEXT" => Loc::getMessage(($bOwner && $bStrategySupported) ? "SEO_CAMPAIGN_EDIT" : "SEO_BANNER_VIEW"),
				"ACTION" => $adminList->ActionRedirect($editUrl),
				"DEFAULT" => true,
			),
		);

		if($bOwner)
		{
			$actionsList[] = array(
				"ICON" => "move",
				"TEXT" => Loc::getMessage("SEO_CAMPAIGN_UPDATE"),
				"ACTION" => 'updateCampaign(BX(\'campaign_update_button_'.$campaign['ID'].'\'), '.$campaign['ID'].');',
			);
		}

		$actionsList[] = array(
			"ICON" => "list",
			"TEXT" => Loc::getMessage("SEO_CAMPAIGN_BANNER_CNT"),
			"ACTION" => $adminList->ActionRedirect('seo_search_yandex_direct_banner.php?lang='.LANGUAGE_ID.'&amp;campaign='.$campaign['ID'].($archive ? '&amp;archive=1' : '')),
		);

		if($bOwner)
		{
			if($campaign['SETTINGS']['StatusArchive'] == Engine\YandexDirect::BOOL_NO)
			{
				if($campaign['SETTINGS']['StatusShow'] == Engine\YandexDirect::BOOL_YES)
				{
					$actionsList[] = array(
						"ICON" => "stop",
						"TEXT" => Loc::getMessage("SEO_BANNER_STOP"),
						"ACTION" => $adminList->ActionDoGroup($campaign['ID'], 'stop'),
					);

				}
				else
				{
					$actionsList[] = array(
						"ICON" => "resume",
						"TEXT" => Loc::getMessage("SEO_BANNER_RESUME"),
						"ACTION" => $adminList->ActionDoGroup($campaign['ID'], 'resume'),
					);
				}
			}

			if($campaign['SETTINGS']['StatusArchive'] == Engine\YandexDirect::BOOL_YES)
			{
				$actionsList[] = array(
					"ICON" => "unarchive",
					"TEXT" => Loc::getMessage("SEO_CAMPAIGN_UNARCHIVE"),
					"ACTION" => "BX.adminPanel.showWait(BX('campaign_update_button_".$campaign['ID']."'));".$adminList->ActionDoGroup($campaign['ID'], "unarchive", "archive=1")
				);
			}
			elseif($campaign['SETTINGS']['IsActive'] !== Engine\YandexDirect::BOOL_YES)
			{
				$actionsList[] = array(
					"ICON" => "delete",
					"TEXT" => Loc::getMessage("SEO_CAMPAIGN_ARCHIVE"),
					"ACTION" => "if(confirm('".\CUtil::JSEscape(Loc::getMessage('SEO_CAMPAIGN_ARCHIVE_CONFIRM'))."')) {BX.adminPanel.showWait(BX('campaign_update_button_".$campaign['ID']."'));".$adminList->ActionDoGroup($campaign['ID'], "archive").'}'
				);
			}
		}

		if($campaign['SETTINGS']['StatusModerate'] == Engine\YandexDirect::STATUS_NEW)
		{
			$actionsList[] = array(
				"ICON" => "delete",
				"TEXT" => Loc::getMessage("SEO_CAMPAIGN_DELETE"),
				"ACTION" => "if(confirm('".\CUtil::JSEscape(Loc::getMessage('SEO_CAMPAIGN_DELETE_CONFIRM'))."')) ".$adminList->ActionDoGroup($campaign['ID'], "delete", $archive ? "archive=1" : "")
			);
		}

		$row->AddActions($actionsList);
	}
}

if($archive)
{
	$aContext = array(
		array(
			"ICON" => "btn_list",
			"TEXT" => GetMessage("SEO_CAMPAIGN_LIST_ACTIVE"),
			"LINK" => "seo_search_yandex_direct.php?lang=".LANGUAGE_ID,
			"TITLE" => GetMessage("SEO_LIST_CAMPAIGN_ACTIVE_TITLE")
		)
	);
}
elseif($bNeedAuth)
{
	$aContext = array(
		array(
			"ICON" => "btn_archive",
			"TEXT" => GetMessage("SEO_LIST_INACTIVE"),
			"LINK" => "seo_search_yandex_direct.php?lang=".LANGUAGE_ID."&archive=1",
			"TITLE" => GetMessage("SEO_LIST_CAMPAIGN_INACTIVE_TITLE")
		),
	);
}
else
{
	$aContext = array(
		array(
			"ICON" => "btn_new",
			"TEXT" => GetMessage("MAIN_ADD"),
			"LINK" => "seo_search_yandex_direct_edit.php?lang=".LANGUAGE_ID,
			"TITLE" => GetMessage("MAIN_ADD")
		),
		array(
			"ICON" => "btn_update",
			"TEXT" => GetMessage("SEO_CAMPAIGN_LIST_UPDATE_LIST"),
			"ONCLICK" => "updateCampaign(this)",
			"TITLE" => GetMessage("SEO_CAMPAIGN_LIST_UPDATE_TITLE")
		),
		array(
			"ICON" => "btn_archive",
			"TEXT" => GetMessage("SEO_LIST_INACTIVE"),
			"LINK" => "seo_search_yandex_direct.php?lang=".LANGUAGE_ID."&archive=1",
			"TITLE" => GetMessage("SEO_LIST_CAMPAIGN_INACTIVE_TITLE")
		),
	);
}

$adminList->AddAdminContextMenu($aContext);

$adminList->CheckListMode();

$APPLICATION->SetTitle($archive ? Loc::getMessage("SEO_YANDEX_DIRECT_TITLE_ARCHIVE") : Loc::getMessage("SEO_YANDEX_DIRECT_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

?>
<script type="text/javascript">
function updateCampaign(btn, campaignId)
{
	if(btn.type == 'button')
	{
		if (!btn.name)
		{
			btn.name = 'template_preview';
		}

		BX.adminPanel.showWait(btn);
	}
	else
	{
		if(BX.hasClass(btn, 'adm-btn-active'))
		{
			return;
		}

		BX.addClass(btn, 'adm-btn-active');
		btn._innerHTML = btn.innerHTML;
		btn.innerHTML = '<?=Loc::getMessage('SEO_YANDEX_DIRECT_LOADING_LIST')?>';
	}

	var url = '/bitrix/tools/seo_yandex_direct.php?action=campaign_update';

	if(!!campaignId)
	{
		url += '&campaign=' + BX.util.urlencode(campaignId);
	}

	BX.ajax.loadJSON(url + '&sessid=' + BX.bitrix_sessid(), function(res){
		if(BX.hasClass(btn, 'adm-btn-active'))
		{
			BX.removeClass(btn, 'adm-btn-active');
			btn.innerHTML = btn._innerHTML;
			delete btn._innerHTML;
		}
		else
		{
			BX.adminPanel.closeWait(btn);
		}

		if(!!res.error && (!!res.error.message || !!res.error.code))
		{
			alert(res.error.message||res.error.code);
		}
		else
		{
			window['<?=$tableID?>'].GetAdminList(window.location.href.replace(/#.*/, ''));
		}
	});
}
</script>
<?
require_once("tab/seo_search_yandex_direct_auth.php");

if(isset($seoproxyAuthError))
	echo $seoproxyAuthError->Show();

$adminList->DisplayList();

$messageText = '<ul>';
foreach(Adv\YandexCampaignTable::$supportedStrategy as $startegy)
{
	$messageText .= '<li>'.Loc::getMessage('SEO_CAMPAIGN_STRATEGY_'.$startegy).'</li>';
}
$messageText .= '</ul>';


$message = new CAdminMessage(array(
	'TYPE' => 'OK',
	'MESSAGE' => Loc::getMessage('SEO_SUPPORTED_STRATEGY_NOTE'),
	"DETAILS" => $messageText,
	'HTML' => 'Y'
));
echo $message->show();



require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>