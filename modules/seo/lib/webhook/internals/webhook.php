<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage seoproxy
 * @copyright 2001-2014 Bitrix
 */
namespace Bitrix\Seo\WebHook\Internals;

use Bitrix\Main\Entity;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Type\DateTime;

/**
 * Class WebHookTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_WebHook_Query query()
 * @method static EO_WebHook_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_WebHook_Result getById($id)
 * @method static EO_WebHook_Result getList(array $parameters = array())
 * @method static EO_WebHook_Entity getEntity()
 * @method static \Bitrix\Seo\WebHook\Internals\EO_WebHook createObject($setDefaultValues = true)
 * @method static \Bitrix\Seo\WebHook\Internals\EO_WebHook_Collection createCollection()
 * @method static \Bitrix\Seo\WebHook\Internals\EO_WebHook wakeUpObject($row)
 * @method static \Bitrix\Seo\WebHook\Internals\EO_WebHook_Collection wakeUpCollection($rows)
 */
class WebHookTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_seo_service_webhook';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'default_value' => new DateTime(),
				'required' => true,
			),
			'TYPE' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'EXTERNAL_ID' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'SECURITY_CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'default_value' => function() {
					return Random::getString(32);
				}
			)
		);
	}
}