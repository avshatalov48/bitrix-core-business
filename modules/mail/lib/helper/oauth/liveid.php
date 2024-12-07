<?php

namespace Bitrix\Mail\Helper\OAuth;

use Bitrix\Main;
use Bitrix\Mail;

class LiveId extends Mail\Helper\OAuth
{

	protected function __construct()
	{
		$this->oauthEntity = new LiveIdInterface;

		$this->oauthEntity->addScope(array(
			'wl.emails',
			'wl.imap',
			'wl.offline_access',
		));
	}

	protected function check()
	{
		$provider = new \CSocServLiveIdOAuth;

		return $provider->checkSettings();
	}

	protected function mapUserData(array $userData)
	{
		return array(
			'email' => $userData['emails']['account'],
			'first_name' => $userData['first_name'],
			'last_name' => $userData['last_name'],
			'full_name' => $userData['name'],
			'image' => sprintf('https://apis.live.net/v5.0/%s/picture?type=small', $userData['id']),
			'error' => $userData['error']['message'],
		);
	}

	public static function getServiceName()
	{
		return 'liveid';
	}

	public function getControllerUrl()
	{
		return \CSocServLiveIdOAuth::CONTROLLER_URL;
	}

}

if (Main\Loader::includeModule('socialservices'))
{
	class_exists('CSocServLiveIdOAuth');

	class LiveIdInterface extends \CLiveIdOAuthInterface
	{

		public function setCode($code)
		{
			$this->code = $code;
		}

		public function setToken($access_token)
		{
			$this->access_token = $access_token;
		}

		public function getStorageTokens()
		{
			return false;
		}

		public function getTokenData()
		{
			return array(
				'access_token' => $this->access_token,
				'refresh_token' => $this->refresh_token,
				'expires_in' => Mail\Helper\OAuth::convertTokenExpiresToUnixtimeIfNeed($this->accessTokenExpires),
			);
		}

		public function getCurrentUser()
		{
			if (empty($this->access_token))
			{
				return false;
			}

			$httpClient = new \Bitrix\Main\Web\HttpClient();
			$httpClient->setHeader('Authorization', 'Bearer ' . $this->access_token);

			$result = $httpClient->get(static::CONTACTS_URL);

			if (!empty($result))
			{
				try
				{
					$result = \Bitrix\Main\Web\Json::decode($result);
				}
				catch (\Exception $e)
				{
					$result = null;
				}
			}

			if (is_array($result))
			{
				$result = array_merge(
					$result,
					$this->getTokenData()
				);
			}

			return $result;
		}

		public function setAccessTokenExpires($expires): void
		{
			$this->accessTokenExpires = (int)$expires;
		}

	}
}
