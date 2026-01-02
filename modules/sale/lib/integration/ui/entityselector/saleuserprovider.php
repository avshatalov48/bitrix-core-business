<?php

namespace Bitrix\Sale\Integration\UI\EntitySelector;

use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;

class SaleUserProvider extends BaseProvider
{
	protected const ENTITY_ID = 'sale-user';
	protected const LIMIT = 20;

	public function __construct(array $options = [])
	{
		parent::__construct();

		$this->options = $options;
	}

	public function isAvailable(): bool
	{
		global $USER;

		return (
			$USER->CanDoOperation('view_subordinate_users')
			|| $USER->CanDoOperation('view_all_users')
			|| $USER->CanDoOperation('edit_all_users')
			|| $USER->CanDoOperation('edit_subordinate_users')
		);
	}

	public function getItems(array $ids): array
	{
		$filter = !empty($ids) ? ['=ID' => $ids] : [];
		$items = [];
		foreach ($this->getUsers($filter) as $user)
		{
			$items[] = $this->makeItem($user);
		}

		return $items;
	}

	private static function formatUserName(array $user): string
	{
		return \CUser::formatName(
			\CSite::getNameFormat(false),
			[
				'NAME' => $user['NAME'],
				'LAST_NAME' => $user['LAST_NAME'],
				'SECOND_NAME' => $user['SECOND_NAME'],
				'LOGIN' => $user['LOGIN'],
				'EMAIL' => $user['EMAIL'],
				'TITLE' => $user['TITLE'],
			],
			true,
			false,
		);
	}

	private static function makeUserAvatar(array $user): ?string
	{
		if (empty($user['PERSONAL_PHOTO']))
		{
			return null;
		}

		$avatar = \CFile::resizeImageGet(
			$user['PERSONAL_PHOTO'],
			['width' => 100, 'height' => 100],
			BX_RESIZE_IMAGE_EXACT,
		);

		return !empty($avatar['src']) ? $avatar['src'] : null;
	}

	private function getUsers(array $filter = []): array
	{
		return \Bitrix\Main\UserTable::getList([
			'select' => [
				'ID', 'LAST_NAME', 'NAME', 'SECOND_NAME', 'LOGIN', 'EMAIL', 'TITLE',
				'PERSONAL_PHOTO',
			],
			'filter' => [
				'=ACTIVE' => 'Y',
				'=EXTERNAL_AUTH_ID' => ['shop', 'sale', 'imconnector'],
				...$filter,
			],
			'order' => ['ID' => 'ASC'],
			'limit' => self::LIMIT,
		])->fetchAll();
	}

	public function fillDialog(Dialog $dialog): void
	{
		foreach ($this->getUsers() as $user)
		{
			$dialog->addRecentItem(
				$this->makeItem($user)
			);
		}
	}

	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
		$filter = [];

		$query = $searchQuery->getQuery();
		if ($query !== '')
		{
			$filter = \Bitrix\Main\UserUtils::getAdminSearchFilter(['FIND' => $query]);
		}

		foreach ($this->getUsers($filter) as $user)
		{
			$dialog->addItem(
				$this->makeItem($user)
			);
		}

		if ($dialog->getItemCollection()->count() >= self::LIMIT)
		{
			$searchQuery->setCacheable(false);
		}
	}

	protected function getCustomData($user): array
	{
		$customData = [];
		$customDataFields = [
			'NAME' => 'name',
			'LAST_NAME' => 'lastName',
			'SECOND_NAME' => 'secondName',
			'EMAIL' => 'email',
			'LOGIN' => 'login',
		];

		foreach ($customDataFields as $snakeCase => $camelCase)
		{
			if (!empty($user[$snakeCase]))
			{
				$customData[$camelCase] = $user[$snakeCase];
			}
		}

		return $customData;
	}

	protected function makeItem(array $user): Item
	{
		return new Item([
			'id' => $user['ID'],
			'entityId' => static::ENTITY_ID,
			'title' => self::formatUserName($user),
			'avatar' => self::makeUserAvatar($user),
			'customData' => self::getCustomData($user),
		]);
	}
}
