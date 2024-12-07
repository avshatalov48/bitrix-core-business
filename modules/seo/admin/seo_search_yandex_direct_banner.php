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

Loc::loadMessages(__DIR__.'/../../main/tools.php');
Loc::loadMessages(__DIR__.'/seo_search.php');
Loc::loadMessages(__DIR__.'/seo_adv.php');

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

$bReadOnly = $bNeedAuth;
$bAllowSimpleActions = !$bNeedAuth;

$message = null;

$request = Main\Context::getCurrent()->getRequest();

$campaignId = intval($request["campaign"]);
$archive = isset($request['archive']) && $request['archive'] == 1;

$campaign = false;
if($campaignId > 0)
{
	$dbRes = Adv\YandexCampaignTable::getByPrimary($campaignId);
	$campaign = $dbRes->fetch();
}

if(!$campaign)
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$message = new CAdminMessage(array(
		"TYPE" => "ERROR",
		"DETAILS" => Loc::getMessage("SEO_ERROR_NO_CAMPAIGN"),
	));
	echo $message->Show();

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}

if($campaign['SETTINGS']['StatusArchive'] == Engine\YandexDirect::BOOL_YES)
{
	$archive = true;
}

if($campaign['OWNER_ID'] != $currentUser['id'])
{
	$bReadOnly = true;
	$bAllowSimpleActions = false;

	$message = new CAdminMessage(array(
		"TYPE" => "ERROR",
		"MESSAGE" => Loc::getMessage('SEO_CAMPAIGN_WRONG_OWNER', array("#USERINFO#" => "(".$campaign["OWNER_ID"].") ".$campaign["OWNER_NAME"]))
	));
}
elseif(!in_array($campaign["SETTINGS"]['Strategy']['StrategyName'], Adv\YandexCampaignTable::$supportedStrategy))
{
	$bReadOnly = true;
	$bAllowSimpleActions = true;

	$message = new CAdminMessage(array(
		"TYPE" => "ERROR",
		"MESSAGE" => Loc::getMessage("SEO_CAMPAIGN_STRATEGY_NOT_SUPPORTED", array(
			"#STRATEGY#" => Loc::getMessage('SEO_CAMPAIGN_STRATEGY_'.$campaign["SETTINGS"]['Strategy']['StrategyName']),
		))
	));
}

$tableID = "tbl_yandex_direct_banner";

$oSort = new \CAdminSorting($tableID, "ID", "desc");
$adminList = new \CAdminList($tableID, $oSort);

if($bAllowSimpleActions && ($bannerIDs = $adminList->GroupAction()))
{
	$filter = array(
		'=CAMPAIGN_ID' => $campaignId,
		'=ENGINE_ID' => $engine->getId(),
		'=ACTIVE' => $archive ? Adv\YandexBannerTable::INACTIVE : Adv\YandexBannerTable::ACTIVE,
	);

	if($_REQUEST['action_target']!=='selected')
	{
		$filter['=ID'] = $bannerIDs;
	}

	$dbRes = Adv\YandexBannerTable::getList(array(
		'filter' => $filter,
		'select' => array('ID', 'XML_ID'),
	));

	$bannersList = array();
	while($banner = $dbRes->fetch())
	{
		$bannersList[$banner['XML_ID']] = $banner['ID'];
	}

	if(count($bannersList) > 0)
	{
		if($_REQUEST['action'] == 'delete')
		{
			try
			{
				$engine->deleteBanners($campaign['XML_ID'], array_keys($bannersList));
				Adv\YandexBannerTable::setSkipRemoteUpdate(true);
			}
			catch(Engine\YandexDirectException $e)
			{
				Adv\YandexBannerTable::setSkipRemoteUpdate(false);
			}

			foreach($bannersList as $bannerId)
			{
				try
				{
					$result = Adv\YandexBannerTable::delete($bannerId);
					if(!$result->isSuccess())
					{
						$errorsList = $result->getErrorMessages();
						foreach($errorsList as $errorMessage)
						{
							$adminList->AddGroupError($errorMessage, $bannerId);
						}
					}
				}
				catch(Engine\YandexDirectException $e)
				{
					if($e->getCode() == Engine\YandexDirect::ERROR_NOT_FOUND)
					{
						Adv\YandexBannerTable::setSkipRemoteUpdate(true);
						$result = Adv\YandexBannerTable::delete($bannerId);
						if(!$result->isSuccess())
						{
							$errorsList = $result->getErrorMessages();
							foreach($errorsList as $errorMessage)
							{
								$adminList->AddGroupError($errorMessage, $bannerId);
							}
						}
						Adv\YandexBannerTable::setSkipRemoteUpdate(false);
					}
					else
					{
						$adminList->AddGroupError($e->getCode().': '.$e->getMessage());
					}
				}
			}
			Adv\YandexBannerTable::setSkipRemoteUpdate(false);
		}
		else
		{
			try
			{
				switch($_REQUEST['action'])
				{
					case 'stop':
						$engine->stopBanners($campaign['XML_ID'], array_keys($bannersList));
						break;

					case 'resume':
						$engine->resumeBanners($campaign['XML_ID'], array_keys($bannersList));
						break;

					case 'moderate':
						$engine->moderateBanners($campaign['XML_ID'], array_keys($bannersList));
						$engine->updateCampaignManual($campaign['ID']);
						break;

					case 'archive':
						$engine->archiveBanners($campaign['XML_ID'], array_keys($bannersList));
						break;

					case 'unarchive':
						$engine->unArchiveBanners($campaign['XML_ID'], array_keys($bannersList));
						break;
				}

				$bannersListRemote = $engine->getBanners(array_keys($bannersList));
				$bannersListSorted = array();
				foreach($bannersListRemote as $key => $bannerInfo)
				{
					$bannersListSorted[$bannerInfo['BannerID']] = $bannerInfo;
				}

				Adv\YandexBannerTable::setSkipRemoteUpdate(true);
				foreach($bannersList as $bannerXmlId => $bannerId)
				{
					if(isset($bannersListSorted[$bannerXmlId]))
					{
						$result = Adv\YandexBannerTable::update(
							$bannerId, array(
								"SETTINGS" => $bannersListSorted[$bannerXmlId]
							)
						);

						if(!$result->isSuccess())
						{
							$errorsList = $result->getErrorMessages();
							foreach($errorsList as $errorMessage)
							{
								$adminList->AddGroupError($errorMessage, $bannerId);
							}
						}
					}
					else
					{
						$adminList->AddGroupError(
							Loc::getMessage(
								'SEO_ERROR_BANNER_UPDATE',
								array(
									'#ID#' => $bannerId,
									'#XML_ID#' => $bannerXmlId,
								)
							)
						);
					}
				}
				Adv\YandexBannerTable::setSkipRemoteUpdate(false);

			}
			catch(Engine\YandexDirectException $e)
			{
				$adminList->AddGroupError($e->getCode().': '.$e->getMessage());
			}
		}
	}
}

$map = Adv\YandexBannerTable::getMap();

unset($map['GROUP']);
unset($map['CAMPAIGN']);

$bannerList = Adv\YandexBannerTable::getList(array(
	'order' => array($by => $order),
	'filter' => array(
		"=ENGINE_ID" => $engine->getId(),
		"=CAMPAIGN_ID" => $campaign['ID'],
		'=ACTIVE' => $archive ? Adv\YandexBannerTable::INACTIVE : Adv\YandexBannerTable::ACTIVE,
	),
	"select" => ['*'],
/*
	'runtime' => array(
		new Entity\ExpressionField(
			'BANNER_CNT',
			'COUNT(%s)',
			"\\Bitrix\\Seo\\Adv\\YandexBannerTable:CAMPAIGN.ID"
		),
	)
*/
));

$data = new \CAdminResult($bannerList, $tableID);
$data->NavStart();

$arHeaders = array(
	array("id"=>"ID", "content"=>Loc::getMessage("SEO_BANNER_ID"), "sort"=>"ID", "default"=>true),
	//array("id"=>"ONLINE", "content"=>"", "default"=>true),
	array("id"=>"ISACTIVE", "content"=>Loc::getMessage('SEO_ISACTIVE'), "default"=>true),
	array("id"=>"NAME", "content"=>Loc::getMessage('SEO_BANNER_NAME'), "sort"=>"NAME", "default"=>true),
	array("id"=>"XML_ID", "content"=>Loc::getMessage('SEO_BANNER_XML_ID'), "sort"=>"XML_ID", "default"=>true),
	array("id"=>"SHOW", "content"=>Loc::getMessage('SEO_STATUS_SHOW'), "default"=>true),
	array("id"=>"MODERATE", "content"=>Loc::getMessage('SEO_STATUS_MODERATE'), "default"=>true),
	array("id"=>"PHRASES_MODERATE", "content"=>Loc::getMessage('SEO_STATUS_PHRASES_MODERATE'), "default"=>true),
	array("id"=>"LAST_UPDATE", "content"=>Loc::getMessage('SEO_BANNER_LAST_UPDATE'), "sort"=>"LAST_UPDATE", "default"=>true),
);

if($request["mode"]!='excel')
{
	$arHeaders[] = array("id"=>"UPDATE", "content"=>"", "default"=>true);
}

$adminList->AddHeaders($arHeaders);
$adminList->NavText($data->GetNavPrint(Loc::getMessage("PAGES")));
while($banner = $data->NavNext())
{
	$editUrl = "seo_search_yandex_direct_banner_edit.php?lang=".LANGUAGE_ID."&campaign=".$campaign['ID']."&ID=".$banner["ID"];

	$row = &$adminList->AddRow($banner["ID"], $banner, $editUrl, Loc::getMessage("SEO_BANNER_EDIT_TITLE", array(
		"#ID#" => $banner["ID"],
		"#XML_ID#" => $banner["XML_ID"],
	)));

	$row->AddViewField("ID", $banner['ID']);

	$row->AddField("NAME", '<a href="'.Converter::getHtmlConverter()->encode($editUrl).'" title="'.Loc::getMessage("SEO_BANNER_EDIT_TITLE", array(
			"#ID#" => $banner["ID"],
			"#XML_ID#" => $banner["XML_ID"],
		)).'">'.Converter::getHtmlConverter()->encode($banner['NAME']).'</a>');

	$row->AddViewField('LAST_UPDATE', $banner['LAST_UPDATE'] ? $banner['LAST_UPDATE'] : Loc::getMessage('SEO_UPDATE_NEVER'));

	$active = '';
	$active_title = '';

	if($banner['SETTINGS']['StatusBannerModerate'] == Engine\YandexDirect::STATUS_NEW)
	{
		$active = 'grey';
		$active_title = Loc::getMessage('SEO_STATUS_MODERATE_NEW');
	}
	else
	{
		if($banner['SETTINGS']['IsActive'] == Engine\YandexDirect::BOOL_YES)
		{
			if($banner['SETTINGS']['StatusActivating'] == Engine\YandexDirect::STATUS_PENDING)
			{
				$active = 'yellow';
				$active_title = Loc::getMessage('SEO_STATUS_PENDING');
			}
			else
			{
				$active = 'green';
				$active_title = Loc::getMessage('SEO_STATUS_SHOW_YES');
			}
		}
		else
		{
			if($banner['SETTINGS']['StatusActivating'] == Engine\YandexDirect::STATUS_PENDING)
			{
				if($banner['SETTINGS']['StatusBannerModerate'] == Engine\YandexDirect::STATUS_PENDING)
				{
					$active = 'grey';
					$active_title = Loc::getMessage('SEO_STATUS_MODERATE_PENDING');
				}
				elseif($banner['SETTINGS']['StatusShow'] == Engine\YandexDirect::BOOL_YES)
				{
					$active = 'grey';
					$active_title = Loc::getMessage('SEO_STATUS_SHOW_PENDING');
				}
				else
				{
					$active = 'grey';
					$active_title = Loc::getMessage('SEO_STATUS_SHOW_OFF');
				}
			}
			else
			{
				$active = 'red';
				$active_title = Loc::getMessage('SEO_ISACTIVE_NO');
			}
		}
	}

	$row->AddField("ISACTIVE", '<div style="white-space:nowrap;"><div class="lamp-'.$active.'" style="display:inline-block;"></div>&nbsp;'.$active_title.'</div>'/*.'<pre>'.print_r(array(
		'IsActive' => $banner['SETTINGS']['IsActive'],
		'StatusShow' => $banner['SETTINGS']['StatusShow'],
		'StatusBannerModerate' => $banner['SETTINGS']['StatusBannerModerate'],
		'StatusActivating' => $banner['SETTINGS']['StatusActivating'],
		'StatusPhrasesModerate' => $banner['SETTINGS']['StatusPhrasesModerate'],
	), 1).'</pre>'*/);

	$row->AddField("UPDATE", '<input type="button" '.($bAllowSimpleActions ? '' : 'disabled="disabled"').' class="adm-btn-save" value="'.Converter::getHtmlConverter()->encode(Loc::getMessage('SEO_CAMPAIGN_UPDATE')).'" onclick="updateBanner(this, '.$banner['ID'].')" name="save" id="banner_update_button_'.$banner['ID'].'" />');

	$row->AddViewField('XML_ID', '<a href="https://direct.yandex.ru/registered/main.pl?cmd=showCampMultiEdit&bids='.$banner['XML_ID'].'&cid='.$campaign['XML_ID'].'" target="_blank" title="'.Converter::getHtmlConverter()->encode(Loc::getMessage('SEO_CAMPAIGN_EDIT_EXTERNAL')).'">'.Loc::getMessage('SEO_YANDEX_DIRECT_LINK_TPL', array('#XML_ID#' => $banner['XML_ID'])).'</a>');

	$row->AddViewField('MODERATE', Loc::getMessage('SEO_YANDEX_STATUS_'.$banner['SETTINGS']['StatusBannerModerate']));
	$row->AddViewField('PHRASES_MODERATE', Loc::getMessage('SEO_YANDEX_STATUS_'.$banner['SETTINGS']['StatusPhrasesModerate']));
	$row->AddViewField('SHOW', Loc::getMessage('SEO_YANDEX_STATUS_'.$banner['SETTINGS']['StatusShow']));

	if($bAllowSimpleActions)
	{
		$rowActions = array(
			array(
				"ICON" => "edit",
				"TEXT" => Loc::getMessage("SEO_BANNER_EDIT"),
				"ACTION" => $adminList->ActionRedirect($editUrl),
				"DEFAULT" => true,
			),
			array(
				"ICON" => "move",
				"TEXT" => Loc::getMessage("SEO_BANNER_UPDATE"),
				"ACTION" => 'updateBanner(BX(\'banner_update_button_'.$banner['ID'].'\'), '.$banner['ID'].');',
			),
		);

		if(!$archive)
		{
			if($banner['SETTINGS']['StatusBannerModerate'] == Engine\YandexDirect::STATUS_NEW)
			{
				$rowActions[] = array(
					"ICON" => "moderate",
					"TEXT" => Loc::getMessage("SEO_BANNER_MODERATE"),
					"ACTION" => "BX.adminPanel.showWait(BX('banner_update_button_".$banner['ID']."'));".$adminList->ActionDoGroup($banner['ID'], 'moderate', 'campaign='.$campaignId),
				);
			}
			else
			{
				if($banner['SETTINGS']['StatusShow'] == Engine\YandexDirect::BOOL_YES)
				{
					$rowActions[] = array(
						"ICON" => "stop",
						"TEXT" => Loc::getMessage("SEO_BANNER_STOP"),
						"ACTION" => "BX.adminPanel.showWait(BX('banner_update_button_".$banner['ID']."'));".$adminList->ActionDoGroup($banner['ID'], 'stop', 'campaign='.$campaignId),
					);
				}
				else
				{
					$rowActions[] = array(
						"ICON" => "start",
						"TEXT" => Loc::getMessage("SEO_BANNER_RESUME"),
						"ACTION" => "BX.adminPanel.showWait(BX('banner_update_button_".$banner['ID']."'));".$adminList->ActionDoGroup($banner['ID'], 'resume', 'campaign='.$campaignId),
					);
				}
			}

			if(
				$banner['SETTINGS']['StatusBannerModerate'] == Engine\YandexDirect::STATUS_NEW
				|| $banner['SETTINGS']['StatusShow'] == Engine\YandexDirect::BOOL_NO
			)
			{
				$rowActions[] = array(
					"ICON" => "delete",
					"TEXT" => Loc::getMessage("SEO_CAMPAIGN_ARCHIVE"),
					"ACTION" => "if(confirm('".\CUtil::JSEscape(Loc::getMessage('SEO_BANNER_ARCHIVE_CONFIRM'))."')) {BX.adminPanel.showWait(BX('banner_update_button_".$banner['ID']."'));".$adminList->ActionDoGroup($banner['ID'], "archive", 'campaign='.$campaignId)."}"
				);
			}
		}
		else
		{
			$rowActions[] = array(
				"TEXT" => Loc::getMessage("SEO_CAMPAIGN_UNARCHIVE"),
				"ACTION" => "BX.adminPanel.showWait(BX('banner_update_button_".$banner['ID']."'));".$adminList->ActionDoGroup($banner['ID'], "unarchive", 'campaign='.$campaignId.'&archive=1')
			);
		}

		if($banner['SETTINGS']['StatusBannerModerate'] == Engine\YandexDirect::STATUS_NEW)
		{
			$rowActions[] = array(
				"ICON" => "delete",
				"TEXT" => Loc::getMessage("SEO_BANNER_DELETE"),
				"ACTION" => "if(confirm('".\CUtil::JSEscape(Loc::getMessage('SEO_BANNER_DELETE_CONFIRM'))."')) ".$adminList->ActionDoGroup($banner['ID'], "delete", 'campaign='.$campaignId.($archive ? "&archive=1" : "")),
			);
		}
	}
	else
	{
		$rowActions = array(
			array(
				"ICON" => "view",
				"TEXT" => Loc::getMessage("SEO_BANNER_VIEW"),
				"ACTION" => $adminList->ActionRedirect($editUrl),
				"DEFAULT" => true,
			),
		);

	}

	$row->AddActions($rowActions);
}


if($bAllowSimpleActions)
{
	$groupActions = array(
		"moderate" => Loc::getMessage('SEO_BANNER_MODERATE'),
		"stop" => Loc::getMessage('SEO_BANNER_STOP'),
		"resume" => Loc::getMessage('SEO_BANNER_RESUME'),
		"delete" => Loc::getMessage('MAIN_DELETE'),
	);

	if($archive)
	{
		$groupActions["unarchive"] = Loc::getMessage('SEO_CAMPAIGN_UNARCHIVE');
	}
	else
	{
		$groupActions["archive"] = Loc::getMessage('SEO_CAMPAIGN_ARCHIVE');
	}

	$adminList->AddGroupActionTable($groupActions);
}

$aContext = array();

if(!$bReadOnly && !$archive)
{
	$aContext[] = array(
		"ICON" => "btn_new",
		"TEXT" => Loc::getMessage("SEO_CAMPAIGN_BANNER_ADD_TITLE"),
		"LINK" => "seo_search_yandex_direct_banner_edit.php?lang=".LANGUAGE_ID.'&campaign='.$campaign['ID'],
		"TITLE" => Loc::getMessage("SEO_CAMPAIGN_BANNER_ADD_TITLE")
	);
}

$aContext[] = array(
	"TEXT" => Loc::getMessage('SEO_CAMPAIGN_LIST'),
	"LINK" => "seo_search_yandex_direct.php?lang=".LANGUAGE_ID,
	"TITLE" => Loc::getMessage('SEO_CAMPAIGN_LIST_TITLE'),
);

$aContext[] = array(
	"ICON" => $bReadOnly ? "btn_view" : "btn_edit",
	"TEXT" => Loc::getMessage($bReadOnly ? 'SEO_CAMPAIGN_VIEW_BTN' : 'SEO_CAMPAIGN_EDIT_BTN'),
	"LINK" => "seo_search_yandex_direct_edit.php?lang=".LANGUAGE_ID."&ID=".$campaign['ID'],
	"TITLE" => Loc::getMessage($bReadOnly ? 'SEO_CAMPAIGN_VIEW_TITLE' : 'SEO_CAMPAIGN_EDIT_TITLE', array("#ID#" => $campaign['ID'], '#XML_ID#' => $campaign['XML_ID'])),
);


if($archive)
{
	if($campaign['SETTINGS']['StatusArchive'] != Engine\YandexDirect::BOOL_YES)
	{
		$aContext[] = array(
				"TEXT" => Loc::getMessage('SEO_BANNER_LIST_ACTIVE'),
				"LINK" => "seo_search_yandex_direct_banner.php?lang=".LANGUAGE_ID.'&campaign='.$campaign['ID'],
				"TITLE" => Loc::getMessage('SEO_LIST_BANNER_ACTIVE_TITLE'),
		);
	}
}
else
{
	if($bAllowSimpleActions)
	{
		$aContext[] = array(
			"ICON" => "btn_update",
			"TEXT" => Loc::getMessage("SEO_BANNER_LIST_UPDATE_LIST"),
			"ONCLICK" => "updateBanner(this)",
			"TITLE" => Loc::getMessage("SEO_BANNER_LIST_UPDATE_TITLE")
		);
	}

	$aContext[] = array(
		"TEXT" => Loc::getMessage('SEO_LIST_INACTIVE'),
		"LINK" => "seo_search_yandex_direct_banner.php?lang=".LANGUAGE_ID.'&campaign='.$campaign['ID'].'&archive=1',
		"TITLE" => Loc::getMessage('SEO_LIST_BANNER_INACTIVE_TITLE'),
	);
}


$adminList->AddAdminContextMenu($aContext);

$adminList->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage($archive ? "SEO_YANDEX_DIRECT_BANNER_TITLE_ARCHIVE" : "SEO_YANDEX_DIRECT_BANNER_TITLE", array(
	"#ID#" => $campaign["ID"],
	"#XML_ID#" => $campaign["XML_ID"],
)));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if($message)
{
	echo $message->Show();
}

if($bAllowSimpleActions):
?>
<script>

function updateBanner(btn, bannerId)
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

	var url = '/bitrix/tools/seo_yandex_direct.php?action=banner_update&campaign=<?=$campaignId?>';

	if(!!bannerId)
	{
		url += '&banner=' + BX.util.urlencode(bannerId);
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
endif;

require_once("tab/seo_search_yandex_direct_auth.php");

$adminList->DisplayList();


require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>