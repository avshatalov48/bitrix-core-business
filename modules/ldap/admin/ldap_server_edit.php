<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2016 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/ldap/prolog.php");

$MOD_RIGHT = $APPLICATION->GetGroupRight("ldap");
if($MOD_RIGHT<"R") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
IncludeModuleLangFile(__FILE__);

if (!extension_loaded('ldap'))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage("LDAP_EXT_NO_LOADED"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/ldap/include.php");

$err_mess = "File: ".__FILE__."<br>Line: ";

$message = null;
$ID = intval($ID);
$defaultMaxPageSizeAD = 1000;
$arSyncFields =	CLdapUtil::GetSynFields();

$bPostback = false;

$arUserFieldMap = Array();

if(is_array($_REQUEST["MAP"]))
{
	foreach($_REQUEST["MAP"] as $v)
	{
		if(strlen($v["USER"])>0 && is_array($arSyncFields[$v["USER"]]) && strlen($v["LDAP"])>0)
		{
			$arUserFieldMap[$v["USER"]] = $v["LDAP"];
		}
	}
}


if($_SERVER['REQUEST_METHOD'] == "POST")
{
	// add not imported groups from select box
	$noimportGroups = array();
	if (array_key_exists("LDAP_NOIMP_GROUPS",$_REQUEST))
	{
		// make a hash map of groups to determine later easily which groups are selected in the select box
		// it also eliminates duplicating groups
		foreach ($_REQUEST['LDAP_NOIMP_GROUPS'] as $ldapGroupId)
			$noimportGroups[md5($ldapGroupId)] = $ldapGroupId;
	}
}


if($_SERVER['REQUEST_METHOD'] == "POST" && (strlen($save)>0 || strlen($apply)>0) && $MOD_RIGHT=="W" && check_bitrix_sessid())
{
	$bPostback = true;
	$arGroups = false;

	// read AD to portal group correspondence
	if(is_array($_REQUEST['LDAP_GROUP']))
	{
		if (!$arGroups)
		{
			$arGroups = Array();
		}
		foreach($_REQUEST['LDAP_GROUP'] as $t_id=>$arGroup)
		{
			if(strlen($arGroup['LDAP_GROUP_ID'])>0 && $arGroup['GROUP_ID']>0 && $arGroup['DEL_GROUP']!='Y')
			{
				if ($arGroup['GROUP_ID'] == 2)
					continue;

				$arGroups[] = $arGroup;
			}
		}
	}

	if (array_key_exists("LDAP_NOIMP_GROUPS",$_REQUEST))
	{
		if (!$arGroups)
			$arGroups = Array();

		// add groups, which import is forbidden, to common mapping with an id of -1
		foreach ($noimportGroups as $ldapGroupId)
			$arGroups[] = array("LDAP_GROUP_ID"=>$ldapGroupId,"GROUP_ID"=>-1);
	}

	$arFields = Array(
		"NAME"	=>	$_REQUEST['NAME'],
		"DESCRIPTION"	=>	$_REQUEST['DESCRIPTION'],
		"CODE"	=>	$_REQUEST['CODE'],
		"SERVER"	=>	$_REQUEST['SERVER'],
		"PORT"	=>	$_REQUEST['PORT'],
		"CONVERT_UTF8"	=>	$_REQUEST['CONVERT_UTF8'],
		"ADMIN_LOGIN"	=>	$_REQUEST['ADMIN_LOGIN'],
		"ACTIVE"		=>	$_REQUEST['ACTIVE'],
		"ADMIN_PASSWORD"	=>	$_REQUEST['ADMIN_PASSWORD'],
		"BASE_DN"	=>	$_REQUEST['BASE_DN'],
		"GROUP_FILTER"	=>	$_REQUEST['GROUP_FILTER'],
		"GROUP_ID_ATTR"	=>	$_REQUEST['GROUP_ID_ATTR'],
		"GROUP_NAME_ATTR"	=>	$_REQUEST['GROUP_NAME_ATTR'],
		"GROUP_MEMBERS_ATTR"	=>	$_REQUEST['GROUP_MEMBERS_ATTR'],
		"USER_FILTER"	=>	$_REQUEST['USER_FILTER'],
		"USER_ID_ATTR"	=>	$_REQUEST['USER_ID_ATTR'],
		"USER_NAME_ATTR"	=>	$_REQUEST['USER_NAME_ATTR'],
		"USER_LAST_NAME_ATTR"	=>	$_REQUEST['USER_LAST_NAME_ATTR'],
		"USER_EMAIL_ATTR"	=>	$_REQUEST['USER_EMAIL_ATTR'],
		"USER_GROUP_ATTR"	=>	$_REQUEST['USER_GROUP_ATTR'],
		"USER_GROUP_ACCESSORY"	=>	$_REQUEST['USER_GROUP_ACCESSORY'],
		"SYNC_PERIOD"	=> 	$_REQUEST['SYNC_PERIOD'],
		"SYNC"	=>	$_REQUEST['SYNC'],
		"SYNC_ATTR"	=>	$_REQUEST['SYNC_ATTR'],
		"USER_DEPARTMENT_ATTR"	=>	$_REQUEST['USER_DEPARTMENT_ATTR'],
		"USER_MANAGER_ATTR"	=>	$_REQUEST['USER_MANAGER_ATTR'],
		"IMPORT_STRUCT"	=>	$_REQUEST['IMPORT_STRUCT'],
		"STRUCT_HAVE_DEFAULT"	=>	$_REQUEST['STRUCT_HAVE_DEFAULT'],
		"ROOT_DEPARTMENT"	=>	$_REQUEST['ROOT_DEPARTMENT'],
		"DEFAULT_DEPARTMENT_NAME"	=>	$_REQUEST['DEFAULT_DEPARTMENT_NAME'],
		"FIELD_MAP"	=>	$arUserFieldMap,
		"MAX_PAGE_SIZE" => $_REQUEST['MAX_PAGE_SIZE'],
		"SYNC_USER_ADD" => $_REQUEST['SYNC_USER_ADD'],
		"CONNECTION_TYPE" => $_REQUEST['CONNECTION_TYPE']
	);

	if(is_array($arGroups))
		$arFields['GROUPS'] = $arGroups;

	// apply form to server config
	if($ID>0)
		$res = CLdapServer::Update($ID, $arFields);
	else
	{
		$ID = CLdapServer::Add($arFields);
		$res = ($ID>0);
	}

	if($res)
	{
		if(strlen($save)>0)
		{
			if(substr($_REQUEST['back_url'], 0, 1)=='/')
				LocalRedirect($_REQUEST['back_url'].'&ldapServer='.$ID);
			else
				LocalRedirect("ldap_server_admin.php?lang=".LANG);
		}
		else
			LocalRedirect($APPLICATION->GetCurPage()."?lang=".LANG."&ID=".$ID."&tabControl_active_tab=".urlencode($tabControl_active_tab));
	}
	else
	{
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("LDAP_SAVING_ERROR"), $e);
	}
}

if(strlen($_REQUEST['check_server'])>0 || strlen($_REQUEST['refresh_groups'])>0)
	$bPostback = true;

$ldp = false;
$str_ACTIVE="Y";
$str_IMPORT_STRUCT="Y";
$str_STRUCT_HAVE_DEFAULT = "Y";
$str_DEFAULT_DEPARTMENT_NAME = (GetMessage('LDAP_DEFAULT_DEPARTMENT')!=''? GetMessage('LDAP_DEFAULT_DEPARTMENT') : 'My company');
$str_PORT="389";

$arGroups = Array();

ClearVars("str_");
if($ID>0)
{
	$ld = CLdapServer::GetByID($ID);

	if(!($arFields = $ld->ExtractFields("str_")))
	{
		$ID=0;
	}
	else
	{
		if($MOD_RIGHT<"W")
			$str_ADMIN_PASSWORD = "";
		$ldp = CLDAP::Connect(
			Array(
				"SERVER"		=>	$arFields['SERVER'],
				"PORT"			=>	$arFields['PORT'],
				"ADMIN_LOGIN"	=>	$arFields['ADMIN_LOGIN'],
				"ADMIN_PASSWORD"=>	$arFields['ADMIN_PASSWORD'],
				"BASE_DN"		=>	$arFields['BASE_DN'],
				"GROUP_FILTER"	=>	$arFields['GROUP_FILTER'],
				"GROUP_ID_ATTR"	=>	$arFields['GROUP_ID_ATTR'],
				"GROUP_NAME_ATTR"=>	$arFields['GROUP_NAME_ATTR'],
				"GROUP_MEMBERS_ATTR"=>	$arFields['GROUP_MEMBERS_ATTR'],
				"CONVERT_UTF8"	=>	$arFields['CONVERT_UTF8'],
				"USER_FILTER"	=>	$arFields['USER_FILTER'],
				"USER_GROUP_ATTR"=>	$arFields['USER_GROUP_ATTR'],
				"USER_GROUP_ACCESSORY"=>	$arFields['USER_GROUP_ACCESSORY'],
				"USER_DEPARTMENT_ATTR"	=>	$arFields['USER_DEPARTMENT_ATTR'],
				"USER_MANAGER_ATTR"	=>	$arFields['USER_MANAGER_ATTR'],
				"MAX_PAGE_SIZE"	=>	$arFields['MAX_PAGE_SIZE'],
				"CONNECTION_TYPE" => $arFields['CONNECTION_TYPE'],
			)
		);

		$db_groups = CLdapServer::GetGroupMap($ID);

		while($arGroup = $db_groups->Fetch())
			$arGroups[$arGroup['GROUP_ID'].' '.md5($arGroup['LDAP_GROUP_ID'])] = $arGroup;

		if (!isset($noimportGroups))
		{
			$noimportGroups = Array();
			$db_groups = CLdapServer::GetGroupBan($ID);

			while($arGroup = $db_groups->Fetch())
				$noimportGroups[md5($arGroup['LDAP_GROUP_ID'])] = $arGroup['LDAP_GROUP_ID'];
		}

		//$ADMIN_PASSWORD = $arFields['ADMIN_PASSWORD'];
		if(!$bPostback)
			$arUserFieldMap = $arFields["FIELD_MAP"];
	}
}

//if(strlen($Add)<=0)
$DB->InitTableVarsForEdit("b_ldap_server", "", "str_");

if(is_array($_REQUEST['LDAP_GROUP']))
{
	foreach($_REQUEST['LDAP_GROUP'] as $t_id=>$arGroup)
	{
		if(strlen($arGroup['LDAP_GROUP_ID'])>0 || $arGroup['GROUP_ID']>0)
			$arGroups[$t_id] = $arGroup;
	}
}

$arGroups[md5(uniqid(rand(), true))] = Array();
$arGroups[md5(uniqid(rand(), true))] = Array();
$arGroups[md5(uniqid(rand(), true))] = Array();

$sDocTitle = ($ID>0) ? GetMessage("LDAP_EDIT_TITLE", array('#ID#' => $ID)) : GetMessage("LDAP_EDIT_TITLE_NEW");
$APPLICATION->SetTitle($sDocTitle);

/***************************************************************************
									HTML form
****************************************************************************/

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
$aMenu = array(
	array(
		"ICON" => "btn_list",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_LIST"),
		"TITLE"=>GetMessage("LDAP_EDIT_LIST"),
		"LINK"=>"ldap_server_admin.php?lang=".LANG
	)
);

if($ID>0)
{
	$aMenu[] = array(
		"ICON" => "btn_new",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_CREATE"),
		"TITLE"=>GetMessage("LDAP_EDIT_NEW"),
		"LINK"=>"ldap_server_edit.php?lang=".LANG
	);

	if ($MOD_RIGHT=="W")
	{
		$aMenu[] = array(
			"ICON" => "btn_delete",
			"TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"),
			"TITLE"=>GetMessage("LDAP_EDIT_DEL"),
			"LINK"=>"javascript:if(confirm('".GetMessage("LDAP_EDIT_DEL_CONF")."'))window.location='ldap_server_admin.php?action=delete&ID=".$ID."&lang=".LANG."&".bitrix_sessid_get()."';",
		);
	}

}



$context = new CAdminContextMenu($aMenu);
$context->Show();

if(strlen($SERVER)>0)
{
	$ldp = false;

	$ldp = CLDAP::Connect(
		Array(
			"SERVER"		=>	$SERVER,
			"PORT"			=>	$PORT,
			"BASE_DN"		=>	$BASE_DN,
			"ADMIN_LOGIN"	=>	$ADMIN_LOGIN,
			"ADMIN_PASSWORD"=>	$ADMIN_PASSWORD,
			"CONVERT_UTF8"	=>	$CONVERT_UTF8,
			"GROUP_FILTER"	=>	$GROUP_FILTER,
			"GROUP_ID_ATTR"	=>	$GROUP_ID_ATTR,
			"GROUP_NAME_ATTR"=>	$GROUP_NAME_ATTR,
			"GROUP_MEMBERS_ATTR"=>	$GROUP_MEMBERS_ATTR,
			"USER_FILTER"	=>	$USER_FILTER,
			"USER_GROUP_ATTR"=>	$USER_GROUP_ATTR,
			"USER_GROUP_ACCESSORY"=>	$USER_GROUP_ACCESSORY,
			"MAX_PAGE_SIZE"	=>	$MAX_PAGE_SIZE,
			//"USER_DEPARTMENT_ATTR"	=>	$USER_DEPARTMENT_ATTR,
			//"USER_MANAGER_ATTR"	=>	$USER_MANAGER_ATTR,
			"CONNECTION_TYPE" => $CONNECTION_TYPE
		)
	);
}

if($bPostback)
{
	if(!$ldp)
	{
		$errorDetails = '';

		if($e = $APPLICATION->GetException())
			$errorDetails = $e->GetString();

		$message = new CAdminMessage(
			Array(
				"MESSAGE" => GetMessage("LDAP_ERROR"),
				"DETAILS" => GetMessage("LDAP_EDIT_ERR_CON")."\n".$errorDetails,
				"TYPE"=>"ERROR"
			)
		);
	}
	elseif(!$ldp->BindAdmin())
	{
		$errorDetails = '';

		if($e = $APPLICATION->GetException())
			$errorDetails = $e->GetString();

		$message = new CAdminMessage(
			Array(
				"MESSAGE" => GetMessage("LDAP_ERROR"),
				"DETAILS" => GetMessage("LDAP_EDIT_ERR_AUT")."\n".$errorDetails,
				"TYPE"=>"ERROR"
			)
		);
	}
	elseif(strlen($_REQUEST['refresh_groups'])<=0)
	{
		$message = new CAdminMessage(Array("MESSAGE" => GetMessage("LDAP_EDIT_OK_CON"), "TYPE"=>"OK"));
	}
}


$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("LDAP_EDIT_TAB1"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("LDAP_EDIT_TAB1_TITLE")),
	array("DIV" => "edit2", "TAB" => GetMessage("LDAP_EDIT_TAB11"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("LDAP_EDIT_TAB11_TITLE")),
	array("DIV" => "edit3", "TAB" => GetMessage("LDAP_EDIT_TAB2"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("LDAP_EDIT_USER_MAP")),
	array("DIV" => "edit4", "TAB" => GetMessage("LDAP_EDIT_TAB4"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("LDAP_EDIT_TAB4_TITLE")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if ($message)
	echo $message->Show();
else
{
	if($ID<=0 && !$bPostback)
	{
		?>
		<script type="text/javascript">
			setTimeout("OutLDSDefParams('AD')", 10);
		</script>
		<?
	}
}
?>

<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANG?>&ID=<?=$ID?>" name="form1">
<?=bitrix_sessid_post()?>
<?echo GetFilterHiddens("find_");?>
<?if($_REQUEST['back_url']):?>
<input type="hidden" name="back_url" value="<?=htmlspecialcharsbx($_REQUEST['back_url'])?>">
<?endif?>
<?$tabControl->Begin();?>

<?$tabControl->BeginNextTab();?>
	<?if($ID>0):?>
	<tr>
		<td><?echo GetMessage("LDAP_EDIT_ID")?></td>
		<td><?echo $str_ID?></td>
	</tr>
	<?endif?>
	<?if(strlen($str_TIMESTAMP_X) > 0):?>
	<tr>
		<td><?echo GetMessage("LDAP_EDIT_TSTAMP")?></td>
		<td><?echo $str_TIMESTAMP_X?></td>
	</tr>
	<?endif;?>
	<tr>
		<td width="40%"><?echo GetMessage("LDAP_EDIT_ACT")?></td>
		<td width="60%"><input type="hidden" name="ACTIVE" value="N"><input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE=="Y")echo " checked"?>></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("LDAP_EDIT_NAME")?></td>
		<td><input type="text" name="NAME" size="53" maxlength="255" value="<?=$str_NAME?>"></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("LDAP_EDIT_DESC")?></td>
		<td><textarea class="typearea" name="DESCRIPTION" cols="40" rows="5"><?echo $str_DESCRIPTION?></textarea></td>
	</tr>
	<tr>
		<td>
		<?=GetMessage("LDAP_EDIT_CODE")?>
		</td>
		<td><input type="text" name="CODE" size="42" maxlength="255" value="<?=$str_CODE?>"></td>
	</tr>
	<tr>
		<td>
		<?=GetMessage("LDAP_EDIT_CODE_USER")?></td>
		<td>
		<?
		$ntlmVarname = COption::GetOptionString('ldap', 'ntlm_varname', 'REMOTE_USER');
		if (array_key_exists($ntlmVarname,$_SERVER) && trim($_SERVER[$ntlmVarname])!='')
			echo htmlspecialcharsbx($_SERVER[$ntlmVarname]);
		else
			echo GetMessage("LDAP_EDIT_CODE_ABS");
		?>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("LDAP_EDIT_SERV_PORT")?></td>
		<td>
			<input type="text" name="SERVER" id="adm-ldap-srv-edit-server" size="42" maxlength="255" value="<?=$str_SERVER?>">:
			<input type="text" name="PORT" id="adm-ldap-srv-edit-port" size="4" maxlength="5" value="<?=$str_PORT?>">
		</td>
	</tr>

	<tr>
		<td><?=GetMessage("LDAP_EDIT_CONNECTION_TYPE")?></td>
		<td>
			<select name="CONNECTION_TYPE" id="adm-ldap-srv-edit-conn-type" onchange="onLdapSrvEditConnType();">
				<option value="<?=CLDAP::CONNECTION_TYPE_SIMPLE?>"<?=intval($str_CONNECTION_TYPE) == CLDAP::CONNECTION_TYPE_SIMPLE ? ' selected' : ''?>><?=GetMessage("LDAP_EDIT_NO_CRYPT")?></option>
				<option value="<?=CLDAP::CONNECTION_TYPE_SSL?>"<?=intval($str_CONNECTION_TYPE) == CLDAP::CONNECTION_TYPE_SSL ? ' selected' : ''?>>SSL</option>
				<option value="<?=CLDAP::CONNECTION_TYPE_TLS?>"<?=intval($str_CONNECTION_TYPE) == CLDAP::CONNECTION_TYPE_TLS ? ' selected' : ''?>>TLS</option>
			</select>
			<script type="text/javascript">
				function onLdapSrvEditConnType()
				{
					var type = BX('adm-ldap-srv-edit-conn-type'),
						typeValue = 0;

					if(type.selectedIndex != -1)
					{
						typeValue = type.options[type.selectedIndex].value;
					}

					var server = BX('adm-ldap-srv-edit-server'),
						port = BX('adm-ldap-srv-edit-port');

					if(typeValue == '<?=CLDAP::CONNECTION_TYPE_SSL?>')
					{
						port.value = 636;
						server.value = setLdapScheeme(server.value, 'ldaps');
					}
					else
					{
						port.value = 389;
						server.value = setLdapScheeme(server.value, 'ldap');
					}
				}

				function setLdapScheeme(address, scheeme)
				{
					var result;

					if(address.search('://') != -1)
						result = address.replace(/(.*):\/\/(.*)/, scheeme+'://$2');
					else
						result = scheeme+'://'+address;

					return result;
				}
			</script>
		</td>
	</tr>

	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("LDAP_EDIT_ADM_LOGIN")?></td>
		<td><input type="text" name="ADMIN_LOGIN" size="53" maxlength="255" value="<?=$str_ADMIN_LOGIN?>"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("LDAP_EDIT_ADM_PASS")?></td>
		<td><input type="password" name="ADMIN_PASSWORD" size="53" maxlength="255" value="<?=$str_ADMIN_PASSWORD?>"></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" name="check_server" value="<?echo GetMessage("LDAP_EDIT_CHECK")?>" class="button"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("LDAP_EDIT_BASE_DN")?></td>
		<td>
			<?
			if($ldp):
				$ar_rootdse = $ldp->RootDSE();
				$rootDSEcount = count($ar_rootdse);
				if($rootDSEcount>0):
				?>
					<select name="" onChange="document.getElementById('BASE_DN').value = this.value">
						<option value=""> </option>
						<?for($i=0; $i<$rootDSEcount; $i++):?>
							<option value="<?=htmlspecialcharsbx($ar_rootdse[$i])?>" title="<?=htmlspecialcharsbx($ar_rootdse[$i])?>"><?=htmlspecialcharsbx($ar_rootdse[$i])?></option>
						<?endfor;?>
					</select>
				<?
				endif;
			endif; //if($ldp)
			?>
			<input type="text" name="BASE_DN" id="BASE_DN" size="53" maxlength="255" value="<?=$str_BASE_DN?>">
		</td>
	</tr>
	<?if(CLdapUtil::isLdapPaginationAviable()):?>
		<tr>
			<td><?echo GetMessage("LDAP_EDIT_MAX_PAGE_SIZE")?></td>
			<td>
				<input type="text" name="MAX_PAGE_SIZE" id="MAX_PAGE_SIZE" size="15" maxlength="255" value="<?=(intval($str_MAX_PAGE_SIZE) > 0 ? intval($str_MAX_PAGE_SIZE) : $defaultMaxPageSizeAD)?>">
			</td>
		</tr>
	<?endif;?>
<?$tabControl->BeginNextTab();?>
	<script type="text/javascript">
		function OutLDSDefParams(t)
		{
			if(t=='AD')
			{
				document.getElementById('GROUP_FILTER').value = '(objectCategory=group)';
				document.getElementById('GROUP_ID_ATTR').value = 'dn';
				document.getElementById('CONVERT_UTF8').checked = true;
				document.getElementById('GROUP_NAME_ATTR').value = 'sAMAccountName';
				document.getElementById('GROUP_MEMBERS_ATTR').value = 'member';
				document.getElementById('USER_FILTER').value = '(&(objectClass=user)(objectCategory=PERSON))';
				document.getElementById('USER_ID_ATTR').value = 'samaccountname';
				//document.getElementById('USER_NAME_ATTR').value = 'givenName';
				//document.getElementById('USER_LAST_NAME_ATTR').value = 'sn';
				//document.getElementById('USER_EMAIL_ATTR').value = 'mail';
				document.getElementById('USER_GROUP_ACCESSORY').checked = false;
				document.getElementById('USER_GROUP_ATTR').value = 'memberof';
				document.getElementById('USER_DEPARTMENT_ATTR').value = 'department';
				document.getElementById('USER_MANAGER_ATTR').value = 'manager';
				document.getElementById('SYNC_ATTR').value = 'whenChanged';
				__UFAdd("ACTIVE");
				__UFAdd("NAME");
				__UFAdd("LAST_NAME");
				__UFAdd("EMAIL");
			}
			else
			{
				document.getElementById('GROUP_FILTER').value = '(objectClass=posixGroup)';
				document.getElementById('GROUP_ID_ATTR').value = 'gidNumber';
				document.getElementById('CONVERT_UTF8').checked = false;
				document.getElementById('GROUP_NAME_ATTR').value = 'cn';
				document.getElementById('GROUP_MEMBERS_ATTR').value = 'memberUid';
				document.getElementById('USER_FILTER').value = '(objectClass=posixAccount)';
				document.getElementById('USER_ID_ATTR').value = 'uid';
				//document.getElementById('USER_NAME_ATTR').value = 'cn';
				//document.getElementById('USER_LAST_NAME_ATTR').value = 'sn';
				//document.getElementById('USER_EMAIL_ATTR').value = 'email';
				document.getElementById('USER_GROUP_ACCESSORY').checked = false;
				document.getElementById('USER_GROUP_ATTR').value = 'gidNumber';
				document.getElementById('USER_DEPARTMENT_ATTR').value = 'department';
				document.getElementById('USER_MANAGER_ATTR').value = 'manager';
				__UFAdd("NAME", true);
				__UFAdd("LAST_NAME", true);
				__UFAdd("EMAIL", true);

			}

			return false;
		}
	</script>
	<tr class="heading">
		<td align="center" colspan="2"><b><?echo GetMessage("LDAP_EDIT_PARAMS")?></b> (<a href="javascript:void(0)" onClick="return OutLDSDefParams('AD')" title="<?echo GetMessage("LDAP_EDIT_PARAMS_AD")?>">AD</a> \ <a href="javascript:void(0)"  onClick="return OutLDSDefParams('LDAP')" title="<?echo GetMessage("LDAP_EDIT_PARAMS_LDAP")?>">LDAP</a>)</td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("LDAP_EDIT_CONVERT_UTF8")?></td>
		<td width="60%"><input type="checkbox" id="CONVERT_UTF8" name="CONVERT_UTF8" value="Y"<?if($str_CONVERT_UTF8=="Y")echo " checked"?>></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("LDAP_EDIT_GROUP_FILTER")?></td>
		<td><input type="text" id="GROUP_FILTER" name="GROUP_FILTER" size="30" maxlength="255" value="<?=$str_GROUP_FILTER?>"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("LDAP_EDIT_GROUP_ATTR")?></td>
		<td><input type="text" id="GROUP_ID_ATTR" name="GROUP_ID_ATTR" size="30" maxlength="255" value="<?=$str_GROUP_ID_ATTR?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("LDAP_EDIT_GROUP_NAME")?></td>
		<td><input type="text" id="GROUP_NAME_ATTR" name="GROUP_NAME_ATTR" size="30" maxlength="255" value="<?=$str_GROUP_NAME_ATTR?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("LDAP_EDIT_GROUP_MEMBERS")?></td>
		<td><input type="text" id="GROUP_MEMBERS_ATTR" name="GROUP_MEMBERS_ATTR" size="30" maxlength="255" value="<?=$str_GROUP_MEMBERS_ATTR?>"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("LDAP_EDIT_USER_FILTER")?></td>
		<td><input type="text" id="USER_FILTER" name="USER_FILTER" size="30" maxlength="255" value="<?=$str_USER_FILTER?>"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("LDAP_EDIT_USER_ATTR")?></td>
		<td><input type="text" id="USER_ID_ATTR" name="USER_ID_ATTR" size="30" maxlength="255" value="<?=$str_USER_ID_ATTR?>"></td>
	</tr>
	<!--
	<tr>
		<td><?echo GetMessage("LDAP_EDIT_USER_NAME")?></td>
		<td><input type="text" id="USER_NAME_ATTR" name="USER_NAME_ATTR" size="30" maxlength="255" value="<?=$str_USER_NAME_ATTR?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("LDAP_EDIT_USER_SNAME")?></td>
		<td><input type="text" id="USER_LAST_NAME_ATTR" name="USER_LAST_NAME_ATTR" size="30" maxlength="255" value="<?=$str_USER_LAST_NAME_ATTR?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("LDAP_EDIT_USER_EMAIL")?></td>
		<td><input type="text" id="USER_EMAIL_ATTR" name="USER_EMAIL_ATTR" size="30" maxlength="255" value="<?=$str_USER_EMAIL_ATTR?>"></td>
	</tr>
	-->
	<tr>
		<td><?echo GetMessage("LDAP_EDIT_USER_MEMBER")?></td>
		<td><input type="text" id="USER_GROUP_ATTR" name="USER_GROUP_ATTR" size="30" maxlength="255" value="<?=$str_USER_GROUP_ATTR?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("LDAP_EDIT_USER_GROUP_ACCESSORY")?></td>
		<td><input type="checkbox" id="USER_GROUP_ACCESSORY" name="USER_GROUP_ACCESSORY" value="Y" <?if($str_USER_GROUP_ACCESSORY=="Y")echo " checked"?>></td>
	</tr>
	<tr>
		<td><?echo GetMessage("LDAP_EDIT_USER_DEPARTMENT")?></td>
		<td><input type="text" id="USER_DEPARTMENT_ATTR" name="USER_DEPARTMENT_ATTR" size="30" maxlength="255" value="<?=$str_USER_DEPARTMENT_ATTR?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("LDAP_EDIT_USER_MANAGER")?></td>
		<td><input type="text" id="USER_MANAGER_ATTR" name="USER_MANAGER_ATTR" size="30" maxlength="255" value="<?=$str_USER_MANAGER_ATTR?>"></td>
	</tr>
	<tr class="heading">
		<td align="center" colspan="2"><b><?echo GetMessage("LDAP_EDIT_USER_FIELDS")?></b> <a href="#notes" style="text-decoration:none;"><span class="required">**</span></a><br></td>
	</tr>
	<?
	$nm=-1;
	foreach($arUserFieldMap as $userField=>$ldapField):
		if ($userField=='UF_DEPARTMENT' && IsModuleInstalled('intranet') /* also checking IsModuleInstalled('intranet'), because user can create his own UF_DEPARTMENT field in non-intranet version? */)
		{
			continue;
		}
		$nm++;
		?>
		<tr id="maprow<?=$nm?>">
			<td>
				<select name="MAP[<?=$nm?>][USER]" id="MAP[<?=$nm?>][USER]" onchange="__UFChange(<?=$nm?>)" style="width:200px">
					<option value=""></option>
					<?foreach($arSyncFields as $k=>$p):
						if ($k=='UF_DEPARTMENT' && IsModuleInstalled('intranet'))
						{
							continue;
						};?>
						<option value="<?=htmlspecialcharsbx($k)?>" title="<?=htmlspecialcharsbx($p["NAME"])?>" <?if($k == $userField)echo " selected"?>><?=htmlspecialcharsbx($p["NAME"])?></option>
					<?endforeach?>
				</select>  :
			</td>
			<td><input type="text"  name="MAP[<?=$nm?>][LDAP]"  id="MAP[<?=$nm?>][LDAP]" value="<?=htmlspecialcharsbx($ldapField)?>"> <a href="javascript:void(0);" onclick="__UFDel(<?=$nm?>);"><?echo GetMessage("LDAP_EDIT_USER_FIELDS_DEL")?></a></td>
		</tr>
	<?endforeach;?>

		<tr id="lastmaprow">
			<td></td>
			<td><a href="javascript:void(0)" onClick="__UFAdd();"><?echo GetMessage("LDAP_EDIT_USER_FIELDS_ADD")?></a></td>
		</tr>

	<script type="text/javascript">
	var nm = <?=$nm?>;
	var arMas = {'_': '' <?foreach($arSyncFields as $k=>$p):?>, '<?=Cutil::JSEscape($k)?>': '<?=Cutil::JSEscape($p["AD"])?>'<?endforeach;?>};
	var arMasLdap = {'_': '' <?foreach($arSyncFields as $k=>$p):?>, '<?=Cutil::JSEscape($k)?>': '<?=Cutil::JSEscape($p["LDAP"])?>'<?endforeach;?>};
	var arMasName = {'_': '' <?foreach($arSyncFields as $k=>$p):?>, '<?=Cutil::JSEscape($k)?>': '<?=Cutil::JSEscape($p["NAME"])?>'<?endforeach;?>};

	function __UFChange(id)
	{
		var oSelect = document.getElementById("MAP["+id+"][USER]");
		var oInput =  document.getElementById("MAP["+id+"][LDAP]");
		var name = oSelect.value;
		oInput.value = arMas[name];
	}
	function __UFDel(id)
	{
		var oTR = document.getElementById("maprow"+id);

		var oTable = oTR.parentNode;
		while(oTable.tagName.toLowerCase()!='table')
			oTable = oTable.parentNode;

		oTable.deleteRow(oTR.rowIndex);
	}

	function __UFAdd(id, ldap)
	{
		var val = '';
		var oSelect;
		if(id)
		{
			if(ldap)
			{
				if(arMasLdap[id])
					val = arMasLdap[id];
			}
			else
			{
				if(arMas[id])
					val = arMas[id];
			}

			var i;
			for(i=0; i<=nm; i++)
			{
				oSelect = document.getElementById("MAP["+i+"][USER]");
				if(oSelect && oSelect.value == id)
				{
					document.getElementById("MAP["+i+"][LDAP]").value = val;
					return;
				}
			}
		}

		var oTR = document.getElementById("lastmaprow");
		nm++;

		var oTable = oTR.parentNode;
		while(oTable.tagName.toLowerCase()!='table')
			oTable = oTable.parentNode;

		oTR = oTable.insertRow(oTR.rowIndex);
		oTR.vAlign = "top";
		oTR.id = "maprow"+nm;
		var oTD = oTR.insertCell(-1);
		oTD.align = "right";

		var k, tdText = '<select name="MAP['+nm+'][USER]" id="MAP['+nm+'][USER]" onchange="__UFChange('+nm+')" style="width:200px">';
		for(k in arMasName)
		{
			tdText = tdText + '<option value="'+k+'" ';
			if(id == k)
				tdText = tdText + ' selected';
			tdText = tdText + '>'+arMasName[k]+'</option>';
		}
		tdText = tdText + '</select>  : ';
		oTD.innerHTML = tdText;

		oTD = oTR.insertCell(-1);
		oTD.innerHTML = '<input type="text"  name="MAP['+nm+'][LDAP]"  id="MAP['+nm+'][LDAP]" value="'+val+'"> <a href="javascript:void(0);" onclick="__UFDel('+nm+')"><?echo GetMessage("LDAP_EDIT_USER_FIELDS_DEL")?></a>';
	}
	</script>


<?

	if (IsModuleInstalled('intranet'))
	{

		$importEnabled = ($str_IMPORT_STRUCT=="Y");
		?>
			<script type="text/javascript">
				function __importStateSwitch(disabled)
				{
					document.getElementById('ROOT_DEPARTMENT').disabled = disabled;
					document.getElementById('STRUCT_HAVE_DEFAULT').disabled = disabled;
					document.getElementById('DEFAULT_DEPARTMENT_NAME').disabled = disabled;
				}
			</script>

			<tr class="heading">
				<td align="center" colspan="2"><b><?echo GetMessage("LDAP_EDIT_STRUCTURE")?></b><br/></td>
			</tr>

			<tr>
				<td>
				<?=GetMessage("LDAP_EDIT_IMPORT_STRUCT")?>: </td>
				<td>
					<input onClick="__importStateSwitch(!this.checked);" type="checkbox" id="IMPORT_STRUCT" name="IMPORT_STRUCT" value="Y"<?if($importEnabled)echo " checked"?>></input>
				</td>
			</tr>
			<?
			$l = CLdapUtil::getDepartmentListFromSystem();
			if ($l !== false) {
			?>
			<tr>
				<td>				<?=GetMessage("LDAP_EDIT_STRUCT_ROOT")?>: </td>
				<td>

					<select name="ROOT_DEPARTMENT" id="ROOT_DEPARTMENT" <? if (!$importEnabled) echo 'disabled="1" '?> style="width: 270px">
						<option value="-1" <?if($str_ROOT_DEPARTMENT==-1) echo 'selected="1"'; ?>><?=GetMessage('LDAP_EDIT_DEPT_NOT_SET')?></option>

					<?
						while($arDepartment = $l->GetNext()):
							?><option value="<?=$arDepartment["ID"]?>" title="<?echo str_repeat(" . ", $arDepartment["DEPTH_LEVEL"])?><?echo $arDepartment["NAME"]?>" <? if ($arDepartment["ID"]==$str_ROOT_DEPARTMENT) echo 'selected="1"'; ?>><?echo str_repeat(" . ", $arDepartment["DEPTH_LEVEL"])?><?echo $arDepartment["NAME"]?></option><?
						endwhile;
					?>
					</select>
				</td>
			</tr>
			<? } ?>
			<tr>
				<td>
				<?=GetMessage("LDAP_EDIT_STRUCT_HAVE_DEFAULT")?>: </td>
				<td>
					<input type="checkbox" id="STRUCT_HAVE_DEFAULT" name="STRUCT_HAVE_DEFAULT" <? if (!$importEnabled) echo 'disabled="1" '?>value="Y"<?if($str_STRUCT_HAVE_DEFAULT && $str_STRUCT_HAVE_DEFAULT=='Y')echo " checked"?>></input>
				</td>
			</tr>

			<tr>
				<td>
				<?=GetMessage("LDAP_EDIT_STRUCT_DEFAULT_VAL")?>: </td>
				<td>
					<input type="text" name="DEFAULT_DEPARTMENT_NAME" id="DEFAULT_DEPARTMENT_NAME" size="40" maxlength="255" <? if (!$importEnabled) echo 'disabled="1" '?>value="<?=$str_DEFAULT_DEPARTMENT_NAME?>">
				</td>
			</tr>

		<?
	}
	?>

	<?$tabControl->BeginNextTab();?>

	<?
	$arLDAPGroups = false;
	if($ldp && ($gr_res = $ldp->GetGroupList()))
	{
		$arLDAPGroups = Array();

		while($ar_group = $gr_res->GetNext())
		{
			$arLDAPGroups[$ar_group['ID']] = (is_set($ar_group, 'NAME') ? $ar_group['NAME'] : $ar_group['ID']);
		}

		uasort($arLDAPGroups, create_function('$a, $b', '$a=ToUpper($a);$b=ToUpper($b); if($a==$b) return 0; return $a>$b?1:-1;'));
	}

	if(!is_array($arLDAPGroups) || count($arLDAPGroups)<=0):?>
	<script type="text/javascript">
	function CheckNAttr()
	{
		if(document.getElementById("GROUP_FILTER").value.length<=0 ||
			document.getElementById("GROUP_ID_ATTR").value.length<=0 ||
			document.getElementById("GROUP_NAME_ATTR").value.length<=0
			)
		{
			alert('<?=GetMessage("LDAP_EDIT_WARN")?>');
			return false;
		}

		return true;
	}
	</script>

	<tr>
		<td align="center" colspan="2"><?echo GetMessage("LDAP_SERV_EDIT_LIST_EMPTY")?></td>
	</tr>
	<tr>
		<td align="center" colspan="2"><input type="submit" name="refresh_groups" value="<?echo GetMessage("LDAP_EDIT_REFRESH")?>" class="button" onClick="return CheckNAttr();"></td>
	</tr>
	<?else:?>
	<tr>
		<td colspan="2" align="center">
			<table class="internal" cellspacing="0" cellpadding="0">
				<tr class="heading">
					<td><?echo GetMessage("LDAP_EDIT_GROUP_REM")?></td>
					<td><?echo GetMessage("LDAP_EDIT_GROUP_LOC")?></td>
					<td><?echo GetMessage("LDAP_EDIT_GROUP_MAP_DEL")?></td>
				</tr>
				<?foreach($arGroups as $t_id=>$arGroup):?>
				<tr>
					<td>
						<select name="LDAP_GROUP[<?=htmlspecialcharsbx($t_id)?>][LDAP_GROUP_ID]" style="width:360px;">
							<option value=""></option>
						<?foreach($arLDAPGroups as $gid => $gname):?>
							<option value="<?=$gid?>" title="<?=$gname?>" <?if(htmlspecialcharsEx($arGroup['LDAP_GROUP_ID'])==$gid)echo ' selected'?>><?=$gname?></option>
						<?endforeach?>
						</select>
					</td>
					<td>
						<?$dbgr = CGroup::GetList($o="sort", $b="asc");?>
						<select name="LDAP_GROUP[<?=htmlspecialcharsbx($t_id)?>][GROUP_ID]" style="width:360px;">
							<option value=""></option>
						<?while($argr = $dbgr->GetNext()):
							if ($argr['ID'] == 2)
								continue;
							?>
							<option value="<?=$argr['ID']?>" title="<?=$argr['NAME']?>  [<?=$argr['ID']?>]" <?if($arGroup['GROUP_ID']==$argr['ID'])echo ' selected'?>><?=$argr['NAME']?> [<?=$argr['ID']?>]</option>
						<?endwhile?>
						</select>
					</td>
					<td align="center">
						<?if(strlen($arGroup['LDAP_GROUP_ID'])>0 || $arGroup['GROUP_ID']>0):?>
							<input type="checkbox" name="LDAP_GROUP[<?=htmlspecialcharsbx($t_id)?>][DEL_GROUP]" value="Y" <?if($arGroup['DEL_GROUP']=='Y')echo ' checked'?>>
						<?endif;?>
					</td>
				</tr>
				<?endforeach?>
				<tr>
					<td colspan="3"><input type="submit" name="more_groups" value="<?echo GetMessage("LDAP_EDIT_GROUP_MAP_MORE")?>"></td>
				</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td colspan="2" align="center">
			<table class="internal" cellspacing="0" cellpadding="0">
				<tr class="heading">
					<td><?echo GetMessage("LDAP_EDIT_GROUP_NOIMPORT")?>:</td>
				</tr>
				<tr>
					<td>
						<select size="8" name="LDAP_NOIMP_GROUPS[]" style="width:360px;" multiple="">
						<?foreach($arLDAPGroups as $gid=>$gname):?>
							<option value="<?=$gid?>" title="<?=$gname?>" <?if(array_key_exists(md5(htmlspecialcharsback($gid)),$noimportGroups)) echo ' selected'?>><?=$gname?></option>
						<?endforeach?>
						</select>
					</td>
				</tr>
			</table>
		</td>
	</tr>

	<?endif;?>


<?$tabControl->BeginNextTab();?>
<script type="text/javascript">
function _SCh(c)
{
	document.getElementById('SYNC_PERIOD').disabled = !c;

	if(document.getElementById('SYNC_PERIOD').value.length<=0)
		document.getElementById('SYNC_PERIOD').value = '24';
	document.getElementById('sc2').disabled = !c;
	document.getElementById('sc3').disabled = !c;
}

</script>
<?
if($str_SYNC=="Y")
	$dis ='';
else
	$dis = ' disabled';
?>
<?if(strlen($str_SYNC_LAST)>0):?>
	<tr>
		<td><?echo GetMessage("LDAP_EDIT_LAST_SYNC")?></td>
		<td><?=$str_SYNC_LAST?></td>
	</tr>
<?endif?>
	<tr>
		<td width="40%"><label for="SYNC_USER_ADD"><?echo GetMessage("LDAP_EDIT_SYNC_USER_ADD")?></label></td>
		<td width="60%"><input type="checkbox" id="SYNC_USER_ADD" name="SYNC_USER_ADD" value="Y" <?if($str_SYNC_USER_ADD=="Y")echo ' checked'?>></td>
	</tr>
	<tr>
		<td width="40%"><label for="SYNC"><?echo GetMessage("LDAP_EDIT_SYNC")?></label></td>
		<td width="60%"><input type="checkbox" id="SYNC" name="SYNC" value="Y" <?if($str_SYNC=="Y")echo ' checked'?> onclick="_SCh(this.checked);"></td>
	</tr>
	<tr id="sc3"<?=$dis?>>
		<td id="sc2"<?=$dis?>><?echo GetMessage("LDAP_EDIT_SYNC_PERIOD")?></td>
		<td><input type="text" id="SYNC_PERIOD" name="SYNC_PERIOD" size="10" maxlength="18" value="<?=$str_SYNC_PERIOD?>"<?=$dis?>> <?echo GetMessage("LDAP_EDIT_SYNC_PERIOD_H")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("LDAP_EDIT_SYNC_ATTR")?></td>
		<td><input type="text" id="SYNC_ATTR" name="SYNC_ATTR" size="20" maxlength="255" value="<?=$str_SYNC_ATTR?>"></td>
	</tr>
<?
$tabControl->Buttons(Array("disabled"=> $MOD_RIGHT<"W" ,"back_url" =>"ldap_server_admin.php?lang=".LANG.GetFilterParams("filter_", false)));
$tabControl->End();?>

</form>

<?$tabControl->ShowWarnings("form1", $message);?>
<a name="notes" ></a>
<?echo BeginNote();?>
<span class="required">**</span> <?echo GetMessage("LDAP_EDIT_SYNC_NOTES")?>
<?echo EndNote();?>
<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
