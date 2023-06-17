<?php

namespace Bitrix\Socialnetwork\Component\WorkgroupList;

use Bitrix\Main\Search;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\UserCounterTable;
use Bitrix\Socialnetwork\Component\WorkgroupList;
use Bitrix\Socialnetwork\Internals\Counter\CounterFilter;
use Bitrix\Socialnetwork\WorkgroupSiteTable;
use Bitrix\Tasks\Internals\Counter as TasksCounter;

class FilterManager
{
	public $runtimeFieldsManager = null;

	/**
	 * @var array
	 */
	private $fieldsList;

	/**
	 * @var array
	 */
	private $gridFilter;
	private Query $query;
	private int $currentUserId;
	private int $contextUserId;
	private string $mode;
	/**
	 * @var array
	 */
	private array $numericFieldsList;
	/**
	 * @var array
	 */
	private array $integerFieldsList;
	/**
	 * @var array[]
	 */
	private array $stringFieldsList;
	/**
	 * @var array
	 */
	private array $booleanFieldsList;
	/**
	 * @var array
	 */
	private array $dateFieldsList;
	private bool $hasAccessToTasksCounters;

	public function __construct(Query $query, RuntimeFieldsManager $runtimeFieldsManager, array $params = [])
	{
		$this->fieldsList = ($params['fieldsList'] ?? []);
		$this->gridFilter = ($params['gridFilter'] ?? []);
		$this->currentUserId = (int)($params['currentUserId'] ?? 0);
		$this->contextUserId = (int)($params['contextUserId'] ?? 0);
		$this->mode = (string)($params['mode'] ?? '');
		$this->query = $query;
		$this->hasAccessToTasksCounters = (boolean)($params['hasAccessToTasksCounters'] ?? false);
		$this->runtimeFieldsManager = $runtimeFieldsManager;
	}

	public function getFilter(): array
	{
		$filter = [
			'=ACTIVE' => 'Y',
			'=SITE.SITE_ID' => SITE_ID,
		];

		if ($this->query === null)
		{
			return $filter;
		}

		if (
			!empty($this->gridFilter['EXTRANET'])
			&& $this->gridFilter['EXTRANET'] === 'Y'
			&& \Bitrix\Main\Filter\UserDataProvider::getExtranetAvailability()
		)
		{
			$this->query->registerRuntimeField(
				new Reference(
					'SITE_EXTRANET',
					WorkgroupSiteTable::class,
					Join::on('this.ID', 'ref.GROUP_ID')->where('ref.SITE_ID', static::getExtranetSiteId()),
					['join_type' => 'INNER']
				)
			);
		}

		if (
			!empty($this->gridFilter['FAVORITES'])
			&& $this->gridFilter['FAVORITES'] === 'Y'
			&& $this->currentUserId > 0
		)
		{
			$filter['=FAVORITES.USER_ID'] = $this->currentUserId;
		}

		if ($this->runtimeFieldsManager->has('SCRUM'))
		{
			if ($this->mode === WorkgroupList::MODE_TASKS_SCRUM)
			{
				$filter['=PROJECT'] = 'Y';
				$filter['=SCRUM'] = 'Y';
			}
			elseif ($this->mode === WorkgroupList::MODE_TASKS_PROJECT)
			{
				$filter['=SCRUM'] = 'N';
			}
		}

		$this->initFilterFieldsData();

		$filter = $this->processNumericFields($filter);
		$filter = $this->processIntegerFields($filter);
		$filter = $this->processStringFields($filter);
		$filter = $this->processBooleanFields($filter);
		$filter = $this->processDateFields($filter);
		$filter = $this->processFind($filter);

		if ($this->hasAccessToTasksCounters)
		{
			$filter = $this->processTasksCounterFilter($filter);
		}
		else
		{
			$filter = $this->processCommonCounterFilter($filter);
		}

		$filter = $this->processProjectDateFilter($filter);

		return $filter;
	}

	private function initFilterFieldsData(): void
	{
		$this->numericFieldsList = [
			[
				'FILTER_FIELD_NAME' => 'ID',
				'FIELD_NAME' => 'ID',
				'VALUE_FROM' => ($this->gridFilter['ID_from'] ?? false),
				'VALUE_TO' => ($this->gridFilter['ID_to'] ?? false)
			],
		];

		$this->integerFieldsList = [
			[
				'FILTER_FIELD_NAME' => 'OWNER',
				'FIELD_NAME' => 'OWNER_ID',
				'OPERATION' => '=',
				'VALUE' => preg_replace('/^U(\d+)$/', '$1', ($this->gridFilter['OWNER'] ?? '')),
			],
			[
				'FILTER_FIELD_NAME' => 'MEMBER',
				'FIELD_NAME' => 'MEMBER_ID',
				'OPERATION' => '=',
				'VALUE' => preg_replace('/^U(\d+)$/', '$1', ($this->gridFilter['MEMBER'] ?? '')),
			],
		];

		$this->stringFieldsList = [
			[
				'FILTER_FIELD_NAME' => 'NAME',
				'FIELD_NAME' => 'NAME',
				'OPERATION' => '%=',
				'VALUE' => ($this->gridFilter['NAME'] ?? '') . '%',
			],
			[
				'FILTER_FIELD_NAME' => 'TAG',
				'FIELD_NAME' => 'TAG',
				'OPERATION' => '%=',
				'VALUE' => ($this->gridFilter['TAG'] ?? '') . '%',
			],
		];

		$this->booleanFieldsList = [
			[
				'FILTER_FIELD_NAME' => 'CLOSED',
				'FIELD_NAME' => 'CLOSED',
				'OPERATION' => '=',
				'VALUE' => ($this->gridFilter['CLOSED'] ?? ''),
			],
			[
				'FILTER_FIELD_NAME' => 'VISIBLE',
				'FIELD_NAME' => 'VISIBLE',
				'OPERATION' => '=',
				'VALUE' => ($this->gridFilter['VISIBLE'] ?? ''),
			],
			[
				'FILTER_FIELD_NAME' => 'OPENED',
				'FIELD_NAME' => 'OPENED',
				'OPERATION' => '=',
				'VALUE' => ($this->gridFilter['OPENED'] ?? ''),
			],
			[
				'FILTER_FIELD_NAME' => 'PROJECT',
				'FIELD_NAME' => 'PROJECT',
				'OPERATION' => '=',
				'VALUE' => ($this->gridFilter['PROJECT'] ?? ''),
			],
			[
				'FILTER_FIELD_NAME' => 'SCRUM',
				'FIELD_NAME' => 'SCRUM',
				'OPERATION' => '=',
				'VALUE' => ($this->gridFilter['SCRUM'] ?? ''),
			],
			[
				'FILTER_FIELD_NAME' => 'LANDING',
				'FIELD_NAME' => 'LANDING',
				'OPERATION' => '=',
				'VALUE' => ($this->gridFilter['LANDING'] ?? ''),
			],
		];

		$this->dateFieldsList = [
			[
				'FILTER_FIELD_NAME' => 'PROJECT_DATE_START',
				'FIELD_NAME' => 'PROJECT_DATE_START',
				'VALUE_FROM' => ($this->gridFilter['PROJECT_DATE_START_from'] ?? false),
				'VALUE_TO' => ($this->gridFilter['PROJECT_DATE_START_to'] ?? false)
			],
			[
				'FILTER_FIELD_NAME' => 'PROJECT_DATE_FINISH',
				'FIELD_NAME' => 'PROJECT_DATE_FINISH',
				'VALUE_FROM' => ($this->gridFilter['PROJECT_DATE_FINISH_from'] ?? false),
				'VALUE_TO' => ($this->gridFilter['PROJECT_DATE_FINISH_to'] ?? false)
			],
		];

	}

	protected function processIntegerFields($filter): array
	{
		foreach ($this->integerFieldsList as $field)
		{
			$value = false;

			if (
				is_array($field['VALUE'])
				&& !empty($field['VALUE'])
			)
			{
				$value = $field['VALUE'];
			}
			elseif (
				!is_array($field['VALUE'])
				&& (string)$field['VALUE'] !== ''
			)
			{
				$value = (int)$field['VALUE'];
			}

			if ($value !== false)
			{
				$filter = $this->addFilterInteger($filter, [
					'FILTER_FIELD_NAME' => $field['FILTER_FIELD_NAME'],
					'FIELD_NAME' => $field['FIELD_NAME'],
					'OPERATION' => ($field['OPERATION'] ?? '='),
					'VALUE' => $value,
				]);
			}
		}

		return $filter;
	}

	protected function processNumericFields($filter): array
	{
		foreach ($this->numericFieldsList as $field)
		{
			if (
				empty($field['VALUE_FROM'])
				&& empty($field['VALUE_TO'])
			)
			{
				return $filter;
			}

			if (
				!empty($field['VALUE_FROM'])
				&& !empty($field['VALUE_TO'])
				&& $field['VALUE_FROM'] === $field['VALUE_TO'])
			{
				$filter['=' . $field['FIELD_NAME']] = $field['VALUE_FROM'];
			}
			else
			{
				if (!empty($field['VALUE_FROM']))
				{
					$filter['>=' . $field['FIELD_NAME']] = $field['VALUE_FROM'];
				}

				if (!empty($field['VALUE_TO']))
				{
					$filter['<=' . $field['FIELD_NAME']] = $field['VALUE_TO'];
				}
			}
		}

		return $filter;
	}

	protected function processStringFields(array $filter): array
	{
		foreach ($this->stringFieldsList as $field)
		{
			if ($field['VALUE'] !== '')
			{
				$filter = $this->addFilterString($filter, [
					'FILTER_FIELD_NAME' => $field['FILTER_FIELD_NAME'],
					'FIELD_NAME' => $field['FIELD_NAME'],
					'OPERATION' => ($field['OPERATION'] ?? '%='),
					'VALUE' => $field['VALUE'],
				]);
			}
		}

		return $filter;
	}

	protected function processBooleanFields(array $filter): array
	{
		foreach ($this->booleanFieldsList as $field)
		{
			if (in_array($field['VALUE'], ['Y', 'N'], true))
			{
				$filter = $this->addFilterString($filter, [
					'FILTER_FIELD_NAME' => $field['FILTER_FIELD_NAME'],
					'FIELD_NAME' => $field['FIELD_NAME'],
					'OPERATION' => '=',
					'VALUE' => $field['VALUE'],
				]);
			}
		}

		return $filter;
	}

	protected function processDateFields(array $filter): array
	{
		foreach ($this->dateFieldsList as $field)
		{
			if (
				!empty($field['VALUE_FROM'])
				|| !empty($field['VALUE_TO'])
			)
			{
				$filter = $this->addFilterDateTime($filter, [
					'FILTER_FIELD_NAME' => $field['FILTER_FIELD_NAME'],
					'FIELD_NAME' => $field['FIELD_NAME'],
					'VALUE_FROM' => ($field['VALUE_FROM'] ?? $this->gridFilter[$field['FILTER_FIELD_NAME']]),
					'VALUE_TO' => ($field['VALUE_TO'] ?? $this->gridFilter[$field['FILTER_FIELD_NAME']]),
				]);
			}
		}

		return $filter;
	}

	protected function processFind(array $filter): array
	{

		if (
			isset($this->gridFilter['FIND'])
			&& $this->gridFilter['FIND']
		)
		{
			$findFilter = $this->getFindFilter($this->gridFilter['FIND']);
			if (!empty($findFilter))
			{
				$filter = array_merge($filter, $findFilter);
			}
		}

		return $filter;
	}

	protected function processTasksCounterFilter(array $filter, string $gridFilterField = 'COUNTERS'): array
	{
		if (
			!in_array($gridFilterField, [ 'COUNTERS', 'COMMON_COUNTERS' ], true)
			|| empty($this->gridFilter[$gridFilterField])
			|| !Loader::includeModule('tasks')
		)
		{
			return $filter;
		}

		$this->query->setDistinct(true);

		$this->query->registerRuntimeField(
			new Reference(
				'TASKS_COUNTER',
				TasksCounter\CounterTable::class,
				Join::on('this.ID', 'ref.GROUP_ID')->where('ref.USER_ID', $this->contextUserId),
				['join_type' => 'INNER']
			)
		);
		$this->runtimeFieldsManager->add('TASKS_COUNTER');

		if ($gridFilterField === 'COUNTERS')
		{
			$typesMap = [
				'EXPIRED' => [
					'INCLUDE' => TasksCounter\CounterDictionary::MAP_EXPIRED,
					'EXCLUDE' => null,
				],
				'NEW_COMMENTS' => [
					'INCLUDE' => TasksCounter\CounterDictionary::MAP_COMMENTS,
					'EXCLUDE' => null,
				],
				'PROJECT_EXPIRED' => [
					'INCLUDE' => array_merge(
						[ TasksCounter\CounterDictionary::COUNTER_GROUP_EXPIRED ],
						TasksCounter\CounterDictionary::MAP_MUTED_EXPIRED,
					),
					'EXCLUDE' => TasksCounter\CounterDictionary::MAP_EXPIRED,
				],
				'PROJECT_NEW_COMMENTS' => [
					'INCLUDE' => array_merge(
						[ TasksCounter\CounterDictionary::COUNTER_GROUP_COMMENTS ],
						TasksCounter\CounterDictionary::MAP_MUTED_COMMENTS,
					),
					'EXCLUDE' => TasksCounter\CounterDictionary::MAP_COMMENTS,
				],
			];
			$type = $typesMap[$this->gridFilter[$gridFilterField]];
		}
		elseif ($gridFilterField === 'COMMON_COUNTERS')
		{
			$type = [
				'INCLUDE' => array_merge(
					array_values(TasksCounter\CounterDictionary::MAP_EXPIRED),
					array_values(TasksCounter\CounterDictionary::MAP_COMMENTS),
				),
				'EXCLUDE' => null,
			];
		}

		$filter['INCLUDED_COUNTER'] = $type['INCLUDE'];

		if ($type['EXCLUDE'])
		{
			$this->query->registerRuntimeField(
				'EXCLUDED_COUNTER_EXISTS',
				new ExpressionField(
					'EXCLUDED_COUNTER_EXISTS',
					"(
						SELECT 1
						FROM b_tasks_scorer
						WHERE
							GROUP_ID = %s
							AND TASK_ID = %s
							AND USER_ID = " . $this->contextUserId . "
							AND TYPE IN ('" . implode("','", $type['EXCLUDE']) . "')
						LIMIT 1
					)",
					[ 'ID', 'TASKS_COUNTER.TASK_ID' ]
				)
			);
			$this->runtimeFieldsManager->add('EXCLUDED_COUNTER_EXISTS');
		}

		return $filter;
	}

	protected function processCommonCounterFilter(array $filter): array
	{
		if (empty($this->gridFilter['COMMON_COUNTERS']))
		{
			return $filter;
		}

		if ($this->gridFilter['COMMON_COUNTERS'] === CounterFilter::VALUE_LIVEFEED)
		{
			// todo oh
		}
		elseif ($this->gridFilter['COMMON_COUNTERS'] === CounterFilter::VALUE_TASKS)
		{
			$filter = $this->processTasksCounterFilter($filter, 'COMMON_COUNTERS');
		}

		return $filter;
	}

	protected function processProjectDateFilter(array $filter): array
	{
		if (
			empty($this->gridFilter['PROJECT_DATE_from'])
			&& empty($this->gridFilter['PROJECT_DATE_to'])
		)
		{
			return $filter;
		}

		if (!empty($this->gridFilter['PROJECT_DATE_from']))
		{
			$filter['>=PROJECT_DATE_START'] = $this->gridFilter['PROJECT_DATE_from'];
		}

		if (!empty($this->gridFilter['PROJECT_DATE_to']))
		{
			$filter['<=PROJECT_DATE_FINISH'] = $this->gridFilter['PROJECT_DATE_to'];
		}

		return $filter;
	}

	protected function addFilterInteger(array $filter = [], array $params = []): array
	{
		$filterFieldName = ($params['FILTER_FIELD_NAME'] ?? '');
		$value = ($params['VALUE'] ?? '');

		if (
			$filterFieldName === ''
			|| (int)$value <= 0
		)
		{
			return $filter;
		}

		$fieldName = (
			isset($params['FIELD_NAME'])
			&& $params['FIELD_NAME'] !== ''
				? $params['FIELD_NAME']
				: $filterFieldName
		);
		$operation = ($params['OPERATION'] ?? '=');

		if (in_array($fieldName, $this->fieldsList, true))
		{
			$filter[$operation . $fieldName] = $value;
		}

		return $filter;
	}

	protected function addFilterString(array $filter = [], array $params = []): array
	{
		$filterFieldName = ($params['FILTER_FIELD_NAME'] ?? '');
		$value = ($params['VALUE'] ?? '');

		if ($filterFieldName === '')
		{
			return $filter;
		}
		if (
			!is_array($value)
			&& trim($value, '%') === ''
		)
		{
			return $filter;
		}

		if (
			is_array($value)
			&& empty(array_filter($value, static function ($item) {
				return trim($item, '%') !== '';
			}))
		)
		{
			return $filter;
		}

		$fieldName = (
			isset($params['FIELD_NAME'])
			&& $params['FIELD_NAME'] !== ''
				? $params['FIELD_NAME']
				: $filterFieldName
		);
		$operation = ($params['OPERATION'] ?? '%=');

		if (in_array($fieldName, $this->fieldsList, true))
		{
			$filter[$operation . $fieldName] = $value;
		}

		return $filter;
	}

	protected function addFilterDateTime(array $filter = [], array $params = []): array
	{
		$filterFieldName = ($params['FILTER_FIELD_NAME'] ?? '');
		$valueFrom = ($params['VALUE_FROM'] ?? '');
		$valueTo = ($params['VALUE_TO'] ?? '');

		if (
			$filterFieldName === ''
			|| (
				$valueFrom === ''
				&& $valueTo === ''
			)
		)
		{
			return $filter;
		}

		$fieldName = (
			isset($params['FIELD_NAME'])
			&& $params['FIELD_NAME'] !== ''
				? $params['FIELD_NAME']
				: $filterFieldName
		);

		if (in_array($fieldName, $this->fieldsList, true))
		{
			if ($valueFrom !== '')
			{
				$filter['>=' . $fieldName] = $valueFrom;
			}
			if ($valueTo !== '')
			{
				$filter['<=' . $fieldName] = $valueTo;
			}
		}

		return $filter;
	}

	/**
	 * @param string $value
	 * @return array
	 */
	protected function getFindFilter(string $value): array
	{
		$result = [];

		$value = trim($value);

		$value = (
			Search\Content::isIntegerToken($value)
				? Search\Content::prepareIntegerToken($value)
				: Search\Content::prepareStringToken($value)
		);

		if (Search\Content::canUseFulltextSearch($value, Search\Content::TYPE_MIXED))
		{
			$result['*SEARCH_INDEX'] = $value;
		}

		return $result;
	}

	private static function getExtranetSiteId(): string
	{
		static $result = null;

		if ($result === null)
		{
			$result = (
				Loader::includeModule('extranet')
					? \CExtranet::getExtranetSiteId()
					: ''
			);
		}

		return $result;
	}
}
