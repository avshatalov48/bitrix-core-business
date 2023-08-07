<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender;

use Bitrix\Main\Access\Entity\DataManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type;
use Bitrix\Sender\Runtime\SegmentDataClearJob;

Loc::loadMessages(__FILE__);


/**
 * Class SegmentDataTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_SegmentData_Query query()
 * @method static EO_SegmentData_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_SegmentData_Result getById($id)
 * @method static EO_SegmentData_Result getList(array $parameters = array())
 * @method static EO_SegmentData_Entity getEntity()
 * @method static \Bitrix\Sender\EO_SegmentData createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\EO_SegmentData_Collection createCollection()
 * @method static \Bitrix\Sender\EO_SegmentData wakeUpObject($row)
 * @method static \Bitrix\Sender\EO_SegmentData_Collection wakeUpCollection($rows)
 */
class SegmentDataTable extends DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_group_data';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'autocomplete' => true,
				'primary' => true,
			),
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'default_value' => new Type\DateTime(),
				'required' => true,
			),
			'CRM_ENTITY_ID' => array(
				'data_type' => 'integer',
				'required' => false,
			),
			'FILTER_ID' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => false,
			),
			'CRM_ENTITY_TYPE' => array(
				'data_type' => 'string',
				'required' => false,
			),
			'CRM_ENTITY_TYPE_ID' => array(
				'data_type' => 'integer',
				'required' => false,
			),
			'CONTACT_ID' => array(
				'data_type' => 'integer',
				'required' => false,
			),
			'COMPANY_ID' => array(
				'data_type' => 'integer',
				'required' => false,
			),
			'EMAIL' => array(
				'data_type' => 'string',
				'required' => false,
			),
			'IM' => array(
				'data_type' => 'string',
				'required' => false,
			),
			'PHONE' => array(
				'data_type' => 'string',
				'required' => false,
			),
			'HAS_EMAIL' => array(
				'data_type' => 'string',
				'required' => false,
			),
			'HAS_IMOL' => array(
				'data_type' => 'string',
				'required' => false,
			),
			'HAS_PHONE' => array(
				'data_type' => 'string',
				'required' => false,
			),
			'SENDER_TYPE_ID' => array(
				'data_type' => 'integer',
				'required' => false,
			),
			'GROUP' => array(
				'data_type' => 'Bitrix\Sender\GroupTable',
				'reference' => array('=this.GROUP_ID' => 'ref.ID'),
			),
		);
	}

	public static function deleteByGroupId(int $groupId)
	{
		$entity = static::getEntity();
		$connection = $entity->getConnection();

		$selectedRows = SegmentDataTable::getList([
			'select' => ['ID'],
			'filter' => ['GROUP_ID' => $groupId],
			'limit' => 1000,
		])->fetchAll();

		$idsToDelete = array_column($selectedRows, 'ID');
		if (empty($idsToDelete))
		{
			return '';
		}

		$ids = implode(',', $idsToDelete);

		$sql = "DELETE FROM b_sender_group_data WHERE ID IN ($ids)";
		$connection->queryExecute($sql);

		return SegmentDataClearJob::getAgentName($groupId);
	}
}