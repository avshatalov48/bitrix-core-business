<?php
use Bitrix\Main;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

/** @var CMain $APPLICATION */
/** @var \Bitrix\Main\HttpRequest $request */
$request = Main\Context::getCurrent()->getRequest();

$backUrl = trim((string)$request->get('back_url_settings'));

$module_id = 'perfmon';
$RIGHT = CMain::GetGroupRight($module_id);
if ($RIGHT >= 'R') :
	IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/options.php');
	IncludeModuleLangFile(__FILE__);

	$arAllOptions = [
		['max_display_url', GetMessage('PERFMON_OPTIONS_MAX_DISPLAY_URL'), ['text', 6]],
		['warning_log', GetMessage('PERFMON_OPTIONS_WARNING_LOG'), ['checkbox']],
		['cache_log', GetMessage('PERFMON_OPTIONS_CACHE_LOG'), ['checkbox']],
		['large_cache_log', GetMessage('PERFMON_OPTIONS_LARGE_CACHE_LOG'), ['checkbox'], GetMessage('PERFMON_OPTIONS_LARGE_CACHE_NOTE')],
		['large_cache_size', GetMessage('PERFMON_OPTIONS_LARGE_CACHE_SIZE'), ['text', 6]],
		['sql_log', GetMessage('PERFMON_OPTIONS_SQL_LOG'), ['checkbox']],
		['sql_backtrace', GetMessage('PERFMON_OPTIONS_SQL_BACKTRACE'), ['checkbox']],
		['slow_sql_log', GetMessage('PERFMON_OPTIONS_SLOW_SQL_LOG'), ['checkbox'], GetMessage('PERFMON_OPTIONS_SLOW_SQL_NOTE')],
		['slow_sql_time', GetMessage('PERFMON_OPTIONS_SLOW_SQL_TIME'), ['text', 6]],
	];

	$ormOptions = [
		[
			'enable_tablet_generator',
			GetMessage('PERFMON_OPTIONS_ENABLE_TABLET_GENERATOR'),
			[
				'checkbox',
			],
		],
		GetMessage('PERFMON_OPTIONS_SECTION_GENERATOR_SETTINGS'),
		[
			'tablet_short_aliases',
			GetMessage('PERFMON_OPTIONS_TABLET_SHORT_ALIASES'),
			[
				'checkbox',
			],
		],
		[
			'tablet_object_settings',
			GetMessage('PERFMON_OPTIONS_TABLET_OBJECT_SETTINGS'),
			[
				'checkbox',
			],
		],
		[
			'tablet_use_map_index',
			GetMessage('PERFMON_OPTIONS_TABLET_USE_MAP_INDEX'),
			[
				'checkbox',
			],
		],
		[
			'tablet_validation_closure',
			GetMessage('PERFMON_OPTIONS_TABLET_VALIDATION_CLOSURE'),
			[
				'checkbox',
			],
		],
	];

	$aTabs = [
		['DIV' => 'edit1', 'TAB' => GetMessage('MAIN_TAB_SET'), 'ICON' => 'perfmon_settings', 'TITLE' => GetMessage('MAIN_TAB_TITLE_SET')],
		['DIV' => 'edit3', 'TAB' => GetMessage('PERFMON_TAB_ORM'), 'ICON' => 'perfmon_settings', 'TITLE' => GetMessage('PERFMON_TAB_TITLE_ORM')],
		['DIV' => 'edit2', 'TAB' => GetMessage('MAIN_TAB_RIGHTS'), 'ICON' => 'perfmon_settings', 'TITLE' => GetMessage('MAIN_TAB_TITLE_RIGHTS')],
	];
	$tabControl = new CAdminTabControl('tabControl', $aTabs);

	Loader::includeModule($module_id);

	$action = null;
	if ($request->getPost('RestoreDefaults') !== null)
	{
		$action = 'clear';
	}
	elseif ($request->getPost('Update') !== null)
	{
		$action = 'save';
	}
	elseif ($request->getPost('Apply'))
	{
		$action = 'apply';
	}
	$actionClear = ($action === 'clear');
	$actionSave = ($action === 'save');
	$actionApply = ($action === 'apply');
	if ($request->isPost() && $action !== null && $RIGHT >= 'W' && check_bitrix_sessid())
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/perfmon/prolog.php';

		if ($request->getPost('clear_data') === 'y')
		{
			CPerfomanceComponent::Clear();
			CPerfomanceSQL::Clear();
			CPerfomanceHit::Clear();
			CPerfomanceError::Clear();
			CPerfomanceCache::Clear();
		}

		$active = $request->getPost('ACTIVE');
		if ($active !== null)
		{
			$active = (int)$active;
			CPerfomanceKeeper::SetActive($active > 0, time() + $active);
		}

		if ($actionClear)
		{
			Option::delete('perfmon', []);
		}
		else
		{
			foreach ($arAllOptions as $arOption)
			{
				$name = $arOption[0];
				$val = $request->getPost($name);
				if ($arOption[2][0] == 'checkbox' && $val !== 'Y')
				{
					$val = 'N';
				}
				Option::set('perfmon', $name, $val, '');
			}

			foreach ($ormOptions as $option)
			{
				$name = $option[0];
				$value = $request->getPost($name);
				if ($value === null)
				{
					continue;
				}
				if ($option[2][0] == 'checkbox')
				{
					if ($value !== 'N' && $value !== 'Y')
					{
						continue;
					}
				}
				Option::set('perfmon', $name, $value, '');
			}
			unset($option);
		}

		ob_start();
		$Update = $actionSave . $actionApply;
		require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/group_rights.php';
		ob_end_clean();

		if ($backUrl !== '')
		{
			if ($actionApply || $actionClear)
			{
				LocalRedirect($APPLICATION->GetCurPage()
					. '?mid=' . urlencode($module_id)
					. '&lang=' . urlencode(LANGUAGE_ID)
					. '&back_url_settings=' . urlencode($backUrl)
					. '&' . $tabControl->ActiveTabParam()
				);
			}
			else
			{
				LocalRedirect($_REQUEST['back_url_settings']);
			}
		}
		else
		{
			LocalRedirect(
				$APPLICATION->GetCurPage()
				. '?mid=' . urlencode($module_id)
				. '&lang=' . urlencode(LANGUAGE_ID)
				. '&' . $tabControl->ActiveTabParam()
			);
		}
	}

	?>
	<form method="post" action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($module_id)?>&amp;lang=<?= LANGUAGE_ID?>">
		<?php
		$tabControl->Begin();
		$tabControl->BeginNextTab();
		$arNotes = [];
		foreach ($arAllOptions as $arOption):
			$val = Option::get('perfmon', $arOption[0]);
			$type = $arOption[2];
			if (isset($arOption[3]))
			{
				$arNotes[] = $arOption[3];
			}
			?>
			<tr>
				<td width="40%" nowrap <?= ($type[0] === 'textarea' ? 'class="adm-detail-valign-top"' : ''); ?>>
					<?php if (isset($arOption[3])): ?>
						<span class="required"><sup><?= count($arNotes); ?></sup></span>
					<?php endif; ?>
					<label for="<?php echo htmlspecialcharsbx($arOption[0]) ?>"><?= $arOption[1] ?>:</label>
				</td>
				<td width="60%">
					<?php if ($type[0] == 'checkbox'): ?>
						<input
							type="checkbox"
							name="<?php echo htmlspecialcharsbx($arOption[0]) ?>"
							id="<?php echo htmlspecialcharsbx($arOption[0]) ?>"
							value="Y"<?php echo $val === 'Y' ? ' checked' : '';?>>
					<?php elseif ($type[0] == 'text'): ?>
						<input
							type="text"
							size="<?php echo $type[1] ?>"
							maxlength="255"
							value="<?php echo htmlspecialcharsbx($val) ?>"
							name="<?php echo htmlspecialcharsbx($arOption[0]) ?>"
							id="<?php echo htmlspecialcharsbx($arOption[0]) ?>">
						<?php
							if ($arOption[0] == 'slow_sql_time')
							{
								echo GetMessage('PERFMON_OPTIONS_SLOW_SQL_TIME_SEC');
							}
						?>
						<?php
							if ($arOption[0] == 'large_cache_size')
							{
								echo GetMessage('PERFMON_OPTIONS_LARGE_CACHE_SIZE_KB');
							}
						?>
					<?php
					elseif ($type[0] == 'textarea'): ?>
						<textarea
							rows="<?php echo $type[1] ?>"
							cols="<?php echo $type[2] ?>"
							name="<?php echo htmlspecialcharsbx($arOption[0]) ?>"
							id="<?php echo htmlspecialcharsbx($arOption[0]) ?>"
						><?php echo htmlspecialcharsbx($val) ?></textarea>
					<?php endif ?>
				</td>
			</tr>
		<?php endforeach ?>
		<?php $ACTIVE = CPerfomanceKeeper::IsActive(); ?>
		<tr>
			<td valign="top" width="50%">
				<?php echo GetMessage('PERFMON_OPT_ACTIVE') ?>:
			</td>
			<td valign="middle" width="50%">
				<?php if ($ACTIVE): ?>
					<?php echo GetMessage('PERFMON_OPT_ACTIVE_Y') ?>
				<?php else: ?>
					<?php echo GetMessage('PERFMON_OPT_ACTIVE_N') ?>
				<?php endif; ?>
			</td>
		</tr>
		<?php if ($ACTIVE): ?>
			<tr>
				<td valign="top" width="50%">
					<?php echo GetMessage('PERFMON_OPT_ACTIVE_TO') ?>:
				</td>
				<td valign="top" width="50%">
					<?php
					$interval = max(0, COption::GetOptionInt('perfmon', 'end_time') - time());
					$hours = sprintf('%02d', intval($interval / 3600));
					$interval -= $hours * 3600;
					$minutes = sprintf('%02d', intval($interval / 60));
					$interval -= $minutes * 60;
					$seconds = sprintf('%02d', intval($interval));
					echo GetMessage('PERFMON_OPT_MINUTES', ['#HOURS#' => $hours, '#MINUTES#' => $minutes, '#SECONDS#' => $seconds]);
					?>
				</td>
			</tr>
			<tr>
				<td valign="top" width="50%">
					<label for="ACTIVE"><?php echo GetMessage('PERFMON_OPT_SET_IN_ACTIVE') ?></label>:
				</td>
				<td valign="top" width="50%">
					<input type="checkbox" name="ACTIVE" value="0" id="ACTIVE_CKBOX">
				</td>
			</tr>
		<?php else: ?>
			<tr>
				<td valign="top" width="50%">
					<?php echo GetMessage('PERFMON_OPT_SET_ACTIVE') ?>:
				</td>
				<td valign="top" width="50%">
					<select name="ACTIVE" id="ACTIVE_LIST">
						<option value="0"><?php echo GetMessage('PERFMON_OPT_INTERVAL_NO') ?></option>
						<option value="60"><?php echo GetMessage('PERFMON_OPT_INTERVAL_60_SEC') ?></option>
						<option value="300"><?php echo GetMessage('PERFMON_OPT_INTERVAL_300_SEC') ?></option>
						<option value="600"><?php echo GetMessage('PERFMON_OPT_INTERVAL_600_SEC') ?></option>
						<option value="1800"><?php echo GetMessage('PERFMON_OPT_INTERVAL_1800_SEC') ?></option>
						<option value="3600"><?php echo GetMessage('PERFMON_OPT_INTERVAL_3600_SEC') ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td valign="top" width="50%">
					<label for="clear_data"><?php echo GetMessage('PERFMON_OPT_CLEAR_DATA') ?></label>
				</td>
				<td valign="top" width="50%">
					<input type="checkbox" name="clear_data" id="clear_data" value="y">
				</td>
			</tr>
		<?php endif;
		$tabControl->BeginNextTab();
		foreach ($ormOptions as $option)
		{
			if (is_string($option))
			{
				?>
				<tr class="heading"><td colspan="2"><?=htmlspecialcharsbx($option); ?></td></tr>
				<?php
				continue;
			}
			$value = Main\Config\Option::get('perfmon', $option[0]);
			$type = $option[2];
			$name = htmlspecialcharsbx($option[0]);
			?>
			<tr>
				<td style="width: 40%; white-space: nowrap" <?php echo $type[0] == 'textarea' ? 'class="adm-detail-valign-top"' : '';?>>
					<label for="<?php echo htmlspecialcharsbx($option[0]) ?>"><?php echo $option[1] ?></label>
				</td>
				<td style="width: 60%;"><?php
				switch ($type[0])
				{
					case 'checkbox':
						?>
						<input type="hidden" name="<?=$name; ?>" value="N">
						<input type="checkbox" name="<?=$name; ?>" id="<?=$name; ?>" value="Y"<?=($value === 'Y' ? ' checked' : ''); ?>>
						<?php
						break;
					case 'text':
						?>
						<input type="text" size="<?=$type[1]; ?>" maxlength="255" value="<?=htmlspecialcharsbx($value); ?>" name="<?=$name; ?>" id="<?=$name; ?>">
						<?php
						break;
					case 'textarea':
						?>
						<textarea rows="<?=$type[1]; ?>" cols="<?=$type[2]; ?>" name="<?=$name; ?>" id="<?=$name; ?>"><?php echo htmlspecialcharsbx($val ?? '') ?></textarea>
						<?php
						break;
				}
				?>
				</td>
			</tr><?php
		}
		$tabControl->BeginNextTab();
		require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/group_rights.php';
		$tabControl->Buttons(); ?>
		<input <?php echo $RIGHT < 'W' ? 'disabled' : '';?> type="submit" name="Update" value="<?=GetMessage('MAIN_SAVE')?>" title="<?=GetMessage('MAIN_OPT_SAVE_TITLE')?>" class="adm-btn-save">
		<input <?php echo $RIGHT < 'W' ? 'disabled' : '';?> type="submit" name="Apply" value="<?=GetMessage('MAIN_OPT_APPLY')?>" title="<?=GetMessage('MAIN_OPT_APPLY_TITLE')?>">
		<?php if ($_REQUEST['back_url_settings'] <> ''): ?>
			<input
				<?php echo $RIGHT < 'W' ? 'disabled' : '';?>
				type="button"
				name="Cancel"
				value="<?=GetMessage('MAIN_OPT_CANCEL')?>"
				title="<?=GetMessage('MAIN_OPT_CANCEL_TITLE')?>"
				onclick="window.location='<?php echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST['back_url_settings'])) ?>'"
			>
			<input
				type="hidden"
				name="back_url_settings"
				value="<?=htmlspecialcharsbx($_REQUEST['back_url_settings'])?>"
			>
		<?php endif ?>
		<input
			type="submit"
			name="RestoreDefaults"
			title="<?php echo GetMessage('MAIN_HINT_RESTORE_DEFAULTS') ?>"
			onclick="return confirm('<?php echo addslashes(GetMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING')) ?>')"
			value="<?php echo GetMessage('MAIN_RESTORE_DEFAULTS') ?>"
		>
		<?=bitrix_sessid_post();?>
		<?php $tabControl->End(); ?>
	</form>
	<script>
		function slow_sql_log_check()
		{
			var activeCheckbox = BX('ACTIVE_LIST');
			if (activeCheckbox)
			{
				jsSelectUtils.deleteAllOptions(activeCheckbox);
				jsSelectUtils.addNewOption(activeCheckbox, '0', '<?php echo GetMessageJS('PERFMON_OPT_INTERVAL_NO')?>');
				if (
					(!BX('sql_log').checked || BX('sql_log').checked && BX('slow_sql_log').checked)
					&& (!BX('cache_log').checked || BX('cache_log').checked && BX('large_cache_log').checked)
				)
				{
					jsSelectUtils.addNewOption(activeCheckbox, '3600', '<?php echo GetMessageJS('PERFMON_OPT_INTERVAL_3600_SEC')?>');
					jsSelectUtils.addNewOption(activeCheckbox, '14400', '<?php echo GetMessageJS('PERFMON_OPT_INTERVAL_4_HOURS')?>');
					jsSelectUtils.addNewOption(activeCheckbox, '28800', '<?php echo GetMessageJS('PERFMON_OPT_INTERVAL_8_HOURS')?>');
					jsSelectUtils.addNewOption(activeCheckbox, '86400', '<?php echo GetMessageJS('PERFMON_OPT_INTERVAL_24_HOURS')?>');
					jsSelectUtils.addNewOption(activeCheckbox, '604800', '<?php echo GetMessageJS('PERFMON_OPT_INTERVAL_7_DAYS')?>');
				}
				else
				{
					jsSelectUtils.addNewOption(activeCheckbox, '60', '<?php echo GetMessageJS('PERFMON_OPT_INTERVAL_60_SEC')?>');
					jsSelectUtils.addNewOption(activeCheckbox, '300', '<?php echo GetMessageJS('PERFMON_OPT_INTERVAL_300_SEC')?>');
					jsSelectUtils.addNewOption(activeCheckbox, '600', '<?php echo GetMessageJS('PERFMON_OPT_INTERVAL_600_SEC')?>');
					jsSelectUtils.addNewOption(activeCheckbox, '1800', '<?php echo GetMessageJS('PERFMON_OPT_INTERVAL_1800_SEC')?>');
					jsSelectUtils.addNewOption(activeCheckbox, '3600', '<?php echo GetMessageJS('PERFMON_OPT_INTERVAL_3600_SEC')?>');
				}
			}
		}
		BX.ready(function ()
		{
			BX.bind(BX('sql_log'), 'click', slow_sql_log_check);
			BX.bind(BX('slow_sql_log'), 'click', slow_sql_log_check);
			BX.bind(BX('cache_log'), 'click', slow_sql_log_check);
			BX.bind(BX('large_cache_log'), 'click', slow_sql_log_check);
			slow_sql_log_check();
		});
	</script>
	<?php
	if (!empty($arNotes))
	{
		echo BeginNote();
		foreach ($arNotes as $i => $str)
		{
			?><span class="required"><sup><?php echo $i + 1 ?></sup></span><?php echo $str ?><br><?php
		}
		echo EndNote();
	}
	?>
<?php
endif;
