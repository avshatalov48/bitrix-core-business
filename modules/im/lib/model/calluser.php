<?php

namespace Bitrix\Im\Model;

use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Data\AddResult;

/**
 * Class CallUserTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CallUser_Query query()
 * @method static EO_CallUser_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_CallUser_Result getById($id)
 * @method static EO_CallUser_Result getList(array $parameters = [])
 * @method static EO_CallUser_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_CallUser createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_CallUser_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_CallUser wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_CallUser_Collection wakeUpCollection($rows)
 */
class CallUserTable extends DataManager
{
	public static function getTableName(): string
	{
		return 'b_im_call_user';
	}

	public static function getMap(): array
	{
		return [
			new IntegerField('CALL_ID', [
				'primary' => true
			]),
			new IntegerField('USER_ID', [
				'primary' => true
			]),
			new StringField('STATE'),
			new DatetimeField('FIRST_JOINED'),
			new DatetimeField('LAST_SEEN'),
			new BooleanField('IS_MOBILE', [
				'values' => ['N', 'Y']
			]),
			new BooleanField('SHARED_SCREEN', [
				'values' => ['N', 'Y']
			]),
			new BooleanField('RECORDED', [
				'values' => ['N', 'Y']
			]),
		];
	}

	/**
	 * Inserts new record into the table, or updates existing record, if record is already found in the table.
	 *
	 * @param array $data Record to be merged to the table.
	 * @return AddResult
	 */
	public static function merge(array $data): AddResult
	{
		$result = new AddResult();

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
	protected static function getMergeFields(): array
	{
		return ['CALL_ID', 'USER_ID'];
	}
}