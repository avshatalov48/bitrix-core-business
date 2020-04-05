<?php

namespace Bitrix\Mail\Helper\OAuth;

use Bitrix\Main;
use Bitrix\Mail;

class Google extends Mail\Helper\OAuth
{

	protected function __construct()
	{
		$this->oauthEntity = new GoogleInterface;

		$this->oauthEntity->addScope(array(
			'email',
			'https://mail.google.com/',
		));
	}

	protected function check()
	{
		$provider = new \CSocServGoogleOAuth;

		return $provider->checkSettings();
	}

	protected function mapUserData(array $userData)
	{
		return array(
			'email' => $userData['email'],
			'first_name' => $userData['given_name'],
			'last_name' => $userData['family_name'],
			'full_name' => $userData['name'],
			'image' => $userData['picture'],
			'error' => $userData['error']['message'],
		);
	}

	public static function getServiceName()
	{
		return 'google';
	}

	public function getControllerUrl()
	{
		return \CSocServAuth::getControllerUrl();
	}

}

if (Main\Loader::includeModule('socialservices'))
{
	class GoogleInterface extends \CGoogleOAuthInterface
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

	}
}
