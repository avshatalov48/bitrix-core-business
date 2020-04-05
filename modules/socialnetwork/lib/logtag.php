<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;

class LogTagTable extends Entity\DataManager
{
	const ITEM_TYPE_LOG = 'L';
	const ITEM_TYPE_COMMENT = 'LC';

	public static function getTableName()
	{
		return 'b_sonet_log_tag';
	}

	public static function getItemTypes()
	{
		return array(
			self::ITEM_TYPE_LOG,
			self::ITEM_TYPE_COMMENT
		);
	}

	public static function getMap()
	{
		return array(
			'LOG_ID' => array(
				'data_type' => 'integer',
			),
			'LOG' => array(
				'data_type' => '\Bitrix\Socialnetwork\Log',
				'reference' => array('=this.LOG_ID' => 'ref.ID')
			),
			'ITEM_TYPE' => array(
				'data_type' => 'string',
				'primary' => true,
			),
			'ITEM_ID' => array(
				'data_type' => 'string',
				'primary' => true,
			),
			'NAME' => array(
				'data_type' => 'string',
				'primary' => true
			)
		);
	}

	public static function deleteByLogId($params = array())
	{
		if (
			!is_array($params)
			|| empty($params['logId'])
			|| intval($params['logId']) <= 0
		)
		{
			return false;
		}

		\Bitrix\Main\Application::getConnection()->queryExecute('DELETE FROM '.self::getTableName().' WHERE LOG_ID = '.intval($params['logId']));
		return true;
	}

	public static function deleteByItem($params = array())
	{
		if (
			!is_array($params)
			|| empty($params['itemId'])
			|| intval($params['itemId']) <= 0
		)
		{
			return false;
		}

		if (
			empty($params['itemType'])
			|| !in_array($params['itemType'], self::getItemTypes())
		)
		{
			$params['itemType'] = self::ITEM_TYPE_LOG;
		}

		\Bitrix\Main\Application::getConnection()->queryExecute('DELETE FROM '.self::getTableName().' WHERE ITEM_TYPE = "'.$params['itemType'].'" AND ITEM_ID = '.intval($params['itemId']));
		return true;
	}

	public static function set($params = array())
	{
		if (
			!is_array($params)
			|| empty($params['itemId'])
			|| intval($params['itemId']) <= 0
			|| !isset($params['tags'])
			|| !is_array($params['tags'])
		)
		{
			return false;
		}

		if (
			empty($params['itemType'])
			|| !in_array($params['itemType'], self::getItemTypes())
		)
		{
			$params['itemType'] = self::ITEM_TYPE_LOG;
		}

		if ($params['itemType'] == self::ITEM_TYPE_LOG)
		{
			$params['logId'] = intval($params['itemId']);
		}
		elseif (
			empty($params['logId'])
			|| intval($params['logId']) <= 0
		)
		{
			$res = LogCommentTable::getList(array(
				'filter' => array(
					'ID' => intval($params['itemId'])
				),
				'select' => array('LOG_ID')
			));
			if ($logEntry = $res->fetch())
			{
				$params['logId'] = intval($logEntry['LOG_ID']);
			}
		}

		if (
			empty($params['logId'])
			|| intval($params['logId']) <= 0
		)
		{
			return false;
		}

		self::deleteByItem(array(
			'itemType' => $params['itemType'],
			'itemId' => intval($params['itemId'])
		));

		foreach($params['tags'] as $tag)
		{
			self::add(array(
				'ITEM_TYPE' => $params['itemType'],
				'ITEM_ID' => intval($params['itemId']),
				'LOG_ID' => intval($params['logId']),
				'NAME' => $tag
			));
		}

		return true;
	}
}
