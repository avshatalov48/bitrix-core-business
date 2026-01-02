<?php
namespace Bitrix\Im\Model;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\OneToMany;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Text\Emoji;

/**
 * Class NotifyGroupTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_NotifyGroup_Query query()
 * @method static EO_NotifyGroup_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_NotifyGroup_Result getById($id)
 * @method static EO_NotifyGroup_Result getList(array $parameters = [])
 * @method static EO_NotifyGroup_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_NotifyGroup createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_NotifyGroup_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_NotifyGroup wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_NotifyGroup_Collection wakeUpCollection($rows)
 */
class NotifyGroupTable extends DataManager
{
	use DeleteByFilterTrait;

	public const FIELD_ID = 'ID';
	public const FIELD_USER_ID = 'USER_ID';
	public const FIELD_TITLE = 'TITLE';
	public const FIELD_DATE_CREATE = 'DATE_CREATE';
	public const FIELD_DATE_UPDATE = 'DATE_UPDATE';
	public const RELATION_CONDITIONS = 'CONDITIONS';

	public static function getTableName(): string
	{
		return 'b_im_notify_group';
	}

	public static function getMap(): array
	{
		return [
			(new IntegerField(self::FIELD_ID))
				->configureAutocomplete()
				->configurePrimary()
			,
			(new IntegerField(self::FIELD_USER_ID))
				->configureRequired()
			,
			(new StringField(self::FIELD_TITLE))
				->configureRequired()
				->addValidator(new LengthValidator(1, 255))
				->addSaveDataModifier(fn($value) => Emoji::encode($value))
				->addFetchDataModifier(fn($value) => Emoji::decode($value))
			,
			(new DatetimeField(self::FIELD_DATE_CREATE))
				->configureDefaultValueNow()
			,
			(new DatetimeField(self::FIELD_DATE_UPDATE))
				->configureNullable()
			,
			(new OneToMany(
				self::RELATION_CONDITIONS,
				NotifyGroupConditionTable::class,
				NotifyGroupConditionTable::RELATION_GROUP,
			))
			->configureJoinType(Join::TYPE_INNER)
		];
	}
}