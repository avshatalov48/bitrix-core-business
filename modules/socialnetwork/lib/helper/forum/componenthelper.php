<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2021 Bitrix
 */
namespace Bitrix\Socialnetwork\Helper\Forum;

use Bitrix\Forum\Permission;
use Bitrix\Main\Loader;

class ComponentHelper
{
	private static $cachedResult = [];

	public static function getForumPermission(array $params = []): ?string
	{
		global $USER;
		$currentUserId = $USER->getId();
		$isCurrentUserAdmin = \CSocNetUser::isCurrentUserModuleAdmin();
		$entityType = ($params['ENTITY_TYPE'] ?? SONET_ENTITY_USER);
		$entityId = (int)($params['ENTITY_ID'] ?? 0);

		$result = null;

		if (
			!Loader::includeModule('forum')
			|| !in_array($entityType, [ SONET_ENTITY_GROUP, SONET_ENTITY_USER ], true)
		)
		{
			return $result;
		}

		$result = Permission::ACCESS_DENIED;

		if (\CSocNetFeaturesPerms::canPerformOperation($currentUserId, $entityType, $entityId, 'forum', 'full', $isCurrentUserAdmin))
		{
			$result = Permission::FULL_ACCESS;
		}
		elseif (\CSocNetFeaturesPerms::CanPerformOperation($currentUserId, $entityType, $entityId, 'forum', 'newtopic', $isCurrentUserAdmin))
		{
			$result = Permission::CAN_ADD_TOPIC;
		}
		elseif (\CSocNetFeaturesPerms::CanPerformOperation($currentUserId, $entityType, $entityId, 'forum', 'answer', $isCurrentUserAdmin))
		{
			$result = Permission::CAN_ADD_MESSAGE;
		}
		elseif (\CSocNetFeaturesPerms::CanPerformOperation($currentUserId, $entityType, $entityId, 'forum', 'view', $isCurrentUserAdmin))
		{
			$result = Permission::CAN_READ;
		}

		return $result;
	}
}
