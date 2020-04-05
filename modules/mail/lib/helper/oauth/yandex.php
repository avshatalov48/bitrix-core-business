<?php

namespace Bitrix\Mail\Helper\OAuth;

use Bitrix\Main;
use Bitrix\Mail;

class Yandex extends Mail\Helper\OAuth
{

	protected function __construct()
	{
		$this->oauthEntity = new YandexInterface;
	}

	protected function check()
	{
		$provider = new \CSocServYandexAuth;

		return $provider->checkSettings();
	}

	protected function mapUserData(array $userData)
	{
		return array(
			'email' => $userData['default_email'],
			'first_name' => $userData['first_name'],
			'last_name' => $userData['last_name'],
			'full_name' => $userData['real_name'],
			'image' => sprintf('https://avatars.yandex.net/get-yapic/%s/islands-middle', $userData['default_avatar_id']),
			//'error' => $data['error']['message'],
		);
	}

	public static function getServiceName()
	{
		return 'yandex';
	}

	public function getControllerUrl()
	{
		return \CSocServYandexAuth::CONTROLLER_URL;
	}

}

if (Main\Loader::includeModule('socialservices'))
{
	class YandexInterface extends \CYandexOAuthInterface
	{

		public function getStorageTokens()
		{
			return false;
		}

		public function getTokenData()
		{
			return array(
				'access_token' => $this->access_token,
				'refresh_token' => $this->refresh_token,
				'expires_in' => $this->accessTokenExpires,
			);
		}

		public function getNewAccessToken($refreshToken = false, $userId = 0, $save = false)
		{
			return false;
		}

	}
}
