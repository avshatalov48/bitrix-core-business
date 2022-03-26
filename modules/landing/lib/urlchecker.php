<?php
namespace Bitrix\Landing;

use Bitrix\Landing\Internals\UrlCheckerStatusTable;
use Bitrix\Landing\Internals\UrlCheckerWhitelistTable;
use Bitrix\Landing\Internals\UrlCheckerHostTable;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

/**
 * Class for check urls at external virus tools
 */
class UrlChecker
{
	private const EXTERNAL_CHECKER_URL_LOAD_URL = 'https://www.virustotal.com/api/v3/urls';
	private const EXTERNAL_CHECKER_URL_GET_ANALYSE = 'https://www.virustotal.com/api/v3/analyses/';

	private const STATUS_KEY_GOOD = 'harmless';
	private const STATUS_KEY_AVERAGE = 'suspicious';
	private const STATUS_KEY_BAD = 'malicious';

	private const AVERAGE_STATUS_PERCENT = 33;

	private $apiKey;
	private $hostUrl;

	/**
	 * UrlChecker constructor.
	 *
	 * @param string $apiKey Google Service api key.
	 * @param string|null $hostUrl External URL, where was checking and bad URL was detected.
	 */
	public function __construct(string $apiKey, ?string $hostUrl = null)
	{
		$this->apiKey = $apiKey;
		$this->hostUrl = $hostUrl;
		if ($this->hostUrl)
		{
			$this->hostUrl = mb_strtolower(trim($this->hostUrl));
		}
	}

	/**
	 * Checks passed url for some threats.
	 * Returns threat's status or null on no threats.
	 *
	 * @param string $url
	 * @return string|null
	 */
	protected function getUrlStatus(string $url): ?string
	{
		try
		{
			$http = new HttpClient;
			$http->setHeader('x-apikey', $this->apiKey);
			$res = Json::decode($http->post(self::EXTERNAL_CHECKER_URL_LOAD_URL, [
				'url' => $url,
			]));
			if (!isset($res['error']) && $res['data'] && $res['data']['id'])
			{
				$resAnalysis = Json::decode($http->get(self::EXTERNAL_CHECKER_URL_GET_ANALYSE . $res['data']['id']));
				if (isset($resAnalysis['error']))
				{
					// todo: set VT error
					return $res['error']['status'];
				}
				$stats = [
					self::STATUS_KEY_GOOD => $resAnalysis['data']['attributes']['stats'][self::STATUS_KEY_GOOD],
					self::STATUS_KEY_AVERAGE => $resAnalysis['data']['attributes']['stats'][self::STATUS_KEY_AVERAGE],
					self::STATUS_KEY_BAD => $resAnalysis['data']['attributes']['stats'][self::STATUS_KEY_BAD],
				];

				// check stats
				$total = $stats[self::STATUS_KEY_GOOD] + $stats[self::STATUS_KEY_AVERAGE] + $stats[self::STATUS_KEY_BAD];
				if (
					$stats[self::STATUS_KEY_BAD] === 0
					&& $stats[self::STATUS_KEY_AVERAGE] <= $total * self::AVERAGE_STATUS_PERCENT * 0.01
				)
				{
					return null;
				}

				return
					self::STATUS_KEY_GOOD . ':' . $stats[self::STATUS_KEY_GOOD]
					. '_' . self::STATUS_KEY_AVERAGE . ':' . $stats[self::STATUS_KEY_AVERAGE]
					. '_' . self::STATUS_KEY_BAD . ':' . $stats[self::STATUS_KEY_BAD];
			}
		}
		catch (\Exception $e){}

		return null;
	}

	/**
	 * Saves host for specified status.
	 *
	 * @param int $statusId Status id.
	 * @return void
	 */
	protected function saveHost(int $statusId): void
	{
		if (!$this->hostUrl)
		{
			return;
		}
		$res = UrlCheckerHostTable::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'STATUS_ID' => $statusId,
				'=HOST' => $this->hostUrl
			],
			'limit' => 1
		]);
		if (!$res->fetch())
		{
			UrlCheckerHostTable::add([
				'STATUS_ID' => $statusId,
				'HOST' => $this->hostUrl
			])->isSuccess();
		}
	}

	/**
	 * Checks passed url for some threats. Returns true on no threats.
	 * Before external checking check whitelist list and stored links.
	 *
	 * @param string $url
	 * @return bool
	 */
	public function check(string $url): bool
	{
		$url = mb_strtolower($url);
		$urlMd5 = md5(explode('//', $url)[1] ?? $url);
		$urlParts = parse_url($url);
		$domain = $urlParts['host'] ?? null;

		if (!$domain)
		{
			return true;
		}

		// check domain in white list
		$res = UrlCheckerWhitelistTable::getList([
			'filter' => [
				'=DOMAIN' => $domain
			],
			'limit' => 1
		]);
		if ($res->fetch())
		{
			return true;
		}

		// check exists url
		$res = UrlCheckerStatusTable::getList([
			'select' => [
				'ID', 'STATUS'
			],
			'filter' => [
				'=HASH' => $urlMd5
			]
		]);
		if ($row = $res->fetch())
		{
			if ($row['STATUS'])
			{
				$this->saveHost($row['ID']);
			}
			return !$row['STATUS'];
		}

		// new check
		$status = $this->getUrlStatus($url);
		$res = UrlCheckerStatusTable::add([
			'STATUS' => $status,
			'HASH' => $urlMd5,
			'URL' => $url
		]);
		// save host if status is bad
		if ($res->isSuccess() && $status)
		{
			$this->saveHost($res->getId());
		}

		return $status === null;
	}
}
