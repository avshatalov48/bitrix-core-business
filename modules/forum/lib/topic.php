<?php
namespace Bitrix\Forum;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\NotImplementedException;

/**
 * Class MessageTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> FORUM_ID int mandatory
 * <li> TOPIC_ID int
 * <li> TITLE string(255) mandatory
 * <li> TITLE_SEO string(255)
 * <li> TAGS string(255)
 * <li> DESCRIPTION string(255)
 * <li> ICON string(255)
 * <li> STATE bool optional default 'Y'
 * <li> APPROVED bool optional default 'Y'
 * <li> SORT int mandatory default '150'
 * <li> VIEWS mandatory default '0'
 * <li> USER_START_ID int
 * <li> USER_START_NAME string(255),
 * <li> START_DATE datetime mandatory
 * <li> POSTS int mandatory default '0'
 * <li> LAST_POSTER_ID int(10)
 * <li> LAST_POSTER_NAME string(255) mandatory
 * <li> LAST_POST_DATE datetime mandatory
 * <li> LAST_MESSAGE_ID int
 * <li> POSTS_UNAPPROVED int mandatory default '0'
 * <li> ABS_LAST_POSTER_ID int
 * <li> ABS_LAST_POSTER_NAME string(255)
 * <li> ABS_LAST_POST_DATE datetime
 * <li> ABS_LAST_MESSAGE_ID int
 * <li> XML_ID string(255)
 * <li> HTML text
 * <li> SOCNET_GROUP_ID int
 * <li> OWNER_ID int
 * </ul>
 *
 * @package Bitrix\Forum
 **/
class TopicTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_forum_topic';
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
			),
			'FORUM_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'TOPIC_ID' => array(
				'data_type' => 'integer',
			),
			'TITLE' => array(
				'data_type' => 'string',
				'size' => 255
			),
			'TITLE_SEO' => array(
				'data_type' => 'string',
				'size' => 255
			),
			'TAGS' => array(
				'data_type' => 'string',
				'size' => 255
			),
			'DESCRIPTION' => array(
				'data_type' => 'string',
				'size' => 255
			),
			'ICON' => array(
				'data_type' => 'string',
				'size' => 255
			),
			'STATE' => array(
				'data_type' => 'boolean',
				'values' => array('Y', 'N'),
			),
			'APPROVED' => array(
				'data_type' => 'boolean',
				'values' => array('Y', 'N'),
			),
			'SORT' => array(
				'data_type' => 'integer',
			),
			'VIEWS' => array(
				'data_type' => 'integer',
			),
			'USER_START_ID' => array(
				'data_type' => 'integer',
			),
			'USER_START_NAME' => array(
				'data_type' => 'string',
				'size' => 255
			),
			'START_DATE' => array(
				'data_type' => 'datetime',
				'required' => true,
			),
			'POSTS' => array(
				'data_type' => 'integer',
			),
			'LAST_POSTER_ID' => array(
				'data_type' => 'integer',
			),
			'LAST_POSTER_NAME' => array(
				'data_type' => 'string',
				'required' => true
			),
			'LAST_POST_DATE' => array(
				'data_type' => 'datetime',
				'required' => true,
			),
			'LAST_MESSAGE_ID' => array(
				'data_type' => 'integer',
			),
			'POSTS_UNAPPROVED' => array(
				'data_type' => 'integer',
			),
			'ABS_LAST_POSTER_ID' => array(
				'data_type' => 'integer',
			),
			'ABS_LAST_POSTER_NAME' => array(
				'data_type' => 'string'
			),
			'ABS_LAST_POST_DATE' => array(
				'data_type' => 'datetime'
			),
			'ABS_LAST_MESSAGE_ID' => array(
				'data_type' => 'integer',
			),
			'XML_ID' => array(
				'data_type' => 'string',
				'size' => 255,
			),
			'SOCNET_GROUP_ID' => array(
				'data_type' => 'integer',
			),
			'OWNER_ID' => array(
				'data_type' => 'integer',
			),
		);
	}



	/**
	 * Adds row to entity table
	 *
	 * @param array $data
	 *
	 * @return Entity\AddResult Contains ID of inserted row
	 *
	 * @throws \Exception
	 */
	public static function add(array $data)
	{
		throw new NotImplementedException;
	}

	/**
	 * Updates row in entity table by primary key
	 *
	 * @param mixed $primary
	 * @param array $data
	 *
	 * @return Entity\UpdateResult
	 *
	 * @throws \Exception
	 */
	public static function update($primary, array $data)
	{
		throw new NotImplementedException;
	}

	/**
	 * Deletes row in entity table by primary key
	 *
	 * @param mixed $primary
	 *
	 * @return Entity\DeleteResult
	 *
	 * @throws \Exception
	 */
	public static function delete($primary)
	{
		throw new NotImplementedException;
	}
}