<?php

namespace Bitrix\Main\UrlPreview;

use Bitrix\Main\Context;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Uri;

class HtmlDocument
{
	const MAX_IMAGES = 4;
	const MAX_IMAGE_URL_LENGTH = 255;

	/** @var \Bitrix\Main\Web\Uri */
	protected $uri;

	/** @var string */
	protected $html;

	/** @var  string */
	protected $htmlEncoding;

	/** @var array
	 * Allowed keys so far: TITLE, DESCRIPTION, IMAGE
	 */
	protected $metadata = array(
		"TITLE" => null,
		"DESCRIPTION" => null,
		"IMAGE" => null,
		"EMBED" => null
	);

	/** @var array  */
	protected $metaElements = array();

	/** @var array */
	protected $linkElements = array();

	/**
	 * HtmlDocument constructor.
	 *
	 * @param string $html Document HTML code.
	 * @param Uri $uri Document's URL.
	 */
	public function __construct($html, Uri $uri)
	{
		$this->html = $html;
		$this->uri = $uri;
	}

	/**
	 * Returns Uri of the document
	 *
	 * @return Uri
	 */
	public function getUri()
	{
		return $this->uri;
	}

	/**
	 * Returns full html code of the document
	 *
	 * @return string
	 */
	public function getHtml()
	{
		return $this->html;
	}

	/**
	 * Returns true if metadata is complete
	 *
	 * @return bool
	 */
	public function checkMetadata()
	{
		$result = (    $this->metadata['TITLE'] != ''
					&& $this->metadata['DESCRIPTION'] != ''
					&& $this->metadata['IMAGE'] != '');

		if($this->isEmbeddingAllowed())
		{
			$result = $result && $this->metadata['EMBED'] != '';
		}

		return $result;
	}

	/**
	 * Returns metadata, extracted from the page. Should return an array with required key TITLE
	 * and optional keys DESCRIPTION and URL
	 *
	 * @return array|false
	 */
	public function getMetadata()
	{
		return $this->metadata;
	}

	/**
	 * Returns document's TITLE metadata
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->metadata['TITLE'];
	}

	/**
	 * Sets document's TITLE metadata
	 *
	 * @param string $title Title.
	 * @return void
	 */
	public function setTitle($title)
	{
		if(strlen($title) > 0)
		{
			$this->metadata['TITLE'] = $this->filterString($title);
		}
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->metadata['DESCRIPTION'];
	}

	/**
	 * Sets document's DESCRIPTION metadata
	 *
	 * @param string $description Description.
	 * @return void
	 */
	public function setDescription($description)
	{
		if(strlen($description) > 0)
		{
			$this->metadata['DESCRIPTION'] = $this->filterString($description);
		}
	}

	/**
	 * @return string Main image's url.
	 */
	public function getImage()
	{
		return $this->metadata['IMAGE'];
	}

	/**
	 * Sets document's IMAGE metadata
	 *
	 * @param string $image Main image's url.
	 * @return void
	 */
	public function setImage($image)
	{
		if(strlen($image) > 0)
		{
			$imageUrl = $this->normalizeImageUrl($image);
			if(!is_null($imageUrl) && $this->validateImage($imageUrl))
				$this->metadata['IMAGE'] = $imageUrl;
		}
	}

	/**
	 * @return string HTML code to embed url to the page.
	 */
	public function getEmdbed()
	{
		return $this->metadata['EMBED'];
	}

	/**
	 * Sets document's EMBED metadata, if site is allowed to be embedded.
	 *
	 * @param string $embed HTML code for embedding object to the page.
	 * @return void
	 */
	public function setEmbed($embed)
	{
		if($this->isEmbeddingAllowed())
		{
			$this->metadata['EMBED'] = $embed;
		}
	}

	/**
	 * Sets additional metadata field.
	 * @param string $fieldName Name of the field. Expected values:
	 * <li>FAVICON: $fieldValue must contain the url of document's favicon
	 * <li>IMAGES: $fieldValue must be the array of urls of images, detected in the document
	 * <li>In other cases, $fieldValue must contain plain text.
	 * @param string $fieldValue Field value.
	 * @return void
	 */
	public function setExtraField($fieldName, $fieldValue)
	{
		if($fieldName == 'FAVICON')
		{
			$this->metadata['EXTRA'][$fieldName] = $this->convertRelativeUriToAbsolute($fieldValue);
		}
		else if($fieldName == 'IMAGES')
		{
			if(is_array($fieldValue))
			{
				$this->metadata['EXTRA']['IMAGES'] = array();
				foreach($fieldValue as $image)
				{
					$image = $this->normalizeImageUrl($image);
					if($image)
						$this->metadata['EXTRA']['IMAGES'][] = $image;

					if(count($this->metadata['EXTRA']['IMAGES']) >= self::MAX_IMAGES)
						break;
				}
			}
		}
		else
		{
			$this->metadata['EXTRA'][$fieldName] = $this->filterString($fieldValue);
		}
	}

	/**
	 * Returns value of the additional metadata field
	 * @param string $fieldName Name of the field.
	 * @return string|null Value of the additional metadata field.
	 */
	public function getExtraField($fieldName)
	{
		return isset($this->metadata['EXTRA'][$fieldName]) ? $this->metadata['EXTRA'][$fieldName] : null;
	}

	/**
	 * Set HTML document encoding
	 *
	 * @param string $encoding Document's encoding.
	 * @return void
	 */
	public function setEncoding($encoding)
	{
		$encoding = trim($encoding, " \t\n\r\0\x0B'\"");
		$this->htmlEncoding = $encoding;
	}

	/**
	 * @return string Document encoding.
	 */
	public function getEncoding()
	{
		if(strlen($this->htmlEncoding) > 0)
		{
			return $this->htmlEncoding;
		}

		$this->htmlEncoding = $this->detectEncoding();
		return $this->htmlEncoding;
	}

	/**
	 * Auto-detect and set HTML document encoding
	 *
	 * @return string Detected encoding.
	 */
	public function detectEncoding()
	{
		$result = '';
		if(count($this->metaElements) == 0)
		{
			$this->metaElements = $this->extractElementAttributes('meta');
		}

		foreach($this->metaElements as $metaElement)
		{
			if(isset($metaElement['http-equiv']) && strtolower($metaElement['http-equiv']) == 'content-type')
			{
				if(preg_match('/charset=([\w-]+)/', $metaElement['content'], $matches))
				{
					$result = $matches[1];
					break;
				}
			}
			else if(isset($metaElement['charset']))
			{
				$result = $metaElement['charset'];
				break;
			}
		}

		return $result;
	}

	/**
	 * Parses html content for attributes of the specified elements and fills $destination array with found attributes
	 *
	 * @param string $tagName Name of the tag.
	 * @return array
	 */
	public function extractElementAttributes($tagName)
	{
		$results = array();
		preg_match_all("/<$tagName.+?>/mis", $this->html, $elements);

		foreach($elements[0] as $element)
		{
			preg_match_all('/(?:([\w-_]+)=([\'"])(.*?)\g{-2}\s*)/mis', $element, $matches);

			$elementAttributes = array();
			foreach($matches[1] as $k => $attributeName)
			{
				$attributeName = strtolower($attributeName);
				$attributeValue = $matches[3][$k];
				$elementAttributes[$attributeName] = $attributeValue;
			}

			$results[] = $elementAttributes;
		}

		return $results;
	}

	/**
	 * Returns value of the content attribute
	 *
	 * @param string $name Value of a name or property attribute.
	 * @return string
	 * */
	public function getMetaContent($name)
	{
		if(count($this->metaElements) == 0)
		{
			$this->metaElements = $this->extractElementAttributes('meta');
		}
		$name = strtolower($name);

		foreach ($this->metaElements as $metaElement)
		{
			if ((isset($metaElement['name']) && strtolower($metaElement['name']) === $name
				|| isset($metaElement['property']) && strtolower($metaElement['property']) === $name)
				&& strlen($metaElement['content']) > 0)
			{
				return $metaElement['content'];
			}
		}

		return null;
	}

	/**
	 * Returns value of the href attribute.
	 *
	 * @param string $rel Value of the rel attribute.
	 * @return string
	 */
	public function getLinkHref($rel)
	{
		if(count($this->linkElements) == 0)
		{
			$this->linkElements = $this->extractElementAttributes('link');
		}
		$rel = strtolower($rel);

		foreach ($this->linkElements as $linkElement)
		{
			if(isset($linkElement['rel'])
				&& strtolower($linkElement['rel']) == $rel
				&& strlen($linkElement['href']) > 0)
			{
				return $linkElement['href'];
			}
		}

		return null;
	}

	/**
	 * Sanitizes string and converts it to the site's charset.
	 *
	 * @param string $str Input string.
	 * @return string
	 */
	protected function filterString($str)
	{
		$str = html_entity_decode($str, ENT_QUOTES, $this->getEncoding());
		$str = Encoding::convertEncoding($str, $this->getEncoding(), Context::getCurrent()->getCulture()->getCharset());
		$str = trim($str);
		$str = strip_tags($str);

		return $str;
	}

	/**
	 * Converts relative url to the absolute, considering document's url.
	 * @param string $uri Relative url.
	 * @return null|string Absolute url or null if relative url contains errors.
	 */
	protected function convertRelativeUriToAbsolute($uri)
	{
		if(strpos($uri, '//') === 0)
			$uri = $this->uri->getScheme().":".$uri;

		if(preg_match('#^https?://#', $uri))
			return $uri;

		$pars = parse_url($uri);
		if($pars === false)
			return null;

		if(isset($pars['host']))
		{
			$result = $uri;
		}
		else if(isset($pars['path']))
		{
			if(substr($pars['path'], 0, 1) !== '/')
			{
				$pathPrefix = preg_replace('/^(.+?)([^\/]*)$/', '$1', $this->uri->getPath());
				$pars['path'] = $pathPrefix.$pars['path'];
			}

			$uriPort = '';
			if ($this->uri->getScheme() === 'http' && $this->uri->getPort() != '80'
				|| $this->uri->getScheme() === 'https' && $this->uri->getPort() != '443')
			{
				$uriPort = ':'.$this->uri->getPort();
			}

			$result = $this->uri->getScheme().'://'
				.$this->uri->getHost()
				.$uriPort
				.$pars['path']
				.(isset($pars['query']) ? '?'.$pars['query'] : '')
				.(isset($pars['fragment']) ? '#'.$pars['fragment'] : '');
		}
		else
		{
			$result = null;
		}

		return $result;
	}

	/**
	 * Transforms image's URL from relative to absolute and checks length of the resulting URL.
	 * @param string $url Image's URL.
	 * @return string|null Absolute image's URL, or null if URL is incorrect or too long.
	 */
	protected function normalizeImageUrl($url)
	{
		$url = $this->convertRelativeUriToAbsolute($url);
		if(strlen($url) > self::MAX_IMAGE_URL_LENGTH)
			$url = null;
		return $url;
	}

	/**
	 * Validates mime-type of the image
	 * @param string $url Absolute image's URL.
	 * @return bool
	 */
	protected function validateImage($url)
	{
		$httpClient = new HttpClient();
		$httpClient->setTimeout(5);
		$httpClient->setStreamTimeout(5);
		$httpClient->setHeader('User-Agent', UrlPreview::USER_AGENT, true);
		if(!$httpClient->query('GET', $url))
			return false;

		if($httpClient->getStatus() !== 200)
			return false;

		$contentType = strtolower($httpClient->getHeaders()->getContentType());
		if(strpos($contentType, 'image/') === 0)
			return true;
		else
			return false;
	}

	/**
	 * Returns true if document's site is allowed to be embedded.
	 * @return bool
	 */
	protected function isEmbeddingAllowed()
	{
		return UrlPreview::isHostTrusted($this->uri);
	}
}
