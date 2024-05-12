<?php

namespace Bitrix\Im\V2\Entity\Url;

use Bitrix\Im\Common;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Rest\RestEntity;
use Bitrix\Main\UrlPreview\UrlMetadataTable;
use Bitrix\Main\UrlPreview\UrlPreview;
use Bitrix\Main\Web\Uri;
use CIMMessageParamAttach;

class UrlItem implements RestEntity
{
	protected string $url = '';
	protected ?array $metadata = [];
	protected static array $staticMetadataCache = [];
	protected ?RichData $richData;
	protected ?\CIMMessageParamAttach $urlAttach = null;

	public static function getRestEntityName(): string
	{
		return 'url';
	}

	public function __construct(?string $url = null, bool $withFetchMetadata = true)
	{
		if (!empty($url))
		{
			$this->setUrl($url);
			if ($this->getUrl())
			{
				$metadata = static::$staticMetadataCache[$this->getUrl()] ?? null;
				if ($metadata === null && $withFetchMetadata)
				{
					try
					{
						$metadata = UrlPreview::getMetadataByUrl($this->getUrl(), true, false);
					}
					catch (\Exception $exception)
					{
						$metadata = false;
					}
					static::$staticMetadataCache[$this->getUrl()] = $metadata;
				}

				if ($metadata !== false && $metadata !== null)
				{
					$this->setMetadata($metadata);
				}
			}
		}
	}

	public static function initByMetadata(array $metadata): self
	{
		return (new static())->setMetadata($metadata)->setUrl($metadata['URL']);
	}

	public static function initByPreviewUrlId(int $previewUrlId, bool $withHtml = true): ?self
	{
		if ($withHtml)
		{
			$metadata = UrlPreview::getMetadataAndHtmlByIds([$previewUrlId]);
		}
		else
		{
			$metadata = UrlPreview::getMetadataByIds([$previewUrlId]);
		}
		if ($metadata === false || !isset($metadata[$previewUrlId]))
		{
			return null;
		}

		return static::initByMetadata($metadata[$previewUrlId]);
	}

	/**
	 * @param string|null $text
	 * @return string[]
	 */
	public static function getUrlsFromText(?string $text): array
	{
		if ($text === null)
		{
			return [];
		}

		$textParser = static::getTextParser();
		$text = $textParser->convertText($text);

		$text = preg_replace('/-{54}.+?-{54}/su', "", $text);
		$text = preg_replace('/\[CODE](.*?)\[\/CODE]/siu', "", $text);

		preg_replace_callback(
			'/^(>>(.*)(\n)?)/miu',
			static fn() => " XXX",
			$text
		);

		$result = [];
		preg_replace_callback(
			'/\[url(=(?P<URL>[^\]]+))?](?P<TEXT>.*?)\[\/url]/iu',
			static function (array $matches) use (&$result) {
				$link = !empty($matches['URL'])? $matches['URL']: $matches['TEXT'];
				if (!empty($link))
				{
					$link = static::normalizeUrl($link);
					if (static::isUrlValid($link))
					{
						$result[] = $link;
					}
				}
			},
			$text
		);

		return $result;
	}

	public static function getFirstUrlFromText(?string $text): ?string
	{
		return self::getUrlsFromText($text)[0] ?? null;
	}

	public static function getByMessage(Message $message): ?self
	{
		$firstUrl = self::getFirstUrlFromText($message->getMessage());

		if ($firstUrl === null)
		{
			return null;
		}

		return new self($firstUrl);
	}

	public function getId(): ?int
	{
		if (isset($this->richData))
		{
			return $this->getRichData()->getId();
		}

		$metadata = $this->getMetadata();

		return $metadata['ID'] ?? null;
	}

	public function toRestFormat(array $option = []): array
	{
		return [
			'source' => $this->getUrl(),
			'richData' => $this->getRichData()->toRestFormat(),
		];
	}

	protected static function getTextParser(): \CTextParser
	{
		$textParser = new \CTextParser();

		$textParser->anchorType = 'bbcode';

		foreach ($textParser->allow as $tag => $value)
		{
			$textParser->allow[$tag] = 'N';
		}
		$textParser->allow['HTML'] = 'Y';
		$textParser->allow['ANCHOR'] = 'Y';
		$textParser->allow['TEXT_ANCHOR'] = 'Y';

		return $textParser;
	}

	protected static function normalizeUrl(string $url): string
	{
		$uri = new Uri($url);
		if ($uri->getHost() === '')
		{
			$uri = new Uri(Common::getPublicDomain().$url);
		}

		return $uri->getUri();
	}

	protected static function isUrlValid(string $url): bool
	{
		return !(
			!($parsedUrl = \parse_url($url))
			|| empty($parsedUrl['host'])
			|| strpos($parsedUrl['host'], '.') === false // domain without dots
			|| preg_match("/[\s]+/", $parsedUrl['host']) // spaces in the host
			|| (!empty($parsedUrl['port']) && !is_numeric($parsedUrl['port'])) // non digit port
		);
	}

	//region Setters & getters

	public function getUrl(): string
	{
		return $this->url;
	}

	public function setUrl(string $url): self
	{
		$url = static::normalizeUrl($url);
		if (static::isUrlValid($url))
		{
			$this->url = $url;
		}
		return $this;
	}

	public function getMetadata(): array
	{
		return $this->metadata;
	}

	public function setMetadata(array $metadata): self
	{
		$this->metadata = $metadata;
		return $this;
	}

	public function isStaticUrl(): bool
	{
		$metadata = $this->getMetadata();

		return !empty($metadata) && ($metadata['TYPE'] == UrlMetadataTable::TYPE_STATIC);
	}

	public function isDynamicUrl(): bool
	{
		$metadata = $this->getMetadata();

		return !empty($metadata) && ($metadata['TYPE'] == UrlMetadataTable::TYPE_DYNAMIC);
	}

	public function getRichData(): RichData
	{
		if (isset($this->richData))
		{
			return $this->richData;
		}

		$this->richData = new RichData();

		$metadata = $this->getMetadata();

		if (empty($metadata))
		{
			return $this->richData;
		}

		if ($metadata['TYPE'] === UrlMetadataTable::TYPE_STATIC)
		{
			$this->setRichData(RichData::initByAttach($this->getUrlAttach()));
		}
		elseif ($metadata['TYPE'] === UrlMetadataTable::TYPE_DYNAMIC)
		{
			$richData = UrlPreview::getImRich($metadata['URL'], true);
			if ($richData === false || $richData->getType() === null)
			{
				$richData = $this->richData->setType(RichData::DYNAMIC_TYPE);
			}
			$this->setRichData($richData);
		}

		return $this->richData->setId($metadata['ID']);
	}

	public function setRichData(?RichData $richData): self
	{
		$this->richData = $richData;
		return $this;
	}

	public function isRich(): bool
	{
		return !empty($this->metadata);
	}

	public function getUrlAttach(): ?\CIMMessageParamAttach
	{
		if ($this->urlAttach === null)
		{
			if ($this->isRich())
			{
				$this->urlAttach = \CIMMessageLink::formatAttach($this->getMetadata()) ?: null;
			}
		}

		return $this->urlAttach;
	}

	public function setUrlAttach(?CIMMessageParamAttach $urlAttach): self
	{
		$this->urlAttach = $urlAttach;
		return $this;
	}

	//endregion
}