<?
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @global string $mid */
$module_id = "form";

use Bitrix\Main\Loader;

Loader::includeModule('form');
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/options.php");
$old_module_version = CForm::IsOldVersion();
$FORM_RIGHT = $APPLICATION->GetGroupRight($module_id);
if ($FORM_RIGHT>="R") :

if ($_SERVER['REQUEST_METHOD'] == "GET" && CForm::IsAdmin() && $RestoreDefaults <> '' && check_bitrix_sessid())
{
	COption::RemoveOption("form");
	$z = CGroup::GetList('', '', array("ACTIVE" => "Y", "ADMIN" => "N"));
	while($zr = $z->Fetch())
	{
		$APPLICATION->DelGroupRight($module_id, array($zr["ID"]));
	}
}

$arAllOptions = array(
	array("USE_HTML_EDIT", GetMessage("FORM_USE_HTML_EDIT"), array("checkbox", "Y")),
	array("SIMPLE", GetMessage("SIMPLE_MODE"), array("checkbox", "Y")),
	array("SHOW_TEMPLATE_PATH", GetMessage("FORM_SHOW_TEMPLATE_PATH"), array("text", 45)),
	array("SHOW_RESULT_TEMPLATE_PATH", GetMessage("FORM_SHOW_RESULT_TEMPLATE_PATH"), array("text", 45)),
	array("PRINT_RESULT_TEMPLATE_PATH", GetMessage("FORM_PRINT_RESULT_TEMPLATE_PATH"), array("text", 45)),
	array("EDIT_RESULT_TEMPLATE_PATH", GetMessage("FORM_EDIT_RESULT_TEMPLATE_PATH"), array("text", 45)),
	Array("RECORDS_LIMIT", GetMessage("FORM_RECORDS_LIMIT"), Array("text", 5)),
	Array("RESULTS_PAGEN", GetMessage("FORM_RESULTS_PAGEN"), Array("text", 5))
	);

if ($old_module_version!="Y")
{
	unset($arAllOptions[2]);
	unset($arAllOptions[3]);
	unset($arAllOptions[4]);
	unset($arAllOptions[5]);
}

if($_SERVER['REQUEST_METHOD'] == "POST" && $Update <> '' && CForm::IsAdmin() && check_bitrix_sessid())
{
	foreach($arAllOptions as $ar)
	{
		$name = $ar[0];
		$val = ${$name};
		if($ar[2][0] == "checkbox" && $val != "Y")
		{
			$val = "N";
		}
		COption::SetOptionString($module_id, $name, $val);
	}
	COption::SetOptionString("form", "FORM_DEFAULT_PERMISSION", $_POST['FORM_DEFAULT_PERMISSION']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_REQUEST['saveCrm'] && CForm::IsAdmin() && check_bitrix_sessid())
{
	$arAdditionalAuthData = array();
	$lastUpdated = '';
	if (is_array($_REQUEST['CRM']))
	{
		foreach ($_REQUEST['CRM'] as $ID => $arCrm)
		{
			if (is_array($arCrm))
			{
				$arCrm['ID'] = intval($ID);
				if ($arCrm['DELETED'] && $arCrm['ID'] > 0)
				{
					CFormCrm::Delete($arCrm['ID']);
				}
				else
				{
					$arCrmFields = array(
						'NAME' => trim($arCrm['NAME']),
						'ACTIVE' => 'Y', //$arCrm['ACTIVE'] == 'Y' ? 'Y' : 'N',
						'URL' => trim($arCrm['URL']),
					);

					if ($arCrm['ID'] <= 0)
					{
						$arCrm['ID'] = CFormCrm::Add($arCrmFields);
					}
					else
					{
						CFormCrm::Update($arCrm['ID'], $arCrmFields);
					}

					$lastUpdated = $arCrm['ID'];

					if ($arCrm['LOGIN'] <> '' && $arCrm['PASSWORD'] <> '')
					{
						$arAdditionalAuthData[$arCrm['ID']] = array(
							'LOGIN' => $arCrm['LOGIN'],
							'PASSWORD' => $arCrm['PASSWORD'],
						);
					}
				}
			}
		}
	}

	if ($_REQUEST['ajax'])
	{
		$arCRMServers = array();
		$dbRes = CFormCrm::GetList(array('NAME' => 'ASC', 'ID' => 'ASC'), array());
		while ($arServer = $dbRes->Fetch())
		{
			if (isset($arAdditionalAuthData[$arServer['ID']]))
				$arServer = array_merge($arServer, $arAdditionalAuthData[$arServer['ID']]);
			if ($lastUpdated == $arServer['ID'])
				$arServer['NEW'] = 'Y';

			$arCRMServers[] = $arServer;
		}

		$APPLICATION->RestartBuffer();
		echo CUtil::PhpToJSObject($arCRMServers);
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");
		exit();
	}
}

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "form_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
	array("DIV" => "edit_crm", "TAB" => GetMessage("FORM_TAB_CRM"), "ICON" => "form_settings", "TITLE" => GetMessage("FORM_TAB_CRM_TITLE")),
	array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "form_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
?>
<?
$tabControl->Begin();
?><form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&lang=<?=LANGUAGE_ID?>"><?=bitrix_sessid_post()?><?
$tabControl->BeginNextTab();
?>
	<?
	if (is_array($arAllOptions)):
		foreach($arAllOptions as $Option):
			$val = COption::GetOptionString($module_id, $Option[0]);
			$type = $Option[2];
	?>
	<tr>
		<td valign="top" width="50%"><?	if($type[0]=="checkbox")
							echo "<label for=\"".htmlspecialcharsbx($Option[0])."\">".$Option[1]."</label>";
						else
							echo $Option[1];?>
		</td>
		<td valign="top" nowrap width="50%"><?
			if($type[0]=="checkbox"):
				?><input type="checkbox" name="<?echo htmlspecialcharsbx($Option[0])?>" id="<?echo htmlspecialcharsbx($Option[0])?>" value="Y"<?if($val=="Y")echo" checked";?>><?
			elseif($type[0]=="text"):
				?><input type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialcharsbx($val)?>" name="<?echo htmlspecialcharsbx($Option[0])?>"><?
			elseif($type[0]=="textarea"):
				?><textarea rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo htmlspecialcharsbx($Option[0])?>"><?echo htmlspecialcharsbx($val)?></textarea><?
			endif;
			?></td>
	</tr>
	<?
		endforeach;
	endif;
	?>
	<tr>
		<td valign="top"><?=GetMessage("FORM_DEFAULT_PERMISSION");?></td>
		<td valign="top" nowrap><?
			$arr = CForm::GetPermissionList("N");
			$perm = COption::GetOptionString("form", "FORM_DEFAULT_PERMISSION");
			echo SelectBoxFromArray("FORM_DEFAULT_PERMISSION", $arr, $perm);
			?></td>
	</tr>
<?$tabControl->BeginNextTab();?>
<?
CJSCore::Init(array('popup', 'ajax'));

$arCRMServers = array();
$dbRes = CFormCrm::GetList(array('NAME' => 'ASC', 'ID' => 'ASC'), array());
while ($arServer = $dbRes->Fetch())
{
	$arCRMServers[] = $arServer;
}
?>
<tr class="heading">
	<td valign="top" align="center" colspan="2"><b><?=GetMessage('FORM_TAB_CRM_SECTION_TITLE')?></b></td>
</tr>
<tr>
	<td colspan="2">
		<style>
.form-crm-settings {width: 300px;}
.form-crm-settings table {width: 100%;}
.form-crm-settings table td {padding: 4px;}
.form-crm-settings, .form-crm-settings table {font-size: 11px;}
.form-crm-settings-hide-auth .form-crm-auth {display: none;}
.form-crm-settings input {width: 180px;}
.form-action-button {display: inline-block; height: 17px; width: 17px;}
.action-edit {background: scroll transparent url(/bitrix/images/form/options_buttons.gif) no-repeat 0 0; }
.action-delete {background: scroll transparent url(/bitrix/images/form/options_buttons.gif) no-repeat -29px 0; }
		</style>
		<table class="internal" cellspacing="0" cellpadding="0" border="0" align="center" width="80%" id="crm_table">
			<thead>
				<tr class="heading">
					<td><?=GetMessage('FORM_TAB_CRM_ROW_TITLE');?></td>
					<td><?=GetMessage('FORM_TAB_CRM_ROW_URL');?></td>
					<td><?=GetMessage('FORM_TAB_CRM_ROW_AUTH');?></td>
					<td width="34"></td>
				</tr>
			</thead>
			<tbody>
<?
if (count($arCRMServers) <= 0):
?>
				<tr>
					<td colspan="4" align="center"><?=GetMessage('FORM_TAB_CRM_NOTE');?> <a href="javascript:void(0)" onclick="CRM(); return false;"><?=GetMessage('FORM_TAB_CRM_NOTE_LINK');?></a></td>
				</tr>
<?
endif;
?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="4" align="left"><input type="button" onclick="CRM(); return false;" value="<?=htmlspecialcharsbx(GetMessage('FORM_TAB_CRM_ADD_BUTTON'));?>"></td>
				</tr>
			</tfoot>
		</table>
	</td>
</tr>
<script>
function _showPass(el)
{
	el.parentNode.replaceChild(BX.create('INPUT', {
		props: {
			type: el.type == 'text' ? 'password' : 'text',
			name: el.name,
			value: el.value
		}
	}), el);
}

function CRM(data)
{
	var popup_id = Math.random();

	data = data || {ID:'new_' + popup_id}

	if (data && data.URL)
	{
		var r = /^(http|https):\/\/([^\/]+)(.*)$/i,
			res = r.exec(data.URL);
		if (!res)
		{
			var proto = data.URL.match(/\.bitrix24\./) ? 'https' : 'http';

			data.URL = proto + '://' + data.URL;
			res = r.exec(data.URL);
		}

		if (res)
		{
			data.URL_SERVER = res[1]+'://'+res[2];
			data.URL_PATH = res[3];
		}
	}

	if (!data.AUTH_HASH)
	{
		var content = '<div class="form-crm-settings"><form name="form_'+popup_id+'"><table cellpadding="0" cellspacing="2" border="0"><tr><td align="right"><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_ROW_TITLE'))?>:</td><td><input type="text" name="NAME" value="'+BX.util.htmlspecialchars(data.NAME||'')+'"></td></tr><tr><td align="right"><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_FORM_URL_SERVER'))?>:</td><td><input type="text" name="URL_SERVER" value="'+BX.util.htmlspecialchars(data.URL_SERVER||'')+'"></td></tr><tr><td align="right"><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_FORM_URL_PATH'))?>:</td><td><input type="text" name="URL_PATH" value="'+BX.util.htmlspecialchars(data.URL_PATH||'<?=FORM_CRM_DEFAULT_PATH?>')+'"></td></tr><tr><td colspan="2" align="center"><b><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_ROW_AUTH'))?></b></td></tr><tr><td align="right"><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_ROW_AUTH_LOGIN'))?>:</td><td><input type="text" name="LOGIN" value="'+BX.util.htmlspecialchars(data.LOGIN||'')+'"></td></tr><tr><td align="right"><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_ROW_AUTH_PASSWORD'))?>:</td><td><input type="password" name="PASSWORD" value="'+BX.util.htmlspecialchars(data.PASSWORD||'')+'"></td></tr><tr><td></td><td><a href="javascript:void(0)" onclick="_showPass(document.forms[\'form_'+popup_id+'\'].PASSWORD); BX.hide(this.parentNode);"><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_ROW_AUTH_PASSWORD_SHOW'))?></a></td></tr></table></form></div>';
	}
	else
	{
		var content = '<div class="form-crm-settings form-crm-settings-hide-auth" id="popup_cont_'+popup_id+'"><form name="form_'+popup_id+'"><table cellpadding="0" cellspacing="2" border="0"><tr><td align="right"><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_ROW_TITLE'))?>:</td><td><input type="text" name="NAME" value="'+BX.util.htmlspecialchars(data.NAME||'')+'"></td></tr><tr><td align="right"><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_FORM_URL_SERVER'))?>:</td><td><input type="text" name="URL_SERVER" value="'+BX.util.htmlspecialchars(data.URL_SERVER||'')+'"></td></tr><tr><td align="right"><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_FORM_URL_PATH'))?>:</td><td><input type="text" name="URL_PATH" value="'+BX.util.htmlspecialchars(data.URL_PATH||'<?=FORM_CRM_DEFAULT_PATH?>')+'"></td></tr><tr class="form-crm-auth"><td colspan="2" align="center"><b><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_ROW_AUTH'))?></b></td></tr><tr class="form-crm-auth"><td align="right"><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_ROW_AUTH_LOGIN'))?>:</td><td><input type="text" name="LOGIN" value="'+BX.util.htmlspecialchars(data.LOGIN||'')+'"></td></tr><tr class="form-crm-auth"><td align="right"><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_ROW_AUTH_PASSWORD'))?>:</td><td><input type="password" name="PASSWORD" value="'+BX.util.htmlspecialchars(data.PASSWORD||'')+'"></td></tr><tr><td align="right"></td><td><a href="javascript:void(0)" onclick="_showPass(document.forms[\'form_'+popup_id+'\'].PASSWORD);BX.hide(this);" class="form-crm-auth"><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_ROW_AUTH_PASSWORD_SHOW'))?></a><a href="javascript:void(0)" onclick="BX.removeClass(BX(\'popup_cont_'+popup_id + '\'), \'form-crm-settings-hide-auth\'); BX.hide(this);"><?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_ROW_AUTH_SHOW'))?></a></td></tr></table></form></div>';
	}

	var wnd = new BX.PopupWindow('popup_' + popup_id, window, {
		titleBar: {content: BX.create('SPAN', {text: !isNaN(parseInt(data.ID)) ? '<?=CUtil::JSEscape(GetMessage('FORM_CRM_TITLEBAR_EDIT'))?>' : '<?=CUtil::JSEscape(GetMessage('FORM_CRM_TITLEBAR_NEW'))?>'})},
		draggable: true,
		autoHide: false,
		closeIcon: true,
		closeByEsc: true,
		content: content,
		buttons: [
			new BX.PopupWindowButton({
				text : BX.message('JS_CORE_WINDOW_SAVE'),
				className : "popup-window-button-accept",
				events : {
					click : function(){CRMSave(wnd, data, document.forms['form_'+popup_id])}
				}
			}),
			new BX.PopupWindowButtonLink({
				text : BX.message('JS_CORE_WINDOW_CANCEL'),
				className : "popup-window-button-link-cancel",
				events : {
					click : function() {wnd.close()}
				}
			})
		]
	});

	wnd.show();
}

function CRMRedraw(data)
{
	var table = BX('crm_table').tBodies[0];

	while (table.rows.length > 0)
		table.removeChild(table.rows[0]);

	for (var i = 0; i < data.length; i++)
	{
		var tr = table.insertRow(-1);
		tr.id = 'crm_row_' + data[i].ID;

		tr.insertCell(-1).appendChild(document.createTextNode(data[i].NAME||'<?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_UNTITLED'))?>'));
		tr.insertCell(-1).appendChild(document.createTextNode(data[i].URL));

		var authCell = tr.insertCell(-1);
		authCell.id = 'crm_auth_cell_' + data[i].ID;
		if (!!data[i].LOGIN && !!data[i].PASSWORD)
		{
			authCell.appendChild(document.createTextNode('<?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_CHECK_LOADING'))?>'));
			BX.ajax.loadJSON('/bitrix/admin/form_crm.php?action=check&reload=Y&ID=' + BX.util.urlencode(data[i].ID) + '&LOGIN=' + BX.util.urlencode(data[i].LOGIN) + '&PASSWORD=' + BX.util.urlencode(data[i].PASSWORD) + '&<?=bitrix_sessid_get()?>', BX.delegate(function(data) {
					BX.cleanNode(this);
					this.innerHTML = (data && data.result == 'ok') ? 'OK' : ('<?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_CHECK_ERROR'))?>'.replace('#ERROR#', data.error||''));
				}, authCell));
		}
		else if (data[i].AUTH_HASH)
		{
			authCell.appendChild(BX.create('A', {
				props: {BXCRMID: data[i].ID},
				attrs: {href: 'javascript: void(0)'},
				events: {click: function() {CRMCheck(this.BXCRMID)}},
				text: '<?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_CHECK'))?>'
			}));
		}
		else
		{
			authCell.appendChild(document.createTextNode('<?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_CHECK_NO'))?>'));
		}

		BX.adjust(tr.insertCell(-1), {
			children: [
				BX.create('A', {
					props: {
						className: 'form-action-button action-edit',
						title: '<?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_EDIT'))?>'
					},

					attrs: {href: 'javascript: void(0)'},
					events: {click: BX.delegate(function() {CRM(this);}, data[i])}
				}),
				BX.create('A', {
					props: {
						BXCRMID: data[i].ID,
						className: 'form-action-button action-delete',
						title: '<?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_DELETE'))?>'
					},
					attrs: {href: 'javascript: void(0)'},
					events: {click: function() {CRMDelete(this.BXCRMID);}}
				})
			]
		});
	}
}

function CRMSave(wnd, data_old, form)
{
	var URL = form.URL_SERVER.value;
	if (URL.substring(URL.length-1,1) != '/' && form.URL_PATH.value.substring(0,1) != '/')
		URL += '/';
	URL += form.URL_PATH.value;

	var flds = ['ID', 'NAME', 'URL', 'ACTIVE','LOGIN','PASSWORD'],
		data = {
			ID: data_old.ID,
			NAME: form.NAME.value,
			URL:  URL,
			ACTIVE: 'Y', //form.ACTIVE.checked ? 'Y' : 'N',
			LOGIN: !!form.LOGIN ? form.LOGIN.value : '',
			PASSWORD: !!form.PASSWORD ? form.PASSWORD.value : ''
		};


	var res = false, r = /^(http|https):\/\/([^\/]+)(.*)$/i;
	if (data.URL)
	{
		res = r.test(data.URL);
		if (!res)
		{
			var proto = data.URL.match(/\.bitrix24\./) ? 'https' : 'http';
			data.URL = proto + '://' + data.URL;
			res = r.test(data.URL);
		}
	}

	if (!res)
	{
		alert('<?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_WRONG_URL'))?>');
	}
	else
	{
		var query_str = '';

		for (var i = 0; i < flds.length; i++)
		{
			query_str += (query_str == '' ? '' : '&') + 'CRM['+data.ID+']['+flds[i]+']='+BX.util.urlencode(data[flds[i]]);
		}

		BX.ajax({
			method: 'POST',
			dataType: 'json',
			url: '<?=CUtil::JSEscape($APPLICATION->GetCurPageParam('saveCrm=Y&ajax=Y&'.bitrix_sessid_get()))?>',
			data: query_str,
			onsuccess: CRMRedraw
		});

		if (!!wnd)
			wnd.close();
	}
}

function CRMDelete(ID)
{
	if (confirm('<?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_CONFIRM'))?>'))
	{
		BX.ajax({
			method: 'POST',
			dataType: 'json',
			url: '<?=CUtil::JSEscape($APPLICATION->GetCurPageParam('saveCrm=Y&ajax=Y&'.bitrix_sessid_get()))?>',
			data: 'CRM['+ID+'][DELETED]=Y',
			onsuccess: CRMRedraw
		});
	}
}

function CRMCheck(ID)
{
	var c = BX('crm_auth_cell_' + ID);
	if (c)
	{
		c.innerHTML = '<?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_CHECK_LOADING'))?>';
	}

	BX.ajax.loadJSON('/bitrix/admin/form_crm.php?action=check&ID='+ID+'&reload=Y&<?=bitrix_sessid_get();?>', function(res)
	{
		if (!!res)
		{
			if (res.result == 'ok')
			{
				BX('crm_auth_cell_' + ID).innerHTML = 'OK';
			}
			else
			{
				BX('crm_auth_cell_' + ID).innerHTML = '<?=CUtil::JSEscape(GetMessage('FORM_TAB_CRM_CHECK_ERROR'))?>'.replace('#ERROR#', res.error||'');
			}
		}
	});
}
<?
if (count($arCRMServers) > 0):
?>
BX.ready(function() {
	BX.ajax({
		method: 'POST',
		dataType: 'json',
		url: '<?=CUtil::JSEscape($APPLICATION->GetCurPageParam('saveCrm=Y&ajax=Y&'.bitrix_sessid_get()))?>',
		onsuccess: CRMRedraw
	});
});
<?
endif;
?>
</script>
<?$tabControl->BeginNextTab();?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
<?$tabControl->Buttons();?>
<script>
function RestoreDefaults()
{
	if(confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
		window.location = "<?echo $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?=LANGUAGE_ID?>&mid=<?echo urlencode($mid)?>&<?=bitrix_sessid_get()?>";
}
</script>
<input <?if ($FORM_RIGHT<"W") echo "disabled" ?> type="submit" name="Update" value="<?=GetMessage("FORM_SAVE")?>">
<input type="hidden" name="Update" value="Y">
<input type="reset" name="reset" value="<?=GetMessage("FORM_RESET")?>">
<input <?if ($FORM_RIGHT<"W") echo "disabled" ?> type="button" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="RestoreDefaults();" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
<?$tabControl->End();?>
</form>
<?endif;