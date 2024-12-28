<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Provider;

use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Collab\Permission\UserRole;
use Bitrix\Socialnetwork\Collab\Property\Feature;
use Bitrix\Socialnetwork\Collab\Property\Permission;
use Bitrix\Socialnetwork\FeatureTable;
use Bitrix\Socialnetwork\Helper\InstanceTrait;

class FeatureProvider
{
	use InstanceTrait;

	protected const CACHE_TTL = 10;

	protected const DEFAULT_PERMISSIONS = [
		'calendar' => [],
		'chat' => [],
		'files' => [],
		'tasks' => [
			'delete_tasks' => UserRole::MODERATOR,
			'edit_tasks' => UserRole::MODERATOR,
		],
	];

	/** @return Feature[] */
	public function getFeatures(int $collabId): array
	{
		$collabFeatures = FeatureTable::query()
			->setSelect(['ID', 'FEATURE', 'ACTIVE'])
			->where('ENTITY_ID', $collabId)
			->where('ENTITY_TYPE', FeatureTable::FEATURE_ENTITY_TYPE_GROUP)
			->setCacheTtl(static::CACHE_TTL)
			->exec()
			->fetchCollection();

		$result = [];
		foreach ($collabFeatures as $collabFeature)
		{
			$featureName = $collabFeature->getFeature();

			$result [$featureName]= new Feature(
				$collabFeature->getId(),
				$featureName,
				$collabFeature->getActive() === 'Y'
			);
		}

		return $result;
	}

	/** @return Permission[] */
	public function getPermissions(int $collabId): array
	{
		$collabFeaturePermissions = FeatureTable::query()
			->setSelect(['ID', 'FEATURE', 'PERMISSIONS.OPERATION_ID', 'PERMISSIONS.ROLE'])
			->where('ENTITY_ID', $collabId)
			->where('ENTITY_TYPE', FeatureTable::FEATURE_ENTITY_TYPE_GROUP)
			->where('ACTIVE', 'Y')
			->setCacheTtl(static::CACHE_TTL)
			->exec()
			->fetchCollection();

		$permissions = [];

		foreach ($collabFeaturePermissions as $collabPermission)
		{
			$featurePermissions = [];

			$collabFeaturePermissions = $collabPermission->getPermissions();
			foreach ($collabFeaturePermissions as $collabFeaturePermission)
			{
				$featurePermissions = array_merge(
					$featurePermissions,
					[$collabFeaturePermission->getOperationId() => $collabFeaturePermission->getRole()]
				);
			}

			if (empty($featurePermissions))
			{
				$featurePermissions = $this->getDefaultPermissions($collabPermission->getFeature());
			}

			$permissions[] = new Permission($collabPermission->getFeature(), $featurePermissions);
		}

		return $permissions;
	}

	public function getAllDefaultPermissions(): array
	{
		return static::DEFAULT_PERMISSIONS;
	}

	public function getDefaultPermissions(string $featureId): array
	{
		return static::DEFAULT_PERMISSIONS[$featureId] ?? [];
	}

	public function getPermissionLabels(): array
	{
		return [
			UserRole::OWNER => Loc::getMessage('SOCIALNETWORK_FEATURE_LABEL_OWNER'),
			UserRole::MODERATOR => Loc::getMessage('SOCIALNETWORK_FEATURE_LABEL_MODERATOR'),
			UserRole::MEMBER => Loc::getMessage('SOCIALNETWORK_FEATURE_LABEL_USER'),
		];
	}

	public function getRightsPermissionLabels(): array
	{
		return [
			UserRole::OWNER => Loc::getMessage('SOCIALNETWORK_FEATURE_LABEL_OWNER'),
			UserRole::MODERATOR => Loc::getMessage('SOCIALNETWORK_FEATURE_LABEL_MODERATOR'),
			UserRole::EMPLOYEE => Loc::getMessage('SOCIALNETWORK_FEATURE_LABEL_EMPLOYEE'),
			UserRole::MEMBER => Loc::getMessage('SOCIALNETWORK_FEATURE_LABEL_USER'),
		];
	}

	public function getOptionLabels(): array
	{
		return [
			'Y' => Loc::getMessage('SOCIALNETWORK_FEATURE_LABEL_YES'),
			'N' => Loc::getMessage('SOCIALNETWORK_FEATURE_LABEL_NO'),
		];
	}
}