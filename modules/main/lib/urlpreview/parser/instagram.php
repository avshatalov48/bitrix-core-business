<?php

namespace Bitrix\Main\UrlPreview\Parser;

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\UrlPreview\HtmlDocument;

class Instagram extends Oembed
{
	protected $versionApiPrefix = 'v19.0';

	protected const METADATA_TTL = 43200; // 12 hours

	/**
	 * @param HtmlDocument $document
	 * @return bool
	 */
	protected function detectOembedLink(HtmlDocument $document): bool
	{
		$result = false;

		if(Loader::includeModule('socialservices'))
		{
			$urlPreviewEnable = Option::get('socialservices', 'facebook_instagram_url_preview_enable', 'N');
			if($urlPreviewEnable === 'Y')
			{
				$urlAppId = Option::get('socialservices', 'facebook_appid', '');
				$urlAppSecret = Option::get('socialservices', 'facebook_appsecret', '');

				if(
					!empty($urlAppId)
					&& !empty($urlAppSecret)
				)
				{
					$this->metadataType = 'json';
					$this->metadataUrl =
						'https://graph.facebook.com/' . $this->versionApiPrefix . '/instagram_oembed?omitscript=true&url='
						. $document->getUri()->getLocator()
						. '&access_token='
						. $urlAppId
						. '|'
						. $urlAppSecret;
					$result = true;
				}
			}
		}

		return $result;
	}

	/**
	 * Downloads and parses HTML's document metadata, formatted with oEmbed standard.
	 *
	 * @param HtmlDocument $document HTML document.
	 * @param HttpClient|null $httpClient
	 */
	public function handle(HtmlDocument $document, HttpClient $httpClient = null)
	{
		if(
			$this->detectOembedLink($document)
			&& $this->metadataUrl !== ''
		)
		{
			if(!$httpClient)
			{
				$httpClient = $this->initHttpClient();
			}
			$rawMetadata = $this->getRawMetaData($httpClient);

			if($rawMetadata !== false)
			{
				$parsedMetadata = $this->parseMetadata($rawMetadata);

				if($parsedMetadata !== false)
				{
					if(
						$this->metadataEncoding !== ''
						&& $document->getEncoding() !== $this->metadataEncoding)
					{
						$parsedMetadata = Encoding::convertEncoding($parsedMetadata, $this->metadataEncoding, $document->getEncoding());
					}

					if(isset($parsedMetadata['author_name']))
					{
						$document->setDescription('@' . $parsedMetadata['author_name']);
					}

					if(isset($parsedMetadata['provider_name']))
					{
						$document->setExtraField('PROVIDER_NAME', $parsedMetadata['provider_name']);
						$document->setTitle($parsedMetadata['provider_name']);
					}

					if(isset($parsedMetadata['thumbnail_url']))
					{
						$document->setImage($parsedMetadata['thumbnail_url']);
					}

					$document->setDateExpire(DateTime::createFromTimestamp(time() + static::METADATA_TTL));
				}
			}
		}
	}
}
