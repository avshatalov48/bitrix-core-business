<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2021 Bitrix
 */
namespace Bitrix\Socialnetwork\Helper;

use Bitrix\Main\Config\Option;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\FeatureTable;
use Bitrix\Socialnetwork\FeaturePermTable;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Socialnetwork\UserToGroupTable;

class Workgroup
{
	public static function getListSprintDuration(): array
	{
		$oneWeek = \DateInterval::createFromDateString('1 week')->format('%d') * 86400;
		$twoWeek = \DateInterval::createFromDateString('2 weeks')->format('%d') * 86400;
		$threeWeek = \DateInterval::createFromDateString('3 weeks')->format('%d') * 86400;
		$fourWeek = \DateInterval::createFromDateString('4 weeks')->format('%d') * 86400;

		return [
			$oneWeek => Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_TYPE_PROJECT_SCRUM_SPRINT_DURATION_ONE_WEEK'),
			$twoWeek => Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_TYPE_PROJECT_SCRUM_SPRINT_DURATION_TWO_WEEK'),
			$threeWeek => Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_TYPE_PROJECT_SCRUM_SPRINT_DURATION_THREE_WEEK'),
			$fourWeek => Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_TYPE_PROJECT_SCRUM_SPRINT_DURATION_FOUR_WEEK'),
		];
	}

	public static function getScrumTaskResponsibleList(): array
	{
		return [
			'A' => Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_TYPE_PROJECT_SCRUM_TASK_RESPONSIBLE_AUTHOR'),
			'M' => Loc::getMessage('SOCIALNETWORK_HELPER_WORKGROUP_TYPE_PROJECT_SCRUM_TASK_RESPONSIBLE_MASTER')
		];
	}

	public static function getTypeCodeByParams($params)
	{
		$result = false;

		if (empty($params['fields']))
		{
			return $result;
		}

		$typesList = (
			!empty($params['typesList'])
				? $params['typesList']
				: \Bitrix\Socialnetwork\Item\Workgroup::getTypes($params)
		);

		foreach ($typesList as $code => $type)
		{
			if (
				$params['fields']['OPENED'] === $type['OPENED']
				&& (
					isset($params['fields']['VISIBLE'])
					&& $params['fields']['VISIBLE'] === $type['VISIBLE']
				)
				&& $params['fields']['PROJECT'] === $type['PROJECT']
				&& $params['fields']['EXTERNAL'] === $type['EXTERNAL']
			)
			{
				$result = $code;
				break;
			}
		}

		return $result;
	}

	public static function getTypeByCode($params = [])
	{
		$result = false;

		if (
			!is_array($params)
			|| empty($params['code'])
		)
		{
			return $result;
		}

		$code = $params['code'];
		$typesList = (
			!empty($params['typesList'])
				? $params['typesList']
				: \Bitrix\Socialnetwork\Item\Workgroup::getTypes($params)
		);

		if (
			!empty($typesList)
			&& is_array($typesList)
			&& !empty($typesList[$code])
		)
		{
			$result = $typesList[$code];
		}

		return $result;
	}

	public static function getEditFeaturesAvailability()
	{
		static $result = null;

		if ($result !== null)
		{
			return $result;
		}

		$result = true;

		if (!ModuleManager::isModuleInstalled('bitrix24'))
		{
			return $result;
		}

		if (\CBitrix24::isNfrLicense())
		{
			return $result;
		}

		if (\CBitrix24::isDemoLicense())
		{
			return $result;
		}

		if (
			\CBitrix24::getLicenseType() !== 'project'
			|| !Option::get('socialnetwork', 'demo_edit_features', false)
		)
		{
			return $result;
		}

		$result = false;

		return $result;
	}

	/**
	 * returns array of workgroups filtered by access permissions of a user, only for the current site
	 * @param array $params
	 * @return array
	 */
	public static function getByFeatureOperation(array $params = []): array
	{
		global $USER;

		$result = [];

		$feature = ($params['feature'] ?? '');
		$operation = ($params['operation'] ?? '');
		$userId = (isset($params['userId']) ? (int)$params['userId'] : (is_object($USER) && $USER instanceof \CUser ? $USER->getId() : 0));

		if (
			(string)$feature === ''
			|| (string)$operation === ''
			|| $userId <= 0
		)
		{
			return $result;
		}

		$featuresSettings = \CSocNetAllowed::getAllowedFeatures();
		if (
			empty($featuresSettings)
			|| empty($featuresSettings[$feature])
			|| empty($featuresSettings[$feature]['allowed'])
			|| !in_array(FeatureTable::FEATURE_ENTITY_TYPE_GROUP, $featuresSettings[$feature]['allowed'], true)
			|| empty($featuresSettings[$feature]['operations'])
			|| empty($featuresSettings[$feature]['operations'][$operation])
			|| empty($featuresSettings[$feature]['operations'][$operation][FeatureTable::FEATURE_ENTITY_TYPE_GROUP])
		)
		{
			return $result;
		}

		$defaultRole = $featuresSettings[$feature]['operations'][$operation][FeatureTable::FEATURE_ENTITY_TYPE_GROUP];

		$query = new \Bitrix\Main\Entity\Query(WorkgroupTable::getEntity());
		$query->addFilter('=ACTIVE', 'Y');

		if (
			(
				!is_array($featuresSettings[$feature]['minoperation'])
				|| !in_array($operation, $featuresSettings[$feature]['minoperation'], true)
			)
			&& Option::get('socialnetwork', 'work_with_closed_groups', 'N') !== 'Y'
		)
		{
			$query->addFilter('!=CLOSED', 'Y');
		}

		$query->addSelect('ID');

		$query->registerRuntimeField(
			'',
			new \Bitrix\Main\Entity\ReferenceField('F',
				FeatureTable::getEntity(),
				[
					'=ref.ENTITY_TYPE' => new SqlExpression('?s', FeatureTable::FEATURE_ENTITY_TYPE_GROUP),
					'=ref.ENTITY_ID' => 'this.ID',
					'=ref.FEATURE' => new SqlExpression('?s', $feature),
				],
				[ 'join_type' => 'LEFT' ]
			)
		);
		$query->addSelect('F.ID', 'FEATURE_ID');

		$query->registerRuntimeField(
			'',
			new \Bitrix\Main\Entity\ReferenceField('FP',
				FeaturePermTable::getEntity(),
				[
					'=ref.FEATURE_ID' => 'this.FEATURE_ID',
					'=ref.OPERATION_ID' => new SqlExpression('?s', $operation),
				],
				[ 'join_type' => 'LEFT' ]
			)
		);

		$query->registerRuntimeField(new \Bitrix\Main\Entity\ExpressionField(
			'PERM_ROLE_CALCULATED',
			'CASE WHEN %s IS NULL THEN \''.$defaultRole.'\' ELSE %s END',
			[ 'FP.ROLE', 'FP.ROLE' ]
		));

		$query->registerRuntimeField(
			'',
			new \Bitrix\Main\Entity\ReferenceField('UG',
				UserToGroupTable::getEntity(),
				[
					'=ref.GROUP_ID' => 'this.ID',
					'=ref.USER_ID' => new SqlExpression($userId),
				],
				[ 'join_type' => 'LEFT' ]
			)
		);

		$query->registerRuntimeField(new \Bitrix\Main\Entity\ExpressionField(
			'HAS_ACCESS',
			'CASE 
				WHEN 
					(
						%s NOT IN (\''.FeaturePermTable::PERM_OWNER.'\', \''.FeaturePermTable::PERM_MODERATOR.'\', \''.FeaturePermTable::PERM_USER.'\')
						OR %s >= %s
					) THEN \'Y\' 
					ELSE \'N\' 
			END',
			[
				'PERM_ROLE_CALCULATED',
				'PERM_ROLE_CALCULATED', 'UG.ROLE',
			]
		));

		$query->addFilter('=HAS_ACCESS', 'Y');

		$res = $query->exec();

		while ($row = $res->fetch())
		{
			$result[] = [
				'ID' => (int) $row['ID']
			];
		}

		return $result;
	}

	public static function checkAnyOpened(array $idList = []): bool
	{
		if (
			empty($idList)
		)
		{
			return false;
		}

		$res = WorkgroupTable::getList([
			'filter' => [
				'@ID' => $idList,
				'=OPENED' => 'Y',
			],
			'select' => [ 'ID' ],
			'limit' => 1,
		]);
		if ($res->fetch())
		{
			return true;
		}

		return false;
	}

	public static function getPermissions(array $params = []): array
	{
		global $USER, $APPLICATION;

		$userId = (int)($params['userId'] ?? (is_object($USER) ? $USER->getId() : 0));
		$groupId = (int)($params['groupId'] ?? 0);
		if ($groupId <= 0)
		{
			$APPLICATION->throwException('Empty workgroup Id', 'SONET_HELPER_WORKGROUP_EMPTY_GROUP');
		}

		$groupFields = \Bitrix\Socialnetwork\Item\Workgroup::getById($groupId)->getFields();
		return \CSocNetUserToGroup::initUserPerms($userId, $groupFields, \CSocNetUser::isCurrentUserModuleAdmin());
	}

	public static function isGroupCopyFeatureEnabled(): bool
	{
		return
			!ModuleManager::isModuleInstalled('bitrix24')
			|| (
				Loader::includeModule('bitrix24')
				&& \Bitrix\Bitrix24\Feature::isFeatureEnabled('socnet_group_copy')
			)
		;
	}
}
