<?php

namespace Bitrix\Mail;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class UserRelationsTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserRelations_Query query()
 * @method static EO_UserRelations_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_UserRelations_Result getById($id)
 * @method static EO_UserRelations_Result getList(array $parameters = array())
 * @method static EO_UserRelations_Entity getEntity()
 * @method static \Bitrix\Mail\EO_UserRelations createObject($setDefaultValues = true)
 * @method static \Bitrix\Mail\EO_UserRelations_Collection createCollection()
 * @method static \Bitrix\Mail\EO_UserRelations wakeUpObject($row)
 * @method static \Bitrix\Mail\EO_UserRelations_Collection wakeUpCollection($rows)
 */
class UserRelationsTable extends Entity\DataManager
{

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_mail_user_relations';
	}

	public static function getMap()
	{
		return array(
			'TOKEN' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('mail_ur_entity_token_field'),
				'primary'   => true
			),
			'SITE_ID' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('mail_ur_entity_site_field')
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'title'     => Localization\Loc::getMessage('mail_ur_entity_user_id_field'),
				'required'  => true
			),
			'ENTITY_TYPE' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('mail_ur_entity_entity_type_field'),
				'required'  => true
			),
			'ENTITY_ID' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('mail_ur_entity_entity_id_field'),
			),
			'ENTITY_LINK' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('mail_ur_entity_entity_link_field'),
			),
			'BACKURL' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('mail_ur_entity_backurl_field'),
			),
			'USER' => array(
				'data_type' => 'Bitrix\Main\User',
				'reference' => array('=this.USER_ID' => 'ref.ID'),
			),
		);
	}

}
