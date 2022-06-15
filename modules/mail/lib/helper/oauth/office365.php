<?php

namespace Bitrix\Mail\Helper\OAuth;

use Bitrix\Main;
use Bitrix\Mail;

class Office365 extends Mail\Helper\OAuth
{

	protected function __construct()
	{
		$this->oauthEntity = new Office365Interface(
			\CSocServOffice365OAuth::getOption('office365_appid'),
			\CSocServOffice365OAuth::getOption('office365_appsecret')
		);

		$this->oauthEntity->setScope(array(
			"offline_access",
			"https://outlook.office.com/IMAP.AccessAsUser.All",
		));
	}

	protected function check()
	{
		$provider = new \CSocServOffice365OAuth;

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
		return 'office365';
	}

	public function getControllerUrl()
	{
		return \CSocServOffice365OAuth::CONTROLLER_URL;
	}

}

if (Main\Loader::includeModule('socialservices'))
{
	class_exists('CSocServOffice365OAuth');

	class Office365Interface extends \COffice365OAuthInterface
	{
		const VERSION = "/v2.0";
		protected $resource = "https://outlook.office.com/api";

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
			if (empty($this->access_token))
			{
				return false;
			}

			$httpClient = new \Bitrix\Main\Web\HttpClient();
			$httpClient->setHeader("Authorization", "Bearer ". $this->access_token);

			$result = $httpClient->get($this->resource.static::VERSION.static::CONTACTS_URL);
			$result = \Bitrix\Main\Web\Json::decode($result);

			if(isset($result['EmailAddress']))
			{
				$email = $result['EmailAddress'];
				$emailIsIntended = false;
			}
			else
			{
				global $USER;
				$email = $USER->GetEmail();
				if(is_null($email))
				{
					$email = '';
				}
				$emailIsIntended = true;
			}

			return array_merge(
				[
					'email' => $email,
					'emailIsIntended' => $emailIsIntended,

				],
				$this->getTokenData()
			);
		}

	}
}
