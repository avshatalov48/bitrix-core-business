<?
if(!$USER->CanDoOperation('edit_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

use Bitrix\Main\Text\Converter;

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

$module_id = "socialservices";
CModule::IncludeModule($module_id);

$GLOBALS["APPLICATION"]->SetAdditionalCSS("/bitrix/js/socialservices/css/ss.css");

$arSites = array();
$arSiteList = array('');
$dbSites = CSite::GetList($b = "sort", $o = "asc", array("ACTIVE" => "Y"));
while ($arSite = $dbSites->Fetch())
{
	$arSites[] = $arSite;
	$arSiteList[] = $arSite['ID'];
}

$oAuthManager = new CSocServAuthManager();
$arOptions = $oAuthManager->GetSettings();

$groupDenyAuth = CSocServAuth::getGroupsDenyAuth();
$groupDenySplit = CSocServAuth::getGroupsDenySplit();

$allowAuthorization = COption::GetOptionString("socialservices", "allow_registration", "Y") == "Y";

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "",
		"TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
	array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_6"), "ICON" => "",
		"TITLE" => GetMessage("MAIN_OPTION_REG")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["Update"].$_POST["Apply"].$_POST["RestoreDefaults"] <> '' && check_bitrix_sessid())
{

	if($_POST["RestoreDefaults"] <> '')
	{
		COption::RemoveOption($module_id);
	}
	else
	{
		COption::SetOptionString("socialservices", "use_on_sites", serialize($_POST["use_on_sites"]));
		CAgent::RemoveModuleAgents("socialservices");
		CAgent::AddAgent("CSocServAuthManager::SendSocialservicesMessages();", "socialservices", "N", 100, "", "Y", "");
		CAgent::AddAgent("CSocServMessage::CleanUp();", "socialservices", "N", 86400, "", "Y", "");
		foreach($arSiteList as $site)
		{
			$suffix = ($site <> ''? '_bx_site_'.$site:'');
			$siteId = ($site <> '' ? $site : SITE_ID);

			COption::SetOptionString("socialservices", "auth_services".$suffix, serialize($_POST["AUTH_SERVICES".$suffix]));
			COption::SetOptionString("socialservices", "twitter_search_hash".$suffix, $_POST["twitter_search_hash".$suffix]);

			foreach($arOptions as $option)
			{
				if(is_array($option))
				{
					$option[0] .= $suffix;
					if($option[3][0] == 'statictext')
					{
						$option[3][0] = 'text';
					}
				}
				__AdmSettingsSaveOption($module_id, $option);
			}
		}
	}
	$sendTwit = $allowSendActivity = 'N';

	if($_POST["get_message_from_twitter"] == 'Y')
	{
		$sendTwit = 'Y';
		CAgent::AddAgent("CSocServAuthManager::GetTwitMessages();", "socialservices", "N", 90, "", "Y", "");
	}
	COption::SetOptionString("socialservices", "get_message_from_twitter", $sendTwit);

	if($_POST["allow_send_user_activity"] == 'Y')
		$allowSendActivity = 'Y';
	COption::SetOptionString("socialservices", "allow_send_user_activity", $allowSendActivity);

	CSocServAuth::setGroupsDenyAuth($_REQUEST["group_deny_auth"]);
	CSocServAuth::setGroupsDenySplit($_REQUEST["group_deny_split"]);

	if(isset($_REQUEST["allow_registration"]))
	{
		COption::SetOptionString("socialservices", "allow_registration", $_REQUEST["allow_registration"] == "N" ? "N" : "Y");
	}

	if(strlen($_REQUEST["back_url_settings"]) > 0)
	{
		if($_POST["Apply"] <> '' || $_POST["RestoreDefaults"] <> '')
			LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam().($_REQUEST["siteTabControl_active_tab"] <> ''? "&siteTabControl_active_tab=".urlencode($_REQUEST["siteTabControl_active_tab"]):''));
		else
			LocalRedirect($_REQUEST["back_url_settings"]);
	}
	else
	{
		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&".$tabControl->ActiveTabParam().($_REQUEST["siteTabControl_active_tab"] <> ''? "&siteTabControl_active_tab=".urlencode($_REQUEST["siteTabControl_active_tab"]):''));
	}
}

?>
<script type="text/javascript">
function MoveRowUp(a)
{
	var table = BX.findParent(a, {'tag':'table'});
	var index = BX.findParent(a, {'tag':'tr'}).rowIndex;
	if(index == 0)
		return;
	table.rows[index].parentNode.insertBefore(table.rows[index], table.rows[index-1]);
	a.focus();
}
function MoveRowDown(a)
{
	var table = BX.findParent(a, {'tag':'table'});
	var index = BX.findParent(a, {'tag':'tr'}).rowIndex;
	if(index == table.rows.length-1)
		return;
	table.rows[index].parentNode.insertBefore(table.rows[index+1], table.rows[index]);
	a.focus();
}

window.networkRegister = (function(){
	var p, domainUrl;

	return function(s)
	{
		if(BX.type.isString(s))
		{
			domainUrl = s;

			BX.ajax.loadJSON('/bitrix/tools/oauth/socserv.ajax.php', {
				action: 'registernetwork',
				url: s,
				sessid: BX.bitrix_sessid()
			}, function(res){
				if(res.error)
				{
					alert(res.error);
				}
				else
				{
					BX('b24net_out').innerHTML = '';
					BX.adjust(BX('b24net_out'), {
						children: [
							BX.create('DIV', {html: '<?=GetMessageJS('SOC_OPT_B24NET_CLIENT_ID');?>: <b>' + BX.util.htmlspecialchars(res.client_id) + '</b><br /><?=GetMessageJS('SOC_OPT_B24NET_CLIENT_SECRET');?>: <b>' + BX.util.htmlspecialchars(res.client_secret) + '</b><br ><br />'}),
							BX.create('INPUT', {
								attrs: {type: "button",value:'<?=GetMessageJS('SOC_OPT_B24NET_PUT');?>'},
								events: {
									click: function()
									{
										networkRegister(res);
									}
								}
							})
						]
					});

					BX.show(BX('b24net_out'));
					BX.hide(BX('b24net_in'));
				}
			})
		}
		else if(!!s && !!s.client_id)
		{
			var currentSite = document.forms['socserv_settings'].siteTabControl_active_tab.value;

			currentSite = currentSite == "opt_common" ? "" : currentSite.replace("opt_", "_bx_");

			if(!document.forms.socserv_settings['bitrix24net_domain' + currentSite])
			{
				document.forms.socserv_settings.appendChild(BX.create('input', {attrs: {
					'type': 'hidden',
					'name': 'bitrix24net_domain' + currentSite
				}}));

				var el = document.forms.socserv_settings['bitrix24net_id' + currentSite];

				BX.findPreviousSibling(el.parentNode.parentNode).cells[1].innerHTML = BX.util.htmlspecialchars(domainUrl);
			}

			document.forms.socserv_settings['bitrix24net_id' + currentSite].value = s.client_id;
			document.forms.socserv_settings['bitrix24net_secret' + currentSite].value = s.client_secret;
			document.forms.socserv_settings['bitrix24net_domain' + currentSite].value = domainUrl;

			BX('AUTH_SERVICES'+currentSite+'Bitrix24Net').checked = true;

			BX.focus(document.forms.socserv_settings['bitrix24net_id' + currentSite]);

			p.close();
		}
		else
		{
			if(!p)
			{
				p = new BX.PopupWindow("socservB24NetPopup", null, {
					overlay: {
						opacity: 50
					},
					closeIcon: true,
					titleBar: {content: BX.create('SPAN', {text:'<?=GetMessageJS('SOC_OPT_B24NET_TITLE')?>'})},
					content: BX.create('DIV', {
						style: {
							padding: '15px',
							textAlign: 'center',
							fontSize: '15px'
						},
						html: '<div id="b24net_in"><?=GetMessageJS('SOC_OPT_B24NET_SITE')?><br /><br /><input type="text" id="b24net_url" value="' +
							BX.util.htmlspecialchars(window.location.protocol+'//'+window.location.host) +
							'" style="width: 400px" /><br /><br />' +
							'<input type="button" onclick="networkRegister(BX(\'b24net_url\').value)" value="<?=GetMessageJS('SOC_OPT_B24NET_GET')?>">' +
							'</div><div id="b24net_out" style="display: none"></div>'
					})
				});
			}
			else
			{
				BX.hide(BX('b24net_out'));
				BX.show(BX('b24net_in'));
			}

			p.show();

			BX.defer(function(){
				BX.focus(BX('b24net_url'));
			})();
		}
	}
})();

<?
if(isset($_REQUEST['register_network'])):
?>
BX.ready(function(){networkRegister()});
<?
endif;
?>
</script>

<form method="post" name="socserv_settings" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($module_id)?>&amp;lang=<?=urlencode(LANGUAGE_ID)?>">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
<tr><td colspan="2">
<?
$aSiteTabs = array(array("DIV" => "opt_common", "TAB" => GetMessage("socserv_sett_common"), 'TITLE' => GetMessage("socserv_sett_common_title"), 'ONSELECT'=>"document.forms['socserv_settings'].siteTabControl_active_tab.value='opt_common'"));
foreach($arSites as $arSite)
	$aSiteTabs[] = array("DIV" => "opt_site_".$arSite["ID"], "TAB" => '['.$arSite["ID"].'] '.htmlspecialcharsbx($arSite["NAME"]), 'TITLE' => GetMessage("socserv_sett_site").' ['.$arSite["ID"].'] '.htmlspecialcharsbx($arSite["NAME"]), 'ONSELECT'=>"document.forms['socserv_settings'].siteTabControl_active_tab.value='opt_site_".$arSite["ID"]."'");

$siteTabControl = new CAdminViewTabControl("siteTabControl", $aSiteTabs);

$siteTabControl->Begin();

$arUseOnSites = unserialize(COption::GetOptionString("socialservices", "use_on_sites", ""));

foreach($arSiteList as $site):
	$suffix = ($site <> ''? '_bx_site_'.$site:'');
	$hash = COption::GetOptionString("socialservices", "twitter_search_hash".$suffix, "#b24");
	$twitHashInput = "<input type=\"text\" name=\"twitter_search_hash".$suffix."\" id=\"twitter_search_hash".$suffix."\" size=15 value=\"".htmlspecialcharsbx($hash)."\">";
	$siteTabControl->BeginNextTab();
?>
<?if($site <> ''):?>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="edit-table">
	<tr>
		<td width="50%" class="field-name"><label for="use_on_sites<?=$suffix?>"><?echo GetMessage("socserv_sett_site_apply")?></td>
		<td width="50%" style="padding-left:7px;">
			<input type="hidden" name="use_on_sites[<?=htmlspecialcharsbx($site)?>]" value="N">
			<input type="checkbox" name="use_on_sites[<?=htmlspecialcharsbx($site)?>]" value="Y"<?if($arUseOnSites[$site] == "Y") echo ' checked'?> id="use_on_sites<?=$suffix?>" onclick="BX('site_settings<?=$suffix?>').style.display=(this.checked? '':'none');">
		</td>
	</tr>
</table>
<?endif?>
<table cellpadding="0" cellspacing="0" border="0" class="edit-table" width="100%" id="site_settings<?=$suffix?>"<?if($site <> '' && $arUseOnSites[$site] <> "Y") echo ' style="display:none"';?>>
	<?if($site == ''):?>
	<?if(IsModuleInstalled("timeman")):?>
		<tr>
			<td> <?=GetMessage("soc_serv_send_activity");?> </td><td>
				<input type="checkbox" name="allow_send_user_activity" id="allow_send_user_activity" value="Y"
					<?if(COption::GetOptionString("socialservices", "allow_send_user_activity", "N") == 'Y') echo " checked"; elseif(COption::GetOptionString("socialservices", "allow_send_user_activity", false) === false)  echo " checked";?>>

			</td>
		</tr>
	<?endif;?>
	<tr>
		<td> <?=str_replace("#hash#", $twitHashInput, GetMessage("socserv_twit_to_buzz"))?> </td><td>
			<input type="checkbox" name="get_message_from_twitter" id="get_message_from_twitter" value="Y"
				<?if(COption::GetOptionString("socialservices", "get_message_from_twitter", "N") == 'Y') echo " checked"; elseif(COption::GetOptionString("socialservices", "get_message_from_twitter", false) === false)  echo " checked";?>>

		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?=GetMessage("soc_serv_opt_list_title")?></td>
	</tr>
	<?endif;?>
	<tr valign="top">

		<td width="50%" class="field-name"><?echo GetMessage("soc_serv_opt_list")?></td>
		<td width="50%">
			<table cellpadding="0" style="width:45%;" cellspacing="3" border="0" width="" class="padding-0">
<?
	$arServices = $oAuthManager->GetAuthServices($suffix);
	foreach($arServices as $id=>$service):
?>
				<tr>
					<td style="padding-top: 3px;">
						<input type="hidden" name="AUTH_SERVICES<?=$suffix?>[<?=htmlspecialcharsbx($id)?>]" value="N">
						<input type="checkbox" name="AUTH_SERVICES<?=$suffix?>[<?=htmlspecialcharsbx($id)?>]"
							id="AUTH_SERVICES<?=$suffix?><?=htmlspecialcharsbx($id)?>"
							value="Y"
							<?if($service["__active"] == true) echo " checked"?>
							<?if($service["DISABLED"] == true) echo " disabled"?>>
					</td>
					<td><div class="bx-ss-icon <?=htmlspecialcharsbx($service["ICON"])?>"></div></td>
					<td><label for="AUTH_SERVICES<?=$suffix?><?=htmlspecialcharsbx($id)?>"><?=htmlspecialcharsbx($service["NAME"])?></label></td>
					<td>&nbsp;</td>
					<td><a href="javascript:void(0)" onclick="MoveRowUp(this)"><img src="/bitrix/images/socialservices/up.gif" width="16" height="16" alt="<?echo GetMessage("soc_serv_opt_up")?>" border="0"></a></td>
					<td><a href="javascript:void(0)" onclick="MoveRowDown(this)"><img src="/bitrix/images/socialservices/down.gif" width="16" height="16" alt="<?echo GetMessage("soc_serv_opt_down")?>" border="0"></a></td>
				</tr>
<?
	endforeach;
?>
			</table>
		</td>
	</tr>
<?
	foreach($arOptions as $option)
	{
		if(!is_array($option))
		{
			$option = GetMessage("soc_serv_opt_settings_of", array("#SERVICE#" => $option));
		}
		else
		{
			$option[0] .= $suffix;
		}

		__AdmSettingsDrawRow($module_id, $option);
	}
?>
</table>
<?
endforeach; //foreach($arSiteList as $site)

$tabControl->BeginNextTab();

$groups = array();
$z = CGroup::GetList(($v1=""), ($v2=""), array("ACTIVE"=>"Y"/*, "ADMIN"=>"N", "ANONYMOUS"=>"N"*/));
while($zr = $z->Fetch())
{
	$ar = array();
	$ar["ID"] = intval($zr["ID"]);
	$ar["NAME"] = htmlspecialcharsbx($zr["NAME"]);
	$arGROUPS[] = $ar;

	$groups[$zr["ID"]] = $zr["NAME"]." [".$zr["ID"]."]";
}
?>
	<tr>
		<td><?echo GetMessage("SOC_OPT_MAIN_REG")?>: </td>
		<td>
<?
if (COption::GetOptionString("main", "new_user_registration", "N") == "Y"):
?>
			<span style="color: green;"><?echo GetMessage("SOC_OPT_MAIN_REG_Y")?></span>
<?
else:
?>
			<span style="color: red;"><?echo GetMessage("SOC_OPT_MAIN_REG_N")?></span>
<?
endif;
?>
		</td>
	</tr>
	<tr>
		<td><label for="allow_registration"><?echo GetMessage("SOC_OPT_SOC_REG")?>: </label></td>
		<td>
			<input type="hidden" name="allow_registration" value="N" />
			<input type="checkbox" name="allow_registration" id="allow_registration" value="Y"<?=$allowAuthorization ? ' checked="checked"' : ''?> />
		</td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("SOC_OPT_MAIN_DENY_AUTH")?>: </td>
		<td>
			<select name="group_deny_auth[]" size="5" multiple="multiple">
<?
foreach($groups as $groupId => $groupTitle)
{
?>
				<option value="<?=$groupId?>"<?=in_array($groupId,
					$groupDenyAuth)?' selected="selected"' : ''?>><?=Converter::getHtmlConverter()->encode($groupTitle)?></option>
<?
}
?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("SOC_OPT_MAIN_DENY_SPLIT")?>: </td>
		<td>
			<select name="group_deny_split[]" size="5" multiple="multiple">
<?
foreach($groups as $groupId => $groupTitle)
{
?>
				<option value="<?=$groupId?>"<?=in_array($groupId,
					$groupDenySplit)?' selected="selected"' : ''?>><?=Converter::getHtmlConverter()->encode($groupTitle)?></option>
<?
}
?>
			</select>
		</td>
	</tr>

<?
$siteTabControl->End();
?>
</td></tr>
<?$tabControl->Buttons();?>
	<input type="hidden" name="siteTabControl_active_tab" value="<?=htmlspecialcharsbx($_REQUEST["siteTabControl_active_tab"])?>">
<?if($_REQUEST["back_url_settings"] <> ''):?>
	<input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>">
<?endif?>
	<input type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
<?if($_REQUEST["back_url_settings"] <> ''):?>
	<input type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
	<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
<?endif?>
	<input type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" onclick="return confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
	<?=bitrix_sessid_post();?>
<?$tabControl->End();?>
</form>
