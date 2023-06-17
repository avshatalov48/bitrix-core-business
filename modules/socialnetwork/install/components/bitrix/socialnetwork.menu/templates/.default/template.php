<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

\Bitrix\Main\UI\Extension::load(['ui.design-tokens']);

if (!empty($arResult["ErrorMessage"]))
{
	?><span class='errortext'><?=$arResult["ErrorMessage"]?></span><?
	return;
}
else
{
	if(!defined("BX_SM_DEFAULT"))
	{
		define("BX_SM_DEFAULT", true);
		?>
		<script>
		var bIEOpera = (BX.browser.IsIE() || BX.browser.IsOpera());
		var bMenuAdd = <?=(count($arResult['FEATURES']) > $arResult["MAX_ITEMS"] ? 'true' : 'false')?>;
		var SMupdateURL = '<?=CUtil::JSEscape(htmlspecialcharsback($arResult['UPD_URL']))?>';
		var langMenuSettDialogTitle1 = '<?=CUtil::JSEscape(GetMessage("SONET_SM_SETTINGS_TITLE_1"))?>';
		var langMenuSettDialogTitle_forum = '<?=CUtil::JSEscape(GetMessage("SONET_SM_SETTINGS_TITLE_forum"))?>';
		var langMenuSettDialogTitle_blog = '<?=CUtil::JSEscape(GetMessage("SONET_SM_SETTINGS_TITLE_blog"))?>';
		var langMenuSettDialogTitle_microblog = '<?=CUtil::JSEscape(GetMessage("SONET_SM_SETTINGS_TITLE_microblog"))?>';
		var langMenuSettDialogTitle_photo = '<?=CUtil::JSEscape(GetMessage("SONET_SM_SETTINGS_TITLE_photo"))?>';
		var langMenuSettDialogTitle_calendar = '<?=CUtil::JSEscape(GetMessage("SONET_SM_SETTINGS_TITLE_calendar"))?>';
		var langMenuSettDialogTitle_tasks = '<?=CUtil::JSEscape(GetMessage("SONET_SM_SETTINGS_TITLE_tasks"))?>';
		var langMenuSettDialogTitle_files = '<?=CUtil::JSEscape(GetMessage("SONET_SM_SETTINGS_TITLE_files"))?>';
		var langMenuSettDialogTitle_search = '<?=CUtil::JSEscape(GetMessage("SONET_SM_SETTINGS_TITLE_search"))?>';
		var langMenuSettDialogTitle_global = '<?=CUtil::JSEscape(GetMessage("SONET_SM_SETTINGS_TITLE_global"))?>';
		<?
		if (array_key_exists("CustomFeaturesTitle", $arResult))
		{
			foreach($arResult["CustomFeaturesTitle"] as $feature => $title)
			{
				?>var langMenuSettDialogTitle_<?=$feature?> = '<?=CUtil::JSEscape($title)?>';<?
			}
		}
		?>
		var langMenuError1 = '<?=CUtil::JSEscape(GetMessage("SONET_SM_TDEF_ERR1"))?>';
		var langMenuError2 = '<?=CUtil::JSEscape(GetMessage("SONET_SM_TDEF_ERR2"))?>';
		var langMenuConfirm1 = '<?=CUtil::JSEscape(GetMessage("SONET_SM_TDEF_CONF1"))?>';
		var langMenuConfirm2 = '<?=CUtil::JSEscape(GetMessage("SONET_SM_TDEF_CONF2"))?>';
		</script>
		<script type="text/javascript" src="/bitrix/components/bitrix/socialnetwork.menu/script.js?v=<?=filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/socialnetwork.menu/script.js');?>"></script>
		<div id="antiselect" style="height:100%; width:100%; left: 0; top: 0; position: absolute; -moz-user-select: none !important; display: none; background-color:#FFFFFF; -moz-opacity: 0.01;"></div>
		<?
	}

	$allFeaturesShow = array_slice($arResult['FEATURES'], 0, $arResult["MAX_ITEMS"]);
	$allFeaturesAdd = array_slice($arResult['FEATURES'], $arResult["MAX_ITEMS"]);
	$allFeaturesInactive = array();
	?>
	<script>
		window.___BXMenu = new BXMenu('<?=$arResult["ID"]?>');
	</script>
	<?

	if ($arResult["PERMISSION"]>"R")
	{
		$allFeaturesInactive = [];
		foreach($arResult['ALL_FEATURES'] as $feature=>$arFeature)
		{
			if (
				!in_array($feature, $arResult["FEATURES_CODES"])
			)
			{
				$allFeaturesInactive[] = [
					'feature' => $feature,
					'Active' => $arFeature['Active'],
					'FeatureName' => $arFeature['FeatureName'],
				];
			}
		}
	}

	?>
	<form action="<?=POST_FORM_ACTION_URI?>" method="POST" id="MenuHolderForm_<?=$arResult["ID"]?>">
	<input type="hidden" name="sm_action" value="">
	<input type="hidden" name="feature" value="">
	<?=bitrix_sessid_post();?>
	<div>
	<table cellspacing="0" cellpadding="0">
	<tr>
	<td width="0%" class="bx-sm-leftshadow"><div style="width: 7px;"></div></td>
	<td valign="top">
	<table class="bx-sm-holder-show" cellspacing="0" cellpadding="0" id="MenuHolder_<?=$arResult["ID"]?>">
	<tr><?
		if (is_array($allFeaturesShow))
		{
			foreach($allFeaturesShow as $i => $arFeature):
				$ind = $i + 1;

				if($i==0)
					$CellID = "s0";
				elseif($i == $arResult["MAX_ITEMS"]-1)
					$CellID = "s2";
				else
					$CellID = "s1";

				if ($arFeature["feature"] == "general")
					$feature_class = ($arParams["PAGE_ID"] == "user" || $arParams["PAGE_ID"] == "group" ? "bx-sm-feature-select" : "bx-sm-feature-noselect");
				else
					$feature_class = ($arParams["PAGE_ID"] == "user_".$arFeature["feature"] || $arParams["PAGE_ID"] == "group_".$arFeature["feature"] ? "bx-sm-feature-select" : "bx-sm-feature-noselect");
				?>
				<td id="<?=$CellID?>">
				<table id="t<?=$arFeature["feature"]?>" cellspacing="0" cellpadding="0" border="0" class="<?=$feature_class?>">
				<tr>
					<td width="100%" align="center" nowrap><?
					if($arResult["PERMISSION"] > "R")
					{
						?><script>var jsMI_<?=$arFeature["feature"]?> = new BXMenuItem('<?=$arFeature["feature"]?>');</script><?
					}
					?><div class="bx-sm-parent" id="item_<?=$arFeature["feature"]?>" <?
					if($arResult["PERMISSION"] > "R" && (!$arFeature["NOPARAMS"] || $arFeature["ALLOW_SETTINGS"]))
					{
						?>onMouseOver="jsMI_<?=$arFeature["feature"]?>.StartTrackMouse(this)"<?
					}
					?> style="position: relative;"><nobr><?
					if($arResult["PERMISSION"] > "R"):
						?><a href="<?=$arFeature["Url"]?>" onClick="if (!window.___BXMenu.bWasDraggedRecently) { location.href='<?=str_replace("'", "\'", $arFeature["Url"])?>'; } return BX.PreventDefault(arguments[0]||window.event);" class="bx-sm-header" style="cursor:pointer;" onmousedown="return getMenuHolder('<?=AddSlashes($arResult["ID"])?>').DragStart('<?=$arFeature["feature"]?>', event)"><?
					else:
						?><a href="<?=$arFeature["Url"]?>" class="bx-sm-header"><?
					endif;
					echo $arFeature["FeatureName"];
					?></a><?
					if($arResult["PERMISSION"] > "R" && (!$arFeature["NOPARAMS"] || $arFeature["ALLOW_SETTINGS"])):
						?>
						<div class="bx-sm-actions" id="act_<?=$arFeature["feature"]?>">
						<div class="bx-sm-actions-left"></div>
						<a class="bx-sm-settings bx-sm-actions-button" href="javascript:void(0)" onclick="return getMenuHolder('<?=AddSlashes($arResult["ID"])?>').ShowSettings('<?=$arFeature["feature"]?>', 'get_settings', '<?=$arFeature["feature"]?>');" title="<?=GetMessage("SONET_SM_TDEF_SETTINGS")?>"></a>
						<div class="bx-sm-actions-separator"></div>
						<a id="act_remove_<?=$arFeature["feature"]?>" class="bx-sm-remove bx-sm-actions-button" href="javascript:void(0)" onclick="return getMenuHolder('<?=AddSlashes($arResult["ID"])?>').Delete('<?=$arFeature["feature"]?>');" title="<?=GetMessage("SONET_SM_TDEF_OFF")?>"></a>
						<div class="bx-sm-actions-right"></div>
						</div><?
					endif;
					?>
					</nobr></div>
					</td>
				</tr>
				</table>
				<div style="display:none;" id="d<?=$arFeature["feature"]?>" class="bx-sm-dotted"></div>
				</td>
				<td width="0%" class="bx-sm-separator"><div style="width: 3px;"></div></td>
				<?
			endforeach;
		}
	?></tr>
	</table>
	</td>
	<td width="0%" valign="top"><?
	if (count($allFeaturesAdd) > 0):
	?><div id="ddmenuaddholder"><table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td>
		<div class="ddmenu" onMouseOver="getMenuHolder('<?=AddSlashes($arResult["ID"])?>').ShowHolder('ddmenuadd', this);" onMouseMove="getMenuHolder('<?=AddSlashes($arResult["ID"])?>').ShowHolder('ddmenuadd', this);"  onClick="getMenuHolder('<?=AddSlashes($arResult["ID"])?>').ShowHolder('ddmenuadd', this);">
		<table cellspacing="0" cellpadding="0">
		<tr>
			<td class="bx-sm-feature-noselect"><a style="display: block; position: relative; z-index: 300;" href="" onClick="return false;" class="bx-sm-parent-button" title="<?=GetMessage("SONET_SM_TDEF_ADD")?>"><div class="bx-sm-header-button"><div class="bx-sm-item-add"></div></div></a></td>
		</tr>
		</table>
		<div id="ddmenuadd" class="ddmenu-inactive">
		<table id="MenuHolderAdd_<?=$arResult["ID"]?>" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td class="bx-sm-ddmenu-top-left"><div class="bx-sm-ddmenu-top-right"></div></td>
		</tr>
		<?
		foreach ($allFeaturesAdd as $j => $arFeature):
			$ind = $j + 1 + count($allFeaturesShow);

			if($j==0)
				$CellID = "sadd0";
			elseif($j == count($allFeaturesAdd))
				$CellID = "sadd2";
			else
				$CellID = "sadd1";

			if ($arFeature["feature"] == "general")
				$feature_class = ($arParams["PAGE_ID"] == "user" || $arParams["PAGE_ID"] == "group" ? "bx-sm-feature-select" : "bx-sm-feature-noselect");
			else
				$feature_class = ($arParams["PAGE_ID"] == "user_".$arFeature["feature"] || $arParams["PAGE_ID"] == "group_".$arFeature["feature"] ? "bx-sm-feature-select" : "bx-sm-feature-noselect");

			?><tr>
				<td id="<?=$CellID?>" class="bx-sm-ddmenu-middle-left"><div class="bx-sm-ddmenu-middle-right<?=($arResult["PERMISSION"] <= "R" ? "-regular" : "" )?>" <?if ($j == (count($allFeaturesAdd)-1)){ echo 'style="padding-bottom: 5px;"'; } ?>><table id="t<?=$arFeature["feature"]?>" class="<?=$feature_class?>" cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td nowrap><?
					if($arResult["PERMISSION"] > "R")
					{
						?><script>var jsMI_<?=$arFeature["feature"]?> = new BXMenuItem('<?=$arFeature["feature"]?>');</script><?
					}
					?><div class="bx-sm-parent" id="item_<?=$arFeature["feature"]?>" <?
					if($arResult["PERMISSION"] > "R" && (!$arFeature["NOPARAMS"] || $arFeature["ALLOW_SETTINGS"]))
					{
						?>onMouseOver="jsMI_<?=$arFeature["feature"]?>.StartTrackMouse(this)"<?
					}
					?> style="position: relative;"><nobr>
						<?
						if($arResult["PERMISSION"] > "R"):
							?><a href="<?=$arFeature["Url"]?>" onClick="if (!window.___BXMenu.bWasDraggedRecently) { location.href='<?=$arFeature["Url"]?>'; } return BX.PreventDefault(arguments[0]||window.event);" class="bx-sm-header" style="cursor:pointer;" onmousedown="return getMenuHolder('<?=AddSlashes($arResult["ID"])?>').DragStart('<?=$arFeature["feature"]?>', event)"><?
						else:
							?><a href="<?=$arFeature["Url"]?>" class="bx-sm-header"><?
						endif;
						echo $arFeature["FeatureName"];
						?></a><?
						if($arResult["PERMISSION"] > "R" && (!$arFeature["NOPARAMS"] || $arFeature["ALLOW_SETTINGS"])):
							?><div class="bx-sm-actions" id="act_<?=$arFeature["feature"]?>">
							<div class="bx-sm-actions-left"></div>
							<a class="bx-sm-settings bx-sm-actions-button" href="javascript:void(0)" onclick="return getMenuHolder('<?=AddSlashes($arResult["ID"])?>').ShowSettings('<?=$arFeature["feature"]?>', 'get_settings', '<?=$arFeature["feature"]?>');" title="<?=GetMessage("SONET_SM_TDEF_SETTINGS")?>"></a>
							<div class="bx-sm-actions-separator"></div>
							<a id="act_remove_<?=$arFeature["feature"]?>" class="bx-sm-remove bx-sm-actions-button" href="javascript:void(0)" onclick="return getMenuHolder('<?=AddSlashes($arResult["ID"])?>').Delete('<?=$arFeature["feature"]?>');" title="<?=GetMessage("SONET_SM_TDEF_OFF")?>"></a>
							<div class="bx-sm-actions-right"></div>
							</div><?
						endif;
						?>
					</nobr></div>
					</td>
				</tr>
				</table>
				<div style="display:none;" id="d<?=$arFeature["feature"]?>" class="bx-sm-dotted"></div>
				</div>
				</td>
			</tr><?
		endforeach;
		?>
		<tr>
			<td class="bx-sm-ddmenu-bottom-left"><div class="bx-sm-ddmenu-bottom-right"></div></td>
		</tr>
		</table>
		</div></div>
		</td>
	</tr>
	</table></div><?
	endif;
	?></td>
	<?if (count($allFeaturesAdd) > 0):?>
	<td width="0%" class="bx-sm-separator"><div style="width: 3px;"></div></td>
	<?
	endif;

	if($arResult["PERMISSION"] > "R"):
		if(count($allFeaturesInactive) > 0):
			?><td width="0%" valign="top"><div class="ddmenu" onMouseOver="getMenuHolder('<?=AddSlashes($arResult["ID"])?>').ShowHolder('ddmenuinact', this);" onMouseMove="getMenuHolder('<?=AddSlashes($arResult["ID"])?>').ShowHolder('ddmenuinact', this);" onClick="getMenuHolder('<?=AddSlashes($arResult["ID"])?>').ShowHolder('ddmenuinact', this);">
			<table cellspacing="0" cellpadding="0">
			<tr>
				<td class="bx-sm-feature-noselect"><a style="display: block; position: relative; z-index: 300;" href="" onClick="return false;" class="bx-sm-parent-button" title="<?=GetMessage("SONET_SM_TDEF_INACTIVE")?>"><div class="bx-sm-header-button"><div class="bx-sm-item-inactive"></div></div></a></td>
			</tr>
			</table>
			<div id="ddmenuinact" class="ddmenu-inactive">
			<table id="MenuHolderInactive_<?=$arResult["ID"]?>" width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<td class="bx-sm-ddmenu-top-left"><div class="bx-sm-ddmenu-top-right"></div></td>
			</tr>
			<?
			foreach ($allFeaturesInactive as $j => $arFeature):

				$ind = $j + 1 + count($allFeaturesShow) + count($allFeaturesInactive);
				?><tr>
					<td class="bx-sm-ddmenu-middle-left"><div class="bx-sm-ddmenu-middle-right" <?if ($j == (count($allFeaturesInactive)-1)){ echo 'style="padding-bottom: 5px;"'; } ?>><table cellspacing="0" cellpadding="0" class="bx-sm-feature-noselect">
					<tr>
						<td nowrap>
						<div class="bx-sm-parent">
						<div class="bx-sm-header" onclick="return getMenuHolder('<?=AddSlashes($arResult["ID"])?>').Add('<?=$arFeature["feature"]?>');" title="<?=GetMessage("SONET_SM_TDEF_ON")?>" style="cursor:pointer;"><nobr><a><?=$arFeature["FeatureName"]?></nobr></a></div>
						</div>
						</td>
					</tr>
					</table>
					</div>
					</td>
				</tr><?
			endforeach;
			?>
			<tr>
				<td class="bx-sm-ddmenu-bottom-left"><div class="bx-sm-ddmenu-bottom-right"></div></td>
			</tr>
			</table>
			</div>
			</div>
			</td>
			<td width="0%" class="bx-sm-separator"><div style="width: 3px;"></div></td><?
		endif;
		?><td width="0%" valign="top">
		<table cellspacing="0" cellpadding="0">
		<tr>
			<td class="bx-sm-feature-noselect" id="bx_sm_settings"><a style="display: block; position: relative;" href="" onMouseOver="getMenuHolder('<?=AddSlashes($arResult["ID"])?>').ClearButtonOver(); return false;" onMouseMove="getMenuHolder('<?=AddSlashes($arResult["ID"])?>').ClearButtonOver(); return false;" onMouseOut="getMenuHolder('<?=AddSlashes($arResult["ID"])?>').ClearButtonOut(); return false;" onClick="getMenuHolder('<?=AddSlashes($arResult["ID"])?>').ShowMenuSettings(); return false;" class="bx-sm-parent-button"><div class="bx-sm-header-button" style="cursor:pointer;" title="<?=GetMessage("SONET_SM_TDEF_MENU_SETTINGS")?>"><div class="bx-sm-item-settings"></div></div></a></td>
		</tr>
		</table>
		</td>
		<?
	endif;
	?>
	<td width="0%" class="bx-sm-rightshadow"><div style="width: 13px;"></div></td>
	<td width="0%" class="bx-sm-rightline"><div style="width: 31px;"></div></td>
	</tr>
	</table>
	</div>
	</form>
	<?
}
?>