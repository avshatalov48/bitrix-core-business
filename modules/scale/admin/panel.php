<?
/**
 * Bitrix Framework
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

define("ADMIN_MODULE_NAME", "scale");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if (!$USER->IsAdmin())
	$APPLICATION->AuthForm(Loc::getMessage("SCALE_PANEL_ACCESS_DENIED"));

if(!\Bitrix\Main\Loader::includeModule("scale"))
	ShowError(Loc::getMessage("SCALE_PANEL_MODULE_NOT_INSTALLED"));

$APPLICATION->SetTitle(Loc::getMessage("SCALE_PANEL_TITLE"));

$APPLICATION->AddHeadScript("/bitrix/js/scale/core.js");
$APPLICATION->AddHeadScript("/bitrix/js/scale/communicator.js");
$APPLICATION->AddHeadScript("/bitrix/js/scale/collection.js");
$APPLICATION->AddHeadScript("/bitrix/js/scale/actionsparamstypes.js");
$APPLICATION->AddHeadScript("/bitrix/js/scale/actionprocessdialog.js");
$APPLICATION->AddHeadScript("/bitrix/js/scale/actionparamsdialog.js");
$APPLICATION->AddHeadScript("/bitrix/js/scale/actionresultdialog.js");
$APPLICATION->AddHeadScript("/bitrix/js/scale/loadbar.js");
$APPLICATION->AddHeadScript("/bitrix/js/scale/role.js");
$APPLICATION->AddHeadScript("/bitrix/js/scale/action.js");
$APPLICATION->AddHeadScript("/bitrix/js/scale/infotable.js");
$APPLICATION->AddHeadScript("/bitrix/js/scale/itloadbar.js");
$APPLICATION->AddHeadScript("/bitrix/js/scale/server.js");
$APPLICATION->AddHeadScript("/bitrix/js/scale/provider.js");
$APPLICATION->AddHeadScript("/bitrix/js/scale/admin_frame.js");

$APPLICATION->SetAdditionalCSS("/bitrix/js/scale/css/scale-page-style.css");

$jsLangMesIds = array(
	"SCALE_PANEL_JS_APD_BUT_START",
	"SCALE_PANEL_JS_APD_2_CONFIRM",
	"SCALE_PANEL_JS_APD_2_NOT_CONCIDE",
	"SCALE_PANEL_JS_ACT_CONFIRM",
	"SCALE_PANEL_JS_ERROR",
	"SCALE_PANEL_JS_ARD_RES",
	"SCALE_PANEL_JS_ACT_RES_ERROR",
	"SCALE_PANEL_JS_ARD_NAME",
	"SCALE_PANEL_JS_ARD_RESULT",
	"SCALE_PANEL_JS_ARD_MESSAGE",
	"SCALE_PANEL_JS_APD_TITLE",
	"SCALE_PANEL_JS_ACT_EXEC_ERROR",
	"SCALE_PANEL_JS_ACT_EXEC_SUCCESS",
	"SCALE_PANEL_JS_MENU",
	"SCALE_PANEL_JS_PASS_MUST_BE_CHANGED",
	"SCALE_PANEL_JS_BID_ERROR",
	"SCALE_JS_SERVER_TITLE_TITLE",
	"SCALE_PANEL_MONITORING_DISABLED",
	"SCALE_PANEL_JS_ADVICE_TO_BACKUP",
	"SCALE_PANEL_JS_GLOBAL_ACTIONS",
	"SCALE_PANEL_JS_MONITORING_DATABASE_CREATING",
	"SCALE_PANEL_JS_ACT_CONFIRM_TITLE",
	"SCALE_PANEL_JS_ADVICE_TO_BACKUP_TITLE",
	"SCALE_PANEL_JS_CANCEL",
	"SCALE_PANEL_JS_CLOSE",
	"SCALE_PANEL_JS_BX_ENV_NOT_INSTALLED",
	"SCALE_PANEL_JS_BX_ENV_NEED_UPDATE",
	"SCALE_PANEL_JS_BX_ENV_VERSION",
	"SCALE_PANEL_JS_BX_INFO_ERROR",
	"SCALE_PANEL_JS_ACT_SERVER_WILL_AVAILABLE",
	"SCALE_PANEL_JS_ACT_HOUR",
	"SCALE_PANEL_JS_ACT_MIN",
	"SCALE_PANEL_JS_ACT_SEC",
	"SCALE_PANEL_JS_EXTRA_DB_CONFIRM",
	"SCALE_PANEL_JS_EXTRA_DB_CONFIRM_TITLE",
	"SCALE_PANEL_JS_WFA_TITLE",
	"SCALE_PANEL_JS_WFA_TEXT",
	"SCALE_PANEL_JS_PROVIDER",
	"SCALE_PANEL_JS_PROVIDER_CHOOSE",
	"SCALE_PANEL_JS_PROVIDER_MANUAL",
	"SCALE_PANEL_JS_PROVIDER_BUT_CHOOSE",
	"SCALE_PANEL_JS_PROVIDER_CONFIG_CHOOSE",
	"SCALE_PANEL_JS_PROVIDER_NO_CONFIGS",
	"SCALE_PANEL_JS_PROVIDER_LIST_ERROR",
	"SCALE_PANEL_JS_PROVIDER_ERROR",
	"SCALE_PANEL_JS_PROVIDER_CONFIGS_ERROR",
	"SCALE_PANEL_JS_PROVIDER_ORDER_ERROR",
	"SCALE_PANEL_JS_REFRESH_TITLE",
	"SCALE_PANEL_JS_REFRESH_TEXT",
	"SCALE_PANEL_JS_BX_VER_ERROR",
	"SCALE_PANEL_JS_BX_ENV_NEED_UPDATE2",
	"SCALE_PANEL_JS_PROVIDER_ORDER_SUCCESS",
	"SCALE_PANEL_JS_PROVIDER_ORDER_SUCCESS_TITLE",
	"SCALE_PANEL_JS_WARNING",
	"SCALE_PANEL_JS_LOAD_FILE"
);

$dataRefreshTimeInterval = 300000; //ms how often we want to refresh monitoring info.
$serversList = \Bitrix\Scale\ServersData::getList();
$runningAction = \Bitrix\Scale\ActionsData::checkRunningAction(); //If one of the actions runs now - get it params, to show dialog, and block page
$pullCreateAction = 'CREATE_PULL';

if(empty($serversList))
{
	$netIfaces = \Bitrix\Scale\Helper::getNetworkInterfaces();

	if(is_array($netIfaces) && count($netIfaces) > 1)
		$pullCreateAction = "CREATE_PULL_NET_IFACE";
}

\CUserCounter::Increment($USER->GetID(),'SCALE_PANEL_VISITS', SITE_ID, false);
\CUserCounter::Set($USER->GetID(),'SCALE_SERVERS_COUNT',count($serversList), SITE_ID, '', false);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if(\Bitrix\Scale\Helper::checkBxEnvVersion())
{
	?>
	<div class="adm-scale-page-wrap" id="adm-scale-page-wrap">
	<div class="adm-scale-blocks-wrapper" id="adm-scale-blocks-wrapper"<?=empty($serversList) ? ' style="width:100%;"' : ''?>>
		<?if(Bitrix\Main\ModuleManager::isModuleInstalled("cluster") || empty($serversList)):?>
			<?if(!empty($serversList)):?>
				<div class="adm-scale-block adm-scale-block-empty" onclick="BX.Scale.Provider.getList();">
			<?else:?>
				<p class="adm-scale-page-notify"><?=Loc::getMessage("SCALE_PANEL_NOTIFY_CREATE_SRV")?></p>
				<div class="adm-scale-block adm-scale-block-empty" onclick="BX.Scale.actionsCollection.getObject('<?=$pullCreateAction?>').start();">
					<?endif;?>
					<div class="adm-scale-block-header">
						<span class="adm-scale-title"><?=Loc::getMessage("SCALE_PANEL_NEW_SERVER")?></span>
						<span class="adm-scale-img"></span>
					</div>
				</div>
			<?endif;?>
		</div>
	</div>

	<?if(!Bitrix\Main\ModuleManager::isModuleInstalled("cluster") && !empty($serversList)):?>
		<div class="adm-scale-page-wrap" id="adm-scale-page-wrap">
			<div class="adm-scale-blocks-wrapper" id="adm-scale-blocks-wrapper">
				<?=Loc::getMessage("SCALE_PANEL_MODULE_CLUSTER_NOT_INSTALLED")?>
			</div>
		</div>
	<?endif;?>

	<script type="text/javascript">
		BX.ready(function(){

			<?foreach($jsLangMesIds as $langMesId):?>BX.message["<?=$langMesId?>"] ="<?=\CUtil::JSEscape(Loc::getMessage($langMesId))?>"; <?endforeach;?>

			BX.Scale.actionsCollection = new BX.Scale.Collection(BX.Scale.Action, <?=CUtil::PhpToJSObject( Bitrix\Scale\ActionsData::getList(true))?>);
			BX.Scale.rolesList = <?=CUtil::PhpToJSObject( \Bitrix\Scale\RolesData::getList())?>;
			BX.Scale.sitesList = <?=CUtil::PhpToJSObject( \Bitrix\Scale\SitesData::getList())?>;
			BX.Scale.monitoringEnabled = <?=Bitrix\Scale\Monitoring::isEnabled() ? "true" : "false"?>;
			BX.Scale.bitrixEnvType = "<?=getenv('BITRIX_ENV_TYPE')?>";
			BX.Scale.monitoringCategories = {};
			BX.Scale.isMonitoringDbCreated = {};

			<?foreach($serversList as $hostname => $server):?>
				BX.Scale.monitoringCategories["<?=$hostname?>"] = <?=CUtil::PhpToJSObject(\Bitrix\Scale\Monitoring::getInfoTableCategoriesList($hostname))?>;
				BX.Scale.isMonitoringDbCreated["<?=$hostname?>"] = <?=Bitrix\Scale\Monitoring::isDatabaseCreated($hostname) ? "true" : "false"?>;
			<?endforeach;?>

			BX.Scale.serversCollection = new BX.Scale.Collection(BX.Scale.Server, <?=CUtil::PhpToJSObject($serversList)?>);

			BX.Scale.Communicator.url = "/bitrix/admin/scale_ajax.php";
			BX.Scale.AdminFrame.init({
				frameObjectName: "adm-scale-page-wrap",
				srvFrameObjectName: "adm-scale-blocks-wrapper",
				graphPageUrl: "<?=$APPLICATION->GetCurDir()?>scale_graph.php?lang=<?=LANGUAGE_ID?>"
			});
			BX.Scale.AdminFrame.build();

			<?if(Bitrix\Scale\Monitoring::isEnabled()):?>
				BX.Scale.AdminFrame.refreshServersRolesLoadbars();
				BX.Scale.AdminFrame.refreshingDataStart(<?=$dataRefreshTimeInterval?>);
			<?endif;?>

			<?if(!empty($runningAction)):?>
				BX.Scale.AdminFrame.waitForAction("<?=key($runningAction)?>");
			<?endif;?>
		});
	</script>
<?
}
else
{
	?>
	<div class="adm-scale-page-wrap" id="adm-scale-page-wrap">
		<div class="adm-scale-blocks-wrapper" id="adm-scale-blocks-wrapper">
			<?=Loc::getMessage("SCALE_PANEL_BVM_TOO_OLD").". ".Loc::getMessage("SCALE_PANEL_BVM_TOO_OLD_DOC")?>
		</div>
	</div>
<?
}
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>