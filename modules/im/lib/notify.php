<?php
namespace Bitrix\Im;

use Bitrix\Im\V2\Message\CounterService;
use Bitrix\Im\V2\Message\ReadService;
use Bitrix\Main\Type\DateTime;

class Notify
{
	public const
		EVENT_DEFAULT = 'default',
		EVENT_SYSTEM = 'system',
		EVENT_GROUP = 'group',
		EVENT_PRIVATE = 'private',
		EVENT_PRIVATE_SYSTEM = 'private_system'
	;

	private const CONFIRM_TYPE = 1;
	private const SIMPLE_TYPE = 3;
	private const ALL_TYPES = 4;

	private $convertText;
	private $pageLimit;
	private $lastType;
	private $lastId;
	private $chatId;
	private $users = [];
	private $firstPage;
	private $searchText;
	private $searchType;
	private $searchDate;
	private $totalCount;

	public function __construct($options = [])
	{
		$this->convertText = $options['CONVERT_TEXT'] ?? null;
		$this->searchText = $options['SEARCH_TEXT'] ?? null;
		$this->searchType = $options['SEARCH_TYPE'] ?? null;
		$this->searchDate = $options['SEARCH_DATE'] ?? null;
		$this->pageLimit = $options['LIMIT'] ?? null;
		$this->lastType = $options['LAST_TYPE'] ?? null;
		$this->lastId = $options['LAST_ID'] ?? null;
		$this->firstPage = !$this->lastId && !$this->lastType;

		$chatData = $this->getChatData();
		if ($chatData !== null)
		{
			$this->chatId = (int)$chatData['CHAT_ID'];
			$this->totalCount = (int)$chatData['IM_MODEL_RELATION_CHAT_MESSAGE_COUNT'];
		}
	}

	private function getChatData(): ?array
	{
		$userId = \Bitrix\Im\Common::getUserId();
		if (!$userId)
		{
			return null;
		}

		$chatData = \Bitrix\Im\Model\RelationTable::getList([
			'select' => ['CHAT_ID', 'CHAT.MESSAGE_COUNT'],
			'filter' => [
				'=USER_ID' => $userId,
				'=MESSAGE_TYPE' => 'S'
			]
		])->fetch();
		if (!$chatData)
		{
			return null;
		}

		return $chatData;
	}

	public static function getRealCounter($chatId): int
	{
		return self::getCounters($chatId, true)[$chatId];
	}

	public static function getRealCounters($chatId)
	{
		return self::getCounters($chatId, true);
	}

	public static function getCounter($chatId): int
	{
		return self::getCounters($chatId)[$chatId];
	}

	public static function getCounters($chatId, $isReal = false)
	{
		$result = Array();
		$chatList = Array();
		if (is_array($chatId))
		{
			foreach($chatId as $id)
			{
				$id = intval($id);
				if ($id)
				{
					$result[$id] = 0;
					$chatList[$id] = $id;
				}
			}
			$chatList = array_values($chatList);
			$isMulti = count($chatList) > 1;
		}
		else
		{
			$id = intval($chatId);
			if ($id)
			{
				$result[$id] = 0;
				$chatList[] = $id;
			}
			$isMulti = false;
		}

		if (!$chatList)
		{
			return false;
		}

		/*if ($isReal)
		{
			$query = "
				SELECT CHAT_ID, COUNT(1) COUNTER
				FROM b_im_message
				WHERE CHAT_ID ".($isMulti? ' IN ('.implode(',', $chatList).')': ' = '.$chatList[0])."
					  AND NOTIFY_READ = 'N'
				GROUP BY CHAT_ID
			";
		}
		else
		{
			$query = "
				SELECT CHAT_ID, COUNTER
				FROM b_im_relation
				WHERE CHAT_ID ".($isMulti? ' IN ('.implode(',', $chatList).')': ' = '.$chatList[0])."
			";
		}*/

		/*$orm = \Bitrix\Main\Application::getInstance()->getConnection()->query($query);
		while($row = $orm->fetch())
		{
			$result[$row['CHAT_ID']] = (int)$row['COUNTER'];
		}*/

		if ($isMulti)
		{
			$result = (new CounterService(Common::getUserId()))->getForNotifyChats($chatList);
		}
		else
		{
			$counter = (new CounterService(Common::getUserId()))->getByChat($chatList[0]);
			$result[$chatList[0]] = $counter;
		}

		return $result;
	}

	public function get()
	{
		if (!$this->chatId || !$this->totalCount)
		{
			return [
				'notifications' => [],
				'users' => [],
			];
		}
		// fetching confirm notifications
		$confirmCollection = $this->fetchConfirms();

		// fetching simple notifications
		$offset = count($confirmCollection);
		$simpleCollection = $this->fetchSimple($offset);
		$notifications = array_merge($confirmCollection, $simpleCollection);

		/*$unreadCount = \Bitrix\Im\Model\MessageTable::getList(
			[
				'select' => ['CNT'],
				'filter' => [
					'=CHAT_ID' => $this->chatId,
					'=NOTIFY_READ' => 'N'
				],
				'runtime' => [
					new \Bitrix\Main\ORM\Fields\ExpressionField('CNT', 'COUNT(*)')
				]
			]
		)->fetch();*/

		$unreadCount = (new CounterService(\Bitrix\Im\Common::getUserId()))->getByChat($this->chatId);

		$result = [
			'TOTAL_COUNT' => $this->totalCount,
			'TOTAL_UNREAD_COUNT' => (int)$unreadCount,
			'CHAT_ID' => $this->chatId,
			'NOTIFICATIONS' => $notifications,
			'USERS' => $this->users,
		];

		foreach ($result['NOTIFICATIONS'] as $key => $value)
		{
			if ($value['DATE'] instanceof DateTime)
			{
				$result['NOTIFICATIONS'][$key]['DATE'] = date('c', $value['DATE']->getTimestamp());
			}

			$result['NOTIFICATIONS'][$key] = array_change_key_case($result['NOTIFICATIONS'][$key], CASE_LOWER);
		}
		$result['NOTIFICATIONS'] = array_values($result['NOTIFICATIONS']);
		$result['USERS'] = array_values($result['USERS']);
		$result = array_change_key_case($result, CASE_LOWER);

		return $result;
	}

	private function requestData(int $requestType, int $limit): array
	{
		$collection = [];
		$ormParams = $this->prepareGettingIdParams($requestType, $limit);
		$ids = \Bitrix\Im\Model\MessageTable::getList($ormParams)->fetchAll();
		if (count($ids) === 0)
		{
			return $collection;
		}

		$ids = array_map(static function($item) {
			return (int)$item['ID'];
		}, $ids);

		$ormParams = $this->prepareFilteringByIdParams($ids);
		$ormResult = \Bitrix\Im\Model\MessageTable::getList($ormParams);

		foreach ($ormResult as $notifyItem)
		{
			if ($notifyItem['NOTIFY_EVENT'] === self::EVENT_PRIVATE_SYSTEM)
			{
				$notifyItem['AUTHOR_ID'] = 0;
			}

			$collection[$notifyItem['ID']] = [
				'ID' => (int)$notifyItem['ID'],
				'CHAT_ID' => $this->chatId,
				'AUTHOR_ID' => (int)$notifyItem['AUTHOR_ID'],
				'DATE' => $notifyItem['DATE_CREATE'],
				'NOTIFY_TYPE' => (int)$notifyItem['NOTIFY_TYPE'],
				'NOTIFY_MODULE' => $notifyItem['NOTIFY_MODULE'],
				'NOTIFY_EVENT' => $notifyItem['NOTIFY_EVENT'],
				'NOTIFY_TAG' => $notifyItem['NOTIFY_TAG'],
				'NOTIFY_SUB_TAG' => $notifyItem['NOTIFY_SUB_TAG'],
				'NOTIFY_TITLE' => $notifyItem['NOTIFY_TITLE'],
				//'NOTIFY_READ' => $notifyItem['NOTIFY_READ'],
				'SETTING_NAME' => $notifyItem['NOTIFY_MODULE'].'|'.$notifyItem['NOTIFY_EVENT'],
			];
			$collection[$notifyItem['ID']]['TEXT'] = \Bitrix\Im\Text::parse(
				\Bitrix\Im\Text::convertHtmlToBbCode($notifyItem['MESSAGE']),
				['LINK_TARGET_SELF' => 'Y']
			);
			if ($notifyItem['AUTHOR_ID'] && !isset($this->users[$notifyItem['AUTHOR_ID']]))
			{
				$user = User::getInstance($notifyItem['AUTHOR_ID'])->getArray([
					'JSON' => 'Y',
					'SKIP_ONLINE' => 'Y'
				]);
				$user['last_activity_date'] =
					$notifyItem['USER_LAST_ACTIVITY_DATE']
						? date('c', $notifyItem['USER_LAST_ACTIVITY_DATE']->getTimestamp())
						: false
				;
				$user['desktop_last_date'] = false;
				$user['mobile_last_date'] = false;
				$user['idle'] = false;

				$this->users[$notifyItem['AUTHOR_ID']] = $user;
			}

			//keyboard creation
			if ($notifyItem['NOTIFY_BUTTONS'])
			{
				$buttons = unserialize($notifyItem['NOTIFY_BUTTONS'], ['allowed_classes' => false]);

				$keyboard = new \Bitrix\Im\Bot\Keyboard(111);
				$command = 'notifyConfirm';
				foreach ($buttons as $button)
				{
					$keyboard->addButton(
						[
							'TEXT' => $button['TITLE'],
							'COMMAND' => $command,
							'COMMAND_PARAMS' => $notifyItem['ID'].'|'.$button['VALUE'],
							'TEXT_COLOR' => '#fff',
							'BG_COLOR' => $button['TYPE'] === 'accept' ? '#8BC84B' : '#ef4b57',
							'DISPLAY' => 'LINE'
						]
					);
				}
				$collection[$notifyItem['ID']]['NOTIFY_BUTTONS'] = $keyboard->getJson();
			}
		}

		if (count($collection) > 0)
		{
			$params = \CIMMessageParam::Get(array_keys($collection));
			foreach ($params as $notificationId => $param)
			{
				$collection[$notificationId]['PARAMS'] = empty($param) ? null : $param;
			}

			$collection = $this->fillReadStatuses($collection);
		}

		return $collection;
	}

	private function fetchConfirms(): array
	{
		$confirmCollection = [];

		$nextPageIsConfirm = $this->lastType === self::CONFIRM_TYPE;
		if ($this->firstPage || $nextPageIsConfirm)
		{
			$confirmCollection = $this->requestData(self::CONFIRM_TYPE, $this->pageLimit);
		}

		return $confirmCollection;
	}

	private function fetchSimple(int $offset): array
	{
		$simpleCollection = [];
		$nextPageIsSimple = $this->lastType === self::SIMPLE_TYPE;
		$needMoreOnFirstPage = $this->firstPage && $offset < $this->pageLimit;
		$notEnoughFromPreviousStep = $this->lastType === self::CONFIRM_TYPE && $offset < $this->pageLimit;

		if ($needMoreOnFirstPage || $notEnoughFromPreviousStep || $nextPageIsSimple)
		{
			$simpleCollection = $this->requestData(self::SIMPLE_TYPE, $this->pageLimit - $offset);
		}

		return $simpleCollection;
	}

	public function search(): array
	{
		if (!$this->chatId)
		{
			return [];
		}

		if (!$this->searchText && !$this->searchType && !$this->searchDate)
		{
			return [];
		}

		if ($this->lastId > 0)
		{
			$this->lastType = self::ALL_TYPES;
			$this->firstPage = false;
		}

		// fetching searched notifications
		$collection = $this->requestData(self::ALL_TYPES, $this->pageLimit);

		$result = [
			'CHAT_ID' => $this->chatId,
			'NOTIFICATIONS' => $collection,
			'USERS' => $this->users,
		];

		if (!$this->lastId)
		{
			$result['TOTAL_RESULTS'] = $this->requestSearchTotalCount();
		}

		foreach ($result['NOTIFICATIONS'] as $key => $value)
		{
			if ($value['DATE'] instanceof DateTime)
			{
				$result['NOTIFICATIONS'][$key]['DATE'] = date('c', $value['DATE']->getTimestamp());
			}

			$result['NOTIFICATIONS'][$key] = array_change_key_case($result['NOTIFICATIONS'][$key], CASE_LOWER);
		}
		$result['NOTIFICATIONS'] = array_values($result['NOTIFICATIONS']);
		$result['USERS'] = array_values($result['USERS']);
		$result = array_change_key_case($result, CASE_LOWER);

		return $result;
	}

	/**
	 * Agent for deleting old notifications.
	 *
	 * @return string
	 */
	public static function cleanNotifyAgent(): string
	{
		$dayCount = 60;
		$limit = 2000;
		$step = 1000;

		$batches = [];
		$result = \Bitrix\Im\Model\MessageTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=NOTIFY_TYPE' => [IM_NOTIFY_CONFIRM, IM_NOTIFY_FROM, IM_NOTIFY_SYSTEM],
				'<DATE_CREATE' => ConvertTimeStamp((time() - 86400 * $dayCount), 'FULL')
			],
			'limit' => $limit
		]);

		$batch = [];
		$i = 0;

		while ($row = $result->fetch())
		{
			if ($i++ === $step)
			{
				$i = 0;
				$batches[] = $batch;
				$batch = [];
			}

			$batch[] = (int)$row['ID'];
		}
		if (!empty($batch))
		{
			$batches[] = $batch;
		}

		$counterService = new CounterService();
		foreach ($batches as $batch)
		{
			\Bitrix\Im\Model\MessageTable::deleteBatch([
				'=ID' => $batch
			]);
			\Bitrix\Im\Model\MessageParamTable::deleteBatch([
				'=MESSAGE_ID' => $batch
			]);
			$counterService->deleteByMessageIdsForAll($batch);
		}

		return __METHOD__. '();';
	}

	private function requestSearchTotalCount(): int
	{
		$filter = [
			'=CHAT_ID' => $this->chatId,
		];

		if ($this->searchText)
		{
			$filter['*%MESSAGE'] = $this->searchText;
		}
		if ($this->searchType)
		{
			$options = explode('|', $this->searchType);
			$filter['=NOTIFY_MODULE'] = $options[0];
			if (isset($options[1]))
			{
				$filter['=NOTIFY_EVENT'] = $options[1];
			}
		}
		if ($this->searchDate)
		{
			$dateStart = new DateTime(
				$this->searchDate,
				\DateTimeInterface::RFC3339,
				new \DateTimeZone('UTC')
			);
			$dateEnd = (
				new DateTime(
					$this->searchDate,
					\DateTimeInterface::RFC3339,
					new \DateTimeZone('UTC')
				)
			)->add('1 DAY');

			$filter['><DATE_CREATE'] = [$dateStart, $dateEnd];
		}

		return \Bitrix\Im\Model\MessageTable::getCount($filter);
	}

	/**
	 * Generates params for GetList to get only IDs of the necessary notifications with filters.
	 *
	 * @param int $requestType Notification type.
	 * @param int $limit Amount of requested notifications.
	 *
	 * @return array
	 * @throws \Bitrix\Main\ObjectException
	 */
	private function prepareGettingIdParams(int $requestType, int $limit): array
	{
		$ormParams = [
			'select' => ['ID'],
			'filter' => ['=CHAT_ID' => $this->chatId],
			'order' => ['DATE_CREATE' => 'DESC', 'ID' => 'DESC'],
			'limit' => $limit
		];

		if ($requestType === self::CONFIRM_TYPE)
		{
			$ormParams['filter']['=NOTIFY_TYPE'] = IM_NOTIFY_CONFIRM;
		}
		elseif ($requestType === self::SIMPLE_TYPE)
		{
			$ormParams['filter']['!=NOTIFY_TYPE'] = IM_NOTIFY_CONFIRM;
		}
		elseif ($requestType === self::ALL_TYPES)
		{
			if ($this->searchText)
			{
				$ormParams['filter']['*%MESSAGE'] = $this->searchText;
			}
			if ($this->searchType)
			{
				$options = explode('|', $this->searchType);
				$ormParams['filter']['=NOTIFY_MODULE'] = $options[0];
				if (isset($options[1]))
				{
					$ormParams['filter']['=NOTIFY_EVENT'] = $options[1];
				}
			}
			if ($this->searchDate)
			{
				$dateStart = new DateTime(
					$this->searchDate,
					\DateTimeInterface::RFC3339,
					new \DateTimeZone('UTC')
				);
				$dateEnd = (
					new DateTime(
						$this->searchDate,
						\DateTimeInterface::RFC3339,
						new \DateTimeZone('UTC')
					)
				)->add('1 DAY');
				$ormParams['filter']['><DATE_CREATE'] = [$dateStart, $dateEnd];
			}
		}

		if (!$this->firstPage)
		{
			if (
				$requestType === self::CONFIRM_TYPE
				|| ($requestType === self::SIMPLE_TYPE && $this->lastType === self::SIMPLE_TYPE)
				|| ($requestType === self::ALL_TYPES && $this->lastType === self::ALL_TYPES)
			)
			{
				$ormParams['filter']['<ID'] = $this->lastId;
			}
		}

		return $ormParams;
	}

	/**
	 * Generates params for GetList to get all the necessary notification data filtering by notifications IDs.
	 *
	 * @param int[] $ids Notification IDs.
	 *
	 * @return array
	 */
	private function prepareFilteringByIdParams(array $ids): array
	{
		return [
			'select' => [
				'ID',
				'AUTHOR_ID',
				'MESSAGE',
				'DATE_CREATE',
				'NOTIFY_TYPE',
				'NOTIFY_EVENT',
				'NOTIFY_MODULE',
				'NOTIFY_TAG',
				'NOTIFY_SUB_TAG',
				'NOTIFY_TITLE',
				//'NOTIFY_READ',
				'NOTIFY_BUTTONS',
				'USER_LAST_ACTIVITY_DATE' => 'AUTHOR.LAST_ACTIVITY_DATE',
				//'USER_IDLE' => 'STATUS.IDLE',
				//'USER_MOBILE_LAST_DATE' => 'STATUS.MOBILE_LAST_DATE',
				//'USER_DESKTOP_LAST_DATE' => 'STATUS.DESKTOP_LAST_DATE',
			],
			'filter' => ['=ID' => $ids],
			'order' => ['DATE_CREATE' => 'DESC', 'ID' => 'DESC'],
		];
	}

	public function getLastId(): ?int
	{
		if (!$this->chatId)
		{
			return null;
		}

		$ormParams = [
			'select' => ['ID'],
			'filter' => ['=CHAT_ID' => $this->chatId],
			'order' => ['DATE_CREATE' => 'DESC', 'ID' => 'DESC'],
			'limit' => 1,
		];

		$getListResult = \Bitrix\Im\Model\MessageTable::getList($ormParams)->fetch();
		if (!$getListResult)
		{
			return null;
		}

		if (count($getListResult) === 1)
		{
			return (int)$getListResult['ID'];
		}

		return null;
	}

	private function fillReadStatuses(array $notifications): array
	{
		$messageIds = array_keys($notifications);

		$readStatuses = (new ReadService(\Bitrix\Im\Common::getUserId()))->getReadStatusesByMessageIds($messageIds);

		foreach ($notifications as $id => $notification)
		{
			$notifications[$id]['NOTIFY_READ'] = $readStatuses[$id] ? 'Y' : 'N';
		}

		return $notifications;
	}
}