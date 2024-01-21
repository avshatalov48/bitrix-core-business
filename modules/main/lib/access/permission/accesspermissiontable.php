<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Access\Permission;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ORM\Data\Result;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\Access\Entity\DataManager;

Loc::loadMessages(__FILE__);

abstract class AccessPermissionTable extends DataManager
{
	public static function getMap()
	{
		return [
			new Entity\IntegerField('ID', [
				'autocomplete' => true,
				'primary' => true
			]),
			new Entity\IntegerField('ROLE_ID', [
				'required' => true
			]),
			new Entity\StringField('PERMISSION_ID', [
				'required' => true
			]),
			new Entity\IntegerField('VALUE', [
				'required' => true
			])
		];
	}

	public static function checkFields(Result $result, $primary, array $data)
	{
		parent::checkFields($result, $primary, $data);

		if (!$result->isSuccess(true))
		{
			return;
		}

		if (!static::checkDataFields($data))
		{
			if (empty($primary))
			{
				$result->addError(new Entity\EntityError(Loc::getMessage('ACCESS_PERMISSION_PARENT_VALIDATE_ERROR')));
				return;
			}
			$data = static::loadUpdateRow($primary, $data);
		}

		if (!static::validateRow($data))
		{
			$result->addError(new Entity\EntityError(Loc::getMessage('ACCESS_PERMISSION_PARENT_VALIDATE_ERROR')));
		}
	}

	public static function addMulti($rows, $ignoreEvents = false)
	{
		throw new NotSupportedException();
	}

	public static function updateMulti($primaries, $data, $ignoreEvents = false)
	{
		throw new NotSupportedException();
	}

	public static function onAfterAdd(Event $event)
	{
		$primary = $event->getParameter("primary");
		$data = $event->getParameter("fields");

		self::updateChildPermission($primary, $data);

		parent::onAfterAdd($event);
	}

	public static function onAfterUpdate(Event $event)
	{
		$primary = $event->getParameter("primary");
		$data = $event->getParameter("fields");

		self::updateChildPermission($primary, $data);

		parent::onAfterUpdate($event);
	}

	protected static function updateChildPermission($primary, array $data)
	{
		$connection = static::getEntity()->getConnection();
		$helper = $connection->getSqlHelper();

		$data = static::loadUpdateRow($primary, $data);
		if ((int) $data['VALUE'] === PermissionDictionary::VALUE_YES)
		{
			return;
		}
		$sql = "
			UPDATE ". $helper->quote(static::getTableName()) ."
			SET VALUE = ". PermissionDictionary::VALUE_NO ."
			WHERE 
				ROLE_ID = ". $data['ROLE_ID'] ."
				AND PERMISSION_ID LIKE '". $data['PERMISSION_ID'] .".%' 
		";
		$connection->query($sql);
	}

	protected static function loadUpdateRow($primary, array $data)
	{
		if (!static::checkDataFields($data))
		{
			$row = static::getRowById($primary);
			foreach ($row as $k => $v)
			{
				if (!array_key_exists($k, $data))
				{
					$data[$k] = $v;
				}
			}
		}
		return $data;
	}

	protected static function validateRow(array $data): bool
	{
		if ((int) $data['VALUE'] === PermissionDictionary::VALUE_NO)
		{
			return true;
		}

		$parentPermissions = PermissionDictionary::getParentsPath($data['PERMISSION_ID']);
		if (!$parentPermissions)
		{
			return true;
		}

		$res = static::getList([
			'select' => ['VALUE'],
			'filter' => [
				'=ROLE_ID' 			=> (int) $data['ROLE_ID'],
				'%=PERMISSION_ID' 	=> $parentPermissions,
				'=VALUE' 			=> PermissionDictionary::VALUE_NO
			],
			'limit' => 1
		])->fetchAll();

		if (is_array($res) && !empty($res))
		{
			return false;
		}

		return true;
	}

	protected static function checkDataFields(array $data)
	{
		$fields = static::getMap();
		foreach ($fields as $field)
		{
			if (!$field->hasParameter('required'))
			{
				continue;
			}
			if (
				!array_key_exists($field->getName(), $data)
				|| !$data[$field->getName()]
			)
			{
				return false;
			}
		}

		return true;
	}
}