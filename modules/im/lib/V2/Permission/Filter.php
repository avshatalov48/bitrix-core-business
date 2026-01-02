<?php

namespace Bitrix\Im\V2\Permission;

use Bitrix\Im\V2\Chat;
use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Query\Query;

/**
 * Modify query or parameters according to permissions
 */
class Filter
{
	public static function getRoleGetListFilter(array $ormParams, ActionGroup $action, string $relationTableAlias, string $chatTableAlias): array
	{
		$actionName = $action->value;
		if ($action === ActionGroup::ManageMessages)
		{
			$actionName = 'CAN_POST';
		}

		$ormParams['runtime'][] = self::getUserRoleExpressionField($relationTableAlias, $chatTableAlias);
		$ormParams['runtime'][] = self::getNeedRoleExpressionField($actionName, $chatTableAlias);
		$ormParams['runtime'][] = self::getHasAccessByRoleExpressionField();
		$ormParams['filter']['==HAS_ACCESS_BY_ROLE'] = true;

		return $ormParams;
	}

	public static function getRoleOrmFilter(Query $query, ActionGroup $action, string $relationTableAlias, string $chatTableAlias): void
	{
		$actionName = $action->value;
		if ($action === ActionGroup::ManageMessages)
		{
			$actionName = 'CAN_POST';
		}

		$query
			->registerRuntimeField('ROLE', self::getUserRoleExpressionField($relationTableAlias, $chatTableAlias))
			->registerRuntimeField('NEED_ROLE', self::getNeedRoleExpressionField($actionName, $chatTableAlias))
			->where(self::getHasAccessByRoleExpressionField(), 'expr', true)
		;
	}

	protected static function getNeedRoleExpressionField(string $action, string $chatTableAlias): ExpressionField
	{
		$noneRole = Chat::ROLE_NONE;
		$ownerRole = Chat::ROLE_OWNER;
		$managerRole = Chat::ROLE_MANAGER;
		$actionField = static::prepareFieldWithAlias($action, $chatTableAlias);

		return (new ExpressionField(
			'NEED_ROLE',
			"CASE
				WHEN %s = '{$noneRole}' THEN 10
				WHEN %s = '{$ownerRole}' THEN 2
				WHEN %s = '{$managerRole}' THEN 1
				WHEN %s IS NULL THEN -1
				ELSE 0
			END",
			[$actionField, $actionField, $actionField, $actionField]
		))->configureValueType(IntegerField::class);
	}

	protected static function getUserRoleExpressionField(string $relationTableAlias, string $chatTableAlias): ExpressionField
	{
		return (new ExpressionField(
			'ROLE',
			"CASE
				WHEN %s = %s THEN 2
				WHEN %s = 'Y' THEN 1
				WHEN %s IS NULL THEN -1
				ELSE 0
			END",
			[
				static::prepareFieldWithAlias('AUTHOR_ID', $chatTableAlias),
				static::prepareFieldWithAlias('USER_ID', $relationTableAlias),
				static::prepareFieldWithAlias('MANAGER', $relationTableAlias),
				static::prepareFieldWithAlias('ID', $relationTableAlias),
			]
		))->configureValueType(IntegerField::class);
	}

	protected static function prepareFieldWithAlias(string $fieldName, string $alias): string
	{
		if ($alias === '')
		{
			return $fieldName;
		}

		return "{$alias}.{$fieldName}";
	}

	protected static function getHasAccessByRoleExpressionField(): ExpressionField
	{
		return (new ExpressionField(
			'HAS_ACCESS_BY_ROLE',
			'%s >= %s',
			['ROLE', 'NEED_ROLE']
		))->configureValueType(BooleanField::class);
	}
}