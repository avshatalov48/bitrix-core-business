<?php
namespace Bitrix\B24connector;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;

/**
 * ORM class describes the limitation of displaying B24-widgets on specific sites.
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ButtonSite_Query query()
 * @method static EO_ButtonSite_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_ButtonSite_Result getById($id)
 * @method static EO_ButtonSite_Result getList(array $parameters = array())
 * @method static EO_ButtonSite_Entity getEntity()
 * @method static \Bitrix\B24connector\EO_ButtonSite createObject($setDefaultValues = true)
 * @method static \Bitrix\B24connector\EO_ButtonSite_Collection createCollection()
 * @method static \Bitrix\B24connector\EO_ButtonSite wakeUpObject($row)
 * @method static \Bitrix\B24connector\EO_ButtonSite_Collection wakeUpCollection($rows)
 */
class ButtonSiteTable extends DataManager
{
	/**
	 * Returns table name
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_b24connector_button_site';
	}

	/**
	 * Returns table structure
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true,
			]),
			new IntegerField('BUTTON_ID', [
				'required' => true,
			]),
			new StringField('SITE_ID', [
				'required' => true,
			]),
		];
	}

	/**
	 * Returns list of allowed sites for every button.
	 * @return array<int, string[]>
	 */
	public static function getAllRestrictions()
	{
		$result = [];
		$rows = static::getList();
		while ($row = $rows->fetch())
		{
			$buttonId = $row['BUTTON_ID'];
			$siteId = $row['SITE_ID'];

			if (!isset($result[$buttonId]))
			{
				$result[$buttonId] = [];
			}

			$result[$buttonId][] = $siteId;
		}
		return $result;
	}

	public static function deleteByButtonId($buttonId)
	{
		$buttonId = (int)$buttonId;
		if ($buttonId <= 0)
		{
			return;
		}

		$rows = static::getList([
			'select' => ['ID'],
			'filter' => ['=BUTTON_ID' => $buttonId]
		]);
		while ($row = $rows->fetch())
		{
			static::delete($row['ID']);
		}
	}
}
