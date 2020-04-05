<?php
namespace Bitrix\Main;

use Bitrix\Main;

/**
 * Class UserIndexSelectorTable
 *
 * Fields:
 * <ul>
 * <li> USER_ID int mandatory
 * <li> SEARCH_SELECTOR_CONTENT string optional
 * </ul>
 *
 * @package Bitrix\Main
 **/

class UserIndexSelectorTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_user_index_selector';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */

	public static function getMap()
	{
		return array(
			'USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'SEARCH_SELECTOR_CONTENT' => array(
				'data_type' => 'text',
			),
		);
	}

	protected static function getMergeFields()
	{
		return array('USER_ID');
	}

	public static function merge(array $data)
	{
		global $DB;

		$result = new Entity\AddResult();

		$helper = Application::getConnection()->getSqlHelper();
		$insertData = $data;
		$updateData = $data;
		$mergeFields = static::getMergeFields();

		foreach ($mergeFields as $field)
		{
			unset($updateData[$field]);
		}

		if (isset($updateData['SEARCH_SELECTOR_CONTENT']))
		{
			$value = $DB->forSql($updateData['SEARCH_SELECTOR_CONTENT']);
			$encryptedValue = sha1($updateData['SEARCH_SELECTOR_CONTENT']);
			$updateData['SEARCH_SELECTOR_CONTENT'] = new \Bitrix\Main\DB\SqlExpression("IF(SHA1(SEARCH_SELECTOR_CONTENT) = '{$encryptedValue}', SEARCH_SELECTOR_CONTENT, '{$value}')");
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