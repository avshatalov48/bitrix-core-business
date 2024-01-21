<?

namespace Bitrix\Main\SidePanel;

use Bitrix\Main\ORM\Data;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\Validators\ForeignValidator;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;

/**
 * Class ToolbarTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Toolbar_Query query()
 * @method static EO_Toolbar_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Toolbar_Result getById($id)
 * @method static EO_Toolbar_Result getList(array $parameters = [])
 * @method static EO_Toolbar_Entity getEntity()
 * @method static \Bitrix\Main\SidePanel\EO_Toolbar createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\SidePanel\EO_Toolbar_Collection createCollection()
 * @method static \Bitrix\Main\SidePanel\EO_Toolbar wakeUpObject($row)
 * @method static \Bitrix\Main\SidePanel\EO_Toolbar_Collection wakeUpCollection($rows)
 */
class ToolbarTable extends Data\DataManager
{
	/**
	 * @inheritdoc
	 */
	public static function getTableName()
	{
		return 'b_sidepanel_toolbar';
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
			(new IntegerField('USER_ID'))
				->configureRequired()
				->addValidator(new ForeignValidator(UserTable::getEntity()->getField('ID')))
			,
			(new StringField('CONTEXT'))
				->configureRequired()
				->configureSize(50)
				->configureFormat('/^[a-zA-Z0-9_:-]+$/')
				->addValidator(new LengthValidator(2, 50))
				->addSaveDataModifier(function ($value) {
					return mb_strtolower($value);
				})
			,
			(new BooleanField('COLLAPSED'))
				->configureValues(0, 1)
				->configureDefaultValue(1)
			,
			(new DatetimeField('CREATED_DATE'))
				->configureDefaultValue(static function () {
					return new DateTime();
				})
			,
			(new Reference(
				'USER',
				UserTable::class,
				Join::on('this.USER_ID', 'ref.ID'),
				['join_type' => Join::TYPE_INNER]
			)),
		];
	}
}
