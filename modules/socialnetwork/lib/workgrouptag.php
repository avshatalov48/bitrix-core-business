<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2019 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;
use Bitrix\Main\DB\SqlQueryException;

class WorkgroupTagTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_group_tag';
	}

	public static function getMap()
	{
		return array(
			'GROUP_ID' => array(
				'data_type' => 'integer',
			),
			'GROUP' => array(
				'data_type' => '\Bitrix\Socialnetwork\Workgroup',
				'reference' => array('=this.GROUP_ID' => 'ref.ID')
			),
			'NAME' => array(
				'data_type' => 'string',
				'primary' => true
			)
		);
	}

	public static function deleteByGroupId($params = array())
	{
		if (
			!is_array($params)
			|| empty($params['groupId'])
			|| intval($params['groupId']) <= 0
		)
		{
			return false;
		}

		\Bitrix\Main\Application::getConnection()->queryExecute('DELETE FROM '.self::getTableName().' WHERE GROUP_ID = '.intval($params['groupId']));
		return true;
	}

	public static function set($params = array())
	{
		if (
			!is_array($params)
			|| empty($params['groupId'])
			|| intval($params['groupId']) <= 0
			|| !isset($params['tags'])
			|| !is_array($params['tags'])
		)
		{
			return false;
		}

		self::deleteByGroupId(array(
			'groupId' => intval($params['groupId'])
		));

		foreach($params['tags'] as $tag)
		{
			self::processAdd(array(
				'GROUP_ID' => intval($params['groupId']),
				'NAME' => toLower($tag)
			));
		}

		return true;
	}

	protected static function processAdd(array $data)
	{
		try
		{
			self::add($data);
		}
		catch (SqlQueryException $exception)
		{
			if (!self::isDuplicateKeyError($exception))
			{
				throw $exception;
			}
		}
	}

	protected static function isDuplicateKeyError(SqlQueryException $exception)
	{
		return mb_strpos($exception->getDatabaseMessage(), '(1062)') !== false;
	}
}
