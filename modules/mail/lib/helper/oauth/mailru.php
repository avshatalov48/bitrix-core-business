<?php

namespace Bitrix\Mail\Helper\OAuth;

use Bitrix\Main;
use Bitrix\Mail;

class Mailru extends Mail\Helper\OAuth
{

	protected function __construct()
	{
		$this->oauthEntity = new MailruInterface(
			\CSocServMyMailru::getOption('mailru_id'),
			\CSocServMyMailru::getOption('mailru_secret_key')
		);

		//$this->oauthEntity->addScope(array(
		//	'userinfo',
		//	'mail.imap',
		//));
	}

	protected function check()
	{
		$provider = new \CSocServMyMailru;

		return $provider->checkSettings();
	}

	protected function mapUserData(array $userData)
	{
		return array(
			'email' => $userData['email'],
			'first_name' => $userData['first_name'],
			'last_name' => $userData['last_name'],
			'full_name' => $userData['nick'],
			'image' => $userData['pic_50'],
			//'error' => $userData['error']['message'],
		);
	}

	public static function getServiceName()
	{
		return 'mailru';
	}

}

if (Main\Loader::includeModule('socialservices'))
{
	class MailruInterface extends \CMailruOAuthInterface
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
				'expires_in' => $this->accessTokenExpires,
			);
		}

		public function getCurrentUser()
		{
			$result = parent::getCurrentUser();

			if (is_array($result))
			{
				$result = reset($result);

				$result['access_token'] = $this->access_token;
				//$result['refresh_token'] = $this->refresh_token;
				//$result['expires_in'] = time() + $this->accessTokenExpires;
			}

			return $result;
		}

	}
}
