<?php
namespace Bitrix\Im\Model;

use Bitrix\Disk\Internals\FileTable;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;

/**
 * Class ChatFileTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> MESSAGE_ID int optional
 * <li> CHAT_ID int optional
 * <li> TYPE string(50) optional
 * <li> DISK_FILE_ID int optional
 * <li> DATE_CREATE datetime mandatory
 * <li> AUTHOR_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_LinkFile_Query query()
 * @method static EO_LinkFile_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_LinkFile_Result getById($id)
 * @method static EO_LinkFile_Result getList(array $parameters = [])
 * @method static EO_LinkFile_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_LinkFile createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_LinkFile_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_LinkFile wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_LinkFile_Collection wakeUpCollection($rows)
 */

class LinkFileTable extends DataManager
{
	use DeleteByFilterTrait;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_link_file';
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
			'MESSAGE_ID' => new IntegerField(
				'MESSAGE_ID',
				[
				]
			),
			'CHAT_ID' => new IntegerField(
				'CHAT_ID',
				[
				]
			),
			'SUBTYPE' => new StringField(
				'SUBTYPE',
				[
					'validation' => [__CLASS__, 'validateSubtype'],
				]
			),
			'DISK_FILE_ID' => new IntegerField(
				'DISK_FILE_ID',
				[
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
			'AUTHOR_ID' => new IntegerField(
				'AUTHOR_ID',
				[
					'required' => true,
				]
			),
			'FILE' => (new Reference(
				'FILE',
				FileTable::class,
				Join::on('this.DISK_FILE_ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_INNER),
		];
	}

	/**
	 * Returns validators for TYPE field.
	 *
	 * @return array
	 */
	public static function validateSubtype(): array
	{
		return [
			new LengthValidator(null, 50),
		];
	}
}