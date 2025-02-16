<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/workflow/include.php';
/** @var CMain $APPLICATION */
$module_id = 'workflow';
$WORKFLOW_RIGHT = $APPLICATION->GetGroupRight($module_id);
if ($WORKFLOW_RIGHT >= 'R')
{
	IncludeModuleLangFile(__FILE__);

	$arAllOptions = [
		[
			'USE_HTML_EDIT',
			GetMessage('FLOW_USE_HTML_EDIT'),
			'Y',
			['checkbox', 'Y'],
		],
		[
			'HISTORY_SIMPLE_EDITING',
			GetMessage('FLOW_HISTORY_SIMPLE_EDITING'),
			'N',
			['checkbox', 'Y'],
		],
		[
			'MAX_LOCK_TIME',
			GetMessage('FLOW_MAX_LOCK'),
			'60',
			['text', 5],
		],
		[
			'DAYS_AFTER_PUBLISHING',
			GetMessage('FLOW_DAYS_AFTER_PUBLISHING'),
			'0',
			['text', 5],
			true,
		],
		[
			'HISTORY_COPIES',
			GetMessage('FLOW_HISTORY_COPIES'),
			'10',
			['text', 5],
			true,
		],
		[
			'HISTORY_DAYS',
			GetMessage('FLOW_HISTORY_DAYS'),
			'-1',
			['text', 5],
			true,
		],
	];

	$aTabs = [
		[
			'DIV' => 'edit1',
			'TAB' => GetMessage('MAIN_TAB_SET'),
			'ICON' => 'workflow_settings',
			'TITLE' => GetMessage('MAIN_TAB_TITLE_SET'),
		],
		[
			'DIV' => 'edit2',
			'TAB' => GetMessage('MAIN_TAB_RIGHTS'),
			'ICON' => 'workflow_settings',
			'TITLE' => GetMessage('MAIN_TAB_TITLE_RIGHTS'),
		],
	];
	$tabControl = new CAdminTabControl('tabControl', $aTabs);

	/* @var $request \Bitrix\Main\HttpRequest */
	$request = \Bitrix\Main\Context::getCurrent()->getRequest();

	if (
		$request->isPost()
		&& $WORKFLOW_RIGHT >= 'W'
		&& check_bitrix_sessid()
	)
	{
		if ((string)$request['RestoreDefaults'] !== '')
		{
			COption::RemoveOption($module_id);
			$z = CGroup::GetList('id', 'asc', ['ACTIVE' => 'Y', 'ADMIN' => 'N']);
			while ($zr = $z->Fetch())
			{
				$APPLICATION->DelGroupRight($module_id, [$zr['ID']]);
			}
		}
		elseif ((string)$request['Update'] !== '')
		{
			foreach ($arAllOptions as $option)
			{
				$name = $option[0];
				$val  = $_POST[$name];
				if ($option[3][0] == 'checkbox' && $val != 'Y')
				{
					$val = 'N';
				}
				COption::SetOptionString($module_id, $name, $val);
			}

			COption::SetOptionString($module_id, 'WORKFLOW_ADMIN_GROUP_ID', intval($WORKFLOW_ADMIN_GROUP_ID));

			if ($_POST['DAYS_AFTER_PUBLISHING_clear'] == 'Y')
			{
				CWorkflow::CleanUpPublished();
			}
			if ($_POST['HISTORY_COPIES_clear'] == 'Y')
			{
				CWorkflow::CleanUpHistoryCopies();
			}
			if ($_POST['HISTORY_DAYS_clear'] == 'Y')
			{
				CWorkflow::CleanUpHistory();
			}
		}

		$Update = (string)$request['Update'] . (string)$request['Apply'];
		ob_start();
		require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/group_rights.php';
		ob_end_clean();

		LocalRedirect($APPLICATION->GetCurPage() . '?mid=' . urlencode($module_id) . '&lang=' . urlencode(LANGUAGE_ID) . '&' . $tabControl->ActiveTabParam());
	}
	$WORKFLOW_ADMIN_GROUP_ID = COption::GetOptionString($module_id, 'WORKFLOW_ADMIN_GROUP_ID');

	?>
	<?php
	$tabControl->Begin();
	?><form method="POST" action="<?php echo htmlspecialcharsbx($APPLICATION->GetCurPage() . '?mid=' . urlencode($module_id) . '&lang=' . LANGUAGE_ID)?>"><?php
	$tabControl->BeginNextTab();
	?>
		<?php
		foreach ($arAllOptions as $Option)
		{
			$val = COption::GetOptionString($module_id, $Option[0], $Option[2]);
			$type = $Option[3];
		?>
		<tr>
			<td width="40%" nowrap <?php echo ($type[0] == 'textarea') ? 'class="adm-detail-valign-top"' : '';?>>
				<label for="<?php echo htmlspecialcharsbx($Option[0])?>"><?php echo $Option[1]?></label>
			<td width="60%">
			<?php if ($type[0] == 'checkbox')
			{
				?><input type="hidden" name="<?php echo htmlspecialcharsbx($Option[0])?>" value="N"><?php
				?><input type="checkbox" name="<?php echo htmlspecialcharsbx($Option[0])?>" id="<?php echo htmlspecialcharsbx($Option[0])?>" value="Y"<?php echo ($val == 'Y') ? ' checked' : '';?>><?php
			}
			elseif ($type[0] == 'text')
			{
				?><input type="text" size="<?php echo $type[1]?>" maxlength="255" value="<?php echo htmlspecialcharsbx($val)?>" name="<?php echo htmlspecialcharsbx($Option[0])?>"><?php
				if (isset($Option[4]))
				{
					?>&nbsp;<label for="<?php echo htmlspecialcharsbx($Option[0])?>_clear"><?=GetMessage('FLOW_CLEAR')?>:</label><input type="hidden" name="<?php echo htmlspecialcharsbx($Option[0])?>_clear" value="N"><input type="checkbox" name="<?php echo htmlspecialcharsbx($Option[0])?>_clear" id="<?php echo htmlspecialcharsbx($Option[0])?>_clear" value="Y"><?php
				}
			}
			elseif ($type[0] == 'textarea')
			{
				?><textarea rows="<?php echo $type[1]?>" cols="<?php echo $type[2]?>" name="<?php echo htmlspecialcharsbx($Option[0])?>"><?php echo htmlspecialcharsbx($val)?></textarea><?php
			}
			?></td>
		</tr>
		<?php }?>
		<tr>
			<td><?php echo GetMessage('FLOW_ADMIN')?></td>
			<td><?php echo SelectBox('WORKFLOW_ADMIN_GROUP_ID', CGroup::GetDropDownList(''), GetMessage('MAIN_NO'), htmlspecialcharsbx($WORKFLOW_ADMIN_GROUP_ID));?></td>
		</tr>

	<?php
	$tabControl->BeginNextTab();
	require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/group_rights.php';
	$tabControl->Buttons();
	?>
	<input <?php echo ($WORKFLOW_RIGHT < 'W') ? 'disabled' : '';?> type="submit" name="Update" value="<?=GetMessage('FLOW_SAVE')?>" class="adm-btn-save">
	<input type="hidden" name="Update" value="Y">
	<input type="reset" name="reset" value="<?=GetMessage('FLOW_RESET')?>">
	<input <?php echo ($WORKFLOW_RIGHT < 'W') ? 'disabled' : '';?> type="submit" title="<?php echo GetMessage('MAIN_HINT_RESTORE_DEFAULTS')?>" OnClick="return confirm('<?php echo addslashes(GetMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING'))?>')" value="<?php echo GetMessage('MAIN_RESTORE_DEFAULTS')?>" name="RestoreDefaults">
	<?=bitrix_sessid_post();?>
	<?php $tabControl->End();?>
	</form>
<?php
}
