<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Loader;
use Bitrix\Catalog;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Main\Localization\Loc;

$module_id = "catalog";

Loc::loadMessages(__FILE__);
if (!Loader::includeModule('catalog'))
{
	CAdminMessage::ShowMessage(GetMessage("CAT_CATALOG_MODULE_IS_EMPTY"));
	die();
}

if (Bitrix\Catalog\Access\AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ)):

	if ($ex = $APPLICATION->GetException())
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

		$strError = $ex->GetString();
		ShowError($strError);

		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		die();
	}

	$arIBlock = [
		'' => GetMessage('CAT_1CE_IBLOCK_ID_EMPTY'),
	];
	$iterator = Catalog\CatalogIblockTable::getList([
		'select' => [
			'IBLOCK_ID',
			'NAME' => 'IBLOCK.NAME',
		],
		'filter' => [
			'=PRODUCT_IBLOCK_ID' => 0,
		],
		'order' => [
			'IBLOCK_ID' => 'ASC',
		],
	]);
	while($row = $iterator->fetch())
	{
		$arIBlock[$row['IBLOCK_ID']] = '[' . $row['IBLOCK_ID'] . '] ' . $row['NAME'];
	}
	unset($row, $iterator);

	$arUGroupsEx = [];
	$dbUGroups = CGroup::GetList();
	while($arUGroups = $dbUGroups -> Fetch())
	{
		$arUGroupsEx[$arUGroups["ID"]] = $arUGroups["NAME"];
	}

	$arAllOptions = array(
		array("1CE_IBLOCK_ID", GetMessage("CAT_1CE_IBLOCK_ID"), "", Array("list", $arIBlock)),
		array("1CE_ELEMENTS_PER_STEP", GetMessage("CAT_1CE_ELEMENTS_PER_STEP"), 1, Array("text", 5)),
		array("1CE_INTERVAL", GetMessage("CAT_1CE_INTERVAL"), "30", Array("text", 20)),
		array("1CE_GROUP_PERMISSIONS", GetMessage("CAT_1CE_GROUP_PERMISSIONS"), "-", Array("mlist", 5, $arUGroupsEx)),
		array("1CE_USE_ZIP", GetMessage("CAT_1CE_USE_ZIP"), "Y", Array("checkbox")),
	);

	if(
		$_SERVER['REQUEST_METHOD'] === "POST"
		&& $Update !== ''
		&& $USER->CanDoOperation('edit_php')
		&& check_bitrix_sessid()
	)
	{
		for ($i = 0, $intCount = count($arAllOptions); $i < $intCount; $i++)
		{
			$name = $arAllOptions[$i][0];
			$val = $_REQUEST[$name];
			if ($arAllOptions[$i][3][0] === "checkbox" && $val !== "Y")
			{
				$val = "N";
			}
			if ($arAllOptions[$i][3][0] === "mlist" && is_array($val))
			{
				$val = implode(",", $val);
			}

			COption::SetOptionString("catalog", $name, $val, $arAllOptions[$i][1]);
		}
		return;
	}

	foreach ($arAllOptions as $Option):
		$val = COption::GetOptionString('catalog', $Option[0], $Option[2]);
		$type = $Option[3];
		?>
		<tr>
			<td <?= ('textarea' === $type[0] || 'mlist' === $type[0] ? 'valign="top"' : ''); ?> style="width: 40%;">
				<?php
				if ($type[0] === 'checkbox')
				{
					echo "<label for=\"".htmlspecialcharsbx($Option[0])."\">".$Option[1]."</label>";
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
					<input type="checkbox" name="<?= htmlspecialcharsbx($Option[0]); ?>" id="<?= htmlspecialcharsbx($Option[0]); ?>" value="Y"<?= ($val === 'Y' ? ' checked' : ''); ?> onclick="Check(this.id);">
					<?php
				elseif ($type[0] === 'text'):
					?>
					<input type="text" size="<?= $type[1]; ?>" maxlength="255" value="<?= htmlspecialcharsbx($val); ?>" name="<?= htmlspecialcharsbx($Option[0]); ?>" id="<?= htmlspecialcharsbx($Option[0]); ?>">
					<?php
				elseif ($type[0] === 'textarea'):
					?>
					<textarea rows="<?= $type[1]; ?>" cols="<?= $type[2]; ?>" name="<?= htmlspecialcharsbx($Option[0]); ?>" id="<?= htmlspecialcharsbx($Option[0]); ?>"><?= htmlspecialcharsbx($val); ?></textarea>
					<?php
				elseif ($type[0] === 'list'):
					?>
					<select name="<?= htmlspecialcharsbx($Option[0]); ?>" id="<?= htmlspecialcharsbx($Option[0]); ?>">
						<?php
						foreach($type[1] as $key => $value):
							?>
							<option value="<?= htmlspecialcharsbx($key); ?>"<?= ($val==$key ? ' selected' : ''); ?>><?= htmlspecialcharsbx($value); ?></option>
							<?php
						endforeach;
						?>
					</select>
					<?php
				elseif ($type[0] === 'mlist'):
					$val = explode(',', $val);
					?>
					<select multiple name="<?= htmlspecialcharsbx($Option[0]); ?>[]" size="<?= $type[1]; ?>" id="<?= htmlspecialcharsbx($Option[0]); ?>">
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
	endforeach;
	if (!$USER->CanDoOperation('edit_php')):
		?><tr><td colspan="2"><?php
			echo BeginNote();
			echo GetMessage('CAT_1CE_SETTINGS_SAVE_DENIED');
			echo EndNote();
			?></td></tr><?php
	endif;
endif;
