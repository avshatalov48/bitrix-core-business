<?
namespace Bitrix\Main\Composite\Debug\Model;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Composite\Debug\Logger;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

/**
 * Class LogTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> URI string(2000) mandatory
 * <li> TITLE string(250) optional
 * <li> CREATED datetime mandatory
 * <li> TYPE string(50) mandatory
 * <li> MESSAGE string optional
 * <li> AJAX bool optional default "N"
 * <li> USER_ID int mandatory default 0
 * <li> PAGE_ID int mandatory default 0
 * </ul>
 *
 * @package Bitrix\Composite
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Log_Query query()
 * @method static EO_Log_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Log_Result getById($id)
 * @method static EO_Log_Result getList(array $parameters = [])
 * @method static EO_Log_Entity getEntity()
 * @method static \Bitrix\Main\Composite\Debug\Model\EO_Log createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Composite\Debug\Model\EO_Log_Collection createCollection()
 * @method static \Bitrix\Main\Composite\Debug\Model\EO_Log wakeUpObject($row)
 * @method static \Bitrix\Main\Composite\Debug\Model\EO_Log_Collection wakeUpCollection($rows)
 */
class LogTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return "b_composite_log";
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
				"title" => "ID"
			),
			"HOST" => array(
				"data_type" => "string",
				"required" => true,
				"validation" => array(__CLASS__, "validateHost"),
				"title" => Loc::getMessage("LOG_ENTITY_HOST_FIELD")
			),
			"URI" => array(
				"data_type" => "string",
				"required" => true,
				"validation" => array(__CLASS__, "validateUri"),
				"title" => Loc::getMessage("LOG_ENTITY_URI_FIELD")
			),
			"TITLE" => array(
				"data_type" => "string",
				"validation" => array(__CLASS__, "validateTitle"),
				"title" => Loc::getMessage("LOG_ENTITY_TITLE_FIELD")
			),
			"CREATED" => array(
				"data_type" => "datetime",
				"required" => true,
				"default_value" => new DateTime(),
				"title" => Loc::getMessage("LOG_ENTITY_CREATED_FIELD"),
			),
			"TYPE" => array(
				"data_type" => "enum",
				"required" => true,
				"values" => Logger::getTypes(),
				"default_value" => Logger::TYPE_MESSAGE,
				"title" => Loc::getMessage("LOG_ENTITY_TYPE_FIELD"),
			),
			"MESSAGE" => array(
				"data_type" => "text",
				"title" => Loc::getMessage("LOG_ENTITY_MESSAGE_FIELD"),
			),
			"MESSAGE_SHORT" => array(
				"data_type" => "text",
				"expression" => array(
					"case when %s = '".Logger::TYPE_CACHE_REWRITING."' then NULL else %s end",
					"TYPE", "MESSAGE"
				),
				"title" => Loc::getMessage("LOG_ENTITY_MESSAGE_FIELD"),
			),

			"AJAX" => array(
				"data_type" => "boolean",
				"values" => array("N", "Y"),
				"title" => Loc::getMessage("LOG_ENTITY_AJAX_FIELD"),
			),
			"USER_ID" => array(
				"data_type" => "integer",
				"required" => true,
				"default_value" => 0,
				"title" => Loc::getMessage("LOG_ENTITY_USER_ID_FIELD"),
			),

			"USER" => array(
				"data_type" => "\\Bitrix\\Main\\UserTable",
				"reference" => array(
					"=this.USER_ID" => "ref.ID"
				),
				"join_type" => "LEFT",
			),

			"PAGE_ID" => array(
				"data_type" => "integer",
				"required" => true,
				"default_value" => 0,
				"title" => Loc::getMessage("LOG_ENTITY_PAGE_ID_FIELD"),
			),
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

	/**
	 * Clears all logging data
	 */
	public static function deleteAll()
	{
		$tableName = static::getTableName();
		$connection = Application::getConnection();
		$connection->queryExecute("DELETE FROM {$tableName}");
	}
}