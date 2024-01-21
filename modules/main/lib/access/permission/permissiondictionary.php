<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Access\Permission;

use Bitrix\Main\Localization\Loc;

abstract class PermissionDictionary
	implements AccessiblePermissionDictionary
{
	public const
		VALUE_NO = 0,
		VALUE_YES = 1;

	public const DELIMITER = '.';

	public const TYPE_TOGGLER = 'toggler';
	public const TYPE_VARIABLES = 'variables';
	public const TYPE_MULTIVARIABLES = 'multivariables';

	public const HINT_PREFIX = 'HINT_';

	protected static $locLoaded = [];

	/**
	 * @param $permissionId
	 * @return string
	 *
	 * @ToDo type can be not only the toggler
	 */
	public static function getType($permissionId): string
	{
		return static::TYPE_TOGGLER;
	}

	/**
	 * @param $permissionId
	 * @return array [
	 * 		'id' 	=>
	 * 		'type' 	=>
	 * 		'title' =>
	 * 		'hint' 	=>
	 * ]
	 */
	public static function getPermission($permissionId): array
	{
		$permission = [
			'id' 	=> $permissionId,
			'type' 	=> static::TYPE_TOGGLER,
			'title' => '',
			'hint' 	=> ''
		];

		static::loadLoc();
		$name = self::getName($permissionId);
		if (!$name)
		{
			return $permission;
		}
		$permission['title'] = Loc::getMessage($name) ?? '';
		$permission['hint'] = Loc::getMessage(self::HINT_PREFIX . $name) ?? '';

		return $permission;
	}

	public static function getTitle($permissionId): string
	{
		static::loadLoc();
		$name = self::getName($permissionId);
		if (!$name)
		{
			return '';
		}
		return Loc::getMessage($name) ?? '';
	}

	public static function getList(): array
	{
		$class = new \ReflectionClass(static::class);
		$permissions = $class->getConstants();

		$res = [];
		foreach ($permissions as $name => $id)
		{
			if (in_array($name, [
				'VALUE_NO',
				'VALUE_YES',
				'DELIMITER',
				'TYPE_VARIABLES',
				'TYPE_MULTIVARIABLES',
				'TYPE_TOGGLER',
				'HINT_PREFIX'
			]))
			{
				continue;
			}

			$res[$id] = [
				'NAME' => $name,
				'LEVEL' => static::getLevel($id)
			];
		}
		return $res;
	}

	public static function getParentsPath(string $permissionId): ?string
	{
		$nodes = explode(static::DELIMITER, $permissionId);
		$count = count($nodes);
		if ($count < 2)
		{
			return null;
		}
		unset($nodes[$count-1]);
		return implode(static::DELIMITER, $nodes);
	}

	public static function recursiveValidatePermission(array $permissions, $id)
	{
		if (!array_key_exists($id, $permissions))
		{
			return false;
		}

		if ($permissions[$id] == static::VALUE_NO)
		{
			return true;
		}

		$parentPath = static::getParentsPath($id);
		if (!$parentPath)
		{
			return true;
		}

		if (!array_key_exists($parentPath, $permissions))
		{
			return false;
		}

		if ($permissions[$parentPath] == static::VALUE_NO)
		{
			return false;
		}

		return static::recursiveValidatePermission($permissions, $parentPath);
	}

	protected static function getLevel($id): int
	{
		$value = explode(static::DELIMITER, $id);
		return count($value) - 1;
	}

	protected static function loadLoc()
	{
		if (!isset(static::$locLoaded[static::class]))
		{
			$r = new \ReflectionClass(static::class);
			Loc::loadMessages($r->getFileName());
			static::$locLoaded[static::class] = true;
		}
	}

	protected static function getName($permissionId): ?string
	{
		$permissions = static::getList();
		if (!array_key_exists($permissionId, $permissions))
		{
			return null;
		}
		return $permissions[$permissionId]['NAME'];
	}
}