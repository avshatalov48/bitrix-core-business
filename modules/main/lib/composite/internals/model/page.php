<?
namespace Bitrix\Main\Composite\Internals\Model;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

/**
 * Class PageTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CACHE_KEY string(2000) mandatory
 * <li> URI string(2000) mandatory
 * <li> TITLE string(250) optional
 * <li> CREATED datetime mandatory
 * <li> CHANGED datetime mandatory
 * <li> LAST_VIEWED datetime mandatory
 * <li> VIEWS int mandatory
 * <li> REWRITES int mandatory
 * <li> SIZE int mandatory
 * </ul>
 *
 * @package Bitrix\Composite
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Page_Query query()
 * @method static EO_Page_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Page_Result getById($id)
 * @method static EO_Page_Result getList(array $parameters = [])
 * @method static EO_Page_Entity getEntity()
 * @method static \Bitrix\Main\Composite\Internals\Model\EO_Page createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Composite\Internals\Model\EO_Page_Collection createCollection()
 * @method static \Bitrix\Main\Composite\Internals\Model\EO_Page wakeUpObject($row)
 * @method static \Bitrix\Main\Composite\Internals\Model\EO_Page_Collection wakeUpCollection($rows)
 */
class PageTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return "b_composite_page";
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			"ID" => array(
				"data_type" => "integer",
				"primary" => true,
				"autocomplete" => true,
				"title" => "ID",
			),
			"CACHE_KEY" => array(
				"data_type" => "string",
				"required" => true,
				"validation" => array(__CLASS__, "validateCacheKey"),
				"title" => Loc::getMessage("PAGE_ENTITY_CACHE_KEY_FIELD"),
			),
			"HOST" => array(
				"data_type" => "string",
				"required" => true,
				"validation" => array(__CLASS__, "validateHost"),
				"title" => Loc::getMessage("PAGE_ENTITY_HOST_FIELD"),
			),
			"URI" => array(
				"data_type" => "string",
				"required" => true,
				"validation" => array(__CLASS__, "validateUri"),
				"title" => Loc::getMessage("PAGE_ENTITY_URI_FIELD"),
			),
			"TITLE" => array(
				"data_type" => "string",
				"validation" => array(__CLASS__, "validateTitle"),
				"title" => Loc::getMessage("PAGE_ENTITY_TITLE_FIELD"),
			),
			"CREATED" => array(
				"data_type" => "datetime",
				"required" => true,
				"default_value" => new DateTime(),
				"title" => Loc::getMessage("PAGE_ENTITY_CREATED_FIELD"),
			),
			"CHANGED" => array(
				"data_type" => "datetime",
				"required" => true,
				"default_value" => new DateTime(),
				"title" => Loc::getMessage("PAGE_ENTITY_CHANGED_FIELD"),
			),
			"LAST_VIEWED" => array(
				"data_type" => "datetime",
				"required" => true,
				"default_value" => new DateTime(),
				"title" => Loc::getMessage("PAGE_ENTITY_LAST_VIEWED_FIELD"),
			),
			"VIEWS" => array(
				"data_type" => "integer",
				"required" => true,
				"default_value" => 0,
				"title" => Loc::getMessage("PAGE_ENTITY_VIEWS_FIELD"),
			),
			"REWRITES" => array(
				"data_type" => "integer",
				"required" => true,
				"default_value" => 0,
				"title" => Loc::getMessage("PAGE_ENTITY_REWRITES_FIELD"),
			),
			"SIZE" => array(
				"data_type" => "integer",
				"required" => true,
				"default_value" => 0,
				"title" => Loc::getMessage("PAGE_ENTITY_SIZE_FIELD"),
			),
		);
	}

	/**
	 * Returns validators for CACHE_KEY field.
	 *
	 * @return array
	 */
	public static function validateCacheKey()
	{
		return array(
			new Main\Entity\Validator\Length(null, 2000),
		);
	}

	/**
	 * Returns validators for URI field.
	 *
	 * @return array
	 */
	public static function validateHost()
	{
		return array(
			new Main\Entity\Validator\Length(null, 100),
		);
	}

	/**
	 * Returns validators for URI field.
	 *
	 * @return array
	 */
	public static function validateUri()
	{
		return array(
			new Main\Entity\Validator\Length(null, 2000),
		);
	}

	/**
	 * Returns validators for TITLE field.
	 *
	 * @return array
	 */
	public static function validateTitle()
	{
		return array(
			new Main\Entity\Validator\Length(null, 250),
		);
	}

	public static function deleteAll()
	{
		$tableName = static::getTableName();
		$connection = Application::getConnection();
		$connection->queryExecute("DELETE FROM {$tableName}");
	}

	/**
	 * Deletes rows by filter.
	 * @param array $filter Filter does not look like filter in getList. It depends on current implementation.
	 * @internal
	 * @return void
	 */
	public static function deleteBatch(array $filter)
	{
		$tableName = static::getTableName();
		$connection = Application::getConnection();
		if (isset($filter["ID"]) && is_array($filter["ID"]) && !empty($filter["ID"]))
		{
			$ids = implode(",", array_map("intval", $filter["ID"]));
			$connection->queryExecute("DELETE FROM {$tableName} WHERE ID IN ($ids)");
		}
	}
}