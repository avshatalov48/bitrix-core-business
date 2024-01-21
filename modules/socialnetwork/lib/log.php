<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM;
use Bitrix\Main\SystemException;
use Bitrix\Socialnetwork\Internals\Log\Log;
use Bitrix\Socialnetwork\Internals\Log\LogCollection;
use Bitrix\Socialnetwork\Item\LogIndex;
use Exception;

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
 * @method static \Bitrix\Socialnetwork\Internals\Log\Log createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\Internals\Log\LogCollection createCollection()
 * @method static \Bitrix\Socialnetwork\Internals\Log\Log wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\Internals\Log\LogCollection wakeUpCollection($rows)
 */
class LogTable extends ORM\Data\DataManager
{
	public static function getTableName(): string
	{
		return 'b_sonet_log';
	}

	public static function getUfId(): string
	{
		return 'SONET_LOG';
	}

	public static function getMap(): array
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'ENTITY_TYPE' => [
				'data_type' => 'string',
			],
			'ENTITY_ID' => [
				'data_type' => 'integer',
			],
			'EVENT_ID' => [
				'data_type' => 'string',
			],
			'USER_ID' => [
				'data_type' => 'integer',
			],
			'USER' => [
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => ['=this.USER_ID' => 'ref.ID'],
			],
			'TITLE' => [
				'data_type' => 'string',
			],
			'MESSAGE' => [
				'data_type' => 'text',
			],
			'TEXT_MESSAGE' => [
				'data_type' => 'text',
			],
			'URL' => [
				'data_type' => 'string',
			],
			'MODULE_ID' => [
				'data_type' => 'string',
			],
			'PARAMS' => [
				'data_type' => 'text',
			],
			'SOURCE_ID' => [
				'data_type' => 'integer',
			],
			'LOG_DATE' => [
				'data_type' => 'datetime',
			],
			'LOG_UPDATE' => [
				'data_type' => 'datetime',
			],
			'COMMENTS_COUNT' => [
				'data_type' => 'integer',
			],
			'TRANSFORM' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y'],
			],
			'INACTIVE' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y'],
			],
			'RATING_TYPE_ID' => [
				'data_type' => 'string',
			],
			'RATING_ENTITY_ID' => [
				'data_type' => 'integer',
			],
		];
	}

	/**
	 * @throws Exception
	 */
	public static function setInactive($id, $status = true): ORM\Data\UpdateResult
	{
		return self::update($id, array(
			'INACTIVE' => ($status ? 'Y' : 'N')
		));
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public static function onDelete(ORM\Event $event): ORM\EventResult
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

			foreach($tabletList as [$tablet, $fieldName])
			{
				/** @var ORM\Data\DataManager $tablet */
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

	public static function onAfterDelete(ORM\Event $event): ORM\EventResult
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

	public static function getObjectClass(): string
	{
		return Log::class;
	}

	public static function getCollectionClass(): string
	{
		return LogCollection::class;
	}
}