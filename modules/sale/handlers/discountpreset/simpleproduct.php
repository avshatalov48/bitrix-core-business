<?php

namespace Sale\Handlers\DiscountPreset;


use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Discount\Preset\ArrayHelper;
use Bitrix\Sale\Discount\Preset\Manager;
use Bitrix\Sale\Discount\Preset\SelectProductPreset;
use Bitrix\Sale\Discount\Preset\State;
use Bitrix\Sale\Internals;


Loc::loadMessages(__FILE__);

class SimpleProduct extends SelectProductPreset
{
	public function getTitle()
	{
		return Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_SIMPLEPRODUCT_NAME');
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
		return Manager::CATEGORY_PRODUCTS;
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

	public function processShowInputAmount(State $state)
	{
		$lid = $state->get('discount_lid');
		$currency = \CSaleLang::getLangCurrency($lid);

		$sectionCount = count($state->get('discount_section', array()));

		return '
			<table width="100%" border="0" cellspacing="7" cellpadding="0">
				<tbody>
				' . $this->renderDiscountValue($state, $currency) . ' 
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

		$this->validateSectionsAndProductsState($state, $this->errorCollection);

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

		$stateFields = array(
			'discount_value' => ArrayHelper::getByPath($discountFields, 'ACTIONS.CHILDREN.0.DATA.Value'),
			'discount_type' => ArrayHelper::getByPath($discountFields, 'ACTIONS.CHILDREN.0.DATA.Unit'),
			'discount_section' => $this->getSectionsFromConditions(ArrayHelper::getByPath($discountFields, 'CONDITIONS.CHILDREN.0.CHILDREN')),
			'discount_product' => $this->getProductsFromConditions(ArrayHelper::getByPath($discountFields, 'CONDITIONS.CHILDREN.0.CHILDREN')),
		);

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
						'CLASS_ID' => 'CondBsktProductGroup',
						'DATA' => array(
							'Found' => 'Found',
							'All' => 'OR',
						),
						'CHILDREN' => array_merge(
							$this->generateSectionConditions($state->get('discount_section')),
							$this->generateProductConditions($state->get('discount_product'))
						),
					)
				),
			),
			'ACTIONS' => array(
				'CLASS_ID' => 'CondGroup',
				'DATA' => array(
					'All' => 'AND',
				),
				'CHILDREN' => array(
					array(
						'CLASS_ID' => 'ActSaleBsktGrp',
						'DATA' => array(
							'Type' => $this->getTypeOfDiscount(),
							'Value' => $state->get('discount_value'),
							'Unit' => $state->get('discount_type', 'CurAll'),
							'Max' => 0,
							'All' => 'OR',
							'True' => 'True',
						),
						'CHILDREN' => array(
							$this->generateSectionActions($state->get('discount_section')),
							$this->generateProductActions($state->get('discount_product')),
						),
					),
				),
			),
		));
	}
}