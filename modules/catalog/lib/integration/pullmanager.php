<?php

namespace Bitrix\Catalog\Integration;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Mobile\Integration\Catalog\EntityEditor\StoreDocumentProvider;
use Bitrix\Mobile\Integration\Catalog\StoreDocumentList\Item;
use Bitrix\Pull\Event;
use Bitrix\Pull\Model\WatchTable;

class PullManager
{
	public const MODULE_ID = 'catalog';

	public const EVENT_DOCUMENTS_LIST_UPDATED = 'CATALOG_DOCUMENTS_LIST_UPDATED';

	protected const EVENT_DOCUMENT_ADDED = 'ADDED';
	protected const EVENT_DOCUMENT_UPDATED = 'UPDATED';
	protected const EVENT_DOCUMENT_DELETED = 'DELETED';

	protected $eventIds = [];
	protected $isEnabled = false;
	protected $isMobileIncluded = false;

	private static $instance;

	public static function getInstance(): PullManager
	{
		if (!isset(self::$instance))
		{
			self::$instance = ServiceLocator::getInstance()->get('catalog.integration.pullmanager');
		}

		return self::$instance;
	}

	public function __construct()
	{
		$this->isEnabled = $this->includeModule();
		$this->isMobileIncluded = Loader::includeModule('mobile');
	}

	private function __clone()
	{
	}

	/**
	 * @return bool
	 */
	protected function includeModule(): bool
	{
		try
		{
			return Loader::includeModule('pull');
		}
		catch(LoaderException $exception)
		{
			return false;
		}
	}

	/**
	 * @return bool
	 */
	public function isEnabled(): bool
	{
		return $this->isEnabled;
	}

	/**
	 * @param array $items
	 * @param array|null $params
	 * @return bool
	 */
	public function sendDocumentAddedEvent(array $items, ?array $params = null): bool
	{
		$this->prepareItems($items, self::EVENT_DOCUMENT_ADDED);
		return $this->sendItemEvent(self::EVENT_DOCUMENT_ADDED, $items, $params);
	}

	/**
	 * @param array $items
	 * @param array|null $params
	 * @return bool
	 */
	public function sendDocumentsUpdatedEvent(array $items, ?array $params = null): bool
	{
		$this->prepareItems($items);
		return $this->sendItemEvent(self::EVENT_DOCUMENT_UPDATED, $items, $params);
	}

	/**
	 * @param array $items
	 * @param array|null $params
	 * @return bool
	 */
	public function sendDocumentDeletedEvent(array $items, ?array $params = null): bool
	{
		return $this->sendItemEvent(self::EVENT_DOCUMENT_DELETED, $items, $params);
	}

	/**
	 * @param string $eventName
	 * @param array $items
	 * @param array|null $params
	 * @return bool
	 */
	protected function sendItemEvent(string $eventName, array $items, ?array $params = null): bool
	{
		$tag = $this->getTag($params);

		$eventParams = $this->prepareItemEventParams($items, $eventName);
		$eventParams['skipCurrentUser'] = (!isset($params['SKIP_CURRENT_USER']) || $params['SKIP_CURRENT_USER']);

		return $this->sendEvent($items, $tag, $eventParams);
	}

	/**
	 * @param array|null $params
	 * @return string
	 */
	protected function getTag(?array $params = null): string
	{
		$entityType = ($params['TYPE'] ?? '');
		return static::getEventName(static::EVENT_DOCUMENTS_LIST_UPDATED, $entityType);
	}

	/**
	 * @param string $eventName
	 * @param string $entityType
	 * @return string
	 */
	protected static function getEventName(string $eventName, $entityType = ''): string
	{
		if(!empty($entityType) && (is_string($entityType) || is_numeric($entityType)))
		{
			$eventName .= '_' . $entityType;
		}

		return $eventName;
	}

	/**
	 * @param array $items
	 * @param string $action
	 */
	protected function prepareItems(array &$items, string $action = self::EVENT_DOCUMENT_UPDATED): void
	{
		$userId = CurrentUser::get()->getId();

		foreach ($items as $key => $item)
		{
			$items[$key]['userId'] = $userId;
			if (!$this->isMobileIncluded)
			{
				continue;
			}

			$document = (
			isset($item['data']['oldFields'])
				? array_merge($item['data']['oldFields'], $item['data']['fields'])
				: $item['data']['fields']
			);
			$document['ID'] = $item['id'];

			if (
				$action === self::EVENT_DOCUMENT_UPDATED
				|| $action === self::EVENT_DOCUMENT_ADDED
			)
			{
				$mobileItem = new Item($document);
				$preparedMobileItem = $mobileItem->prepareItem();
				$items[$key]['mobileData'] = $preparedMobileItem['data'];

				/*
				 * Because we not have the realtime on desktop, I temporarily remove the raw data
				 * when we do, then it will be necessary to process this raw data
				 */
				unset($items[$key]['data']);
			}
		}
	}

	/**
	 * @param string $eventName
	 * @param string $entityType
	 * @param int $itemId
	 * @return string|null
	 */
	protected static function getItemEventName(string $eventName, string $entityType, int $itemId): ?string
	{
		if (!empty($entityType) && $itemId > 0)
		{
			return $eventName . '_' . $entityType . '_' . $itemId;
		}

		return null;
	}

	/**
	 * @param array $items
	 * @param string $eventId
	 * @param array $params
	 * @return bool
	 */
	protected function sendEvent(array $items, string $eventId, array $params = []): bool
	{
		//$params['eventId'] = $eventId;
		$userIds = $this->getSubscribedUserIdsWithItemPermissions($items, $eventId);

		if ($params['skipCurrentUser'])
		{
			$currentUser = CurrentUser::get()->getId();
			unset($userIds[$currentUser]);
		}
		unset($params['skipCurrentUser']);

		return $this->sendUserEvent($eventId, $params, $userIds);
	}

	/**
	 * @param array $items
	 * @param string $eventName
	 * @return array
	 */
	protected function prepareItemEventParams(array $items, string $eventName = ''): array
	{
		return [
			'eventName' => $eventName,
			'items' => $items
		];
	}

	/**
	 * @param array $items
	 * @param string $eventName
	 * @return array
	 */
	protected function getSubscribedUserIdsWithItemPermissions(array $items, string $eventName): array
	{
		if(!$this->isEnabled())
		{
			return [];
		}
		$userIds = WatchTable::getUserIdsByTag($eventName);
		return $this->filterUserIdsWhoCanViewItem($items, $userIds);
	}

	/**
	 * @param array $items
	 * @param array $userIds
	 * @return array
	 */
	protected function filterUserIdsWhoCanViewItem(array $items, array $userIds): array
	{
		global $USER;
		$result = [];

		foreach($userIds as $userId)
		{
			$userId = (int)$userId;
			if($userId > 0 && $USER->CanDoOperation('catalog_read', $userId))
			{
				$result[$userId] = $userId;
			}
		}

		return $result;
	}

	/**
	 * @param string $tag
	 * @param bool $immediate
	 * @return string|null
	 */
	protected function subscribeOnEvent(string $tag, bool $immediate = true): ?string
	{
		if($this->isEnabled && !empty($tag))
		{
			$addResult = \CPullWatch::Add(CurrentUser::get()->getId(), $tag, $immediate);
			if($addResult)
			{
				return $tag;
			}
		}

		return null;
	}

	/**
	 * @param string $tag
	 * @param array $params
	 * @param array|null $userIds
	 * @return bool
	 */
	protected function sendUserEvent(string $tag, array $params = [], ?array $userIds = null): bool
	{
		if(!$this->isEnabled())
		{
			return false;
		}

		if(is_array($userIds))
		{
			if(!empty($userIds))
			{
				return Event::add($userIds, [
					'module_id' => static::MODULE_ID,
					'command' => $tag,
					'params' => $params,
				]);
			}
		}
		else
		{
			return \CPullWatch::AddToStack($tag, [
				'module_id' => static::MODULE_ID,
				'command' => $tag,
				'params' => $params,
			]);
		}

		return false;
	}

	/**
	 * @return array
	 */
	public static function onGetDependentModule(): array
	{
		return [
			'MODULE_ID' => static::MODULE_ID,
			'USE' => ['PUBLIC_SECTION'],
		];
	}
}
