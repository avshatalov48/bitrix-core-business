<?php

namespace Bitrix\Main\UpdateSystem;

use Bitrix\Main\SystemException;
use Bitrix\Main\Web\HttpHeaders;
use Bitrix\Main\Web\Uri;

class ReincarnationRequestBuilder implements RequestBuilderInterface
{
	private Request $request;
	private Coupon $coupon;

	public function __construct(Coupon $coupon)
	{
		$this->coupon = $coupon;
		$this->request = new Request;
	}

	public function setHeaders(): self
	{
		$headers = new HttpHeaders();
		$headers->add('User-Agent', 'BitrixSMUpdater');
		$headers->add('Content-type', 'application/x-www-form-urlencoded');

		$this->request->setHeaders($headers);

		return $this;
	}

	public function setUrl(): self
	{
		$host = \COption::GetOptionString("main", "update_site", 'mysql.smn');
		$url = new Uri("http://".$host."/bitrix/updates/us_updater_actions.php");
		$this->request->setUrl($url);

		return $this;
	}

	public function setProxy(): self
	{
		$proxyData = [
			'host' => \COption::GetOptionString("main", "update_site_proxy_addr", ""),
			'port' => \COption::GetOptionString("main", "update_site_proxy_port", ""),
			'user' => \COption::GetOptionString("main", "update_site_proxy_user", ""),
			'password' => \COption::GetOptionString("main", "update_site_proxy_pass", ""),
		];

		$this->request->setProxy($proxyData);

		return $this;
	}

	/**
	 * @throws SystemException
	 */
	public function setBody(): self
	{
		$portalInfo = new PortalInfo();
		$common = $portalInfo->common();
		$modules = $this->addPrefixToKey($portalInfo->getModules(), 'bitm_');
		$languages = $this->addPrefixToKey($portalInfo->getLanguages(), 'bitl_');
		$parameters = array_merge(
			$common,
			$modules,
			$languages,
			[
				'coupon' => $this->coupon->getKey(),
				'query_type' => 'reincarnate',
				'NS' => \COption::GetOptionString("main", "update_site_ns", ""),
				'KDS' => \COption::GetOptionString("main", "update_devsrv", ""),
			]
		);

		$this->request->setBody($parameters);

		return $this;
	}

	public function build(): Request
	{
		return $this->request;
	}

	private function addPrefixToKey(array $array, string $prefix): array
	{
		$result = [];
		foreach ($array as $key => $value)
		{
			$result[$prefix.$key] = $value;
		}

		return $result;
	}
}
