<?php
namespace Bitrix\Im\Model;

use Bitrix\Main,
	Bitrix\Main\Application,
	Bitrix\Main\Entity,
	Bitrix\Main\Error;


/**
 * Class MessageIndexTable
 *
 * Fields:
 * <ul>
 * <li> MESSAGE_ID int mandatory
 * <li> SEARCH_CONTENT string optional
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MessageIndex_Query query()
 * @method static EO_MessageIndex_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_MessageIndex_Result getById($id)
 * @method static EO_MessageIndex_Result getList(array $parameters = array())
 * @method static EO_MessageIndex_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_MessageIndex createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_MessageIndex_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_MessageIndex wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_MessageIndex_Collection wakeUpCollection($rows)
 */

class MessageIndexTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_message_index';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'MESSAGE_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'SEARCH_CONTENT' => array(
				'data_type' => 'text',
			),
		);
	}

	public static function validateTitle()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}

	protected static function getMergeFields()
	{
		return array('MESSAGE_ID');
	}

	public static function merge(array $data)
	{
		$result = new Entity\AddResult();

		$helper = Application::getConnection()->getSqlHelper();
		$insertData = $data;
		$updateData = $data;
		$mergeFields = static::getMergeFields();

		foreach ($mergeFields as $field)
		{
			unset($updateData[$field]);
		}

		$merge = $helper->prepareMerge(
			static::getTableName(),
			static::getMergeFields(),
			$insertData,
			$updateData
		);

		if ($merge[0] != "")
		{
			Application::getConnection()->query($merge[0]);
			$id = Application::getConnection()->getInsertedId();
			$result->setId($id);
			$result->setData($data);
		}
		else
		{
			$result->addError(new Error('Error constructing query'));
		}

		return $result;
	}
}