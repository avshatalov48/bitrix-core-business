<?
//<title>Froogle</title>
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/export_setup_templ.php');

global $APPLICATION;

$strCatalogDefaultFolder = COption::GetOptionString("catalog", "export_default_path", CATALOG_DEFAULT_EXPORT_PATH);

$arSetupErrors = array();

if (($ACTION == 'EXPORT_EDIT' || $ACTION == 'EXPORT_COPY') && $STEP == 1)
{
	if (array_key_exists('IBLOCK_ID', $arOldSetupVars))
		$IBLOCK_ID = $arOldSetupVars['IBLOCK_ID'];
	if (array_key_exists('SETUP_FILE_NAME', $arOldSetupVars))
		$SETUP_FILE_NAME = str_replace($strCatalogDefaultFolder, '', $arOldSetupVars['SETUP_FILE_NAME']);
	if (array_key_exists('SETUP_PROFILE_NAME', $arOldSetupVars))
		$SETUP_PROFILE_NAME = $arOldSetupVars['SETUP_PROFILE_NAME'];
	if (array_key_exists('V', $arOldSetupVars))
		$V = $arOldSetupVars['V'];
}

if ($STEP>1)
{
	$IBLOCK_ID = intval($IBLOCK_ID);
	$rsIBlocks = CIBlock::GetByID($IBLOCK_ID);
	if ($IBLOCK_ID<=0 || !($arIBlock = $rsIBlocks->Fetch()))
	{
		$arSetupErrors[] = GetMessage("CET_ERROR_NO_IBLOCK1")." #".$IBLOCK_ID." ".GetMessage("CET_ERROR_NO_IBLOCK2");
	}
	elseif (!CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, 'iblock_admin_display'))
	{
		$arSetupErrors[] = str_replace('#IBLOCK_ID#',$IBLOCK_ID,GetMessage('CET_ERROR_IBLOCK_PERM'));
	}

	if (strlen($SETUP_FILE_NAME)<=0)
	{
		$arSetupErrors[] = GetMessage("CET_ERROR_NO_FILENAME");
	}
	if (empty($arSetupErrors))
	{
		$SETUP_FILE_NAME = str_replace('//','/',$strCatalogDefaultFolder.Rel2Abs("/", $SETUP_FILE_NAME));
		if (preg_match(BX_CATALOG_FILENAME_REG,$SETUP_FILE_NAME))
		{
			$arSetupErrors[] = GetMessage("CES_ERROR_BAD_EXPORT_FILENAME");
		}
		elseif ($APPLICATION->GetFileAccessPermission($SETUP_FILE_NAME) < "W")
		{
			$arSetupErrors[] = str_replace("#FILE#", $SETUP_FILE_NAME, "You do not have access rights to add or modify #FILE#");
		}
	}

	if (empty($arSetupErrors))
	{
		$bAllSections = False;
		$arSections = array();
		if (is_array($V))
		{
			foreach ($V as $key => $value)
			{
				if (trim($value)=="0")
				{
					$bAllSections = True;
					break;
				}
				if (intval($value)>0)
				{
					$arSections[] = intval($value);
				}
			}
		}

		if (!$bAllSections && count($arSections)<=0)
			$arSetupErrors[] = GetMessage("CET_ERROR_NO_GROUPS");
	}

	if (($ACTION=="EXPORT_SETUP" || $ACTION == 'EXPORT_EDIT' || $ACTION == 'EXPORT_COPY') && strlen($SETUP_PROFILE_NAME)<=0)
		$arSetupErrors[] = GetMessage("CET_ERROR_NO_PROFILE_NAME");

	if (!empty($arSetupErrors))
	{
		$STEP = 1;
	}
}

$aMenu = array(
	array(
		"TEXT"=>GetMessage("CATI_ADM_RETURN_TO_LIST"),
		"TITLE"=>GetMessage("CATI_ADM_RETURN_TO_LIST_TITLE"),
		"LINK"=>"/bitrix/admin/cat_export_setup.php?lang=".LANGUAGE_ID,
		"ICON"=>"btn_list",
	)
);

$context = new CAdminContextMenu($aMenu);

$context->Show();

if (!empty($arSetupErrors))
	ShowError(implode('<br />', $arSetupErrors));

$actionParams = "";
if ($adminSidePanelHelper->isSidePanel())
{
	$actionParams = "?IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER";
}
?>
<form method="post" action="<? echo $APPLICATION->GetCurPage().$actionParams; ?>" name="froogle_setup_form" id="froogle_setup_form">
<?

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("CAT_ADM_MISC_EXP_TAB1"), "ICON" => "store", "TITLE" => GetMessage("CAT_ADM_MISC_EXP_TAB1_TITLE")),
	array("DIV" => "edit2", "TAB" => GetMessage("CAT_ADM_MISC_EXP_TAB2"), "ICON" => "store", "TITLE" => GetMessage("CAT_ADM_MISC_EXP_TAB2_TITLE")),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs, false, true);
$tabControl->Begin();

$tabControl->BeginNextTab();

if ($STEP==1)
{
?><tr>
	<td width="40%"><? echo GetMessage('CET_SELECT_IBLOCK_EXT'); ?></td>
	<td width="60%"><?
	$arIBlockIDs = array();
	$rsCatalogs = CCatalog::GetList(
		array(),
		array(),
		false,
		false,
		array('IBLOCK_ID')
	);
	while ($arCatalog = $rsCatalogs->Fetch())
	{
		$arIBlockIDs[] = $arCatalog['IBLOCK_ID'];
	}
	if (empty($arIBlockIDs))
		$arIBlockIDs[] = -1;
	echo GetIBlockDropDownListEx(
		$IBLOCK_ID, 'IBLOCK_TYPE_ID', 'IBLOCK_ID',
		array(
			'ID' => $arIBlockIDs, 'ACTIVE' => 'Y',
			'CHECK_PERMISSIONS' => 'Y','MIN_PERMISSION' => 'W'
		),
		"ClearSelected(); BX('ifr').src='/bitrix/tools/catalog_export/froogle_util.php?IBLOCK_ID=0&'+'".bitrix_sessid_get()."';",
		"ClearSelected(); BX('ifr').src='/bitrix/tools/catalog_export/froogle_util.php?IBLOCK_ID='+this[this.selectedIndex].value+'&'+'".bitrix_sessid_get()."';",
		'class="adm-detail-iblock-types"',
		'class="adm-detail-iblock-list"'
	);
	?>
		<script type="text/javascript">
		var TreeSelected = new Array();
		<?
		$intCountSelected = 0;
		if (isset($V) && !empty($V) && is_array($V))
		{
			foreach ($V as $oneKey)
			{
				?>TreeSelected[<? echo $intCountSelected ?>] = <? echo intval($oneKey); ?>;
			<?
			$intCountSelected++;
			}
		}
		?>
		function ClearSelected()
		{
			BX.showWait();
			TreeSelected = new Array();
		}
		</script>
	</td>
</tr>
<tr>
	<td width="40%" valign="top"><?echo GetMessage("CET_SELECT_GROUP");?></td>
	<td width="60%">
		<div id="tree"></div>
		<script type="text/javascript">
		BX.showWait();
		clevel = 0;

		function buildNoMenu()
		{
			var buffer;
			buffer  = '<?echo GetMessageJS("CET_FIRST_SELECT_IBLOCK");?>';
			BX('tree', true).innerHTML = buffer;
			BX.closeWait();
		}

		function buildMenu()
		{
			var i;
			var buffer;
			var imgSpace;

			buffer = '<table border="0" cellspacing="0" cellpadding="0">';
			buffer += '<tr>';
			buffer += '<td colspan="2" valign="top" align="left"><input type="checkbox" name="V[]" value="0" id="v0"'+(BX.util.in_array(0,TreeSelected) ? ' checked' : '')+'><label for="v0"><font class="text"><b><?echo CUtil::JSEscape(GetMessage("CET_ALL_GROUPS"));?></b></font></label></td>';
			buffer += '</tr>';

			for (i in Tree[0])
			{
				if (!Tree[i])
				{
					space = '<input type="checkbox" name="V[]" value="'+i+'" id="V'+i+'"'+(BX.util.in_array(i,TreeSelected) ? ' checked' : '')+'><label for="V'+i+'"><font class="text">' + Tree[0][i][0] + '</font></label>';
					imgSpace = '';
				}
				else
				{
					space = '<input type="checkbox" name="V[]" value="'+i+'"'+(BX.util.in_array(i,TreeSelected) ? ' checked' : '')+'><a href="javascript: collapse(' + i + ')"><font class="text"><b>' + Tree[0][i][0] + '</b></font></a>';
					imgSpace = '<img src="/bitrix/images/catalog/load/plus.gif" width="13" height="13" id="img_' + i + '" OnClick="collapse(' + i + ')">';
				}

				buffer += '<tr>';
				buffer += '<td width="20" valign="top" align="center">' + imgSpace + '</td>';
				buffer += '<td id="node_' + i + '">' + space + '</td>';
				buffer += '</tr>';
			}

			buffer += '</table>';
			BX('tree', true).innerHTML = buffer;
			BX.adminPanel.modifyFormElements('froogle_setup_form');
			BX.closeWait();
		}

		function collapse(node)
		{
			if (!BX('table_' + node))
			{
				var i;
				var buffer;
				var imgSpace;

				buffer = '<table border="0" id="table_' + node + '" cellspacing="0" cellpadding="0">';

				for (i in Tree[node])
				{
					if (!Tree[i])
					{
						space = '<input type="checkbox" name="V[]" value="'+i+'" id="V'+i+'"'+(BX.util.in_array(i,TreeSelected) ? ' checked' : '')+'><label for="V'+i+'"><font class="text">' + Tree[node][i][0] + '</font></label>';
						imgSpace = '';
					}
					else
					{
						space = '<input type="checkbox" name="V[]" value="'+i+'"'+(BX.util.in_array(i,TreeSelected) ? ' checked' : '')+'><a href="javascript: collapse(' + i + ')"><font class="text"><b>' + Tree[node][i][0] + '</b></font></a>';
						imgSpace = '<img src="/bitrix/images/catalog/load/plus.gif" width="13" height="13" id="img_' + i + '" OnClick="collapse(' + i + ')">';
					}

					buffer += '<tr>';
					buffer += '<td width="20" align="center" valign="top">' + imgSpace + '</td>';
					buffer += '<td id="node_' + i + '">' + space + '</td>';
					buffer += '</tr>';
				}

				buffer += '</table>';

				BX('node_' + node).innerHTML += buffer;
				BX('img_' + node).src = '/bitrix/images/catalog/load/minus.gif';
			}
			else
			{
				var tbl = document.getElementById('table_' + node);
				tbl.parentNode.removeChild(tbl);
				BX('img_' + node).src = '/bitrix/images/catalog/load/plus.gif';
			}
			BX.adminPanel.modifyFormElements('froogle_setup_form');
		}
		</script>
		<iframe src="/bitrix/tools/catalog_export/froogle_util.php?IBLOCK_ID=<?=intval($IBLOCK_ID)?>&<? echo bitrix_sessid_get(); ?>" id="ifr" name="ifr" style="display:none"></iframe>
	</td>
</tr>
<tr>
	<td width="40%"><?echo GetMessage("CET_SAVE_FILENAME");?></td>
	<td width="60%"><b><? echo htmlspecialcharsex($strCatalogDefaultFolder); ?></b>
		<input type="text" name="SETUP_FILE_NAME" value="<?echo htmlspecialcharsbx(strlen($SETUP_FILE_NAME)>0 ? str_replace($strCatalogDefaultFolder, '', $SETUP_FILE_NAME) : "froogle_".mt_rand(0, 999999).".txt"); ?>" size="50">
	</td>
</tr>
<?
	if ($ACTION=="EXPORT_SETUP" || $ACTION == 'EXPORT_EDIT' || $ACTION == 'EXPORT_COPY')
	{
?><tr>
	<td width="40%"><?echo GetMessage("CET_PROFILE_NAME");?></td>
	<td width="60%">
		<input type="text" name="SETUP_PROFILE_NAME" value="<? echo (strlen($SETUP_PROFILE_NAME) > 0 ? htmlspecialcharsbx($SETUP_PROFILE_NAME) : ''); ?>" size="30">
	</td>
</tr><?
	}
}

$tabControl->EndTab();

$tabControl->BeginNextTab();

if ($STEP==2)
{
	$FINITE = true;
}

$tabControl->EndTab();

$tabControl->Buttons();

?><? echo bitrix_sessid_post();?><?
if ($ACTION == 'EXPORT_EDIT' || $ACTION == 'EXPORT_COPY')
{
	?><input type="hidden" name="PROFILE_ID" value="<? echo intval($PROFILE_ID); ?>"><?
}

if (2 > $STEP)
{
	?><input type="hidden" name="lang" value="<?echo LANGUAGE_ID ?>">
	<input type="hidden" name="ACT_FILE" value="<?echo htmlspecialcharsbx($_REQUEST["ACT_FILE"]) ?>">
	<input type="hidden" name="ACTION" value="<?echo htmlspecialcharsbx($ACTION) ?>">
	<input type="hidden" name="STEP" value="<?echo intval($STEP) + 1 ?>">
	<input type="hidden" name="SETUP_FIELDS_LIST" value="V,IBLOCK_ID,SETUP_FILE_NAME">
	<input type="submit" value="<?echo ($ACTION=="EXPORT")?GetMessage("CET_EXPORT"):GetMessage("CET_SAVE")?>"><?
}

$tabControl->End();
?></form>
<script type="text/javascript">
<?if ($STEP < 2):?>
tabControl.SelectTab("edit1");
tabControl.DisableTab("edit2");
<?elseif ($STEP == 2):?>
tabControl.SelectTab("edit2");
tabControl.DisableTab("edit1");
<?endif;?>
</script>