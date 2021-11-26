<?php
namespace Bitrix\Blog\Internals;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\NotImplementedException;

Loc::loadMessages(__FILE__);


/**
 * Class BlogUserTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_BlogUser_Query query()
 * @method static EO_BlogUser_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_BlogUser_Result getById($id)
 * @method static EO_BlogUser_Result getList(array $parameters = array())
 * @method static EO_BlogUser_Entity getEntity()
 * @method static \Bitrix\Blog\Internals\EO_BlogUser createObject($setDefaultValues = true)
 * @method static \Bitrix\Blog\Internals\EO_BlogUser_Collection createCollection()
 * @method static \Bitrix\Blog\Internals\EO_BlogUser wakeUpObject($row)
 * @method static \Bitrix\Blog\Internals\EO_BlogUser_Collection wakeUpCollection($rows)
 */
class BlogUserTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_blog_user';
	}
	
	public static function getMap()
	{
		return array(
			new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true,
			)),
			
			new Entity\IntegerField('USER_ID', array(
				'required' => true,
			)),
			
			new Entity\StringField('ALIAS', array(
				'required' => false,
				'validation' => array(__CLASS__, 'validateAlias'),
			)),
			
			new Entity\TextField('DESCRIPTION', array(
				'required' => false,
			)),
			
			new Entity\IntegerField('AVATAR', array(
				'required' => false,
			)),
			
			new Entity\StringField('INTERESTS', array(
				'required' => false,
				'validation' => array(__CLASS__, 'validateInterests'),
			)),
			
			new Entity\DatetimeField('LAST_VISIT', array(
				'required' => false,
			)),
			
			new Entity\DatetimeField('DATE_REG', array(
				'required' => true,
			)),
			
			new Entity\BooleanField('ALLOW_POST', array(
				'required' => true,
				'values' => array('N', 'Y'),
			)),
			
			new Entity\ReferenceField(
				'USER',
				'Bitrix\Main\UserTable',
				array('=this.USER_ID' => 'ref.ID')
			),
		);
	}
	
	/**
	 * Returns validators for ALIAS field.
	 *
	 * @return array
	 */
	public static function validateAlias()
	{
		return array(
			new Entity\Validator\Length(NULL, 255),
		);
	}
	
	/**
	 * Returns validators for INTERESTS field.
	 *
	 * @return array
	 */
	public static function validateInterests()
	{
		return array(
			new Entity\Validator\Length(NULL, 255),
		);
	}
	
	public static function add(array $data)
	{
		throw new NotImplementedException("Use CBlogUser class.");
	}
	
	public static function update($primary, array $data)
	{
		throw new NotImplementedException("Use CBlogUser class.");
	}
	
	public static function delete($primary)
	{
		throw new NotImplementedException("Use CBlogUser class.");
	}
}