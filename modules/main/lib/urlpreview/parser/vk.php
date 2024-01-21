<?php

namespace Bitrix\Main\UrlPreview\Parser;

use Bitrix\Main\UrlPreview\HtmlDocument;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\UrlPreview\UrlPreview;

class Vk extends OpenGraph
{
	public function handle(HtmlDocument $document)
	{
		$uri = $document->getUri();
		if(mb_strpos($uri->getPath(), '/video') !== 0)
		{
			return;
		}

		parent::handle($document);

		if(!empty($document->getExtraField('VIDEO')))
		{
			$ogVideo = $document->getExtraField('VIDEO');
			$uri = new Uri($ogVideo);
			$query = $uri->getQuery();
			if(!empty($query))
			{
				parse_str($query, $params);
				if(isset($params['oid']) && isset($params['id']) && isset($params['hash']))
				{
					$embedUri = new Uri('https://vk.com/video_ext.php');
					$embedUri->addParams(array('oid' => $params['oid'], 'id' => $params['id'], 'hash' => $params['hash']));
					if($document->getExtraField('VIDEO_WIDTH') && $document->getExtraField('VIDEO_WIDTH') < UrlPreview::IFRAME_MAX_WIDTH)
					{
						$width = $document->getExtraField('VIDEO_WIDTH');
					}
					else
					{
						$width = UrlPreview::IFRAME_MAX_WIDTH;
					}
					if($document->getExtraField('VIDEO_HEIGHT') && $document->getExtraField('VIDEO_HEIGHT') < UrlPreview::IFRAME_MAX_HEIGHT)
					{
						$height = $document->getExtraField('VIDEO_HEIGHT');
					}
					else
					{
						$height = UrlPreview::IFRAME_MAX_HEIGHT;
					}
					$iframe = '<iframe src="'.$embedUri->getLocator().'" allowfullscreen="" width="'.$width.'" height="'.$height.'" frameborder="0"></iframe>';
					$document->setEmbed($iframe);
					$document->setExtraField('PROVIDER_NAME', 'VK');
				}
			}
		}
	}
}
