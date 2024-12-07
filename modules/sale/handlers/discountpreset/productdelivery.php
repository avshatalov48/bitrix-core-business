<?php

namespace Sale\Handlers\DiscountPreset;

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Discount\Preset\ArrayHelper;
use Bitrix\Sale\Discount\Preset\HtmlHelper;
use Bitrix\Sale\Discount\Preset\Manager;
use Bitrix\Sale\Discount\Preset\State;
use Bitrix\Sale\Internals;

class ProductDelivery extends Delivery
{
	public function getTitle()
	{
		return Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_PRODUCTDELIVERY_NAME');
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

	public function processShowInputAmount(State $state)
	{
		$lid = $state->get('discount_lid');
		$currency = Internals\SiteCurrencyTable::getSiteCurrency($lid);
		$deliverySystems = $this->getDeliverySystems($lid);

		$forSelectData = array();
		foreach($deliverySystems as $id => $deliverySystem)
		{
			$forSelectData[$id] = $deliverySystem->getNameWithParent();
		}
		Main\Type\Collection::sortByColumn($forSelectData, 'NAME', '', null, true);

		$sectionCount = count($state->get('discount_section', array()));

		return '
			<table width="100%" border="0" cellspacing="7" cellpadding="0">
				<tbody>
				' . $this->renderDiscountValue($state, $currency) . ' 
				<tr>
					<td class="adm-detail-content-cell-l" style="width:40%;"><strong>' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_SHIPMENT_DELIVERY_LABEL') . ':</strong></td>
					<td class="adm-detail-content-cell-r">
						' . HtmlHelper::generateMultipleSelect('discount_delivery[]', $forSelectData, $state->get('discount_delivery', array())) . '
					</td>
				</tr>
				</tbody>
			</table>
			
			<script type="application/javascript">
			BX.ready(function(){
				new BX.Sale.Admin.DiscountPreset.SelectProduct({
					presetId: "' . \CUtil::JSEscape($this->className()) . '",
					siteId: "' . \CUtil::JSEscape($lid) . '",
					sectionCount: ' . $sectionCount . ',
					products: ' . \CUtil::PhpToJSObject($this->generateProductsData($state->get('discount_product'), $lid)) . '
				});
			});
			</script>

			' . $this->renderElementBlock($state) . '			
			' . $this->renderSectionBlock($state) . '						
		';
	}

	public function processSaveInputAmount(State $state)
	{
		if(!trim($state->get('discount_value')))
		{
			$this->errorCollection[] = new Error(Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ERROR_EMPTY_VALUE'));
		}

		if(!$state->get('discount_delivery'))
		{
			$this->errorCollection[] = new Error(Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ERROR_EMPTY_DELIVERY'));
		}

		if(!$this->errorCollection->isEmpty())
		{
			return array($state, 'InputAmount');
		}

		return array($state, 'CommonSettings');
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
			'discount_delivery' => ArrayHelper::getByPath($discountFields, 'CONDITIONS.CHILDREN.0.DATA.value'),
			'discount_section' => $this->getSectionsFromConditions(ArrayHelper::getByPath($discountFields, 'CONDITIONS.CHILDREN.1.CHILDREN.0.CHILDREN')),
			'discount_product' => $this->getProductsFromConditions(ArrayHelper::getByPath($discountFields, 'CONDITIONS.CHILDREN.1.CHILDREN.1.CHILDREN')),
		];

		return parent::generateState($discountFields)->append($stateFields);
	}

	public function generateDiscount(State $state)
	{
		$generateProductConditions = $this->generateProductConditions($state->get('discount_product'));
		$generateSectionConditions = $this->generateSectionConditions($state->get('discount_section'));

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
							'CLASS_ID' => 'CondSaleDelivery',
							'DATA' => [
								'logic' => 'Equal',
								'value' => $state->get('discount_delivery'),
							],
						],
						[
							'CLASS_ID' => 'CondGroup',
							'DATA' => [
								'All' => 'OR',
								'True' => 'True',
							],
							'CHILDREN' => [
								$generateSectionConditions? [
									'CLASS_ID' => 'CondBsktProductGroup',
									'DATA' => [
										'Found' => 'Found',
										'All' => 'OR',
									],
									'CHILDREN' => $generateSectionConditions,
								] : [],
								$generateProductConditions? [
									'CLASS_ID' => 'CondBsktProductGroup',
									'DATA' => [
										'Found' => 'Found',
										'All' => 'OR',
									],
									'CHILDREN' => $generateProductConditions,
								] : [],
							],
						],
						[
							'CLASS_ID' => 'CondBsktCntGroup',
							'DATA' => [
								'logic' => 'Equal',
								'Value' => 0,
								'All' => 'OR',
							],
							'CHILDREN' => array_merge(
								$this->generateSectionConditions($state->get('discount_section'), 'Not'),
								$this->generateProductConditions($state->get('discount_product'), 'Not')
							),
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
							'CLASS_ID' => 'ActSaleDelivery',
							'DATA' => [
								'Type' => $this->getTypeOfDiscount(),
								'Value' => $state->get('discount_value'),
								'Unit' => $state->get('discount_type', 'Cur'),
							],
						],
					],
				],
			]
		);
	}
}
