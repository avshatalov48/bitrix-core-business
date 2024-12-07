<?php

namespace Sale\Handlers\DiscountPreset;

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Discount\Preset\ArrayHelper;
use Bitrix\Sale\Discount\Preset\BasePreset;
use Bitrix\Sale\Discount\Preset\HtmlHelper;
use Bitrix\Sale\Discount\Preset\Manager;
use Bitrix\Sale\Discount\Preset\State;
use Bitrix\Sale;

class PaySystem extends BasePreset
{
	public function getTitle()
	{
		return Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_PAYSYSTEM_NAME');
	}

	public function getDescription()
	{
		return '';
	}

	/**
	 * @return int
	 */
	public function getCategory()
	{
		return Manager::CATEGORY_PAYMENT;
	}

	public function getFirstStepName()
	{
		return 'InputName';
	}

	public function processShowInputName(State $state)
	{
		return $this->processShowInputNameInternal($state);
	}

	public function processSaveInputName(State $state)
	{
		return $this->processSaveInputNameInternal($state, 'InputAmount');
	}

	protected function getPaymentSystems()
	{
		$dbRes = Sale\PaySystem\Manager::getList(array(
			'select' => array(
				'ID',
				'NAME',
				'SORT',
				'DESCRIPTION',
				'ACTIVE',
				'ACTION_FILE',
				'LOGOTIP',
			)
		));

		$result = array();
		while($paySystem = $dbRes->fetch())
		{
			$logoFileArray = \CFile::GetFileArray($paySystem['LOGOTIP']);
			$paySystem['LOGOTIP'] = \CFile::ShowImage($logoFileArray, 100, 100, "border=0", "", false);

			$result[$paySystem['ID']] = $paySystem;
		}

		return $result;
	}

	public function processShowInputAmount(State $state)
	{
		$lid = $state->get('discount_lid');
		$currency = \Bitrix\Sale\Internals\SiteCurrencyTable::getSiteCurrency($lid);
		$paymentSystems = $this->getPaymentSystems();

		$forSelectData = array();
		foreach($paymentSystems as $id => $paymentSystem)
		{
			$forSelectData[$id] = $paymentSystem['NAME'];
		}
		Main\Type\Collection::sortByColumn($forSelectData, 'NAME', '', null, true);

		return '
			<table width="100%" border="0" cellspacing="7" cellpadding="0">
				<tbody>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:40%;"><strong>' . $this->getLabelDiscountValue() . ':</strong></td>
					<td class="adm-detail-content-cell-r" style="width:60%;">
						<input type="text" name="discount_value" value="' . htmlspecialcharsbx($state->get('discount_value')) . '" maxlength="100" style="width: 100px;"> <span>' . $currency . '</span>
					</td>
				</tr>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:40%;"><strong>' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_PAYSYSTEM_PAYMENT_LABEL') . ':</strong></td>
					<td class="adm-detail-content-cell-r">
						' . HtmlHelper::generateSelect('discount_payment', $forSelectData, $state->get('discount_payment')) . '
					</td>
				</tr>
				</tbody>
			</table>
		';
	}

	public function processSaveInputAmount(State $state)
	{
		if(!trim($state->get('discount_value')))
		{
			$this->addErrorEmptyActionValue();
		}

		if(!$state->get('discount_payment'))
		{
			$this->errorCollection[] = new Error(Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ERROR_EMPTY_PAYMENT'));
		}

		if(!$this->errorCollection->isEmpty())
		{
			return array($state, 'InputAmount');
		}

		return array($state, 'CommonSettings');
	}

	public function processShowCommonSettings(State $state)
	{
		return $this->processShowCommonSettingsInternal($state);
	}

	public function processSaveCommonSettings(State $state)
	{
		return $this->processSaveCommonSettingsInternal($state);
	}

	public function generateState(array $discountFields)
	{
		$discountFields = $this->normalizeDiscountFields($discountFields);

		$stateFields = [
			'discount_lid' => $discountFields['LID'],
			'discount_name' => $discountFields['NAME'],
			'discount_groups' => $this->getUserGroupsByDiscount($discountFields['ID']),
			'discount_value' => ArrayHelper::getByPath($discountFields, 'ACTIONS.CHILDREN.0.DATA.Value'),
			'discount_type' => ArrayHelper::getByPath($discountFields, 'ACTIONS.CHILDREN.0.DATA.Unit'),
			'discount_payment' => ArrayHelper::getByPath($discountFields, 'CONDITIONS.CHILDREN.0.DATA.value.0'),
		];

		return parent::generateState($discountFields)->append($stateFields);
	}

	public function generateDiscount(State $state)
	{
		return array_merge(
			parent::generateDiscount($state),
			[
				'CONDITIONS' => [
					'CLASS_ID' => 'CondGroup',
					'DATA' => [
						'All' => 'AND',
						'True' => 'True',
					],
					'CHILDREN' => [
						[
							'CLASS_ID' => 'CondSalePaySystem',
							'DATA' => [
								'logic' => 'Equal',
								'value' => [$state->get('discount_payment')],
							],
						],
					],
				],
				'ACTIONS' => [
					'CLASS_ID' => 'CondGroup',
					'DATA' => [
						'All' => 'AND',
					],
					'CHILDREN' => [
						[
							'CLASS_ID' => 'ActSaleBsktGrp',
							'DATA' => [
								'Type' => $this->getTypeOfDiscount(),
								'Value' => $state->get('discount_value'),
								'Unit' => $state->get('discount_type', 'CurAll'),
								'Max' => 0,
								'All' => 'AND',
								'True' => 'True',
							],
							'CHILDREN' => [],
						],
					],
				],
			]
		);
	}
}
