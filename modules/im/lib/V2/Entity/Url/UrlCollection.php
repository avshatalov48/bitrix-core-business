<?php

namespace Bitrix\Im\V2\Entity\Url;

use Bitrix\Im\V2\Entity\EntityCollection;
use Bitrix\Main\UrlPreview\UrlPreview;

/**
 * @implements \IteratorAggregate<int,UrlItem>
 * @method UrlItem offsetGet($key)
 */
class UrlCollection extends EntityCollection
{
	/**
	 * @param string[]|null $urls
	 */
	public function __construct(?array $urls = null)
	{
		parent::__construct();

		if ($urls != null)
		{
			foreach ($urls as $url)
			{
				$this[] = new UrlItem($url);
			}
		}
	}

	public static function getRestEntityName(): string
	{
		return 'urls';
	}

	/**
	 * @param int[] $previewUrlsIds
	 * @return static
	 */
	public static function initByPreviewUrlsIds(array $previewUrlsIds, bool $withHtml = true): self
	{
		$urlCollection = new static();

		if (empty($previewUrlsIds))
		{
			return $urlCollection;
		}

		if ($withHtml)
		{
			$previews = UrlPreview::getMetadataAndHtmlByIds($previewUrlsIds);
		}
		else
		{
			$previews = UrlPreview::getMetadataByIds($previewUrlsIds);
		}

		if ($previews === false)
		{
			return $urlCollection;
		}

		foreach ($previews as $preview)
		{
			$urlCollection[] = UrlItem::initByMetadata($preview);
		}

		return $urlCollection;
	}

	public static function initByMessage(\Bitrix\Im\V2\Message $message): self
	{
		$urls = UrlItem::getUrlsFromText($message->getMessage());

		return new static($urls);
	}
}