<?php
namespace Bitrix\Sale\Internals\Analytics;

use Bitrix\Main\Context;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Loader;
use Bitrix\Main\Config;

/**
 * Class Sender
 * @package Bitrix\Sale\Internals\Analytics
 */
final class Sender
{
	protected const URL = 'https://util.1c-bitrix.ru/analytics.php';

	/** @var Provider $provider */
	private $provider;

	/**
	 * Service constructor.
	 * @param Provider $provider
	 */
	public function __construct(Provider $provider)
	{
		$this->provider = $provider;
	}

	/**
	 * @param DateTime $dateFrom
	 * @param DateTime $dateTo
	 * @return bool
	 */
	public function sendForPeriod(DateTime $dateFrom, DateTime $dateTo): bool
	{
		if ($data = $this->provider->getData($dateFrom, $dateTo))
		{
			$postData = $this->getCommonData();
			$postData['content'] = $data;
			$postData['bx_hash'] = self::signRequest($postData);
			$postData = Json::encode($postData);

			$httpClient = new HttpClient();
			$response = $httpClient->post(self::URL, $postData);
			if (!$response || $httpClient->getStatus() !== 200)
			{
				return false;
			}

			try
			{
				$response = Json::decode($response);
				if ($response['result'] !== 'ok')
				{
					return false;
				}
			}
			catch (\Bitrix\Main\ArgumentException $ex)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * @return array
	 */
	private function getCommonData(): array
	{
		$isB24 = self::isB24();
		$data = [
			'type' => $isB24 ? 'b24' : 'self_hosted',
			'date_time' => (new DateTime())->format('Y-m-d H:i:s'),
			'transaction_type' => $this->provider::getCode(),
			'host_name' => self::getHostName(),
		];

		if($isB24)
		{
			$data['tariff'] = \CBitrix24::getLicensePrefix();
		}
		else
		{
			$data['license_key'] = \Bitrix\Main\Analytics\Counter::getAccountId();
		}

		return $data;
	}

	/**
	 * @param array $request
	 * @return string
	 */
	private static function signRequest(array $request): string
	{
		$requestHash = md5(serialize($request));

		if (Loader::includeModule('bitrix24'))
		{
			return \CBitrix24::RequestSign($requestHash);
		}

		$privateKey = \Bitrix\Main\Analytics\Counter::getPrivateKey();
		return md5($requestHash.$privateKey);
	}

	/**
	 * @return string
	 */
	private static function getHostName(): string
	{
		if (self::isB24())
		{
			$hostName = BX24_HOST_NAME;
		}
		else
		{
			$hostName = Config\Option::get('main', 'server_name');
			if (!$hostName)
			{
				$hostName = (defined('SITE_SERVER_NAME') && !empty(SITE_SERVER_NAME)) ? SITE_SERVER_NAME : '';
			}

			if (!$hostName)
			{
				$request = Context::getCurrent()->getRequest();
				$hostName = $request->getHttpHost();
			}
		}

		return (string)$hostName;
	}

	/**
	 * @return bool
	 */
	private static function isB24(): bool
	{
		return Loader::includeModule('bitrix24');
	}
}
