<?php

namespace Bitrix\UI\AccessRights\V2;

use Bitrix\Main\Access\AccessCode;
use Bitrix\Main\Application;
use Bitrix\Main\UI\AccessRights\DataProvider;
use Bitrix\Main\UI\AccessRights\Exception\UnknownEntityTypeException;
use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider;
use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Models\RightId;
use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Models\RightModel;
use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure\Entity;
use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure\Permission;
use Bitrix\UI\AccessRights\V2\Dto\Controller\AccessRightDto;
use Bitrix\UI\AccessRights\V2\Options\RightSection;
use Bitrix\UI\AccessRights\V2\Options\RightSection\RightItem;
use Bitrix\UI\AccessRights\V2\Options\UserGroup\Member;

final class AccessRightsBuilder
{
	public function __construct(
		private readonly Provider $provider,
		private readonly DataProvider $dataProvider = new DataProvider(),
	)
	{
	}

	/**
	 * @return RightSection[]
	 */
	public function buildAccessRights(): array
	{
		$sections = [];
		foreach ($this->provider->loadEntities() as $entity)
		{
			$section = new RightSection($entity->getTitle());
			foreach ($entity->getPermissions() as $permission)
			{
				$uiId = $this->provider->getRightIdConverter()->buildUIId($entity, $permission);
				$control = $permission->getControl();
				$action = $permission->getAction();

				$rightItem = new RightItem($uiId, $action->getTitle(), $control->getType());

				if ($control instanceof Provider\Structure\Configurator\RightItemConfigurator)
				{
					$control->configureRightItem($rightItem);
				}

				if ($action instanceof Provider\Structure\Configurator\RightItemConfigurator)
				{
					$action->configureRightItem($rightItem);
				}

				$section->addRight($rightItem);
			}

			if ($entity instanceof Provider\Structure\Configurator\RightSectionConfigurator)
			{
				$entity->configureRightSection($section);
			}

			$sections[] = $section;
		}

		return $sections;
	}

	/**
	 * @return Options\UserGroup[]
	 */
	public function buildUserGroups(): array
	{
		$optionUserGroups = [];

		$userGroupModels = $this->provider->loadUserGroupModels();

		foreach ($userGroupModels as $userGroup)
		{
			$optionUserGroup = new Options\UserGroup($userGroup->id(), $userGroup->name());

			foreach ($userGroup->accessCodes() as $accessCode)
			{
				if (!AccessCode::isValid($accessCode))
				{
					continue;
				}

				$parsedAccessCode = new AccessCode($accessCode);
				try {
					$accessRightEntity = $this->dataProvider
						->getEntity(
							$parsedAccessCode->getEntityType(),
							$parsedAccessCode->getEntityId(),
						)
					;
				}
				catch (UnknownEntityTypeException $e)
				{
					Application::getInstance()->getExceptionHandler()->writeToLog($e);

					continue;
				}

				$optionUserGroup->addMember($accessCode, Member::fromAccessRightEntity($accessRightEntity));
			}

			foreach ($userGroup->accessRightModels() as $accessRightModel)
			{
				$rightId = $this->provider->getRightIdConverter()->parseRightModel($accessRightModel);
				if ($rightId === null)
				{
					continue;
				}

				$result = $this->findEntityAndPermissionByRightId($rightId);
				if ($result === null)
				{
					continue;
				}

				/** @var $entity Entity */
				/** @var $permission Permission */
				[$entity, $permission] = $result;

				$uiId = $this->provider->getRightIdConverter()->buildUIId($entity, $permission);

				$access = new Member\Access(
					id: $uiId,
					value: $permission->getControl()->convertModelValueToUIValue($accessRightModel),
				);

				$optionUserGroup->addAccessRight($access);
			}

			$optionUserGroups[] = $optionUserGroup;
		}

		return $optionUserGroups;
	}

	/**
	 * @param AccessRightDto $accessCode
	 * @return RightModel|null
	 */
	public function decodeAccessCode(AccessRightDto $accessCode): ?RightModel
	{
		$rightId = $this->provider->getRightIdConverter()->parseUIId($accessCode->id);
		if ($rightId === null)
		{
			return null;
		}

		$result = $this->findEntityAndPermissionByRightId($rightId);
		if ($result === null)
		{
			return null;
		}

		/** @var $permission Permission */
		[, $permission] = $result;
		$value = $permission->getControl()->convertUIValueToModelValue($accessCode->value);

		return $this->provider->createRightModelByRightId($rightId, $value);
	}

	/**
	 * @param RightId $rightId
	 * @return array{Entity, Permission}|null
	 */
	private function findEntityAndPermissionByRightId(RightId $rightId): ?array
	{
		foreach ($this->provider->loadEntities() as $entity)
		{
			if (!$rightId->isEntityEquals($entity))
			{
				continue;
			}

			foreach ($entity->getPermissions() as $permission)
			{
				if (!$rightId->isPermissionEquals($permission))
				{
					continue;
				}

				return [
					$entity,
					$permission,
				];
			}
		}

		return null;
	}
}
