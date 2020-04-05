<?php

namespace Bitrix\Main\UrlPreview\Parser;

use Bitrix\Main\UrlPreview\HtmlDocument;

class Instagram extends Oembed
{
	protected function detectOembedLink(HtmlDocument $document)
	{
		$this->metadataType = 'json';
		$this->metadataUrl = 'https://api.instagram.com/oembed/?url='.$document->getUri()->getLocator().'&hidecaption=1';
		return true;
	}
}
