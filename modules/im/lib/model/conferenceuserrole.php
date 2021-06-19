<?php
namespace Bitrix\Im\Model;

use Bitrix\Main,
	Bitrix\Main\Entity;

class ConferenceUserRoleTable extends Main\Entity\DataManager
{
	public static function getTableName(): string
	{
		return 'b_im_conference_user_role';
	}

	public static function getMap(): array
	{
		return array(
			new Entity\IntegerField('CONFERENCE_ID', [
				'primary' => true
			]),
			new Entity\IntegerField('USER_ID', [
				'primary' => true
			]),
			new Entity\StringField('ROLE'),
		);
	}
}