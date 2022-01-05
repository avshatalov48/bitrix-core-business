<?php

namespace Bitrix\Im\Model;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\SystemException,
	Bitrix\Main\Application,
	Bitrix\Main\Error,
	Bitrix\Main\ORM\Data;

Loc::loadMessages(__FILE__);


/**
 * Class OptionUserTable
 *
 * Fields:
 * <ul>
 * <li> USER_ID int mandatory
 * <li> GROUP_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Im
 **/

class OptionUserTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_im_option_user';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 * @throws SystemException
	 */
	public static function getMap(): array
	{
		return [
			'USER_ID' => (new IntegerField('USER_ID', [
				'primary' => true,
			])),
			'NOTIFY_GROUP_ID' => (new IntegerField('NOTIFY_GROUP_ID', [])),
			'GENERAL_GROUP_ID' => (new IntegerField('GENERAL_GROUP_ID', [])),
		];
	}

	/**
	 * Inserts new record into the table, or updates existing record, if record is already found in the table.
	 *
	 * @param array $data Record to be merged to the table.
	 * @return Data\AddResult
	 * @throws SystemException
	 */
	public static function merge(array $data): Data\AddResult
	{
		$result = new Data\AddResult();

		$helper = Application::getConnection()->getSqlHelper();
		$insertData = $data;
		$updateData = $data;
		$mergeFields = static::getMergeFields();
		foreach ($mergeFields as $field)
		{
			unset($updateData[$field]);
		}

		// use save modifiers
		$entity = static::getEntity();
		foreach ($updateData as $fieldName => $value)
		{
			$field = $entity->getField($fieldName);
			$updateData[$fieldName] = $field->modifyValueBeforeSave($value, $updateData);
		}
		foreach ($insertData as $fieldName => $value)
		{
			$field = $entity->getField($fieldName);
			$insertData[$fieldName] = $field->modifyValueBeforeSave($value, $insertData);
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

	protected static function getMergeFields(): array
	{
		return ['USER_ID'];
	}
}