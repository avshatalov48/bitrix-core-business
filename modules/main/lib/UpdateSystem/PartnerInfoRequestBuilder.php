<?php

namespace Bitrix\Main\UpdateSystem;

use Bitrix\Main\Application;
use Bitrix\Main\License;
use Bitrix\Main\Web\HttpHeaders;
use Bitrix\Main\Web\Uri;

class PartnerInfoRequestBuilder implements RequestBuilderInterface
{
	private Request $request;
	private License $license;
	private string $name;
	private string $phone;
	private string $email;

	public function __construct(string $name, string $phone, string $email)
	{
		$this->name = $name;
		$this->email = $email;
		$this->phone = $phone;
		$this->request = new Request;
		$this->license = Application::getInstance()->getLicense();
	}

	public function setHeaders(): self
	{
		$headers = new HttpHeaders();
		$this->request->setHeaders($headers);

		return $this;
	}

	public function setUrl(): self
	{
		$url = new Uri($this->license->getDomainStoreLicense() . '/key_update.php');
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

	public function setBody(): self
	{
		$portalInfo = new PortalInfo();
		$parameters = [
			'action' => 'send_partner_info',
			'license_key' => $this->license->getHashLicenseKey(),
			'partner_id' => $this->license->getPartnerId(),
			'phone' => $this->phone,
			'email' => $this->email,
			'name' => $this->name,
			'site' => $_SERVER['HTTP_HOST'],
		];

		$this->request->setBody($parameters);

		return $this;
	}

	public function build(): Request
	{
		return $this->request;
	}
}