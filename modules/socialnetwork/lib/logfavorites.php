<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;

/**
 * Class LogFavoritesTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_LogFavorites_Query query()
 * @method static EO_LogFavorites_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_LogFavorites_Result getById($id)
 * @method static EO_LogFavorites_Result getList(array $parameters = [])
 * @method static EO_LogFavorites_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\EO_LogFavorites createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\EO_LogFavorites_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\EO_LogFavorites wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\EO_LogFavorites_Collection wakeUpCollection($rows)
 */
class LogFavoritesTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_log_favorites';
	}

	public static function getMap()
	{
		$fieldsMap = array(
			'USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'LOG_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
		);

		return $fieldsMap;
	}
}
