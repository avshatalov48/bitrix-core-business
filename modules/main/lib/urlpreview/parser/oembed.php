<?php

namespace Bitrix\Main\UrlPreview\Parser;

use Bitrix\Main\Text\Encoding;
use Bitrix\Main\UrlPreview\UrlPreview;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\UrlPreview\HtmlDocument;
use Bitrix\Main\UrlPreview\Parser;

class Oembed extends Parser
{
	const OEMBED_TYPE_XML = "text/xml+oembed";
	const OEMBED_TYPE_JSON ="application/json+oembed";

	/** @var string Possible values: (json|xml) */
	protected $metadataType;

	/** @var string */
	protected $metadataUrl;

	/** @var  string */
	protected $metadataEncoding;

	/**
	 * Downloads and parses HTML's document metadata, formatted with oEmbed standard.
	 *
	 * @param HtmlDocument $document HTML document.
	 * @param HttpClient|null $httpClient
	 */
	public function handle(HtmlDocument $document, HttpClient $httpClient = null)
	{
		if(!$this->detectOembedLink($document) || $this->metadataUrl == '')
		{
			return;
		}

		$isHttpClientPassed = true;
		if(!$httpClient)
		{
			$httpClient = $this->initHttpClient();
			$isHttpClientPassed = false;
		}
		$rawMetadata = $this->getRawMetaData($httpClient);
		// if request was served through http - try to switch to https
		if(
			(
				!$rawMetadata
				|| $httpClient->getStatus() === 403
			)
			&& str_starts_with($this->metadataUrl, 'http://'))
		{
			if(!$isHttpClientPassed)
			{
				$httpClient = $this->initHttpClient();
			}
			$metadataUrl = str_replace('http://', 'https://', $this->metadataUrl);
			$rawMetadata = $httpClient->get($metadataUrl);
		}

		if($rawMetadata === false)
		{
			return;
		}

		$parsedMetadata = $this->parseMetadata($rawMetadata);
		if($parsedMetadata !== false)
		{
			if($this->metadataEncoding <> '' && $document->getEncoding() !== $this->metadataEncoding)
			{
				$parsedMetadata = Encoding::convertEncoding($parsedMetadata, $this->metadataEncoding, $document->getEncoding());
			}

			if($document->getTitle() == '' && !empty($parsedMetadata['title']))
			{
				$document->setTitle($parsedMetadata['title']);
			}

			if($document->getImage() == '' && !empty($parsedMetadata['thumbnail_url']))
			{
				$image = $parsedMetadata['thumbnail_url'];
				if (is_array($image))
				{
					$image = reset($image);
				}
				$document->setImage($image);
			}

			if($document->getEmdbed() == '' && !empty($parsedMetadata['html']))
			{
				$document->setEmbed($parsedMetadata['html']);
			}

			if($document->getExtraField('PROVIDER_NAME') == '' && !empty($parsedMetadata['provider_name']))
			{
				$document->setExtraField('PROVIDER_NAME', $parsedMetadata['provider_name']);
			}

			if($document->getExtraField('VIDEO_WIDTH') == '' && !empty($parsedMetadata['width']))
			{
				$document->setExtraField('VIDEO_WIDTH', $parsedMetadata['width']);
			}

			if($document->getExtraField('VIDEO_HEIGHT') == '' && !empty($parsedMetadata['height']))
			{
				$document->setExtraField('VIDEO_HEIGHT', $parsedMetadata['height']);
			}
		}
	}

	/**
	 * @param HtmlDocument $document
	 * @return bool
	 */
	protected function detectOembedLink(HtmlDocument $document)
	{
		preg_match_all('/<link[^>]*rel\s*=\s*["\']?alternate["\']?[^>]*?>/', $document->getHtml(), $linkElements);

		foreach($linkElements[0] as $linkElement)
		{
			$typeJson = (str_contains($linkElement, $this::OEMBED_TYPE_JSON));
			$typeXml = (str_contains($linkElement, $this::OEMBED_TYPE_XML));
			if($typeJson || $typeXml)
			{
				if(preg_match('/href=[\'"](.+?)[\'"]/', $linkElement, $attributes))
				{
					$this->metadataType = ($typeJson ? 'json' : 'xml');
					$this->metadataUrl = htmlspecialcharsback($attributes[1]);
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @param string $rawMetadata
	 * @return array|false
	 */
	protected function parseMetadata($rawMetadata)
	{
		switch($this->metadataType)
		{
			case 'json':
				return $this->parseJsonMetadata($rawMetadata);
				break;
			case 'xml':
				return $this->parseXmlMetadata($rawMetadata);
				break;
		}

		return false;
	}

	protected function parseJsonMetadata($rawMetadata)
	{
		$parsedMetadata = json_decode($rawMetadata, true);
		$this->metadataEncoding = 'UTF-8';

		return $parsedMetadata;
	}

	/**
	 * @param string $rawMetadata
	 * @return array|false
	 */
	protected function parseXmlMetadata($rawMetadata)
	{
		$xml = new \CDataXML();
		if($xml->LoadString($rawMetadata))
		{
			//detect xml encoding
			if(preg_match('/<\?xml[^>]+?encoding=[\'"](.+?)[\'"]\?>/', $rawMetadata, $matches))
				$this->metadataEncoding = $matches[1];
			else
				$this->metadataEncoding = 'UTF-8';

			$result = array();
			$dom = $xml->GetTree();
			$mainNode = $dom->elementsByName('oembed');
			foreach($mainNode[0]->children as $node)
			{
				$result[$node->name] = $node->content;
			}
			return $result;
		}

		return false;
	}

	protected function getRawMetaData(HttpClient $httpClient)
	{
		$rawMetadata = $httpClient->get($this->metadataUrl);

		return $rawMetadata;
	}

	protected function initHttpClient(): HttpClient
	{
		$httpClient = new HttpClient();
		$httpClient->setTimeout(5);
		$httpClient->setStreamTimeout(5);
		$httpClient->setHeader('User-Agent', UrlPreview::USER_AGENT, true);
		$httpClient->setPrivateIp(false);

		return $httpClient;
	}
}
