<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Handler\Update;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\SocialNetwork\Collab\Analytics\CollabAnalytics;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabUpdateCommand;
use Bitrix\Socialnetwork\Collab\Log\CollabLogEntryCollection;
use Bitrix\Socialnetwork\Collab\Log\Entry\UpdateCollabLogEntry;
use Bitrix\Socialnetwork\Collab\Property\Permission;
use Bitrix\Socialnetwork\Control\Command\UpdateCommand;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\Provider\FeatureProvider;
use CSocNetFeaturesPerms;

class UpdatePermissionsHandler implements UpdateHandlerInterface
{
	public function update(UpdateCommand $command, Workgroup $entityBefore, Workgroup $entityAfter): HandlerResult
	{
		$handlerResult = new HandlerResult();

		$permissions = $command->getPermissions()?->getValue() ?? [];
		if (empty($permissions))
		{
			return $handlerResult;
		}

		$handlerResult->setGroupChanged();

		$currentFeatures = FeatureProvider::getInstance()->getFeatures($command->getId());
		foreach ($permissions as $featureName => $operations)
		{
			$featureId = $currentFeatures[$featureName]?->id ?? 0;
			if ($featureId <= 0)
			{
				continue;
			}

			foreach ($operations as $operationName => $operationValue)
			{
				$operationId = CSocNetFeaturesPerms::SetPerm(
					$featureId,
					$operationName,
					$operationValue
				);

				if (!$operationId)
				{
					$handlerResult->addApplicationError();
				}
			}
		}

		return $handlerResult->merge($this->writePermissionsChangesToLog($command, $entityBefore));
	}

	private function writePermissionsChangesToLog(UpdateCommand $command, Workgroup $entityBefore): HandlerResult
	{
		$handlerResult = new HandlerResult();

		if (!($command instanceof CollabUpdateCommand) || !($entityBefore instanceof Collab))
		{
			return $handlerResult;
		}

		$permissions = $command->getPermissions()?->getValue() ?? null;

		if (empty($permissions))
		{
			return $handlerResult;
		}

		$analytics = CollabAnalytics::getInstance();

		$logEntryCollection = new CollabLogEntryCollection();
		$previousPermissions = $entityBefore->getPermissions();

		foreach ($permissions as $featureName => $operations)
		{
			$previousPermission = $this->getPermissionByFeature($previousPermissions, $featureName);

			if (!$previousPermission)
			{
				continue;
			}

			$previousPermission = $previousPermission->toArray();

			foreach ($operations as $operationName => $operationValue)
			{
				$previousValue = $previousPermission[$featureName][$operationName] ?? null;

				if ($previousValue === $operationValue)
				{
					continue;
				}

				$logEntry = new UpdateCollabLogEntry(
					userId: $command->getInitiatorId(),
					collabId: $command->getId(),
				);

				$fieldName = UpdateCollabLogEntry::PERMISSION_FIELD_PREFIX . '_' . $featureName . '_' . $operationName;

				$logEntry
					->setFieldName($fieldName)
					->setPreviousValue($previousValue)
					->setCurrentValue($operationValue)
				;

				$logEntryCollection->add($logEntry);

				$analytics->onSettingsChanged($command->getInitiatorId(), $command->getId(), $operationName);
			}
		}

		if ($logEntryCollection->isEmpty())
		{
			return $handlerResult;
		}

		$service = ServiceLocator::getInstance()->get('socialnetwork.collab.log.service');
		$service->saveCollection($logEntryCollection);

		return $handlerResult;
	}

	private function getPermissionByFeature(array $permissions, string $name): ?Permission
	{
		foreach ($permissions as $permission)
		{
			if (isset($permission?->toArray()[$name]))
			{
				return $permission;
			}
		}

		return null;
	}
}
