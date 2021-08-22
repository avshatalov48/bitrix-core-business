<?php

namespace Bitrix\Im\Model;

use Bitrix\Main\Application;
use Bitrix\Main\Entity;
use Bitrix\Main\Error;

/**
 * Class CallUserTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CallUser_Query query()
 * @method static EO_CallUser_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_CallUser_Result getById($id)
 * @method static EO_CallUser_Result getList(array $parameters = array())
 * @method static EO_CallUser_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_CallUser createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_CallUser_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_CallUser wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_CallUser_Collection wakeUpCollection($rows)
 */
class CallUserTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_im_call_user';
	}

	public static function getMap()
	{
		return [
			new Entity\IntegerField('CALL_ID', [
				'primary' => true
			]),
			new Entity\IntegerField('USER_ID', [
				'primary' => true
			]),
			new Entity\StringField('STATE'),
			new Entity\DatetimeField('FIRST_JOINED'),
			new Entity\DatetimeField('LAST_SEEN'),
			new Entity\BooleanField('IS_MOBILE', [
				'values' => ['N', 'Y']
			]),
			new Entity\BooleanField('SHARED_SCREEN', [
				'values' => ['N', 'Y']
			]),
			new Entity\BooleanField('RECORDED', [
				'values' => ['N', 'Y']
			]),
		];
	}

	/**
	 * Inserts new record into the table, or updates existing record, if record is already found in the table.
	 *
	 * @param array $data Record to be merged to the table.
	 * @return Entity\AddResult
	 */
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

	/**
	 * Should return array of names of fields, that should be used to detect record duplication.
	 * @return array;
	 */
	protected static function getMergeFields()
	{
		return array('CALL_ID', 'USER_ID');
	}
}