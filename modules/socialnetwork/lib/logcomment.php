<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\ORM;
use Bitrix\Socialnetwork\Item\LogIndex;

class LogCommentTable extends ORM\Data\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_log_comment';
	}

	public static function getUfId()
	{
		return 'SONET_COMMENT';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'LOG_ID' => array(
				'data_type' => 'integer',
			),
			'LOG' => array(
				'data_type' => 'Bitrix\Socialnetwork\LogTable',
				'reference' => array('=this.LOG_ID' => 'ref.ID'),
			),
			'EVENT_ID' => array(
				'data_type' => 'string',
			),
			'USER_ID' => array(
				'data_type' => 'integer',
			),
			'MESSAGE' => array(
				'data_type' => 'text',
			),
			'SOURCE_ID' => array(
				'data_type' => 'integer',
			),
			'LOG_DATE' => array(
				'data_type' => 'datetime',
			),
			'SHARE_DEST' => array(
				'data_type' => 'string',
			),
			'RATING_TYPE_ID' => array(
				'data_type' => 'string',
			),
			'RATING_ENTITY_ID' => array(
				'data_type' => 'integer',
			),
		);

		return $fieldsMap;
	}

	public static function onAfterDelete(ORM\Event $event)
	{
		$result = new ORM\EventResult;
		$primary = $event->getParameter('primary');
		$commentId = (!empty($primary['ID']) ? (int)$primary['ID'] : 0);

		if ($commentId > 0)
		{
			LogIndex::deleteIndex(array(
				'itemType' => LogIndexTable::ITEM_TYPE_COMMENT,
				'itemId' => $commentId
			));

			LogTagTable::deleteByItem(array(
				'itemType' => LogTagTable::ITEM_TYPE_COMMENT,
				'itemId' => $commentId
			));
		}

		return $result;
	}
}
