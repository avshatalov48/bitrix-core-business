<?php

namespace Bitrix\Socialnetwork\Integration\UI\EntitySelector;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;
use Bitrix\Socialnetwork\Helper\Workgroup;
use Bitrix\Socialnetwork\UserToGroupTable;

class ProjectUserProvider extends UserProvider
{
	protected function prepareOptions(array $options = []): void
	{
		parent::prepareOptions($options);
		$this->options['projectId'] = (int)($options['projectId'] ?? null);
	}

	/**
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	protected static function getQuery(array $options = []): Query
	{
		$projectId = $options['projectId'];
		if ($projectId <= 0 || !static::isMember($projectId))
		{
			return static::getEmptyQuery();
		}

		return parent::getQuery($options)->registerRuntimeField(
			'GROUP_MEMBERS',
			new Reference(
				'USER_TO_GROUP',
				UserToGroupTable::getEntity(),
				Join::on('this.ID', 'ref.USER_ID')
					->where('ref.GROUP_ID', $projectId)
					->whereIn('ref.ROLE', UserToGroupTable::getRolesMember()),
				['join_type' => Join::TYPE_INNER]
			)
		);
	}

	protected static function getExtranetUsersQuery(int $currentUserId): ?Query
	{
		// return null because static::getQuery() adds the necessary conditions
		return null;
	}

	private static function isMember(int $projectId): bool
	{
		$permissions = Workgroup::getPermissions([
			'groupId' => $projectId,
			'userId' => static::getCurrentUserId(),
		]);

		return $permissions['UserIsMember'] ?? false;
	}

	/**
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	private static function getEmptyQuery(): Query
	{
		return UserTable::query()->setSelect(['ID'])->where('ID', 0);
	}
}
