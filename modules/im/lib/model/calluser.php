<?php

namespace Bitrix\Im\Model;

use Bitrix\Main\Application;
use Bitrix\Main\Entity;
use Bitrix\Main\Error;

class CallUserTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_im_call_user';
	}

	public static function getMap()
	{
		return array(
			new Entity\IntegerField('CALL_ID', array(
				'primary' => true
			)),
			new Entity\IntegerField('USER_ID', array(
				'primary' => true
			)),
			new Entity\StringField('STATE'),
			new Entity\DatetimeField('LAST_SEEN')
		);
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