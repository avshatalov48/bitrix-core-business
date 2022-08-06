<?php
$module_id = 'rest';

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!Loader::includeModule($module_id))
{
	return;
}

$context = Application::getInstance()->getContext();
$request = $context->getRequest();
$docRoot = $context->getServer()->getDocumentRoot();

Loc::loadMessages($docRoot . BX_ROOT . "/modules/main/options.php");
Loc::loadMessages(__FILE__);

if ($APPLICATION->GetGroupRight($module_id) < "S")
{
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

$tabs = [
	[
		"DIV" => "edit1",
		"TAB" => Loc::getMessage("REST_TAB_SET"),
		"TITLE" => Loc::getMessage("REST_TAB_TITLE_SET"),
	],
	[
		"DIV" => "edit2",
		"TAB" => Loc::getMessage("REST_TAB_LOG"),
		"TITLE" => Loc::getMessage("REST_TAB_TITLE_LOG"),
	],
];

$tabControl = new CAdminTabControl("tabControl", $tabs);

$backUrl = $request->get('back_url_settings');
$arDefaultValues = array(
	'BLOCK_NEW_USER_LF_SITE' => 'N',
);
$allOptions = [
	[
		'CODE' => 'enable_mod_zip',
		'NAME' => Loc::getMessage('REST_OPT_ENABLE_MOD_ZIP') . ':',
		'PARAMS' => [
			'TYPE' => 'checkbox'
		]
	],
	[
		'CODE' => 'import_max_size',
		'NAME' => Loc::getMessage('REST_OPT_MAX_IMPORT_SIZE') . ':',
		'PARAMS' => [
			'TYPE' => 'float',
			'PRECISION' => 2,
			'ABS' => 'Y'
		]
	],
];
$filterOptions = [
	[
		'CODE' => 'client_id',
		'NAME' => Loc::getMessage('REST_OPT_LOG_FILTER_CLIENT_ID') . ' (client_id):',
		'SIZE' => 45,
	],
	[
		'CODE' => 'password_id',
		'NAME' => Loc::getMessage('REST_OPT_LOG_FILTER_PASSWORD_ID') . ' (password_id):',
		'SIZE' => 45,
	],
	[
		'CODE' => 'scope',
		'NAME' => Loc::getMessage('REST_OPT_LOG_FILTER_SCOPE') . ' (scope):',
		'SIZE' => 12,
	],
	[
		'CODE' => 'method',
		'NAME' => Loc::getMessage('REST_OPT_LOG_FILTER_METHOD') . ' (method):',
		'SIZE' => 45,
	],
	[
		'CODE' => 'user_id',
		'NAME' => Loc::getMessage('REST_OPT_LOG_FILTER_USER_ID') . ' (user_id):',
		'SIZE' => 6,
	],
];

// post save
if ($Apply.$RestoreDefaults <> '' && \check_bitrix_sessid())
{
	if ($RestoreDefaults <> '')
	{
		include_once('default_option.php');
		if (is_array($rest_default_option))
		{
			foreach ($rest_default_option as $option => $value)
			{
				\COption::setOptionString($module_id, $option, $value);
			}
		}
	}
	else
	{
		foreach ($allOptions as $option)
		{
			if ($option[0] == 'header')
			{
				continue;
			}

			$code = $option['CODE'];
			$val = ${$code};
			$val = trim($val);

			switch ($option['PARAMS']['TYPE']):
				case 'checkbox':
					if ($val <> 'Y')
					{
						$val = 'N';
					}
					break;
				case 'float':
					$precision = $option['PARAMS']['PRECISION'] ? : 0;
					$val = round($val, $precision);
					break;
			endswitch;

			if($option['PARAMS']['ABS'] && $option['PARAMS']['ABS'] == 'Y')
			{
				$val = abs($val);
			}

			\COption::setOptionString($module_id, $code, $val);
		}

		if ($_REQUEST["clear_data"] === "y")
		{
			\Bitrix\Rest\LogTable::clearAll();
		}

		if (array_key_exists('ACTIVE', $_REQUEST))
		{
			$ACTIVE = intval($_REQUEST['ACTIVE']);
			if ($ACTIVE > 0 && $ACTIVE <= 86400)
			{
				\COption::setOptionString($module_id, 'log_end_time', time() + $ACTIVE);
			}
			else
			{
				\COption::removeOption($module_id, 'log_end_time');
			}
		}

		$filters = array();
		foreach ($filterOptions as $option)
		{
			$val = trim($_REQUEST["log_filters"][$option["CODE"]]);
			if ($val)
			{
				$filters[$option["CODE"]] = $val;
			}
		}
		\COption::setOptionString($module_id, "log_filters", serialize($filters));
	}

	\LocalRedirect(
		$APPLICATION->GetCurPage() .
		'?mid=' . urlencode($mid) .
		'&lang=' . urlencode(LANGUAGE_ID) .
		'&back_url_settings=' . urlencode($backUrl) .
		'&' . $tabControl->ActiveTabParam());
}

$tabControl->Begin();
?>
<form method="post" name="intr_opt_form" action="<? echo $APPLICATION->GetCurPage() ?>?mid=<?=urlencode($mid)?>&amp;lang=<? echo LANGUAGE_ID ?>">
	<? echo bitrix_sessid_post(); ?>
	<?
	$tabControl->BeginNextTab();
	?>
	<? foreach($allOptions as $option): ?>
		<? if ($option['CODE'] == 'header'):?>
			<tr class="heading">
				<td colspan="2">
					<?= $option['NAME'];?>
				</td>
			</tr>
			<?if (isset($option['PARAMS'])):?>
				<tr>
					<td></td>
					<td>
						<?
						echo BeginNote();
						echo $option['PARAMS'];
						echo EndNote();
						?>
					</td>
				</tr>
			<?endif;?>
			<? continue;
		endif;
		?>
		<?
			$params = $option['PARAMS'];
			$val = \COption::getOptionString(
				$module_id,
				$option['CODE'],
				isset($option['DEFAULT']) ? $option['DEFAULT'] : null
			);
		?>
		<tr>
			<td valign="top" width="40%"><?
				if ($params['TYPE'] == 'checkbox')
				{
					echo '<label for="' . \htmlspecialcharsbx($option['CODE']) . '">'.$option['NAME'].'</label>';
				}
				else
				{
					echo $option['NAME'];
				}
				?></td>
			<td valign="middle" width="60%">
				<? if ($params['TYPE'] == 'checkbox'): ?>
					<input
						type="checkbox"
						name="<?=\htmlspecialcharsbx($option['CODE'])?>"
						id="<?=\htmlspecialcharsbx($option['CODE'])?>"
						value="Y"
						<?=($val == 'Y') ? 'checked="checked" ' : '';?>
					/>
				<? else: ?>
					<input
						type="text"
						size="<?=$params['SIZE']?>"
						maxlength="255"
						value="<?=\htmlspecialcharsbx($val)?>"
						name="<?=\htmlspecialcharsbx($option['CODE'])?>"
					/>
				<? endif;?>
			</td>
		</tr>
	<?
	endforeach;
	$tabControl->BeginNextTab();
	$ACTIVE = \COption::GetOptionInt('rest', 'log_end_time', 0) >= time();
	?>
	<tr>
		<td valign="top" width="40%">
			<? echo GetMessage("REST_OPT_ACTIVE") ?>:
		</td>
		<td valign="middle" width="60%">
			<? if ($ACTIVE): ?>
				<? echo GetMessage("REST_OPT_ACTIVE_Y") ?>
			<? else: ?>
				<? echo GetMessage("REST_OPT_ACTIVE_N") ?>
			<? endif; ?>
		</td>
	</tr>
	<? if ($ACTIVE): ?>
		<tr>
			<td valign="top" width="40%">
				<? echo GetMessage("REST_OPT_ACTIVE_TO") ?>:
			</td>
			<td valign="top" width="60%">
				<?
				$interval = max(0, COption::GetOptionInt("rest", "log_end_time") - time());
				$hours = sprintf("%02d", intval($interval / 3600));
				$interval -= $hours * 3600;
				$minutes = sprintf("%02d", intval($interval / 60));
				$interval -= $minutes * 60;
				$seconds = sprintf("%02d", intval($interval));
				echo GetMessage("REST_OPT_MINUTES", array("#HOURS#" => $hours, "#MINUTES#" => $minutes, "#SECONDS#" => $seconds));
				?>
			</td>
		</tr>
		<tr>
			<td valign="top" width="40%">
				<label for="ACTIVE_CKBOX"><? echo GetMessage("REST_OPT_SET_IN_ACTIVE") ?></label>:
			</td>
			<td valign="top" width="60%">
				<input type="checkbox" name="ACTIVE" value="0" id="ACTIVE_CKBOX">
			</td>
		</tr>
	<? else: ?>
		<tr>
			<td valign="top" width="40%">
				<? echo GetMessage("REST_OPT_SET_ACTIVE") ?>:
			</td>
			<td valign="top" width="60%">
				<select name="ACTIVE" id="ACTIVE_LIST">
					<option value="0"><? echo GetMessage("REST_OPT_INTERVAL_NO") ?></option>
					<option value="600"><? echo GetMessage("REST_OPT_INTERVAL_600_SEC") ?></option>
					<option value="3600"><? echo GetMessage("REST_OPT_INTERVAL_3600_SEC") ?></option>
					<option value="86400"><? echo GetMessage("REST_OPT_INTERVAL_24_HOURS") ?></option>
				</select>
			</td>
			<tr>
				<td valign="top" width="40%">
					<label for="clear_data"><? echo GetMessage("REST_OPT_CLEAR_DATA") ?>:</label>
				</td>
				<td valign="top" width="60%">
					<input type="checkbox" name="clear_data" id="clear_data" value="y">
				</td>
			</tr>
		</tr>
	<?endif;?>
	<?if (IsModuleInstalled('perfmon')): ?>
		<tr>
			<td valign="top" width="40%">
				<? echo GetMessage("REST_OPT_LOG_RECS_COUNT") ?>:
			</td>
			<td valign="top" width="60%">
				<a href="<? echo htmlspecialcharsbx("/bitrix/admin/perfmon_table.php?lang=".LANGUAGE_ID."&table_name=b_rest_log&by=ID&order=desc") ?>"><? echo \Bitrix\Rest\LogTable::getCountAll(); ?></a>
			</td>
		</tr>
	<? endif; ?>
	<tr class="heading">
		<td colspan="2"><?=GetMessage("REST_OPT_LOG_FILTERS")?></td>
	</tr>
	<? foreach($filterOptions as $option):
		$filters = @unserialize(
			\Bitrix\Main\Config\Option::get('rest', 'log_filters', ''),
			[
				'allowed_classes' => false
			]
		);
		if (!is_array($filters))
		{
			$filters = array();
		}
		?>
		<tr>
			<td width="40%">
				<label for="<? echo htmlspecialcharsbx($option['CODE']) ?>"><? echo $option["NAME"] ?></label>
			</td>
			<td valign="middle" width="60%">
				<input
					type="text"
					size="<?=$option['SIZE']?>"
					maxlength="255"
					value="<?=\htmlspecialcharsbx($filters[$option["CODE"]])?>"
					name="<?=\htmlspecialcharsbx("log_filters[".$option['CODE']."]")?>"
					id="<?=\htmlspecialcharsbx($option['CODE'])?>"
				/>
			</td>
		</tr>
	<? endforeach; ?>
	<? $tabControl->Buttons(); ?>
	<?=bitrix_sessid_post();?>
	<input type="submit" name="Apply" value="<?=GetMessage("MAIN_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
	<input type="button" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="RestoreDefaults();" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">

	<? if ($backUrl <> ''): ?>
		<input
			type="button"
			name="Cancel"
			value="<?=Loc::getMessage('MAIN_OPT_CANCEL')?>"
			title="<?=Loc::getMessage('MAIN_OPT_CANCEL_TITLE')?>"
			onclick="window.location='<?=\htmlspecialcharsbx(CUtil::addslashes($backUrl)) ?>'"/>
		<input type="hidden" name="back_url_settings" value="<?=\htmlspecialcharsbx($backUrl)?>"/>
	<? endif ?>
	<? $tabControl->End(); ?>
</form>
<script type="text/javascript">
	function RestoreDefaults()
	{
		if(confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
			window.location = "<?echo $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?=LANGUAGE_ID?>&mid=<?echo urlencode($mid)?>&<?echo bitrix_sessid_get()?>";
	}
</script>