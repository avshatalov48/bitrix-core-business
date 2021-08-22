<?php
namespace Bitrix\Im\Model;

use Bitrix\Main,
	Bitrix\Main\Entity;

/**
 * Class ConferenceTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Conference_Query query()
 * @method static EO_Conference_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Conference_Result getById($id)
 * @method static EO_Conference_Result getList(array $parameters = array())
 * @method static EO_Conference_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_Conference createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_Conference_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_Conference wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_Conference_Collection wakeUpCollection($rows)
 */
class ConferenceTable extends Main\Entity\DataManager
{
	public static function getTableName(): string
	{
		return 'b_im_conference';
	}

	public static function getMap(): array
	{
		return array(
			new Entity\IntegerField('ID', array(
				'primary' => true,
				'autocomplete' => true
			)),
			new Entity\IntegerField('ALIAS_ID', array(
				'required' => true
			)),
			new \Bitrix\Main\Entity\CryptoField('PASSWORD', array(
				'crypto_enabled' => static::cryptoEnabled("PASSWORD"),
			)),
			new Entity\TextField('INVITATION'),
			new Entity\DatetimeField('CONFERENCE_START'),
			new Entity\DatetimeField('CONFERENCE_END'),
			new Entity\StringField('IS_BROADCAST', array(
				'default_value' => 'N'
			)),
			new Entity\ReferenceField(
				'ALIAS',
				'Bitrix\Im\Model\AliasTable',
				array('=this.ALIAS_ID' => 'ref.ID')
			)
		);
	}
}