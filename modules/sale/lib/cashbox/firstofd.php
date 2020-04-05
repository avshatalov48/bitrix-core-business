<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Localization;
use Bitrix\Main;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class FirstOfd
 * @package Bitrix\Sale\Cashbox
 */
class FirstOfd extends Ofd
{
	const ACTIVE_URL = 'https://consumer.1-ofd.ru/v1?';
	const TEST_URL = 'https://test-consumer.1-ofd.ru/v1?';

	/**
	 * @return string
	 */
	protected function getUrl()
	{
		return $this->isTestMode() ? static::TEST_URL : static::ACTIVE_URL;
	}

	/**
	 * @return array
	 */
	protected function getLinkParamsMap()
	{
		return array(
			't' => Check::PARAM_DOC_TIME,
			's' => Check::PARAM_DOC_SUM,
			'fn' => Check::PARAM_FN_NUMBER,
			'i' => Check::PARAM_FISCAL_DOC_NUMBER,
			'fp' => Check::PARAM_FISCAL_DOC_ATTR,
			'n' => Check::PARAM_CALCULATION_ATTR
		);
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
			{
				if ($queryKey === 't')
				{
					$dateTime = Main\Type\DateTime::createFromTimestamp($data[$checkKey]);
					$queryParams[] = $queryKey.'='.$dateTime->format('Ymd\THis');
				}
				else if ($queryKey === 'n')
				{
					$calculatedSignMap = $this->getCalculatedSignMap();
					$queryParams[] = $queryKey.'='.$calculatedSignMap[$data[$checkKey]];
				}
				else
				{
					$queryParams[] = $queryKey.'='.$data[$checkKey];
				}
			}
		}

		if (empty($queryParams))
			return '';

		$url = $this->getUrl();
		return $url.implode('&', $queryParams);
	}

	/**
	 * @return array
	 */
	private function getCalculatedSignMap()
	{
		return array(
			Check::CALCULATED_SIGN_INCOME => 1,
			Check::CALCULATED_SIGN_CONSUMPTION => 2,
		);
	}

	/**
	 * @throws Main\NotImplementedException
	 * @return string
	 */
	public static function getName()
	{
		return Localization\Loc::getMessage('SALE_CASHBOX_FIRST_OFD_NAME');
	}

}