<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Main\UserConsent;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;

Loc::loadLanguageFile(__FILE__);

/**
 * Class DataProvider.
 * @package Bitrix\Main\UserConsent
 */
class DataProvider
{
	const EVENT_NAME_LIST = 'OnUserConsentDataProviderList';

	/** @var string  */
	protected $code;
	/** @var string  */
	protected $name;
	/** @var array|callable  */
	protected $data;
	/** @var string */
	protected $editUrl;

	/**
	 * Create instance.
	 *
	 * @param string $providerCode Provider code.
	 * @return static|null
	 */
	public static function getByCode($providerCode)
	{
		$list = self::getList();
		foreach ($list as $provider)
		{
			if ($provider->getCode() != $providerCode)
			{
				continue;
			}

			return $provider;
		}

		return null;
	}

	/**
	 * Constructor.
	 *
	 * @param string $code Code.
	 * @param string $name Name.
	 * @param array|callable $data Data.
	 * @param string $editUrl Url to data edit page.
	 * @param array|callable $data Data.
	 */
	public function __construct($code, $name, $data, $editUrl)
	{
		$this->code = $code;
		$this->name = $name;
		$this->data = $data;
		$this->editUrl = $editUrl;
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get edit url.
	 *
	 * @return string
	 */
	public function getEditUrl()
	{
		return $this->editUrl;
	}

	/**
	 * Get data.
	 *
	 * @return array
	 */
	public function getData()
	{
		if (is_callable($this->data))
		{
			$getDataFunc = $this->data;
			$data = $getDataFunc();
		}
		else
		{
			$data = $this->data;
		}

		$result = array();
		foreach ($data as $key => $value)
		{
			if (!$value)
			{
				continue;
			}

			$result[$key] = $value;
		}

		return $result;
	}

	/**
	 * Get as array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return array(
			'CODE' => $this->getCode(),
			'NAME' => $this->getName(),
			'DATA' => $this->getData(),
			'EDIT_URL' => $this->getEditUrl(),
		);
	}

	/**
	 * Get list.
	 *
	 * @return static[]
	 */
	public static function getList()
	{
		$data = array();
		$event = new Event('main', self::EVENT_NAME_LIST, array($data));
		$event->send();

		$list = array();
		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() == EventResult::ERROR)
			{
				continue;
			}

			$params = $eventResult->getParameters();
			if(!$params || !is_array($params))
			{
				continue;
			}


			foreach ($params as $item)
			{
				if (!self::checkObligatoryFields($item))
				{
					continue;
				}

				$list[] = new static($item['CODE'], $item['NAME'], $item['DATA'], $item['EDIT_URL']);
			}
		}

		return $list;
	}

	protected static function checkObligatoryFields($params)
	{
		if (!isset($params['DATA']) || !$params['DATA'])
		{
			return false;
		}

		if (!is_array($params['DATA']) && !is_callable($params['DATA']))
		{
			return false;
		}

		if (!isset($params['NAME']) || !$params['NAME'] || !is_string($params['NAME']))
		{
			return false;
		}

		if (!isset($params['CODE']) || !$params['CODE'] || !is_string($params['CODE']))
		{
			return false;
		}

		return true;
	}
}