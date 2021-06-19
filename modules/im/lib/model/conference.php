<?php
namespace Bitrix\Im\Model;

use Bitrix\Main,
	Bitrix\Main\Entity;

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