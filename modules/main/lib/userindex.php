<?php
namespace Bitrix\Main;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class SessionIndexTable
 *
 * Fields:
 * <ul>
 * <li> SESSION_ID int mandatory
 * <li> SEARCH_CONTENT string optional
 * </ul>
 *
 * @package Bitrix\Main
 **/

class UserIndexTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_user_index';
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
			'SEARCH_USER_CONTENT' => array(
				'data_type' => 'text',
			),
			'SEARCH_ADMIN_CONTENT' => array(
				'data_type' => 'text',
			),
			'SEARCH_DEPARTMENT_CONTENT' => array(
				'data_type' => 'text',
			),
			'NAME' => array(
				'data_type' => 'string'
			),
			'SECOND_NAME' => array(
				'data_type' => 'string'
			),
			'LAST_NAME' => array(
				'data_type' => 'string'
			),
			'WORK_POSITION' => array(
				'data_type' => 'string'
			),
			'UF_DEPARTMENT_NAME' => array(
				'data_type' => 'string'
			),
		);
	}

	protected static function getMergeFields()
	{
		return array('USER_ID');
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