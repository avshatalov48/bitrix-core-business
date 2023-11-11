<?php
namespace Bitrix\Iblock;

use Bitrix\Main,
	Bitrix\Main\ORM,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class PropertyFeatureTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> PROPERTY_ID int mandatory
 * <li> MODULE_ID string(50) mandatory
 * <li> FEATURE_ID string(100) mandatory
 * <li> IS_ENABLED bool optional default 'N'
 * <li> PROPERTY reference to {@link \Bitrix\Iblock\PropertyTable}
 * </ul>
 *
 * @package Bitrix\Iblock
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_PropertyFeature_Query query()
 * @method static EO_PropertyFeature_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_PropertyFeature_Result getById($id)
 * @method static EO_PropertyFeature_Result getList(array $parameters = [])
 * @method static EO_PropertyFeature_Entity getEntity()
 * @method static \Bitrix\Iblock\EO_PropertyFeature createObject($setDefaultValues = true)
 * @method static \Bitrix\Iblock\EO_PropertyFeature_Collection createCollection()
 * @method static \Bitrix\Iblock\EO_PropertyFeature wakeUpObject($row)
 * @method static \Bitrix\Iblock\EO_PropertyFeature_Collection wakeUpCollection($rows)
 */

class PropertyFeatureTable extends ORM\Data\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_iblock_property_feature';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => new ORM\Fields\IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('PROPERTY_FEATURE_ENTITY_ID_FIELD')
			]),
			'PROPERTY_ID' => new ORM\Fields\IntegerField('PROPERTY_ID', [
				'title' => Loc::getMessage('PROPERTY_FEATURE_ENTITY_PROPERTY_ID_FIELD')
			]),
			'MODULE_ID' => new ORM\Fields\StringField('MODULE_ID', [
				'validation' => [__CLASS__, 'validateModuleId'],
				'title' => Loc::getMessage('PROPERTY_FEATURE_ENTITY_MODULE_ID_FIELD')
			]),
			'FEATURE_ID' => new ORM\Fields\StringField('FEATURE_ID', [
				'validation' => [__CLASS__, 'validateFeatureId'],
				'title' => Loc::getMessage('PROPERTY_FEATURE_ENTITY_FEATURE_ID_FIELD')
			]),
			'IS_ENABLED' => new ORM\Fields\BooleanField('IS_ENABLED', [
				'values' => ['N', 'Y'],
				'default_value' => 'N',
				'title' => Loc::getMessage('PROPERTY_FEATURE_ENTITY_IS_ENABLED_FIELD')
			]),
			'PROPERTY' => new ORM\Fields\Relations\Reference(
				'PROPERTY',
				'\Bitrix\Iblock\Property',
				['=this.PROPERTY_ID' => 'ref.ID']
			)
		];
	}

	/**
	 * Returns validators for MODULE_ID field.
	 *
	 * @return array
	 */
	public static function validateModuleId()
	{
		return [
			new ORM\Fields\Validators\LengthValidator(null, 50),
		];
	}

	/**
	 * Returns validators for FEATURE_ID field.
	 *
	 * @return array
	 */
	public static function validateFeatureId()
	{
		return [
			new ORM\Fields\Validators\LengthValidator(null, 100),
		];
	}

	/**
	 * Delete all features for property.
	 *
	 * @param int $property		Property Id.
	 * @return void
	 */
	public static function deleteByProperty($property)
	{
		$property = (int)$property;
		if ($property <= 0)
			return;
		$conn = Main\Application::getConnection();
		$helper = $conn->getSqlHelper();
		$conn->queryExecute(
			'delete from '.$helper->quote(self::getTableName()).' where '.$helper->quote('PROPERTY_ID').' = '.$property
		);
		unset($helper, $conn);
	}
}