<?php

namespace Bitrix\Main\UrlPreview\Parser;
use Bitrix\Main\UrlPreview\HtmlDocument;
use Bitrix\Main\UrlPreview\Parser;
use Bitrix\Main\Web\Uri;

class Common extends Parser
{
	const MIN_IMAGE_HEIGHT = 100;
	const MIN_IMAGE_WIDTH = 100;

	/** @var array img elements, discovered in the document */
	protected $imgElements = array();

	/**
	 * Parses HTML document's meta tags, and fills document's metadata.
	 *
	 * @param HtmlDocument $document HTML document to scan for metadata.
	 * @return void
	 */
	public function handle(HtmlDocument $document)
	{
		if($document->getTitle() == '')
		{
			$document->setTitle($this->getTitle($document));
		}

		if($document->getDescription() == '')
		{
			$document->setDescription($document->getMetaContent('description'));
		}

		$this->imgElements = $document->extractElementAttributes('img');
		if($document->getImage() == '')
		{
			$image = $this->getImage($document);
			if($image <> '')
			{
				$document->setImage($image);
			}
			else
			{
				$imageCandidates = $this->getImageCandidates();
				if(count($imageCandidates) === 1)
				{
					$document->setImage($imageCandidates[0]);
				}
				else if(count($imageCandidates) > 1)
				{
					$document->setExtraField('IMAGES', $imageCandidates);
				}
			}
		}
		if($document->getExtraField('VIDEO') == '')
		{
			preg_match_all("/<video.+?<\/video>/mis", $document->getHtml(), $videoTags);
			foreach($videoTags[0] as $videoTag)
			{
				$videoInfo = $this->getVideoInfo($videoTag);
				if(!empty($videoInfo))
				{
					$document->setExtraField('VIDEO', $videoInfo['src']);
					$document->setExtraField('VIDEO_TYPE', $videoInfo['type']);
					$document->setExtraField('VIDEO_WIDTH', $videoInfo['width']);
					$document->setExtraField('VIDEO_HEIGHT', $videoInfo['height']);
				}
			}
		}
	}

	/**
	 * @param HtmlDocument $document HTML document to scan for title.
	 * @return string
	 */
	protected function getTitle(HtmlDocument $document)
	{
		$title = $document->getMetaContent('title');
		if($title <> '')
		{
			return $title;
		}

		preg_match('/<title>(.+?)<\/title>/mis', $document->getHtml(), $matches);
		return ($matches[1] ?? null);
	}

	/**
	 * @param HtmlDocument $document
	 * @return string
	 */
	protected function getImage(HtmlDocument $document)
	{
		$result = $document->getLinkHref('image_src');
		if($result <> '')
		{
			return $result;
		}

		foreach($this->imgElements as $imgElement)
		{
			if(isset($imgElement['rel']) && $imgElement['rel'] == 'image_src')
			{
				$result = $imgElement['src'];
				return $result;
			}
		}

		return null;
	}

	/**
	 * Iterates through img elements, and return array of urls of images, which size is greater then 100pxx100px
	 * @return array
	 */
	protected function getImageCandidates()
	{
		$result = array();
		foreach ($this->imgElements as $imgElement)
		{
			$imageDimensions = $this->getImageDimensions($imgElement);
			if($imageDimensions['width'] >= self::MIN_IMAGE_WIDTH && $imageDimensions['height'] >= self::MIN_IMAGE_HEIGHT)
			{
				$result[] = $imgElement['src'];
			}
		}
		return $result;
	}

	/**
	 * Returns size of the img element
	 * @param array $imageAttributes Array of the attributes of the img tag.
	 * @return array Returns array with keys width and height.
	 */
	protected function getImageDimensions(array $imageAttributes)
	{
		$result = array(
			'width' => null,
			'height' => null
		);

		foreach(array_keys($result) as $imageDimension)
		{
			if(isset($imageAttributes[$imageDimension]))
			{
				$result[$imageDimension] = $imageAttributes[$imageDimension];
			}
			else if(isset($imageAttributes['style']) && preg_match('/'.$imageDimension.':\s*(\d+?)px/', $imageAttributes['style'], $matches))
			{
				$result[$imageDimension] = $matches[1];
			}
		}
		return $result;
	}

	/**
	 * Parse one <video> tag and try to get valid information off it.
	 *
	 * @param string $html - one <video> from the document.
	 * @return array
	 */
	protected function getVideoInfo($html = '')
	{
		$maxWeight = -1;
		$result = array();
		$uri = new Uri('/');
		$document = new HtmlDocument($html, $uri);
		$videoElements = $document->extractElementAttributes('video');
		foreach ($videoElements as $videoElement)
		{
			if (!isset($videoElement['src']))
			{
				$sourceElements = $document->extractElementAttributes('source');
				foreach ($sourceElements as $sourceElement)
				{
					if (
						isset($sourceElement['type'])
						&& $this->isValidVideoMimeType($sourceElement['type'])
					)
					{
						$videoElement['src'] = $sourceElement['src'];
						$videoElement['type'] = $sourceElement['type'];
						break;
					}
				}
			}
			if (isset($videoElement['src']))
			{
				if (
					(isset($videoElement['width']) &&
					isset($videoElement['height']) &&
					(int)$videoElement['width'] * (int)$videoElement['height'] > $maxWeight) ||
					(empty($result))
				)
				{
					$result = $videoElement;
					$maxWeight = 0;
					if(isset($videoElement['width']) && isset($videoElement['height']))
					{
						$maxWeight = (int)$videoElement['width'] * (int)$videoElement['height'];
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Returns true if $type is a valid video mime-type.
	 *
	 * @param string $type
	 * @return bool
	 */
	protected function isValidVideoMimeType($type = '')
	{
		if(empty($type))
		{
			return false;
		}

		static $validTypes = array(
			'video/mp4', 'video/x-flv', 'video/webm', 'video/ogg', 'video/quicktime'
		);

		return in_array($type, $validTypes);
	}
}