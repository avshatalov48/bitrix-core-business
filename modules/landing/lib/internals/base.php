<?php
namespace Bitrix\Landing\Internals;

use \Bitrix\Landing\Manager;

class BaseTable
{
	public static $internalClass = null;

	/**
	 * Get internal class (must declarated in external class).
	 * @return string
	 */
	private static function getCallingClass()
	{
		if (static::$internalClass === null)
		{
			throw new \Bitrix\Main\SystemException(
				'Variable static::$internalClass must be declarated in external class.'
			);
		}
		return '\\' . __NAMESPACE__ . '\\' . static::$internalClass;
	}

	/**
	 * Get Map of table.
	 * @return array
	 */
	public static function getMap()
	{
		/** @var \Bitrix\Main\ORM\Data\DataManager $class */
		$class = self::getCallingClass();
		return $class::getMap();
	}

	/**
	 * Create new record and return it new id.
	 * @param array $fields Fields array.
	 * @return \Bitrix\Main\ORM\Data\AddResult
	 */
	public static function add($fields)
	{
		$uid = Manager::getUserId();
		$uid = $uid ? $uid : 1;
		$date = new \Bitrix\Main\Type\DateTime;

		$charValue = array(
			'ACTIVE', 'PUBLIC', 'SITEMAP', 'FOLDER'
		);
		foreach ($charValue as $code)
		{
			if (isset($fields[$code]) && $fields[$code] != 'Y')
			{
				$fields[$code] = 'N';
			}
		}
		if (!isset($fields['CREATED_BY_ID']))
		{
			$fields['CREATED_BY_ID'] = $uid;
		}
		if (!isset($fields['MODIFIED_BY_ID']))
		{
			$fields['MODIFIED_BY_ID'] = $uid;
		}
		if (!isset($fields['DATE_CREATE']))
		{
			$fields['DATE_CREATE'] = $date;
		}
		if (!isset($fields['DATE_MODIFY']))
		{
			$fields['DATE_MODIFY'] = $date;
		}

		/** @var \Bitrix\Main\ORM\Data\DataManager $class */
		$class = self::getCallingClass();
		return $class::add($fields);
	}

	/**
	 * Update record.
	 * @param int $id Record key.
	 * @param array $fields Fields array.
	 * @return \Bitrix\Main\Result
	 */
	public static function update($id, $fields = array())
	{
		$uid = Manager::getUserId();
		$date = new \Bitrix\Main\Type\DateTime;

		$charValue = array(
			'ACTIVE', 'PUBLIC', 'SITEMAP', 'FOLDER'
		);
		foreach ($charValue as $code)
		{
			if (isset($fields[$code]) && $fields[$code] != 'Y')
			{
				$fields[$code] = 'N';
			}
		}

		if (isset($fields['ID']))
		{
			unset($fields['ID']);
		}
		if (!isset($fields['MODIFIED_BY_ID']))
		{
			$fields['MODIFIED_BY_ID'] = $uid;
		}
		else if (!$fields['MODIFIED_BY_ID'])
		{
			unset($fields['MODIFIED_BY_ID']);
		}
		if (!isset($fields['DATE_MODIFY']))
		{
			$fields['DATE_MODIFY'] = $date;
		}
		if (!$fields['DATE_MODIFY'])
		{
			unset($fields['DATE_MODIFY']);
		}

		/** @var \Bitrix\Main\ORM\Data\DataManager $class */
		$class = self::getCallingClass();
		return $class::update($id, $fields);
	}

	/**
	 * Delete record.
	 * @param int $id Record key.
	 * @return \Bitrix\Main\Result
	 */
	public static function delete($id)
	{
		/** @var \Bitrix\Main\ORM\Data\DataManager $class */
		$class = self::getCallingClass();
		\Bitrix\Landing\Debug::log(
			$class,
			'id: ' . $id . '@' . print_r(\Bitrix\Main\Diag\Helper::getBackTrace(5), true),
			'LANDING_ENTITY_DELETE'
		);
		return $class::delete($id);
	}

	/**
	 * Get records of table.
	 * @param array $params Params array like ORM style.
	 * @return \Bitrix\Main\ORM\Query\Result
	 */
	public static function getList($params = array())
	{
		$class = self::getCallingClass();

		if (method_exists($class, 'setAccessFilter'))
		{
			$params = $class::setAccessFilter($params);
		}

		/** @var \Bitrix\Main\ORM\Data\DataManager $class */
		return $class::getList($params);
	}

	/**
	 * Register calllback for internal table.
	 * @param string $code Type of callback.
	 * @param callable $callback Callback.
	 * @return void
	 */
	public static function callback($code, $callback)
	{
		$class = self::getCallingClass();
		if (mb_substr(mb_strtolower($class), -5) == 'table')
		{
			$class = mb_substr($class, 0, -5);
			if ($class)
			{
				$eventManager = \Bitrix\Main\EventManager::getInstance();
				$eventManager->addEventHandler(
					'landing',
					$class . '::' . $code,
					$callback
				);
			}
		}
	}
}
