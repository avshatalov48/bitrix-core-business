<?php
namespace Bitrix\Im;

use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type\DateTime;

class Alias
{
	const ENTITY_TYPE_USER = 'USER';
	const ENTITY_TYPE_CHAT = 'CHAT';
	const ENTITY_TYPE_OPEN_LINE = 'LINES';
	const ENTITY_TYPE_LIVECHAT = 'LIVECHAT';
	const ENTITY_TYPE_VIDEOCONF = 'VIDEOCONF';
	const ENTITY_TYPE_JITSICONF = 'JITSICONF';
	const ENTITY_TYPE_OTHER = 'OTHER';

	const CACHE_TTL = 31536000;
	const CACHE_PATH = '/bx/im/alias/';

	const FILTER_BY_ALIAS = 'alias';
	const FILTER_BY_ID = 'id';

	public static function add(array $fields)
	{
		$alias = self::prepareAlias($fields['ALIAS']);
		$entityType = $fields['ENTITY_TYPE'];
		$entityId = $fields['ENTITY_ID'];

		if (
			($fields['ENTITY_TYPE'] !== self::ENTITY_TYPE_VIDEOCONF && empty($entityId))
			|| empty($entityType)
			|| empty($alias))
		{
			return false;
		}

		$aliasData = self::get($alias);
		if ($aliasData)
			return false;

		$result = \Bitrix\Im\Model\AliasTable::add(Array(
			'ALIAS' => $alias,
			'ENTITY_TYPE' => $entityType,
			'ENTITY_ID' => $entityId,
			'DATE_CREATE' => new DateTime()
		));
		if (!$result->isSuccess())
		{
			return false;
		}

		return $result->getId();
	}

	public static function addUnique(array $fields)
	{
		$alias = \Bitrix\Im\Alias::prepareAlias(self::generateUnique());
		$fields['ALIAS'] = $alias;

		$id = self::add($fields);
		if (!$id)
		{
			return self::addUnique($fields);
		}

		return Array(
			'ID' => $id,
			'ALIAS' => $alias,
			'LINK' => self::getPublicLink($fields['ENTITY_TYPE'], $alias)
		);
	}

	public static function update($id, $fields)
	{
		$id = intval($id);
		if ($id <= 0)
			return false;

		$update = Array();
		if (isset($fields['ALIAS']))
		{
			$update['ALIAS'] = self::prepareAlias($fields['ALIAS']);
			$result = self::get($update['ALIAS']);
			if ($result)
			{
				return false;
			}
		}

		if (isset($fields['ENTITY_TYPE']))
		{
			$update['ENTITY_TYPE'] = $fields['ENTITY_TYPE'];
		}
		if (isset($fields['ENTITY_ID']))
		{
			$update['ENTITY_ID'] = $fields['ENTITY_ID'];
		}

		if (empty($update))
			return false;

		\Bitrix\Im\Model\AliasTable::update($id, $update);

		return true;
	}

	public static function delete($id, $filter = self::FILTER_BY_ID)
	{
		if ($filter == self::FILTER_BY_ALIAS)
		{
			$aliasData = self::get($id);
			if (!$aliasData)
				return false;
		}
		else
		{
			$aliasData['ID'] = intval($id);
		}

		\Bitrix\Im\Model\AliasTable::delete($aliasData['ID']);

		return true;
	}

	public static function get($alias)
	{
		$alias = self::prepareAlias($alias);
		if (empty($alias))
		{
			return false;
		}

		$query = \Bitrix\Im\Model\AliasTable::query();

		$connection = \Bitrix\Main\Application::getConnection();
		if ($connection instanceof \Bitrix\Main\DB\PgsqlConnection)
		{
			$alias = $connection->getSqlHelper()->forSql($alias);
			$query
				->setSelect(['*'])
				->whereExpr("LOWER(%s) = LOWER('{$alias}')", ['ALIAS'])
			;
		}
		else
		{
			$query
				->setSelect(['*'])
				->where('ALIAS', $alias)
			;
		}

		$result = $query->exec()->fetch();

		if (!$result)
		{
			return false;
		}

		$result['LINK'] = self::getPublicLink($result['ENTITY_TYPE'], $result['ALIAS']);

		return $result;
	}

	public static function getByIdAndCode($id, $code)
	{
		$query = \Bitrix\Im\Model\AliasTable::query();
		$query
			->setSelect(['*'])
			->where('ID', $id)
		;

		$connection = \Bitrix\Main\Application::getConnection();
		if ($connection instanceof \Bitrix\Main\DB\PgsqlConnection)
		{
			$code = $connection->getSqlHelper()->forSql($code);
			$query->whereExpr("LOWER(%s) = LOWER('{$code}')", ['ALIAS']);
		}
		else
		{
			$query->where('ALIAS', $code);
		}

		return $query->exec()->fetch();
	}

	public static function getByEntity($entityType, $entityId)
	{
		$result = \Bitrix\Im\Model\AliasTable::getList(Array(
			'filter' => ['=ENTITY_TYPE' => $entityType, '=ENTITY_ID' => $entityId]
		))->fetch();

		if (!$result)
		{
			return false;
		}

		$result['LINK'] = self::getPublicLink($result['ENTITY_TYPE'], $result['ALIAS']);

		return $result;
	}

	public static function prepareAlias($alias)
	{
		$alias = preg_replace("/[^\.\-0-9a-zA-Z]+/", "", $alias);
		$alias = mb_substr($alias, 0, 255);

		return $alias;
	}

	public static function getPublicLink($type, $alias)
	{
		$path = '/online/';

		if ($type === self::ENTITY_TYPE_VIDEOCONF || $type === self::ENTITY_TYPE_JITSICONF)
		{
			$path = '/video/';
		}
		else if ($type === self::ENTITY_TYPE_LIVECHAT)
		{
			return '';
		}

		return \Bitrix\Im\Common::getPublicDomain() . $path . $alias;
	}

	public static function generateUnique()
	{
		if (\Bitrix\Main\Loader::includeModule('security'))
		{
			return \Bitrix\Main\Security\Random::getString(8, true);
		}
		else
		{
			return mb_substr(uniqid(),-8);
		}
	}
}