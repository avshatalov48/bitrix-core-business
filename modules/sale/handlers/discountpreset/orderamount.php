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

Loc::loadMessages(__FILE__);

class OrderAmount extends BasePreset
{
	protected function init()
	{
		parent::init();
		
		\CJSCore::RegisterExt('order_amount_preset', array(
			'js' => '/bitrix/js/sale/admin/discountpreset/order_amount_preset.js',
		));

		\CUtil::InitJSCore(array('order_amount_preset'));
	}

	public function getTitle()
	{
		return Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ORDERAMOUNT_NAME');
	}

	public function getDescription()
	{
		return Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ORDERAMOUNT_DESCRIPTION');
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

		$presetJsName = 'presetOrderAmount';

		$u = new \CAdminPopupEx(
			"menu_prediction_text",
			array(
				array(
					"TEXT" => Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ORDERAMOUNT_ORDER_DISCOUNT_PH_SHORTAGE'),
					"TITLE" => "",
					"ONCLICK" => $presetJsName .".insertVar('#SHORTAGE#', 'menu_prediction_text', 'prediction_text')",
				),
				array(
					"TEXT" => Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ORDERAMOUNT_ORDER_DISCOUNT_PH_DISCOUNT_VALUE'),
					"TITLE" => "",
					"ONCLICK" => $presetJsName .".insertVar('#DISCOUNT_VALUE#', 'menu_prediction_text', 'prediction_text')",
				),
			),
			array("zIndex" => 2000)
		);
		$popupHtml = $u->Show(true);

		return $popupHtml . '
			<script>
			var ' . $presetJsName . ' = new BX.Sale.Admin.DiscountPreset.OrderAmount();
			</script>
			<table width="100%" border="0" cellspacing="7" cellpadding="0">
				<tbody>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:40%;"><strong>' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ORDERAMOUNT_ORDER_AMOUNT') . ':</strong></td>
					<td class="adm-detail-content-cell-r" style="width:60%;">
						<input type="text" name="discount_order_amount" value="' . htmlspecialcharsbx($state->get('discount_order_amount')) . '" size="39" maxlength="100" style="width: 100px;"> <span>' . $currency . '</span>
					</td>
				</tr>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:40%;"><strong>' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ORDERAMOUNT_ORDER_DISCOUNT_VALUE') . ':</strong></td>
					<td class="adm-detail-content-cell-r" style="width:60%;">
						<input type="text" name="discount_value" value="' . htmlspecialcharsbx($state->get('discount_value')) . '" maxlength="100" style="width: 100px;"> <span>' . $currency . '</span>
					</td>
				</tr>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:40%;"><strong>' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ORDERAMOUNT_ORDER_DISCOUNT_PREDICTION_VALUE') . ':</strong></td>
					<td class="adm-detail-content-cell-r" style="width:60%;">
						<input type="text" name="discount_prediction_value" value="' . htmlspecialcharsbx($state->get('discount_prediction_value')) . '" maxlength="100" style="width: 100px;"> <span>' . $currency . '</span>
					</td>
				</tr>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:40%;"><strong>' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ORDERAMOUNT_ORDER_DISCOUNT_PREDICTION_TEXT') . ':</strong></td>
					<td class="adm-detail-content-cell-r" style="width:60%;">
						<textarea name="discount_prediction_text" id="prediction_text" cols="55" rows="1" style="width: 90%; margin-top: 0px; margin-bottom: 0px; height: 50px;">' . htmlspecialcharsbx($state->get('discount_prediction_text', Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ORDERAMOUNT_ORDER_DISCOUNT_PREDICTION_TEXT_DEFAULT'))) . '</textarea>
						<input style="float:right" type="button" id="menu_prediction_text" value="...">
					</td>
				</tr>
				</tbody>
			</table>
		';
	}

	public function processSaveInputAmount(State $state)
	{
		if(!trim($state->get('discount_order_amount')))
		{
			$this->errorCollection[] = new Error(Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ERROR_EMPTY_ORDER_AMOUNT'));
		}

		if(!trim($state->get('discount_value')))
		{
			$this->errorCollection[] = new Error(Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ERROR_EMPTY_VALUE'));
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
		
		$stateFields = array(
			'discount_lid' => $discountFields['LID'],
			'discount_name' => $discountFields['NAME'],
			'discount_prediction_text' => $discountFields['PREDICTION_TEXT'],
			'discount_groups' => $this->getUserGroupsByDiscount($discountFields['ID']),
			'discount_order_amount' => ArrayHelper::getByPath($discountFields, 'CONDITIONS.CHILDREN.0.DATA.Value'),
			'discount_value' => ArrayHelper::getByPath($discountFields, 'ACTIONS.CHILDREN.0.DATA.Value'),
			'discount_type' => ArrayHelper::getByPath($discountFields, 'ACTIONS.CHILDREN.0.DATA.Unit'),
			'discount_prediction_value' => ArrayHelper::getByPath($discountFields, 'PREDICTIONS.CHILDREN.0.DATA.Value'),
		);

		return parent::generateState($discountFields)->append($stateFields);
	}

	public function generateDiscount(State $state)
	{
		$predictions = null;
		if($state->get('discount_prediction_value'))
		{
			$predictions = array(
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
							'Value' => $state->get('discount_prediction_value'),
							'All' => 'AND',
						),
						'CHILDREN' => array(),
					),
				),
			);
		}

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
							'Type' => 'Discount',
							'Value' => $state->get('discount_value'),
							'Unit' => $state->get('discount_type', 'CurAll'),
							'Max' => 0,
							'All' => 'AND',
							'True' => 'True',
						),
						'CHILDREN' => array(),
					),
				),
			),
			'PREDICTIONS' => $predictions,
			'PREDICTION_TEXT' => $state->get('discount_prediction_text'),
		));
	}
}