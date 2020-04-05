<?
namespace Bitrix\Forum;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

/**
 * Class PermissionTable
 *
 * Fields:
 * <ul>
 * <li> ID int not null auto_increment,
 * <li> FORUM_ID int null,
 * <li> GROUP_ID int null,
 * <li> PERMISSION char(1)
 * </ul>
 *
 * @package Bitrix\Forum
 */
class PermissionTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_forum_perms';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new Entity\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
			new Entity\IntegerField('FORUM_ID'),
			new Entity\IntegerField('GROUP_ID'),
			new Entity\StringField('PERMISSION', ['size' => 1]),
			new Reference("GROUP", \Bitrix\Main\GroupTable::class, Join::on("this.ID", "ref.GROUP_ID"))
		];
	}
}


// A < E < I < M < Q < U < Y
class Permission {
	public const ACCESS_DENIED = "A";
	public const CAN_READ = "E";
	public const CAN_ADD_MESSAGE = "I";
	public const CAN_ADD_TOPIC = "M";
	public const CAN_MODERATE = "Q";
	public const CAN_EDIT = "U";
	public const FULL_ACCESS = "Y";

	/**
	 * Gets types list
	 * @return array
	 */
	public static function getList()
	{
		$res = (new \ReflectionClass(__CLASS__))->getConstants();
		$result = array();
		foreach ($res as $code => $id)
		{
			$result[$id] = $code;
		}
		return $result;
	}
	/**
	 * Gets types list
	 * @return array
	 */
	public static function getTitledList()
	{
		$res = (new \ReflectionClass(__CLASS__))->getConstants();
		$result = array();
		foreach ($res as $code => $id)
		{
			$result[$id] = Loc::getMessage("FORUM_PERMISSION_".$code);
		}
		return $result;
	}
}