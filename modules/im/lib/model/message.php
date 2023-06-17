<?php
namespace Bitrix\Im\Model;

use Bitrix\Im\Text;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Search\MapBuilder;

Loc::loadMessages(__FILE__);

/**
 * Class MessageTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CHAT_ID int mandatory
 * <li> AUTHOR_ID int mandatory
 * <li> MESSAGE string optional
 * <li> MESSAGE_OUT string optional
 * <li> DATE_CREATE datetime mandatory
 * <li> EMAIL_TEMPLATE string(255) optional
 * <li> NOTIFY_TYPE int optional
 * <li> NOTIFY_MODULE string(255) optional
 * <li> NOTIFY_EVENT string(255) optional
 * <li> NOTIFY_TAG string(255) optional
 * <li> NOTIFY_SUB_TAG string(255) optional
 * <li> NOTIFY_TITLE string(255) optional
 * <li> NOTIFY_BUTTONS string optional
 * <li> NOTIFY_READ bool optional default 'N'
 * <li> IMPORT_ID int optional
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Message_Query query()
 * @method static EO_Message_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Message_Result getById($id)
 * @method static EO_Message_Result getList(array $parameters = array())
 * @method static EO_Message_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_Message createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_Message_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_Message wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_Message_Collection wakeUpCollection($rows)
 */

class MessageTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_message';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('MESSAGE_ENTITY_ID_FIELD'),
			),
			'CHAT_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('MESSAGE_ENTITY_CHAT_ID_FIELD'),
			),
			'AUTHOR_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('MESSAGE_ENTITY_AUTHOR_ID_FIELD'),
			),
			'MESSAGE' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('MESSAGE_ENTITY_MESSAGE_FIELD'),
				'save_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getSaveModificator'),
				'fetch_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getFetchModificator'),
			),
			'MESSAGE_OUT' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('MESSAGE_ENTITY_MESSAGE_OUT_FIELD'),
				'save_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getSaveModificator'),
				'fetch_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getFetchModificator'),
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'required' => true,
				'title' => Loc::getMessage('MESSAGE_ENTITY_DATE_CREATE_FIELD'),
				'default_value' => array(__CLASS__, 'getCurrentDate'),
			),
			'EMAIL_TEMPLATE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateEmailTemplate'),
				'title' => Loc::getMessage('MESSAGE_ENTITY_EMAIL_TEMPLATE_FIELD'),
			),
			'NOTIFY_TYPE' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('MESSAGE_ENTITY_NOTIFY_TYPE_FIELD'),
			),
			'NOTIFY_MODULE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateNotifyModule'),
				'title' => Loc::getMessage('MESSAGE_ENTITY_NOTIFY_MODULE_FIELD'),
			),
			'NOTIFY_EVENT' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateNotifyEvent'),
				'title' => Loc::getMessage('MESSAGE_ENTITY_NOTIFY_EVENT_FIELD'),
			),
			'NOTIFY_TAG' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateNotifyTag'),
				'title' => Loc::getMessage('MESSAGE_ENTITY_NOTIFY_TAG_FIELD'),
			),
			'NOTIFY_SUB_TAG' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateNotifySubTag'),
				'title' => Loc::getMessage('MESSAGE_ENTITY_NOTIFY_SUB_TAG_FIELD'),
			),
			'NOTIFY_TITLE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateNotifyTitle'),
				'title' => Loc::getMessage('MESSAGE_ENTITY_NOTIFY_TITLE_FIELD'),
			),
			'NOTIFY_BUTTONS' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('MESSAGE_ENTITY_NOTIFY_BUTTONS_FIELD'),
			),
			'NOTIFY_READ' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('MESSAGE_ENTITY_NOTIFY_READ_FIELD'),
			),
			'IMPORT_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('MESSAGE_ENTITY_IMPORT_ID_FIELD'),
			),
			'CHAT' => array(
				'data_type' => 'Bitrix\Im\Model\ChatTable',
				'reference' => array('=this.CHAT_ID' => 'ref.ID'),
			),
			'AUTHOR' => array(
				'data_type' => 'Bitrix\Main\User',
				'reference' => array('=this.AUTHOR_ID' => 'ref.ID'),
			),
			'STATUS' => array(
				'data_type' => 'Bitrix\Im\Model\StatusTable',
				'reference' => array('=this.AUTHOR_ID' => 'ref.USER_ID'),
			),
			'RELATION' => array(
				'data_type' => 'Bitrix\Im\Model\RelationTable',
				'reference' => array('=this.CHAT_ID' => 'ref.CHAT_ID'),
				'join_type' => 'INNER',
			),
			'INDEX' => array(
				'data_type' => 'Bitrix\Im\Model\MessageIndex',
				'reference' => array('=this.ID' => 'ref.MESSAGE_ID'),
				'join_type' => 'INNER',
			),
			'UUID' => array(
				'data_type' => \Bitrix\Im\Model\MessageUuidTable::class,
				'reference' => array('=this.ID' => 'ref.MESSAGE_ID'),
				'join_type' => 'LEFT',
			),
		);
	}
	/**
	 * Returns validators for EMAIL_TEMPLATE field.
	 *
	 * @return array
	 */
	public static function validateEmailTemplate()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for NOTIFY_MODULE field.
	 *
	 * @return array
	 */
	public static function validateNotifyModule()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for NOTIFY_EVENT field.
	 *
	 * @return array
	 */
	public static function validateNotifyEvent()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for NOTIFY_TAG field.
	 *
	 * @return array
	 */
	public static function validateNotifyTag()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for NOTIFY_SUB_TAG field.
	 *
	 * @return array
	 */
	public static function validateNotifySubTag()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for NOTIFY_TITLE field.
	 *
	 * @return array
	 */
	public static function validateNotifyTitle()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Return current date for DATE_CREATE field.
	 *
	 * @return \Bitrix\Main\Type\DateTime
	 */
	public static function getCurrentDate()
	{
		return new \Bitrix\Main\Type\DateTime();
	}

	/**
	 * Add message data to MessageIndex table
	 * @param $id
	 *
	 * @return bool|void
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function indexRecord($id)
	{
		$indexEnabled = \Bitrix\Main\Config\Option::get('im', 'message_history_index');

		if (!$indexEnabled)
		{
			return;
		}

		$message = parent::getByPrimary($id)->fetch();
		if (!is_array($message))
		{
			return;
		}

		MessageIndexTable::merge(
			[
				'MESSAGE_ID' => $id,
				'SEARCH_CONTENT' => self::generateSearchContent($message)
			]
		);

		return true;
	}

	/**
	 * Generate text with all the info about message for searching purposes
	 * @param array $message
	 *
	 * @return string
	 * @throws Main\NotImplementedException
	 */
	private static function generateSearchContent(array $message) : string
	{
		$builder = MapBuilder::create();

		if($message['AUTHOR_ID'] > 0)
		{
			$authorName = \Bitrix\Im\User::getInstance($message['AUTHOR_ID'])->getFullName();
			$builder->addText($authorName);
		}

		$messageText = $message['MESSAGE'];
		if($message['NOTIFY_TYPE'] === IM_NOTIFY_FROM)
		{
			$messageText = strip_tags($messageText);
		}
		else if($message['NOTIFY_TYPE'] === IM_NOTIFY_MESSAGE)
		{
			$messageText = Text::removeBbCodes($messageText);
		}
		$builder->addText($messageText);

		$params = \CIMMessageParam::Get($message['ID']);
		// Add text from attaches to builder
		if(isset($params['ATTACH']))
		{
			$textNodes = \CIMMessageParamAttach::GetTextForIndex($params['ATTACH'][0]);
			foreach($textNodes as $text)
			{
				$builder->addText($text);
			}
		}

		// Add file names to builder
		if(isset($params['FILE_ID']))
		{
			foreach($params['FILE_ID'] as $fileId)
			{
				$file = \Bitrix\Disk\File::getById($fileId);
				if (!$file)
				{
					continue;
				}
				$builder->addText($file->getName());
			}
		}

		return $builder->build();
	}

	/**
	 * Deletes rows by filter.
	 * @param array $filter Filter does not look like filter in getList. It depends by current implementation.
	 * @return void
	 */
	public static function deleteBatch(array $filter)
	{
		$whereSql = \Bitrix\Main\Entity\Query::buildFilterSql(static::getEntity(), $filter);

		if ($whereSql <> '')
		{
			$tableName = static::getTableName();
			$connection = Main\Application::getConnection();
			$connection->queryExecute("DELETE FROM {$tableName} WHERE {$whereSql}");
		}
	}

	public static function withUnreadOnly(Query $query): void
	{
		$unreadSubQuery = MessageUnreadTable::query()
			->setSelect(['ID'])
			->where('MESSAGE_ID', new \Bitrix\Main\DB\SqlExpression('%s'))
		;
		$query->whereExpr("EXISTS ({$unreadSubQuery->getQuery()})", ['ID']);
	}

	public static function withViewedOnly(Query $query): void
	{
		$viewedSubQuery = MessageViewedTable::query()
			->setSelect(['ID'])
			->where('MESSAGE_ID', new \Bitrix\Main\DB\SqlExpression('%s'))
		;
		$query->whereExpr("EXISTS ({$viewedSubQuery->getQuery()})", ['ID']);
	}

	public static function withReadOnly(Query $query): void
	{
		$unreadSubQuery = MessageUnreadTable::query()
			->setSelect(['ID'])
			->where('MESSAGE_ID', new \Bitrix\Main\DB\SqlExpression('%s'))
		;
		$query->whereExpr("NOT EXISTS ({$unreadSubQuery->getQuery()})", ['ID']);
	}
}