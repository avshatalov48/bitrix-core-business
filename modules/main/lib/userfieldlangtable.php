<?php

namespace Bitrix\Main;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM;

/**
 * Class UserFieldLangTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserFieldLang_Query query()
 * @method static EO_UserFieldLang_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UserFieldLang_Result getById($id)
 * @method static EO_UserFieldLang_Result getList(array $parameters = [])
 * @method static EO_UserFieldLang_Entity getEntity()
 * @method static \Bitrix\Main\EO_UserFieldLang createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EO_UserFieldLang_Collection createCollection()
 * @method static \Bitrix\Main\EO_UserFieldLang wakeUpObject($row)
 * @method static \Bitrix\Main\EO_UserFieldLang_Collection wakeUpCollection($rows)
 */
class UserFieldLangTable extends ORM\Data\DataManager
{
	public static function getTableName(): string
	{
		return 'b_user_field_lang';
	}

	public static function getMap(): array
	{
		return [
			(new ORM\Fields\IntegerField('USER_FIELD_ID'))
				->configurePrimary()
				->configureRequired(),
			(new ORM\Fields\StringField('LANGUAGE_ID'))
				->configurePrimary()
				->configureRequired()
				->configureSize(2),
			(new ORM\Fields\StringField('EDIT_FORM_LABEL'))
				->configureSize(255)
				->configureTitle(Loc::getMessage('MAIN_USER_FIELD_LANG_TABLE_EDIT_FORM_LABEL_TITLE')),
			(new ORM\Fields\StringField('LIST_COLUMN_LABEL'))
				->configureSize(255)
				->configureTitle(Loc::getMessage('MAIN_USER_FIELD_LANG_TABLE_LIST_COLUMN_LABEL_TITLE')),
			(new ORM\Fields\StringField('LIST_FILTER_LABEL'))
				->configureSize(255)
				->configureTitle(Loc::getMessage('MAIN_USER_FIELD_LANG_TABLE_LIST_FILTER_LABEL_TITLE')),
			(new ORM\Fields\StringField('ERROR_MESSAGE'))
				->configureSize(255)
				->configureTitle(Loc::getMessage('MAIN_USER_FIELD_LANG_TABLE_ERROR_MESSAGE_TITLE')),
			(new ORM\Fields\StringField('HELP_MESSAGE'))
				->configureSize(255)
				->configureTitle(Loc::getMessage('MAIN_USER_FIELD_LANG_TABLE_HELP_MESSAGE_TITLE')),
			(new ORM\Fields\Relations\Reference(
				'USER_FIELD',
				UserFieldTable::class,
				[
					'=this.USER_FIELD_ID' => 'ref.ID',
				])),
		];
	}
}