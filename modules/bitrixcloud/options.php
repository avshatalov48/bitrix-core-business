<?php
/* @var CUser $USER */
/* @var CMain $APPLICATION */
$module_id = 'bitrixcloud';
$RIGHT_W = $RIGHT_R = $USER->IsAdmin();
if ($RIGHT_R || $RIGHT_W) :
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/options.php');
IncludeModuleLangFile(__FILE__);

$arAllOptions = [
	[
		'monitoring_interval',
		GetMessage('BCL_MONITORING_INTERVAL') . ' ',
		[
			'selectbox',
			[
				7 => GetMessage('BCL_MONITORING_INTERVAL_WEEK'),
				30 => GetMessage('BCL_MONITORING_INTERVAL_MONTH'),
				90 => GetMessage('BCL_MONITORING_INTERVAL_QUARTER'),
				365 => GetMessage('BCL_MONITORING_INTERVAL_YEAR'),
			],
		],
	],
];

$aTabs = [
	[
		'DIV' => 'edit1',
		'TAB' => GetMessage('MAIN_TAB_SET'),
		'ICON' => 'bitrixcloud_settings',
		'TITLE' => GetMessage('MAIN_TAB_TITLE_SET'),
	],
	[
		'DIV' => 'edit2',
		'TAB' => GetMessage('MAIN_TAB_RIGHTS'),
		'ICON' => 'bitrixcloud_settings',
		'TITLE' => GetMessage('MAIN_TAB_TITLE_RIGHTS'),
	],
];
$tabControl = new CAdminTabControl('tabControl', $aTabs);

CModule::IncludeModule($module_id);

if (
	$_SERVER['REQUEST_METHOD'] === 'POST'
	&& (
		isset($_REQUEST['Update'])
		|| isset($_REQUEST['Apply'])
		|| isset($_REQUEST['RestoreDefaults'])
	)
	&& $RIGHT_W
	&& check_bitrix_sessid()
)
{
	if (isset($_REQUEST['RestoreDefaults']))
	{
		COption::RemoveOption($module_id);
	}
	else
	{
		foreach ($arAllOptions as $arOption)
		{
			$name = $arOption[0];
			$val = trim($_REQUEST[$name], " \t\n\r");
			if ($arOption[2][0] === 'checkbox' && $val !== 'Y')
			{
				$val = 'N';
			}
			COption::SetOptionString($module_id, $name, $val, $arOption[1]);
		}
	}

	ob_start();
	$Update = $_REQUEST['Update'] . $_REQUEST['Apply'];
	require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/group_rights2.php';
	ob_end_clean();

	if (isset($_REQUEST['back_url_settings']))
	{
		if (
			isset($_REQUEST['Apply'])
			|| isset($_REQUEST['RestoreDefaults'])
		)
		{
			LocalRedirect($APPLICATION->GetCurPage() . '?mid=' . urlencode($module_id) . '&lang=' . urlencode(LANGUAGE_ID) . '&back_url_settings=' . urlencode($_REQUEST['back_url_settings']) . '&' . $tabControl->ActiveTabParam());
		}
		else
		{
			LocalRedirect($_REQUEST['back_url_settings']);
		}
	}
	else
	{
		LocalRedirect($APPLICATION->GetCurPage() . '?mid=' . urlencode($module_id) . '&lang=' . urlencode(LANGUAGE_ID) . '&' . $tabControl->ActiveTabParam());
	}
}

?>
<form method="post" action="<?php echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($module_id)?>&amp;lang=<?=LANGUAGE_ID?>">
<?php
$tabControl->Begin();
$tabControl->BeginNextTab();

	foreach ($arAllOptions as $arOption):
		$val = COption::GetOptionString($module_id, $arOption[0]);
		$type = $arOption[2];
	?>
	<tr>
		<td width="40%" nowrap <?php echo $type[0] == 'textarea' ? 'class="adm-detail-valign-top"' : '';?>>
			<label for="<?php echo htmlspecialcharsbx($arOption[0])?>"><?php echo $arOption[1]?>:</label>
		<td width="60%">
			<?php if ($type[0] == 'checkbox'):?>
				<input type="checkbox" name="<?php echo htmlspecialcharsbx($arOption[0])?>" id="<?php echo htmlspecialcharsbx($arOption[0])?>" value="Y" <?php echo $val == 'Y' ? 'checked' : '';?>>
			<?php elseif ($type[0] == 'text'):?>
				<input type="text" size="<?php echo $type[1]?>" maxlength="255" value="<?php echo htmlspecialcharsbx($val)?>" name="<?php echo htmlspecialcharsbx($arOption[0])?>" id="<?php echo htmlspecialcharsbx($arOption[0])?>">
			<?php elseif ($type[0] == 'textarea'):?>
				<textarea rows="<?php echo $type[1]?>" cols="<?php echo $type[2]?>" name="<?php echo htmlspecialcharsbx($arOption[0])?>" id="<?php echo htmlspecialcharsbx($arOption[0])?>"><?php echo htmlspecialcharsbx($val)?></textarea>
			<?php elseif ($type[0] == 'selectbox'):
				?><select name="<?php echo htmlspecialcharsbx($arOption[0])?>"><?php
					foreach ($type[1] as $key => $value):
						?><option value="<?php echo htmlspecialcharsbx($key)?>" <?php echo $key == $val ? 'selected="selected"' : '';?>><?php echo htmlspecialcharsEx($value)?></option><?php
					endforeach;
				?></select><?php
			endif?>
		</td>
	</tr>
	<?php endforeach?>
<?php $tabControl->BeginNextTab();?>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/group_rights2.php';?>
<?php $tabControl->Buttons();?>
	<input <?php echo !$RIGHT_W ? 'disabled' : '';?> type="submit" name="Update" value="<?=GetMessage('MAIN_SAVE')?>" title="<?=GetMessage('MAIN_OPT_SAVE_TITLE')?>" class="adm-btn-save">
	<input <?php echo !$RIGHT_W ? 'disabled' : '';?> type="submit" name="Apply" value="<?=GetMessage('MAIN_OPT_APPLY')?>" title="<?=GetMessage('MAIN_OPT_APPLY_TITLE')?>">
	<?php if ($_REQUEST['back_url_settings'] <> ''):?>
		<input <?php echo !$RIGHT_W ? 'disabled' : '';?> type="button" name="Cancel" value="<?=GetMessage('MAIN_OPT_CANCEL')?>" title="<?=GetMessage('MAIN_OPT_CANCEL_TITLE')?>" onclick="window.location='<?php echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST['back_url_settings']))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST['back_url_settings'])?>">
	<?php endif?>
	<input <?php echo !$RIGHT_W ? 'disabled' : '';?> type="submit" name="RestoreDefaults" title="<?php echo GetMessage('MAIN_HINT_RESTORE_DEFAULTS')?>" onclick="return confirm('<?php echo CUtil::addslashes(GetMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING'))?>')" value="<?php echo GetMessage('MAIN_RESTORE_DEFAULTS')?>">
	<?=bitrix_sessid_post();?>
<?php $tabControl->End();?>
</form>
<?php endif;
