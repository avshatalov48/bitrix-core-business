<?php

namespace Sale\Handlers\DiscountPreset;

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;
use Bitrix\Sale\Discount\Actions;
use Bitrix\Sale\Discount\CumulativeCalculator;
use Bitrix\Sale\Discount\Preset\ArrayHelper;
use Bitrix\Sale\Discount\Preset\BasePreset;
use Bitrix\Sale\Discount\Preset\HtmlHelper;
use Bitrix\Sale\Discount\Preset\Manager;
use Bitrix\Sale\Discount\Preset\State;

final class Cumulative extends BasePreset
{
	const TYPE_FIXED   = Actions::VALUE_TYPE_FIX;
	const TYPE_PERCENT = Actions::VALUE_TYPE_PERCENT;

	const TYPE_COUNT_PERIOD_ALL_TIME = CumulativeCalculator::TYPE_COUNT_PERIOD_ALL_TIME;
	const TYPE_COUNT_PERIOD_INTERVAL = CumulativeCalculator::TYPE_COUNT_PERIOD_INTERVAL;
	const TYPE_COUNT_PERIOD_RELATIVE = CumulativeCalculator::TYPE_COUNT_PERIOD_RELATIVE;

	const MARK_DEFAULT_EMPTY_ROW = PHP_INT_MIN;

	public function getTitle()
	{
		return Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_CUMULATIVE_NAME');
	}

	public function getDescription()
	{
		return '';
	}

	public function getAvailableState(): int
	{
		if (Sale\Config\Feature::isCumulativeDiscountsEnabled())
		{
			return BasePreset::AVAILABLE_STATE_ALLOW;
		}
		if ($this->bitrix24Included)
		{
			return BasePreset::AVAILABLE_STATE_TARIFF;
		}

		return BasePreset::AVAILABLE_STATE_DISALLOW;
	}

	public function getAvailableHelpLink(): ?array
	{
		if ($this->getAvailableState() === BasePreset::AVAILABLE_STATE_TARIFF)
		{
			return Sale\Config\Feature::getCumulativeDiscountsHelpLink();
		}

		return null;
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
		return $this->processSaveInputNameInternal($state, 'InputRanges');
	}

	public function processShowInputRanges(State $state)
	{
		$lid = $state->get('discount_lid');
		$currency = \Bitrix\Sale\Internals\SiteCurrencyTable::getSiteCurrency($lid);

		$rows = $this->generateRows($state);
		$templateRow = $this->generateRow(-1, array(), 'display: none');

		return $this->generateJavascript() . '
			<table width="100%" border="0" cellspacing="7" cellpadding="0">
				<tbody>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:25%;"><strong>' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_LABEL_RANGES') . ':</strong></td>
					<td class="adm-detail-content-cell-r" style="width:75%;">
						<table id="range_table" style="width: auto;" class="internal" border="0" cellspacing="7" cellpadding="0">
							<tbody>
							<tr class="heading">
								<td align="center">' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_H_SUM', array('#CURRENCY#'  => $currency,)) . '</td>
								<td align="center">' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_H_DISCOUNT_VALUE') . '</td>
							</tr>
							' . $rows . '
							' . $templateRow . '
						</table>
						<div style="width: 100%; text-align: left; margin-top: 10px;">
							<input id="clone_range" class="adm-btn" type="button" value="' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_DISCOUNT_ADD_RANGE') . '">
						</div>
					</td>
				</tr>
				
				<tr>
					<td class="adm-detail-content-cell-l" style="width:25%;"><strong>' . Loc::getMessage('SALE_BASE_PRESET_ACTIVE_PERIOD') . ':</strong></td>
					<td class="adm-detail-content-cell-r">' .
					   HtmlHelper::generateSelect(
						   'discount_type_sum_period',
						   array(
							   self::TYPE_COUNT_PERIOD_ALL_TIME => Loc::getMessage("SALE_HANDLERS_DISCOUNTPRESET_CUMULATIVE_TYPE_COUNT_PERIOD_ALL_TIME"),
							   self::TYPE_COUNT_PERIOD_INTERVAL => Loc::getMessage("SALE_HANDLERS_DISCOUNTPRESET_CUMULATIVE_TYPE_COUNT_PERIOD_INTERVAL"),
							   self::TYPE_COUNT_PERIOD_RELATIVE => Loc::getMessage("SALE_HANDLERS_DISCOUNTPRESET_CUMULATIVE_TYPE_COUNT_PERIOD_RELATIVE"),
						   ),
						   $state['discount_type_sum_period']
					   ) . '
					</td>
				</tr>

				<tr id="tr_interval_start" class="js-date-interval" style="display: none;">
					<td class="adm-detail-content-cell-l" width="25%">' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_CUMULATIVE_PERIOD_START') . ':</td>
					<td class="adm-detail-content-cell-r" width="75%">' . \CAdminCalendar::CalendarDate('discount_sum_order_start', $state['discount_sum_order_start'], 19, true) . '</td>
				</tr>
				<tr id="tr_interval_end" class="js-date-interval" style="display: none;">
					<td class="adm-detail-content-cell-l" width="25%">' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_CUMULATIVE_PERIOD_END') . ':</td>
					<td class="adm-detail-content-cell-r" width="75%">' . \CAdminCalendar::CalendarDate('discount_sum_order_end', $state['discount_sum_order_end'], 19, true) . '</td>
				</tr>
				<tr id="tr_relative" class="js-date-relative" style="display: none;">
					<td class="adm-detail-content-cell-l" width="25%">' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_CUMULATIVE_PERIOD_RELATIVE') . ':</td>
					<td class="adm-detail-content-cell-r" width="75%"><input type="text" name="discount_sum_period_value" id="discount_sum_period_value" value="' . $state['discount_sum_period_value'] . '" size="7" maxlength="10">&nbsp;' .
					   HtmlHelper::generateSelect('discount_sum_period_type',
						  array(
							  'Y' => Loc::getMessage("SALE_HANDLERS_DISCOUNTPRESET_CUMULATIVE_PERIOD_RELATIVE_Y"),
							  'M' => Loc::getMessage("SALE_HANDLERS_DISCOUNTPRESET_CUMULATIVE_PERIOD_RELATIVE_M"),
							  'D' => Loc::getMessage("SALE_HANDLERS_DISCOUNTPRESET_CUMULATIVE_PERIOD_RELATIVE_D"),
						  ),
						  $state['discount_sum_period_type']
					   ) . '
					</td>
				</tr>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:25%;"><strong>' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_DONT_APPLY_IF_THERE_IS_DISCOUNTS') . ':</strong></td>
					<td class="adm-detail-content-cell-r">
						<input type="checkbox" name="discount_skip_if_there_were_discounts" value="Y" ' . ($state->get('discount_skip_if_there_were_discounts', 'N') == 'Y'? 'checked' : '') . '>
					</td>
				</tr>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:25%;"><strong>' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_APPLY_IF_MORE_PROFITABLE_DISCOUNT') . ':</strong></td>
					<td class="adm-detail-content-cell-r">
						<input type="checkbox" name="discount_apply_if_more_profitable" value="Y" ' . ($state->get('discount_apply_if_more_profitable', 'N') == 'Y'? 'checked' : '') . '>
					</td>
				</tr>
				
				</tbody>	
			</table>
			<script>
				BX.ready(function(){
					BX.bind(BX("discount_type_sum_period"), "change", function(){
						var table = BX("count_period_table");
						var trIntervalStart = BX("tr_interval_start");
						var trIntervalEnd = BX("tr_interval_end");
						var trRelative = BX("tr_relative");
						
						switch(this.value)
						{
							case "' . self::TYPE_COUNT_PERIOD_ALL_TIME . '":
								BX.hide(trIntervalStart, "table-row");
								BX.hide(trIntervalEnd, "table-row");
								BX.hide(trRelative, "table-row");
								
								break;								
							case "' . self::TYPE_COUNT_PERIOD_INTERVAL . '":
								BX.hide(trRelative, "table-row");
								BX.show(trIntervalStart, "table-row");
								BX.show(trIntervalEnd, "table-row");
							
								break;								
							case "' . self::TYPE_COUNT_PERIOD_RELATIVE . '":
								BX.show(trRelative, "table-row");
								BX.hide(trIntervalStart, "table-row");
								BX.hide(trIntervalEnd, "table-row");
								
								break;									
						}
					});
					setTimeout(function(){
						BX.fireEvent(BX("discount_type_sum_period"), "change");
					}, 300);
				})
			</script>
		';
	}

	protected function generateJavascript()
	{
		return '
			<script>
				BX.ready(function(){
					BX.bind(BX("clone_range"), "click", function(){
						var row = BX("range_-1").cloneNode(true);
						row.id = "";
						BX.insertAfter(row, BX.lastChild(BX("range_table")));
						row.style.display = "";
					});
				});		
			</script>
		';
	}

	protected function generateRows(State $state)
	{
		$html = '';
		foreach ($state->get('discount_ranges', $this->getDefaultRowValues()) as $i => $range)
		{
			if (
				($range['sum'] === '' || $range['sum'] === null) ||
				empty($range['value']) ||
				empty($range['type'])
			)
			{
				continue;
			}

			$html .= $this->generateRow($i, $range);
		}

		return $html;
	}

	private function fillValueInsteadMarkedEmpty(&$value)
	{
		if ($value === self::MARK_DEFAULT_EMPTY_ROW)
		{
			$value = '';
		}
	}

	protected function generateRow($index, array $range, $style = '')
	{
		$sum = $range['sum'] ?? '';
		$value = $range['value'] ?? '';
		$type = $range['type'] ?? '';

		$this->fillValueInsteadMarkedEmpty($sum);
		$this->fillValueInsteadMarkedEmpty($value);
		$this->fillValueInsteadMarkedEmpty($type);

		return '
			<tr id="range_' . $index . '" style="' . $style . '">
				<td>
					' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_DISCOUNT_SUM_GREATER_THAN') . '
					<input type="text" name="range_sum[]" size="13" value="' . $sum . '">
				</td>
				<td>
					<input type="text" name="range_value[]" size="13" value="' . $value . '"> 
					' . $this->generateSelectWithDiscountType("range_type[]", $type) . '
				</td>
			</tr>		
		';
	}

	protected function generateSelectWithDiscountType($name, $selectedValue)
	{
		return HtmlHelper::generateSelect(
			$name,
			array(
				self::TYPE_PERCENT => Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_DISCOUNT_TYPE_PERCENT'),
				self::TYPE_FIXED => Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_DISCOUNT_TYPE_VALUE'),
			),
			$selectedValue
		);
	}

	protected function buildRangesFromState(State $state)
	{
		/** @var array $rangeSum */
		$rangeSum = $state->get('range_sum', array());
		/** @var array $rangeValue */
		$rangeValue = $state->get('range_value', array());
		/** @var array $rangeType */
		$rangeType = $state->get('range_type', array());

		$matrix = array();
		foreach ($rangeSum as $i => $item)
		{
			if (empty($rangeValue[$i]) && empty($rangeSum[$i]))
			{
				continue;
			}

			if (!isset($rangeValue[$i]))
			{
				$this->errorCollection[] = new Error(Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_CUMULATIVE_ERROR_BAD_RANGE'));

				return null;
			}

			$rangeValue[$i] = str_replace(",", ".", $rangeValue[$i]);
			$rangeValue[$i] = doubleval($rangeValue[$i]);

			if ($rangeValue[$i] <= 0)
			{
				$this->errorCollection[] = new Error(Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_CUMULATIVE_ERROR_RANGE_VALUE'));

				return null;
			}

			if ($rangeType[$i] === self::TYPE_PERCENT && $rangeValue[$i] > 100)
			{
				$this->errorCollection[] = new Error(Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_CUMULATIVE_ERROR_RANGE_VALUE'));

				return null;
			}

			$matrix[] = array(
				'sum' => $rangeSum[$i],
				'value' => $rangeValue[$i],
				'type' => $rangeType[$i]?: self::TYPE_PERCENT,
			);
		}

		if (!$matrix)
		{
			return null;
		}

		Main\Type\Collection::sortByColumn($matrix, 'sum');

		$prevSum = null;
		foreach ($matrix as $row)
		{
			if ($row['sum'] === $prevSum)
			{
				$this->errorCollection[] = new Error(Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_CUMULATIVE_ERROR_RANGE_FROM_DUPLICATE'));

				return null;
			}
		}

		return $matrix;
	}

	public function processSaveInputRanges(State $state)
	{
		$ranges = $this->buildRangesFromState($state);
		$state['discount_ranges'] = $ranges;

		if ($ranges)
		{
			unset($state['range_sum']);
			unset($state['range_value']);
			unset($state['range_type']);
		}

		switch ($state['discount_type_sum_period'])
		{
			case self::TYPE_COUNT_PERIOD_ALL_TIME:
				unset($state['discount_sum_order_start']);
				unset($state['discount_sum_order_end']);
				unset($state['discount_sum_period_value']);
				unset($state['discount_sum_period_type']);

				break;
			case self::TYPE_COUNT_PERIOD_INTERVAL:
				unset($state['discount_sum_period_value']);
				unset($state['discount_sum_period_type']);

				break;
			case self::TYPE_COUNT_PERIOD_RELATIVE:
				unset($state['discount_sum_order_start']);
				unset($state['discount_sum_order_end']);

				break;
		}

		if($state['discount_skip_if_there_were_discounts'] !== 'Y' || !$this->request->getPost('discount_skip_if_there_were_discounts'))
		{
			$state['discount_skip_if_there_were_discounts'] = 'N';
		}

		if($state['discount_apply_if_more_profitable'] !== 'Y' || !$this->request->getPost('discount_apply_if_more_profitable'))
		{
			$state['discount_apply_if_more_profitable'] = 'N';
		}

		if(!$ranges)
		{
			$this->errorCollection[] = new Error(Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ERROR_EMPTY_VALUE'));
		}

		if(!$this->errorCollection->isEmpty())
		{
			return array($state, 'InputRanges');
		}

		return array($state, 'CommonSettings');
	}

	protected function getAllowableUserGroups()
	{
		$allowableUserGroups = parent::getAllowableUserGroups();

		unset($allowableUserGroups[2]); //There is nonsense to use cumulative discounts for guests.

		return $allowableUserGroups;
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

		$firstChildrenControlId = ArrayHelper::getByPath($discountFields, 'ACTIONS.CHILDREN.0.CHILDREN.0.CLASS_ID') === \CSaleActionCondCtrlBasketFields::CONTROL_ID_APPLIED_DISCOUNT;
		$stateFields = array(
			'discount_lid' => $discountFields['LID'],
			'discount_name' => $discountFields['NAME'],
			'discount_groups' => $this->getUserGroupsByDiscount($discountFields['ID']),
			'discount_ranges' => ArrayHelper::getByPath($discountFields, 'ACTIONS.CHILDREN.0.DATA.ranges'),
			'discount_type_sum_period' => ArrayHelper::getByPath($discountFields, 'ACTIONS.CHILDREN.0.DATA.type_sum_period'),
			'discount_skip_if_there_were_discounts' => $firstChildrenControlId? 'Y' : '',
			'discount_apply_if_more_profitable' => ArrayHelper::getByPath($discountFields, 'ACTIONS.CHILDREN.0.DATA.apply_if_more_profitable'),
			'discount_sum_order_start' => '',
			'discount_sum_order_end' => '',
			'discount_sum_period_value' => '',
			'discount_sum_period_type' => '',
		);

		$periodData = ArrayHelper::getByPath($discountFields, 'ACTIONS.CHILDREN.0.DATA.sum_period_data', array());
		$stateFields = array_merge($stateFields, array_filter(array_intersect_key($periodData, array(
			'discount_sum_order_start'  => true,
			'discount_sum_order_end'  => true,
			'discount_sum_period_value'  => true,
			'discount_sum_period_type'  => true,
		))));

		return parent::generateState($discountFields)->append($stateFields);
	}

	public function generateDiscount(State $state)
	{
		$periodData = array(
			'discount_sum_order_start' => $state['discount_sum_order_start'],
			'discount_sum_order_end' => $state['discount_sum_order_end'],
			'discount_sum_period_value' => $state['discount_sum_period_value'],
			'discount_sum_period_type' => $state['discount_sum_period_type'],
		);

		$filterChildren = array();
		if ($state['discount_skip_if_there_were_discounts'] === 'Y')
		{
			$filterChildren = array(
				array(
					'CLASS_ID'  => \CSaleActionCondCtrlBasketFields::CONTROL_ID_APPLIED_DISCOUNT,
					'DATA' => array(
						'logic' => 'Equal',
						'value' => 'N',
					),
				)
			);
		}

		return array_merge(parent::generateDiscount($state), array(
			'CONDITIONS' => array(
				'CLASS_ID' => 'CondGroup',
				'DATA' => array(
					'All' => 'AND',
					'True' => 'True',
				),
				'CHILDREN' => array(),
			),
			'ACTIONS' => array(
				'CLASS_ID' => 'CondGroup',
				'DATA' => array(
					'All' => 'AND',
				),
				'CHILDREN' => array(
					array(
						'CLASS_ID' => \CSaleCumulativeAction::getControlID(),
						'DATA' => array(
							'ranges' => $state['discount_ranges'],
							'type_sum_period' => $state['discount_type_sum_period'],
							'sum_period_data' => $periodData,
							'apply_if_more_profitable' => $state['discount_apply_if_more_profitable'],
							'All' => 'AND',
							'True' => 'True',
						),
						'CHILDREN' => $filterChildren,
					),
				),
			),
		));
	}

	/**
	 * @return array
	 */
	protected function getDefaultRowValues()
	{
		return array(
			array(
				'sum' => self::MARK_DEFAULT_EMPTY_ROW,
				'value' => self::MARK_DEFAULT_EMPTY_ROW,
				'type' => self::TYPE_PERCENT,
			),
			array(
				'sum' => self::MARK_DEFAULT_EMPTY_ROW,
				'value' => self::MARK_DEFAULT_EMPTY_ROW,
				'type' => self::TYPE_PERCENT,
			),
			array(
				'sum' => self::MARK_DEFAULT_EMPTY_ROW,
				'value' => self::MARK_DEFAULT_EMPTY_ROW,
				'type' => self::TYPE_PERCENT,
			),
			array(
				'sum' => self::MARK_DEFAULT_EMPTY_ROW,
				'value' => self::MARK_DEFAULT_EMPTY_ROW,
				'type' => self::TYPE_PERCENT,
			),
		);
	}
}