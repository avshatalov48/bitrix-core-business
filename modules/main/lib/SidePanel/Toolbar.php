<?
namespace Bitrix\Main\SidePanel;

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;

class Toolbar
{
	private EO_Toolbar $entity;
	private const CACHE_TTL = 3600;
	private const CACHE_PATH = '/bx/main/sidepanel/toolbar/';

	private function __construct(EO_Toolbar $entity)
	{
		$this->entity = $entity;
	}

	public function getId(): int
	{
		return $this->entity->getId();
	}

	public function getContext(): string
	{
		return $this->entity->getContext();
	}

	public function getUserId(): int
	{
		return $this->entity->getUserId();
	}

	public function isCollapsed(): bool
	{
		return $this->entity->getCollapsed();
	}

	public static function get(string $context, int $userId = 0): ?static
	{
		$toolbarUserId = $userId > 0 ? $userId : (int)\Bitrix\Main\Engine\CurrentUser::get()->getId();

		$cache = Cache::createInstance();
		$cacheId = static::getCacheId('toolbar', $context, $toolbarUserId);
		$cachePath = static::getCachePath($toolbarUserId);
		if ($cache->initCache(static::CACHE_TTL, $cacheId, $cachePath))
		{
			$vars = $cache->getVars();
			$entity = is_array($vars['toolbar']) ? EO_Toolbar::wakeUp($vars['toolbar']) : null;

			return $entity ? new static($entity) : null;
		}

		$entity = ToolbarTable::getList([
			'filter' => [
				'=CONTEXT' => $context,
				'=USER_ID' => $toolbarUserId,
			]
		])->fetchObject();

		$cache->startDataCache();
		$cache->endDataCache(['toolbar' => $entity ? $entity->collectValues() : null]);

		return $entity ? new static($entity) : null;
	}

	public static function getOrCreate(string $context, int $userId = 0): static
	{
		$toolbarEntity = static::get($context, $userId);
		if ($toolbarEntity === null)
		{
			$toolbarEntity = new EO_Toolbar();
			$toolbarEntity->setContext($context);
			$toolbarEntity->setUserId($userId > 0 ? $userId : (int)\Bitrix\Main\Engine\CurrentUser::get()->getId());
			$result = $toolbarEntity->save();

			if (!$result->isSuccess())
			{
				throw new \Bitrix\Main\SystemException($result->getErrors()[0]->getMessage(), $result->getErrors()[0]->getCode());
			}

			$toolbar = new static($toolbarEntity);
			$toolbar->clearToolbarCache();

			return $toolbar;
		}

		return $toolbarEntity;
	}

	public static function getCacheId(string $prefix, string $context, int $userId): string
	{
		return $prefix . '_' . $userId . '_' . md5($context);
	}

	public static function getCachePath(int $userId): string
	{
		return static::CACHE_PATH . $userId . '/';
	}

	public function createOrUpdateItem(array $options): Result
	{
		$result = new Result();
		$entityType = $options['entityType'] ?? '';
		$entityId = $options['entityId'] ?? '';

		$item = $this->getItem($entityType, $entityId);
		if ($item === null)
		{
			$item = new EO_ToolbarItem();
			$item->setToolbarId($this->getId());
			$item->setEntityType($entityType);
			$item->setEntityId($entityId);
			$item->setTitle($options['title'] ?? '');
			$item->setUrl($options['url'] ?? '');
			$saveResult = $item->save();
			if (!$saveResult->isSuccess())
			{
				$result->addErrors($saveResult->getErrors());
			}
			else
			{
				$result->setData(['item' => $item]);
			}
		}
		else
		{
			$item->setLastUseDate(new DateTime());
			$item->save();
			$result->setData(['item' => $item]);
		}

		$this->clearItemsCache();

		return $result;
	}

	public function getItem(string $entityType, string $entityId): ?EO_ToolbarItem
	{
		return ToolbarItemTable::getList([
			'filter' => [
				'=TOOLBAR_ID' => $this->getId(),
				'=ENTITY_TYPE' => $entityType,
				'=ENTITY_ID' => $entityId,
			]
		])->fetchObject();
	}

	public function getItems(): EO_ToolbarItem_Collection
	{
		$cache = Cache::createInstance();
		$cacheId = static::getCacheId('items', $this->getContext(), $this->getUserId());
		$cachePath = static::getCachePath($this->getUserId());
		if ($cache->initCache(static::CACHE_TTL, $cacheId, $cachePath))
		{
			$vars = $cache->getVars();

			return EO_ToolbarItem_Collection::wakeUp($vars['items']);
		}

		$itemCollection = ToolbarItemTable::getList([
			'filter' => [
				'=TOOLBAR_ID' => $this->getId(),
			],
			'order' => ['LAST_USE_DATE' => 'DESC'],
			'limit' => 100,
		])->fetchCollection();

		$items = [];
		foreach ($itemCollection as $item)
		{
			$items[] = $item->collectValues();
		}

		$cache->startDataCache();
		$cache->endDataCache(['items' => $items]);

		return $itemCollection;
	}

	public function removeItem(string $entityType, string $entityId)
	{
		$item = $this->getItem($entityType, $entityId);
		$item?->delete();

		$this->clearItemsCache();
	}

	public function removeAll()
	{
		ToolbarItemTable::deleteByFilter([
			'=TOOLBAR_ID' => $this->getId(),
		]);

		$this->clearItemsCache();
	}

	public function clearCache(string $prefix)
	{
		$cache = Cache::createInstance();
		$cacheId = static::getCacheId($prefix, $this->getContext(), $this->getUserId());
		$cachePath = static::getCachePath($this->getUserId());
		$cache->clean($cacheId, $cachePath);
	}

	public function clearToolbarCache(): void
	{
		$this->clearCache('toolbar');
	}

	public function clearItemsCache(): void
	{
		$this->clearCache('items');
	}

	public function collapse(): void
	{
		$this->clearToolbarCache();

		$this->entity->setCollapsed(true);
		$this->entity->save();
	}

	public function expand(): void
	{
		$this->clearToolbarCache();

		$this->entity->setCollapsed(false);
		$this->entity->save();
	}
}
