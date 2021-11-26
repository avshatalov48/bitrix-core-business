<?php

namespace Bitrix\Main\UrlPreview\Parser;

use Bitrix\Main\UrlPreview\HtmlDocument;
use Bitrix\Main\UrlPreview\UrlPreview;
use Bitrix\Main\Web\HttpClient;

class RuTube extends OpenGraph
{
	public function handle(HtmlDocument $document)
	{
		parent::handle($document);

		$video = $document->getExtraField('VIDEO');
		if (!empty($video) && $document->getExtraField('VIDEO_TYPE') === 'text/html')
		{
			$width = $document->getExtraField('VIDEO_WIDTH');
			if (!$width || $width > UrlPreview::IFRAME_MAX_WIDTH)
			{
				$width = UrlPreview::IFRAME_MAX_WIDTH;
				$document->setExtraField('VIDEO_WIDTH', $width);
			}
			$height = $document->getExtraField('VIDEO_HEIGHT');
			if (!$height || $height > UrlPreview::IFRAME_MAX_HEIGHT)
			{
				$height = UrlPreview::IFRAME_MAX_HEIGHT;
				$document->setExtraField('VIDEO_HEIGHT', $height);
			}
			$iframe = '<iframe src="'.$video.'" allowfullscreen="" width="'.$width.'" height="'.$height.'" frameborder="0"></iframe>';
			$document->setEmbed($iframe);
		}
	}
}