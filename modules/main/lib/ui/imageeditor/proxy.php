<?

namespace Bitrix\Main\UI\ImageEditor;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Web\HttpClient;

/**
 * Class Proxy
 * @package Bitrix\Main\UI\ImageEditor
 */
class Proxy
{
	/** @var Uri */
	protected $uri;

	/** @var array<string> */
	protected $allowedHosts = [];

	/**
	 * Proxy constructor.
	 * @param string $url
	 * @param array<string> $allowedHosts
	 * @throws \Bitrix\Main\SystemException
	 */
	public function __construct($url, $allowedHosts = [])
	{
		$response = static::getResponse();

		if (!static::isAuthorized())
		{
			$response->setStatus(401)->flush();
			die('Unauthorized');
		}

		if (is_array($allowedHosts))
		{
			$this->allowedHosts = array_filter($allowedHosts, function($item) {
				return is_string($item) && !empty($item);
			});
		}

		$this->uri = new Uri($url);

		if (!$this->uri->getHost())
		{
			$this->uri->setHost(static::getCurrentHttpHost());
		}
	}

	/**
	 * @return \Bitrix\Main\HttpResponse
	 * @throws \Bitrix\Main\SystemException
	 */
	protected static function getResponse()
	{
		return Application::getInstance()->getContext()->getResponse();
	}

	/**
	 * @return bool
	 */
	protected static function isAuthorized()
	{
		global $USER;
		return ($USER->isAuthorized() && check_bitrix_sessid());
	}

	/**
	 * Gets current http host
	 * @return null|string
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getCurrentHttpHost()
	{
		static $server = null;

		if ($server === null)
		{
			$server = Application::getInstance()->getContext()->getServer();
		}

		return explode(':', $server->getHttpHost())[0];
	}

	/**
	 * Gets allowed hosts
	 * @return array<string>
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function getAllowedHosts()
	{
		return array_merge(
			[$this->getCurrentHttpHost()],
			$this->allowedHosts,
			$this->getUserAllowedHosts()
		);
	}

	/**
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	protected function getUserAllowedHosts()
	{
		static $hosts = null;

		if ($hosts === null)
		{
			$hosts = Option::get('main', 'imageeditor_proxy_white_list', []);
			if (is_string($hosts))
			{
				$hosts = unserialize($hosts);
			}
		}

		return $hosts;
	}

	/**
	 * Checks that this host is allowed
	 * @param string $host
	 * @return bool
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function isAllowedHost($host)
	{
		return (
			in_array($host, $this->getAllowedHosts()) ||
			(in_array('*', $this->getAllowedHosts()) && $this->isEnabledForAll())
		);
	}


	/**
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	protected function isEnabledForAll()
	{
		return static::getEnabledOption() === 'Y';
	}

	/**
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	protected static function isEnabledForWhiteList()
	{
		return static::getEnabledOption() === 'YWL';
	}

	/**
	 * @return string
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	protected static function getEnabledOption()
	{
		static $option = null;

		if ($option === null)
		{
			$option = Option::get('main', 'imageeditor_proxy_enabled', 'N');
		}

		return $option;
	}

	/**
	 * @param $host
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function isEnabledForHost($host)
	{
		return static::isEnabledForWhiteList() && $this->isAllowedHost($host);
	}

	public function output()
	{
		$remoteHost = $this->uri->getHost();
		$currentHost = static::getCurrentHttpHost();
		$response = static::getResponse();

		if ($this->isEnabledForAll() ||
			$this->isEnabledForHost($remoteHost) ||
			$remoteHost === $currentHost)
		{
			$client = new HttpClient();
			$contents = $client->get($this->uri->getUri());

			$response->addHeader('Content-Type', $client->getContentType());
			$response->flush($contents);
		}
		else
		{
			$response->setStatus(404)->flush();
		}
	}
}