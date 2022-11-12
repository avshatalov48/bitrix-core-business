<?php
namespace Bitrix\UI\Avatar\Mask;

use Bitrix\Main;
use Bitrix\Main\FileTable;
use Bitrix\Main\ORM;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use Bitrix\UI\Avatar;

class ItemToFileTable extends OrmDataManager
{
	public static function getTableName(): string
	{
		return 'b_ui_avatar_mask_item_applied_to';
	}

	public static function getMap(): array
	{
		return array(
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete()
			,

			(new IntegerField('ORIGINAL_FILE_ID'))->configureRequired(),
			(new IntegerField('FILE_ID'))->configureRequired(),
			(new IntegerField('ITEM_ID'))->configureRequired(),

			(new IntegerField('USER_ID')),

			(new DatetimeField('TIMESTAMP_X'))
				->configureRequired()
				->configureDefaultValue(function() {
					return new DateTime();
				}
			),

			(new Reference(
				'FILE',
				FileTable::class,
				Join::on('this.FILE_ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_INNER),

			(new Reference(
				'ITEM',
				ItemTable::class,
				Join::on('this.ITEM_ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_INNER),

			(new Reference(
				'USER',
				Main\UserTable::class,
				Join::on('this.USER_ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_LEFT),
		);
	}
}