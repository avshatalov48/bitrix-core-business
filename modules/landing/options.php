<?php
use \Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Seo\Engine\Bitrix;

if (!\Bitrix\Main\Loader::includeModule('landing'))
{
	return;
}

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

$moduleId = 'landing';
$mid = $request->get('mid');
$backUrl = $request->get('back_url_settings');
$docRoot = Manager::getDocRoot();
$postRight = $APPLICATION->GetGroupRight('main');

if ($postRight >= 'R'):

	IncludeModuleLangFile($docRoot . '/bitrix/modules/main/options.php');
	Loc::loadMessages(__FILE__);

	$allOptions[] = array(
		'site_template_id',
		Loc::getMessage('LANDING_OPT_SITE_TEMPLATE_ID') . ':',
		array('text', 32)
	);

	// paths for sites
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
		$allOptions[] = array(
			'pub_path_' . $row['LID'],
			Loc::getMessage('LANDING_OPT_PUB_PATH') .
			' (' . $row['NAME'] . '[' . $row['LID'] . ']' . ')' . ':',
			array('text', 32),
			\Bitrix\Landing\Manager::PUBLICATION_PATH
		);
	}

	$tabControl = new \CAdmintabControl('tabControl', array(
		array('DIV' => 'edit1', 'TAB' => Loc::getMessage('MAIN_TAB_SET'), 'ICON' => ''),
		array('DIV' => 'edit2', 'TAB' => Loc::getMessage('MAIN_TAB_RIGHTS'), 'ICON' => '')
	));

	if (
		strlen($Update.$Apply.$RestoreDefaults) > 0 &&
		($postRight=='W' || $postRight=='X') &&
		\check_bitrix_sessid()
	)
	{
		if (strlen($RestoreDefaults)>0)
		{
			\COption::RemoveOption($moduleId);
		}
		else
		{
			foreach ($allOptions as $arOption)
			{
				$name = $arOption[0];
				if ($arOption[2][0] == 'text-list')
				{
					$val = '';
					for ($j = 0; $j < count($$name); $j++)
					{
						if (strlen(trim(${$name}[$j])) > 0)
						{
							$val .= ($val <> '' ? ',':'') . trim(${$name}[$j]);
						}
					}
				}
				elseif ($arOption[2][0] == 'doubletext')
				{
					$val = ${$name.'_1'} . 'x' . ${$name.'_2'};
				}
				elseif ($arOption[2][0] == 'selectbox')
				{
					$val = '';
					for ($j=0; $j<count($$name); $j++)
					{
						if (strlen(trim(${$name}[$j])) > 0)
						{
							$val .= ($val <> '' ? ',':'') . trim(${$name}[$j]);
						}
					}
				}
				else
				{
					$val = $$name;
				}

				if ($arOption[2][0] == 'checkbox' && $val<>'Y')
				{
					$val = 'N';
				}

				\COption::SetOptionString($moduleId, $name, $val);
			}
		}

		$Update = $Update . $Apply;

		ob_start();
		require_once($docRoot . '/bitrix/modules/main/admin/group_rights.php');
		ob_end_clean();

		if (strlen($Update)>0 && strlen($backUrl)>0)
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

	?><form method="post" action="<?= $APPLICATION->GetCurPage()?>?mid=<?= urlencode($mid)?>&amp;lang=<?= LANGUAGE_ID?>"><?
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	foreach($allOptions as $Option):
		$type = $Option[2];
		$val = \COption::getOptionString(
			$moduleId,
			$Option[0],
			isset($Option[3]) ? $Option[3] : null
		);
		?>
		<tr>
			<td valign="top" width="40%"><?
				if ($type[0]=='checkbox')
				{
					echo '<label for="' . \htmlspecialcharsbx($Option[0]) . '">'.$Option[1].'</label>';
				}
				else
				{
					echo $Option[1];
				}
		?></td>
		<td valign="middle" width="60%"><?
			if ($type[0] == 'checkbox'):
				?><input type="checkbox" name="<?echo \htmlspecialcharsbx($Option[0])?>" id="<?echo \htmlspecialcharsbx($Option[0])?>" value="Y"<?if($val == 'Y') echo ' checked="checked"';?> /><?
			elseif ($type[0] == 'text'):
				?><input type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo \htmlspecialcharsbx($val)?>" name="<?echo \htmlspecialcharsbx($Option[0])?>" /><?
			elseif ($type[0] == 'doubletext'):
				list($val1, $val2) = explode('x', $val);
				?><input type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo \htmlspecialcharsbx($val1)?>" name="<?echo \htmlspecialcharsbx($Option[0].'_1')?>" /><?
				?><input type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo \htmlspecialcharsbx($val2)?>" name="<?echo \htmlspecialcharsbx($Option[0].'_2')?>" /><?
			elseif ($type[0] == 'textarea'):
				?><textarea rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo \htmlspecialcharsbx($Option[0])?>"><?echo \htmlspecialcharsbx($val)?></textarea><?
			elseif ($type[0] == 'text-list'):
				$aVal = explode(",", $val);
				for($j=0; $j<count($aVal); $j++):
					?><input type="text" size="<?echo $type[2]?>" value="<?echo \htmlspecialcharsbx($aVal[$j])?>" name="<?echo \htmlspecialcharsbx($Option[0]).'[]'?>" /><br /><?
				endfor;
				for($j=0; $j<$type[1]; $j++):
					?><input type="text" size="<?echo $type[2]?>" value="" name="<?echo \htmlspecialcharsbx($Option[0]).'[]'?>" /><br /><?
				endfor;
			elseif ($type[0] == 'selectbox'):
				$arr = $type[1];
				$arr_keys = array_keys($arr);
				$arVal = explode(',', $val);
				?><select name="<?echo \htmlspecialcharsbx($Option[0])?>[]"<?= $type[2]?>><?
					for($j = 0; $j < count($arr_keys); $j++):
						?><option value="<?echo $arr_keys[$j]?>"<?if(in_array($arr_keys[$j], $arVal))echo ' selected="selected"'?>><?echo \htmlspecialcharsbx($arr[$arr_keys[$j]])?></option><?
					endfor;
					?></select><?
			endif;
			echo $Option[4];?>
		</td>
		<?
	endforeach;

	$tabControl->BeginNextTab();
	require_once($docRoot . '/bitrix/modules/main/admin/group_rights.php');

	$tabControl->Buttons();
	?>
	<input <?if ($postRight < 'W') echo 'disabled="disabled"' ?> type="submit" name="Update" value="<?= Loc::getMessage('MAIN_SAVE')?>" title="<?= Loc::getMessage('MAIN_OPT_SAVE_TITLE')?>" />
	<input <?if ($postRight < 'W') echo 'disabled="disabled"' ?> type="submit" name="Apply" value="<?= Loc::getMessage('MAIN_OPT_APPLY')?>" title="<?= Loc::getMessage('MAIN_OPT_APPLY_TITLE')?>" />
	<?if (strlen($backUrl) > 0):?>
		<input <?if ($postRight < 'W') echo 'disabled="disabled"' ?> type="button" name="Cancel" value="<?= Loc::getMessage('MAIN_OPT_CANCEL')?>" title="<?= Loc::getMessage('MAIN_OPT_CANCEL_TITLE')?>" onclick="window.location='<?echo \htmlspecialcharsbx(CUtil::addslashes($backUrl))?>'" />
		<input type="hidden" name="back_url_settings" value="<?=\htmlspecialcharsbx($backUrl)?>" />
	<?endif?>
	<input <?if ($postRight < 'W') echo 'disabled="disabled"' ?> type="submit" name="RestoreDefaults" title="<?= Loc::getMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" onclick="confirm('<?echo AddSlashes(Loc::getMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING'))?>')" value="<?echo Loc::getMessage('MAIN_RESTORE_DEFAULTS')?>" />
	<?=bitrix_sessid_post();?>
	<?$tabControl->End();?>
	</form>

<?endif;?>