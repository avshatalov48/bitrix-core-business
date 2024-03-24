<?php
namespace Bitrix\UI\Avatar\Model;

use Bitrix\UI\Avatar;
use Bitrix\Main;

class AccessTable extends OrmDataManager
{
	public static function getTableName()
	{
		return 'b_ui_avatar_mask_access';
	}

	public static function getMap()
	{
		return array(
			(new Main\ORM\Fields\IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),

			(new Main\ORM\Fields\IntegerField('ITEM_ID'))->configureRequired(),
			(new Main\ORM\Fields\StringField('ACCESS_CODE', []))->configureRequired()->configureSize(50),

			(new Main\ORM\Fields\Relations\Reference(
				'USER_ACCESS',
				Main\UserAccessTable::class,
				(Main\ORM\Query\Join::on('this.ACCESS_CODE', 'ref.ACCESS_CODE')
					->where('this.ACCESS_CODE', '=', 'UA')
					->logic('or')
				)
			))->configureJoinType(Main\ORM\Query\Join::TYPE_INNER),
		);
	}
}
