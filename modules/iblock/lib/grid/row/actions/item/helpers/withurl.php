<?php

namespace Bitrix\Iblock\Grid\Row\Actions\Item\Helpers;

use CUtil;

trait WithUrl
{
	protected string $url;

	public function setUrl(string $url): void
	{
		$this->url = $url;
	}

	protected function getUrlForOnclick(): string
	{
		return CUtil::JSEscape($this->url);
	}
}
