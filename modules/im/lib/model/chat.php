<?php
namespace Bitrix\Im\Model;

use Bitrix\Im\Internals\ChatIndex;
use Bitrix\Im\V2\Chat;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\Search\MapBuilder;

Loc::loadMessages(__FILE__);

/**
 * Class ChatTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TITLE string(255) optional
 * <li> DESCRIPTION text optional
 * <li> TYPE string(1) optional
 * <li> AUTHOR_ID int mandatory
 * <li> AVATAR int optional
 * <li> COLOR string optional
 * <li> CALL_TYPE int optional
 * <li> CALL_NUMBER string(20) optional
 * <li> EXTRANET bool optional default 'N'
 * <li> ENTITY_TYPE string(50) optional
 * <li> ENTITY_ID string(255) optional
 * <li> ENTITY_DATA_1 string(255 optional
 * <li> ENTITY_DATA_2 string(255) optional
 * <li> ENTITY_DATA_3 string(255) optional
 * <li> DISK_FOLDER_ID int optional
 * <li> AUTHOR reference to {@link \Bitrix\User\UserTable}
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Chat_Query query()
 * @method static EO_Chat_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Chat_Result getById($id)
 * @method static EO_Chat_Result getList(array $parameters = array())
 * @method static EO_Chat_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_Chat createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_Chat_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_Chat wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_Chat_Collection wakeUpCollection($rows)
 */

class ChatTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_im_chat';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('CHAT_ENTITY_ID_FIELD'),
			),
			'PARENT_ID' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
			'PARENT_MID' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
			'TITLE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateTitle'),
				'title' => Loc::getMessage('CHAT_ENTITY_TITLE_FIELD'),
				'save_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getSaveModificator'),
				'fetch_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getFetchModificator'),
			),
			'DESCRIPTION' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('CHAT_ENTITY_DESCRIPTION_FIELD'),
				'save_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getSaveModificator'),
				'fetch_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getFetchModificator'),
			),
			'COLOR' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateColor'),
				'title' => Loc::getMessage('CHAT_ENTITY_COLOR_FIELD'),
			),
			'TYPE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateType'),
				'title' => Loc::getMessage('CHAT_ENTITY_TYPE_FIELD'),
				'default_value' => 'C',
			),
			'EXTRANET' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('CHAT_ENTITY_EXTRANET_FIELD'),
				'default_value' => 'N',
			),
			'AUTHOR_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('CHAT_ENTITY_AUTHOR_ID_FIELD'),
			),
			'AVATAR' => array(
				'data_type' => 'integer'
			),
			'CALL_TYPE' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('CHAT_ENTITY_CALL_TYPE_FIELD'),
			),
			'CALL_NUMBER' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCallNumber'),
				'title' => Loc::getMessage('CHAT_ENTITY_CALL_NUMBER_FIELD'),
			),
			'ENTITY_TYPE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateEntityType'),
				'title' => Loc::getMessage('CHAT_ENTITY_ENTITY_TYPE_FIELD'),
			),
			'ENTITY_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateEntityId'),
				'title' => Loc::getMessage('CHAT_ENTITY_ENTITY_ID_FIELD'),
			),
			'ENTITY_DATA_1' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateEntityData'),
				'title' => Loc::getMessage('CHAT_ENTITY_ENTITY_DATA_1_FIELD'),
			),
			'ENTITY_DATA_2' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateEntityData'),
				'title' => Loc::getMessage('CHAT_ENTITY_ENTITY_DATA_2_FIELD'),
			),
			'ENTITY_DATA_3' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateEntityData'),
				'title' => Loc::getMessage('CHAT_ENTITY_ENTITY_DATA_3_FIELD'),
			),
			'AUTHOR' => array(
				'data_type' => 'Bitrix\Main\User',
				'reference' => array('=this.AUTHOR_ID' => 'ref.ID'),
			),
			'DISK_FOLDER_ID' => array(
				'data_type' => 'integer'
			),
			'PIN_MESSAGE_ID' => array(
				'data_type' => 'integer',
				'default_value' => 0
			),
			'MESSAGE_COUNT' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
			'USER_COUNT' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
			'PREV_MESSAGE_ID' => array(
				'data_type' => 'integer',
				'default_value' => 0
			),
			'LAST_MESSAGE_ID' => array(
				'data_type' => 'integer',
				'default_value' => 0
			),
			'LAST_MESSAGE_STATUS' => array(
				'data_type' => 'string',
				'default_value' => IM_MESSAGE_STATUS_RECEIVED,
				'validation' => array(__CLASS__, 'validateMessageStatus'),
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'required' => false,
				'default_value' => array(__CLASS__, 'getCurrentDate'),
			),
			'MANAGE_USERS' => array(
				'data_type' => 'enum',
				'values' => [Chat::MANAGE_RIGHTS_ALL, Chat::MANAGE_RIGHTS_OWNER, Chat::MANAGE_RIGHTS_MANAGERS],
				'default_value' => Chat::MANAGE_RIGHTS_ALL,
			),
			'MANAGE_UI' => array(
				'data_type' => 'enum',
				'values' =>[Chat::MANAGE_RIGHTS_ALL, Chat::MANAGE_RIGHTS_OWNER, Chat::MANAGE_RIGHTS_MANAGERS],
				'default_value' => Chat::MANAGE_RIGHTS_ALL,
			),
			'MANAGE_SETTINGS' => array(
				'data_type' => 'enum',
				'values' => [Chat::MANAGE_RIGHTS_OWNER, Chat::MANAGE_RIGHTS_MANAGERS],
				'default_value' => Chat::MANAGE_RIGHTS_OWNER,
			),
			'INDEX' => array(
				'data_type' => 'Bitrix\Im\Model\ChatIndex',
				'reference' => array('=this.ID' => 'ref.CHAT_ID'),
				'join_type' => 'INNER',
			),
			'ALIAS' => array(
				'data_type' => 'Bitrix\Im\Model\AliasTable',
				'reference' => array('=this.ID' => 'ref.ENTITY_ID', '=this.ENTITY_TYPE' => 'ref.ENTITY_TYPE'),
				'join_type' => 'LEFT',
			),
		);
	}

	public static function onAfterUpdate(\Bitrix\Main\ORM\Event $event)
	{
		$fields = $event->getParameter("fields");
		if (isset($fields['TITLE']))
		{
			$primary = $event->getParameter("id");

			$chatIndex =
				ChatIndex::create()
					->setChatId((int)$primary["ID"])
			;
			static::updateIndexRecord($chatIndex);
		}

		if (static::needCacheInvalidate($fields))
		{
			Chat::cleanCache((int)$event->getParameter("id")['ID']);
		}

		return new Entity\EventResult();
	}

	public static function onAfterDelete(Event $event)
	{
		$id = (int)$event->getParameter('primary')['ID'];
		Chat::cleanCache($id);

		return new Entity\EventResult();
	}

	public static function indexRecord(ChatIndex $chatIndex)
	{
		if ($chatIndex->getChatId() === 0)
		{
			return;
		}

		$record = static::getRecordChatData($chatIndex->getChatId());
		if(!is_array($record))
		{
			return;
		}

		$chatIndex->setTitle($record['TITLE']);

		ChatIndexTable::merge([
			'CHAT_ID' => $chatIndex->getChatId(),
			'SEARCH_CONTENT' => MapBuilder::create()->addText(self::generateSearchContent($chatIndex))->build(),
			'SEARCH_TITLE' => MapBuilder::create()->addText(self::generateSearchTitle($chatIndex))->build(),
		]);
	}

	public static function addIndexRecord(ChatIndex $index)
	{
		if ($index->getChatId() === 0)
		{
			return;
		}
		$insertData = self::prepareParamsForIndex($index);

		ChatIndexTable::add($insertData);
	}

	public static function updateIndexRecord(ChatIndex $index)
	{
		$record = static::getRecordChatData($index->getChatId());
		if(!is_array($record))
		{
			return;
		}

		$index->setTitle($record['TITLE']);
		$updateData = self::prepareParamsForIndex($index);

		ChatIndexTable::updateIndex(
			$index->getChatId(),
			'CHAT_ID',
			$updateData
		);
	}

	protected static function needCacheInvalidate(array $updatedFields): bool
	{
		$cacheInvalidatingFields = [
			'TITLE',
			'DESCRIPTION',
			'COLOR',
			'TYPE',
			'EXTRANET',
			'AUTHOR_ID',
			'AVATAR',
			'ENTITY_TYPE',
			'ENTITY_ID',
			'ENTITY_DATA_1',
			'ENTITY_DATA_2',
			'ENTITY_DATA_3',
			'DISK_FOLDER_ID',
		];

		return !empty(array_intersect($cacheInvalidatingFields, array_keys($updatedFields)));
	}


	/**
	 * @param ChatIndex $chatIndex
	 * @return string
	 */
	public static function generateSearchContent(ChatIndex $chatIndex)
	{
		$indexTitle = $chatIndex->getClearedTitle();
		$userNameList = static::getChatUserNameList($chatIndex);

		return $indexTitle . ' ' . implode(' ', $userNameList);
	}

	public static function generateSearchTitle(ChatIndex $chatIndex): string
	{
		return $chatIndex->getClearedTitle();
	}

	public static function validateTitle()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
	public static function validateType()
	{
		return array(
			new Entity\Validator\Length(null, 1),
		);
	}
	public static function validateColor()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
	public static function validateEntityType()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}
	public static function validateEntityId()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
	public static function validateCallNumber()
	{
		return array(
			new Entity\Validator\Length(null, 20),
		);
	}
	public static function validateEntityData()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
	public static function getCurrentDate()
	{
		return new \Bitrix\Main\Type\DateTime();
	}
	public static function validateMessageStatus()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}

	/**
	 * @param int $id
	 * @return array|false
	 */
	private static function getRecordChatData(int $id)
	{
		return
			self::query()
				->setSelect(['*'])
				->where('ID', $id)
				->whereNot('ENTITY_TYPE', 'LIVECHAT')
				->whereNotIn('TYPE', [\Bitrix\Im\Chat::TYPE_SYSTEM, \Bitrix\Im\Chat::TYPE_PRIVATE])
				->fetch()
			;
	}

	private static function getChatUserNameList(ChatIndex $chatIndex): array
	{
		if (!$chatIndex->isEmptyUsers())
		{
			return $chatIndex->getClearedUserList();
		}

		$query =
			\Bitrix\Im\Model\RelationTable::query()
				->addSelect('USER_ID')
				->where('CHAT_ID', $chatIndex->getChatId())
				->setLimit(100)
		;

		$clearedUsers = [];
		foreach ($query->exec() as $relation)
		{
			$rowUserName = \Bitrix\Im\User::getInstance($relation['USER_ID'])->getFullName(false);
			$clearedUsers[] = ChatIndex::clearText($rowUserName);
		}

		return $clearedUsers;
	}

	/**
	 * @param ChatIndex $index
	 * @return array{CHAT_ID: string, SEARCH_CONTENT: string, SEARCH_TITLE: string}
	 */
	private static function prepareParamsForIndex(ChatIndex $index): array
	{
		return [
			'CHAT_ID' => $index->getChatId(),
			'SEARCH_CONTENT' => MapBuilder::create()->addText(self::generateSearchContent($index))->build(),
			'SEARCH_TITLE' => MapBuilder::create()->addText(self::generateSearchTitle($index))->build(),
		];
	}
}