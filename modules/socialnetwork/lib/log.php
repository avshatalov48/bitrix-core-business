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

/**
 * Class LogTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Log_Query query()
 * @method static EO_Log_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Log_Result getById($id)
 * @method static EO_Log_Result getList(array $parameters = [])
 * @method static EO_Log_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\EO_Log createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\EO_Log_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\EO_Log wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\EO_Log_Collection wakeUpCollection($rows)
 */
class LogTable extends ORM\Data\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_log';
	}

	public static function getUfId()
	{
		return 'SONET_LOG';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'ENTITY_TYPE' => array(
				'data_type' => 'string',
			),
			'ENTITY_ID' => array(
				'data_type' => 'integer',
			),
			'EVENT_ID' => array(
				'data_type' => 'string',
			),
			'USER_ID' => array(
				'data_type' => 'integer',
			),
			'USER' => array(
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => array('=this.USER_ID' => 'ref.ID'),
			),
			'TITLE' => array(
				'data_type' => 'string',
			),
			'MESSAGE' => array(
				'data_type' => 'text',
			),
			'TEXT_MESSAGE' => array(
				'data_type' => 'text',
			),
			'URL' => array(
				'data_type' => 'string',
			),
			'MODULE_ID' => [
				'data_type' => 'string',
			],
			'PARAMS' => array(
				'data_type' => 'text',
			),
			'SOURCE_ID' => array(
				'data_type' => 'integer',
			),
			'LOG_DATE' => array(
				'data_type' => 'datetime',
			),
			'LOG_UPDATE' => array(
				'data_type' => 'datetime',
			),
			'COMMENTS_COUNT' => array(
				'data_type' => 'integer',
			),
			'TRANSFORM' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y')
			),
			'INACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y')
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

	public static function setInactive($id, $status = true)
	{
		return self::update($id, array(
			'INACTIVE' => ($status ? 'Y' : 'N')
		));
	}

	public static function onDelete(ORM\Event $event)
	{
		$result = new ORM\EventResult;
		$primary = $event->getParameter('primary');
		$logId = (!empty($primary['ID']) ? (int)$primary['ID'] : 0);

		if ($logId > 0)
		{
			$tabletList = [
				[ '\Bitrix\Socialnetwork\LogCommentTable', 'LOG_ID' ],
				[ '\Bitrix\Socialnetwork\LogRightTable', 'LOG_ID' ],
				[ '\Bitrix\Socialnetwork\LogSiteTable', 'LOG_ID' ],
				[ '\Bitrix\Socialnetwork\LogFavoritesTable', 'LOG_ID' ],
				[ '\Bitrix\Socialnetwork\LogTagTable', 'LOG_ID' ]
			];

			foreach($tabletList as list($tablet, $fieldName))
			{
				$collection = $tablet::query()
					->where($fieldName, $logId)
					->fetchCollection();

				foreach ($collection as $entity)
				{
					$entity->delete();
				}
			}
		}

		return $result;
	}

	public static function onAfterDelete(ORM\Event $event)
	{
		$result = new ORM\EventResult;
		$primary = $event->getParameter('primary');
		$logId = (!empty($primary['ID']) ? (int)$primary['ID'] : 0);

		if ($logId > 0)
		{
			LogIndex::deleteIndex(array(
				'itemType' => LogIndexTable::ITEM_TYPE_LOG,
				'itemId' => $logId
			));
		}

		return $result;
	}
}
