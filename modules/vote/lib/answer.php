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
Loc::loadMessages(__FILE__);

/**
 * Class AnswerTable
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ACTIVE bool mandatory default 'Y',
 * <li> TIMESTAMP_X datetime,
 * <li> QUESTION_ID int,
 * <li> C_SORT int,
 * <li> COUNTER int,
 * <li> MESSAGE text,
 * <li> MESSAGE_TYPE string(4),
 * <li> FIELD_TYPE int,
 * <li> COLOR string(7),
 * </ul>
 */
class AnswerTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_vote_answer';
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
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'Y',
				'title' => Loc::getMessage('V_TABLE_FIELD_ACTIVE')
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('V_TABLE_FIELD_TIMESTAMP_X'),
			),
			'QUESTION_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_QUESTION_ID'),
			),
			'C_SORT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_C_SORT'),
			),
			'COUNTER' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_COUNTER'),
			),
			'MESSAGE' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('V_TABLE_FIELD_MESSAGE')
			),
			'MESSAGE_TYPE' => array(
				'data_type' => 'enum',
				'values' => array("text", "html"),
				'default_value' => "text",
				'title' => Loc::getMessage('V_TABLE_FIELD_MESSAGE_TYPE'),
			),
			'FIELD_TYPE' =>  array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_FIELD_TYPE'),
			),
			'COLOR' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_COLOR'),
			),
		);
	}
	/**
	 * @param array $id Answer IDs.
	 * @param mixed $increment True - increment, false - decrement, integer - exact value.
	 * @return void
	 */
	public static function setCounter(array $id, $increment = true)
	{
		$id = implode(", ", array_map('intval', $id));
		if (empty($id))
			return;
		$connection = \Bitrix\Main\Application::getInstance()->getConnection();

		$sql = intval($increment);
		if ($increment === true)
			$sql = "COUNTER+1";
		else if ($increment === false)
			$sql = "COUNTER-1";
		$connection->queryExecute("UPDATE ".self::getTableName()." SET COUNTER=".$sql." WHERE ID IN (".$id.")");
	}
}
class Answer
{
	public static $storage = array();
}