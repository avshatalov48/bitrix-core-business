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
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ChatIndex_Query query()
 * @method static EO_ChatIndex_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_ChatIndex_Result getById($id)
 * @method static EO_ChatIndex_Result getList(array $parameters = array())
 * @method static EO_ChatIndex_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_ChatIndex createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_ChatIndex_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_ChatIndex wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_ChatIndex_Collection wakeUpCollection($rows)
 */

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

		if (method_exists($helper, 'getConditionalAssignment'))
		{
			if (isset($updateData['SEARCH_CONTENT']))
			{
				$updateData['SEARCH_CONTENT'] = new Main\DB\SqlExpression($helper->getConditionalAssignment('SEARCH_CONTENT', $updateData['SEARCH_CONTENT']));
			}

			if (isset($updateData['SEARCH_TITLE']))
			{
				$updateData['SEARCH_TITLE'] = new Main\DB\SqlExpression($helper->getConditionalAssignment('SEARCH_TITLE', $updateData['SEARCH_TITLE']));
			}
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

	public static function updateIndex($id, $primaryField, array $updateData): Main\ORM\Data\UpdateResult
	{
		$result = new Main\ORM\Data\UpdateResult();
		$helper = Application::getConnection()->getSqlHelper();

		if (isset($updateData[$primaryField]))
		{
			unset($updateData[$primaryField]);
		}

		if (method_exists($helper, 'getConditionalAssignment'))
		{
			if (isset($updateData['SEARCH_CONTENT']))
			{
				$updateData['SEARCH_CONTENT'] = new Main\DB\SqlExpression($helper->getConditionalAssignment('SEARCH_CONTENT', $updateData['SEARCH_CONTENT']));
			}

			if (isset($updateData['SEARCH_TITLE']))
			{
				$updateData['SEARCH_TITLE'] = new Main\DB\SqlExpression($helper->getConditionalAssignment('SEARCH_TITLE', $updateData['SEARCH_TITLE']));
			}
		}

		$update = $helper->prepareUpdate(
			static::getTableName(),
			$updateData
		);

		if ($update[0] !== '')
		{
			Application::getConnection()->query(
				"UPDATE " . static::getTableName() . " SET " . $update[0] . " WHERE " . $primaryField . " = " . $id
			);
		}

		return $result;
	}
}