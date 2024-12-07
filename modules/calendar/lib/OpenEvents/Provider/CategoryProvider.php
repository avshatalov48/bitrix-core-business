<?php

namespace Bitrix\Calendar\OpenEvents\Provider;

use Bitrix\Calendar\Core\Common;
use Bitrix\Calendar\EventCategory\Dto\EventCategoryPermissions;
use Bitrix\Calendar\EventCategory\EventCategoryAccess;
use Bitrix\Calendar\Internals\Counter;
use Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryAttendeeCollection;
use Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryCollection;
use Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory;
use Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee;
use Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryAttendeeTable;
use Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryTable;
use Bitrix\Calendar\OpenEvents\Item\Category;
use Bitrix\Calendar\OpenEvents\Provider;
use Bitrix\Calendar\OpenEvents\Provider\Category\Enum\CategoryOrderEnum;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Calendar\OpenEvents\Service\DefaultCategoryService;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Text\Emoji;

final class CategoryProvider
{
	private int $userId;

	public function __construct(?int $userId = null)
	{
		$this->userId = $userId ?? (int)CurrentUser::get()->getId();
	}

	public function getCategoryCollection(array $categoryIds): OpenEventCategoryCollection
	{
		$collection = new OpenEventCategoryCollection();

		$categoryResult = OpenEventCategoryTable::query()
			->setSelect(['ID', 'CHANNEL_ID', 'NAME'])
			->whereIn('ID', $categoryIds)
			->exec()
		;

		while($category = $categoryResult->fetchObject())
		{
			$category->setName($this->prepareCategoryName($category->getName()));
			$collection->add($category);
		}

		return $collection;
	}

	public function getAllCategory(): Category
	{
		return new Category(
			id: 0,
			closed: false,
			name: Loc::getMessage('CALENDAR_OPEN_EVENTS_ALL_EVENTS'),
			description: Loc::getMessage('CALENDAR_OPEN_EVENTS_ALL_EVENTS'),
			creatorId: 0,
			eventsCount: 0,
			permissions: new EventCategoryPermissions(false, false),
			channelId: -1,
			isMuted: false,
			newCount: Counter::getInstance($this->userId)->get(Counter\CounterDictionary::COUNTER_OPEN_EVENTS),
		);
	}

	public function getByChannelId(int $channelId): ?Category
	{
		$category = OpenEventCategoryTable::query()
			->where('CHANNEL_ID', $channelId)
			->fetchObject()
		;

		if (!$category)
		{
			return null;
		}

		return new Category(
			id: $category->getId(),
			closed: $category->getClosed(),
			name: $category->getName(),
			description: $category->getDescription(),
			creatorId: $category->getCreatorId(),
			eventsCount: $category->getEventsCount(),
			permissions: new EventCategoryPermissions(false, false),
			channelId: $category->getChannelId(),
			updatedAt: $category->getLastActivity()->getTimestamp(),
		);
	}

	/**
	 * @return Category[]
	 */
	public function list(Provider\Category\Query $categoryQuery): array
	{
		$query = OpenEventCategoryAttendeeTable::query();

		$this->prepareSelect($query);
		$this->prepareFilter($query, $categoryQuery);
		$this->prepareLimit($query, $categoryQuery);
		$this->prepareOrder($query, $categoryQuery);

		$categoryCollection = $query->fetchCollection();

		return $this->prepareResult($categoryCollection, $categoryQuery);
	}

	private function prepareSelect(Query $query): void
	{
		$query->registerRuntimeField($this->getCategoryReference());

		$query->setSelect([
			'CATEGORY_ID',
			'CATEGORY.NAME',
			'CATEGORY.DESCRIPTION',
			'CATEGORY.CLOSED',
			'CATEGORY.CREATOR_ID',
			'CATEGORY.EVENTS_COUNT',
			'CATEGORY.CHANNEL_ID',
			'CATEGORY.LAST_ACTIVITY',
		]);
	}

	private function prepareFilter(Query $query, Provider\Category\Query $categoryQuery): void
	{
		$query->whereIn('USER_ID', array_unique([Common::SYSTEM_USER_ID, $this->userId]));

		if (isset($categoryQuery->filter->isBanned))
		{
			$bannedCategoryIds = (new CategoryBanProvider($this->userId))->listIds();
			if (!empty($bannedCategoryIds))
			{
				if ($categoryQuery->filter->isBanned)
				{
					$query->whereIn('CATEGORY_ID', $bannedCategoryIds);
				}
				else
				{
					$query->whereNotIn('CATEGORY_ID', $bannedCategoryIds);
				}
			}
		}

		if (!empty($categoryQuery->filter->channelId))
		{
			$query->where('CATEGORY.CHANNEL_ID', $categoryQuery->filter->channelId);
		}

		if (!empty($categoryQuery->filter->query))
		{
			$query->whereLike('CATEGORY.NAME', '%' . Emoji::encode($categoryQuery->filter->query) . '%');
		}

		if (!empty($categoryQuery->filter->categoryId))
		{
			$query->where('CATEGORY_ID', $categoryQuery->filter->categoryId);
		}
	}

	private function prepareLimit(Query $query, Provider\Category\Query $categoryQuery): void
	{
		if (!empty($categoryQuery->limit))
		{
			$query->setOffset($categoryQuery->page * $categoryQuery->limit);
			$query->setLimit($categoryQuery->limit);
		}
	}

	private function prepareOrder(Query $query, Provider\Category\Query $categoryQuery): void
	{
		if (!empty($categoryQuery->order))
		{
			$query->addOrder(...$this->getOrder($categoryQuery->order));
		}
	}

	private function getOrder(CategoryOrderEnum $order): array
	{
		return match ($order) {
			CategoryOrderEnum::BY_ACTIVITY => ['CATEGORY.LAST_ACTIVITY', 'DESC'],
			CategoryOrderEnum::BY_NAME => ['CATEGORY.NAME', 'ASC'],
		};
	}

	/**
	 * @return Category[]
	 */
	private function prepareResult(
		OpenEventCategoryAttendeeCollection $categoryCollection,
		Provider\Category\Query $categoryQuery,
	): array
	{
		$categoryIds = $categoryCollection->getCategoryIdList();

		$defaultCategoryMatchesSearchString = !empty($categoryQuery->filter->query)
			&& mb_stripos($this->getDefaultCategoryName(), $categoryQuery->filter->query) !== false;

		if ($categoryQuery->requireDefault || $defaultCategoryMatchesSearchString)
		{
			$defaultCategoryId = DefaultCategoryService::getInstance()->getCategoryId();

			if (!in_array($defaultCategoryId, $categoryIds, true))
			{
				$categoryCollection->add($this->getDefaultRow());

				$categoryIds[] = $defaultCategoryId;
			}
		}

		if ($categoryCollection->isEmpty())
		{
			return [];
		}

		$mutedCategories = (new CategoryMuteProvider($this->userId))->getByCategoryIds($categoryIds);

		if (isset($categoryQuery->filter->isBanned))
		{
			$bannedCategoryIds = $categoryQuery->filter->isBanned ? $categoryIds : [];
		}
		else
		{
			$bannedCategoryIds = (new CategoryBanProvider($this->userId))->listIds();
		}

		$counter = Counter::getInstance($this->userId);

		$categories = [];
		foreach ($categoryCollection as $row)
		{
			/**
			 * @var OpenEventCategory $category
			 */
			$category = $row->get('CATEGORY');

			if (!$category)
			{
				continue;
			}

			$name = $this->prepareCategoryName($category->getName());

			if (
				$category->getName() === DefaultCategoryService::DEFAULT_CATEGORY_NAME
				&& !empty($categoryQuery->filter->query)
				&& mb_stripos($name, $categoryQuery->filter->query) === false
			)
			{
				continue;
			}

			$categories[] = new Category(
				id: $category->getId(),
				closed: $category->getClosed(),
				name: $name,
				description: $this->prepareCategoryDescription($category->getDescription()),
				creatorId: $category->getCreatorId(),
				eventsCount: $category->getEventsCount(),
				permissions: EventCategoryAccess::getPermissionsForEntity($category, $this->userId),
				channelId: $category->getChannelId(),
				isMuted: $mutedCategories[$category->getId()] ?? false,
				isBanned: in_array($category->getId(), $bannedCategoryIds, true),
				newCount: $counter->get(Counter\CounterDictionary::COUNTER_OPEN_EVENTS, $category->getId()),
				updatedAt: $category->getLastActivity()->getTimestamp(),
			);
		}

		return $categories;
	}

	public function prepareCategoryName(string $name): string
	{
		if ($name === DefaultCategoryService::DEFAULT_CATEGORY_NAME)
		{
			return $this->getDefaultCategoryName();
		}

		return Emoji::decode($name);
	}

	protected function getDefaultCategoryName(): string
	{
		return Loc::getMessage('CALENDAR_OPEN_EVENTS_DEFAULT_CATEGORY_NAME');
	}

	public function prepareCategoryDescription(?string $description): string
	{
		if ($description === DefaultCategoryService::DEFAULT_CATEGORY_DESCRIPTION)
		{
			return Loc::getMessage('CALENDAR_OPEN_EVENTS_DEFAULT_CATEGORY_DESCRIPTION');
		}

		return Emoji::decode($description ?? '');
	}

	private function getDefaultRow(): OpenEventCategoryAttendee
	{
		$defaultCategory = DefaultCategoryService::getInstance()->getCategory();

		$row = new OpenEventCategoryAttendee();
		$row->entity->addField($this->getCategoryReference(),'CATEGORY');
		$row->set('CATEGORY_ID', $defaultCategory->getId());
		$row->set('CATEGORY', $defaultCategory);

		return $row;
	}

	private function getCategoryReference(): ReferenceField
	{
		return new ReferenceField(
			'CATEGORY',
			OpenEventCategoryTable::getEntity(),
			Join::on('this.CATEGORY_ID', 'ref.ID'),
		);
	}
}
