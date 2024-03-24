<?php

namespace Bitrix\Im\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;

/**
 * Class BlockUserTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_BlockUser_Query query()
 * @method static EO_BlockUser_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_BlockUser_Result getById($id)
 * @method static EO_BlockUser_Result getList(array $parameters = array())
 * @method static EO_BlockUser_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_BlockUser createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_BlockUser_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_BlockUser wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_BlockUser_Collection wakeUpCollection($rows)
 */
class BlockUserTable extends Entity\DataManager
{
	use DeleteByFilterTrait;

	public static function getTableName()
	{
		return 'b_im_block_user';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				//'title' => Loc::getMessage('BLOCK_USER_ENTITY_ID_FIELD'),
			),
			'CHAT_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				//'title' => Loc::getMessage('BLOCK_USER_ENTITY_CHAT_ID_FIELD'),
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				//'title' => Loc::getMessage('BLOCK_USER_ENTITY_USER_ID_FIELD'),
			),
			'BLOCK_DATE' => array(
				'data_type' => 'datetime'
			),
		);
	}
}