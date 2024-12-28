<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Registry;

use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Provider\CollabOptionProvider;
use Bitrix\Socialnetwork\Internals\Registry\GroupRegistry;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\Provider\FeatureProvider;

class CollabRegistry extends GroupRegistry
{
	private static ?self $instance = null;

	public static function getInstance(): static
	{
		if (static::$instance === null)
		{
			static::$instance = new static();
		}

		return static::$instance;
	}

	public function get(int $groupId): ?Collab
	{
		$group = parent::get($groupId);
		if ($group instanceof Collab)
		{
			return $group;
		}

		return null;
	}

	protected function loadData(int $groupId): array
	{
		$fields = parent::loadData($groupId);

		if (empty($fields))
		{
			return $fields;
		}

		$this->loadAdditionalData($fields);

		return $fields;
	}

	protected function onObjectAlreadyLoaded(?Workgroup $group): void
	{
		if (!$group instanceof Collab)
		{
			return;
		}

		$fields = $group->getFields();

		$this->loadAdditionalData($fields);

		$group->setFields($fields);
	}

	protected function loadAdditionalData(array &$fields): void
	{
		if (!array_key_exists('OPTIONS', $fields))
		{
			$this->fillOptions($fields);
		}

		if (!array_key_exists('PERMISSIONS', $fields))
		{
			$this->fillPermissions($fields);
		}
	}

	private function fillOptions(array &$fields): void
	{
		$options = CollabOptionProvider::getInstance()->get((int)$fields['ID']);
		if (!empty($options))
		{
			$fields['OPTIONS'] = $options;
		}
	}

	private function fillPermissions(array &$fields): void
	{
		$permissions = FeatureProvider::getInstance()->getPermissions((int)$fields['ID']);
		if (!empty($permissions))
		{
			$fields['PERMISSIONS'] = $permissions;
		}
	}
}