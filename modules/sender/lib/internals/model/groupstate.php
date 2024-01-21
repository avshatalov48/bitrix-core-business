<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals\Model;

use Bitrix\Main\Access\Entity\DataManager;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class GroupStateTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_GroupState_Query query()
 * @method static EO_GroupState_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_GroupState_Result getById($id)
 * @method static EO_GroupState_Result getList(array $parameters = array())
 * @method static EO_GroupState_Entity getEntity()
 * @method static \Bitrix\Sender\Internals\Model\EO_GroupState createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\Internals\Model\EO_GroupState_Collection createCollection()
 * @method static \Bitrix\Sender\Internals\Model\EO_GroupState wakeUpObject($row)
 * @method static \Bitrix\Sender\Internals\Model\EO_GroupState_Collection wakeUpCollection($rows)
 */
class GroupStateTable extends DataManager
{
	const STATES = [
		'CREATED' => 1,
		'IN_PROGRESS' => 2,
		'COMPLETED' => 3,
		'HALTED' => 4,
	];

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_group_state';
	}

	/**
	 * Get map.
	 *
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
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
			),
			'STATE' => array(
				'data_type' => 'integer',
			),
			'FILTER_ID' => array(
				'data_type' => 'string',
			),
			'ENDPOINT' => array(
				'data_type' => 'string',
			),
			'GROUP' => array(
				'data_type' => 'Bitrix\Sender\GroupTable',
				'reference' => array('=this.GROUP_ID' => 'ref.ID'),
			),
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'OFFSET' => array(
				'data_type' => 'integer',
			)
		);
	}

	/**
	 * Handler of event onDelete
	 *
	 * @param \Bitrix\Main\Entity\Event $event Event.
	 * @return \Bitrix\Main\Entity\EventResult
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function onDelete(\Bitrix\Main\Entity\Event $event)
	{
		$result = new \Bitrix\Main\Entity\EventResult;
		$data = $event->getParameters();

		$listId = array();
		if(array_key_exists('ID', $data['primary']))
		{
			$listId[] = $data['primary']['ID'];
		}
		else
		{
			$filter = array();
			foreach($data['primary'] as $primKey => $primVal)
			{
				$filter[$primKey] = $primVal;
			}

			$tableDataList = static::getList(array(
				'select' => array('ID'),
				'filter' => $filter
			));
			while($tableData = $tableDataList->fetch())
			{
				$listId[] = $tableData['ID'];
			}

		}

		foreach($listId as $primaryId)
		{
			$primary = array('GROUP_STATE_ID' => $primaryId);
			GroupThreadTable::deleteList($primary);
		}

		return $result;
	}
}
