<?php

namespace Bitrix\Socialnetwork\Space\Toolbar;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Internals\Space\Composition\SpaceCompositionTable;
use Bitrix\Socialnetwork\Space\Cache\Cache;
use Bitrix\Socialnetwork\Space\Toolbar\Composition\CompositionItemCollection;
use Bitrix\Socialnetwork\Space\Toolbar\Composition\AbstractCompositionItem;
use Bitrix\Socialnetwork\Space\Toolbar\Composition\Item\BusinessProcess;
use Bitrix\Socialnetwork\Space\Toolbar\Composition\Item\CalendarEvent;
use Bitrix\Socialnetwork\Space\Toolbar\Composition\Item\Crm;
use Bitrix\Socialnetwork\Space\Toolbar\Composition\Item\ListElement;
use Bitrix\Socialnetwork\Space\Toolbar\Composition\Item\Message;
use Bitrix\Socialnetwork\Space\Toolbar\Composition\Item\Task;
use Exception;

class Composition
{
	public const FILTER = 'MODULE_ID';
	public const CACHE_ID = 'socialnetwork-toolbar-composition-';

	private CompositionItemCollection $collection;
	private Cache $cache;

	public function __construct(private int $userId, private int $spaceId = 0)
	{
		$this->init();
	}

	public function setDefaultSettings(): Result
	{
		$result = new Result();
		try
		{
			if (!SpaceCompositionTable::isDataFilled($this->userId, $this->spaceId))
			{
				$result = $this->setSettings($this->getDefaultSettings());
			}
		}
		catch (Exception $exception)
		{
			$result->addError(Error::createFromThrowable($exception));
		}

		return $result;
	}

	public function setSettings(array $settings): Result
	{
		$result = new Result();
		$collection = CompositionItemCollection::createFromModuleIds($settings)->fillBoundItems();
		try
		{
			$composition = SpaceCompositionTable::getByIds($this->userId, $this->spaceId);
			if (is_null($composition))
			{
				$result = SpaceCompositionTable::fill($this->userId, $this->spaceId, $collection->toArray());
			}
			else
			{
				$result = $composition
					->setSettings($collection->toArray())
					->save();
			}
		}
		catch (Exception $exception)
		{
			$result->addError(Error::createFromThrowable($exception));
		}

		if ($result->isSuccess())
		{
			$this->cache->store($result->getData()['SETTINGS']);
		}

		return $result;
	}

	public function getDefaultSettings(bool $withHidden = true): array
	{
		$items = [];
		foreach ($this->collection as $item)
		{
			/** @var AbstractCompositionItem $item */
			if ($item->isHidden() && !$withHidden)
			{
				continue;
			}

			$items[] = $item->getModuleId();
		}

		return $items;
	}

	public function getSettings(bool $withHidden = true): array
	{
		if ($data = $this->cache->get())
		{
			$collection = CompositionItemCollection::createFromModuleIds($data);
			!$withHidden && $collection->hideItems();

			return $collection->toArray();
		}

		try
		{
			$composition = SpaceCompositionTable::getByIds($this->userId, $this->spaceId);
			$collection = CompositionItemCollection::createFromModuleIds((array)$composition?->getSettings());
			$cacheCollection = clone $collection;

			!$withHidden && $collection->hideItems();

			$this->cache->store(
				$cacheCollection
					->fillBoundItems()
					->hideItems()
					->toArray()
			);

			return $collection->toArray();
		}
		catch (Exception)
		{
			return $this->getDefaultSettings();
		}
	}

	public function getDeselectedSettings(bool $withHidden = true, bool $withDisabled = true): array
	{
		$deselectedSettings = array_diff($this->getDefaultSettings($withHidden), $this->getSettings());

		return $withDisabled
			? array_merge($deselectedSettings, $this->getDisabledSettings())
			: $deselectedSettings;
	}

	public function getDisabledSettings(): array
	{
		// disable all crm events in spaces
		return [
			(new Crm())->getModuleId(),
		];
	}

	private function init(): void
	{
		$this->cache = new Cache($this->userId, $this->spaceId);

		$this->collection = (new CompositionItemCollection())
			->addItem(new Task())
			->addItem(new CalendarEvent())
			->addItem(new Message())
			->addItem(new BusinessProcess())
			->addItem(new ListElement());
	}

	private function getCacheId(): string
	{
		return static::CACHE_ID . $this->userId . '-' . $this->spaceId;
	}
}