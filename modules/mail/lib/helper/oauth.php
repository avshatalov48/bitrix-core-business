<?php

namespace Bitrix\Mail\Helper;

use Bitrix\Main;
use Bitrix\Mail;
use Bitrix\Main\Web\Uri;
use Bitrix\Mail\Helper\OAuth\UserData;


abstract class OAuth
{

	/**
	 * @var \CSocServOAuthTransport
	 */
	protected $oauthEntity;

	protected $service, $storedUid;

	public const WEB_TYPE = 'web';
	public const MOBILE_TYPE = 'mobile';
	protected UserData $publicUserData;

	/**
	 * Returns the list of supported services
	 *
	 * @return array
	 */
	public static function getKnownServices()
	{
		static $knownServices;

		if (is_null($knownServices))
		{
			$knownServices = [
				OAuth\Google::getServiceName(),
				OAuth\LiveId::getServiceName(),
				OAuth\Yandex::getServiceName(),
				OAuth\Mailru::getServiceName(),
				OAuth\Office365::getServiceName(),
			];
		}

		return $knownServices;
	}

	/**
	 * Returns helper instance
	 *
	 * @param string $service Service name.
	 * @return \Bitrix\Mail\Helper\OAuth|false
	 */
	public static function getInstance($service = null): bool|OAuth
	{
		if (get_called_class() != get_class())
		{
			$className = get_called_class();
			$service = $className::getServiceName();
		}
		else
		{
			if (!in_array($service, self::getKnownServices()))
			{
				return false;
			}

			$className = sprintf('%s\OAuth\%s', __NAMESPACE__, $service);
		}

		if (!Main\Loader::includeModule('socialservices'))
		{
			return false;
		}

		$instance = new $className;

		$instance->service = $service;
		$instance->storedUid = sprintf('%x%x', time(), rand(0, 0xffffffff));

		if (!$instance->check())
		{
			return false;
		}

		return $instance;
	}

	/**
	 * Returns helper instance by packed metadata
	 *
	 * @param string $meta Packed metadata.
	 * @return \Bitrix\Mail\Helper\OAuth|false
	 */
	public static function getInstanceByMeta($meta)
	{
		if ($meta = static::parseMeta($meta))
		{
			if ($instance = self::getInstance($meta['service']))
			{
				if ('oauthb' == $meta['type'])
				{
					$instance->storedUid = $meta['key'];
				}

				return $instance;
			}
		}
	}

	/**
	 * Returns packed metadata for instance
	 *
	 * @return string
	 * @throws Main\ObjectException
	 */
	public function buildMeta()
	{
		return sprintf(
			"\x00oauthb\x00%s\x00%s",
			$this->getServiceName(),
			$this->getStoredUid()
		);
	}

	/**
	 * Returns unpacked metadata
	 *
	 * @param string $meta Packed metadata.
	 * @return array
	 */
	public static function parseMeta($meta)
	{
		$regex = sprintf(
			'/^\x00(oauthb)\x00(%s)\x00([a-f0-9]+)$/',
			join(
				'|',
				array_map(
					function ($item)
					{
						return preg_quote($item, '/');
					},
					self::getKnownServices()
				)
			)
		);

		if (!preg_match($regex, $meta, $matches))
		{
			if (!preg_match('/^\x00(oauth)\x00(google|liveid)\x00(\d+)$/', $meta, $matches))
			{
				return null;
			}
		}

		return array(
			'type' => $matches[1],
			'service' => $matches[2],
			'key' => $matches[3],
		);
	}

	/**
	 * Returns token from socialservices
	 *
	 * @param string $service Service name.
	 * @param string $key User ID.
	 * @return string|null
	 */
	private static function getSocservToken($service, $key)
	{
		if (Main\Loader::includeModule('socialservices'))
		{
			switch ($service)
			{
				case 'google':
					$oauthClient = new \CSocServGoogleOAuth($key);
					$oauthClient->getEntityOAuth()->addScope('https://mail.google.com/');
					break;
				case 'liveid':
					$oauthClient = new \CSocServLiveIDOAuth($key);
					$oauthClient->getEntityOAuth()->addScope(['wl.imap', 'wl.offline_access']);
					break;
			}

			if (!empty($oauthClient))
			{
				return $oauthClient->getStorageToken() ?: false;
			}
		}

		return null;
	}

	/**
	 * Returns token by packed metadata
	 *
	 * @param string $meta Packed metadata.
	 * @param int $expireGapSeconds Gap needed for using token, to ensure when we use token it still alive
	 *
	 * @return string|null
	 */
	public static function getTokenByMeta($meta, int $expireGapSeconds = 10)
	{
		if ($meta = static::parseMeta($meta))
		{
			if ('oauthb' == $meta['type'])
			{
				if ($oauthHelper = self::getInstance($meta['service']))
				{
					return $oauthHelper->getStoredToken($meta['key'], $expireGapSeconds) ?: false;
				}
			}
			else if ('oauth' == $meta['type'])
			{
				return self::getSocservToken($meta['service'], $meta['key']);
			}
		}
	}

	/**
	 * Returns user data by packed metadata
	 *
	 * @param string $meta Packed metadata.
	 * @param boolean $secure Strip raw data (includes tokens).
	 * @return array|null
	 */
	public static function getUserDataByMeta($meta, $secure = true)
	{
		if ($meta = static::parseMeta($meta))
		{
			if ($oauthHelper = self::getInstance($meta['service']))
			{
				if ('oauthb' == $meta['type'])
				{
					$oauthHelper->getStoredToken($meta['key']);
				}
				else if ('oauth' == $meta['type'])
				{
					if ($token = self::getSocservToken($meta['service'], $meta['key']))
					{
						$oauthHelper->getOAuthEntity()->setToken($token);
					}
				}

				return $oauthHelper->getUserData($secure);
			}
		}

		return null;
	}

	/**
	 * Returns service interface entity
	 *
	 * @return mixed
	 */
	public function getOAuthEntity()
	{
		return $this->oauthEntity;
	}

	/**
	 * Returns instance UID
	 *
	 * @return string
	 */
	public function getStoredUid()
	{
		return $this->storedUid;
	}

	/**
	 * Returns server OAuth handler URI
	 *
	 * @param boolean $final Skip Bitrix24 proxy.
	 * @return string
	 */
	public function getRedirect(bool $final = true): string
	{
		if (isModuleInstalled('bitrix24') && defined('BX24_HOST_NAME') && !$final)
		{
			return $this->getControllerUrl() . '/redirect.php';
		}
		else
		{
			$uri = new Uri(Main\Engine\UrlManager::getInstance()->getHostUrl().'/bitrix/tools/mail_oauth.php');
			return $uri->getLocator();
		}
	}

	/**
	 * Returns service OAuth endpoint URI
	 *
	 * @return string
	 */
	public function getUrl(): string
	{
		global $APPLICATION;

		if (isModuleInstalled('bitrix24') && defined('BX24_HOST_NAME'))
		{
			$state = sprintf(
				'%s?%s',
				$this->getRedirect(),
				http_build_query(array(
					'check_key' => \CSocServAuthManager::getUniqueKey(),
					'dummy' => 'https://dummy.bitrix24.com/',
					'state' => rawurlencode(http_build_query(array(
						'service' => $this->service,
						'uid' => $this->storedUid,
					))),
				))
			);
		}
		else
		{
			$state = http_build_query(array(
				'check_key' => \CSocServAuthManager::getUniqueKey(),
				'service' => $this->service,
				'uid' => $this->storedUid,
			));
		}

		return $this->oauthEntity->getAuthUrl($this->getRedirect(false), $state);
	}

	/**
	 * Fetches token by instance UID from DB
	 *
	 * @return array|false
	 */
	protected function fetchStoredToken()
	{
		return Mail\Internals\OAuthTable::getList(array(
			'filter' => array(
				'=UID' => $this->storedUid,
			),
			'order' => array(
				'ID' => 'DESC',
			),
		))->fetch();
	}

	/**
	 * Returns token by instance UID
	 *
	 * @param string $uid Instance UID.
	 * @param int $expireGapSeconds Gap needed for using token, to ensure when we use token it still alive
	 *
	 * @return string|null
	 */
	public function getStoredToken($uid = null, int $expireGapSeconds = 10)
	{
		$token = null;

		if (!empty($uid))
		{
			$this->storedUid = $uid;
		}

		$item = $this->fetchStoredToken();

		if (!empty($item))
		{
			$this->oauthEntity->setToken($token = $item['TOKEN']);
			$this->oauthEntity->setRefreshToken($item['REFRESH_TOKEN']);
			$expireThreshold = time() + $expireGapSeconds;

			if (empty($token) || $item['TOKEN_EXPIRES'] > 0 && $item['TOKEN_EXPIRES'] < $expireThreshold)
			{
				$this->oauthEntity->setToken(null);

				if (!empty($item['REFRESH_TOKEN']))
				{
					if ($this->oauthEntity->getNewAccessToken($item['REFRESH_TOKEN']))
					{
						$tokenData = $this->oauthEntity->getTokenData();

						Mail\Internals\OAuthTable::update(
							$item['ID'],
							array(
								'TOKEN' => $tokenData['access_token'],
								'REFRESH_TOKEN' => $tokenData['refresh_token'],
								'TOKEN_EXPIRES' => $tokenData['expires_in'],
							)
						);
					}
				}

				$token = $this->oauthEntity->getToken();
			}
		}

		return $token;
	}

	/**
	 * Obtains tokens from service
	 *
	 * @param string $code Service authorization code.
	 * @return boolean
	 */
	public function getAccessToken($code = null)
	{
		if ($code)
		{
			$this->oauthEntity->setCode($code);
		}

		$oauthData = $_SESSION['OAUTH_DATA'];

		$result = $this->oauthEntity->getAccessToken($this->getRedirect(false));

		$_SESSION['OAUTH_DATA'] = $oauthData;

		return $result;
	}

	/**
	 * Returns user data
	 *
	 * @param boolean $secure Strip raw data (includes tokens).
	 * @return array|null
	 */
	public function getUserData($secure = true)
	{
		try
		{
			$userData = $this->oauthEntity->getCurrentUser();
		}
		catch (Main\SystemException $e)
		{
		}

		if (!empty($userData))
		{
			return array_merge(
				$this->mapUserData($userData),
				$secure ? array() : array('__data' => $userData)
			);
		}
	}

	/**
	 * Returns unified user data
	 *
	 * @param array $userData User data.
	 * @return array
	 */
	abstract protected function mapUserData(array $userData);

	/**
	 * Returns service name
	 *
	 * @throws \Bitrix\Main\ObjectException
	 * @return string
	 */
	public static function getServiceName()
	{
		throw new Main\ObjectException('abstract');
	}

    public function saveResponse($state): bool
	{
		$this->storedUid = $state['uid'];

		if ($item = $this->fetchStoredToken())
		{
			$this->oauthEntity->setRefreshToken($item['REFRESH_TOKEN']);
		}

		if (!empty($_REQUEST['code']) && \CSocServAuthManager::checkUniqueKey())
		{
			$this->getAccessToken($_REQUEST['code']);

			if ($userData = $this->getUserData(false))
			{
				$fields = [
					'UID' => $this->getStoredUid(),
					'TOKEN' => $userData['__data']['access_token'],
					'REFRESH_TOKEN' => $userData['__data']['refresh_token'],
					'TOKEN_EXPIRES' => $userData['__data']['expires_in'],
				];

				if (empty($item))
				{
					Mail\Internals\OAuthTable::add($fields);
				}
				else
				{
					Mail\Internals\OAuthTable::update($item['ID'], $fields);
				}

				$userDataObject = new UserData(
					(string) $userData['email'],
					(string) $userData['first_name'],
					(string) $userData['last_name'],
					(string) $userData['full_name'],
					(string) $userData['image']
				);

				if (isset($userData['__data']['emailIsIntended']))
				{
					$userDataObject->setEmailIsIntended((bool)$userData['__data']['emailIsIntended']);
				}
				else
				{
					$userDataObject->setEmailIsIntended(false);
				}

				if (isset($userData['__data']['userPrincipalName']))
				{
					$userDataObject->setUserPrincipalName((string)$userData['__data']['userPrincipalName']);
				}

				$this->setPublicUserData($userDataObject);

				return true;
			}
		}

        return false;
	}

	protected function setPublicUserData(UserData $userData): void
	{
		$this->publicUserData = $userData;
	}

	public function getPublicUserData(): UserData
	{
		return $this->publicUserData;
	}

	/**
	 * Handles service response
	 *
	 * @param array $state Response data.
	 * @return void
	 */
	public function handleResponse(array $state, $context = self::WEB_TYPE): void
	{
        if ($this->saveResponse($state))
        {
			if ($context === self::WEB_TYPE)
			{
				?>

				<script type="text/javascript">

					targetWindow = window.opener ? window.opener : window;

					targetWindow.BX.onCustomEvent(
						'OnMailOAuthBCompleted',
						[
							'<?=\CUtil::jsEscape($this->getStoredUid()) ?>',
							'<?=\CUtil::jsEscape($this->getUrl()) ?>',
							<?=$this->getPublicUserData()->getJson() ?>
						]
					);

					if (targetWindow !== window)
					{
						window.close();
					}

				</script>

				<?
			}
			else if ($context === self::MOBILE_TYPE)
			{
				$params = http_build_query([
					'storedUid' => \CUtil::jsEscape($this->getStoredUid()),
					'email' => $this->getPublicUserData()->getEmail(),
				]);
				header('Location: bitrix24://?'.$params);
			}
        }
    }
}
