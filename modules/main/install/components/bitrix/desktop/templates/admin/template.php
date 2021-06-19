<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (
	!defined("ADMIN_SECTION")
	|| ADMIN_SECTION !== true
)
{
	ShowError(GetMessage('CMDESKTOP_TDEF_PUBLIC'));
	return;
}

if(!defined("BX_GADGET_DEFAULT"))
{
	define("BX_GADGET_DEFAULT", true);
	?>
	<script type="text/javascript">
	var updateURL = '<?=CUtil::JSEscape(htmlspecialcharsback($arResult['UPD_URL']))?>';
	var bxsessid = '<?=CUtil::JSEscape(bitrix_sessid())?>';
	var language_id = '<?=CUtil::JSEscape(LANGUAGE_ID)?>';	
	var langGDError1 = '<?=CUtil::JSEscape(GetMessage("CMDESKTOP_TDEF_ERR1"))?>';
	var langGDError2 = '<?=CUtil::JSEscape(GetMessage("CMDESKTOP_TDEF_ERR2"))?>';
	var langGDConfirm1 = '<?=CUtil::JSEscape(GetMessage("CMDESKTOP_TDEF_CONF"))?>';
	var langGDClearConfirm = '<?=CUtil::JSEscape(GetMessage("CMDESKTOP_TDEF_CLEAR_CONF"))?>';
	var langGDCancel = "<?echo CUtil::JSEscape(GetMessage("CMDESKTOP_TDEF_CANCEL"))?>";
	
	BX.message({
			langGDSettingsDialogTitle: '<?=CUtil::JSEscape(GetMessage("CMDESKTOP_TDEF_SETTINGS_DIALOG_TITLE"))?>',
			langGDSettingsAllDialogTitle: '<?=CUtil::JSEscape(GetMessage("CMDESKTOP_TDEF_SETTINGS_ALL_DIALOG_TITLE"))?>',
			langGDSettingsDialogRowTitle: '<?=CUtil::JSEscape(GetMessage("CMDESKTOP_TDEF_COLUMN_WIDTH"))?>',
			langGDGadgetSettingsDialogTitle: '<?=CUtil::JSEscape(GetMessage("CMDESKTOP_TDEF_GADGET_SETTINGS_DIALOG_TITLE"))?>'
	});
	</script>
	<?

	if ($arParams["MULTIPLE"] == "Y")
	{
		?>
		<script type="text/javascript">
		var desktopPage = '<?=CUtil::JSEscape(htmlspecialcharsback($arParams["DESKTOP_PAGE"]))?>';
		var desktopBackurl = '<?=CUtil::JSEscape(htmlspecialcharsback($GLOBALS["APPLICATION"]->GetCurPageParam("", array("dt_page"))))?>';
		</script>
		<?
	}

	if($arResult["PERMISSION"] > "R"):?>
		<script type="text/javascript" src="/bitrix/components/bitrix/desktop/script.js?v=<?=filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/desktop/script.js');?>"></script>
		<script type="text/javascript" src="/bitrix/components/bitrix/desktop/templates/admin/script_admin.js?v=<?=filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/desktop/templates/admin/script_admin.js');?>"></script>	
	<?endif?>
	<?
}

if($arResult["PERMISSION"] > "R"):

	$allGD = Array();
	foreach($arResult['ALL_GADGETS'] as $gd)
	{
		$allGD[] = Array(
			'ID' => $gd["ID"],
			'TEXT' =>
				'<div style="text-align: left;">'.(isset($gd['ICON1']) && $gd['ICON1'] ? '<img src="'.($gd['ICON']).'" align="left">' : '').
				'<b>'.(htmlspecialcharsbx($gd['NAME'])).'</b><br>'.(htmlspecialcharsbx($gd['DESCRIPTION'])).'</div>',
			);
	}


	$aContext = array();

	$arGadgetGroups = array();
	foreach($arResult["GROUPS"] as $arGroup)
	{
		$arGadgets = array();
		foreach($arGroup["GADGETS"] as $gadget)
		{
			if (array_key_exists($gadget, $arResult["ALL_GADGETS"]))
			{
				$arGadgets[] = array(
					"TEXT" => $arResult["ALL_GADGETS"][$gadget]["NAME"],
					"TITLE" => $arResult["ALL_GADGETS"][$gadget]["DESCRIPTION"],
					"ACTION" => "getGadgetHolder('".AddSlashes($arResult["ID"])."').Add('".AddSlashes($arResult["ALL_GADGETS"][$gadget]["ID"])."')"
				);
			}
		}
		
		$arGadgetGroups[] = array(
			"TEXT" => $arGroup["NAME"],
			"TITLE" => $arGroup["DESCRIPTION"],
			"MENU" => $arGadgets
		);
	}
	
	$arGadgetsButton =
		array(
			"TEXT" => GetMessage("CMDESKTOP_TDEF_ADD_BUTTON"),
			"MENU" => $arGadgetGroups,
			"ICON" => "btn_desktop_gadgets"
		);
	
	$arSettingsMenu = array(
		array(
			"TEXT" => GetMessage("CMDESKTOP_TDEF_DESKTOP_ADD"),
			"TITLE" => GetMessage("CMDESKTOP_TDEF_DESKTOP_ADD"),
			"ACTION" => "__ShowDesktopAddDialog()"
		),
		array(
			"TEXT" => GetMessage("CMDESKTOP_TDEF_DESKTOP_SETTINGS"),
			"TITLE" => GetMessage("CMDESKTOP_TDEF_DESKTOP_SETTINGS"),
			"ACTION" => "__ShowDesktopSettingsDialog()"
		),
		array(
			"SEPARATOR" => "Y"
		),
		array(
			"TEXT" => GetMessage("CMDESKTOP_TDEF_DESKTOP_ALL_SETTINGS"),
			"TITLE" => GetMessage("CMDESKTOP_TDEF_DESKTOP_ALL_SETTINGS"),
			"ACTION" => "__ShowDesktopAllSettingsDialog()"
		),
		array(
			"SEPARATOR" => "Y"
		),
		array(
			"TEXT" => GetMessage("CMDESKTOP_TDEF_CLEAR"),
			"TITLE" => GetMessage("CMDESKTOP_TDEF_CLEAR"),
			"ACTION" => "getGadgetHolder('".AddSlashes($arResult["ID"])."').ClearUserSettingsConfirm()"
		)
	);

	if($arResult["PERMISSION"]>"W")
		$arSettingsMenu[] = array(
				"TEXT" => GetMessage("CMDESKTOP_TDEF_SET"),
				"TITLE" => GetMessage("CMDESKTOP_TDEF_SET"),
				"ACTION" => "getGadgetHolder('".AddSlashes($arResult["ID"])."').SetForAll('')"
			);

	$arSettingsButton =
		array(
			"TEXT" => GetMessage("CMDESKTOP_TDEF_DESKTOP_SETTINGS_BUTTON"),
			"TITLE" => GetMessage("CMDESKTOP_TDEF_DESKTOP_SETTINGS_BUTTON"),
			"MENU" => $arSettingsMenu,
			"ICON" => "btn_desktop_settings"
		);

	$mContext = new CAdminContextMenu(array());
	?>
	<script type="text/javascript">
		var arGDGroups = <?=CUtil::PhpToJSObject($arResult["GROUPS"])?>;
		new BX.AdminGadget('<?=$arResult["ID"]?>', <?=CUtil::PhpToJSObject($allGD)?>);
	</script>
	<div class="bx-gadgets-header"><?
		if (array_key_exists($arParams["DESKTOP_PAGE"], $arResult["DESKTOPS"]))
		{
			$title = ($arResult["DESKTOPS"][$arParams["DESKTOP_PAGE"]]["NAME"] <> '' ? $arResult["DESKTOPS"][$arParams["DESKTOP_PAGE"]]["NAME"] : str_replace("#NUM#", intval($arParams["DESKTOP_PAGE"] + 1), GetMessage("CMDESKTOP_TDEF_ADMIN_TITLE_DEFAULT")));
			$title = str_replace("#TITLE#", $title, GetMessage("CMDESKTOP_TDEF_ADMIN_TITLE"));
			?><h1 id="adm-title" class="adm-title" id=""><?=htmlspecialcharsbx($title)?></h1><?
		}

		?><div class="bx-gadgets-buttons"><?
			$mContext->Button($arGadgetsButton, CHotKeys::getInstance());
			$mContext->Button($arSettingsButton, CHotKeys::getInstance());
		?></div>
	</div>
	<?
endif;
?>
<form action="<?=POST_FORM_ACTION_URI?>" method="POST" id="GDHolderForm_<?=$arResult["ID"]?>">
<?=bitrix_sessid_post()?>
<input type="hidden" name="holderid" value="<?=$arResult["ID"]?>">
<input type="hidden" name="gid" value="0">
<input type="hidden" name="action" value="">
</form>

<div class="bx-gadgets-container-new">
<table class="gadgetholder" cellspacing="0" cellpadding="0" width="100%" id="GDHolder_<?=$arResult["ID"]?>">
	<tbody>
	<tr>
	<?for($i=0; $i<$arResult["COLS"]; $i++):?>
		<?if($i==0):?>
			<td class="gd-page-column<?=$i?>" valign="top" width="<?=$arResult["COLUMN_WIDTH"][$i]?>" id="s0">
		<?elseif($i==$arResult["COLS"]-1):?>
			<td width="20">
				<div style="WIDTH: 20px"></div>
				<br />
			</td>
			<td class="gd-page-column<?=$i?>" valign="top" width="<?=$arResult["COLUMN_WIDTH"][$i]?>" id="s2">
		<?else:?>
			<td width="20">
				<div style="WIDTH: 20px"></div>
				<br />
			</td>
			<td class="gd-page-column<?=$i?>" valign="top"  width="<?=$arResult["COLUMN_WIDTH"][$i]?>" id="s1">
		<?endif?>
		<?foreach($arResult["GADGETS"][$i] as $arGadget):
			$bChangable = true;
			if (
				(
					!$GLOBALS["USER"]->IsAdmin()
					|| (isset($arGadget["TOTALLY_FIXED"]) && $arGadget["TOTALLY_FIXED"])
				)
				&& array_key_exists("GADGETS_FIXED", $arParams) 
				&& is_array($arParams["GADGETS_FIXED"]) 
				&& in_array($arGadget["GADGET_ID"], $arParams["GADGETS_FIXED"])
				&& array_key_exists("CAN_BE_FIXED", $arGadget)
				&& $arGadget["CAN_BE_FIXED"]
			)
			{
				$bChangable = false;
			}

			if ($arGadget["COLOURFUL"] ?? false)
			{
				?><div class="bx-gadgets-colourful bx-gadgets<?=($arGadget["TITLE_ICON_CLASS"] <> '' ? " ".$arGadget["TITLE_ICON_CLASS"] : "")?>" id="t<?=$arGadget["ID"]?>">
					<div class="bx-gadgets-content">
						<?=$arGadget["CONTENT"]?><?
						if ($bChangable)
						{
							?><a href="javascript:void(0)" class="bx-gadgets-color-config-close" onclick="return getGadgetHolder('<?=AddSlashes($arResult["ID"])?>').Delete('<?=$arGadget["ID"]?>');" title="<?=GetMessage("CMDESKTOP_TDEF_DELETE")?>"></a><?
						}
						?><div class="bx-gadgets-side" style="cursor:move;" onmousedown="return getGadgetHolder('<?=AddSlashes($arResult["ID"])?>').DragStart('<?=$arGadget["ID"]?>', event)"></div><?
					?></div>
				</div><?
			}
			else
			{
				?><div class="bx-gadgets<?=(($arGadget["TITLE_ICON_CLASS"] ?? '') <> '' ? " ".$arGadget["TITLE_ICON_CLASS"] : "")?>" id="t<?=$arGadget["ID"]?>">
					<div class="bx-gadgets-top-wrap" onmousedown="return getGadgetHolder('<?=AddSlashes($arResult["ID"])?>').DragStart('<?=$arGadget["ID"]?>', event)">
						<div class="bx-gadgets-top-center">
							<div class="bx-gadgets-top-title"><?=$arGadget["TITLE"]?></div>
							<div class="bx-gadgets-top-button"><?
								if ($bChangable):
									?><a class="bx-gadgets-config<?=(($arGadget["NOPARAMS"] ?? false) ? ' bx-gadgets-noparams' : '')?>" href="javascript:void(0)" onclick="return getAdminGadgetHolder('<?=AddSlashes($arResult["ID"])?>').ShowSettings('<?=$arGadget["ID"]?>', '<?=CUtil::JSEscape($arGadget["TITLE"])?>');" title="<?=GetMessage("CMDESKTOP_TDEF_SETTINGS")?>"></a>
									<a class="bx-gadgets-config-close" href="javascript:void(0)" onclick="return getGadgetHolder('<?=AddSlashes($arResult["ID"])?>').Delete('<?=$arGadget["ID"]?>');" title="<?=GetMessage("CMDESKTOP_TDEF_DELETE")?>"></a><?
								endif;
							?></div>
						</div>
					</div>
					<div class="bx-gadgets-content"><?=$arGadget["CONTENT"]?></div>
				</div><?
			}
			
			?><div style="display:none; border:1px #404040 dashed; margin-bottom:8px;" id="d<?=$arGadget["ID"]?>"></div>
		<?endforeach;?>
	</td>
	<?endfor;?>
	</tr>
	</tbody>
</table>
</div>