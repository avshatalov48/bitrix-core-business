<?

namespace Bitrix\Main\UrlPreview\Parser;

use Bitrix\Main\UrlPreview\HtmlDocument;
use Bitrix\Main\UrlPreview\Parser;

class OpenGraph extends Parser
{
	/**
	 * Parses HTML documents OpenGraph metadata
	 *
	 * @param HtmlDocument $document HTML document to be parsed.
	 * @return void
	 */
	public function handle(HtmlDocument $document)
	{
		if($document->getTitle() == '')
		{
			$ogTitle = $document->getMetaContent('og:title');
			if($ogTitle <> '')
			{
				$document->setTitle($ogTitle);
			}
		}

		if($document->getDescription() == '')
		{
			$ogDescription = $document->getMetaContent('og:description');
			if($ogDescription <> '')
			{
				$document->setDescription($ogDescription);
			}
		}

		if($document->getImage() == '')
		{
			$ogImage = $document->getMetaContent('og:image:secure_url') ?: $document->getMetaContent('og:image');
			if($ogImage <> '')
			{
				$document->setImage($ogImage);
			}
		}

		$this->parseVideoData($document);

		if(!$document->getExtraField('SITE_NAME'))
		{
			$ogSiteName = $document->getMetaContent('og:site_name');
			if($ogSiteName <> '')
			{
				$document->setExtraField('SITE_NAME', $ogSiteName);
			}
		}

		/*	Not really opengraph property :), but it's placed in opengraph parser to prevent executing full parser chain
			just to get favicon */
		if(!$document->getExtraField('FAVICON'))
		{
			if($favicon = $document->getLinkHref('icon'))
			{
				$document->setExtraField('FAVICON', $favicon);
			}
		}
	}

	protected function parseVideoData(HtmlDocument $document)
	{
		if(!$document->getExtraField('VIDEO'))
		{
			$ogVideo = $document->getMetaContent('og:video')
				?? $document->getMetaContent('og:video:secure_url')
				?? $document->getMetaContent('og:video:url')
				?? '';
			if($ogVideo <> '')
			{
				$document->setExtraField('VIDEO', $ogVideo);
			}
		}

		if(!$document->getExtraField('VIDEO_TYPE'))
		{
			$ogVideoType = $document->getMetaContent('og:video:type') ?? '';
			if($ogVideoType <> '')
			{
				$document->setExtraField('VIDEO_TYPE', $ogVideoType);
			}
		}

		if(!$document->getExtraField('VIDEO_WIDTH'))
		{
			$ogVideoWidth = $document->getMetaContent('og:video:width') ?? '';
			if($ogVideoWidth <> '')
			{
				$document->setExtraField('VIDEO_WIDTH', $ogVideoWidth);
			}
		}

		if(!$document->getExtraField('VIDEO_HEIGHT'))
		{
			$ogVideoHeight = $document->getMetaContent('og:video:height') ?? '';
			if($ogVideoHeight <> '')
			{
				$document->setExtraField('VIDEO_HEIGHT', $ogVideoHeight);
			}
		}
	}
}