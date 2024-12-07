<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Pull\SharedServer;

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/pull/options.php');
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/options.php');

$module_id = 'pull';
\Bitrix\Main\Loader::includeModule($module_id);

/** @global \CMain $APPLICATION */
$MOD_RIGHT = $APPLICATION->GetGroupRight($module_id);

include($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/pull/default_option.php');
$arDefaultValues['default'] = $pull_default_option;

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => Loc::getMessage("PULL_TAB_SETTINGS"),
		"ICON" => "pull_path",
		"TITLE" => Loc::getMessage("PULL_TAB_TITLE_SETTINGS"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$registrationErrors = [];
if (
	$_POST['Update'].$_GET['RestoreDefaults'] <> ''
	&& \check_bitrix_sessid()
	&& $MOD_RIGHT >= 'W'
)
{
	if($_GET['RestoreDefaults'] <> '')
	{
		$arDefValues = $arDefaultValues['default'];
		foreach($arDefValues as $key=>$value)
		{
			COption::RemoveOption("pull", $key);
		}
		COption::RemoveOption("pull", 'exclude_sites');
	}
	elseif($_POST['Update'] <> '')
	{
		if ($_POST['push_server_mode'] === 'N')
		{
			if (CPullOptions::GetQueueServerStatus())
			{
				CPullOptions::SendConfigDie();
				CPullOptions::SetQueueServerStatus('N');
			}
			CPullOptions::SetQueueServerMode(CPullOptions::SERVER_MODE_PERSONAL);
			SharedServer\Config::setRegistered(false);
		}
		else if ($_POST['push_server_mode'] === CPullOptions::SERVER_MODE_SHARED)
		{
			if(!CPullOptions::IsServerShared())
			{
				CPullOptions::SendConfigDie();
				CPullOptions::SetQueueServerMode(\CPullOptions::SERVER_MODE_SHARED);
			}

			if(!SharedServer\Config::isRegistered())
			{
				// try to register
				$preferredServer = $_POST["cloud_server_hostname"] ?? "";
				$registerResult = \Bitrix\Pull\SharedServer\Client::register($preferredServer);
				if($registerResult->isSuccess())
				{
					CPullOptions::SetQueueServerStatus("Y");
					CPullOptions::SetConfigTimestamp();
				}
				else
				{
					$registrationErrors = $registerResult->getErrorMessages();
				}
			}
		}
		else
		{
			if(CPullOptions::IsServerShared())
			{
				CPullOptions::SendConfigDie();
				CPullOptions::SetQueueServerMode(CPullOptions::SERVER_MODE_PERSONAL);
			}

			CPullOptions::SetQueueServerStatus('Y');
			SharedServer\Config::setRegistered(false);
			if (CPullOptions::GetSignatureKey() != $_POST['signature_key'] && $_POST['nginx_version'] > 2)
			{
				CPullOptions::SetSignatureKey($_POST['signature_key']);
			}
			if ($_POST['path_to_publish'] != "" && CPullOptions::GetPublishUrl() != $_POST['path_to_publish'])
			{
				CPullOptions::SetPublishUrl($_POST['path_to_publish']);
			}
			if ($_POST['path_to_json_rpc'] != "" && CPullOptions::GetJsonRpcUrl() != $_POST['path_to_json_rpc'])
			{
				CPullOptions::SetJsonRpcUrl($_POST['path_to_json_rpc']);
			}
			if ($_POST['path_to_modern_listener'] != "" && CPullOptions::GetListenUrl("") != $_POST['path_to_modern_listener'])
			{
				CPullOptions::SetListenUrl($_POST['path_to_modern_listener']);
			}
			if ($_POST['path_to_modern_listener_secure'] != "" && CPullOptions::GetListenSecureUrl("") != $_POST['path_to_modern_listener_secure'])
			{
				CPullOptions::SetListenSecureUrl($_POST['path_to_modern_listener_secure']);
			}
			if ($_POST['path_to_publish_web'] != "" && CPullOptions::GetPublishWebUrl() != $_POST['path_to_publish_web'])
			{
				CPullOptions::SetPublishWebUrl($_POST['path_to_publish_web']);
			}
			if ($_POST['path_to_publish_web_secure'] != "" && CPullOptions::GetPublishWebSecureUrl() != $_POST['path_to_publish_web_secure'])
			{
				CPullOptions::SetPublishWebSecureUrl($_POST['path_to_publish_web_secure']);
			}
			if ($_POST['nginx_version'] != "" && CPullOptions::GetQueueServerVersion() != $_POST['nginx_version'])
			{
				CPullOptions::SetQueueServerVersion($_POST['nginx_version']);
			}

			if (isset($_POST['websocket']) || CPullOptions::GetQueueServerVersion() > 2)
			{
				CPullOptions::SetWebSocket('Y');
			}
			else
			{
				CPullOptions::SetWebSocket('N');
			}
			if ($_POST['path_to_websocket'] != "" && CPullOptions::GetWebSocketUrl() != $_POST['path_to_websocket'])
			{
				CPullOptions::SetWebSocketUrl($_POST['path_to_websocket']);
			}
			if ($_POST['path_to_websocket_secure'] != "" && CPullOptions::GetWebSocketSecureUrl() != $_POST['path_to_websocket_secure'])
			{
				CPullOptions::SetWebSocketSecureUrl($_POST['path_to_websocket_secure']);
			}
		}

		CPullOptions::SetConfigTimestamp();
		CPullOptions::SendConfigDie();

		if (isset($_POST['push']))
		{
			if (!CPullOptions::GetPushStatus())
				CPullOptions::SetPushStatus('Y');
		}
		else
		{
			if (CPullOptions::GetPushStatus())
				CPullOptions::SetPushStatus('N');
		}

		if ($_POST['push_message_per_hit'] != "")
		{
			CPullOptions::SetPushMessagePerHit($_POST['push_message_per_hit']);
		}

		if (isset($_POST['guest']))
		{
			if (!CPullOptions::GetGuestStatus())
				CPullOptions::SetGuestStatus('Y');
		}
		else
		{
			if (CPullOptions::GetGuestStatus())
				CPullOptions::SetGuestStatus('N');
		}

		if (isset($_POST['exclude_sites']))
		{
			$arSites = Array();
			foreach ($_POST['exclude_sites'] as $site)
			{
				$site = htmlspecialcharsbx(trim($site));
				if ($site == '')
					continue;

				$arSites[$site] = $site;
			}
			CPullOptions::SetExcludeSites($arSites);
		}
	}
}
?>
<form method="post" action="<?= $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&lang=<?= LANG?>">
<?= bitrix_sessid_post()?>
<?php
$tabControl->Begin();
$tabControl->BeginNextTab();

CPullOptions::ClearAgent();
$arDependentModule = Array();
$ar = CPullOptions::GetDependentModule();
foreach ($ar as $key => $value)
	$arDependentModule[] = $value['MODULE_ID'];


$dbSites = CSite::GetList('', '', Array("ACTIVE" => "Y"));
$arSites = array();
$aSubTabs = array();
while ($site = $dbSites->Fetch())
{
	$site["ID"] = htmlspecialcharsbx($site["ID"]);
	$site["NAME"] = htmlspecialcharsbx($site["NAME"]);
	$arSites[$site["ID"]] = $site;
}
$arExcludeSites = CPullOptions::GetExcludeSites();
?>
	<tr>
		<td width="40%"><?=Loc::getMessage("PULL_OPTIONS_STATUS")?>:</td>
		<td width="60%">
			<? if(CPullOptions::ModuleEnable()): ?>
				<span style="color:green; font-weight: bold"><?=Loc::getMessage("PULL_OPTIONS_STATUS_Y")?></span>
			<? else: ?>
				<span style="color:gray; font-weight: bold"><?=Loc::getMessage("PULL_OPTIONS_STATUS_N")?></span>
			<? endif; ?>
		</td>
	</tr>
<? if(CPullOptions::ModuleEnable()): ?>
	<tr>
		<td width="40%"><?=Loc::getMessage("PULL_OPTIONS_USE")?>:</td>
		<td width="60%"><?=implode(", ", $arDependentModule)?></td>
	</tr>
<?endif;?>
	<tr>
		<td width="40%"></td>
		<td width="60%"></td>
	</tr>
	<tr>
		<td align="right" width="50%"><?=Loc::getMessage("PULL_OPTIONS_PUSH")?>:</td>
		<td><input  id="push_enable" type="checkbox" size="40" value="Y" <?=(CPullOptions::GetPushStatus()?' checked':'')?> name="push"></td>
	</tr>
	<tr>
		<td align="right" width="50%"><?= Loc::getMessage("PULL_BATCH_MAX_COUNT_MESSAGES");?>:</td>
		<td><input id="push_message_per_hit" type="text" size="10" value="<?=\CPullOptions::GetPushMessagePerHit()?>" name="push_message_per_hit"  <?=(!CPullOptions::GetPushStatus()?' disabled':'')?>></td>
	</tr>
	<tr>
		<td align="right" width="50%" valign="top"><?=Loc::getMessage("PULL_OPTIONS_GUEST")?>:</td>
		<td><input type="checkbox" size="40" value="Y" <?=(CPullOptions::GetGuestStatus()?' checked':'')?> <?=(IsModuleInstalled('statistic')?'':' disabled="true')?> name="guest" title="<?=htmlspecialcharsEx(Loc::getMessage("PULL_OPTIONS_GUEST_DESC"))?>"></td>
	</tr>
	<tr>
		<td width="40%"></td>
		<td width="60%"></td>
	</tr>
	<tr>
		<td width="40%"><nobr><?=Loc::getMessage("PULL_OPTIONS_USE_PUSH_SERVER")?></nobr>:</td>
		<td width="60%">
			<select id="push_server_mode" name="push_server_mode">
				<option value="N" <?=(!\CPullOptions::IsServerShared() && !\CPullOptions::GetQueueServerStatus() ? "selected" : "")?>>
					<?= Loc::getMessage("PULL_OPTIONS_DO_NOT_USE_PUSH_SERVER") ?>
				</option>
				<option value="<?=\CPullOptions::SERVER_MODE_SHARED?>" <?=(\CPullOptions::IsServerShared() ? "selected" : "")?>>
					<?= Loc::getMessage("PULL_OPTIONS_USE_CLOUD_SERVER") ?>
				</option>
				<option value="<?=\CPullOptions::SERVER_MODE_PERSONAL?>" <?=(!\CPullOptions::IsServerShared() && \CPullOptions::GetQueueServerStatus() ? "selected" : "")?>>
					<?= Loc::getMessage("PULL_OPTIONS_USE_LOCAL_SERVER") ?>
				</option>
			</select>
		</td>
	</tr>
	<tbody id="pull_disabled" style="<?= \CPullOptions::IsServerShared() || \CPullOptions::GetQueueServerStatus()  ? "display: none" : ""?>">
	<tr>
		<td colspan="2" align="center">
			<?= BeginNote() ?>
				<?= Loc::getMessage("PULL_OPTIONS_PUSH_SERVER_IS_REQUIRED")?>
			<?= EndNote() ?>
		</td>
	</tr>
	<tbody id="pull_cloud_settings" style="<?= \CPullOptions::IsServerShared() ? "" : "display: none"?>">
	<tr>
		<td align="right"><?=Loc::getMessage("PULL_OPTIONS_CLOUD_SERVER_REGISTRATION")?>:</td>
		<td>
			<? if (SharedServer\Config::isRegistered()): ?>
				<span style="color:green; font-weight: bold"><?= Loc::getMessage("PULL_OPTIONS_CLOUD_REGISTRATION_ACTIVE")?></span>
			<? else: ?>
				<? if (count($registrationErrors) > 0): ?>
					<span style="color:red; font-weight: bold;"><?= Loc::getMessage("PULL_OPTIONS_CLOUD_REGISTRATION_ERROR") ?>: <?=htmlspecialcharsbx($registrationErrors[0])?></span>
				<? else: ?>
					<span style="color:red; font-weight: bold;"><?= Loc::getMessage("PULL_OPTIONS_CLOUD_REGISTRATION_INACTIVE")?></span>
				<? endif ?>
			<? endif ?>
		</td>
	</tr>
	<? if (SharedServer\Config::isRegistered()): ?>
		<tr>
			<td align="right"><?=Loc::getMessage("PULL_OPTIONS_CLOUD_SERVER_ADDRESS")?>:</td>
			<td><?=htmlspecialcharsbx(SharedServer\Config::getServerAddress())?></td>
		</tr>
	<? else: ?>
		<tr>
			<td align="right"><?=Loc::getMessage("PULL_OPTIONS_REGISTER_ON_CLOUD_SERVER")?>:</td>
			<td>
				<? $cloudServerResult = SharedServer\Client::getServerList();?>
				<? if($cloudServerResult->isSuccess()): ?>
					<select name="cloud_server_hostname">
						<option value="">
							<?=Loc::getMessage("PULL_OPTIONS_CLOUD_SERVER_ADDRESS_AUTO_SELECT")?>
						</option>
						<? foreach ($cloudServerResult->getData()['serverList'] as $server): ?>
							<option value="<?= htmlspecialcharsbx($server['url'])?>">
								<?= htmlspecialcharsbx($server['url'] . " (" . $server['region']. ")") ?>
							</option>
						<? endforeach ?>
					</select>
				<? else: ?>
					<span style="color:red;"><?=Loc::getMessage("PULL_OPTIONS_CLOUD_SERVER_ADDRESS_LIST_ERROR", ["#HOST#" => SharedServer\Config::DEFAULT_SERVER])?></span>
				<? endif ?>
			</td>
		</tr>
		<? if ($MOD_RIGHT >= 'W'): ?>
			<tr>
				<td></td>
				<td >
					<input type="submit" name="Update" value="<?= Loc::getMessage('PULL_OPTIONS_CLOUD_SERVER_REGISTER')?>" class="adm-btn-save">
				</td>
			</tr>
		<? endif ?>
	<? endif ?>
	<tr>
		<td colspan="2" align="center">
			<?= BeginNote()?>
				<?= Loc::getMessage("PULL_OPTIONS_CLOUD_ACTIVE_KEY_REQUIRED")?>
			<?= EndNote()?>
		</td>
	</tr>
	<tbody id="pull_local_settings" style="<?= \CPullOptions::IsServerShared() || !CPullOptions::GetQueueServerStatus() ? "display: none" : ""?>">
	<tr>
		<td width="40%" valign="top" style="padding-top:9px"><nobr><?=Loc::getMessage("PULL_OPTIONS_NGINX_VERSION")?></nobr>:</td>
		<td width="60%">
			<nobr>
				<label>
					<input type="radio" id="config_nginx_version_4" value="4" name="nginx_version" <?=(CPullOptions::GetQueueServerVersion() == 4?' checked':'')?>>
					<?=Loc::getMessage("PULL_OPTIONS_NGINX_VERSION_730_2")?>
				</label>
			</nobr>
			<?php if (defined('PULL_ALLOW_VERSION_5')): ?>
			<nobr>
				<label>
					<input type="radio" id="config_nginx_version_5" value="5" name="nginx_version" <?=(CPullOptions::GetQueueServerVersion() == 5?' checked':'')?>>
					<?=Loc::getMessage("PULL_OPTIONS_NGINX_VERSION_760")?>
				</label>
			</nobr>
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<?= BeginNote(); ?>
			<?=Loc::getMessage('PULL_NOTIFY_DEPRECATED');?>
			<?= EndNote(); ?>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><b><?=Loc::getMessage('PULL_OPTIONS_HEAD_PUB_2')?></b></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage("PULL_OPTIONS_PATH_TO_PUBLISH")?>:</td>
		<td><input id="config_path_to_publish" type="text" size="40" value="<?=htmlspecialcharsbx(CPullOptions::GetPublishUrl())?>" name="path_to_publish"></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage("PULL_OPTIONS_PATH_TO_JSON_RPC")?>:</td>
		<td><input id="config_path_to_json_rpc" type="text" size="40" value="<?=htmlspecialcharsbx(CPullOptions::GetJsonRpcUrl())?>" name="path_to_json_rpc"></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage("PULL_OPTIONS_SIGNATURE_KEY")?>:</td>
		<td><input id="config_signature_key" type="text" size="40" value="<?=htmlspecialcharsbx(CPullOptions::GetSignatureKey())?>" name="signature_key" <?=(CPullOptions::GetQueueServerVersion() > 2? '':'disabled="true"')?>></td>
	</tr>
	<tr>
		<td></td>
		<td><div id="config_signature_key_error" style="color: darkred; font-weight: bold; <?=(CPullOptions::GetQueueServerStatus() && CPullOptions::GetQueueServerVersion() > 3 && !CPullOptions::GetSignatureKey()? '':'display: none')?>"><b><?=Loc::getMessage('PULL_OPTIONS_SIGNATURE_KEY_ERROR')?></b></div></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><b><?=Loc::getMessage('PULL_OPTIONS_PUBLISH_WEB_HEAD')?></b></td>
	</tr>
	<tr>
		<td ><?=Loc::getMessage("PULL_OPTIONS_PATH_TO_PUBLISH_WEB")?>:</td>
		<td><input id="config_path_to_publish_web" type="text" size="40" value="<?=htmlspecialcharsbx(CPullOptions::GetPublishWebUrl())?>" name="path_to_publish_web" <?=(CPullOptions::GetQueueServerVersion() > 3? '': 'disabled="true"')?>></td>
	</tr>
	<tr>
		<td ><?=Loc::getMessage("PULL_OPTIONS_PATH_TO_PUBLISH_WEB_SECURE")?>:</td>
		<td><input id="config_path_to_publish_web_secure" type="text" size="40" value="<?=htmlspecialcharsbx(CPullOptions::GetPublishWebSecureUrl())?>" name="path_to_publish_web_secure"  <?=(CPullOptions::GetQueueServerVersion() > 3? '': 'disabled="true"')?>></td>
	</tr>
	<tr>
		<td width="40%"></td>
		<td width="60%">
			<?=Loc::getMessage("PULL_OPTIONS_PUBLISH_WEB_DESC")?>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><b><?=Loc::getMessage('PULL_OPTIONS_HEAD_SUB_MODERN')?></b></td>
	</tr>
	<tr>
		<td ><?=Loc::getMessage("PULL_OPTIONS_PATH_TO_LISTENER")?>:</td>
		<td><input id="config_path_to_modern_listener" type="text" size="40" value="<?=htmlspecialcharsbx(CPullOptions::GetListenUrl())?>" name="path_to_modern_listener"></td>
	</tr>
	<tr>
		<td ><?=Loc::getMessage("PULL_OPTIONS_PATH_TO_LISTENER_SECURE")?>:</td>
		<td><input id="config_path_to_modern_listener_secure" type="text" size="40" value="<?=htmlspecialcharsbx(CPullOptions::GetListenSecureUrl())?>" name="path_to_modern_listener_secure"></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><b><?=Loc::getMessage('PULL_OPTIONS_HEAD_SUB_WS')?></b></td>
	</tr>
	<tr>
		<td align="right" width="50%"><?=Loc::getMessage("PULL_OPTIONS_WEBSOCKET")?>:</td>
		<td><input type="checkbox" size="40" value="Y" <?=(CPullOptions::GetWebSocket()?' checked':'')?> id="config_websocket" name="websocket" <?=(CPullOptions::GetQueueServerVersion() == 2? '': 'disabled="true"')?>></td>
	</tr>
	<tr>
		<td ><?=Loc::getMessage("PULL_OPTIONS_PATH_TO_LISTENER")?>:</td>
		<td><input id="config_path_to_websocket" type="text" size="40" value="<?=htmlspecialcharsbx(CPullOptions::GetWebSocketUrl())?>" name="path_to_websocket" <?=(!CPullOptions::GetWebSocketStatus() ? 'disabled="true"': '')?></td>
	</tr>
	<tr>
		<td ><?=Loc::getMessage("PULL_OPTIONS_PATH_TO_LISTENER_SECURE")?>:</td>
		<td><input id="config_path_to_websocket_secure" type="text" size="40" value="<?=htmlspecialcharsbx(CPullOptions::GetWebSocketSecureUrl())?>" name="path_to_websocket_secure" <?=(!CPullOptions::GetWebSocketStatus() ? 'disabled="true"': '')?></td>
	</tr>
	<tr>
		<td width="40%"></td>
		<td width="60%">
			<?=Loc::getMessage("PULL_OPTIONS_WEBSOCKET_DESC")?>
		</td>
	</tr>
	<?if (count($arSites) > 1 || count($arExcludeSites) > 0):?>
		<tbody id="pull_disabled_sites">
		<tr class="heading">
			<td colspan="2"><b><?=Loc::getMessage('PULL_OPTIONS_HEAD_BLOCK')?></b></td>
		</tr>
		<tr valign="top">
			<td><?=Loc::getMessage("PULL_OPTIONS_SITES")?>:</td>
			<td>
				<select name="exclude_sites[]" multiple size="4">
					<option value=""></option>
					<?foreach($arSites as $site):?>
						<option value="<?=$site['ID']?>" <?=(isset($arExcludeSites[$site['ID']])?' selected':'')?>><? echo $site['NAME'].' ['.$site['ID'].']'?></option>
					<?endforeach;?>
				</select>
			</td>
		</tr>
		</tbody>
	<?endif;?>

	<?$tabControl->Buttons();?>
<script>
BX.bind(BX('push_enable'), 'change', function(){
	BX('push_message_per_hit').disabled = !this.checked;
});

BX.bind(BX('push_server_mode'), 'bxchange', function() {
	var mode = this.value;
	var disabledServerSection = BX("pull_disabled");
	var sharedServerSettings = BX("pull_cloud_settings");
	var localServerSettings = BX("pull_local_settings");
	var disabledSites = BX("pull_disabled_sites");
	if (mode == "N")
	{
		disabledServerSection.style.removeProperty("display");
		sharedServerSettings.style.display = "none";
		localServerSettings.style.display = "none";
		if(disabledSites)
		{
			disabledSites.style.display = "none";
		}
	}
	else if (mode == "<?=CPullOptions::SERVER_MODE_PERSONAL?>")
	{
		disabledServerSection.style.display = "none";
		sharedServerSettings.style.display = "none";
		localServerSettings.style.removeProperty("display");
		if(disabledSites)
		{
			disabledSites.style.removeProperty("display");
		}
	}
	else
	{
		disabledServerSection.style.display = "none";
		sharedServerSettings.style.removeProperty("display");
		localServerSettings.style.display = "none";
		if(disabledSites)
		{
			disabledSites.style.removeProperty("display");
		}
	}
});

BX.bind(BX('config_nginx'), 'change', function(){
	if (this.checked)
	{
		if (confirm("<?=GetMessageJS("PULL_OPTIONS_NGINX_CONFIRM")?>"))
		{
			BX('config_nginx_version_1').disabled = false;
			BX('config_nginx_version_2').disabled = false;
			BX('config_nginx_version_3').disabled = false;
			BX('config_nginx_version_4').disabled = false;
			BX('config_path_to_publish').disabled = false;
			BX('config_path_to_json_rpc').disabled = false;
			BX('config_path_to_modern_listener').disabled = false;
			BX('config_path_to_modern_listener_secure').disabled = false;
			BX('config_signature_key').disabled = false;

			if (BX('config_nginx_version_2').checked || BX('config_nginx_version_3').checked || BX('config_nginx_version_4').checked)
			{
				BX('config_websocket').disabled = false;
				BX('config_signature_key').disabled = BX('config_nginx_version_2').checked;

				if (BX('config_websocket').checked || BX('config_nginx_version_3').checked)
				{
					BX('config_path_to_websocket').disabled = false;
					BX('config_path_to_websocket_secure').disabled = false;
				}
				if (BX('config_nginx_version_3').checked || BX('config_nginx_version_4').checked)
				{
					BX('config_websocket').disabled = true;
				}
				if (BX('config_nginx_version_4').checked)
				{
					BX('config_path_to_publish_web').disabled = false;
					BX('config_path_to_publish_web_secure').disabled = false;
					BX.style(BX('config_signature_key_error'), 'display', BX('config_signature_key').value.toString().length>0? 'none': 'block');
				}
				else
				{
					BX('config_path_to_publish_web').disabled = true;
					BX('config_path_to_publish_web_secure').disabled = true;
					BX.style(BX('config_signature_key_error'), 'display', 'none');
				}
			}
			else
			{
				BX('config_websocket').disabled = true;
				BX('config_signature_key').disabled = true;
				BX('config_path_to_websocket').disabled = true;
				BX('config_path_to_websocket_secure').disabled = true;
				BX('config_path_to_publish_web').disabled = true;
				BX('config_path_to_publish_web_secure').disabled = true;
				BX.style(BX('config_signature_key_error'), 'display', 'none');
			}
		}
		else
		{
			this.checked = false;
			BX.style(BX('config_signature_key_error'), 'display', 'none');
		}
	}
	else
	{
		BX('config_nginx_version_1').disabled = true;
		BX('config_nginx_version_2').disabled = true;
		BX('config_nginx_version_3').disabled = true;
		BX('config_nginx_version_4').disabled = true;
		BX('config_signature_key').disabled = true;
		BX('config_path_to_publish').disabled = true;
		BX('config_path_to_json_rpc').disabled = true;
		BX('config_path_to_modern_listener').disabled = true;
		BX('config_path_to_modern_listener_secure').disabled = true;

		BX('config_websocket').disabled = true;
		BX('config_path_to_websocket').disabled = true;
		BX('config_path_to_websocket_secure').disabled = true;
		BX('config_path_to_publish_web').disabled = true;
		BX('config_path_to_publish_web_secure').disabled = true;

		BX.style(BX('config_signature_key_error'), 'display', 'none');
	}
});
BX.bind(BX('config_nginx_version_1'), 'change', function(){

	BX('config_signature_key').disabled = true;

	BX('config_path_to_publish_web').disabled = true;
	BX('config_path_to_publish_web_secure').disabled = true;

	BX('config_websocket').disabled = true;
	BX('config_websocket').checked = false;
	BX('config_path_to_websocket').disabled = true;
	BX('config_path_to_websocket_secure').disabled = true;

	BX.style(BX('config_signature_key_error'), 'display', 'none');
});
BX.bind(BX('config_nginx_version_2'), 'change', function(){

	BX('config_signature_key').disabled = true;

	BX('config_path_to_publish_web').disabled = true;
	BX('config_path_to_publish_web_secure').disabled = true;

	BX('config_websocket').disabled = false;
	if (BX('config_websocket').checked)
	{
		BX('config_path_to_websocket').disabled = false;
		BX('config_path_to_websocket_secure').disabled = false;
	}

	BX.style(BX('config_signature_key_error'), 'display', 'none');
});
BX.bind(BX('config_nginx_version_3'), 'change', function(){

	BX('config_signature_key').disabled = false;

	BX('config_websocket').disabled = true;
	BX('config_websocket').checked = true;
	BX('config_path_to_websocket').disabled = false;
	BX('config_path_to_websocket_secure').disabled = false;

	BX('config_websocket').disabled = true;
	BX('config_websocket').checked = true;
	BX('config_path_to_websocket').disabled = false;
	BX('config_path_to_websocket_secure').disabled = false;
	BX('config_path_to_publish_web').disabled = true;
	BX('config_path_to_publish_web_secure').disabled = true;

	BX.style(BX('config_signature_key_error'), 'display', 'none');
});

BX.bind(BX('config_nginx_version_4'), 'change', function(){

	BX('config_signature_key').disabled = false;
	BX('config_path_to_json_rpc').disabled = true;

	BX('config_websocket').disabled = true;
	BX('config_websocket').checked = true;
	BX('config_path_to_websocket').disabled = false;
	BX('config_path_to_websocket_secure').disabled = false;

	BX('config_path_to_websocket').disabled = false;
	BX('config_path_to_websocket_secure').disabled = false;

	BX('config_path_to_publish_web').disabled = false;
	BX('config_path_to_publish_web_secure').disabled = false;

	BX.style(BX('config_signature_key_error'), 'display', BX('config_signature_key').value.toString().length>0? 'none': 'block');
});
BX.bind(BX('config_nginx_version_5'), 'change', function(){

	BX('config_signature_key').disabled = false;
	BX('config_path_to_json_rpc').disabled = false;

	BX('config_websocket').disabled = true;
	BX('config_websocket').checked = true;
	BX('config_path_to_websocket').disabled = false;
	BX('config_path_to_websocket_secure').disabled = false;

	BX('config_path_to_websocket').disabled = false;
	BX('config_path_to_websocket_secure').disabled = false;

	BX('config_path_to_publish_web').disabled = false;
	BX('config_path_to_publish_web_secure').disabled = false;

	BX.style(BX('config_signature_key_error'), 'display', BX('config_signature_key').value.toString().length>0? 'none': 'block');
});
BX.bind(BX('config_websocket'), 'change', function(){
	if (this.checked)
	{
		BX('config_path_to_websocket').disabled = false;
		BX('config_path_to_websocket_secure').disabled = false;
	}
	else
	{
		BX('config_path_to_websocket').disabled = true;
		BX('config_path_to_websocket_secure').disabled = true;
	}
});
BX.bind(BX('config_signature_key'), 'keyup', function() {
	BX.style(BX('config_signature_key_error'), 'display', this.value.toString().length>0? 'none': 'block');
});
function RestoreDefaults()
{
	if(confirm('<?= AddSlashes(Loc::getMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING'))?>'))
		window.location = "<?= $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?= LANG?>&mid=<?= urlencode($mid)."&".bitrix_sessid_get();?>";
}
</script>
<input type="submit" name="Update" <?if ($MOD_RIGHT<'W') echo "disabled" ?> value="<?= Loc::getMessage('MAIN_SAVE')?>" class="adm-btn-save">
<input type="reset" name="reset" value="<?= Loc::getMessage('MAIN_RESET')?>">
<?=bitrix_sessid_post();?>
<input type="button" <?if ($MOD_RIGHT<'W') echo "disabled" ?> title="<?= Loc::getMessage('MAIN_HINT_RESTORE_DEFAULTS')?>" OnClick="RestoreDefaults();" value="<?= Loc::getMessage('MAIN_RESTORE_DEFAULTS')?>">
<?$tabControl->End();?>
</form>
<?=BeginNote();?>
	<?=Loc::getMessage("PULL_OPTIONS_NGINX_DOC")?> <a href="<?=(LANGUAGE_ID == "ru"? "https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=41&LESSON_ID=2033": "https://www.bitrixsoft.com/support/training/course/index.php?COURSE_ID=26&LESSON_ID=5144")?>" target="_blank"><?=Loc::getMessage("PULL_OPTIONS_NGINX_DOC_LINK")?></a>.
<?=EndNote();?>
</div>