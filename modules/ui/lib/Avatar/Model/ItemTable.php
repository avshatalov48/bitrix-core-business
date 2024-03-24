<?php
namespace Bitrix\UI\Avatar\Model;

use Bitrix\Main\FileTable;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use Bitrix\UI\Avatar;

class ItemTable extends OrmDataManager
{
	public static function getTableName(): string
	{
		return 'b_ui_avatar_mask_item';
	}

	public static function getMap(): array
	{
		return array(
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),
			(new IntegerField('FILE_ID'))->configureRequired(),

			(new StringField('OWNER_TYPE'))->configureRequired()->configureSize(100),
			(new StringField('OWNER_ID', []))->configureRequired()->configureSize(20),

			new StringField('GROUP_ID'),

			new StringField('TITLE'),
			new TextField('DESCRIPTION'),
			(new IntegerField('SORT'))->configureDefaultValue(100),

			(new DatetimeField('TIMESTAMP_X'))
				->configureRequired()
				->configureDefaultValue(function() {
					return new DateTime();
				}),

			(new Reference(
				'FILE',
				FileTable::class,
				Join::on('this.FILE_ID', 'ref.ID')
			))->configureJoinType(Join::TYPE_INNER),

			(new Reference(
				'SHARED_FOR',
				Avatar\Model\AccessTable::class,
				Join::on('this.ID', 'ref.ITEM_ID')
			))->configureJoinType(Join::TYPE_INNER),

			(new Reference(
				'RECENTLY_USED_BY',
				Avatar\Model\RecentlyUsedTable::class,
				Join::on('this.ID', 'ref.ITEM_ID')
			))->configureJoinType(Join::TYPE_INNER)
		);
	}
}
