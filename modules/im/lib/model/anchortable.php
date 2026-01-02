<?php

declare(strict_types=1);

namespace Bitrix\Im\Model;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Query\Join;

class AnchorTable extends DataManager
{
	use DeleteByFilterTrait;

	public static function getTableName(): string
	{
		return 'b_im_anchor';
	}

	public static function getMap(): array
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),

			(new IntegerField('MESSAGE_ID'))
				->configureRequired(),

			(new IntegerField('CHAT_ID'))
				->configureRequired(),

			(new IntegerField('USER_ID'))
				->configureRequired(),

			(new IntegerField('FROM_USER_ID'))
				->configureRequired(),

			(new StringField('TYPE'))
				->configureRequired(),

			(new StringField('SUB_TYPE'))
				->configureNullable()
				->configureDefaultValue(null),

			(new Reference('MESSAGE', MessageTable::getEntity(), Join::on('this.MESSAGE_ID', 'ref.ID')))
				->configureJoinType(Join::TYPE_INNER),
		];
	}
}