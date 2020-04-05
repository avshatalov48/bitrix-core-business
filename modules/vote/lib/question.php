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
 * Class VoteTable
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ACTIVE bool mandatory default 'Y',
 * <li> TIMESTAMP_X datetime,
 * <li> VOTE_ID int,
 * <li> C_SORT int,
 * <li> COUNTER int,
 * <li> QUESTION text,
 * <li> QUESTION_TYPE string(4),
 * <li> IMAGE_ID int,
 * <li> DIAGRAM bool mandatory default 'Y',
 * <li> DIAGRAM_TYPE string(10) mandatory default 'histogram' || 'circle',
 * <li> REQUIRED bool mandatory default 'N',
 * </ul>
 */
class QuestionTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_vote_question';
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
			'VOTE_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_VOTE_ID'),
			),
			'C_SORT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_C_SORT'),
			),
			'COUNTER' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_COUNTER'),
			),
			'QUESTION' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('V_TABLE_FIELD_QUESTION')
			),
			'QUESTION_TYPE' => array(
				'data_type' => 'enum',
				'values' => array("text", "html"),
				'default_value' => "text",
				'title' => Loc::getMessage('V_TABLE_FIELD_QUESTION_TYPE'),
			),
			'IMAGE_ID' =>  array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('V_TABLE_FIELD_IMAGE_ID'),
			),
			'DIAGRAM' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'Y',
				'title' => Loc::getMessage('V_TABLE_FIELD_DIAGRAM')
			),
			'DIAGRAM_TYPE' => array(
				'data_type' => 'enum',
				'values' => array("histogram", "circle"),
				'default_value' => "histogram",
				'title' => Loc::getMessage('V_TABLE_FIELD_DIAGRAM_TYPE'),
			),
			'REQUIRED' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
				'title' => Loc::getMessage('V_TABLE_FIELD_REQUIRED')
			),
			'VOTE' => array(
				'data_type' => '\Bitrix\Vote\VoteTable',
				'reference' => array(
					'=this.VOTE_ID' => 'ref.ID',
				),
				'join_type' => 'RIGHT',
			),
			'ANSWER' => array(
				'data_type' => '\Bitrix\Vote\AnswerTable',
				'reference' => array(
					'=this.ID' => 'ref.QUESTION_ID',
				),
				'join_type' => 'LEFT',
			)
		);
	}
	/**
	 * @param array $id Question IDs.
	 * @param mixed $increment True - increment, false - decrement, integer - exact value.
	 * @return void
	 */
	public static function setCounter(array $id, $increment = true)
	{
		$id = implode(", ", $id);
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

class Question
{
	public static $storage = array();
}