<?php
namespace Bitrix\Sale\Internals\Analytics;

use Bitrix\Main;

/**
 * Class Sender
 * @package Bitrix\Sale\Internals\Analytics
 */
final class Sender
{
	protected const URL = 'https://util.1c-bitrix.ru/analytics.php';

	/** @var string $type */
	private $type;

	/** @var array $data */
	private $data;

	/**
	 * Service constructor.
	 * @param array $data
	 */
	public function __construct(string $type, array $data)
	{
		$this->type = $type;
		$this->data = $data;
	}

	/**
	 * @return bool
	 */
	public function send(): bool
	{
		if ($this->data)
		{
			$postData = $this->getCommonData();
			$postData['content'] = $this->data;
			$postData['bx_hash'] = self::signRequest($postData);
			$postData = Main\Web\Json::encode($postData);

			$httpClient = new Main\Web\HttpClient();
			$response = $httpClient->post(self::URL, $postData);
			if (!$response || $httpClient->getStatus() !== 200)
			{
				return false;
			}

			try
			{
				$response = Main\Web\Json::decode($response);
				if ($response['result'] !== 'ok')
				{
					return false;
				}
			}
			catch (Main\ArgumentException $ex)
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
			'date_time' => (new Main\Type\DateTime())->format('Y-m-d H:i:s'),
			'transaction_type' => $this->type,
			'host_name' => self::getHostName(),
		];

		if($isB24)
		{
			$data['tariff'] = \CBitrix24::getLicensePrefix();
		}
		else
		{
			$data['license_key'] = Main\Analytics\Counter::getAccountId();
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

		if (Main\Loader::includeModule('bitrix24'))
		{
			return \CBitrix24::RequestSign($requestHash);
		}

		$privateKey = Main\Analytics\Counter::getPrivateKey();
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
			$hostName = Main\Config\Option::get('main', 'server_name');
			if (!$hostName)
			{
				$hostName = (defined('SITE_SERVER_NAME') && !empty(SITE_SERVER_NAME)) ? SITE_SERVER_NAME : '';
			}

			if (!$hostName)
			{
				$request = Main\Context::getCurrent()->getRequest();
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
		return Main\Loader::includeModule('bitrix24');
	}
}
