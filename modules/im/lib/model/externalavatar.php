<?php
namespace Bitrix\Im\Model;

use Bitrix\Main;

/**
 * Class ExternalAvatarTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> LINK_MD5 string(32) mandatory
 * <li> AVATAR_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ExternalAvatar_Query query()
 * @method static EO_ExternalAvatar_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_ExternalAvatar_Result getById($id)
 * @method static EO_ExternalAvatar_Result getList(array $parameters = array())
 * @method static EO_ExternalAvatar_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_ExternalAvatar createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_ExternalAvatar_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_ExternalAvatar wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_ExternalAvatar_Collection wakeUpCollection($rows)
 */

class ExternalAvatarTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_external_avatar';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				//'title' => Loc::getMessage('EXTERNAL_AVATAR_ENTITY_ID_FIELD'),
			),
			'LINK_MD5' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateLinkMd5'),
				//'title' => Loc::getMessage('EXTERNAL_AVATAR_ENTITY_LINK_MD5_FIELD'),
			),
			'AVATAR_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				//'title' => Loc::getMessage('EXTERNAL_AVATAR_ENTITY_AVATAR_ID_FIELD'),
			),
			'FILE' => array(
				'data_type' => 'Bitrix\Main\FileTable',
				'reference' => array('=this.AVATAR_ID' => 'ref.ID'),
				'join_type' => 'LEFT',
			),
		);
	}
	/**
	 * Returns validators for LINK_MD5 field.
	 *
	 * @return array
	 */
	public static function validateLinkMd5()
	{
		return array(
			new Main\Entity\Validator\Length(null, 32),
		);
	}
}