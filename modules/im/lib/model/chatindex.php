<?php
namespace Bitrix\Im\Model;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Application,
	Bitrix\Main\Entity,
	Bitrix\Main\Error;

Loc::loadMessages(__FILE__);

/**
 * Class ChatIndexTable
 *
 * Fields:
 * <ul>
 * <li> CHAT_ID int mandatory
 * <li> SEARCH_CONTENT string optional
 * </ul>
 *
 * @package Bitrix\Im
 **/

class ChatIndexTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_chat_index';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'CHAT_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'SEARCH_TITLE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateTitle'),
			),
			'SEARCH_CONTENT' => array(
				'data_type' => 'text',
			),
			'SEARCH_USERS' => array(
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
		return array('CHAT_ID');
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