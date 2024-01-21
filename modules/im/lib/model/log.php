<?php
namespace Bitrix\Im\Model;

use Bitrix\Im\V2\Common\MultiplyInsertTrait;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Data\Internal\MergeTrait;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Type\DateTime;

/**
 * Class LogTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_ID int mandatory
 * <li> ENTITY_TYPE string(50) optional
 * <li> ENTITY_ID int optional
 * <li> EVENT string(50) mandatory
 * <li> DATE_CREATE datetime mandatory
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Log_Query query()
 * @method static EO_Log_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Log_Result getById($id)
 * @method static EO_Log_Result getList(array $parameters = [])
 * @method static EO_Log_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_Log createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_Log_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_Log wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_Log_Collection wakeUpCollection($rows)
 */

class LogTable extends DataManager
{
	use MergeTrait;
	use MultiplyInsertTrait;
	use DeleteByFilterTrait;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_log';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
				]
			),
			'USER_ID' => new IntegerField(
				'USER_ID',
				[
					'required' => true,
				]
			),
			'ENTITY_TYPE' => new StringField(
				'ENTITY_TYPE',
				[
					'validation' => function()
					{
						return[
							new LengthValidator(null, 50),
						];
					},
				]
			),
			'ENTITY_ID' => new IntegerField(
				'ENTITY_ID',
				[
				]
			),
			'EVENT' => new StringField(
				'EVENT',
				[
					'required' => true,
					'validation' => function()
					{
						return[
							new LengthValidator(null, 50),
						];
					},
				]
			),
			'DATE_CREATE' => new DatetimeField(
				'DATE_CREATE',
				[
					'required' => true,
					'default_value' => static function() {
						return new DateTime();
					}
				]
			),
			'DATE_DELETE' => new DatetimeField(
				'DATE_DELETE',
				[
				]
			),
		];
	}
}