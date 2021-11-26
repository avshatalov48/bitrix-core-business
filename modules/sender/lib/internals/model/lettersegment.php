<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Query;

Loc::loadMessages(__FILE__);

/**
 * Class LetterSegmentTable
 * @package Bitrix\Sender
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_LetterSegment_Query query()
 * @method static EO_LetterSegment_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_LetterSegment_Result getById($id)
 * @method static EO_LetterSegment_Result getList(array $parameters = array())
 * @method static EO_LetterSegment_Entity getEntity()
 * @method static \Bitrix\Sender\Internals\Model\EO_LetterSegment createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\Internals\Model\EO_LetterSegment_Collection createCollection()
 * @method static \Bitrix\Sender\Internals\Model\EO_LetterSegment wakeUpObject($row)
 * @method static \Bitrix\Sender\Internals\Model\EO_LetterSegment_Collection wakeUpCollection($rows)
 */
class LetterSegmentTable extends Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_mailing_chain_group';
	}

	/**
	 * Get map.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'LETTER_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'column_name' => 'CHAIN_ID',
			),
			'SEGMENT_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'column_name' => 'GROUP_ID',
			),
			'INCLUDE' => array(
				'data_type' => 'boolean',
				'values' => array(false, true),
				'required' => true,
			),
			'LETTER' => array(
				'data_type' => 'Bitrix\Sender\MailingChainTable',
				'reference' => array('=this.LETTER_ID' => 'ref.ID'),
			),
			'SEGMENT' => array(
				'data_type' => 'Bitrix\Sender\GroupTable',
				'reference' => array('=this.SEGMENT_ID' => 'ref.ID'),
			),
		);
	}


	/**
	 * @param array $filter
	 * @return \Bitrix\Main\DB\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\DB\SqlQueryException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function deleteList(array $filter)
	{
		$entity = static::getEntity();
		$connection = $entity->getConnection();

		\CTimeZone::disable();
		$sql = sprintf(
			'DELETE FROM %s WHERE %s',
			$connection->getSqlHelper()->quote($entity->getDbTableName()),
			Query::buildFilterSql($entity, $filter)
		);
		$res = $connection->query($sql);
		\CTimeZone::enable();

		return $res;
	}
}