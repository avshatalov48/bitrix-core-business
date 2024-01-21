<?

namespace Bitrix\Main\SidePanel;

use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\Validators\ForeignValidator;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\Text\Emoji;
use Bitrix\Main\Type\DateTime;

/**
 * Class ToolbarItemTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ToolbarItem_Query query()
 * @method static EO_ToolbarItem_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_ToolbarItem_Result getById($id)
 * @method static EO_ToolbarItem_Result getList(array $parameters = [])
 * @method static EO_ToolbarItem_Entity getEntity()
 * @method static \Bitrix\Main\SidePanel\EO_ToolbarItem createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\SidePanel\EO_ToolbarItem_Collection createCollection()
 * @method static \Bitrix\Main\SidePanel\EO_ToolbarItem wakeUpObject($row)
 * @method static \Bitrix\Main\SidePanel\EO_ToolbarItem_Collection wakeUpCollection($rows)
 */
class ToolbarItemTable extends Data\DataManager
{
	use DeleteByFilterTrait;

	/**
	 * @inheritdoc
	 */
	public static function getTableName()
	{
		return 'b_sidepanel_toolbar_item';
	}

	/**
	 * @inheritdoc
	 */
	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete()
			,
			(new IntegerField('TOOLBAR_ID'))
				->configureRequired()
				->addValidator(new ForeignValidator(ToolbarTable::getEntity()->getField('ID')))
			,
			(new StringField('URL'))
				->configureRequired()
				->configureSize(2000)
				->configureFormat('~^/~')
				->addValidator(new LengthValidator(1, 2000))
			,
			(new StringField('TITLE'))
				->configureRequired()
				->configureSize(255)
				->addValidator(new LengthValidator(1, 255))
				->addSaveDataModifier([Emoji::class, 'encode'])
				->addFetchDataModifier([Emoji::class, 'decode'])
			,
			(new StringField('ENTITY_TYPE'))
				->configureRequired()
				->configureSize(50)
				->configureFormat('/^[a-zA-Z0-9_:-]+$/')
				->addValidator(new LengthValidator(2, 50))
				->addSaveDataModifier(function ($value) {
					return mb_strtolower($value);
				})
			,
			(new StringField('ENTITY_ID'))
				->configureRequired()
				->configureSize(50)
				->configureFormat('/^[a-zA-Z0-9_:-]+$/')
				->addValidator(new LengthValidator(1, 50))
				->addSaveDataModifier(function ($value) {
					return mb_strtolower($value);
				})
			,
			(new DatetimeField('CREATED_DATE'))
				->configureDefaultValue(static function () {
					return new DateTime();
				})
			,
			(new DatetimeField('LAST_USE_DATE'))
				->configureDefaultValue(static function () {
					return new DateTime();
				})
			,
		];
	}
}
