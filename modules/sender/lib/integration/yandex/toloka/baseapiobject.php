<?

namespace Bitrix\Sender\Integration\Yandex\Toloka;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Sender\Integration\Yandex\Toloka\Exception\AccessDeniedException;
use Bitrix\Seo\Analytics\Service;
use Bitrix\Seo\Engine\YandexJson;
use COption;

if(!defined("BITRIX_CLOUD_ADV_URL"))
{
	define("BITRIX_CLOUD_ADV_URL", 'https://cloud-adv.bitrix.info');
}

if(!defined("SEO_BITRIX_API_URL"))
{
	define("SEO_BITRIX_API_URL", BITRIX_CLOUD_ADV_URL."/rest/");
}

abstract class BaseApiObject
{
	private const API_URL = SEO_BITRIX_API_URL;
	protected $result;
	protected $accessToken;
	protected $clientId;
	protected $clientSecret;

	private function checkResult(): void
	{
		$errorCode = $this->result['error'] ?? $this->result['code']?? false;

		if(!$errorCode)
		{
			return;
		}

		switch ($errorCode)
		{
			case 'verification_needed':
			case 'ACCESS_DENIED':
				COption::RemoveOption('sender', ApiRequest::ACCESS_CODE);
				throw new AccessDeniedException();
				break;
		}
	}

	/**
	 * @return mixed
	 */
	public function getAccessToken()
	{
		return $this->accessToken;
	}

	/**
	 * @param mixed $accessToken
	 *
	 * @return BaseApiObject
	 */
	public function setAccessToken($accessToken)
	{
		$this->accessToken = $accessToken;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getClientId()
	{
		return $this->clientId;
	}

	/**
	 * @param mixed $clientId
	 *
	 * @return BaseApiObject
	 */
	public function setClientId($clientId)
	{
		$this->clientId = $clientId;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getClientSecret()
	{
		return $this->clientSecret;
	}

	/**
	 * @param mixed $clientSecret
	 *
	 * @return BaseApiObject
	 */
	public function setClientSecret($clientSecret)
	{
		$this->clientSecret = $clientSecret;

		return $this;
	}

	protected function sendRequest($data = [])
	{
		$scope = static::getScope().$data['methodName']??'';

		$this->registerOnCloudAdv();
		$httpResult =  $this
			->query($scope, $data['parameters']??[])
			->getResult();

		$httpResult = $httpResult ?
			YandexJson::decode($httpResult) : [];

		$this->result = isset($httpResult['result']) ?
			YandexJson::decode($httpResult['result']) :
			$httpResult
		;

		$this->checkResult();
	}
	protected function registerOnCloudAdv()
	{
		$authAdapter = Service::getInstance()->getAuthAdapter(Service::TYPE_YANDEX);

		$http = new HttpClient();
		$http->setRedirect(false);
		$http->get($authAdapter->getAuthUrl());
	}

	protected function query($scope, $param = NULL)
	{
		if ($param === NULL)
		{
			$param = array();
		}

		$http = new HttpClient();
		$http->setRedirect(false);

		$postData = array(
			"access_code" => $this->accessToken,
			"client_id" => $this->clientId,
			"client_secret" => $this->clientSecret,
			"data" => json_encode($param, JSON_UNESCAPED_UNICODE)
		);

		if (!empty($param))
		{
			$postData = array_merge($postData, $param);
		}

		$http->post(self::API_URL.$scope, $postData, false);

		return $http;
	}

	abstract function getScope():string;
}