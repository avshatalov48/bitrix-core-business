<?php
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Catalog\Access\AccessController,
	Bitrix\Catalog\Access\ActionDictionary,
	Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$module_id = "catalog";

Loc::loadMessages(__FILE__);
if (!Loader::includeModule('catalog'))
{
	CAdminMessage::ShowMessage(GetMessage("CAT_1C_CATALOG_MODULE_IS_EMPTY"));
	\Bitrix\Main\Application::getInstance()->end();
}

if (AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ)) :

	if ($ex = $APPLICATION->GetException())
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
		ShowError($ex->GetString());
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		\Bitrix\Main\Application::getInstance()->end();
	}

	if (Loader::includeModule('iblock')):

		$arIBlockType = array(
			"-" => Loc::getMessage("CAT_1C_CREATE"),
		);
		$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
		while ($arr=$rsIBlockType->Fetch())
		{
			if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
				$arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["NAME"];
			unset($ar);
		}
		unset($arr, $rsIBlockType);

		$rsSite = CSite::GetList("sort", "asc", $arFilter=array("ACTIVE" => "Y"));
		$arSites = array(
			"-" => Loc::getMessage("CAT_1C_CURRENT"),
		);
		while ($arSite = $rsSite->GetNext())
			$arSites[$arSite["LID"]] = $arSite["NAME"];
		unset($arSite, $rsSite);

		$arUGroupsEx = Array();
		$dbUGroups = CGroup::GetList();
		while($arUGroups = $dbUGroups -> Fetch())
		{
			$arUGroupsEx[$arUGroups["ID"]] = $arUGroups["NAME"];
		}

		$arAction = array(
			"N" => Loc::getMessage("CAT_1C_NONE"),
			"A" => Loc::getMessage("CAT_1C_DEACTIVATE"),
			"D" => Loc::getMessage("CAT_1C_DELETE"),
		);

		$arBaseOptions = array(
			array("1C_IBLOCK_TYPE", Loc::getMessage("CAT_1C_IBLOCK_TYPE"), "-", array("list", $arIBlockType)),
			array("1C_SITE_LIST", Loc::getMessage("CAT_1C_SITE_LIST"), "-", array("list", $arSites)),
			array("1C_GROUP_PERMISSIONS", Loc::getMessage("CAT_1C_GROUP_PERMISSIONS"), "1", array("mlist", 5, $arUGroupsEx)),
			array("1C_USE_OFFERS", Loc::getMessage("CAT_1C_USE_OFFERS_2"), "N", array("checkbox")),
			array("1C_TRANSLIT_ON_ADD", Loc::getMessage("CAT_1C_TRANSLIT_ON_ADD_2"), "Y", array("checkbox")),
			array("1C_TRANSLIT_ON_UPDATE", Loc::getMessage("CAT_1C_TRANSLIT_ON_UPDATE_2"), "Y", array("checkbox")),
			array("1C_TRANSLIT_REPLACE_CHAR", Loc::getMessage("CAT_1C_TRANSLIT_REPLACE_CHAR"), "_", array("text", 1)),
		);

		$arExtOptions = array(
			array("1C_INTERVAL", Loc::getMessage("CAT_1C_INTERVAL"), "30", array("text", 20)),
			array("1C_FILE_SIZE_LIMIT", Loc::getMessage("CAT_1C_FILE_SIZE_LIMIT"), 200*1024, array("text", 20)),
			array("1C_USE_ZIP", Loc::getMessage("CAT_1C_USE_ZIP"), "Y", array("checkbox")),
			array("1C_USE_CRC", Loc::getMessage("CAT_1C_USE_CRC"), "Y", array("checkbox")),
			array("1C_ELEMENT_ACTION", Loc::getMessage("CAT_1C_ELEMENT_ACTION_2"), "D", array("list", $arAction)),
			array("1C_SECTION_ACTION", Loc::getMessage("CAT_1C_SECTION_ACTION_2"), "D", array("list", $arAction)),
			array("1C_FORCE_OFFERS", Loc::getMessage("CAT_1C_FORCE_OFFERS_2"), "N", array("checkbox")),
			array("1C_USE_IBLOCK_TYPE_ID", Loc::getMessage("CAT_1C_USE_IBLOCK_TYPE_ID"), "N", array("checkbox")),
			array("1C_SKIP_ROOT_SECTION", Loc::getMessage("CAT_1C_SKIP_ROOT_SECTION_2"), "N", array("checkbox")),
			array("1C_DISABLE_CHANGE_PRICE_NAME", Loc::getMessage("CAT_1C_DISABLE_CHANGE_PRICE_NAME"), "N", array("checkbox")),
			array(
				"1C_IBLOCK_CACHE_MODE",
				Loc::getMessage("CAT_1C_IBLOCK_CACHE_MODE"),
				"-",
				array("list", CIBlockCMLImport::getIblockCacheModeList(true))
			),
			array("1C_USE_IBLOCK_PICTURE_SETTINGS", Loc::getMessage("CAT_1C_USE_IBLOCK_PICTURE_SETTINGS"), "N", array("checkbox")),
			array("1C_GENERATE_PREVIEW", Loc::getMessage("CAT_1C_GENERATE_PREVIEW"), "Y", array("checkbox")),
			array("1C_PREVIEW_WIDTH", Loc::getMessage("CAT_1C_PREVIEW_WIDTH"), 100, array("text", 20)),
			array("1C_PREVIEW_HEIGHT", Loc::getMessage("CAT_1C_PREVIEW_HEIGHT"), 100, array("text", 20)),
			array("1C_DETAIL_RESIZE", Loc::getMessage("CAT_1C_DETAIL_RESIZE"), "Y", array("checkbox")),
			array("1C_DETAIL_WIDTH", Loc::getMessage("CAT_1C_DETAIL_WIDTH"), 300, array("text", 20)),
			array("1C_DETAIL_HEIGHT", Loc::getMessage("CAT_1C_DETAIL_HEIGHT"), 300, array("text", 20)),
		);

		$arOptionsDeps = array(
			"catalog_1C_USE_IBLOCK_PICTURE_SETTINGS" => array(
				"catalog_1C_GENERATE_PREVIEW",
				"catalog_1C_PREVIEW_WIDTH",
				"catalog_1C_PREVIEW_HEIGHT",
				"catalog_1C_DETAIL_RESIZE",
				"catalog_1C_DETAIL_WIDTH",
				"catalog_1C_DETAIL_HEIGHT",
			),
		);

		$optionHints = [
			'1C_ELEMENT_ACTION' => Loc::getMessage('CAT_1C_MESS_ONLY_BASE_1C_MODULE'),
			'1C_SECTION_ACTION' => Loc::getMessage('CAT_1C_MESS_ONLY_BASE_1C_MODULE'),
		];

		if ($_SERVER['REQUEST_METHOD'] == "POST" && $Update <> '' && $USER->CanDoOperation('edit_php') && check_bitrix_sessid())
		{
			$arDisableOptions = array();
			foreach ($arOptionsDeps as $option => $subOptions)
			{
				if (isset($_REQUEST[$option]) && (string)$_REQUEST[$option] == 'Y')
				{
					$arDisableOptions = (
						empty($arDisableOptions) ?
						array_fill_keys($subOptions, true) :
						array_merge($arDisableOptions, array_fill_keys($subOptions, true))
					);
				}
			}

			foreach ($arBaseOptions as $Option)
			{
				$name = $Option[0];
				$reqName = 'catalog_'.$name;
				if (isset($_REQUEST[$reqName]) && !isset($arDisableOptions[$reqName]))
				{
					$val = $_REQUEST[$reqName];
					if ($Option[3][0] == "checkbox" && $val != "Y")
						$val = "N";
					if ($Option[3][0] == "mlist" && is_array($val))
						$val = implode(",", $val);
					Main\Config\Option::set('catalog', $name, $val, '');
				}
			}

			foreach ($arExtOptions as $Option)
			{
				$name = $Option[0];
				$reqName = 'catalog_'.$name;
				if (isset($_REQUEST[$reqName]) && !isset($arDisableOptions[$reqName]))
				{
					$val = $_REQUEST[$reqName];
					if ($Option[3][0] == "checkbox" && $val != "Y")
						$val = "N";
					if ($Option[3][0] == "mlist")
						$val = implode(",", $val);
					Main\Config\Option::set('catalog', $name, $val, '');
				}
			}

			return;
		}

		$showExtOptions = false;
		foreach($arExtOptions as $Option)
		{
			$val = Main\Config\Option::get('catalog', $Option[0], $Option[2]);
			if ($val != $Option[2])
				$showExtOptions = true;
		}

		foreach($arBaseOptions as $Option)
		{
			$val = Main\Config\Option::get('catalog', $Option[0], $Option[2]);
			$type = $Option[3];
			$strOptionName = htmlspecialcharsbx("catalog_".$Option[0]);
			?>
		<tr>
			<td <?= ('textarea' == $type[0] || 'mlist' == $type[0] ? 'valign="top"' : ''); ?> style="width: 40%;">
				<?php
				$id = $Option[0];
				if (isset($optionHints[$id]))
				{
					?><span id="hint_<?= $strOptionName; ?>"></span>
					<script>BX.hint_replace(BX('hint_<?=$strOptionName;?>'), '<?=\CUtil::JSEscape($optionHints[$id]); ?>');</script>&nbsp;<?php
				}

				if ($type[0] === 'checkbox')
				{
					echo '<label for="'.$strOptionName.'">'.$Option[1].'</label>';
				}
				else
				{
					echo $Option[1];
				}
				?>:
			</td>
			<td style="width: 60%;">
					<?php
					if ($type[0] === 'checkbox'):
						?>
						<input type="hidden" name="<?= $strOptionName; ?>" id="<?= $strOptionName; ?>_N" value="N">
						<input type="checkbox" name="<?= $strOptionName; ?>" id="<?= $strOptionName; ?>" value="Y"<?= ($val === 'Y' ? ' checked' : ''); ?> onclick="Check(this.id);">
						<?php
					elseif ($type[0] === 'text'):
						?>
						<input type="text" size="<?= $type[1]; ?>" maxlength="255" value="<?= htmlspecialcharsbx($val); ?>" name="<?= $strOptionName; ?>" id="<?= $strOptionName; ?>">
						<?php
					elseif ($type[0] == 'textarea'):
						?>
						<textarea rows="<?= $type[1]; ?>" cols="<?= $type[2]; ?>" name="<?= $strOptionName; ?>" id="<?= $strOptionName; ?>"><?= htmlspecialcharsbx($val); ?></textarea>
						<?php
					elseif ($type[0] === 'list'):
						?>
						<select name="<?= $strOptionName; ?>" id="<?= $strOptionName; ?>">
						<?php
						foreach($type[1] as $key=>$value):
							?>
							<option value="<?= htmlspecialcharsbx($key); ?>"<?= ($val == $key ? ' selected' : ''); ?>><?= htmlspecialcharsbx($value); ?></option>
							<?php
						endforeach;
						?>
						</select>
						<?php
					elseif ($type[0] === 'mlist'):
						$val = explode(",", $val);
						?>
						<select multiple name="<?= $strOptionName; ?>[]" size="<?= $type[1]; ?>" id="<?= $strOptionName; ?>">
						<?php
						foreach($type[2] as $key=>$value):
							?>
							<option value="<?= htmlspecialcharsbx($key); ?>"<?= (in_array($key, $val) ? ' selected' : ''); ?>><?= htmlspecialcharsbx($value); ?></option>
							<?php
						endforeach;
						?>
						</select>
						<?php
					endif;
					?>
			</td>
		</tr>
		<?php
		}
		?>
		<tr class="heading">
			<td id="td_extended_options" colspan="2">
				<?php
				if ($showExtOptions):
					echo Loc::getMessage("CAT_1C_EXTENDED_SETTINGS");
				else:
					?>
					<a class="bx-action-href" href="javascript:showExtOptions()"><?= Loc::getMessage("CAT_1C_EXTENDED_SETTINGS"); ?></a>
					<?php
				endif;
				?>
			</td>
		</tr>
		<?php
		foreach ($arExtOptions as $Option)
		{
			$val = Main\Config\Option::get('catalog', $Option[0], $Option[2]);
			$type = $Option[3];
			$strOptionName = htmlspecialcharsbx("catalog_".$Option[0]);
			?>
		<tr id="tr_<?= htmlspecialcharsbx($Option[0]); ?>"<?= (!$showExtOptions ? ' style="display:none"' : ''); ?>>
			<td <?= ('textarea' === $type[0] || 'mlist' === $type[0] ? 'valign="top"' : ''); ?> style="width: 40%;">
				<?php
				$id = $Option[0];
				if (isset($optionHints[$id]))
				{
				?><span id="hint_<?= $strOptionName; ?>"></span>
				<script>BX.hint_replace(BX('hint_<?=$strOptionName;?>'), '<?=\CUtil::JSEscape($optionHints[$id]); ?>');</script>&nbsp;<?php
				}

				if ($type[0] === 'checkbox')
				{
					echo '<label for="'.$strOptionName.'">'.$Option[1].'</label>';
				}
				else
				{
					echo $Option[1];
				}
				?>:
			</td>
			<td style="width: 60%;">
					<?php
					if ($type[0] === 'checkbox'):
						?>
						<input type="hidden" name="<?= $strOptionName; ?>" id="<?= $strOptionName; ?>_N" value="N">
						<input type="checkbox" name="<?= $strOptionName; ?>" id="<?= $strOptionName; ?>" value="Y"<?= ($val === 'Y' ? ' checked' : ''); ?> onclick="Check(this.id);">
						<?php
					elseif ($type[0] === 'text'):
						?>
						<input type="text" size="<?= $type[1]; ?>" maxlength="255" value="<?= htmlspecialcharsbx($val); ?>" name="<?= $strOptionName; ?>" id="<?= $strOptionName; ?>">
						<?php
					elseif ($type[0] === 'textarea'):
						?>
						<textarea rows="<?= $type[1]; ?>" cols="<?= $type[2]; ?>" name="<?= $strOptionName; ?>" id="<?= $strOptionName; ?>"><?= htmlspecialcharsbx($val); ?></textarea>
						<?php
					elseif ($type[0] === 'list'):
						?>
						<select name="<?= $strOptionName; ?>" id="<?= $strOptionName; ?>">
						<?php
						foreach ($type[1] as $key => $value):
							?>
							<option value="<?= htmlspecialcharsbx($key); ?>"<?= ($val == $key ? ' selected' : ''); ?>><?= htmlspecialcharsbx($value); ?></option>
							<?php
						endforeach;
						?>
						</select>
						<?php
					elseif ($type[0] === 'mlist'):
						$val = explode(",", $val);
						?>
						<select multiple name="<?= $strOptionName; ?>[]" size="<?= $type[1]; ?>" id="<?= $strOptionName; ?>">
						<?php
						foreach ($type[2] as $key => $value):
							?>
							<option value="<?= htmlspecialcharsbx($key); ?>"<?= (in_array($key, $val) ? ' selected' : ''); ?>><?= htmlspecialcharsbx($value); ?></option>
							<?php
						endforeach;
						?>
						</select>
					<?php
					endif;
					?>
			</td>
		</tr>
		<?php
		}
		if (!$USER->CanDoOperation('edit_php'))
		{
			?><tr><td colspan="2"><?php
				echo BeginNote();
				echo GetMessage('CAT_1C_SETTINGS_SAVE_DENIED');
				echo EndNote();
			?></td></tr><?php
		}
		?>
	<script>
	var controls = <?= CUtil::PhpToJSObject($arOptionsDeps); ?>;
	function Check(checkbox)
	{
		var i, mainCheckbox;
		if (!!controls[checkbox] && BX.type.isArray(controls[checkbox]))
		{
			mainCheckbox = BX(checkbox);
			if (!!mainCheckbox)
			{
				for (i = 0;i < controls[checkbox].length; i++)
				{
					if (!!BX(controls[checkbox][i]))
					{
						BX(controls[checkbox][i]).disabled = mainCheckbox.checked;
					}
				}
			}
		}
	}
	var bExtOptions = <?= $showExtOptions? 'true': 'false'; ?>;
	function showExtOptions()
	{
		if (bExtOptions)
		{
		<?php
		foreach($arExtOptions as $Option):
			?>
			BX('<?= CUtil::JSEscape('tr_'.$Option[0]); ?>').style.display = 'none';
			<?php
		endforeach;
		?>
		}
		else
		{
		<?php
		foreach($arExtOptions as $Option):
			?>
			BX('<?= CUtil::JSEscape('tr_'.$Option[0]); ?>').style.display = 'table-row';
			<?php
		endforeach;
		?>
		}
		bExtOptions = !bExtOptions;
		BX.onCustomEvent('onAdminTabsChange');
	}
	BX.ready(function(){
		<?php
		foreach($arOptionsDeps as $key => $value):
		?>
			Check('<?= CUtil::JSEscape($key); ?>');
		<?php
		endforeach;
		?>
	});
	</script>
		<?php
	else:
		CAdminMessage::ShowMessage(Loc::getMessage("CAT_NO_IBLOCK_MOD"));
	endif;
endif;
