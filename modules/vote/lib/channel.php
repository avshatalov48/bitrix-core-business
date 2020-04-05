<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage vote
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Vote;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Vote\Base\BaseObject;
Loc::loadMessages(__FILE__);

/**
 * Class ChannelTable
 * Fields:
 * <ul>
 * <li> ID int mandatory,
 * <li> SYMBOLIC_NAME string(255) mandatory ,
 * <li> TITLE string(255) mandatory ,
 * <li> C_SORT int,
 * <li> ACTIVE bool mandatory default 'Y',
 * <li> HIDDEN bool mandatory default 'N',
 * <li> TIMESTAMP_X datetime,
 * <li> VOTE_SINGLE bool mandatory default 'Y',
 * <li> USE_CAPTCHA bool mandatory default 'N'
 * </ul>
 */
class ChannelTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_vote_channel';
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
				'title' => Loc::getMessage('V_TABLE_FIELD_ID'),
			),
			'SYMBOLIC_NAME' => array(
				'data_type' => 'string',
				'size' => 255,
				'title' => Loc::getMessage('V_TABLE_FIELD_SYMBOLIC_NAME'),
			),
			'TITLE' => array(
				'data_type' => 'string',
				'size' => 255,
				'title' => Loc::getMessage('V_TABLE_FIELD_TITLE'),
			),
			'C_SORT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_C_SORT'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'Y',
				'title' => Loc::getMessage('V_TABLE_FIELD_ACTIVE')
			),
			'HIDDEN' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
				'title' => Loc::getMessage('V_TABLE_FIELD_HIDDEN')
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('V_TABLE_FIELD_TIMESTAMP_X'),
			),
			'VOTE_SINGLE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'Y',
				'title' => Loc::getMessage('V_TABLE_FIELD_ACTIVE')
			),
			'USE_CAPTCHA' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
				'title' => Loc::getMessage('V_TABLE_FIELD_HIDDEN')
			),
			'PERMISSION' => array(
				'data_type' => '\Bitrix\Vote\ChannelGroupTable',
				'reference' => array(
					'=this.ID' => 'ref.CHANNEL_ID',
				),
				'join_type' => 'INNER',
			),
			'SITE' => array(
				'data_type' => '\Bitrix\Vote\ChannelSiteTable',
				'reference' => array(
					'=this.ID' => 'ref.CHANNEL_ID',
				),
				'join_type' => 'LEFT',
			)
		);
	}
}

/**
 * Class ChannelGroupTable
 * Fields:
 * <ul>
 * <li> ID int mandatory,
 * <li> CHANNEL_ID int mandatory,
 * <li> GROUP_ID int mandatory,
 * <li> PERMISSION int mandatory (1-4),
 * </ul>
 */
class ChannelGroupTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_vote_channel_2_group';
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
				'title' => Loc::getMessage('V_TABLE_FIELD_ID'),
			),
			'CHANNEL_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_CHANNEL_ID'),
			),
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_GROUP_ID'),
			),
			'PERMISSION' => array(
				'data_type' => 'enum',
				'values' => array(1, 2, 3, 4),
				'default_value' => 4,
				'title' => Loc::getMessage('V_TABLE_PERMISSION')
			)
		);
	}
}
/**
 * Class ChannelSiteTable
 * Fields:
 * <ul>
 * <li> CHANNEL_ID int mandatory,
 * <li> SITE_ID string(2) mandatory
 * </ul>
 */
class ChannelSiteTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_vote_channel_2_site';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'CHANNEL_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('V_TABLE_FIELD_CHANNEL_ID'),
			),
			'SITE_ID' => array(
				'data_type' => 'string',
				'primary' => true,
				'size' => 2,
				'title' => Loc::getMessage('V_TABLE_FIELD_SITE_ID'),
			)
		);
	}
}

class Channel extends BaseObject implements \ArrayAccess
{
	static $channels = array();
	private $data = array();

	/**
	 * Channel constructor.
	 * @param $id
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public function __construct($id)
	{
		if (!($id > 0))
			throw new \Bitrix\Main\ArgumentNullException("id");
		parent::__construct($id);
	}
	/**
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 */
	public function init()
	{
		if (($data = self::getById($this->id)->fetch()) && !empty($data))
			$this->data = $data;
		else
			throw new \Bitrix\Main\ObjectNotFoundException(GetMessage("V_CHANNEL_IS_NOT_FOUND", "channel is not found"));
	}
	/**
	 * @param array $parameters Array of query parameters.
	 * @return \Bitrix\Main\DB\Result|\CDBResult
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getList(array $parameters)
	{
		global $CACHE_MANAGER;
		$md5 = md5(serialize($parameters));
		if (!array_key_exists($md5, self::$channels))
		{
			$data = array();
			if (defined("VOTE_CACHE_TIME") && $CACHE_MANAGER->read(VOTE_CACHE_TIME, "b_vote_channel_".$md5, "b_vote_channel"))
				$data = $CACHE_MANAGER->get("b_vote_channel_".$md5);
			else
			{
				$db = ChannelTable::getList($parameters);
				while ($r = $db->fetch())
					$data[$r["ID"]] = $r;
				if (defined("VOTE_CACHE_TIME"))
					$CACHE_MANAGER->set("b_vote_channel_".$md5, $data);
			}
			self::$channels[$md5] =  $data;
		}

		$db = new \CDBResult();
		$db->initFromArray(self::$channels[$md5]);
		return $db;
	}

	/**
	 * @param integer $id Channel ID.
	 * @return \Bitrix\Main\DB\Result|\CDBResult
	 */
	public static function getById($id)
	{
		return self::getList(array(
			'select' => array("*"),
			'filter' => array("ID" => $id),
		));
	}

	/**
	 * @param integer $userId User ID.
	 * @return boolean
	 */
	public function canRead($userId)
	{
		if (!parent::canEdit($userId))
		{
			$groups = parent::loadUserGroups($userId);
			$dbRes = \Bitrix\Vote\Channel::getList(array(
				'select' => array("*"),
				'filter' => array(
					"ACTIVE" => "Y",
					"HIDDEN" => "N",
					">=PERMISSION.PERMISSION" => 1,
					"PERMISSION.GROUP_ID" => $groups
				),
				'order' => array(
					'TITLE' => 'ASC'
				),
				'group' => array("ID")
			));
			while ($res = $dbRes->fetch())
			{
				if ($res["ID"] == $this->id)
					return true;
			}
			return false;
		}
		return true;
	}

	/**
	 * @param integer $userId User ID.
	 * @return boolean
	 */
	public function canEdit($userId)
	{
		return parent::canEdit($userId);
	}
	/**
	 * @param integer $userId User ID.
	 * @return boolean
	 */
	public function canEditVote($userId)
	{
		if (!parent::canEdit($userId))
		{
			$groups = parent::loadUserGroups($userId);
			$dbRes = \Bitrix\Vote\Channel::getList(array(
				'select' => array("*"),
				'filter' => array(
					"ACTIVE" => "Y",
					"HIDDEN" => "N",
					">=PERMISSION.PERMISSION" => 4,
					"PERMISSION.GROUP_ID" => $groups
				),
				'order' => array(
					'TITLE' => 'ASC'
				),
				'group' => array("ID")
			));
			while ($res = $dbRes->fetch())
			{
				if ($res["ID"] == $this->id)
					return true;
			}
			return false;
		}
		return true;
	}
	/**
	 * @param string $key Characteristic that you want to know.
	 * @return mixed|null
	 */
	public function get($key)
	{
		return array_key_exists($key, $this->data) ? $this->data[$key] : null;
	}
	/**
	 * Whether a offset exists.
	 * @param string $offset An offset to check for.
	 * @return mixed|null
	 */
	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->data);
	}
	/**
	 * @param string $offset The offset to retrieve.
	 * @return mixed|null
	 */
	public function offsetGet($offset)
	{
		return $this->data[$offset];
	}

	/**
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value The value to set.
	 * @return void
	 * @throws \Bitrix\Main\NotSupportedException
	 */
	public function offsetSet($offset, $value)
	{
		throw new \Bitrix\Main\NotSupportedException('Model provide ArrayAccess only for reading');
	}
	/**
	 * @param mixed $offset The offset to unset.
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		throw new \Bitrix\Main\NotSupportedException('Model provide ArrayAccess only for reading');
	}
}