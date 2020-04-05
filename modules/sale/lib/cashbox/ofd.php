<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;
use Bitrix\Sale\Result;

/**
 * Class Ofd
 * @package Bitrix\Sale\Cashbox
 */
abstract class Ofd
{
	/**
	 * @return array
	 */
	public static function getHandlerList()
	{
		return array(
			'\Bitrix\Sale\Cashbox\FirstOfd' => FirstOfd::getName(),
			'\Bitrix\Sale\Cashbox\PlatformaOfd' => PlatformaOfd::getName(),
			'\Bitrix\Sale\Cashbox\YarusOfd' => YarusOfd::getName(),
			'\Bitrix\Sale\Cashbox\TaxcomOfd' => TaxcomOfd::getName(),
			'\Bitrix\Sale\Cashbox\OfdruOfd' => OfdruOfd::getName(),
			'\Bitrix\Sale\Cashbox\TenzorOfd' => TenzorOfd::getName(),
			'\Bitrix\Sale\Cashbox\ConturOfd' => ConturOfd::getName(),
		);
	}

	/**
	 * @param Cashbox $cashbox
	 * @return null
	 */
	public static function create(Cashbox $cashbox)
	{
		$handler = $cashbox->getField('OFD');
		if (class_exists($handler))
			return new $handler($cashbox);

		return null;
	}

	/**
	 * Ofd constructor.
	 * @param Cashbox $cashbox
	 */
	private function __construct(Cashbox $cashbox)
	{
		$this->cashbox = $cashbox;
	}

	/**
	 * @return string
	 */
	protected function getUrl()
	{
		return '';
	}

	/**
	 * @return array
	 */
	protected function getLinkParamsMap()
	{
		return array();
	}

	/**
	 * @param $data
	 * @return string
	 */
	public function generateCheckLink($data)
	{
		$queryParams = array();

		$map = $this->getLinkParamsMap();
		foreach ($map as $queryKey => $checkKey)
		{
			if ($data[$checkKey])
				$queryParams[] = $queryKey.'='.$data[$checkKey];
		}

		if (empty($queryParams))
			return '';

		$url = $this->getUrl();
		return $url.implode('&', $queryParams);
	}

	/**
	 * @throws NotImplementedException
	 * @return string
	 */
	public static function getName()
	{
		throw new NotImplementedException();
	}

	/**
	 * @return array
	 */
	public static function getSettings()
	{
		return array(
			'OFD_MODE' => array(
				'LABEL' => Loc::getMessage('SALE_CASHBOX_OFD_SETTINGS'),
				'ITEMS' => array(
					'IS_TEST' => array(
						'TYPE' => 'Y/N',
						'LABEL' => Loc::getMessage('SALE_CASHBOX_OFD_TEST_MODE'),
						'VALUE' => 'N'
					)
				)
			)
		);
	}

	/**
	 * @param $settings
	 * @return Result
	 */
	public static function validateSettings($settings)
	{
		return new Result();
	}

	/**
	 * @param $name
	 * @param $code
	 * @return mixed
	 */
	public function getValueFromSettings($name, $code = null)
	{
		$map = $this->cashbox->getField('OFD_SETTINGS');
		if (isset($map[$name]))
		{
			if (is_array($map[$name]))
			{
				if (isset($map[$name][$code]))
					return $map[$name][$code];
			}
			else
			{
				return $map[$name];
			}
		}

		return null;
	}

	/**
	 * @return bool
	 */
	protected function isTestMode()
	{
		return $this->getValueFromSettings('OFD_MODE', 'IS_TEST') === 'Y';
	}
}