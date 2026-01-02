<?php
namespace Bitrix\Im\Model;

use Bitrix\Main;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;


/**
 * Class MessageUuidTable
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_MessageUuid_Query query()
 * @method static EO_MessageUuid_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_MessageUuid_Result getById($id)
 * @method static EO_MessageUuid_Result getList(array $parameters = [])
 * @method static EO_MessageUuid_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_MessageUuid createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_MessageUuid_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_MessageUuid wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_MessageUuid_Collection wakeUpCollection($rows)
 */

class MessageUuidTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_im_message_uuid';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 * @throws Main\SystemException
	 */
	public static function getMap(): array
	{
		return [
			new StringField(
				'UUID',
				[
					'primary' => true,
					'required' => true,
					'size' => 36
				]
			),
			new IntegerField('MESSAGE_ID'),
			new DatetimeField(
				'DATE_CREATE',
				[
					'required' => true,
				]
			),
		];
	}
}