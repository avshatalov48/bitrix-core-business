<?php

namespace Sale\Handlers\DiscountPreset;

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Discount\Preset\ArrayHelper;
use Bitrix\Sale\Discount\Preset\HtmlHelper;
use Bitrix\Sale\Discount\Preset\Manager;
use Bitrix\Sale\Discount\Preset\SelectProductPreset;
use Bitrix\Sale\Discount\Preset\State;

class Delivery extends SelectProductPreset
{
	public function getTitle()
	{
		return Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_DELIVERY_NAME');
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
		return Manager::CATEGORY_DELIVERY;
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

	/**
	 * @param $siteId
	 *
	 * @return \Bitrix\Sale\Delivery\Services\Base[]
	 */
	protected function getDeliverySystems($siteId)
	{
		$parameters = array(
			'select' => array(
				'*',
				'SITES' => 'RESTRICTION_BY_SITE.PARAMS',
			),
			'filter' => array(
				'=ACTIVE' => 'Y',
				'!=CLASS_NAME' => array(
					'\Bitrix\Sale\Delivery\Services\Group',
					'\Bitrix\Sale\Delivery\Services\EmptyDeliveryService',
				),
			),
			'runtime' => array(
				'RESTRICTION_BY_SITE' => array(
					'data_type' => 'Bitrix\Sale\Internals\ServiceRestrictionTable',
					'reference' => array(
						'ref.SERVICE_ID' => 'this.ID',
						'ref.SERVICE_TYPE' => array(
							'?',
							\Bitrix\Sale\Delivery\Restrictions\Manager::SERVICE_TYPE_SHIPMENT,
						),
						'ref.CLASS_NAME' => array('?', '\Bitrix\Sale\Delivery\Restrictions\BySite'),
					),
					'join_type' => 'left',
				),
			),
		);

		$result = array();
		$dbResultList = \Bitrix\Sale\Delivery\Services\Table::getList($parameters);
		while($service = $dbResultList->fetch())
		{
			if(!empty($service['SITES']) && is_array($service['SITES']['SITE_ID']))
			{
				if(!in_array($siteId, $service['SITES']['SITE_ID']))
				{
					continue;
				}
			}
			unset($service['SITES']);

			$deliveryService = \Bitrix\Sale\Delivery\Services\Manager::createObject($service);
			if (!$deliveryService || $deliveryService->canHasProfiles() || $deliveryService->canHasChildren())
			{
				continue;
			}

			$result[$service['ID']] = $deliveryService;
		}

		return $result;
	}

	protected function getLabelDiscountValue()
	{
		return Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_DELIVERY_DELIVERY_DISCOUNT_VALUE');
	}

	public function processShowInputAmount(State $state)
	{
		$lid = $state->get('discount_lid');
		$currency = \Bitrix\Sale\Internals\SiteCurrencyTable::getSiteCurrency($lid);
		$deliverySystems = $this->getDeliverySystems($lid);

		$forSelectData = array();
		foreach($deliverySystems as $id => $deliverySystem)
		{
			$forSelectData[$id] = $deliverySystem->getNameWithParent();
		}
		Main\Type\Collection::sortByColumn($forSelectData, 'NAME', '', null, true);

		return '
			<table width="100%" border="0" cellspacing="7" cellpadding="0">
				<tbody>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:40%;"><strong>' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_SHIPMENT_DELIVERY_ORDER_AMOUNT') . ':</strong></td>
					<td class="adm-detail-content-cell-r" style="width:60%;">
						<input type="text" name="discount_order_amount" value="' . htmlspecialcharsbx($state->get('discount_order_amount')) . '" size="39" maxlength="100" style="width: 100px;"> <span>' . $currency . '</span>
					</td>
				</tr>' . $this->renderDiscountValue($state, $currency) . ' 
				<tr>
					<td class="adm-detail-content-cell-l" style="width:40%;"><strong>' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_SHIPMENT_DELIVERY_LABEL') . ':</strong></td>
					<td class="adm-detail-content-cell-r">
						' . HtmlHelper::generateSelect('discount_delivery', $forSelectData, $state->get('discount_delivery')) . '
					</td>
				</tr>
				</tbody>
			</table>
		';
	}

	protected function renderDiscountValue(State $state, $currency)
	{
		return '
			<tr>
				<td class="adm-detail-content-cell-l" style="width:40%;"><strong>' . $this->getLabelDiscountValue() . ':</strong></td>
				<td class="adm-detail-content-cell-r" style="width:60%;">
					<input type="text" name="discount_value" value="' . htmlspecialcharsbx($state->get('discount_value')) . '" maxlength="100" style="width: 100px;"> '
					. HtmlHelper::generateSelect('discount_type', array(
						'Perc' => Loc::getMessage('SHD_BT_SALE_ACT_GROUP_BASKET_SELECT_PERCENT'),
						'Cur' => $currency,
					), $state->get('discount_type')) . '
				</td>
			</tr>		
		';
	}

	public function processSaveInputAmount(State $state)
	{
		if(!trim($state->get('discount_value')))
		{
			$this->errorCollection[] = new Error(Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ERROR_EMPTY_VALUE'));
		}

		if(!trim($state->get('discount_order_amount')))
		{
			$this->errorCollection[] = new Error(Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ERROR_EMPTY_ORDER_AMOUNT'));
		}

		if((int)$state->get('discount_delivery') < 0)
		{
			$this->errorCollection[] = new Error(Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ERROR_EMPTY_DELIVERY'));
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
			'discount_order_amount' => ArrayHelper::getByPath($discountFields, 'CONDITIONS.CHILDREN.0.DATA.Value'),
			'discount_delivery' => ArrayHelper::getByPath($discountFields, 'CONDITIONS.CHILDREN.1.DATA.value.0'),
		];

		return parent::generateState($discountFields)->append($stateFields);
	}

	public function generateDiscount(State $state)
	{
		return array_merge(parent::generateDiscount($state), array(
			'CONDITIONS' => array(
				'CLASS_ID' => 'CondGroup',
				'DATA' => array(
					'All' => 'AND',
					'True' => 'True',
				),
				'CHILDREN' => array(
					array(
						'CLASS_ID' => 'CondBsktAmtGroup',
						'DATA' => array(
							'logic' => 'EqGr',
							'Value' => $state->get('discount_order_amount'),
							'All' => 'AND',
						),
						'CHILDREN' => array(),
					),
					array(
						'CLASS_ID' => 'CondSaleDelivery',
						'DATA' => array(
							'logic' => 'Equal',
							'value' => array((int)$state->get('discount_delivery')),
						),
					),
				),
			),
			'ACTIONS' => array(
				'CLASS_ID' => 'CondGroup',
				'DATA' => array(
					'All' => 'AND',
				),
				'CHILDREN' => array(
					array(
						'CLASS_ID' => 'ActSaleDelivery',
						'DATA' => array(
							'Type' => $this->getTypeOfDiscount(),
							'Value' => $state->get('discount_value'),
							'Unit' => $state->get('discount_type', 'Cur'),
						),
					),
				),
			),
		));
	}
}