<?php
namespace Bitrix\Landing;

use Bitrix\Landing\Internals\UrlCheckerStatusTable;
use Bitrix\Landing\Internals\UrlCheckerWhitelistTable;
use Bitrix\Landing\Internals\UrlCheckerHostTable;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

class UrlChecker
{
	private const EXTERNAL_CHECKER_URL =
		'https://webrisk.googleapis.com/v1/uris:search?'
		. 'threatTypes=MALWARE&threatTypes=SOCIAL_ENGINEERING&threatTypes=UNWANTED_SOFTWARE'
		. '&key=#key#'
		. '&uri=#url#';

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
		$status = [];
		$checkerUrl = $this::EXTERNAL_CHECKER_URL;
		$checkerUrl = str_replace(
			['#key#', '#url#'],
			[$this->apiKey, urlencode($url)],
			$checkerUrl
		);
		try
		{
			$http = new HttpClient;
			$res = Json::decode($http->get($checkerUrl));
			if ($res['error']['status'] ?? null)
			{
				return $res['error']['status'];
			}
			$status = $res['threat']['threatTypes'] ?? [];
		}
		catch (\Exception $e){}

		return $status ? implode(',', $status) : null;
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
