<?php

namespace Bitrix\Main\UserField;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\UserFieldTable;

abstract class UserFieldAccess
{
	public const SETTINGS_USER_FIELD_KEY = 'userField';
	public const SETTINGS_ACCESS_CLASS_KEY = 'access';

	protected $userId;

	public function __construct(int $userId = null)
	{
		if($userId === null)
		{
			$userId = $this->getDefaultUserId();
		}

		$this->userId = $userId;
	}

	public static function getInstance(string $moduleId, int $userId = null): UserFieldAccess
	{
		$configuration = Configuration::getInstance($moduleId);

		$value = $configuration->get(static::SETTINGS_USER_FIELD_KEY);
		if(
			is_array($value)
			&& isset($value[static::SETTINGS_ACCESS_CLASS_KEY])
			&& Loader::includeModule($moduleId)
			&& is_a($value[static::SETTINGS_ACCESS_CLASS_KEY], self::class, true))
		{
			return new $value[static::SETTINGS_ACCESS_CLASS_KEY]($userId);
		}

		throw new ObjectNotFoundException('No settings for UserFieldAccess');
	}

	protected function getDefaultUserId(): int
	{
		global $USER;
		if($USER instanceof \CUser)
		{
			return (int) CurrentUser::get()->getId();
		}

		return 0;
	}

	public function setUserId(int $userId): UserFieldAccess
	{
		$this->userId = $userId;

		return $this;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	abstract protected function getAvailableEntityIds(): array;

	public function getRestrictedTypes(): array
	{
		return [
			'resourcebooking', // available in crm only
			'mail_message', // no way to edit
			'hlblock', // the field is not implemented yet
		];
	}

	public function canReadWithFilter(array $filter): bool
	{
		return is_array($this->prepareFilter($filter));
	}

	public function prepareFilter(array $filter = []): ?array
	{
		$filterEntityIds = [];
		foreach($filter as $name => $value)
		{
			if(mb_strpos($name, 'ENTITY_ID') !== false)
			{
				if($name === 'ENTITY_ID')
				{
					if(is_array($value))
					{
						$filterEntityIds = $value;
					}
					else
					{
						$filterEntityIds = [$value];
					}
				}

				unset($filter[$name]);
			}
		}
		$availableEntityIds = $this->getAvailableEntityIds();
		if(empty($filterEntityIds))
		{
			$filterEntityIds = $availableEntityIds;
		}
		else
		{
			foreach($filterEntityIds as $key => $entityId)
			{
				if(!in_array($entityId, $availableEntityIds, true))
				{
					unset($filterEntityIds[$key]);
				}
			}
		}

		if(empty($filterEntityIds))
		{
			return null;
		}

		$filter['ENTITY_ID'] = $filterEntityIds;

		return $filter;
	}

	public function canRead(int $id): bool
	{
		$filter = $this->prepareFilter([
			'=ID' => $id,
		]);

		if(empty($filter))
		{
			return false;
		}

		return (UserFieldTable::getCount($filter) > 0);
	}

	public function canAdd(array $field): bool
	{
		$availableEntityIds = $this->getAvailableEntityIds();

		return (isset($field['ENTITY_ID']) && in_array($field['ENTITY_ID'], $availableEntityIds, true));
	}

	public function canUpdate(int $id): bool
	{
		return $this->canRead($id);
	}

	public function canDelete(int $id): bool
	{
		return $this->canUpdate($id);
	}
}