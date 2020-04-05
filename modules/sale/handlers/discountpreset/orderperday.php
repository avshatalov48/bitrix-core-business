<?php

namespace Sale\Handlers\DiscountPreset;


use Bitrix\Iblock\SectionTable;
use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Discount\Preset\ArrayHelper;
use Bitrix\Sale\Discount\Preset\BasePreset;
use Bitrix\Sale\Discount\Preset\HtmlHelper;
use Bitrix\Sale\Discount\Preset\Manager;
use Bitrix\Sale\Discount\Preset\State;
use Bitrix\Sale\Helpers\Admin\OrderEdit;
use Bitrix\Sale\Internals;
use Bitrix\Sale\Helpers\Admin\Blocks;
use Bitrix\Sale\Order;


Loc::loadMessages(__FILE__);

class OrderPerDay extends BasePreset
{
	public function getSort()
	{
		return 200;
	}
	
	public function getTitle()
	{
		return Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ORDER_PERDAY_NAME');
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

		$days = array(
			1 => Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ORDER_PERDAY_DAY_OF_WEEK_1'),
			2 => Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ORDER_PERDAY_DAY_OF_WEEK_2'),
			3 => Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ORDER_PERDAY_DAY_OF_WEEK_3'),
			4 => Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ORDER_PERDAY_DAY_OF_WEEK_4'),
			5 => Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ORDER_PERDAY_DAY_OF_WEEK_5'),
			6 => Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ORDER_PERDAY_DAY_OF_WEEK_6'),
			7 => Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ORDER_PERDAY_DAY_OF_WEEK_7')
		);

		$sectionCount = count($state->get('discount_section', array()));

		return '
			<table width="100%" border="0" cellspacing="7" cellpadding="0">
				<tbody>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:40%;"><strong>' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ORDER_PERDAY_ORDER_DISCOUNT_VALUE') . ':</strong></td>
					<td class="adm-detail-content-cell-r" style="width:60%;">
						<input type="text" name="discount_value" value="' . htmlspecialcharsbx($state->get('discount_value')) . '" maxlength="100" style="width: 100px;"> <span>' . $currency . '</span>
					</td>
				</tr>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:40%;"><strong>' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ORDER_PERDAY_DAY_LABEL') . ':</strong></td>
					<td class="adm-detail-content-cell-r">
						' . HtmlHelper::generateMultipleSelect('discount_days[]', $days, $state->get('discount_days', array()), array('size=7')) . '
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
			$this->errorCollection[] = new Error(Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ERROR_EMPTY_VALUE'));
		}

		if(!$state->get('discount_days'))
		{
			$this->errorCollection[] = new Error(Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ERROR_EMPTY_VALUE_DAYS'));
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
			'discount_groups' => $this->getUserGroupsByDiscount($discountFields['ID']),
			'discount_value' => ArrayHelper::getByPath($discountFields, 'ACTIONS.CHILDREN.0.DATA.Value'),
			'discount_type' => ArrayHelper::getByPath($discountFields, 'ACTIONS.CHILDREN.0.DATA.Unit'),
			'discount_days' => ArrayHelper::getByPath($discountFields, 'CONDITIONS.CHILDREN.0.DATA.value'),
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
						'CLASS_ID' => 'CondSaleCmnDayOfWeek',
						'DATA' => array(
							'logic' => 'Equal',
							'value' => $state->get('discount_days'),
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
		));
	}
}