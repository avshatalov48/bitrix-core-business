<?php

namespace Sale\Handlers\DiscountPreset;

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Discount\Preset\ArrayHelper;
use Bitrix\Sale\Discount\Preset\Manager;
use Bitrix\Sale\Discount\Preset\SelectProductPreset;
use Bitrix\Sale\Discount\Preset\State;

class ConnectedProduct extends SelectProductPreset
{
	const TYPE_PRODUCT = 'p';
	const TYPE_SECTION = 's';

	const PREDICTION_TEXT_TYPE_ACTION    = 'a';
	const PREDICTION_TEXT_TYPE_CONDITION = 'c';

	protected function init()
	{
		parent::init();

		\CJSCore::RegisterExt(
			'order_amount_preset',
			[
				'js' => '/bitrix/js/sale/admin/discountpreset/connected_product_preset.js',
			]
		);

		\Bitrix\Main\UI\Extension::load(['order_amount_preset']);
	}

	public function getSort()
	{
		return 100;
	}

	public function getTitle()
	{
		return Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_CP_NAME');
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
		$this->setStepDescription(Loc::getMessage("SALE_HANDLERS_DISCOUNTPRESET_CP_STEP_DESCR_INPUT_NAME"));

		return $this->processShowInputNameInternal($state);
	}

	public function processSaveInputName(State $state)
	{
		return $this->processSaveInputNameInternal($state, 'ProductForDiscount');
	}

	public function processShowProductForDiscount(State $state)
	{
		$this->setStepTitle(Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_CP_STEP_TITLE_DISCOUNT_FOR'));
		$this->setStepDescription(Loc::getMessage("SALE_HANDLERS_DISCOUNTPRESET_CP_STEP_DESCR_FOR_DISCOUNT"));

		$lid = $state->get('discount_lid');
		$currency = \CSaleLang::getLangCurrency($lid);

		$sectionCount = count($state->get('discount_section', array()));
		$presetJsName = 'presetConnectedProduct';

		$u = new \CAdminPopupEx(
			"menu_prediction_text",
			array(
				array(
					"TEXT" => Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_CP_DISCOUNT_PH_LINK'),
					"TITLE" => "",
					"ONCLICK" => $presetJsName .".insertVar('#LINK#', 'menu_prediction_text', 'discount_prediction_text_act')",
				),
				array(
					"TEXT" => Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_CP_DISCOUNT_PH_DISCOUNT_VALUE'),
					"TITLE" => "",
					"ONCLICK" => $presetJsName .".insertVar('#DISCOUNT_VALUE#', 'menu_prediction_text', 'discount_prediction_text_act')",
				),
				array(
					"TEXT" => Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_CP_DISCOUNT_PH_NAME'),
					"TITLE" => "",
					"ONCLICK" => $presetJsName .".insertVar('#NAME#', 'menu_prediction_text', 'discount_prediction_text_act')",
				),
			),
			array("zIndex" => 2000)
		);
		$popupHtml = $u->Show(true);

		return $popupHtml . '
			<script>
			var ' . $presetJsName . ' = new BX.Sale.Admin.DiscountPreset.ConnectedProduct();
			</script>
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
			<table width="100%" border="0" cellspacing="7" cellpadding="0">
				<tbody>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:20%;"><strong>' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_CP_DISCOUNT_PREDICTION_TEXT') . ':</strong></td>
					<td class="adm-detail-content-cell-r" style="width:80%;">
						<textarea name="discount_prediction_text_act" id="discount_prediction_text_act" cols="55" rows="1" style="width: 90%; margin-top: 0; margin-bottom: 0; height: 50px;">' . htmlspecialcharsbx($state->get('discount_prediction_text_act', Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_CP_DISCOUNT_PREDICTION_TEXT_DEFAULT_ACT'))) . '</textarea>
						<input style="float:right" type="button" id="menu_prediction_text" value="...">
					</td>
				</tr>
				</tbody>
			</table>						
		';
	}

	public function processSaveProductForDiscount(State $state)
	{
		if(!trim($state->get('discount_value')))
		{
			$this->addErrorEmptyActionValue();
		}

		$this->validateSectionsAndProductsState($state, $this->errorCollection);

		if(!$this->errorCollection->isEmpty())
		{
			return array($state, 'ProductForDiscount');
		}

		return array($state, 'ProductWhenDiscount');
	}

	public function processShowProductWhenDiscount(State $state)
	{
		$this->setStepTitle(Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_CP_STEP_TITLE_DISCOUNT_WHEN'));
		$this->setStepDescription(Loc::getMessage("SALE_HANDLERS_DISCOUNTPRESET_CP_STEP_DESCR_WHEN_DISCOUNT"));

		$lid = $state->get('discount_lid');
		$sectionCount = count($state->get('discount_cond_section', array()));

		$presetJsName = 'presetConnectedProduct';

		$u = new \CAdminPopupEx(
			"menu_prediction_text",
			array(
				array(
					"TEXT" => Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_CP_DISCOUNT_PH_LINK'),
					"TITLE" => "",
					"ONCLICK" => $presetJsName .".insertVar('#LINK#', 'menu_prediction_text', 'discount_prediction_text_cond')",
				),
				array(
					"TEXT" => Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_CP_DISCOUNT_PH_DISCOUNT_VALUE'),
					"TITLE" => "",
					"ONCLICK" => $presetJsName .".insertVar('#DISCOUNT_VALUE#', 'menu_prediction_text', 'discount_prediction_text_cond')",
				),
				array(
					"TEXT" => Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_CP_DISCOUNT_PH_NAME'),
					"TITLE" => "",
					"ONCLICK" => $presetJsName .".insertVar('#NAME#', 'menu_prediction_text', 'discount_prediction_text_cond')",
				),
			),
			array("zIndex" => 2000)
		);
		$popupHtml = $u->Show(true);

		return $popupHtml . '
			<script>
			var ' . $presetJsName . ' = new BX.Sale.Admin.DiscountPreset.ConnectedProduct();
			</script>
			<script type="application/javascript">
			BX.ready(function(){
				new BX.Sale.Admin.DiscountPreset.SelectProduct({
					presetId: "' . \CUtil::JSEscape($this->className()) . '",
					siteId: "' . \CUtil::JSEscape($lid) . '",
					sectionCount: ' . $sectionCount . ',
					inputNameProduct: "discount_cond_product[]",
					inputNameSection: "discount_cond_section[]",
					products: ' . \CUtil::PhpToJSObject($this->generateProductsData($state->get('discount_cond_product'), $lid)) . '
				});
			});
			</script>

			' . $this->renderElementBlock($state, 'discount_cond_product', true) . '			
			' . $this->renderSectionBlock($state, 'discount_cond_section', true) . '		
			<table width="100%" border="0" cellspacing="7" cellpadding="0">
				<tbody>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:20%;"><strong>' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_CP_DISCOUNT_PREDICTION_TEXT') . ':</strong></td>
					<td class="adm-detail-content-cell-r" style="width:80%;">
						<textarea name="discount_prediction_text_cond" id="discount_prediction_text_cond" cols="55" rows="1" style="width: 90%; margin-top: 0; margin-bottom: 0; height: 50px;">' . htmlspecialcharsbx($state->get('discount_prediction_text_cond', Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_CP_DISCOUNT_PREDICTION_TEXT_DEFAULT_COND'))) . '</textarea>
						<input style="float:right" type="button" id="menu_prediction_text" value="...">
					</td>
				</tr>
				</tbody>
			</table>
		';
	}

	public function processSaveProductWhenDiscount(State $state)
	{
		if(!is_array($state->get('discount_cond_product', array())))
		{
			$this->errorCollection[] = new Error(Loc::getMessage('SALE_BASE_PRESET_ERROR_SECTION_NON_ARRAY'));
		}

		if(!is_array($state->get('discount_cond_section', array())))
		{
			$this->errorCollection[] = new Error(Loc::getMessage('SALE_BASE_PRESET_ERROR_PRODUCT_NON_ARRAY'));
		}

		if(!$this->errorCollection->isEmpty())
		{
			return array($state, 'ProductWhenDiscount');
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

	/**
	 * @param array $discountFields
	 * @return State
	 * @throws Main\ArgumentException
	 */
	public function generateState(array $discountFields)
	{
		$discountFields = $this->normalizeDiscountFields($discountFields);

		$stateFields = array(
			'discount_value' => ArrayHelper::getByPath($discountFields, 'ACTIONS.CHILDREN.0.DATA.Value'),
			'discount_type' => ArrayHelper::getByPath($discountFields, 'ACTIONS.CHILDREN.0.DATA.Unit'),
			'discount_section' => $this->getSectionsFromConditions(ArrayHelper::getByPath($discountFields, 'ACTIONS.CHILDREN.0.CHILDREN.0.CHILDREN')),
			'discount_product' => $this->getProductsFromConditions(ArrayHelper::getByPath($discountFields, 'ACTIONS.CHILDREN.0.CHILDREN.1.CHILDREN')),
			'discount_cond_section' => $this->getSectionsFromConditions(ArrayHelper::getByPath($discountFields, 'CONDITIONS.CHILDREN.0.CHILDREN.0.CHILDREN.0.CHILDREN')),
			'discount_cond_product' => $this->getProductsFromConditions(ArrayHelper::getByPath($discountFields, 'CONDITIONS.CHILDREN.0.CHILDREN.1.CHILDREN.0.CHILDREN')),
		);

		if(!empty($discountFields['PREDICTION_TEXT']) && is_string($discountFields['PREDICTION_TEXT']))
		list(
			$stateFields['discount_prediction_text_act'],
			$stateFields['discount_prediction_text_cond']
		) = explode('|del|', $discountFields['PREDICTION_TEXT']);

		return parent::generateState($discountFields)->append($stateFields);
	}

	private function generatePredictions(State $state)
	{
		$generateProductPredictions = $this->generateProductConditions(
			array_unique(array_merge($state->get('discount_cond_product', array()), $state->get('discount_product', array())))
		);
		$generateSectionPredictions = $this->generateSectionConditions(
			array_unique(array_merge($state->get('discount_cond_section', array()), $state->get('discount_section', array())))
		);

		$predictions = array(
			'CLASS_ID' => 'CondGroup',
			'DATA' => array(
				'All' => 'AND',
				'True' => 'True',
			),
			'CHILDREN' => array(
				array(
					'CLASS_ID' => 'CondGroup',
					'DATA' => array(
						'All' => 'OR',
						'True' => 'True',
					),
					'CHILDREN' => array(
						$generateSectionPredictions ? array(
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
									'CHILDREN' => $generateSectionPredictions,
								),
							),
						) : array(),
						$generateProductPredictions ? array(
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
									'CHILDREN' => $generateProductPredictions,
								),
							),
						) : array(),
					),
				),
			),
		);

		return $predictions;
	}

	public function generateDiscount(State $state)
	{
		$generateProductConditions = $this->generateProductConditions($state->get('discount_cond_product'));
		$generateSectionConditions = $this->generateSectionConditions($state->get('discount_cond_section'));

		return array_merge(parent::generateDiscount($state), array(
			'PREDICTION_TEXT' => implode('|del|', array(
				$state->get('discount_prediction_text_act'),
				$state->get('discount_prediction_text_cond'),
			)),
			'PREDICTIONS' => $this->generatePredictions($state),
			'CONDITIONS' => array(
				'CLASS_ID' => 'CondGroup',
				'DATA' => array(
					'All' => 'AND',
					'True' => 'True',
				),
				'CHILDREN' => array(
					array(
						'CLASS_ID' => 'CondGroup',
						'DATA' => array(
							'All' => 'OR',
							'True' => 'True',
						),
						'CHILDREN' => array(
							$generateSectionConditions? array(
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
										'CHILDREN' => $generateSectionConditions,
									),
								),
							) : array(),
							$generateProductConditions? array(
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
										'CHILDREN' => $generateProductConditions,
									),
								),
							) : array(),
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

	public function getDescribedDataProductCondition(State $state)
	{
		$product = $state->get('discount_cond_product', array());
		$section = $state->get('discount_cond_section', array());

		if($product)
		{
			return array($this::TYPE_PRODUCT, $product);
		}
		elseif($section)
		{
			return array($this::TYPE_SECTION, $section);
		}

		return null;
	}

	public function getDescribedDataProductAction(State $state)
	{
		$product = $state->get('discount_product', array());
		$section = $state->get('discount_section', array());

		if($product)
		{
			return array($this::TYPE_PRODUCT, $product);
		}
		elseif($section)
		{
			return array($this::TYPE_SECTION, $section);
		}

		return null;
	}

	public function getPredictionText(State $state, $type)
	{
		if($type === static::PREDICTION_TEXT_TYPE_ACTION)
		{
			return $state['discount_prediction_text_act'];
		}
		elseif($type === static::PREDICTION_TEXT_TYPE_CONDITION)
		{
			return $state['discount_prediction_text_cond'];
		}

		return null;
	}
}