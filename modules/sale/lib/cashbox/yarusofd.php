<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Localization;
use Bitrix\Main;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class YarusOfd
 * @package Bitrix\Sale\Cashbox
 */
class YarusOfd extends Ofd
{
	const HEADER_TOKEN_NAME = 'Ofdapitoken';

	const ACTIVE_URL = 'https://api.ofd-ya.ru/ofdapi/v1/getChequeLink';
	const TEST_URL = 'https://testapi.ofd-ya.ru/ofdapi/v1/getChequeLink';

	/**
	 * @return string
	 */
	protected function getUrl()
	{
		if ($this->getValueFromSettings('OFD_MODE', 'IS_TEST') === 'Y')
		{
			return static::TEST_URL;
		}

		return static::ACTIVE_URL;
	}

	/**
	 * @return array
	 */
	protected function getLinkParamsMap()
	{
		return array(
			'fiscalDocumentNumber' => Check::PARAM_FISCAL_DOC_NUMBER,
			'fiscalDriveNumber' => Check::PARAM_FN_NUMBER
		);
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Localization\Loc::getMessage('SALE_CASHBOX_YARUS_OFD_NAME');
	}

	/**
	 * @param $data
	 * @return string
	 * @throws Main\ArgumentException
	 */
	public function generateCheckLink($data)
	{
		$queryString = $this->getQueryString($data);
		$result = $this->sendQuery($queryString);

		if (isset($result['link']))
		{
			return $result['link'];
		}

		return '';
	}

	/**
	 * @param $data
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	private function getQueryString($data)
	{
		$queryData = array();
		$paramsMap = $this->getLinkParamsMap();
		foreach ($paramsMap as $param => $key)
		{
			$queryData[$param] = $data[$key];
		}

		return Main\Web\Json::encode($queryData);
	}

	/**
	 * @param $queryString
	 * @return bool|array
	 */
	private function sendQuery($queryString)
	{
		$authKey = $this->getValueFromSettings('AUTH', 'INN');
		if (!$authKey)
		{
			return '';
		}

		$client = new Main\Web\HttpClient();
		$client->setHeader(static::HEADER_TOKEN_NAME, $authKey);

		$client->query('POST', $this->getUrl(), $queryString);
		$result = $client->getResult();
		try
		{
			$result = Main\Web\Json::decode($result);
		}
		catch (Main\ArgumentException $exception)
		{
			return false;
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public static function getSettings()
	{
		$settings = parent::getSettings();

		$settings['AUTH'] = array(
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_YARUS_SETTINGS_AUTH'),
			'ITEMS' => array(
				'INN' => array(
					'TYPE' => 'STRING',
					'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_YARUS_AUTH_KEY'),
					'VALUE' => ''
				)
			)
		);

		return $settings;
	}

}