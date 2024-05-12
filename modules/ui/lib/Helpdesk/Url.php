<?php

namespace Bitrix\UI\Helpdesk;

use Bitrix\Main\Web;

class Url
{
	private Domain $domain;

	public function __construct(bool $useLicenseRegion = false)
	{
		$this->domain = new Domain($useLicenseRegion);
	}

	public function getByPath(string $path): Web\Uri
	{
		return (new Web\Uri($this->domain->get()))->setPath($path);
	}

	public function getDomain(): Domain
	{
		return $this->domain;
	}

	public function getByCodeArticle(string $code): Web\Uri
	{
		return $this->getByPath('/open/code_' . $code . '/');
	}
}