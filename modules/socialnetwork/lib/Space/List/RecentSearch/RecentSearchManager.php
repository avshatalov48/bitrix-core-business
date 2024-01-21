<?php
namespace Bitrix\Socialnetwork\Space\List\RecentSearch;

use Bitrix\Main\ObjectException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UI\EntitySelector\EntityUsageTable;

final class RecentSearchManager
{
	private const CONTEXT = 'SOCIALNETWORK_SPACE_LIST';
	private const ENTITY_ID = 'socnet-space';
	private const LOAD_LIMIT = 40;

	public function __construct(private int $userId)
	{}

	public function addSpaceToRecentSearch(int $spaceId): void
	{
		EntityUsageTable::merge([
			'USER_ID' => $this->userId,
			'CONTEXT' => self::CONTEXT,
			'ENTITY_ID' => self::ENTITY_ID,
			'ITEM_ID' => $spaceId,
		]);
	}

	public function getRecentlySearchedSpacesData(): SpaceSearchDataCollection
	{
		$result = new SpaceSearchDataCollection();

		$queryResult = EntityUsageTable::query()
			->setSelect(['ITEM_ID', 'LAST_USE_DATE'])
			->where('USER_ID', $this->userId)
			->where('CONTEXT', self::CONTEXT)
			->where('ENTITY_ID', self::ENTITY_ID)
			->addOrder('LAST_USE_DATE', 'DESC')
			->setLimit(self::LOAD_LIMIT)
			->setDistinct()
			->fetchAll()
		;

		foreach ($queryResult as $value)
		{
			try
			{
				$result->add(new SpaceSearchData((int)$value['ITEM_ID'], new DateTime($value['LAST_USE_DATE'])));
			}
			catch (ObjectException $exception) {}
		}

		return $result;
	}
}
