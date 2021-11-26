<?php
namespace Bitrix\Sale\PaySystem\AdminPage\PaySystemCashbox
{
	use Bitrix\Main\Application;
	use Bitrix\Main\Localization\Loc;
	use Bitrix\Main\Page\Asset;
	use Bitrix\Sale;
	use Bitrix\Sale\Cashbox;

	if (!\defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	{
		die();
	}

	Asset::getInstance()->addJs('/bitrix/js/sale/cashbox.js');
	Asset::getInstance()->addJs('/bitrix/js/sale/pay_system_cashbox.js');

	global $APPLICATION;

	$saleModulePermissions = $APPLICATION->GetGroupRight('sale');
	if ($saleModulePermissions < 'W')
	{
		$APPLICATION->AuthForm(Loc::getMessage('SALE_PAY_SYSTEM_ACCESS_DENIED'));
	}

	$request = Application::getInstance()->getContext()->getRequest();

	/**
	 * @var $service Sale\PaySystem\Service;
	 * @var $cashboxClass Sale\Cashbox\CashboxPaySystem
	 * @var $errorMessage string
	 */

	/** @var Cashbox\CashboxPaySystem $cashboxClass */
	$cashboxClass = $service->getCashboxClass();

	$paySystemCodeForKkm = $cashboxClass::getPaySystemCodeForKkm();
	$supportedKkmModels = Sale\BusinessValue::getValuesByCode($service->getConsumerName(), $paySystemCodeForKkm);

	$kkmId = current($supportedKkmModels);
	if ($request->get('kkmId'))
	{
		$kkmId = $request->get('kkmId');
	}
	elseif ($request->get('CASHBOX'))
	{
		$cashBoxData = $request->get('CASHBOX');
		if (!empty($cashBoxData['KKM_ID']))
		{
			$kkmId = $cashBoxData['KKM_ID'];
		}
	}

	if (empty($kkmId))
	{
		?>
			<tbody>
				<tr>
					<td colspan="2"><?= Loc::getMessage('SALE_PAY_SYSTEM_ACCOUNT_NOT_FOUND') ?></td>
				</tr>
			</tbody>
		<?php

		return;
	}

	$fiscalizationEnable = true;
	$cashbox = Cashbox\Manager::getList([
		'filter' => [
			'=HANDLER' => $cashboxClass,
			'=KKM_ID' => $kkmId,
		],
	])->fetch();
	if (!$cashbox)
	{
		$fiscalizationEnable = false;

		$cashbox = [
			'HANDLER' => $cashboxClass,
			'OFD' => '',
			'KKM_ID' => $kkmId,
		];
	}

	if (
		$request->isPost()
		&& $request->get('CASHBOX_SAVE') === 'Y'
		&& ($request->get('save') !== null || $request->get('apply') !== null)
	)
	{
		if ($request->get('CAN_PRINT_CHECK_SELF') === 'Y')
		{
			$cashBoxData = $request->get('CASHBOX');
			$fields = [
				'NAME' => $cashboxClass::getName(),
				'HANDLER' => $cashboxClass,
				'OFD' => $cashBoxData['OFD'],
				'EMAIL' => $cashBoxData['EMAIL'],
				'NUMBER_KKM' => '',
				'KKM_ID' => $cashBoxData['KKM_ID'],
				'USE_OFFLINE' => 'N',
				'ENABLED' => 'Y',
				'SORT' => 100,
				'OFD_SETTINGS' => $request->getPost('OFD_SETTINGS') ?: [],
			];

			/** @var Cashbox\Cashbox $handler */
			$handlerList = Cashbox\Cashbox::getHandlerList();
			if (isset($handlerList[$fields['HANDLER']]))
			{
				$handler = $fields['HANDLER'];
				if (class_exists($handler))
				{
					$fields['SETTINGS'] = $handler::extractSettingsFromRequest($request);
				}

				$cashboxObject = Cashbox\Cashbox::create($fields);
				$result = $cashboxObject->validate();
				if ($result->isSuccess())
				{
					$cashboxId = $cashbox['ID'];
					if ($cashboxId)
					{
						$result = Cashbox\Manager::update($cashboxId, $fields);
						if ($result->isSuccess())
						{
							$cashboxObject = Cashbox\Manager::getObjectById($cashboxId);
							AddEventToStatFile('sale', 'updateCashbox', $cashboxId, $cashboxObject::getCode());
						}
					}
					else
					{
						$result = Cashbox\Manager::add($fields);
						if ($result->isSuccess())
						{
							$cashboxId = $result->getId();
							$cashboxObject = Cashbox\Manager::getObjectById($cashboxId);
							AddEventToStatFile('sale', 'addCashbox', $cashboxId, $cashboxObject::getCode());
						}
					}
				}
				else
				{
					foreach ($result->getErrors() as $error)
					{
						$errorMessage .= $error->getMessage()."<br>\n";
					}
				}
			}
			else
			{
				$errorMessage .= Loc::getMessage('SALE_PAY_SYSTEM_ERROR_NO_HANDLER_EXIST')."<br>\n";
			}
		}
		else
		{
			$onDisabledFiscalizationResult = Sale\PaySystem\Cashbox\EventHandler::onDisabledFiscalization($service, $kkmId);
			if (!$onDisabledFiscalizationResult->isSuccess())
			{
				$errorMessage .= implode("<br>\n", $onDisabledFiscalizationResult->getErrorMessages());
			}
		}
	}
	else
	{
		?>
		<tbody>
			<input type="hidden" name="CASHBOX_SAVE" id="CASHBOX_SAVE" value="Y">
			<input type="hidden" name="CASHBOX[HANDLER]" id="HANDLER" value="<?= $cashbox['HANDLER'] ?>">
		</tbody>
		<?php
		if (\count($supportedKkmModels) === 1)
		{
			?><input type="hidden" name="CASHBOX[KKM_ID]" id="KKM_ID" value="<?= $kkmId ?>"><?php
		}
		else
		{
			$handlerDescription = $service->getHandlerDescription();
			$paySystemCodeName = $handlerDescription['CODES'][$cashboxClass::getPaySystemCodeForKkm()]['NAME'];
		?>
			<tbody>
				<tr>
					<td width="40%" class="adm-detail-content-cell-l">
						<span class="adm-required-field"><?= $paySystemCodeName ?></span>
					</td>
					<td width="60%" class="adm-detail-content-cell-r">
						<select name="CASHBOX[KKM_ID]" id="KKM_ID" onchange="BX.Sale.PaySystemCashbox.reloadSettings()">
							<?php
							foreach ($supportedKkmModels as $supportedKkm)
							{
								$selected = (($supportedKkm === $cashbox['KKM_ID']) ? 'selected' : '');
								echo '<option value="' . $supportedKkm . '" ' . $selected . '>' . htmlspecialcharsbx($supportedKkm) . '</option>';
							}
							?>
						</select>
					</td>
				</tr>
			</tbody>
		<?php
		}
		?>
		<tbody>
			<tr>
				<td width="40%" class="adm-detail-content-cell-l">
					<label for="CAN_PRINT_CHECK_SELF"><?= Loc::getMessage('SALE_PAY_SYSTEM_CASHBOX_FISCALIZATION') ?></label>
				</td>
				<td width="60%" class="adm-detail-content-cell-r">
					<input class="adm-designed-checkbox" type="checkbox" name="CAN_PRINT_CHECK_SELF" id="CAN_PRINT_CHECK_SELF" value="Y" onchange="BX.Sale.PaySystemCashbox.toggleCashboxSetting(this)" <?= ($fiscalizationEnable ? ' checked' : '') ?>>
					<label class="adm-designed-checkbox-label" for="CAN_PRINT_CHECK_SELF" title=""></label>
				</td>
			</tr>
		</tbody>
		<tbody data="pay-system-cashbox-common-settings-container" <?= ($fiscalizationEnable ? '' : "style='display:none;'") ?>>
			<tr class="heading">
				<td colspan="2"><?= Loc::getMessage('SALE_PAY_SYSTEM_CASHBOX_SETTINGS') ?></td>
			</tr>
			<tr>
				<td width="40%" class="adm-detail-content-cell-l">
					<span class="adm-required-field">Email:</span>
				</td>
				<td width="60%" class="adm-detail-content-cell-r">
					<?php
					$email = $request->get('CASHBOX')['EMAIL'] ?: $cashbox['EMAIL'];
					?>
					<input type="text" name="CASHBOX[EMAIL]" value="<?= htmlspecialcharsbx($email) ?>" size="40">
					<span id="hint_EMAIL"></span>
					<script>
						BX.hint_replace(BX('hint_EMAIL'), '<?= \CUtil::JSEscape(Loc::getMessage('SALE_PAY_SYSTEM_EMAIL_HINT')) ?>');
					</script>
				</td>
			</tr>
		</tbody>
		<?php
		ob_start();
		require_once (Application::getDocumentRoot()."/bitrix/modules/sale/admin/cashbox_settings.php");
		$cashboxSettings = ob_get_clean();
		?>
		<tbody id="sale-cashbox-settings-container" data="pay-system-cashbox-settings-container" <?= ($fiscalizationEnable ? '' : "style='display:none;'") ?>>
			<?= $cashboxSettings ?>
		</tbody>
		<?php
	}
}