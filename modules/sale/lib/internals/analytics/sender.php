<?php
namespace Bitrix\Sale\Internals\Analytics;

use Bitrix\Main\Context,
	Bitrix\Main\Type\DateTime,
	Bitrix\Main\Web\HttpClient,
	Bitrix\Main\Web\Json,
	Bitrix\Main\Loader;

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
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\LoaderException
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
			$response = $httpClient->post(static::URL, $postData);
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
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\LoaderException
	 */
	private function getCommonData(): array
	{
		$isB24 = Loader::includeModule('bitrix24');
		$data = [
			'type' => $isB24 ? 'b24' : 'self_hosted',
			'date_time' => (new DateTime())->format('Y-m-d H:i:s'),
			'transaction_type' => $this->provider::getCode(),
		];

		if($isB24)
		{
			$data['host_name'] = BX24_HOST_NAME;
			$data['tariff'] = \CBitrix24::getLicensePrefix();
		}
		else
		{
			$request = Context::getCurrent()->getRequest();
			$data['host_name'] = $request->getHttpHost();
			$data['license_key'] = md5('BITRIX'.LICENSE_KEY.'LICENCE');
		}

		return $data;
	}

	/**
	 * @param array $request
	 * @return string
	 * @throws \Bitrix\Main\LoaderException
	 */
	private static function signRequest(array $request): string
	{
		$requestHash = md5(serialize($request));

		if (Loader::includeModule('bitrix24'))
		{
			return \CBitrix24::RequestSign($requestHash);
		}

		return md5($requestHash.md5(LICENSE_KEY));
	}
}
