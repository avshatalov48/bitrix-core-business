<?php
$module_id = 'landing';

use \Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\SiteTemplateTable;

if (!\Bitrix\Main\Loader::includeModule('landing'))
{
	return;
}

/** @var \CMain $APPLICATION */

// vars
$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();
$mid = $request->get('mid');
$backUrl = $request->get('back_url_settings');
$docRoot = Manager::getDocRoot();
$postRight = $APPLICATION->GetGroupRight($module_id);

// lang
IncludeModuleLangFile($docRoot . '/bitrix/modules/main/options.php');
Loc::loadMessages(__FILE__);

// local function for build iblocks tree
$getIblocksTree = function()
{
	static $iblocks = null;

	if ($iblocks !== null)
	{
		return $iblocks;
	}

	$iblocks = [];
	if (\Bitrix\Main\Loader::includeModule('iblock'))
	{
		// first gets types
		$iblockTypes = [];
		$res = \CIBlockType::getList();
		while($row = $res->fetch())
		{
			if ($typeLang = \CIBlockType::getByIDLang($row['ID'], LANG))
			{
				$iblockTypes[$typeLang['IBLOCK_TYPE_ID']] = [
					'NAME' => $typeLang['NAME'],
					'SORT' => $typeLang['SORT']
				];
			}
		}

		// and iblocks then
		$res = \CIBlock::getList(['sort' => 'asc']);
		while ($row = $res->GetNext(true, false))
		{
			if (!isset($iblocks[$row['IBLOCK_TYPE_ID']]))
			{
				$iblocks[$row['IBLOCK_TYPE_ID']] = [
					'ID' => $row['IBLOCK_TYPE_ID'],
					'NAME' => $iblockTypes[$row['IBLOCK_TYPE_ID']]['NAME'],
					'SORT' => $iblockTypes[$row['IBLOCK_TYPE_ID']]['SORT'],
					'ITEMS' => []
				];
			}
			$iblocks[$row['IBLOCK_TYPE_ID']]['ITEMS'][] = [
				'ID' => $row['ID'],
				'NAME' => $row['NAME']
			];
		}

		// sorting by sort
		usort($iblocks,
		  	function($a, $b)
			{
				if ($a['SORT'] == $b['SORT'])
				{
					return 0;
				}
				return ($a['SORT'] < $b['SORT']) ? -1 : 1;
			}
		);

		return $iblocks;
	}
};

if ($postRight >= 'R'):

	// sites list
	$sites = [];
	$res = \Bitrix\Main\SiteTable::getList(array(
		'select' => array(
			'*'
		),
		'filter' => array(
			'ACTIVE' => 'Y'
		),
		'order' => array(
			'SORT' => 'ASC'
		)
	));
	while ($row = $res->fetch())
	{
		$row['NAME']  = \htmlspecialcharsbx($row['NAME']);
		$sites[] = $row;
	}

	// site templates
	$allOptions[] = array(
		'site_template_id',
		Loc::getMessage('LANDING_OPT_SITE_TEMPLATE_ID') . ':',
		array('text', 32)
	);
	$allOptions[] = array(
		'header',
		Loc::getMessage('LANDING_OPT_SITE_TEMPLATE_ID_SITES')
	);
	foreach ($sites as $row)
	{
		$allOptions[] = array(
			'site_template_id_' . $row['LID'],
			$row['NAME'] . ' [' . $row['LID'] . ']:',
			array('text', 32)
		);
	}

	// paths for sites
	$allOptions[] = array(
		'header',
		Loc::getMessage('LANDING_OPT_PUB_PATH_HEADER'),
		Loc::getMessage('LANDING_OPT_PUB_PATH_HELP')
	);
	foreach ($sites as $row)
	{
		$allOptions[] = array(
			'pub_path_' . $row['LID'],
			$row['NAME'] . ' [' . $row['LID'] . ']:',
			array('text', 32),
			\Bitrix\Landing\Manager::getPublicationPathConst()
		);
	}

	// other options
	$allOptions[] = array(
		'header',
		Loc::getMessage('LANDING_OPT_OTHER')
	);
	$allOptions[] = array(
		'google_images_key',
		Loc::getMessage('LANDING_OPT_GOOGLE_IMAGES_KEY') . ':',
		array('text', 32)
	);
	if (Manager::isB24())
	{
		$allOptions[] = array(
			'portal_url',
			Loc::getMessage('LANDING_OPT_PORTAL_URL') . ' (host[:port]):',
			array('text', 32)
		);
	}
	$allOptions[] = array(
		'deleted_lifetime_days',
		Loc::getMessage('LANDING_OPT_DELETED_LIFETIME_DAYS') . ':',
		array('text', 4)
	);
	$allOptions[] = [
		'history_lifetime_days',
		Loc::getMessage('LANDING_OPT_HISTORY_LIFETIME') . ':',
		['text', 4]
	];
	if (Manager::isB24())
	{
		$allOptions[] = array(
			'rights_extended_mode',
			Loc::getMessage('LANDING_OPT_RIGHTS_EXTENDED_MODE') . ':',
			array('checkbox')
		);
	}
	$allOptions[] = array(
		'public_hook_on_save',
		Loc::getMessage('LANDING_OPT_PUBLIC_HOOK_ON_SAVE') . ':',
		array('checkbox')
	);
	$allOptions[] = array(
		'allow_svg_content',
		Loc::getMessage('LANDING_OPT_ALLOW_SVG_CONTENT') . ':',
		array('checkbox')
	);
	/*$allOptions[] = array(
		'strict_verification_update',
		Loc::getMessage('LANDING_OPT_STRICT_VERIFICATION_UPDATE') . ':',
		array('checkbox')
	);*/
	$allOptions[] = array(
		'source_iblocks',
		Loc::getMessage('LANDING_OPT_SOURCE_IBLOCKS') . ':',
		array(
			'selectboxtree',
			$getIblocksTree(),
			'multiple="multiple" size="10"'
		)
	);

	// tabs
	$tabControl = new \CAdmintabControl('tabControl', array(
		array('DIV' => 'edit1', 'TAB' => Loc::getMessage('MAIN_TAB_SET'), 'ICON' => ''),
		array('DIV' => 'edit2', 'TAB' => Loc::getMessage('MAIN_TAB_RIGHTS'), 'ICON' => '')
	));

	$Update = $Update ?? '';
	$Apply = $Apply ?? '';

	// post save
	if (
		$Update . $Apply <> '' &&
		($postRight=='W' || $postRight=='X') &&
		\check_bitrix_sessid()
	)
	{
		foreach ($allOptions as $arOption)
		{
			if ($arOption[0] == 'header')
			{
				continue;
			}
			$name = $arOption[0];
			if ($arOption[2][0] == 'text-list')
			{
				$val = '';
				for ($j = 0; $j < count($$name); $j++)
				{
					if (trim(${$name}[$j]) <> '')
					{
						$val .= ($val <> '' ? ',':'') . trim(${$name}[$j]);
					}
				}
			}
			elseif ($arOption[2][0] == 'doubletext')
			{
				$val = ${$name.'_1'} . 'x' . ${$name.'_2'};
			}
			elseif (
				$arOption[2][0] == 'selectbox' ||
				$arOption[2][0] == 'selectboxtree'
			)
			{
				$val = '';
				if (isset($$name))
				{
					for ($j=0; $j<count($$name); $j++)
					{
						if (trim(${$name}[$j]) <> '')
						{
							$val .= ($val <> '' ? ',':'') . trim(${$name}[$j]);
						}
					}
				}
			}
			else
			{
				$val = $$name ?? '';
			}

			if ($arOption[2][0] == 'checkbox' && $val<>'Y')
			{
				$val = 'N';
			}

			$val = trim($val);

			// set new references site <> templates
			$prefix = 'site_template_id_';
			if ($arOption[0] == 'site_template_id')// base template
			{
				$valOld = trim(\COption::getOptionString(
					$module_id,
					'site_template_id'
				));
				if (!$val)
				{
					$val = $valOld;
				}
				if ($valOld != $val)
				{
					$res = SiteTemplateTable::getList(array(
						'filter' => array(
							'=TEMPLATE' => $valOld
						)
					));
					while ($row = $res->fetch())
					{
						SiteTemplateTable::update($row['ID'], [
								'TEMPLATE' => $val
							]
						);
					}
				}
			}
			elseif (strpos($arOption[0], $prefix) === 0)// individual templates
			{
				$valDefault = trim(\COption::getOptionString(
					$module_id,
					'site_template_id'
				));
				$valOld = \COption::getOptionString(
					$module_id,
					$arOption[0]
				);
				if ($valOld != $val)
				{
					$siteId = substr($arOption[0], strlen($prefix));
					$res = SiteTemplateTable::getList(array(
						'filter' => array(
							'=SITE_ID' => $siteId,
							'=TEMPLATE' => $valOld ? $valOld : $valDefault
						)
 					));
					while ($row = $res->fetch())
					{
						SiteTemplateTable::update($row['ID'], [
								'TEMPLATE' => $val ? $val : $valDefault
							]
						);
					}
				}
			}
			elseif (!$val && strpos($name, 'pub_path_') === 0)
			{
				$val = '/';
			}

			\COption::setOptionString($module_id, $name, $val);
		}

		$Update = $Update . $Apply;

		// access settings save
		ob_start();
		require_once($docRoot . '/bitrix/modules/main/admin/group_rights.php');
		ob_end_clean();

		if ($Update <> '' && $backUrl <> '')
		{
			\LocalRedirect($backUrl);
		}
		else
		{
			\LocalRedirect(
				$APPLICATION->GetCurPage() .
				'?mid=' . urlencode($mid) .
				'&lang=' . urlencode(LANGUAGE_ID) .
				'&back_url_settings=' . urlencode($backUrl) .
				'&' . $tabControl->ActiveTabParam());
		}
	}

	?><form method="post" action="<?= $APPLICATION->GetCurPage()?>?mid=<?= urlencode($mid)?>&amp;lang=<?= LANGUAGE_ID?>"><?php
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	foreach($allOptions as $Option):
		if ($Option[0] == 'header')
		{
			?>
			<tr class="heading">
				<td colspan="2">
					<?= $Option[1];?>
				</td>
			</tr>
			<?php if (isset($Option[2])):?>
			<tr>
				<td></td>
				<td>
					<?php
					echo BeginNote();
					echo $Option[2];
					echo EndNote();
					?>
				</td>
			</tr>
			<?php
			endif;
			continue;
		}
		$type = $Option[2];
		$val = \COption::getOptionString(
			$module_id,
			$Option[0],
			isset($Option[3]) ? $Option[3] : null
		);
		?>
		<tr>
			<td valign="top" width="40%"><?php
				if ($type[0]=='checkbox')
				{
					echo '<label for="' . \htmlspecialcharsbx($Option[0]) . '">'.$Option[1].'</label>';
				}
				else
				{
					echo $Option[1];
				}
		?></td>
		<td valign="middle" width="60%"><?php
			if ($type[0] == 'checkbox'):
				?><input type="checkbox" name="<?= \htmlspecialcharsbx($Option[0])?>" id="<?= \htmlspecialcharsbx($Option[0])?>" value="Y"<?php if($val == 'Y') echo ' checked="checked"';?> /><?php
			elseif ($type[0] == 'text'):
				?><input type="text" size="<?= $type[1]?>" maxlength="255" value="<?= \htmlspecialcharsbx($val)?>" name="<?= \htmlspecialcharsbx($Option[0])?>" /><?php
			elseif ($type[0] == 'doubletext'):
				list($val1, $val2) = explode('x', $val);
				?><input type="text" size="<?= $type[1]?>" maxlength="255" value="<?= \htmlspecialcharsbx($val1)?>" name="<?= \htmlspecialcharsbx($Option[0].'_1')?>" /><?php
				?><input type="text" size="<?= $type[1]?>" maxlength="255" value="<?= \htmlspecialcharsbx($val2)?>" name="<?= \htmlspecialcharsbx($Option[0].'_2')?>" /><?php
			elseif ($type[0] == 'textarea'):
				?><textarea rows="<?= $type[1]?>" cols="<?= $type[2]?>" name="<?= \htmlspecialcharsbx($Option[0])?>"><?= \htmlspecialcharsbx($val)?></textarea><?php
			elseif ($type[0] == 'text-list'):
				$aVal = explode(",", $val);
				for($j=0; $j<count($aVal); $j++):
					?><input type="text" size="<?= $type[2]?>" value="<?= \htmlspecialcharsbx($aVal[$j])?>" name="<?= \htmlspecialcharsbx($Option[0]).'[]'?>" /><br /><?php
				endfor;
				for($j=0; $j<$type[1]; $j++):
					?><input type="text" size="<?= $type[2]?>" value="" name="<?= \htmlspecialcharsbx($Option[0]).'[]'?>" /><br /><?php
				endfor;
			elseif ($type[0] == 'selectbox'):
				$arr = $type[1];
				$arr_keys = array_keys($arr);
				$currValue = explode(',', $val);
				?><select name="<?= \htmlspecialcharsbx($Option[0])?>[]"<?= $type[2]?>><?php
					for($j = 0; $j < count($arr_keys); $j++):
						?><option value="<?= $arr_keys[$j]?>"<?php if(in_array($arr_keys[$j], $currValue))echo ' selected="selected"'?>><?= \htmlspecialcharsbx($arr[$arr_keys[$j]])?></option><?php
					endfor;
					?></select><?php
			elseif ($type[0] == 'selectboxtree'):
				$arr = $type[1];
				$currValue = explode(',', $val);

				$output = '<select name="'.\htmlspecialcharsbx($Option[0]).'[]"'.$type[2].'>';
				$output .= '<option></option>';
				foreach ($getIblocksTree() as $rowType)
				{
					$strIBlocksCpGr = '';
					foreach ($rowType['ITEMS'] as $rowIb)
					{
						if (in_array($rowIb['ID'], $currValue))
						{
							$sel = ' selected="selected"';
						}
						else
						{
							$sel = '';
						}
						$strIBlocksCpGr .= '<option value="' . $rowIb['ID'] . '"' . $sel . '>' .
										   		$rowIb['NAME'] .
										   '</option>';
					}
					if ($strIBlocksCpGr != '')
					{
						$output .= '<optgroup label="'.$rowType['NAME'].'">';
						$output .= $strIBlocksCpGr;
						$output .= '</optgroup>';
					}
				}
				$output .= '</select>';
				echo $output;
			endif;
			echo $Option[4] ?? '';?>
		</td>
		<?php
	endforeach;

	// access tab
	$tabControl->BeginNextTab();
	require_once($docRoot . '/bitrix/modules/main/admin/group_rights.php');

	$tabControl->Buttons();
	?>
	<input <?php if ($postRight < 'W') echo 'disabled="disabled"' ?> type="submit" name="Update" value="<?= Loc::getMessage('MAIN_SAVE')?>" title="<?= Loc::getMessage('MAIN_OPT_SAVE_TITLE')?>" />
	<input <?php if ($postRight < 'W') echo 'disabled="disabled"' ?> type="submit" name="Apply" value="<?= Loc::getMessage('MAIN_OPT_APPLY')?>" title="<?= Loc::getMessage('MAIN_OPT_APPLY_TITLE')?>" />
	<?php if ($backUrl <> ''):?>
		<input <?php if ($postRight < 'W') echo 'disabled="disabled"' ?> type="button" name="Cancel" value="<?= Loc::getMessage('MAIN_OPT_CANCEL')?>" title="<?= Loc::getMessage('MAIN_OPT_CANCEL_TITLE')?>" onclick="window.location='<?= \htmlspecialcharsbx(CUtil::addslashes($backUrl))?>'" />
		<input type="hidden" name="back_url_settings" value="<?=\htmlspecialcharsbx($backUrl)?>" />
	<?php endif?>
	<?=bitrix_sessid_post()?>
	<?php $tabControl->End()?>
	</form>

<?php endif;
