<?

namespace Bitrix\UI\EntitySelector;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\UI\EntitySelector\EntityUsageTable;

class Dialog implements \JsonSerializable
{
	protected $id;

	/** @var ItemCollection */
	protected $itemCollection;

	/** @var Tab[] */
	protected $tabs = [];

	/** @var array<string, Entity> */
	protected $entities = [];

	/** @var RecentCollection */
	protected $recentItems;

	/** @var RecentCollection */
	protected $globalRecentItems;

	/** @var PreselectedCollection */
	protected $preselectedItems;

	/** @var string */
	protected $context;

	/** @var string */
	protected $footer;

	/** @var array */
	protected $footerOptions;

	/** @var boolean */
	protected $clearUnavailableItems = false;

	public function __construct(array $options)
	{
		if (isset($options['entities']) && is_array($options['entities']))
		{
			foreach ($options['entities'] as $entityOptions)
			{
				if (is_array($entityOptions))
				{
					$entity = Entity::create($entityOptions);
					if ($entity)
					{
						$this->addEntity($entity);
					}
				}
			}
		}

		if (isset($options['id']) && is_string($options['id']))
		{
			$this->id = $options['id'];
		}

		if (isset($options['context']) && is_string($options['context']) && strlen($options['context']) > 0)
		{
			$this->context = $options['context'];
		}

		if (isset($options['clearUnavailableItems']) && is_bool($options['clearUnavailableItems']))
		{
			$this->clearUnavailableItems = $options['clearUnavailableItems'];
		}

		$this->itemCollection = new ItemCollection();
		$this->recentItems = new RecentCollection();
		$this->globalRecentItems = new RecentCollection();
		$this->preselectedItems = new PreselectedCollection();

		if (isset($options['preselectedItems']) && is_array($options['preselectedItems']))
		{
			$this->setPreselectedItems($options['preselectedItems']);
		}
	}

	public function getId(): ?string
	{
		return $this->id;
	}

	public function getContext(): ?string
	{
		return $this->context;
	}

	public function getItemCollection(): ItemCollection
	{
		return $this->itemCollection;
	}

	public function getCurrentUserId(): int
	{
		return is_object($GLOBALS['USER']) ? $GLOBALS['USER']->getId() : 0;
	}

	public function addItem(Item $item)
	{
		$success = $this->getItemCollection()->add($item);
		if ($success)
		{
			$this->handleItemAdd($item);
		}
	}

	public function addItems(array $items)
	{
		foreach ($items as $item)
		{
			$this->addItem($item);
		}
	}

	public function addRecentItem(Item $item)
	{
		$this->addItem($item);

		$recentItem = $this->getRecentItems()->getByItem($item);
		if (!$recentItem && $item->isAvailableInRecentTab())
		{
			$this->getRecentItems()->add(
				new RecentItem(
					[
						'id' => $item->getId(),
						'entityId' => $item->getEntityId(),
						'loaded' => true,
					]
				)
			);
		}
	}

	public function addRecentItems(array $items)
	{
		foreach ($items as $item)
		{
			$this->addRecentItem($item);
		}
	}

	public function setFooter(string $footer, array $options = [])
	{
		if (strlen($footer) > 0)
		{
			$this->footer = $footer;
			$this->footerOptions = $options;
		}
	}

	public function getFooter(): ?string
	{
		return $this->footer;
	}

	public function getFooterOptions(): ?array
	{
		return $this->footerOptions;
	}

	public function handleItemAdd(Item $item): void
	{
		$item->setDialog($this);

		$recentItem = $this->getRecentItems()->getByItem($item);
		if ($recentItem)
		{
			$recentItem->setLoaded(true);
			$recentItem->setAvailable($item->isAvailableInRecentTab());
			$item->setContextSort($recentItem->getLastUseDate());
		}

		$globalRecentItem = $this->getGlobalRecentItems()->getByItem($item);
		if ($globalRecentItem)
		{
			$globalRecentItem->setLoaded(true);
			$item->setGlobalSort($globalRecentItem->getLastUseDate());
		}

		$preselectedItem = $this->getPreselectedItems()->getByItem($item);
		if ($preselectedItem && !$preselectedItem->getItem())
		{
			$preselectedItem->setItem($item);
		}

		foreach ($item->getChildren() as $childItem)
		{
			$this->handleItemAdd($childItem);
		}
	}

	public function getRecentItems(): RecentCollection
	{
		return $this->recentItems;
	}

	public function getGlobalRecentItems(): RecentCollection
	{
		return $this->globalRecentItems;
	}

	public function addTab(Tab $tab): void
	{
		if (!empty($tab->getId()))
		{
			$this->tabs[$tab->getId()] = $tab;
		}
	}

	/**
	 * @return Tab[]
	 */
	public function getTabs(): array
	{
		return $this->tabs;
	}

	public function getTab(string $tabId): ?Tab
	{
		return $this->tabs[$tabId] ?? null;
	}

	public function addEntity(Entity $entity)
	{
		if (!empty($entity->getId()))
		{
			$this->entities[$entity->getId()] = $entity;
		}
	}

	/**
	 * @return Entity[]
	 */
	public function getEntities(): array
	{
		return $this->entities;
	}

	/**
	 * @param string $entityId
	 *
	 * @return Entity
	 */
	public function getEntity(string $entityId): ?Entity
	{
		return $this->entities[$entityId] ?? null;
	}

	/**
	 * @internal
	 */
	public function load(): void
	{
		$entities = [];
		foreach ($this->getEntities() as $entity)
		{
			if ($entity->hasDynamicLoad())
			{
				$entities[] = $entity->getId();
			}
		}

		if (empty($entities))
		{
			return;
		}

		$this->fillRecentItems($entities);
		if ($this->getContext() !== null)
		{
			$this->fillGlobalRecentItems($entities);
		}

		foreach ($entities as $entityId)
		{
			$this->getEntity($entityId)->getProvider()->fillDialog($this);
		}

		$this->loadRecentItems();
		$this->loadPreselectedItems();
	}

	/**
	 * @internal
	 * @param SearchQuery $searchQuery
	 */
	public function doSearch(SearchQuery $searchQuery)
	{
		if (empty($searchQuery->getQueryWords()))
		{
			return;
		}

		$entities = [];
		foreach ($this->getEntities() as $entity)
		{
			$hasDynamicSearch =
				$entity->isSearchable() &&
				($entity->hasDynamicSearch() || $searchQuery->hasDynamicSearchEntity($entity->getId()))
			;

			if ($hasDynamicSearch)
			{
				$entities[] = $entity->getId();
			}
		}

		$this->fillGlobalRecentItems($entities);
		foreach ($entities as $entityId)
		{
			$this->getEntity($entityId)->getProvider()->doSearch($searchQuery, $this);
		}
	}

	/**
	 * @internal
	 * @param Item $parentItem
	 */
	public function getChildren(Item $parentItem)
	{
		$entities = [];
		foreach ($this->getEntities() as $entity)
		{
			if ($entity->hasDynamicLoad())
			{
				$entities[] = $entity->getId();
			}
		}

		$entity = $this->getEntity($parentItem->getEntityId());
		if ($entity && $entity->hasDynamicLoad())
		{
			$this->fillGlobalRecentItems($entities);
			$entity->getProvider()->getChildren($parentItem, $this);
		}
	}

	public function setPreselectedItems(array $preselectedItems)
	{
		$this->preselectedItems->load($preselectedItems);
	}

	public function getPreselectedItems(): PreselectedCollection
	{
		return $this->preselectedItems;
	}

	/**
	 * @internal
	 */
	public function loadPreselectedItems($selectedItemsMode = true): void
	{
		if ($this->getPreselectedItems()->count() < 1)
		{
			return;
		}

		foreach ($this->getPreselectedItems()->getItems() as $entityId => $preselectedItems)
		{
			$unloadedIds = [];
			$entity = $this->getEntity($entityId) ?? Entity::create(['id' => $entityId]);
			foreach ($preselectedItems as $preselectedItem)
			{
				// Entity doesn't exist
				if (!$entity && $selectedItemsMode)
				{
					$this->addItem(self::createHiddenItem($preselectedItem->getId(), $entityId));
				}
				else if (!$preselectedItem->isLoaded())
				{
					$unloadedIds[] = $preselectedItem->getId();
				}
			}

			if ($entity && !empty($unloadedIds))
			{
				$availableItems = [];
				$items =
					$selectedItemsMode
					? $entity->getProvider()->getSelectedItems($unloadedIds)
					: $entity->getProvider()->getItems($unloadedIds)
				;

				foreach ($items as $item)
				{
					$availableItems[$item->getId()] = $item;
				}

				foreach ($unloadedIds as $unloadedId)
				{
					$item = $availableItems[$unloadedId] ?? null;
					if ($item)
					{
						$this->addItem($item);
					}
					else if ($selectedItemsMode)
					{
						$this->addItem(self::createHiddenItem($unloadedId, $entityId));
					}
				}
			}
		}
	}

	public function shouldClearUnavailableItems(): bool
	{
		return $this->clearUnavailableItems;
	}

	public static function createHiddenItem($id, $entityId): Item
	{
		return new Item([
			'id' => $id,
			'entityId' => $entityId,
			'title' => Loc::getMessage("UI_SELECTOR_HIDDEN_ITEM_TITLE"),
			'hidden' => true,
			'deselectable' => false,
			'searchable' => false,
			'saveable' => false,
			'link' => '',
			'avatar' => '',
			'availableInRecentTab' => false
		]);
	}

	public static function getSelectedItems(array $ids, array $options = []): ItemCollection
	{
		return self::getItemsInternal($ids, $options, true);
	}

	public static function getItems(array $ids, array $options = []): ItemCollection
	{
		return self::getItemsInternal($ids, $options, false);
	}

	private static function getItemsInternal(array $ids, array $options = [], $selectedItemsMode = true): ItemCollection
	{
		$isAssocArray = array_keys($options) !== range(0, count($options) - 1);
		$dialogOptions = $isAssocArray ? $options : ['entities' => $options];

		$dialog = new self($dialogOptions);
		$dialog->setPreselectedItems($ids);
		$dialog->loadPreselectedItems($selectedItemsMode);
		$dialog->applyFilters();

		return $dialog->getItemCollection();
	}

	public function saveRecentItems(array $recentItems)
	{
		if ($this->getContext() === null)
		{
			return;
		}

		foreach ($recentItems as $recentItemOptions)
		{
			if (!is_array($recentItemOptions))
			{
				continue;
			}

			$recentItem = new Item($recentItemOptions);
			$entity = $this->getEntity($recentItem->getEntityId());

			if ($entity)
			{
				$entity->getProvider()->handleBeforeItemSave($recentItem);
				if ($recentItem->isSaveable())
				{
					EntityUsageTable::merge([
						'USER_ID' => $GLOBALS['USER']->getId(),
						'CONTEXT' => $this->getContext(),
						'ENTITY_ID' => $recentItem->getEntityId(),
						'ITEM_ID' => $recentItem->getId()
					]);
				}
			}
		}
	}

	private function fillRecentItems(array $entities)
	{
		if (empty($entities))
		{
			return;
		}

		if ($this->getContext() === null)
		{
			$usages = $this->getGlobalUsages($entities, 50);
			while ($usage = $usages->fetch())
			{
				$this->getRecentItems()->add(
					new RecentItem(
						[
							'id' => $usage['ITEM_ID'],
							'entityId' => $usage['ENTITY_ID'],
							'lastUseDate' => $usage['MAX_LAST_USE_DATE']->getTimestamp()
						]
					)
				);
			}
		}
		else
		{
			$usages = $this->getContextUsages($entities);
			foreach ($usages as $usage)
			{
				$this->getRecentItems()->add(
					new RecentItem(
						[
							'id' => $usage->getItemId(),
							'entityId' => $usage->getEntityId(),
							'lastUseDate' => $usage->getLastUseDate()->getTimestamp()
						]
					)
				);
			}
		}
	}

	private function fillGlobalRecentItems(array $entities)
	{
		if (empty($entities))
		{
			return;
		}

		$usages = $this->getGlobalUsages($entities);
		while ($usage = $usages->fetch())
		{
			$this->getGlobalRecentItems()->add(
				new RecentItem(
					[
						'id' => $usage['ITEM_ID'],
						'entityId' => $usage['ENTITY_ID'],
						'lastUseDate' => $usage['MAX_LAST_USE_DATE']->getTimestamp()
					]
				)
			);
		}
	}

	private function getContextUsages(array $entities)
	{
		return EntityUsageTable::getList(
			[
				'select' => ['*'],
				'filter' => [
					'=USER_ID' => $this->getCurrentUserId(),
					'=CONTEXT' => $this->getContext(),
					'@ENTITY_ID' => $entities
				],
				'limit' => 50,
				'order' => [
					'LAST_USE_DATE' => 'DESC'
				]
			]
		)->fetchCollection();
	}

	private function getGlobalUsages(array $entities, int $limit = 200)
	{
		$query = EntityUsageTable::query();
		$query->setSelect(['ENTITY_ID', 'ITEM_ID', 'MAX_LAST_USE_DATE']);
		$query->setGroup(['ENTITY_ID', 'ITEM_ID']);
		$query->where('USER_ID', $this->getCurrentUserId());
		$query->whereIn('ENTITY_ID', $entities);

		if ($this->getContext() !== null)
		{
			$query->whereNot('CONTEXT', $this->getContext());
		}

		$query->registerRuntimeField(new ExpressionField('MAX_LAST_USE_DATE', 'MAX(%s)', 'LAST_USE_DATE'));
		$query->setOrder(['MAX_LAST_USE_DATE' => 'desc']);
		$query->setLimit($limit);

		return $query->exec();
	}

	private function loadRecentItems()
	{
		foreach ($this->getEntities() as $entity)
		{
			$unloadedIds = [];
			$unavailableIds = [];
			$recentItems = $this->getRecentItems()->getEntityItems($entity->getId());
			foreach ($recentItems as $recentItem)
			{
				if (!$recentItem->isAvailable())
				{
					$unavailableIds[] = $recentItem->getId();
				}
				else if (!$recentItem->isLoaded())
				{
					$unloadedIds[] = $recentItem->getId();
				}
			}

			if (!empty($unloadedIds))
			{
				$availableItems = [];
				$items = $entity->getProvider()->getItems($unloadedIds);
				foreach ($items as $item)
				{
					if ($item instanceof Item)
					{
						$availableItems[$item->getId()] = $item;
					}
				}

				foreach ($unloadedIds as $unloadedId)
				{
					$item = $availableItems[$unloadedId] ?? null;
					if ($item && $item->isAvailableInRecentTab())
					{
						$this->addRecentItem($item);
					}
					else
					{
						$unavailableIds[] = $unloadedId;
					}
				}
			}

			if ($this->getContext() !== null && $this->shouldClearUnavailableItems() && !empty($unavailableIds))
			{
				EntityUsageTable::deleteByFilter([
					'=USER_ID' => $this->getCurrentUserId(),
					'=CONTEXT' => $this->getContext(),
					'=ENTITY_ID' => $entity->getId(),
					'@ITEM_ID' => $unavailableIds
				]);
			}
		}
	}

	public function applyFilters(): void
	{
		foreach ($this->getEntities() as $entity)
		{
			$items = $this->getItemCollection()->getEntityItems($entity->getId());
			if (empty($items))
			{
				continue;
			}

			$filters = $entity->getFilters();
			foreach ($filters as $filter)
			{
				$filter->apply($items, $this);
			}
		}
	}

	/**
	 * @internal
	 */
	public function getAjaxData(): array
	{
		$this->applyFilters();

		return $this->jsonSerialize();
	}

	public function jsonSerialize()
	{
		$json = [
			'id' => $this->getId(),
			'items' => $this->getItemCollection(),
			'tabs' => array_values($this->getTabs()),
			'entities' => array_values($this->getEntities()),
		];

		if ($this->getFooter())
		{
			$json['footer'] = $this->getFooter();
			$json['footerOptions'] = $this->getFooterOptions();
		}

		if ($this->getRecentItems()->count() > 0)
		{
			$json['recentItems'] = $this->getRecentItems();
		}

		if ($this->getPreselectedItems()->count() > 0)
		{
			$json['preselectedItems'] = $this->getPreselectedItems();
		}

		return $json;
	}
}