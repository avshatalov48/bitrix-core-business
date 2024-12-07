<?php

/** @global CMain $APPLICATION */
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Type\DateTime;
use Bitrix\Currency;

$module_id = 'currency';
$moduleAccessLevel = $APPLICATION->GetGroupRight($module_id);
if ($moduleAccessLevel >= 'R')
{
	Loader::includeModule('currency');

	$settingsUrl = $APPLICATION->GetCurPage() . '?lang=' . LANGUAGE_ID . '&mid=' . $module_id;

	$request = Context::getCurrent()->getRequest();

	$aTabs = [
		[
			'DIV' => 'edit0',
			'TAB' => Loc::getMessage('CURRENCY_SETTINGS'),
			'ICON' => 'currency_settings',
			'TITLE' => Loc::getMessage('CURRENCY_SETTINGS_TITLE'),
		],
		[
			'DIV' => 'edit1',
			'TAB' => Loc::getMessage('CO_TAB_RIGHTS'),
			'ICON' => 'currency_settings',
			'TITLE' => Loc::getMessage('CO_TAB_RIGHTS_TITLE'),
		],
	];
	$tabControl = new CAdminTabControl('currencyTabControl', $aTabs, true, true);

	$systemTabs = [
		[
			'DIV' => 'proc_edit0',
			'TAB' => Loc::getMessage('CURRENCY_BASE_RATE'),
			'ICON' => '',
			'TITLE' => Loc::getMessage('CURRENCY_BASE_RATE_TITLE'),
		],
		[
			'DIV' => 'proc_edit1',
			'TAB' => Loc::getMessage('CURRENCY_AGENTS'),
			'ICON' => '',
			'TITLE' => Loc::getMessage('CURRENCY_AGENTS_TITLE'),
		],
	];
	$systemTabControl = new CAdminTabControl('currencyProcTabControl', $systemTabs, true, true);

	if (
		$request->getRequestMethod() === 'GET'
		&& $request->get('RestoreDefaults') !== null
		&& $moduleAccessLevel === 'W'
		&& check_bitrix_sessid()
	)
	{
		Option::delete('currency');

		$userGroupIds = [];
		$iterator = CGroup::GetList(
			'id',
			'asc',
			[
				'ACTIVE' => 'Y',
				'ADMIN' => 'N',
			]
		);
		while ($row = $iterator->Fetch())
		{
			$userGroupIds[] = (int)$row['ID'];
		}
		unset($row, $iterator);
		if (!empty($userGroupIds))
		{
			$APPLICATION->DelGroupRight($module_id, $userGroupIds);
		}
		unset($userGroupIds);

		LocalRedirect($settingsUrl);
	}

	if (
		$request->isPost()
		&& $moduleAccessLevel === 'W'
		&& check_bitrix_sessid()
	)
	{
		if ($request->getPost('Update') === 'Y')
		{
			$newBaseCurrency = $request->getPost('BASE_CURRENCY');
			if (!is_string($newBaseCurrency))
			{
				$newBaseCurrency = null;
			}
			$newBaseCurrency = trim((string)$newBaseCurrency);
			if ($newBaseCurrency !== '')
			{
				$res = CCurrency::SetBaseCurrency($newBaseCurrency);
			}

			ob_start();
			require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/group_rights.php';
			ob_end_clean();

			LocalRedirect($settingsUrl . '&' . $tabControl->ActiveTabParam());
		}
		if ($request->getPost('procedures') === 'Y' && $request->getPost('action') === 'recalc')
		{
			Currency\CurrencyManager::updateBaseRates();
			LocalRedirect($settingsUrl . '&' . $systemTabControl->ActiveTabParam());
		}
		if ($request->getPost('agents') === 'Y' && !empty($_POST['action']))
		{
			$action = $request->getPost('action');
			if (!is_string($action))
			{
				$action = null;
			}
			$action = trim((string)$action);
			if ($action !== '')
			{
				switch ($action)
				{
					case 'activate':
					case 'deactivate':
						$agentIterator = CAgent::GetList(
							[],
							[
								'MODULE_ID' => 'currency',
								'=NAME' => '\Bitrix\Currency\CurrencyManager::currencyBaseRateAgent();',
							]
						);
						$currencyAgent = $agentIterator->Fetch();
						unset($agentIterator);
						if (!empty($currencyAgent))
						{
							$active = ($action === 'activate' ? 'Y' : 'N');
							CAgent::Update(
								$currencyAgent['ID'],
								[
									'ACTIVE' => $active,
								]
							);
						}
						break;
					case 'create':
						$checkDate = DateTime::createFromTimestamp(strtotime('tomorrow 00:01:00'));
						CAgent::AddAgent(
							'\Bitrix\Currency\CurrencyManager::currencyBaseRateAgent();',
							'currency',
							'Y',
							86400,
							'',
							'Y',
							$checkDate->toString(),
							100,
							false,
							false
						);
						break;
				}
				LocalRedirect($settingsUrl . '&' . $systemTabControl->ActiveTabParam());
			}
		}
	}

	$baseCurrency = Currency\CurrencyManager::getBaseCurrency();

	$tabControl->Begin();
	?>
	<form method="POST" action="<?= $APPLICATION->GetCurPage(); ?>?lang=<?= LANGUAGE_ID; ?>&mid=<?= $module_id; ?>" name="currency_settings">
	<?= bitrix_sessid_post();

	$tabControl->BeginNextTab();
	?><tr>
	<td style="width: 40%;"><?= HtmlFilter::encode(Loc::getMessage('BASE_CURRENCY')); ?></td>
	<td><select name="BASE_CURRENCY"><?php
	$currencyList = Currency\CurrencyManager::getCurrencyList();
	if (!empty($currencyList))
	{
		foreach ($currencyList as $currency => $title)
		{
			?><option value="<?= HtmlFilter::encode($currency); ?>"<?= ($currency === $baseCurrency ? ' selected' : ''); ?>><?php
				echo HtmlFilter::encode($title);
			?></option><?php
		}
		unset($title, $currency);
	}
	unset($currencyList);
	?></select></td>
	</tr>
	<?php
	$tabControl->BeginNextTab();

	require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/group_rights.php';

	$tabControl->Buttons();?>
<script>
function RestoreDefaults()
{
	if (confirm('<?= CUtil::JSEscape(Loc::getMessage("CUR_OPTIONS_BTN_HINT_RESTORE_DEFAULT_WARNING")); ?>'))
	{
		window.location = "<?= $APPLICATION->GetCurPage(); ?>?lang=<?= LANGUAGE_ID; ?>&mid=<?= $module_id; ?>&RestoreDefaults=Y&<?= bitrix_sessid_get()?>";
	}
}
</script>
	<input
		type="submit"<?= ($moduleAccessLevel < 'W' ? ' disabled' : ''); ?>
		name="Update"
		class="adm-btn-save"
		value="<?= HtmlFilter::encode(Loc::getMessage('CUR_OPTIONS_BTN_SAVE')); ?>"
		title="<?= HtmlFilter::encode(Loc::getMessage('CUR_OPTIONS_BTN_SAVE_TITLE')); ?>"
	>
	<input type="hidden" name="Update" value="Y">
	<input
		type="reset"
		name="reset"
		value="<?= HtmlFilter::encode(Loc::getMessage('CUR_OPTIONS_BTN_RESET')); ?>"
		title="<?= HtmlFilter::encode(Loc::getMessage('CUR_OPTIONS_BTN_RESET_TITLE')); ?>"
	>
	<input
		type="button"<?= ($moduleAccessLevel < 'W' ? ' disabled' : ''); ?>
		value="<?= HtmlFilter::encode(Loc::getMessage('CUR_OPTIONS_BTN_RESTORE_DEFAULT')); ?>"
		title="<?= HtmlFilter::encode(Loc::getMessage('CUR_OPTIONS_BTN_HINT_RESTORE_DEFAULT')); ?>"
		onclick="RestoreDefaults();"
	>
	</form>
	<?php
	$tabControl->End();
	?><h2><?= Loc::getMessage('CURRENCY_PROCEDURES'); ?></h2><?php
	$systemTabControl->Begin();
	$systemTabControl->BeginNextTab();
	?><form method="POST" action="<?= $APPLICATION->GetCurPage(); ?>?lang=<?= LANGUAGE_ID; ?>&mid=<?= $module_id; ?>" name="currency_procedures"><?php
	echo bitrix_sessid_post();
	?>
	<input type="hidden" name="action" value="recalc">
	<input
		type="submit"<?= ($moduleAccessLevel < "W" || $baseCurrency === '' ? ' disabled' : ''); ?>
		name="recalc"
		value="<?= HtmlFilter::encode(Loc::getMessage('CUR_PROCEDURES_BTN_RECALC')); ?>"
	>
	<input type="hidden" name="procedures" value="Y">
	</form><?php
	$systemTabControl->BeginNextTab();
	?><form method="POST" action="<?= $APPLICATION->GetCurPage(); ?>?lang=<?= LANGUAGE_ID; ?>&mid=<?= $module_id; ?>" name="currency_agents"><?php
	echo bitrix_sessid_post();
	?><h4><?= Loc::getMessage('CURRENCY_BASE_RATE_AGENT'); ?></h4><?php
	$currencyAgent = false;
	$agentIterator = CAgent::GetList(
		[],
		[
			'MODULE_ID' => 'currency',
			'=NAME' => '\Bitrix\Currency\CurrencyManager::currencyBaseRateAgent();',
		]
	);
	if ($agentIterator)
	{
		$currencyAgent = $agentIterator->Fetch();
	}
	if (!empty($currencyAgent))
	{
		$currencyAgent['LAST_EXEC'] = (string)$currencyAgent['LAST_EXEC'];
		$currencyAgent['NEXT_EXEC'] = (string)$currencyAgent['NEXT_EXEC'];
		?><b><?= Loc::getMessage('CURRENCY_BASE_RATE_AGENT_ACTIVE'); ?>:</b>&nbsp;<?= (
			$currencyAgent['ACTIVE'] === 'Y'
				? Loc::getMessage('CURRENCY_AGENTS_ACTIVE_YES')
				: Loc::getMessage('CURRENCY_AGENTS_ACTIVE_NO')
		);?><br><?php
		if ($currencyAgent['LAST_EXEC'])
		{
			?><b><?= Loc::getMessage('CURRENCY_AGENTS_LAST_EXEC'); ?>:</b>&nbsp;<?= $currencyAgent['LAST_EXEC']; ?><br><?php
			if ($currencyAgent['ACTIVE'] === 'Y')
			{
				?><b><?= Loc::getMessage('CURRENCY_AGENTS_NEXT_EXEC');?>:</b>&nbsp;<?= $currencyAgent['NEXT_EXEC']; ?><br><?php
			}
		}
		elseif ($currencyAgent['ACTIVE'] === 'Y')
		{
			?><b><?= Loc::getMessage('CURRENCY_AGENTS_PLANNED_NEXT_EXEC') ?>:</b>&nbsp;<?= $currencyAgent['NEXT_EXEC']; ?><br><?php
		}
		if ($currencyAgent['ACTIVE'] !== 'Y')
		{
			?><br><input type="hidden" name="action" value="activate">
			<input type="submit" name="activate" value="<?= HtmlFilter::encode(Loc::getMessage('CURRENCY_AGENTS_ACTIVATE')); ?>"><?php
		}
		else
		{
			?><br><input type="hidden" name="action" value="deactivate">
			<input type="submit" name="deactivate" value="<?= HtmlFilter::encode(Loc::getMessage('CURRENCY_AGENTS_DEACTIVATE')); ?>"><?php
		}
	}
	else
	{
		?><b><?= Loc::getMessage('CURRENCY_BASE_RATE_AGENT_ABSENT'); ?></b><br><br>
		<input type="hidden" name="action" value="create">
		<input type="submit" name="startagent" value="<?= HtmlFilter::encode(Loc::getMessage('CURRENCY_AGENTS_CREATE_AGENT')); ?>">
		<?php
	}

	?><input type="hidden" name="agents" value="Y">
	</form><?php
	$systemTabControl->End();
}
