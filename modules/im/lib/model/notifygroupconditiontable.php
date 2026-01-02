<?php
namespace Bitrix\Im\Model;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Query\Join;

/**
 * Class NotifyGroupConditionTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_NotifyGroupCondition_Query query()
 * @method static EO_NotifyGroupCondition_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_NotifyGroupCondition_Result getById($id)
 * @method static EO_NotifyGroupCondition_Result getList(array $parameters = [])
 * @method static EO_NotifyGroupCondition_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_NotifyGroupCondition createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_NotifyGroupCondition_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_NotifyGroupCondition wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_NotifyGroupCondition_Collection wakeUpCollection($rows)
 */
class NotifyGroupConditionTable extends DataManager
{
	use DeleteByFilterTrait;

	public const FIELD_ID = 'ID';
	public const FIELD_GROUP_ID = 'GROUP_ID';
	public const FIELD_MODULE = 'MODULE';
	public const FIELD_EVENT = 'EVENT';
	public const FIELD_USER_ID = 'USER_ID';
	public const FIELD_DATE_CREATE = 'DATE_CREATE';
	public const RELATION_GROUP = 'GROUP';

	public static function getTableName(): string
	{
		return 'b_im_notify_group_condition';
	}

	public static function getMap(): array
	{
		return [
			(new IntegerField(self::FIELD_ID))
				->configureAutocomplete()
				->configurePrimary()
			,
			(new IntegerField(self::FIELD_GROUP_ID))
				->configureRequired()
			,
			(new StringField(self::FIELD_MODULE))
				->configureRequired()
				->addValidator(new LengthValidator(1, 255))
			,
			(new StringField(self::FIELD_EVENT))
				->configureDefaultValue('')
				->addValidator(new LengthValidator(0, 255))
			,
			(new IntegerField(self::FIELD_USER_ID))
				->configureRequired()
			,
			(new DatetimeField(self::FIELD_DATE_CREATE))
				->configureDefaultValueNow()
			,
			(new Reference(
				self::RELATION_GROUP,
				NotifyGroupTable::class,
				Join::on('this.'.self::FIELD_GROUP_ID, 'ref.'. self::FIELD_ID),
			))
			->configureJoinType(Join::TYPE_INNER)
		];
	}
}