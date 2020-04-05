<?php

namespace Bitrix\Mail\Helper\OAuth;

use Bitrix\Main;
use Bitrix\Mail;

class Mailru extends Mail\Helper\OAuth
{

	protected function __construct()
	{
		$this->oauthEntity = new MailruInterface(
			\CSocServMailRu2::getOption('mailru2_client_id'),
			\CSocServMailRu2::getOption('mailru2_client_secret')
		);

		$this->oauthEntity->addScope(array(
			'userinfo',
			'mail.imap',
		));
	}

	protected function check()
	{
		$provider = new \CSocServMailRu2;

		return $provider->checkSettings();
	}

	protected function mapUserData(array $userData)
	{
		return array(
			'email' => $userData['email'],
			'first_name' => $userData['first_name'],
			'last_name' => $userData['last_name'],
			'full_name' => $userData['name'],
			'image' => $userData['image'],
			'error' => $userData['error_description'],
		);
	}

	public static function getServiceName()
	{
		return 'mailru';
	}

	public function getControllerUrl()
	{
		return \CSocServMailRu2::CONTROLLER_URL;
	}

}

if (Main\Loader::includeModule('socialservices'))
{
	class_exists('CSocServMailRu2');

	class MailruInterface extends \CMailRu2Interface
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

		public function getCurrentUser()
		{
			$result = parent::getCurrentUser();

			if (is_array($result))
			{
				$result = array_merge(
					$result,
					$this->getTokenData()
				);
			}

			return $result;
		}

	}
}
