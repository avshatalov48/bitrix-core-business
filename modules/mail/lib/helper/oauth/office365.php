<?php

namespace Bitrix\Mail\Helper\OAuth;

use Bitrix\Main;
use Bitrix\Mail;
use Bitrix\Main\ArgumentException;

class Office365 extends Mail\Helper\OAuth
{

	protected function __construct()
	{
		$this->oauthEntity = new Office365Interface(
			\CSocServOffice365OAuth::getOption('office365_appid'),
			\CSocServOffice365OAuth::getOption('office365_appsecret')
		);

		// get graph universal scopes, for user profile read access
		$this->oauthEntity->setScope($this->oauthEntity->getGraphScopes());
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
			'userPrincipalName' => $userData['userPrincipalName'] ?? '',
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
		/**
		 * Version of outlook resource api (part of url)
		 */
		const OUTLOOK_API_VERSION = "/v2.0";

		/**
		 * Resource for outlook token
		 */
		const OUTLOOK_RESOURCE = "https://outlook.office.com/api";

		public function getStorageTokens()
		{
			return false;
		}

		/**
		 * Use refresh token to get outlook scopes, compatible with graph scopes
		 *
		 * @return void
		 */
		private function refreshToOutlookAccessToken(): void
		{
			$this->refreshWithScopes($this->getOutlookScopes());
		}

		/**
		 * Outlook scopes, that give Outlook email address, instead of profile email address
		 *
		 * @return array|string[]
		 */
		public function getOutlookScopes(): array
		{
			return [
				'offline_access',
				'https://outlook.office.com/IMAP.AccessAsUser.All',
				'https://outlook.office.com/SMTP.Send',
			];
		}

		/**
		 * Get user profile name
		 *
		 * @param bool $isSwitchedAlready If function already run
		 *
		 * @return string
		 */
		private function getGraphPrincipalName(bool $isSwitchedAlready = false): string
		{
			$httpClient = new \Bitrix\Main\Web\HttpClient();
			$httpClient->setHeader("Authorization", "Bearer " . $this->access_token);
			$jsonResponse = $httpClient->get($this->resource . static::VERSION . static::CONTACTS_URL);
			try
			{
				$decoded = \Bitrix\Main\Web\Json::decode($jsonResponse);
				if (!empty($decoded['userPrincipalName']) && is_string($decoded['userPrincipalName']))
				{
					return $decoded['userPrincipalName'];
				}
				else if (!empty($decoded['error']) && !$isSwitchedAlready)
				{
					$this->refreshToGraphAccessToken();
					return $this->getGraphPrincipalName(true);
				}
			}
			catch (ArgumentException $e)
			{
				AddMessage2Log($e->getMessage(), 'mail', 2, true);
			}
			return '';
		}

		/**
		 * Use refresh token to get outlook scopes, compatible with graph scopes
		 *
		 * @return void
		 */
		private function refreshToGraphAccessToken(): void
		{
			$this->refreshWithScopes($this->getGraphScopes());
		}

		/**
		 * Refresh access token with specific scopes
		 *
		 * @param array|string[] $scopes Array of scopes
		 *
		 * @return void
		 */
		private function refreshWithScopes(array $scopes): void
		{
			if (empty($this->refresh_token))
			{
				return;
			}

			$httpClient = new \Bitrix\Main\Web\HttpClient();

			$jsonResponse = $httpClient->post(static::TOKEN_URL, [
				'refresh_token' => $this->refresh_token,
				'client_id' => $this->appID,
				'client_secret' => $this->appSecret,
				'grant_type' => 'refresh_token',
				'scope' => implode(' ', $scopes),
			]);

			try
			{
				$decoded = \Bitrix\Main\Web\Json::decode($jsonResponse);
				if (!empty($decoded['access_token']))
				{
					$this->access_token = (string)$decoded['access_token'];
					$this->refresh_token = (string)$decoded['refresh_token'];
					$this->accessTokenExpires = (int)$decoded["expires_in"];
				}
			}
			catch (ArgumentException $e)
			{
				AddMessage2Log($e->getMessage(), 'mail', 2, true);
			}
		}

		/**
		 * Get Scopes for graph resource
		 *
		 * @return array|string[]
		 */
		public function getGraphScopes(): array
		{
			return [
				'User.read',
				'offline_access',
				'IMAP.AccessAsUser.All',
				'SMTP.Send',
			];
		}

		public function getTokenData(): array
		{
			return [
				'access_token' => $this->access_token,
				'refresh_token' => $this->refresh_token,
				'expires_in' => $this->accessTokenExpires + time(),
			];
		}

		public function getCurrentUser()
		{
			if (empty($this->access_token))
			{
				return false;
			}
			$userPrincipalName = $this->getGraphPrincipalName();
			$this->refreshToOutlookAccessToken();

			$httpClient = new \Bitrix\Main\Web\HttpClient();
			$httpClient->setHeader("Authorization", "Bearer ". $this->access_token);

			$result = $httpClient->get(static::OUTLOOK_RESOURCE . static::OUTLOOK_API_VERSION . static::CONTACTS_URL);
			try
			{
				$result = \Bitrix\Main\Web\Json::decode($result);
			}
			catch (ArgumentException $e)
			{
				AddMessage2Log($e->getMessage(), 'mail', 2, true);
				$result = [];
			}

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
					'userPrincipalName' => $userPrincipalName,
				],
				$this->getTokenData()
			);
		}

	}
}
