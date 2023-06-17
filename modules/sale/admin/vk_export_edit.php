<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Sale\TradingPlatform\Helper;
use Bitrix\Sale\TradingPlatform\Vk;
use Bitrix\Main\Application;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Text\HtmlFilter;

Loc::loadMessages(__FILE__);
global $APPLICATION;

//system VARs
$application = Application::getInstance();
$context = $application->getContext();
$request = $context->getRequest();
$server = $context->getServer();

//CHECK errors
if ($APPLICATION->GetGroupRight("sale") < "W")
{
	$APPLICATION->AuthForm(Loc::getMessage("SALE_VK_ACCESS_DENIED"));
}

if (!Loader::includeModule('sale'))
	$arResult["ERROR"] = Loc::getMessage("SALE_VK_MODULE_NOT_INSTALLED");

//get or create export ID
if (isset($request['ID']) && $request['ID'])
{
	$exportId = (int)$request['ID'];
}
else
{
	$exportId = null;
}

//	download LOG file
if(isset($request["download_log"]) && $request["download_log"] == "Y" && $exportId)
{
	header('Content-disposition: attachment; filename=vk_export.log');
	header('Content-type: text/plain');
	echo Vk\Logger::createLogFileContent($exportId);
	die();
}

//init VK and SETTINGS
$vk = Vk\Vk::getInstance();
if ($exportId)
{
	$vkSettings = $vk->getSettings($exportId);
}
else
{
	$vkSettings = array();
}

\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/vk_admin.js", true);
require_once($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . "/modules/main/include/prolog_admin_after.php");

//	check REQUIRED fields
$errorRequiredFields = array();
if (isset($request["VK"]) && check_bitrix_sessid())
{
	if (!isset($request["VK"]["DESCRIPTION"]) || $request["VK"]["DESCRIPTION"] == '')
	{
		$errorRequiredFields[] = Loc::getMessage("SALE_VK_SETTINGS_NO_NAME");
	}

	if (!isset($request["VK"]["VK_SETTINGS"]["APP_ID"]) || $request["VK"]["VK_SETTINGS"]["APP_ID"] == '')
	{
		$errorRequiredFields[] = Loc::getMessage("SALE_VK_SETTINGS_NO_APP_ID");
	}

	if (!isset($request["VK"]["VK_SETTINGS"]["SECRET"]) || $request["VK"]["VK_SETTINGS"]["SECRET"] == '')
	{
		$errorRequiredFields[] = Loc::getMessage("SALE_VK_SETTINGS_NO_SECRET");
	}

//	todo: somtehing wrong...
	if ($exportId && empty($errorRequiredFields) && isset($vkSettings["OAUTH"]["ACCESS_TOKEN"]) && !empty($vkSettings["OAUTH"]["ACCESS_TOKEN"]))
	{
		if (!isset($request["VK"]["VK_SETTINGS"]["GROUP_ID"]) || $request["VK"]["VK_SETTINGS"]["GROUP_ID"] <= 0)
		{
			$errorRequiredFields[] = Loc::getMessage("SALE_VK_SETTINGS_NO_GROUP_ID");
		}

		if(!isset($request["VK"]["EXPORT_SETTINGS"]["CATEGORY_DEFAULT"]) || $request["VK"]["EXPORT_SETTINGS"]["CATEGORY_DEFAULT"] <= 0)
		{
			$errorRequiredFields[] = Loc::getMessage("SALE_VK_SETTINGS_NO_CATEOGRY");

//			drop default category from settings
			unset($vkSettings["EXPORT_SETTINGS"]["CATEGORY_DEFAULT"]);
			$vk->saveSettings(array('SETTINGS' => $vkSettings, 'EXPORT_ID' => $exportId));
			$vkSettings = $vk->getSettings($exportId);    //.. and get new settings array
			$vk->unsetActiveById($exportId);
		}
	}
}


//preset URL vars
$siteDomain = $server->getHttpHost();
$currPageUrl = $APPLICATION->GetCurPage() . "?lang=" . LANGUAGE_ID;
$currPageFullUrl = $siteDomain . $currPageUrl;
$currPageSettingsTabUrl = $currPageFullUrl . '&tabControl_active_tab=vk_settings';
if ($exportId)
{
	$currPageFullUrl .= '&ID=' . $exportId;
	$currPageSettingsTabUrl .= '&ID=' . $exportId;
}

if ($exportId)
	$APPLICATION->SetTitle(Loc::getMessage('SALE_VK_TITLE', array('#E1#' => $vkSettings["DESCRIPTION"])));
else
	$APPLICATION->SetTitle(Loc::getMessage('SALE_VK_TITLE_NEW'));


//ONLY RUSSIAN!!!
if (defined('LANG') && LANG != 'ru')
{
	echo BeginNote();
	echo '<p>' . Loc::getMessage("SALE_VK_ONLY_RUSSIAN") . '</p>';
	echo '<p>' . Loc::getMessage("SALE_VK_ONLY_RUSSIAN_2") . '</p>';
	echo '<img src="/bitrix/images/sale/vk/vk_only_russian.png" alt="">';
	echo EndNote();
	require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php");
	die();
}


///////////////////////////////////////////////////////////////////
//get and save AUTH CODE
if (isset($request["code"]) && !empty($request["code"]) && $exportId)
{
	$vkSettings["OAUTH"]["CODE"] = $request["code"];
	$vkSettings["OAUTH"]["CODE_TIME"] = time();    //set expires time for code

	$tokenUrl = $vk->getTokenUrl($exportId, $currPageSettingsTabUrl, $vkSettings["OAUTH"]["CODE"]);
	$http = new HttpClient();
	$responseStr = $http->get($tokenUrl);

//	check answer from VK
	if ($responseStr == '')
	{
//		logger my throw exception
		try
		{
			$logger = new Vk\Logger($exportId);
			$logger->addError('VK_NOT_AVAILABLE');
			unset($logger);
		}
		catch (Vk\ExecuteException $e){}
	}
	else
	{
		$response = Bitrix\Main\Web\Json::decode($responseStr);
		if (isset($response["error"]))
		{
			try
			{
//				if catch error - must clear access_token and unset activity
				unset($vkSettings["OAUTH"]["ACCESS_TOKEN"], $vkSettings["OAUTH"]["ACCESS_TOKEN_TIME"]);
				$vk->unsetActiveById($exportId);
				$logger = new Vk\Logger($exportId);
				$logger->addError(ToUpper(str_replace(' ','_',$response["error_description"])));
			}
			catch (Vk\ExecuteException $e){}
		}
		elseif (isset($response["access_token"]))
		{
			$vkSettings["OAUTH"]["ACCESS_TOKEN"] = $response["access_token"];
			$vkSettings["OAUTH"]["ACCESS_TOKEN_TIME"] = time();

//			clear error about access token
			$logger = new Vk\Logger($exportId);
			$logger->clearOneError("WRONG_ACCESS_TOKEN");
			unset($logger);
		}

		$bSaved = $vk->saveSettings(array('SETTINGS' => $vkSettings, 'EXPORT_ID' => $exportId));

		$vk->changeActiveById($exportId);
	}

	LocalRedirect("sale_vk_export_edit.php?ID=" . $exportId . "&lang=" . LANGUAGE_ID);
}


///////////////////////////////////////////////////////////////////
//	SAVE or APPLY settings
if (isset($request["VK"]) && is_array($request["VK"]) && ($_POST['save'] || $_POST['apply']) && check_bitrix_sessid())
{
	if (empty($errorRequiredFields))
	{
// 		install platform in first run
		if (!$vk->isInstalled())
			$vk->install();

//		VALIDATE settings
		if (isset($request["VK"]["DESCRIPTION"]))
			$vkSettings["DESCRIPTION"] = $request["VK"]["DESCRIPTION"];

//		adding "-"
		if (mb_strlen($request["VK"]["VK_SETTINGS"]["GROUP_ID"]) > 1)
		{
			$vkSettings["VK_SETTINGS"]["GROUP_ID"] =
				mb_substr($request["VK"]["VK_SETTINGS"]["GROUP_ID"], 0, 1) != "-" ?
					"-" . intval($request["VK"]["VK_SETTINGS"]["GROUP_ID"]) :
					intval($request["VK"]["VK_SETTINGS"]["GROUP_ID"]);
		}
		if ($request["VK"]["VK_SETTINGS"]["APP_ID"])
			$vkSettings["VK_SETTINGS"]["APP_ID"] = intval($request["VK"]["VK_SETTINGS"]["APP_ID"]);
		if ($request["VK"]["VK_SETTINGS"]["SECRET"])
			$vkSettings["VK_SETTINGS"]["SECRET"] = htmlspecialcharsbx($request["VK"]["VK_SETTINGS"]["SECRET"]);

//		validate EXPORT SETTINGS
		if (!isset($request["VK"]["EXPORT_SETTINGS"]["TIMELIMIT"]) || !$request["VK"]["EXPORT_SETTINGS"]["TIMELIMIT"])
			$vkSettings["EXPORT_SETTINGS"]["TIMELIMIT"] = Vk\Vk::DEFAULT_TIMELIMIT;
		else
			$vkSettings["EXPORT_SETTINGS"]["TIMELIMIT"] = intval($request["VK"]["EXPORT_SETTINGS"]["TIMELIMIT"]);

		if ($request["VK"]["EXPORT_SETTINGS"]["COUNT_ITEMS"])
			$vkSettings["EXPORT_SETTINGS"]["COUNT_ITEMS"] =
				$request["VK"]["EXPORT_SETTINGS"]["COUNT_ITEMS"] >= Vk\Vk::MAX_EXECUTION_ITEMS ? Vk\Vk::MAX_EXECUTION_ITEMS : intval($request["VK"]["EXPORT_SETTINGS"]["COUNT_ITEMS"]);
		else
			$vkSettings["EXPORT_SETTINGS"]["COUNT_ITEMS"] = Vk\Vk::DEFAULT_EXECUTION_ITEMS;

		if ($request["VK"]["EXPORT_SETTINGS"]["CATEGORY_DEFAULT"])
			$vkSettings["EXPORT_SETTINGS"]["CATEGORY_DEFAULT"] = intval($request["VK"]["EXPORT_SETTINGS"]["CATEGORY_DEFAULT"]);
		if (isset($request["VK"]["EXPORT_SETTINGS"]["AGRESSIVE"]))
			$vkSettings["EXPORT_SETTINGS"]["AGRESSIVE"] = $request["VK"]["EXPORT_SETTINGS"]["AGRESSIVE"];
		if (isset($request["VK"]["EXPORT_SETTINGS"]["ONLY_AVAILABLE_FLAG"]))
			$vkSettings["EXPORT_SETTINGS"]["ONLY_AVAILABLE_FLAG"] = $request["VK"]["EXPORT_SETTINGS"]["ONLY_AVAILABLE_FLAG"];
		if (isset($request["VK"]["EXPORT_SETTINGS"]["RICH_LOG"]))
			$vkSettings["EXPORT_SETTINGS"]["RICH_LOG"] = $request["VK"]["EXPORT_SETTINGS"]["RICH_LOG"];

//		validate settings for AGENTS
		if (isset($request["VK"]["AGENT"]["INTERVAL"]))
		{
//			convert interval from hours to seconds
			$interval = intval($request["VK"]["AGENT"]["INTERVAL"] * 3600);
			$vkSettings["AGENT"]["INTERVAL"] = $interval;
			$vkSettings["AGENT"]['ID'] = ($agentId = Vk\Agent::update($exportId, 'ALL', $interval)) ? $agentId : 0;
		}

//		SAVE and init exportId if we create new profile
		if ($exportId)
		{
			$bSaved = $vk->saveSettings(array('SETTINGS' => $vkSettings, 'EXPORT_ID' => $exportId));
		}
		else
		{
			$exportId = (int)$vk->saveSettings(array('SETTINGS' => $vkSettings));
		}

//		change of settings may change sections lists. Drop cache to have true data
		$sectionsList = new Vk\SectionsList($exportId);
		$sectionsList->clearCaches();

// 		create Agent for update vk categories to cache
		$vkCategories = new Vk\VkCategories($exportId);
		$vkCategories->createAgent();

//		checking ACTIVITY after saving
		$vk->changeActiveById($exportId);

//		REDIRECT to listpage, if save (if apply - stay here)
		if ($_POST['save'])
		{
			LocalRedirect('sale_vk_export_list.php?lang=' . LANGUAGE_ID);
		}
		else
		{
			LocalRedirect("sale_vk_export_edit.php?ID=" . $exportId . "&lang=" . LANGUAGE_ID);
		}
	}    //end if required fields
}


///////////////////////////////////////////////////////////////////
//	PREPARE params to print

//	intervals if agent not set yet
$defaultAgentsIntervals = Helper::getDefaultFeedIntervals();

//	find running single (not pereodical) agents for noticy
$runningProcess = Vk\Journal::getCurrentProcess($exportId);
$processDisabledFlag = '';
$processDisabledClassFlag = '';

if ($runningProcess)
{
	$processDisabledFlag = ' disabled ';
	$processDisabledClassFlag = ' adm-btn-disabled ';
}


//	prepare ACCESS TOKEN to print
if (isset($vkSettings["OAUTH"]["ACCESS_TOKEN"]) && !empty($vkSettings["OAUTH"]["ACCESS_TOKEN"]))
{
	$authText = Loc::getMessage("SALE_VK_SETTINGS_ACCESS_TOKEN_REGET") . ' (' . date('j.m.Y - H:i', $vkSettings["OAUTH"]["ACCESS_TOKEN_TIME"]) . ')';
	$authButtonText = "SALE_VK_SETTINGS_ACCESS_TOKEN_REGET_BUTTON";
}
else
{
	$authButtonText = "SALE_VK_SETTINGS_ACCESS_TOKEN_GET_BUTTON";
}
$authUrl = $vk->getAuthUrl($exportId, $currPageSettingsTabUrl);



//	Try to get VK categories and VK groups.
//	in this method we use API request and must be checked execution errors
//  If catched errors - it means that export is not available
try
{
	if (isset($exportId) && $exportId)
	{
		$vkCategorySelected = $vkSettings["EXPORT_SETTINGS"]["CATEGORY_DEFAULT"] > 0 ?
			$vkSettings["EXPORT_SETTINGS"]["CATEGORY_DEFAULT"] :
			null;
		$categoriesVk = new Vk\VkCategories($exportId);
		$vkCategorySelector = $categoriesVk->getVkCategorySelector($vkCategorySelected);

		$apiHelper = new Vk\Api\ApiHelper($exportId);
		$vkGroupsSelector = $apiHelper->getUserGroupsSelector($vkSettings["VK_SETTINGS"]["GROUP_ID"], 'VK[VK_SETTINGS][GROUP_ID]');
	}
}
catch (Vk\ExecuteException $e)
{
	$vkCategorySelector = '';
	$vkGroupsSelector = '';
	$vk->unsetActiveById($exportId);				// if error - set vk-export is not active
	$vkSettings = $vk->getSettings($exportId);		// and get new settings
}
catch (ArgumentNullException $e)
{
	if ($e->getParameter() == 'accessToken')
	{
//		drop selectors
		$vkCategorySelector = '';
		$vkGroupsSelector = '';

		$vk->unsetActiveById($exportId);              // if error - set vk-export is not active
		$vkSettings = $vk->getSettings($exportId);    // and get new settings

		$errorRequiredFields[] = Loc::getMessage('SALE_VK_SETTINGS_ACCESS_TOKEN_NEED_GET');
	}
}



// SHOW error REQUIRED fields
if(!empty($errorRequiredFields))
{
	$errorRequiredFields = implode("\n", $errorRequiredFields);
	echo CAdminMessage::ShowMessage($errorRequiredFields);
}

//	TIMELIMIT for feed
$feedTimelimit = $vkSettings["EXPORT_SETTINGS"]["TIMELIMIT"] ? $vkSettings["EXPORT_SETTINGS"]["TIMELIMIT"] : Vk\Vk::DEFAULT_TIMELIMIT;

//	prepare TABS
$arrTabs = array(
	array(
		"DIV" => "vk_settings",
		"TAB" => Loc::getMessage("SALE_VK_TAB_SETTINGS"),
		"TITLE" => Loc::getMessage("SALE_VK_TAB_SETTINGS_DESC"),
	),
);
//	exchange and map active tab only if active
if ($vk->isActive() && $vk->isActiveById($exportId))
{
	array_unshift($arrTabs, array(
		"DIV" => "vk_export",
		"TAB" => Loc::getMessage("SALE_VK_TAB_EXPORT"),
		"TITLE" => Loc::getMessage("SALE_VK_TAB_EXPORT_DESC"),
	));
	$arrTabs[] = array(
		"DIV" => "vk_export_map",
		"TAB" => Loc::getMessage("SALE_VK_TAB_MAP"),
		"TITLE" => Loc::getMessage("SALE_VK_TAB_MAP_DESC"),
	);

//	async map loading in tab
	echo "<script>BX.Sale.VkAdmin.loadExportMap(".$exportId.");</script>";
}

$tabControl = new CAdminTabControl("tabControl", $arrTabs);

///////////////////////////////////////////////////////////////////
// PRINTING FORM //////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////
?>
<div id="vk_export_notify__progress">
	<?
	if ($runningProcess)
		echo Vk\Journal::getProgressMessage($exportId, $runningProcess['TYPE']);
	?>
</div>

<div id="vk_export_notify__error_critical">
	<?php
//	check error log - show if not empty
	$logger = new Vk\Logger($exportId);
	$errorsCritical = $logger->getErrorsList(true);
	if ($errorsCritical <> '')
	{
		$errorsCriticalString = Vk\Journal::getCriticalErrorsMessage($exportId, $errorsCritical);
		echo $errorsCriticalString <> '' ? $errorsCriticalString : '';
	}
	?>
</div>

<? $tabControl->Begin(); ?>
<!--	----------------------------------------------------->
<!--	EXPORT tab-->
<? if ($vk->isActive() && $vk->isActiveById($exportId)): ?>
<?php $tabControl->BeginNextTab(); ?>

<!--	NOTIFY about running agent-->
<? if ($runningProcess): ?>
	<script>
		BX.ready(function () {
			BX.Sale.VkAdmin.exportProcessProlog();
			BX.Sale.VkAdmin.startFeed("<?=$runningProcess['TYPE']?>", "<?=$exportId?>");
		});
	</script>
<? endif; ?>

<?
//	formatted BUTTONS
//	if exists running process - not active buttons
$additionalMenuAdd = array();
$onclickAdd = 'return false;';
$additionalMenuDel = array();
$onclickDel = 'return false;';
foreach (array('ALBUMS', 'PRODUCTS') as $type1)
{
//	ADD
	$additionalMenuAdd[] = array(
		"TEXT" => Loc::getMessage("SALE_VK_EXPORT_BUTTON_" . $type1),
		"TITLE" => Loc::getMessage("SALE_VK_EXPORT_BUTTON_" . $type1),
		"SHOW_TITLE" => 'true',
		"ONCLICK" => "BX.Sale.VkAdmin.startFeed('" . $type1 . "', '" . $exportId . "', true);",
	);

//	DEL
	foreach (array('_DELETE', '_DELETE_ALL') as $type2)
	{
		$msg = Loc::getMessage("SALE_VK_EXPORT_BUTTON_" . $type1 . $type2 . "_ALERT") . '\n' . Loc::getMessage("SALE_VK_SETTINGS_BUTTON_CONFIRM");
		$additionalMenuDel[] = array(
			"TEXT" => Loc::getMessage("SALE_VK_EXPORT_BUTTON_" . $type1 . $type2),
			"TITLE" => Loc::getMessage("SALE_VK_EXPORT_BUTTON_" . $type1 . $type2),
			"SHOW_TITLE" => 'true',
			"ONCLICK" => "if(confirm('" . $msg . "'))
								{BX.Sale.VkAdmin.startFeed('" . $type1 . $type2 . "', '" . $exportId . "', true);}
							",
		);
	}
}

	$contextButtonAdd = array(
		"TEXT" => Loc::getMessage("SALE_VK_EXPORT_BUTTON_ADDITIONAL_ADD"),
		"MENU" => $additionalMenuAdd
	);
	$contextButtonDel = array(
		"TEXT" => Loc::getMessage("SALE_VK_EXPORT_BUTTON_ADDITIONAL_DEL"),
		"MENU" => $additionalMenuDel
	);
	$contextButtons = new CAdminContextMenu(array($contextButtonAdd, $contextButtonDel));

?>
<tr>
	<td>
		<input id="vk_export_button__startFeed_all" class="adm-btn-save" type="button" <?= $processDisabledFlag ?>
			   style="margin-right:10px"
			   value="<?= Loc::getMessage("SALE_VK_EXPORT_BUTTON_ALL") ?>"
			   onclick="BX.Sale.VkAdmin.startFeed('ALL','<?= $exportId ?>', true);">

		<?=$contextButtons->Button($contextButtonAdd, CHotKeys::getInstance());?>
		<?=$contextButtons->Button($contextButtonDel, CHotKeys::getInstance());?>
	</td>
</tr>

<tr class="heading">
	<td colspan="2"><?= Loc::getMessage("SALE_VK_EXPORT_STATISTIC") ?></td>
</tr>
<tr>
	<td colspan="2">
		<?php $errorsNormal = $logger->getErrorsList(false); ?>
		<div id="vk_export_notify__error_normal"
			 style="display:<?= ($errorsNormal <> '') ? 'block' : 'none' ?>">
			<? echo BeginNote(); ?>
			<span id="vk_export_notify__error_normal__msg"><?= $errorsNormal ?></span>
			<span id="vk_export_notify__error_normal__button">
			<input type="button" value="<?= Loc::getMessage("SALE_VK_EXPORT_BUTTON_CLEAR_LOG") ?>"
				   onclick="if(confirm('<?= Loc::getMessage("SALE_VK_EXPORT_BUTTON_CLEAR_LOG_ALERT") ?>'))
					   {BX.Sale.VkAdmin.clearErrorLog('<?= $exportId ?>');}">
			</span>
			<? echo EndNote(); ?>
		</div>
		<?=$logger->getErrorExpandScript();?>
	</td>
</tr>

<tr>
	<td colspan="2">
		<div class="adm-bus-table-container border" id="vk_export_statistic__albums">
			<?= Vk\Journal::getStatisticText('ALBUMS', $exportId); ?>
		</div>
		<div class="adm-bus-table-container border" id="vk_export_statistic__products">
			<?= Vk\Journal::getStatisticText('PRODUCTS', $exportId); ?>
		</div>
	</td>
</tr>
<? endif; ?>


<?php

$tabControl->BeginNextTab();
?>


<!--		----------------------------------------------------->
<!--		SETTINGS tab-->
<form name="vk_exhangesettings_form" method="post" action="<?= $currPageUrl ?>">
	<?= bitrix_sessid_post() ?>
	<!--		hidden EXPORT ID and DESC-->
	<tr>
		<td colspan="2">
			<?php if ($exportId): ?>
				<input type="hidden" name="ID" value="<?= htmlspecialcharsbx($exportId) ?>">
			<? endif; ?>
		</td>
	</tr>

	<tr class="adm-detail-required-field">
		<td width="40%"><span><?= Loc::getMessage("SALE_VK_SETTINGS_NAME") ?>:</span></td>
		<td width="60%">
			<input type="text" name="VK[DESCRIPTION]" size="50" maxlength="255"
				   value="<?= isset($vkSettings["DESCRIPTION"]) ? HtmlFilter::encode($vkSettings["DESCRIPTION"]) : "" ?>">
		</td>
	</tr>


	<tr class="heading">
		<td colspan="2"><?= Loc::getMessage("SALE_VK_SETTINGS_CONNECT") ?></td>
	</tr>

	<!--		App ID-->
	<tr class="adm-detail-required-field">
		<td>
			<?= ShowJSHint(Loc::getMessage("SALE_VK_SETTINGS_EXPORT_SETTING_MANUAL", array(
				"#A1" => "<a href=/bitrix/admin/sale_vk_manual.php?lang=" . LANGUAGE_ID . ">",
				"#A2" => '</a>',
			))); ?>
			<span><?= Loc::getMessage("SALE_VK_SETTINGS_APP_ID") ?>:</span>
		</td>
		<td>
			<input type="text" name="VK[VK_SETTINGS][APP_ID]" size="25" maxlength="255"
				   value="<?= isset($vkSettings["VK_SETTINGS"]["APP_ID"]) ? $vkSettings["VK_SETTINGS"]["APP_ID"] : "" ?>">
		</td>
	</tr>

	<!--		Secret key-->
	<tr class="adm-detail-required-field">
		<td>
			<?= ShowJSHint(Loc::getMessage("SALE_VK_SETTINGS_EXPORT_SETTING_MANUAL", array(
				"#A1" => "<a href=/bitrix/admin/sale_vk_manual.php?lang=" . LANGUAGE_ID . ">",
				"#A2" => '</a>',
			))); ?>
			<span><?= Loc::getMessage("SALE_VK_SETTINGS_SECRET") ?>:</span>
		</td>
		<td>
			<input type="text" name="VK[VK_SETTINGS][SECRET]" size="25" maxlength="255"
				   value="<?= isset($vkSettings["VK_SETTINGS"]["SECRET"]) ? $vkSettings["VK_SETTINGS"]["SECRET"] : "" ?>">
		</td>
	</tr>

	<!--		get TOKEN-->
	<? if (
		isset($vkSettings["VK_SETTINGS"]["APP_ID"]) && !empty($vkSettings["VK_SETTINGS"]["APP_ID"]) &&
		isset($vkSettings["VK_SETTINGS"]["SECRET"]) && !empty($vkSettings["VK_SETTINGS"]["SECRET"])
	):
		?>
		<tr>
			<td><span><?= $authText ?></span></td>
			<td>
				<a href="<?= $authUrl ?>"><?= Loc::getMessage($authButtonText) ?></a>
			</td>
		</tr>
	<? endif; ?>


	<? if ($exportId && $vkGroupsSelector <> ''): ?>
		<tr class="heading">
			<td colspan="2"><?= Loc::getMessage("SALE_VK_SETTINGS_VK_SETTINGS") ?></td>
		</tr>
		<!--		Groud ID-->
		<tr class="adm-detail-required-field">
			<td>
				<?= ShowJSHint(Loc::getMessage("SALE_VK_SETTINGS_EXPORT_SETTING_MANUAL", array(
					"#A1" => "<a href=/bitrix/admin/sale_vk_manual.php?lang=" . LANGUAGE_ID . ">",
					"#A2" => '</a>',
				)))
				?>
				<span><?= Loc::getMessage("SALE_VK_SETTINGS_GROUP_ID") ?>:</span>
			</td>
			<td><?=$vkGroupsSelector?></td>
		</tr>
	<?endif; //group selector?>


	<?php if ($exportId && $vkCategorySelector <> ''): ?>
		<!--		CATEGORIES mapping-->
		<tr class="heading">
			<td colspan="2"><?= Loc::getMessage("SALE_VK_SETTINGS_CATEGORIES") ?></td>
		</tr>

		<tr class="adm-detail-required-field">
			<td>
				<?= ShowJSHint(Loc::getMessage("SALE_VK_SETTINGS_CATEGORIES_DEFAULT_HELP")) ?>
				<?= Loc::getMessage("SALE_VK_SETTINGS_CATEGORIES_DEFAULT"); ?>:
			</td>
			<td>
				<select id="VK[EXPORT_SETTINGS][CATEGORY_DEFAULT]"
						name="VK[EXPORT_SETTINGS][CATEGORY_DEFAULT]"><?= $vkCategorySelector ?></select>
			</td>
		</tr>
	<? endif; ?>


	<? if ($vk->isActive() && $vk->isActiveById($exportId)): ?>
		<tr class="heading">
			<td colspan="2"><?= Loc::getMessage("SALE_VK_SETTINGS_EXPORT") ?></td>
		</tr>

		<!--		level of LOG messages (default - all messages (debug))-->
		<tr>
			<td colspan="2"><input type="hidden" name="VK[LOG_LEVEL]"
								   value="<?= \Bitrix\Sale\TradingPlatform\Logger::LOG_LEVEL_DEBUG ?>"></td>
		</tr>

		<!--		AGRESSIVE export -->
		<tr>
			<td>
				<?= ShowJSHint(Loc::getMessage("SALE_VK_SETTINGS_AGRESSIVE_EXPORT_HELP")); ?>
				<?= Loc::getMessage("SALE_VK_SETTINGS_AGRESSIVE_EXPORT"); ?>:<br>
			</td>
			<td>
				<input type="hidden" name="VK[EXPORT_SETTINGS][AGRESSIVE]" value="0">
				<input <?= isset($vkSettings["EXPORT_SETTINGS"]["AGRESSIVE"]) && $vkSettings["EXPORT_SETTINGS"]["AGRESSIVE"] ? "checked = checked" : "" ?>
					type="checkbox" name="VK[EXPORT_SETTINGS][AGRESSIVE]" value="1">
			</td>
		</tr>

		<!--		IS AVAILABLE flag -->
		<tr>
			<td>
				<?= ShowJSHint(Loc::getMessage("SALE_VK_SETTINGS_ONLY_AVAILABLE_FLAG_HELP")); ?>
				<?= Loc::getMessage("SALE_VK_SETTINGS_ONLY_AVAILABLE_FLAG"); ?>:<br>
			</td>
			<td>
				<input type="hidden" name="VK[EXPORT_SETTINGS][ONLY_AVAILABLE_FLAG]" value="0">
				<input <?= isset($vkSettings["EXPORT_SETTINGS"]["ONLY_AVAILABLE_FLAG"]) && !$vkSettings["EXPORT_SETTINGS"]["ONLY_AVAILABLE_FLAG"] ? "" : "checked = checked" ?>
						type="checkbox" name="VK[EXPORT_SETTINGS][ONLY_AVAILABLE_FLAG]" value="1">
			</td>
		</tr>



		<!--		export step LIFETIME-->
		<tr>
			<td>
				<?= ShowJSHint(Loc::getMessage("SALE_VK_SETTINGS_EXPORT_TIMELIMIT")) ?>
				<?= Loc::getMessage("SALE_VK_SETTINGS_EXPORT_TIMELIMIT") ?>:
			</td>
			<td>
				<input type="text" size="3" name="VK[EXPORT_SETTINGS][TIMELIMIT]"
					   value="<?= isset($vkSettings["EXPORT_SETTINGS"]["TIMELIMIT"]) ? $vkSettings["EXPORT_SETTINGS"]["TIMELIMIT"] : Vk\Vk::DEFAULT_TIMELIMIT; ?>">
			</td>
		</tr>


		<!--		export step ITEMS COUNT-->
		<tr>
			<td>
				<?= ShowJSHint(Loc::getMessage("SALE_VK_SETTINGS_EXPORT_COUNT_ITEMS_HELP")) ?>
				<?= Loc::getMessage("SALE_VK_SETTINGS_EXPORT_COUNT_ITEMS") ?>:
			</td>
			<td>
				<input type="text" size="3" name="VK[EXPORT_SETTINGS][COUNT_ITEMS]"
					   value="<?= isset($vkSettings["EXPORT_SETTINGS"]["COUNT_ITEMS"]) ? $vkSettings["EXPORT_SETTINGS"]["COUNT_ITEMS"] : ceil(intval(Vk\Vk::MAX_EXECUTION_ITEMS) / 2); ?>">
			</td>
		</tr>

		<!--		rich LOG -->
		<tr>
			<td>
				<?= ShowJSHint(Loc::getMessage("SALE_VK_SETTINGS_RICH_LOG_HELP")); ?>
				<?= Loc::getMessage("SALE_VK_SETTINGS_RICH_LOG"); ?>:<br>
			</td>
			<td>
				<input type="hidden" name="VK[EXPORT_SETTINGS][RICH_LOG]" value="0">
				<input <?= isset($vkSettings["EXPORT_SETTINGS"]["RICH_LOG"]) && $vkSettings["EXPORT_SETTINGS"]["RICH_LOG"] ? "checked = checked" : "" ?>
						type="checkbox" name="VK[EXPORT_SETTINGS][RICH_LOG]" value="1">
			</td>
		</tr>
	<? endif; ?>

	<tr height="25">
		<td></td>
	</tr>
	<tr>
		<td>
			<input type="submit" class="adm-btn-save" name="save" id="vk_export_button__save"
				   value="<?= Loc::getMessage("SALE_VK_SETTINGS_BUTTON_SAVE") ?>" style="margin-right:10px"/>
			<input type="submit" name="apply" style="margin-right:10px" id="vk_export_button__apply"
				   value="<?= Loc::getMessage("SALE_VK_SETTINGS_BUTTON_APPLY") ?>"/>
			<input type="button" style="margin-right:10px" id="vk_export_button__cancel"
				   onclick="window.location='/bitrix/admin/sale_vk_export_list.php?lang=<?= LANGUAGE_ID ?>'"
				   name="cancel"
				   value="<?= Loc::getMessage("SALE_VK_SETTINGS_BUTTON_CANCEL") ?>"/>
		</td>
	</tr>
</form>


<? if ($vk->isActive() && $vk->isActiveById($exportId)): ?>
	<?php
//	export MAP
	$tabControl->BeginNextTab();
	?>
	<tr>
		<td id="vk_export_map_edit_table__content">
			<?= BeginNote() ?>
			<?= Loc::getMessage("SALE_VK_TAB_MAP_LOAD") ?>
			<?= EndNote() ?>
		</td>
	</tr>
<? endif; ?>
<?php
$tabControl->End();
?>


<? require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php");