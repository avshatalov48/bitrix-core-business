<?php

namespace Bitrix\Sender\Posting;

use Bitrix\Main\DB\Result;
use Bitrix\Main\Entity;
use Bitrix\Sender\Connector;
use Bitrix\Sender\Connector\IncrementallyConnector;
use Bitrix\Sender\Entity\Segment;
use Bitrix\Sender\GroupConnectorTable;
use Bitrix\Sender\GroupTable;
use Bitrix\Sender\Integration\Crm\Connectors\QueryCount;
use Bitrix\Sender\Integration\Sender\Connectors\Contact;
use Bitrix\Sender\Internals\Model\GroupCounterTable;
use Bitrix\Sender\Internals\Model\GroupStateTable;
use Bitrix\Sender\Internals\Model\GroupThreadTable;
use Bitrix\Sender\Internals\Model\LetterSegmentTable;
use Bitrix\Sender\Internals\Model\LetterTable;
use Bitrix\Sender\Recipient\Type;
use Bitrix\Sender\Runtime\SegmentDataBuilderJob;
use Bitrix\Sender\SegmentDataTable;
use Bitrix\Sender\Posting\SegmentThreadStrategy\AbstractThreadStrategy;
use Bitrix\Sender\UI\PageNavigation;
use CModule;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Runtime;
Loc::loadMessages(__FILE__);

class SegmentDataBuilder
{
	/**
	 * @var int
	 */
	private $groupId;

	/**
	 * @var string
	 */
	private $filterId;

	private ?int $groupStateId;

	private $endpoint;

	private $dataFilter = [];
	private const PER_PAGE = 100000;
	private const MINIMAL_PER_PAGE = 500;
	private const SEGMENT_TABLE = 'sender_segment_data';
	private const CONNECTOR_ENTITY = [
		'crm_client' => 'CONTACT',
		'crm_lead' => 'LEAD'
	];

	private const SEGMENT_LOCK_KEY = 'segment_lock_';
	private const SEGMENT_DATA_LOCK_KEY = 'segment_data_lock_';

	public const FILTER_COUNTER_TAG = 'senderGroupFilterCounter';
	private static $isSent = [];

	/**
	 * SegmentDataBuilder constructor.
	 *
	 * @param int $groupId
	 * @param string $filterId
	 * @param array $endpoint
	 */
	public function __construct(
		int $groupId,
		string $filterId,
		array $endpoint = [],
		?int $groupStateId = null
	)
	{
		$this->groupId = $groupId;
		$this->filterId = $filterId;
		$this->endpoint = $endpoint;
		$this->groupStateId = $groupStateId;
	}

	private static function checkBlockers()
	{
		$query = "
SELECT b.ID, b.GROUP_ID
FROM b_sender_group_state b
INNER JOIN (
    SELECT GROUP_ID, FILTER_ID
    FROM b_sender_group_state
    GROUP BY GROUP_ID, FILTER_ID
    HAVING COUNT(*) > 1
) d ON b.GROUP_ID = d.GROUP_ID AND b.FILTER_ID = d.FILTER_ID;
";

		$dbResult = \Bitrix\Main\Application::getConnection()->query($query);
		$groups = [];
		while ($row = $dbResult->fetch()) {
			$groupId = $row['GROUP_ID'];
			if (in_array($groupId, $groups))
			{
				continue;
			}
			$id = $row['ID'];
			GroupStateTable::delete($id);
			$groups[] = $groupId;
			Runtime\SegmentDataClearJob::addEventAgent($groupId);
		}
	}

	/**
	 * @return array|bool|false
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getCurrentGroupState()
	{
		$groupState = GroupStateTable::getList(
			[
				'filter' => $this->groupStateId
					? [
						'=ID' => $this->groupStateId
					]
					: [
						'=FILTER_ID' => $this->filterId,
						'=GROUP_ID'  => $this->groupId,
					]
			]
		)->fetch();


		return $groupState ?: $this->createGroupState();
	}
	/**
	 * @return array|bool|false
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getAllStates()
	{
		return GroupStateTable::getList(
			[
				'filter' => [
					'=GROUP_ID'  => $this->groupId,
				]
			]
		)->fetchAll();
	}

	/**
	 * @return bool
	 * @throws \Exception
	 */
	public function isBuildingCompleted(): bool
	{
		$groupState = $this->getCurrentGroupState();

		return !$groupState || (int)$groupState['STATE'] === (int)GroupStateTable::STATES['COMPLETED']
			;
	}

	/**
	 * @return ?array
	 * @throws \Exception
	 */
	public function createGroupState(): ?array
	{
		if (!static::checkEndpoint($this->endpoint))
		{
			return null;
		}

		$dataToSet = [
			'FILTER_ID' => $this->filterId,
			'GROUP_ID' => $this->groupId,
			'ENDPOINT' => json_encode($this->endpoint),
			'OFFSET' => 0,
			'STATE' => GroupStateTable::STATES['CREATED'],
			'NEW_CREATED' => true,
		];

		$dataToSet['ID'] = GroupStateTable::add($dataToSet)->getId();

		return $dataToSet;
	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	public function resetGroupState(int $id)
	{
		$dataToSet = [
			'FILTER_ID' => $this->filterId,
			'GROUP_ID' => $this->groupId,
			'ENDPOINT' => json_encode($this->endpoint),
			'OFFSET' => 0,
			'STATE' => GroupStateTable::STATES['CREATED'],
		];

		$dataToSet['ID'] = GroupStateTable::update($id, $dataToSet)->getId();

		return $dataToSet;
	}

	/**
	 * @param int $offset
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function updateGroupStateOffset(int $offset)
	{
		$groupState = $this->getCurrentGroupState();
		if ($groupState)
		{
			GroupStateTable::update(
				$groupState['ID'],
				[
					'FILTER_ID' => $this->filterId,
					'GROUP_ID' => $this->groupId,
					'OFFSET' => $offset,
					'STATE' => GroupStateTable::STATES['IN_PROGRESS'],
				]
			);
		}
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function completeBuilding()
	{
		$groupState = $this->getCurrentGroupState();
		if ($groupState)
		{
			GroupStateTable::update(
				$groupState['ID'],
				[
					'STATE' => GroupStateTable::STATES['COMPLETED'],
				]
			);

			if (self::checkIsSegmentPrepared($this->groupId))
			{
				$this->calculateFilterCounts();
			}
		}
	}

	/**
	 * @param int $groupId
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\DB\SqlQueryException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function checkIsSegmentPrepared(int $groupId)
	{
		if (!Locker::lock(self::SEGMENT_LOCK_KEY, $groupId))
		{
			return false;
		}

		$states = GroupStateTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId
			],
		])->fetchAll();

		$currentState = GroupTable::STATUS_READY_TO_USE;
		foreach ($states as $state)
		{
			if ((int)$state['STATE'] !== GroupStateTable::STATES['COMPLETED'])
			{
				if (!SegmentDataBuilderJob::existsInDB($state['ID']))
				{
					SegmentDataBuilderJob::addEventAgent($state['ID']);
				}

				$currentState = GroupTable::STATUS_IN_PROGRESS;
				break;
			}
		}

		GroupTable::update($groupId, [
			'fields' => ['STATUS' => $currentState]
		]);

		$prepared = $currentState === GroupTable::STATUS_READY_TO_USE;
		if (CModule::IncludeModule('im') && $prepared)
		{
			$mailings = LetterSegmentTable::getList([
				'select' => [
					'ID' => 'LETTER.ID',
					'USER_ID' => 'LETTER.CREATED_BY',
				],
				'filter' => [
					'=SEGMENT_ID' => $groupId,
					'!=LETTER.STATUS' => LetterTable::STATUS_END
				],
			]);
			$group = GroupTable::getById($groupId)->fetchRaw();

			foreach ($mailings as $mailing)
			{
				if (!$mailing['ID'])
				{
					continue;
				}

				if (static::$isSent[$groupId][$mailing['USER_ID']])
				{
					continue;
				}

				LetterTable::update($mailing['ID'], [
					'WAITING_RECIPIENT' => 'N'
				]);

				$messageFields = [
					"NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
					"NOTIFY_MODULE" => "sender",
					"NOTIFY_EVENT" => "group_prepared",
					"TO_USER_ID" => $mailing['USER_ID'],
					"NOTIFY_TAG" => "SENDER|GROUP_PREPARED|" . $groupId . "|" . $mailing['USER_ID'],
					"NOTIFY_MESSAGE" => Loc::getMessage(
						"SENDER_SEGMENT_BUILDER_GROUP_PREPARED",
						[
							"#SEGMENT_ID#" => $groupId,
							"#SEGMENT_NAME#" => htmlspecialcharsbx($group['NAME'])
						]
					)
				];

				\CIMNotify::Add($messageFields);
				static::$isSent[$groupId][$mailing['USER_ID']] = $mailing['USER_ID'];
			}
		}

		Locker::unlock(self::SEGMENT_LOCK_KEY, $groupId);

		return $prepared;
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function haltBuilding()
	{
		$groupState = $this->getCurrentGroupState();
		if ($groupState)
		{
			GroupStateTable::update(
				$groupState['ID'],
				[
					'STATE' => GroupStateTable::STATES['HALTED'],
				]
			);
		}
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function clearBuilding(int $groupStateId)
	{
		if ($groupStateId)
		{
			GroupStateTable::delete($groupStateId);
		}

		SegmentDataTable::deleteList([
			'=GROUP_ID' => $this->groupId,
			'=FILTER_ID' => $this->filterId,
		]);
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function clearGroupBuilding(int $groupId)
	{
		if ($groupId)
		{
			$filter = [
				'=GROUP_ID' => $groupId,
			];

			GroupStateTable::deleteList($filter);
			Runtime\SegmentDataClearJob::addEventAgent($groupId);
		}
	}

	/**
	 * @param Result $data
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function addToDB(?Result $data)
	{
		if ($data)
		{
			$rows = [];
			$rowsDataCounter = [
				$this->groupId => [

				],
			];
			$counter = 0;
			while ($row = $data->fetch())
			{
				$rows[] = [
					'GROUP_ID' => $this->groupId,
					'FILTER_ID' => $this->filterId,
					'CRM_ENTITY_ID'  => $row['CRM_ENTITY_ID'],
					'NAME' => $row['NAME'],
					'CRM_ENTITY_TYPE_ID' => $row['CRM_ENTITY_TYPE_ID'],
					'CRM_ENTITY_TYPE' => $row['CRM_ENTITY_TYPE'],
					'CONTACT_ID' => $row['CRM_CONTACT_ID'],
					'COMPANY_ID' => $row['CRM_COMPANY_ID'],
					'EMAIL' => $row['EMAIL'] ?? null,
					'IM' => $row['IM'] ?? null,
					'PHONE' => $row['PHONE'] ?? null,
					'HAS_EMAIL' => $row['EMAIL'] ? 'Y' : 'N',
					'HAS_IMOL' => $row['IM'] ? 'Y' : 'N',
					'HAS_PHONE' => $row['PHONE'] ? 'Y' : 'N',
					'SENDER_TYPE_ID' => $this->detectSenderType($row),
				];
				$detectedTypes = $this->detectSenderTypes($row);

				foreach ($detectedTypes as $type)
				{
					if (!isset($rowsDataCounter[$this->groupId][$type]))
					{
						$rowsDataCounter[$this->groupId][$type] = 0;
					}
					$rowsDataCounter[$this->groupId][$type]++;
				}
				$counter++;

				if ($counter === self::MINIMAL_PER_PAGE)
				{
					SegmentDataTable::addMulti($rows, true);
					$rows = [];
					$counter = 0;
				}
			}

			if ($rows)
			{
				$this->updateCounters($rowsDataCounter);
				SegmentDataTable::addMulti($rows, true);
			}
		}
	}

	/**
	 * @param array $endpoint
	 *
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function buildData($perPage = null): bool
	{
		if (!$this->connectorIterable())
		{
			return true;
		}

		$groupState = $this->getCurrentGroupState();

		if (!$groupState)
		{
			return true;
		}

		if ($this->isBuildingCompleted())
		{
			return true;
		}

		$connector = Connector\Manager::getConnector($this->endpoint);
		$connector->setDataTypeId(null);
		$connector->setCheckAccessRights(false);
		$connector->setFieldValues($this->endpoint['FIELDS']);

		$lastId = $connector->getEntityLimitInfo()['lastId'];

		/** @var AbstractThreadStrategy $threadStrategy */
		$threadStrategy = Runtime\Env::getGroupThreadContext();

		$threadStrategy->setGroupStateId($groupState['ID']);

		$threadState = $threadStrategy->checkThreads();
		if ($threadState === AbstractThreadStrategy::THREAD_LOCKED)
		{
			return false;
		}

		if ($threadState === AbstractThreadStrategy::THREAD_NEEDED)
		{
			$threadStrategy->fillThreads();
		}

		$threadStrategy->setPerPage(self::PER_PAGE);

		if (
			$threadStrategy->lockThread() === AbstractThreadStrategy::THREAD_UNAVAILABLE
			|| $threadStrategy->isProcessLimited()
		)
		{
			return false;
		}

		$offset = $threadStrategy->getOffset();
		if (!Locker::lock(self::SEGMENT_DATA_LOCK_KEY, $this->groupId))
		{
			return false;
		}

		if ($offset < $lastId)
		{
			$limit = $offset + self::PER_PAGE;

			$this->addToDB(
				$connector->getLimitedData($offset, $limit)
			);

			Locker::unlock(self::SEGMENT_DATA_LOCK_KEY, $this->groupId);
			$threadStrategy->updateStatus(GroupThreadTable::STATUS_NEW);
			return false;
		}
		Locker::unlock(self::SEGMENT_DATA_LOCK_KEY, $this->groupId);

		if ($threadStrategy->getThreadId() < $threadStrategy->lastThreadId())
		{
			$threadStrategy->updateStatus(GroupThreadTable::STATUS_DONE);
			return false;
		}

		$threadStrategy->updateStatus(GroupThreadTable::STATUS_NEW);

		if (!$threadStrategy->finalize())
		{
			return false;
		}

		$this->completeBuilding();

		return true;
	}
	
	/**
	 * @return bool
	 * @throws \Exception
	 */
	public function prepareForAgent($rebuild = false)
	{
		if (!$this->connectorIterable())
		{
			return false;
		}

		$groupState = $this->getCurrentGroupState();
		$result = '';

		if (isset($groupState['NEW_CREATED'])
			|| $groupState
			&& ($groupState['ENDPOINT'] !== json_encode($this->endpoint) || $rebuild))
		{
			SegmentDataBuilderJob::removeAgentFromDB($groupState['ID']);
			$this->clearBuilding($groupState['ID']);
			GroupCounterTable::deleteByGroupId($this->groupId);

			$groupState = $this->getCurrentGroupState();

			if ($groupState)
			{
				GroupTable::update($this->groupId, [
					'fields' => ['STATUS' => GroupTable::STATUS_IN_PROGRESS]
				]);

				$result = self::run($groupState['ID'], self::MINIMAL_PER_PAGE);
				SegmentDataBuilderJob::addEventAgent($groupState['ID']);
			}
		}

		return $result !== '';
	}

	/**
	 * @return \Bitrix\Main\ORM\Query\Query
	 * @throws \Exception
	 */
	public function getQuery(): \Bitrix\Main\ORM\Query\Query
	{

		$query = SegmentDataTable::query();
		$query->setFilter(
			[
				'=GROUP_ID' => $this->groupId,
				'=FILTER_ID' => $this->filterId,
			]
		);

		$query->registerRuntimeField(new Entity\ExpressionField('CRM_COMPANY_ID' , '%s', ['COMPANY_ID']));
		$query->registerRuntimeField(new Entity\ExpressionField('CRM_CONTACT_ID' , '%s', ['CONTACT_ID']));

		$query->setSelect(
			[
				'CRM_ENTITY_ID',
				'NAME',
				'CRM_ENTITY_TYPE_ID',
				'CRM_ENTITY_TYPE',
				'CRM_CONTACT_ID',
				'CRM_COMPANY_ID',
			]
		);

		return $query;
	}

	private function prepareEntityTypeFilter($type)
	{
		switch ($type) {
			case Type::EMAIL:
				return ['=HAS_EMAIL' => 'Y'];
			case Type::IM:
				return ['=HAS_IMOL' => 'Y'];
			case Type::PHONE:
				return ['=HAS_PHONE' => 'Y'];
			case Type::CRM_COMPANY_ID:
				return  ['!=COMPANY_ID' => null];
			case Type::CRM_CONTACT_ID:
				return ['!=CONTACT_ID' => null];
			case Type::CRM_DEAL_PRODUCT_CONTACT_ID:
			case Type::CRM_ORDER_PRODUCT_CONTACT_ID:
			case Type::CRM_DEAL_PRODUCT_COMPANY_ID:
			case Type::CRM_ORDER_PRODUCT_COMPANY_ID:
			default:
				return null;
		}
	}

	public function setDataFilter(array $filter = []): SegmentDataBuilder
	{
		$this->dataFilter = [];

		$whiteList = [
			'EMAIL' => '=EMAIL',
			'PHONE' => '=PHONE',
			'IM' => '=IM',
			'NAME' => 'NAME',
			'SENDER_RECIPIENT_TYPE_ID' => "",
		];

		foreach ($filter as $key => $filterValue)
		{
			if (!isset($whiteList[$key]) || $filterValue == "undefined")
			{
				continue;
			}

			if ($key === 'SENDER_RECIPIENT_TYPE_ID')
			{
				$type =  $this->prepareEntityTypeFilter($filterValue);
				if ($type)
				{
					$this->dataFilter[] =  $this->prepareEntityTypeFilter($filterValue);
				}

				continue;
			}

			$this->dataFilter[$whiteList[$key]] = $filterValue;
		}

		return $this;
	}
	/**
	 * @return Result
	 * @throws \Exception
	 */
	public function getData(PageNavigation $nav = null, bool $useFilterId = true): Result
	{
		$params = [
			'select' => [
				'*',
				'CRM_COMPANY_ID' => 'COMPANY_ID',
				'CRM_CONTACT_ID' => 'CONTACT_ID',
			],
			'filter' => $this->prepareFilter($useFilterId),
		];

		if ($nav)
		{
			$params['limit'] = $nav->getLimit();
			$params['offset'] = $nav->getOffset();
		}

		return SegmentDataTable::getList($params);
	}

	private function prepareFilter(bool $useFilterId = true)
	{
		$filter = [];
		$filter['=GROUP_ID'] = $this->groupId;

		if ($useFilterId)
		{
			$filter['=FILTER_ID'] = $this->filterId;
		}

		if ($this->dataFilter)
		{
			$filter = array_merge($this->dataFilter, $filter);
		}

		return $filter;
	}

	/**
	 * @return Connector\Result
	 * @throws \Exception
	 */
	public function getPreparedData(): Connector\Result
	{
		$connector = Connector\Manager::getConnector($this->endpoint);

		$personalizeList = array();
		$personalizeListTmp = $connector->getPersonalizeList();
		foreach($personalizeListTmp as $tag)
		{
			if(!empty($tag['ITEMS']))
			{
				foreach ($tag['ITEMS'] as $item)
				{
					$personalizeList[$item['CODE']] = $item['CODE'];
				}
				continue;
			}
			if(strlen($tag['CODE']) > 0)
			{
				$personalizeList[] = $tag['CODE'];
			}
		}

		$result = new Connector\Result($this->getData());
		$result->setFilterFields($personalizeList);
		$result->setDataTypeId($connector->getDataTypeId());

		return $result;
	}

	/**
	 * @return int
	 * @throws \Exception
	 */
	public function getDataCount(bool $useFilterId = true): int
	{
		return SegmentDataTable::getCount($this->prepareFilter($useFilterId));
	}

	private function connectorIterable()
	{
		$connector = Connector\Manager::getConnector($this->endpoint);

		return $connector instanceof Connector\IncrementallyConnector;
	}

	/**
	 * @param $groupStateId
	 * @param null $perPage
	 *
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function run($groupStateId, $perPage = null)
	{
		$groupState = GroupStateTable::getById($groupStateId)->fetch();

		if (!$groupState['FILTER_ID'])
		{
			GroupStateTable::update(
				$groupStateId,
				[
					'STATE' => GroupStateTable::STATES['COMPLETED'],
				]
			);

			if ($groupState['GROUP_ID'])
			{
				self::checkIsSegmentPrepared($groupState['GROUP_ID']);
			}
			
			return '';
		}
		$segmentBuilder = new SegmentDataBuilder(
			(int)$groupState['GROUP_ID'],
			$groupState['FILTER_ID'],
			json_decode($groupState['ENDPOINT'], true),
			$groupState['ID']
		);

		if (!$segmentBuilder->buildData($perPage))
		{
			return SegmentDataBuilderJob::getAgentName($groupStateId);
		}

		return '';
	}

	/**
	 * @return Connector\DataCounter
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function calculateCurrentFilterCount()
	{
		$groupState = $this->getCurrentGroupState();

		if (!$groupState)
		{
			return new Connector\DataCounter([]);
		}

		$connector = Connector\Manager::getConnector(
			json_decode($groupState['ENDPOINT'], true)
		);

		if (!$connector)
		{
			$this->clearBuilding($groupState['ID']);
			return new Connector\DataCounter([]);
		}

		$counter = new Connector\DataCounter(QueryCount::getPreparedCount(
			$this->getQuery(),
			self::SEGMENT_TABLE,
			self::CONNECTOR_ENTITY[$connector->getCode()]
		));

		Segment::updateAddressCounters($this->groupId, [$counter]);
		if (CModule::IncludeModule('pull'))
		{
			\CPullWatch::AddToStack(
				self::FILTER_COUNTER_TAG,
				[
					'module_id' => 'sender',
					'command' => 'updateFilterCounter',
					'params' => [
						'groupId' => $this->groupId,
						'filterId' => $this->filterId,
						'count' => $counter->getArray(),
						'state' => $groupState['STATE'],
						'completed' => (int)$groupState['STATE'] === GroupStateTable::STATES['COMPLETED']
					],
				]
			);
		}

		return $counter;
	}

	/**
	 * Calculate all current counters
	 * @return Connector\DataCounter[]
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function calculateFilterCounts(): array
	{
		$connectors = GroupConnectorTable::getList(
			[
				'filter' => ['=GROUP_ID' => $this->groupId]
			])->fetchAll();

		$counters = [];
		foreach ($connectors as $dbConnector)
		{
			$endpoint = $dbConnector['ENDPOINT'];
			$connector = Connector\Manager::getConnector(
				$endpoint
			);

			$this->filterId = $endpoint['FILTER_ID'] ?? 'sender_crm_client_--filter--crmclient--';
			if ($connector instanceof Contact)
			{
				$connector->setCheckAccessRights(false);
				$connector->setFieldValues($endpoint['FIELDS']);
			}
			$counters[] = self::CONNECTOR_ENTITY[$connector->getCode()] ?
						new Connector\DataCounter(QueryCount::getPreparedCount(
						$this->getQuery(),
						self::SEGMENT_TABLE,
						self::CONNECTOR_ENTITY[$connector->getCode()]
					)) : $connector->getDataCounter()
			;

		}

		Segment::updateAddressCounters($this->groupId, $counters);

		return $counters;
	}

	/**
	 * @param array $endpoint
	 *
	 * @return SegmentDataBuilder
	 */
	public function setEndpoint(array $endpoint): SegmentDataBuilder
	{
		$this->endpoint = $endpoint;

		return $this;
	}

	/**
	 * @param int $groupId
	 * @param bool $rebuild
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function actualize(int $groupId, bool $rebuild = false)
	{
		$states = GroupStateTable::getList([
			'filter' => [
				'=GROUP_ID' => $groupId
			],
		])->fetchAll();

		$connectors = GroupConnectorTable::getList([
			'select' => [
				'FILTER_ID',
				'ENDPOINT'
			],
			'filter' => [
				'=GROUP_ID' => $groupId
			],
		])->fetchAll();

		$usedFilters = [];
		$endpoints = [];
		foreach ($connectors as $connector)
		{
			if (!static::checkEndpoint($connector['ENDPOINT']))
			{
				continue;
			}

			$entityConnector = \Bitrix\Sender\Connector\Manager::getConnector($connector['ENDPOINT']);

			if (!$connector['FILTER_ID'] && $entityConnector instanceof Connector\BaseFilter)
			{
				$connector['FILTER_ID'] = $entityConnector->getUiFilterId();
			}

			if (
				!$entityConnector instanceof IncrementallyConnector
				|| !isset($connector['FILTER_ID'])
			)
			{
				continue;
			}

			$usedFilters[] = $connector['FILTER_ID'];
			$endpoints[$connector['FILTER_ID']] = $connector['ENDPOINT'];

			$isUsed = false;

			foreach ($states as $state)
			{
				if ($state['FILTER_ID'] === $connector['FILTER_ID'])
				{
					$isUsed = true;
					break;
				}
			}

			if (!$isUsed)
			{
				$dataBuilder = new SegmentDataBuilder($groupId, $connector['FILTER_ID'], $connector['ENDPOINT']);
				$dataBuilder->prepareForAgent(true);
				$dataBuilder = null;
			}
		}

		foreach ($states as $state)
		{
			$endpoint = json_decode($state['ENDPOINT'], true);
			$dataBuilder = new SegmentDataBuilder($groupId, $state['FILTER_ID'], $endpoint);

			if (!static::checkEndpoint($endpoint))
			{
				$dataBuilder->clearBuilding($state['ID']);
				continue;
			}

			if (!in_array($state['FILTER_ID'], $usedFilters))
			{
				$dataBuilder->clearBuilding($state['ID']);
			}

			if ($endpoints[$state['FILTER_ID']] && $endpoints[$state['FILTER_ID']] !== $endpoint)
			{
				$dataBuilder->setEndpoint($endpoints[$state['FILTER_ID']]);
				$dataBuilder->prepareForAgent();
			}

			if ($rebuild)
			{
				$dataBuilder->prepareForAgent(true);
			}

			$dataBuilder = null;
		}

		self::checkIsSegmentPrepared($groupId);
	}

	private static function checkEndpoint(?array $endpoint): bool
	{
		return $endpoint && isset($endpoint['FIELDS']) && !empty($endpoint['FIELDS']);
	}

	public static function checkBuild(): void
	{
		$groupStateList = GroupStateTable::getList([
			'select' => [
				'GROUP_ID',
				'FILTER_ID',
				'ENDPOINT',
			],
			'filter' => [
				'!@STATE' => [
						GroupStateTable::STATES['COMPLETED'],
						GroupStateTable::STATES['HALTED'],
					]
			],
		]);

		while ($groupState = $groupStateList->fetch())
		{
			$segmentBuilder = new SegmentDataBuilder(
				(int)$groupState['GROUP_ID'],
				$groupState['FILTER_ID'],
				json_decode($groupState['ENDPOINT'], true)
			);

			$segmentBuilder->buildData();
			self::checkIsSegmentPrepared((int)$groupState['GROUP_ID']);
		}
	}

	public static function checkNotCompleted(): string
	{
		self::checkBlockers();
		$groupStateList = GroupStateTable::getList([
			'select' => [
				'GROUP_ID',
				'FILTER_ID',
				'ENDPOINT',
			],
			'filter' => [
				'!@STATE' => [
						GroupStateTable::STATES['COMPLETED'],
						GroupStateTable::STATES['HALTED'],
					]
			],
		]);

		while ($groupState = $groupStateList->fetch())
		{
			self::checkIsSegmentPrepared((int)$groupState['GROUP_ID']);
		}

		$groupList = GroupTable::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'=STATUS' => GroupTable::STATUS_IN_PROGRESS
			]
		]);

		while ($group = $groupList->fetch())
		{
			self::checkIsSegmentPrepared((int)$group['ID']);
		}

		return '\\Bitrix\Sender\\Posting\\SegmentDataBuilder::checkNotCompleted();';
	}

	private function detectSenderType(array $row)
	{
		if (isset($row['PROD_CRM_ORDER_ID']) && $row['PROD_CRM_ORDER_ID']
			&& isset($row['CRM_ENTITY_TYPE_ID']) && $row['CRM_ENTITY_TYPE_ID'] == 5)
			return Type::CRM_ORDER_PRODUCT_CONTACT_ID;
		if (isset($row['CRM_ENTITY_TYPE_ID']) && $row['CRM_ENTITY_TYPE_ID'] == 5)
			return Type::CRM_CONTACT_ID;
		if (isset($row['SGT_DEAL_ID']) && isset($row['CRM_ENTITY_TYPE_ID']) && $row['CRM_ENTITY_TYPE_ID'] == 5)
			return Type::CRM_DEAL_PRODUCT_CONTACT_ID;

		if (isset($row['PROD_CRM_ORDER_ID']) && isset($row['CRM_ENTITY_TYPE_ID']) && $row['CRM_ENTITY_TYPE_ID'] == 4)
			return Type::CRM_ORDER_PRODUCT_COMPANY_ID;
		if (isset($row['CRM_ENTITY_TYPE_ID']) && $row['CRM_ENTITY_TYPE_ID'] == 4)
			return Type::CRM_COMPANY_ID;
		if (isset($row['SGT_DEAL_ID']) && isset($row['CRM_ENTITY_TYPE_ID']) && $row['CRM_ENTITY_TYPE_ID'] == 4)
			return Type::CRM_DEAL_PRODUCT_COMPANY_ID;

		if (isset($row['IM']))
			return Type::IM;
		if (isset($row['EMAIL']))
			return Type::EMAIL;
		if (isset($row['PHONE']))
			return Type::PHONE;

		return Type::EMAIL;
	}

	private function detectSenderTypes(array $row)
	{
		$types = [];
		if (isset($row['PROD_CRM_ORDER_ID']) && isset($row['CRM_ENTITY_TYPE_ID']) && $row['CRM_ENTITY_TYPE_ID'] == 5)
		{
			$types[] = Type::CRM_ORDER_PRODUCT_CONTACT_ID;
		}
		if (isset($row['CRM_ENTITY_TYPE_ID']) && $row['CRM_ENTITY_TYPE_ID'] == 5)
		{
			$types[] = Type::CRM_CONTACT_ID;
		}
		if (isset($row['SGT_DEAL_ID']) && isset($row['CRM_ENTITY_TYPE_ID']) && $row['CRM_ENTITY_TYPE_ID'] == 5)
		{
			$types[] = Type::CRM_DEAL_PRODUCT_CONTACT_ID;
		}

		if (isset($row['PROD_CRM_ORDER_ID']) && isset($row['CRM_ENTITY_TYPE_ID']) && $row['CRM_ENTITY_TYPE_ID'] == 4)
		{
			$types[] = Type::CRM_ORDER_PRODUCT_COMPANY_ID;
		}
		if (isset($row['CRM_ENTITY_TYPE_ID']) &&$row['CRM_ENTITY_TYPE_ID'] == 4)
		{
			$types[] = Type::CRM_COMPANY_ID;
		}
		if (isset($row['SGT_DEAL_ID']) && isset($row['CRM_ENTITY_TYPE_ID']) &&$row['CRM_ENTITY_TYPE_ID'] == 4)
		{
			$types[] = Type::CRM_DEAL_PRODUCT_COMPANY_ID;
		}

		if ($row['CRM_ENTITY_TYPE_ID'] === Type::CRM_LEAD_ID)
		{
			$types[] = Type::CRM_LEAD_ID;
		}

		if (isset($row['IM']))
		{
			$types[] = Type::IM;
		}
		if (isset($row['EMAIL']))
		{
			$types[] = Type::EMAIL;
		}
		if (isset($row['PHONE']))
		{
			$types[] = Type::PHONE;
		}

		return $types;
	}

	private function updateCounters(array $rowsDataCounter)
	{
		if (!Locker::lock(self::SEGMENT_LOCK_KEY, $this->groupId))
		{
			return;
		}

		$counter = GroupCounterTable::getList([
			'select' => [
				'GROUP_ID', 'TYPE_ID', 'CNT'
			],
			'filter' => [
				'=GROUP_ID' => $this->groupId
			],
		]);

		while ($item = $counter->fetch())
		{
			if (!isset($rowsDataCounter[$item['GROUP_ID']][$item['TYPE_ID']]))
			{
				$rowsDataCounter[$item['GROUP_ID']][$item['TYPE_ID']] = $item['CNT'];
			}

			$rowsDataCounter[$item['GROUP_ID']][$item['TYPE_ID']] += $item['CNT'];
		}

		GroupCounterTable::deleteByGroupId($this->groupId);
		foreach ($rowsDataCounter as $groupId => $dataCounter)
		{
			foreach ($dataCounter as $typeId => $count)
			{
				GroupCounterTable::add(array(
					'GROUP_ID' => $groupId,
					'TYPE_ID' => $typeId,
					'CNT' => $count,
				));
			}
		}
		Locker::unlock(self::SEGMENT_LOCK_KEY, $this->groupId);

		$groupState = $this->getCurrentGroupState();
		if (!$groupState)
		{
			return;
		}

		$counter = new Connector\DataCounter($rowsDataCounter);

		if (CModule::IncludeModule('pull'))
		{
			\CPullWatch::AddToStack(
				self::FILTER_COUNTER_TAG,
				[
					'module_id' => 'sender',
					'command' => 'updateFilterCounter',
					'params' => [
						'groupId' => $this->groupId,
						'filterId' => $this->filterId,
						'count' => $counter->getArray(),
						'state' => $groupState['STATE'],
						'completed' => (int)$groupState['STATE'] === GroupStateTable::STATES['COMPLETED']
					],
				]
			);
		}
	}
}
