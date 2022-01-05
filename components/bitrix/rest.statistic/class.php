<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Entity;
use Bitrix\Main\UI\Filter\Options;
use Bitrix\Main\UI\Filter\DateType;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Grid;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\SystemException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Rest\UsageStatTable;
use Bitrix\Rest\UsageEntityTable;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\APAuth\PasswordTable;

Loc::loadMessages(__FILE__);

class CRestStatisticComponent extends CBitrixComponent implements Controllerable
{
	const PREFIX = 'data_';
	const PREFIX_HOURS_PROPS_CODE = 'HOUR_';
	const LAST_HOURS_PROPS_CODE = 23;
	const PAGE_LIMIT = 20;

	/**
	 * Check required params.
	 *
	 * @throws SystemException
	 * @throws LoaderException
	 */
	protected function checkRequiredParams()
	{
		if (!Loader::includeModule('rest'))
		{
			throw new SystemException('Module `rest` is not installed.');
		}

		if (!\CRestUtil::isAdmin())
		{
			ShowError(Loc::getMessage('REST_STATISTIC_ACCESS_DENIED'));
			return false;
		}

		return true;
	}

	public function onPrepareComponentParams($params)
	{
		$result = [
			"CACHE_TYPE" => 'N',
			"CACHE_TIME" => 0,
			"TYPE" => 'day',
			'MORE_FILTER' => []
		];

		if (!empty($params['FILTER_NAME']))
		{
			$result['FILTER_NAME'] = htmlspecialcharsbx($params['FILTER_NAME']);
		}
		else
		{
			$result['FILTER_NAME'] = 'appFilterHistoryChart';
		}

		if (isset($params['ONLY_ACTIVE']))
		{
			$result['ONLY_ACTIVE'] = htmlspecialcharsbx($params['ONLY_ACTIVE']);
		}
		else
		{
			$result['ONLY_ACTIVE'] = 'Y';
		}

		if (is_array($params['SHOW_FILTER_SUB_ENTITY_TYPE']))
		{
			$result['SHOW_FILTER_SUB_ENTITY_TYPE'] = $params['SHOW_FILTER_SUB_ENTITY_TYPE'];
		}
		else
		{
			$result['SHOW_FILTER_SUB_ENTITY_TYPE'] = [
				'SUB_ENTITY_TYPE_METHOD' => UsageEntityTable::SUB_ENTITY_TYPE_METHOD,
				'SUB_ENTITY_TYPE_EVENT' => UsageEntityTable::SUB_ENTITY_TYPE_EVENT,
				'SUB_ENTITY_TYPE_PLACEMENT' => UsageEntityTable::SUB_ENTITY_TYPE_PLACEMENT,
				'SUB_ENTITY_TYPE_ROBOT' => UsageEntityTable::SUB_ENTITY_TYPE_ROBOT,
				'SUB_ENTITY_TYPE_ACTIVITY' => UsageEntityTable::SUB_ENTITY_TYPE_ACTIVITY,
				'SUB_ENTITY_TYPE_SEND_MESSAGE' => UsageEntityTable::SUB_ENTITY_TYPE_SEND_MESSAGE,
			];
		}

		if ($params['MAX_SECOND_RANGE_DATE_FILTER'] > 0)
		{
			$result['MAX_SECOND_RANGE_DATE_FILTER'] = $params['MAX_SECOND_RANGE_DATE_FILTER'];
		}
		else
		{
			$result['MAX_SECOND_RANGE_DATE_FILTER'] = 1209600;//default 2 week
		}

		if (!empty($params['GRID_ID']))
		{
			$result['GRID_ID'] = htmlspecialcharsbx($params['GRID_ID']);
		}
		else
		{
			$result['GRID_ID'] = $result['FILTER_NAME'] . 'Grid';
		}

		$result['MAX_DAYS_RANGE_DATE_FILTER'] = $result['MAX_SECOND_RANGE_DATE_FILTER'] / 86400;
		$result['APP_ID'] = intVal($params['APP_ID']);
		$result['PASSWORD_ID'] = intVal($params['PASSWORD_ID']);
		$result['ONE_APP_MODE'] = ($result['APP_ID'] > 0 || $result['PASSWORD_ID'] > 0);
		$result['SET_TITLE'] = isset($result['SET_TITLE']) ? $result['SET_TITLE'] === 'Y' : true;

		return $result;
	}

	protected function listKeysSignedParameters()
	{
		return [
			'FILTER_NAME',
			'ONLY_ACTIVE',
			'APP_ID',
			'PASSWORD_ID',
			'GRID_ID',
			'MAX_DAYS_RANGE_DATE_FILTER',
			'SHOW_FILTER_SUB_ENTITY_TYPE',
			'MAX_SECOND_RANGE_DATE_FILTER'
		];
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	private function getAllApp()
	{
		$allApp = [];
		$cacheTime = 3600;
		$filter = [];
		if ($this->arParams['ONLY_ACTIVE'] === 'Y')
		{
			$filter['=ACTIVE'] = 'Y';
		}

		$resAppData = AppTable::getList(
			[
				'order' => 'APP_NAME',
				'filter' => $filter,
				'select' => [
					'ID',
					'CODE',
					'ACTIVE',
					'APP_NAME',
					'STATUS'
				],
				'cache' => [
					'ttl' => $cacheTime
				]
			]
		);
		$prefix = self::PREFIX;
		while ($appData = $resAppData->Fetch())
		{
			$appData['NAME'] = (!empty($appData['APP_NAME'])) ? $appData['APP_NAME'] : $appData['CODE'];
			$allApp[$prefix . $appData['ID']] = $appData;
		}

		return $allApp;
	}

	/**
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	private function getAllPasswordApp()
	{
		$result = [];
		$cacheTime = 3600;
		$prefix = self::PREFIX;

		$filter = [];
		if ($this->arParams['ONLY_ACTIVE'] === 'Y')
		{
			$filter['=ACTIVE'] = 'Y';
		}

		$resAppData = PasswordTable::getList(
			[
				'order' => 'ID',
				'filter' => $filter,
				'select' => [
					'ID',
					'ACTIVE',
					'TITLE'
				],
				'cache' => [
					'ttl' => $cacheTime
				]
			]
		);
		while ($data = $resAppData->Fetch())
		{
			if (!empty($data['TITLE']))
			{
				$data['NAME'] = $data['TITLE'];
			}
			else
			{
				$data['NAME'] = Loc::getMessage(
					'REST_STATISTIC_PASSWORD_NAME_NUMBER',
					[
						'#NUM#' => $data['ID']
					]
				);
			}
			$result[$prefix . $data['ID']] = $data;
		}

		return $result;
	}

	/**
	 * @return array
	 * @throws ObjectException
	 */
	protected function getFilter()
	{
		$result = [];
		$filterOption = (new Options($this->arParams["FILTER_NAME"]));
		if ($filter = $filterOption->getFilter())
		{
			$filterAppId = [];
			$filterPasswordId = [];
			$prefixHoursPropsCode = self::PREFIX_HOURS_PROPS_CODE;

			if (is_array($filter['APP_ID']))
			{
				foreach ($filter['APP_ID'] as $id)
				{
					$filterAppId[] = intVal($id);
				}
			}
			if (is_array($filter['PASSWORD_ID']))
			{
				foreach ($filter['PASSWORD_ID'] as $id)
				{
					$filterPasswordId[] = intVal($id);
				}
			}

			if ($filterPasswordId || $filterAppId)
			{
				if ($filterPasswordId && $filterAppId)
				{
					$result[] = [
						'LOGIC' => 'OR',
						[
							'=ENTITY_DATA_ENTITY_ID' => $filterAppId,
							'=ENTITY_DATA_ENTITY_TYPE' => UsageEntityTable::ENTITY_TYPE_APPLICATION
						],
						[
							'=ENTITY_DATA_ENTITY_ID' => $filterPasswordId,
							'=ENTITY_DATA_ENTITY_TYPE' => UsageEntityTable::ENTITY_TYPE_WEBHOOK
						],
					];
				}
				elseif ($filterAppId)
				{
					$result['=ENTITY_DATA_ENTITY_ID'] = $filterAppId;
					$result['=ENTITY_DATA_ENTITY_TYPE'] = UsageEntityTable::ENTITY_TYPE_APPLICATION;
					$this->arParams["TYPE"] = 'one_app';
				}
				elseif ($filterPasswordId)
				{
					$result['=ENTITY_DATA_ENTITY_ID'] = $filterPasswordId;
					$result['=ENTITY_DATA_ENTITY_TYPE'] = UsageEntityTable::ENTITY_TYPE_WEBHOOK;
					$this->arParams["TYPE"] = 'one_app';
				}
			}

			switch ($filter[$prefixHoursPropsCode . 'TOTAL_numsel'])
			{
				case 'exact':
					$result['=' . $prefixHoursPropsCode . 'TOTAL'] = intVal(
						$filter[$prefixHoursPropsCode . 'TOTAL_from']
					);

					break;
				case 'range':
					$result['>=' . $prefixHoursPropsCode . 'TOTAL'] = intVal(
						$filter[$prefixHoursPropsCode . 'TOTAL_from']
					);
					$result['<=' . $prefixHoursPropsCode . 'TOTAL'] = intVal(
						$filter[$prefixHoursPropsCode . 'TOTAL_to']
					);

					break;
				case 'more':
					$result['>' . $prefixHoursPropsCode . 'TOTAL'] = intVal(
						$filter[$prefixHoursPropsCode . 'TOTAL_from']
					);

					break;
				case 'less':
					$result['<' . $prefixHoursPropsCode . 'TOTAL'] = intVal(
						$filter[$prefixHoursPropsCode . 'TOTAL_to']
					);

					break;
			}
		}
		else
		{
			$filter = [];
		}

		if ($filter['ENTITY_DATA_SUB_ENTITY_NAME'])
		{
			$result['=ENTITY_DATA_SUB_ENTITY_NAME'] = $filter['ENTITY_DATA_SUB_ENTITY_NAME'];
		}

		if ($filter['ENTITY_DATA_SUB_ENTITY_TYPE'])
		{
			$result['=ENTITY_DATA_SUB_ENTITY_TYPE'] = $filter['ENTITY_DATA_SUB_ENTITY_TYPE'];
		}

		if (empty($filter['STAT_DATE_from']))
		{
			if (empty($filter['STAT_DATE_to']))
			{
				$date = new DateTime();
				$date->add('-' . (int)$this->arParams['MAX_DAYS_RANGE_DATE_FILTER'] . 'D');
				$filter['STAT_DATE_from'] = $date;
			}
			else
			{
				$filter['STAT_DATE_from'] = new DateTime(date("Y-m-d") . " 00:00:00", "Y-m-d H:i:s");
			}
		}

		if (empty($filter['STAT_DATE_to']))
		{
			$date = clone $filter['STAT_DATE_from'];
			$date->add((int)$this->arParams['MAX_DAYS_RANGE_DATE_FILTER'] . 'D');
			$filter['STAT_DATE_to'] = $date;
		}
		else
		{
			$startDate = strtotime($filter['STAT_DATE_from']);
			$endDate = strtotime($filter['STAT_DATE_to']);
			if (($endDate - $startDate) > $this->arParams['MAX_SECOND_RANGE_DATE_FILTER'])
			{
				$date = date(
					"Y-m-d",
					strtotime($filter['STAT_DATE_from']) + $this->arParams['MAX_SECOND_RANGE_DATE_FILTER']
				);
				$filter['STAT_DATE_to'] = new DateTime($date . " 23:59:59", "Y-m-d H:i:s");
			}
		}

		$result['>=STAT_DATE'] = $filter['STAT_DATE_from'];
		$result['<=STAT_DATE'] = $filter['STAT_DATE_to'];

		if ($this->arParams['ONE_APP_MODE'])
		{
			$this->arParams["TYPE"] = 'one_app';
			if ($this->arParams["APP_ID"] > 0)
			{
				$result['=ENTITY_DATA_ENTITY_ID'] = $this->arParams["APP_ID"];
				$result['=ENTITY_DATA_ENTITY_TYPE'] = UsageEntityTable::ENTITY_TYPE_APPLICATION;
			}
			if ($this->arParams["PASSWORD_ID"] > 0)
			{
				$result['=ENTITY_DATA_ENTITY_ID'] = $this->arParams["PASSWORD_ID"];
				$result['=ENTITY_DATA_ENTITY_TYPE'] = UsageEntityTable::ENTITY_TYPE_WEBHOOK;
			}
		}

		if (
			isset($filter['STAT_DATE_datesel'])
			&&
			(
				$filter['STAT_DATE_datesel'] === 'EXACT'
				|| $filter['STAT_DATE_datesel'] === 'YESTERDAY'
				|| $filter['STAT_DATE_datesel'] === 'CURRENT_DAY'
				|| $filter['STAT_DATE_datesel'] === 'TOMORROW'
			)
		)
		{
			if ($this->arParams["TYPE"] === 'one_app')
			{
				$this->arParams["TYPE"] = 'one_day_one_app';
			}
			else
			{
				$this->arParams["TYPE"] = 'one_day';
			}
		}

		return $result;
	}

	protected function getGridHeader()
	{
		$result = [
			[
				'id' => 'DATE',
				'name' => Loc::getMessage('REST_STATISTIC_GRID_HEADER_DATE'),
				'sort' => 'STAT_DATE',
				'default' => true
			],
			[
				'id' => 'NAME',
				'name' => Loc::getMessage('REST_STATISTIC_GRID_HEADER_NAME'),
				'sort' => false,
				'default' => true
			],
			[
				'id' => 'ENTITY_DATA_SUB_ENTITY_NAME',
				'name' => Loc::getMessage('REST_STATISTIC_GRID_HEADER_SUB_ENTITY_NAME'),
				'sort' => 'ENTITY_DATA_SUB_ENTITY_NAME',
				'default' => true
			]
		];

		for ($i = 0; $i <= self::LAST_HOURS_PROPS_CODE; $i++)
		{
			$result[] = [
				'id' => self::PREFIX_HOURS_PROPS_CODE . $i,
				'name' => $i . ':00',
				'sort' => self::PREFIX_HOURS_PROPS_CODE . $i,
				'default' => false
			];
		}
		$result[] = [
			'id' => self::PREFIX_HOURS_PROPS_CODE . 'TOTAL',
			'name' => Loc::getMessage('REST_STATISTIC_GRID_HEADER_TOTAL'),
			'sort' => self::PREFIX_HOURS_PROPS_CODE . 'TOTAL',
			'default' => true
		];

		return $result;
	}

	protected function getGridSort($oldSort)
	{
		$result = $oldSort;

		$gridOption = new Grid\Options($this->arParams["GRID_ID"]);
		if (($option = $gridOption->GetOptions()) && !empty($option['views']['default']['last_sort_by']))
		{
			$by = $option['views']['default']['last_sort_by'];
			$order = ($option['views']['default']['last_sort_order'] == 'asc') ? 'ASC' : 'DESC';
			$result = [
				$by => $order
			];
		}

		return $result;
	}

	protected function getGridFilterHeader()
	{
		$result = [];
		$result[] = [
			'id' => 'STAT_DATE',
			'name' => Loc::getMessage(
				'REST_STATISTIC_FILTER_HEADER_DATE',
				['#DAYS#' => $this->arParams['MAX_DAYS_RANGE_DATE_FILTER']]
			),
			'type' => 'date',
			'exclude' => [
				DateType::YEAR,
				DateType::MONTH,
				DateType::QUARTER,
				DateType::NEXT_DAYS,
				DateType::NEXT_WEEK,
				DateType::NEXT_MONTH,
				DateType::LAST_MONTH,
				DateType::LAST_30_DAYS,
				DateType::LAST_60_DAYS,
				DateType::LAST_90_DAYS,
				DateType::TOMORROW,
				DateType::CURRENT_QUARTER,
				DateType::CURRENT_MONTH,
				DateType::CURRENT_WEEK,
			],
			'default' => true
		];
		if (!$this->arParams['ONE_APP_MODE'])
		{
			$result[] = [
				'id' => 'APP_ID',
				'name' => Loc::getMessage('REST_STATISTIC_FILTER_HEADER_APP'),
				'type' => 'list',
				'items' => array_column($this->arResult['APP'], 'NAME', 'ID'),
				'params' => [
					'multiple' => 'Y'
				],
				'default' => true
			];
			$result[] = [
				'id' => 'PASSWORD_ID',
				'name' => Loc::getMessage('REST_STATISTIC_FILTER_HEADER_AP'),
				'type' => 'list',
				'items' => array_column($this->arResult['PASSWORD_APP'], 'NAME', 'ID'),
				'params' => [
					'multiple' => 'Y'
				],
				'default' => true
			];
		}

		$result[] = [
			'id' => self::PREFIX_HOURS_PROPS_CODE . 'TOTAL',
			'name' => Loc::getMessage('REST_STATISTIC_FILTER_HEADER_TOTAL_COUNT'),
			'type' => 'number',
			'default' => true
		];
		$result[] = [
			'id' => 'ENTITY_DATA_SUB_ENTITY_NAME',
			'name' => Loc::getMessage('REST_STATISTIC_FILTER_SUB_ENTITY_NAME'),
			'type' => 'string',
			'default' => true,
			'params' => [
				'multiple' => 'Y'
			],
		];

		$subEntity = [];
		foreach ($this->arParams['SHOW_FILTER_SUB_ENTITY_TYPE'] as $k => $val)
		{
			$subEntity[$val] = loc::getMessage('REST_STATISTIC_' . $k);
		}
		$result[] = [
			'id' => 'ENTITY_DATA_SUB_ENTITY_TYPE',
			'name' => Loc::getMessage('REST_STATISTIC_FILTER_SUB_ENTITY_TYPE'),
			'type' => 'list',
			'items' => $subEntity,
			'params' => [
				'multiple' => 'Y'
			],
			'default' => true
		];

		return $result;
	}

	protected function prepareGraphsData($itemsStat)
	{
		$return = [];
		$prefix = self::PREFIX;
		$prefixHoursPropsCode = self::PREFIX_HOURS_PROPS_CODE;
		$type = $this->arParams["TYPE"];
		$idList = [];
		if (!empty($itemsStat))
		{
			switch ($type)
			{
				case 'day':
					foreach ($itemsStat as $item)
					{
						$key = $item['columns']['KEY_DATE'];
						$chartItems[$key]['category'] = $item['columns']['DATE'];
						if (!isset($chartItems[$key]['segments'][$prefix . $item['columns']['APP_ID']]['end']))
						{
							$chartItems[$key]['segments'][$prefix . $item['columns']['APP_ID']] = [
								'id' => $item['columns']['APP_ID'],
								'start' => 0,
								'end' => 0,
								'title' => $item['columns']['NAME'],
							];
							$idList[] = $item['columns']['APP_ID'];
						}
						$chartItems[$key]['segments'][$prefix . $item['columns']['APP_ID']]['end'] += $item['columns'][$prefixHoursPropsCode . 'TOTAL'];
					}
					break;
				case 'one_app':
					foreach ($itemsStat as $item)
					{
						$key = $item['columns']['KEY_DATE'];
						$chartItems[$key]['category'] = $item['columns']['DATE'];
						if (!isset($itemsStat[$key]['segments'][$prefix . $item['columns']['ENTITY_DATA_ID']]['end']))
						{
							$chartItems[$key]['segments'][$prefix . $item['columns']['ENTITY_DATA_ID']] = [
								'id' => $item['columns']['ENTITY_DATA_ID'],
								'start' => 0,
								'end' => 0,
								'title' => $item['columns']['ENTITY_DATA_SUB_ENTITY_NAME'],
							];
							$idList[] = $item['columns']['ENTITY_DATA_ID'];
						}
						$chartItems[$key]['segments'][$prefix .	$item['columns']['ENTITY_DATA_ID']]['end'] += $item['columns'][$prefixHoursPropsCode . 'TOTAL'];
					}
					break;
				case 'one_day':
					foreach ($itemsStat as $item)
					{
						$key = $item['columns']['KEY_DATE'];
						for ($i = 0; $i <= self::LAST_HOURS_PROPS_CODE; $i++)
						{
							$code = $prefixHoursPropsCode . $i;
							if (isset($item['columns'][$code]))
							{
								if (empty($chartItems[$key . $code]['category']))
								{
									$chartItems[$key . $code]['category'] = $i . ':00';
								}
								if (!isset($chartItems[$key . $code]['segments'][$prefix . $item['columns']['APP_ID']]))
								{
									$chartItems[$key . $code]['segments'][$prefix . $item['columns']['APP_ID']] = [
										'id' => $item['columns']['APP_ID'],
										'start' => 0,
										'end' => 0,
										'title' => $item['columns']['NAME'],
									];
									$idList[] = $item['columns']['APP_ID'];
								}
								$chartItems[$key . $code]['segments'][$prefix . $item['columns']['APP_ID']]['end'] += $item['columns'][$code];
							}
						}
					}
					break;
				case 'one_day_one_app':
					foreach ($itemsStat as $item)
					{
						$key = $item['columns']['KEY_DATE'];
						for ($i = 0; $i <= self::LAST_HOURS_PROPS_CODE; $i++)
						{
							$code = self::PREFIX_HOURS_PROPS_CODE . $i;
							if (isset($item['columns'][$code]))
							{
								if (empty($chartItems[$key . $code]['category']))
								{
									$chartItems[$key . $code]['category'] = $i . ':00';
								}
								if (
									!isset($chartItems[$key . $code]['segments'][$prefix .$item['columns']['ENTITY_DATA_ID']])
								)
								{
									$chartItems[$key . $code]['segments'][$prefix . $item['columns']['ENTITY_DATA_ID']] = [
										'id' => $item['columns']['ENTITY_DATA_ID'],
										'start' => 0,
										'end' => 0,
										'title' => $item['columns']['ENTITY_DATA_SUB_ENTITY_NAME']
									];
									$idList[] = $item['columns']['ENTITY_DATA_ID'];
								}
								$chartItems[$key . $code]['segments'][$prefix . $item['columns']['ENTITY_DATA_ID']]['end'] += $item['columns'][$code];
							}
						}
					}
					break;
			}

			if (!empty($chartItems))
			{
				foreach ($chartItems as $chartItem)
				{
					$return['DATA'][] = [
						'category' => $chartItem['category'],
						'segments' => array_values($chartItem['segments'])
					];
				}
				$return['ID_LIST'] = array_values(array_unique($idList));
			}
		}

		return $return;
	}

	/**
	 * @param $type string
	 *
	 * @throws SystemException
	 */
	protected function processResultData($type = 'grid')
	{
		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			/**@var CAllMain */
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('REST_STATISTIC_PAGE_TITLE'));
		}
		$prefix = self::PREFIX;
		$prefixHoursPropsCode = self::PREFIX_HOURS_PROPS_CODE;
		$prefixLastHoursPropsCode = self::LAST_HOURS_PROPS_CODE;
		$useNav = false;
		$itemsStat = [];
		$paramsGetList = [
			'order' => ['STAT_DATE'],
			'select' => [
				'*',
				'ENTITY_DATA_' =>'ENTITY'
			]
		];
		$allApp = $this->getAllApp();
		$allPasswordApp = $this->getAllPasswordApp();

		try
		{
			$filter = $this->getFilter();
		}
		catch (ObjectException $e)
		{
			throw new SystemException($e->getMessage());
		}

		$hoursCodes = [];
		for ($i = 0; $i <= $prefixLastHoursPropsCode; $i++)
		{
			$hoursCodes[] = $prefixHoursPropsCode . $i;
		}
		if (!empty($hoursCodes))
		{
			$paramsGetList['runtime'][] = new Entity\ExpressionField(
				$prefixHoursPropsCode . 'TOTAL', implode(' + ', $hoursCodes)
			);
			$paramsGetList['select'][] = $prefixHoursPropsCode . 'TOTAL';
		}

		$paramsGetList['filter'] = $filter;

		$nav = new PageNavigation($this->arParams['FILTER_NAME'] . 'nav');
		if ($type == 'grid')
		{
			$useNav = true;
			$paramsGetList['order'] = $this->getGridSort($paramsGetList['order']);
			$paramsGetList['limit'] = self::PAGE_LIMIT;
			$nav->setPageSize($paramsGetList['limit']);
			$nav->initFromUri();
			$paramsGetList['count_total'] = true;
			$paramsGetList['offset'] = $nav->getOffset();
			$paramsGetList['limit'] = $nav->getLimit();
		}

		$resMethodStat = UsageStatTable::getList(
			$paramsGetList
		);

		if ($useNav)
		{
			$nav->setRecordCount($resMethodStat->getCount());
		}

		while ($methodStat = $resMethodStat->Fetch())
		{
			$keyDate = $methodStat['STAT_DATE']->format("Ymd");
			$item = $methodStat;
			$item['id'] = $methodStat['ENTITY_DATA_ID'].$keyDate;
			$item['KEY_DATE'] = $keyDate;
			$item['DATE'] = $methodStat['STAT_DATE']->toString();

			if ($methodStat['ENTITY_DATA_ENTITY_TYPE'] == UsageEntityTable::ENTITY_TYPE_APPLICATION)
			{
				if (!empty($allApp[$prefix . $methodStat['ENTITY_DATA_ENTITY_ID']]['NAME']))
				{
					$item['NAME'] = htmlspecialcharsbx($allApp[$prefix . $methodStat['ENTITY_DATA_ENTITY_ID']]['NAME']);
				}
				else
				{
					$item['NAME'] = Loc::getMessage("REST_STATISTIC_UNKNOWN_APP_NAME");
				}
			}
			elseif ($methodStat['ENTITY_DATA_ENTITY_TYPE'] == UsageEntityTable::ENTITY_TYPE_WEBHOOK)
			{
				if (
					!empty($allPasswordApp[$prefix . $methodStat['ENTITY_DATA_ENTITY_ID']]['NAME'])
				)
				{
					$item['NAME'] = htmlspecialcharsbx($allPasswordApp[$prefix . $methodStat['ENTITY_DATA_ENTITY_ID']]['NAME']);
				}
				else
				{
					$item['NAME'] = Loc::getMessage(
						'REST_STATISTIC_DELETE_PASSWORD',
						[
							'#PASSWORD_ID#' => $methodStat['ENTITY_DATA_ENTITY_ID']
						]
					);
				}
			}
			$item['ENTITY_DATA_SUB_ENTITY_NAME'] = htmlspecialcharsbx($item['ENTITY_DATA_SUB_ENTITY_NAME']);

			$itemsStat[] = [
				'id' => $item['id'],
				'columns' => $item
			];
		}

		if ($type == 'graphs')
		{
			$this->arResult['CHART_DATA'] = $this->prepareGraphsData($itemsStat);
		}

		$this->arResult['APP'] = $allApp;
		$this->arResult['PASSWORD_APP'] = $allPasswordApp;

		$this->arResult['GRID_HEADERS'] = $this->getGridHeader();
		$this->arResult['GRID_ITEMS'] = $itemsStat;
		$this->arResult['FILTER'] = $this->getGridFilterHeader();

		$this->arResult['NAV_OBJECT'] = $nav;
	}

	public function executeComponent()
	{

		try
		{
			$this->processResultData('grid');
			if ($this->checkRequiredParams())
			{
				$this->includeComponentTemplate();
			}
		}
		catch (SystemException $e)
		{
			ShowError($e->getMessage());
		}
		catch (LoaderException $e)
		{
			ShowError($e->getMessage());
		}

		return $this->arResult;
	}

	public function getChartDataAction()
	{
		$result = [
			'dataProvider' => [],
			'idList' => [],
		];

		try
		{
			if ($this->checkRequiredParams())
			{
				$this->processResultData('graphs');
				if (isset($this->arResult['CHART_DATA']['DATA']))
				{
					$result['dataProvider'] = $this->arResult['CHART_DATA']['DATA'];
				}
				if (isset($this->arResult['CHART_DATA']['ID_LIST']))
				{
					$result['idList'] = $this->arResult['CHART_DATA']['ID_LIST'];
				}
			}
		}
		catch (SystemException $e)
		{
			ShowError($e->getMessage());
		}
		catch (LoaderException $e)
		{
			ShowError($e->getMessage());
		}

		return $result;
	}

	public function configureActions()
	{
		return [
			'getChartData' => [
				'prefilters' => [
					new ActionFilter\Authentication(),
					new ActionFilter\HttpMethod(
						[ActionFilter\HttpMethod::METHOD_POST]
					),
					new ActionFilter\Csrf(),
				],
				'postfilters' => [

				]
			]
		];
	}
}
