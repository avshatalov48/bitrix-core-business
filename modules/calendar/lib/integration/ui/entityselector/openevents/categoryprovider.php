<?php

namespace Bitrix\Calendar\Integration\UI\EntitySelector\OpenEvents;

use Bitrix\Calendar\OpenEvents\Item\Category;
use Bitrix\Calendar\OpenEvents\Provider;
use Bitrix\Calendar\OpenEvents\Provider\Category\Enum\CategoryOrderEnum;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;

final class CategoryProvider extends BaseProvider
{
	public const ENTITY_ID = 'event-category';

	protected int $userId;

	public function __construct()
	{
		parent::__construct();

		$this->userId = (int)CurrentUser::get()->getId();
	}

	public function isAvailable(): bool
	{
		return $this->userId > 0;
	}

	public function getItems(array $ids): array
	{
		return $this->getCategories();
	}

	public function fillDialog(Dialog $dialog): void
	{
		$dialog->addItems($this->getCategories());
	}

	public function getCategories(): array
	{
		$categoryQuery = new Provider\Category\Query(
			order: CategoryOrderEnum::BY_NAME,
			requireDefault: true,
		);

		$categories = (new Provider\CategoryProvider($this->userId))->list($categoryQuery);

		return $this->getItemsFromCategories($categories);
	}

	public function getItemsFromCategories(array $categories): array
	{
		return array_map(static fn (Category $it) => self::makeItem($it), $categories);
	}

	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
		$filter = new Provider\Category\Filter(
			query: $searchQuery->getQuery(),
		);

		$categoryQuery = new Provider\Category\Query(
			filter: $filter,
			order: CategoryOrderEnum::BY_NAME,
		);

		$categories = (new Provider\CategoryProvider($this->userId))->list($categoryQuery);

		foreach ($categories as $category)
		{
			$dialog->addItem(self::makeItem($category));
		}
	}

	protected static function makeItem(Category $category): Item
	{
		return new Item([
			'id' => $category->id,
			'entityId' => self::ENTITY_ID,
			'title' => $category->name,
			'tabs' => 'recents',
		]);
	}
}
