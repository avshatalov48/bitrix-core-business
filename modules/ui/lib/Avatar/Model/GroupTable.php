<?php
namespace Bitrix\UI\Avatar\Model;

use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\Type\DateTime;

class GroupTable extends ORM\Data\DataManager
{
	public static function getTableName()
	{
		return 'b_ui_avatar_mask_group';
	}

	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),
			(new DatetimeField('TIMESTAMP_X'))
				->configureRequired()
				->configureDefaultValue(function() {
					return new DateTime();
				}),
			(new StringField('OWNER_TYPE'))->configureRequired()->configureSize(100),
			(new StringField('OWNER_ID', []))->configureRequired()->configureSize(20),
			(new IntegerField('SORT', []))->configureDefaultValue(100),

			(new StringField('TITLE'))->configureRequired(),
			new TextField('DESCRIPTION'),
		];
	}
}
