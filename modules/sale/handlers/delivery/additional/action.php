<?
namespace Sale\Handlers\Delivery\Additional;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

/**
 * Class Action
 * @package Sale\Handlers\Delivery\Additional
 */
class Action
{
	const EXECUTED_OPTION = 'handlers_dlv_add_action_executed';

	/**
	 * @param array $params
	 * @return Result
	 */
	public static function execute(array $params)
	{
		$result = new Result();

		if(!isset($params['TYPE']))
		{
			$result->addError(new Error('Action type type is undefined'));
			return $result;
		}

		if(!isset($params['ID']))
		{
			$result->addError(new Error('Action id is undefined'));
			return $result;
		}

		if(self::isExecuted($params['ID']))
			return $result;

		switch($params['TYPE'])
		{
			case 'TYPE_CACHE_CLEAR':

				if(!isset($params['PARAMS']['CACHE_TYPE']))
					break;

				if(!$cache = CacheManager::getItem($params['PARAMS']['CACHE_TYPE']))
					break;

				$cache->clean();
				break;

			case 'TYPE_LOCATIONS_CHANGED':
				break;

			default:
			{
				$result->addError(new Error('Unknown type of action: "'.$params['TYPE'].'"'));
				return $result;
			}
		}

		self::setExecuted($params['ID']);
		return $result;
	}

	/**
	 * @param string $id Action identifier.
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	private static function isExecuted($id)
	{
		return(
			in_array(
				$id,
				explode(',',Option::get('sale', self::EXECUTED_OPTION, '')
			))
		);
	}

	/**
	 * @param string $id Action identifier.
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	private static function setExecuted($id)
	{
		if(!self::isExecuted($id))
		{
			$value = Option::get('sale', self::EXECUTED_OPTION, '');

			if(strlen($value) > 0)
				$value .= ',';

			$value .= $id;

			Option::set('sale', self::EXECUTED_OPTION, $value);
		}
	}
}