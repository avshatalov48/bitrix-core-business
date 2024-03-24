<?php
/* @var CMain $APPLICATION */
/* @var CUser $USER */
$module_id = 'subscribe';
$POST_RIGHT = CMain::GetUserRight($module_id);
if ($POST_RIGHT >= 'R') :
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/options.php');
IncludeModuleLangFile(__FILE__);

$arAllOptions = [
	['allow_anonymous', GetMessage('opt_anonym'), ['checkbox', 'Y']],
	['show_auth_links', GetMessage('opt_links'), ['checkbox', 'Y']],
	['subscribe_section', GetMessage('opt_sect'), ['text', 35]],
	['posting_interval', GetMessage('opt_interval'), ['text', 5]],
	['max_bcc_count', GetMessage('opt_max_bcc_count'), ['text', 5]],
	['default_from', GetMessage('opt_def_from'), ['text', 35]],
	['default_to', GetMessage('opt_def_to'), ['text', 35]],
	['posting_charset', GetMessage('opt_encoding'), ['text-list', 3, 20]],
	['allow_8bit_chars', GetMessage('opt_allow_8bit'), ['checkbox', 'Y']],
	['mail_additional_parameters', GetMessage('opt_mail_additional_parameters'), ['text', 35]],
	['attach_images', GetMessage('opt_attach'), ['checkbox', 'Y']],
	['subscribe_confirm_period', GetMessage('opt_delete'), ['text', 5]],
	['subscribe_auto_method', GetMessage('opt_method'), ['selectbox', ['agent' => GetMessage('opt_method_agent'), 'cron' => GetMessage('opt_method_cron')]]],
	['subscribe_max_emails_per_hit', GetMessage('opt_max_per_hit'), ['text', 5]],
	['subscribe_template_method', GetMessage('opt_template_method'), ['selectbox', ['agent' => GetMessage('opt_method_agent'), 'cron' => GetMessage('opt_method_cron')]]],
	['subscribe_template_interval', GetMessage('opt_template_interval'), ['text', 10]],
	['max_files_size', GetMessage('opt_max_files_size'), ['text', 5]],
];
$aTabs = [
	['DIV' => 'edit1', 'TAB' => GetMessage('MAIN_TAB_SET'), 'ICON' => 'subscribe_settings', 'TITLE' => GetMessage('MAIN_TAB_TITLE_SET')],
	['DIV' => 'edit2', 'TAB' => GetMessage('MAIN_TAB_RIGHTS'), 'ICON' => 'subscribe_settings', 'TITLE' => GetMessage('MAIN_TAB_TITLE_RIGHTS')],
];
$tabControl = new CAdminTabControl('tabControl', $aTabs);

/* @var $request \Bitrix\Main\HttpRequest */
$request = \Bitrix\Main\Context::getCurrent()->getRequest();

if (
	$request->isPost()
	&& (
		(string)$request['Update'] !== ''
		|| (string)$request['Apply'] !== ''
		|| (string)$request['RestoreDefaults'] !== ''
	)
	&& $POST_RIGHT === 'W'
	&& check_bitrix_sessid()
)
{
	if ((string)$request['RestoreDefaults'] !== '')
	{
		COption::RemoveOption('subscribe');
		$z = CGroup::GetList('id', 'asc', ['ACTIVE' => 'Y', 'ADMIN' => 'N']);
		while ($zr = $z->Fetch())
		{
			CMain::DelGroupRight($module_id, [$zr['ID']]);
		}
	}
	else
	{
		foreach ($arAllOptions as $arOption)
		{
			$name = $arOption[0];
			if ($arOption[2][0] == 'text-list')
			{
				$val = '';
				foreach ($_POST[$name] as $postValue)
				{
					$postValue = trim($postValue);
					if ($postValue !== '')
					{
						$val .= ($val !== '' ? ',' : '') . $postValue;
					}
				}
			}
			else
			{
				$val = $_POST[$name];
			}

			if ($arOption[2][0] == 'checkbox' && $val !== 'Y')
			{
				$val = 'N';
			}

			if ($name != 'mail_additional_parameters' || $USER->IsAdmin())
			{
				COption::SetOptionString($module_id, $name, $val);
			}
		}
	}
	CAgent::RemoveAgent('CPostingTemplate::Execute();', 'subscribe');
	if (COption::GetOptionString('subscribe', 'subscribe_template_method') !== 'cron')
	{
		CAgent::AddAgent('CPostingTemplate::Execute();', 'subscribe', 'N', COption::GetOptionString('subscribe', 'subscribe_template_interval'));
	}

	$Update = (string)$request['Update'] . (string)$request['Apply'];
	ob_start();
	require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/group_rights.php';
	ob_end_clean();

	if ($request['back_url_settings'] !== '')
	{
		if (((string)$request['Apply'] !== '') || ((string)$request['RestoreDefaults'] !== ''))
		{
			LocalRedirect($APPLICATION->GetCurPage() . '?mid=' . urlencode($module_id) . '&lang=' . urlencode(LANGUAGE_ID) . '&back_url_settings=' . urlencode($_REQUEST['back_url_settings']) . '&' . $tabControl->ActiveTabParam());
		}
		else
		{
			LocalRedirect($request['back_url_settings']);
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

	foreach ($arAllOptions as $Option)
	{
	$type = $Option[2];
	$val = COption::GetOptionString($module_id, $Option[0]);
	?>
	<tr>
		<td width="40%" <?php echo ($type[0] == 'textarea' || $type[0] == 'text-list') ? 'class="adm-detail-valign-top"' : '';?>>
			<label for="<?php echo htmlspecialcharsbx($Option[0])?>"><?php echo $Option[1]?></label>
		<td width="60%">
		<?php
		if ($type[0] == 'checkbox')
		{
			?><input type="checkbox" name="<?php echo htmlspecialcharsbx($Option[0])?>" id="<?php echo htmlspecialcharsbx($Option[0])?>" value="Y" <?php echo ($val == 'Y') ? 'checked' : '';?>><?php
		}
		elseif ($type[0] == 'text')
		{
			?><input type="text" size="<?php echo $type[1]?>" maxlength="255" value="<?php echo htmlspecialcharsbx($val)?>" name="<?php echo htmlspecialcharsbx($Option[0])?>"><?php
		}
		elseif ($type[0] == 'textarea')
		{
			?><textarea rows="<?php echo $type[1]?>" cols="<?php echo $type[2]?>" name="<?php echo htmlspecialcharsbx($Option[0])?>"><?php echo htmlspecialcharsbx($val)?></textarea><?php
		}
		elseif ($type[0] == 'text-list')
		{
			$aVal = explode(',', $val);
			foreach ($aVal as $val)
			{
				?><input type="text" size="<?php echo $type[2]?>" value="<?php echo htmlspecialcharsbx($val)?>" name="<?php echo htmlspecialcharsbx($Option[0]) . '[]'?>"><br><?php
			}
			for ($j = 0; $j < $type[1]; $j++)
			{
				?><input type="text" size="<?php echo $type[2]?>" value="" name="<?php echo htmlspecialcharsbx($Option[0]) . '[]'?>"><br><?php
			}
		}
		elseif ($type[0] == 'selectbox')
		{
			?><select name="<?php echo htmlspecialcharsbx($Option[0])?>"><?php
			foreach ($type[1] as $optionValue => $optionDisplay)
			{
				?><option value="<?php echo $optionValue?>" <?php echo ($val == $optionValue) ? 'selected' : '';?>><?php echo htmlspecialcharsbx($optionDisplay)?></option><?php
			}
			?></select><?php
		}
		?></td>
	</tr>
	<?php
	}
	?>
<?php $tabControl->BeginNextTab();?>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/group_rights.php';?>
<?php $tabControl->Buttons();?>
	<input <?php echo ($POST_RIGHT < 'W') ? 'disabled' : '';?> type="submit" name="Update" value="<?=GetMessage('MAIN_SAVE')?>" title="<?=GetMessage('MAIN_OPT_SAVE_TITLE')?>" class="adm-btn-save">
	<input <?php echo ($POST_RIGHT < 'W') ? 'disabled' : '';?> type="submit" name="Apply" value="<?=GetMessage('MAIN_OPT_APPLY')?>" title="<?=GetMessage('MAIN_OPT_APPLY_TITLE')?>">
	<?php if ($_REQUEST['back_url_settings'] <> ''):?>
		<input <?php echo ($POST_RIGHT < 'W') ? 'disabled' : '';?> type="button" name="Cancel" value="<?=GetMessage('MAIN_OPT_CANCEL')?>" title="<?=GetMessage('MAIN_OPT_CANCEL_TITLE')?>" onclick="window.location='<?php echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST['back_url_settings']))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST['back_url_settings'])?>">
	<?php endif?>
	<input <?php echo ($POST_RIGHT < 'W') ? 'disabled' : '';?> type="submit" name="RestoreDefaults" title="<?php echo GetMessage('MAIN_HINT_RESTORE_DEFAULTS')?>" OnClick="return confirm('<?php echo addslashes(GetMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING'))?>')" value="<?php echo GetMessage('MAIN_RESTORE_DEFAULTS')?>">
	<?=bitrix_sessid_post();?>
<?php $tabControl->End();?>
</form>
<?php endif;
