<?php
namespace Bitrix\Im\Model;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Type\DateTime;

/**
 * Class FileTemporaryTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> DISK_FILE_ID int mandatory
 * <li> DATE_CREATE datetime mandatory
 * <li> SOURCE string(50) mandatory
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_FileTemporary_Query query()
 * @method static EO_FileTemporary_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_FileTemporary_Result getById($id)
 * @method static EO_FileTemporary_Result getList(array $parameters = [])
 * @method static EO_FileTemporary_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_FileTemporary createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_FileTemporary_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_FileTemporary wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_FileTemporary_Collection wakeUpCollection($rows)
 */

class FileTemporaryTable extends DataManager
{
	use DeleteByFilterTrait;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_file_temporary';
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
			'DISK_FILE_ID' => new IntegerField(
				'DISK_FILE_ID',
				[
					'required' => true,
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
			'SOURCE' => new StringField(
				'SOURCE',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateSource'],
				]
			),
		];
	}

	/**
	 * Returns validators for SOURCE field.
	 *
	 * @return array
	 */
	public static function validateSource(): array
	{
		return [
			new LengthValidator(null, 50),
		];
	}
}