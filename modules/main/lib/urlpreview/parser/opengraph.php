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
		if(strlen($document->getTitle()) == 0)
		{
			$ogTitle = $document->getMetaContent('og:title');
			if(strlen($ogTitle) > 0)
			{
				$document->setTitle($ogTitle);
			}
		}

		if(strlen($document->getDescription()) == 0)
		{
			$ogDescription = $document->getMetaContent('og:description');
			if(strlen($ogDescription) > 0)
			{
				$document->setDescription($ogDescription);
			}
		}

		if(strlen($document->getImage()) == 0)
		{
			$ogImage = $document->getMetaContent('og:image:secure_url') ?: $document->getMetaContent('og:image');
			if(strlen($ogImage) > 0)
			{
				$document->setImage($ogImage);
			}
		}

		$this->parseVideoData($document);

		if(!$document->getExtraField('SITE_NAME'))
		{
			$ogSiteName = $document->getMetaContent('og:site_name');
			if(strlen($ogSiteName) > 0)
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
			$ogVideo = $document->getMetaContent('og:video') ?: $document->getMetaContent('og:video');
			if(strlen($ogVideo) > 0)
			{
				$document->setExtraField('VIDEO', $ogVideo);
			}
		}

		if(!$document->getExtraField('VIDEO_TYPE'))
		{
			$ogVideoType = $document->getMetaContent('og:video:type') ?: $document->getMetaContent('og:video:type');
			if(strlen($ogVideoType) > 0)
			{
				$document->setExtraField('VIDEO_TYPE', $ogVideoType);
			}
		}


		if(!$document->getExtraField('VIDEO_WIDTH'))
		{
			$ogVideoWidth = $document->getMetaContent('og:video:width') ?: $document->getMetaContent('og:video:width');
			if(strlen($ogVideoWidth) > 0)
			{
				$document->setExtraField('VIDEO_WIDTH', $ogVideoWidth);
			}
		}

		if(!$document->getExtraField('VIDEO_HEIGHT'))
		{
			$ogVideoHeight = $document->getMetaContent('og:video:height') ?: $document->getMetaContent('og:video:height');
			if(strlen($ogVideoHeight) > 0)
			{
				$document->setExtraField('VIDEO_HEIGHT', $ogVideoHeight);
			}
		}
	}
}