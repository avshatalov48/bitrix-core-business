<?php
namespace Bitrix\Main\UrlPreview\Parser;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\UrlPreview\Parser,
	Bitrix\Main\UrlPreview\HtmlDocument;

/**
 * Class AppleMaps
 * @package Bitrix\Main\UrlPreview\Parser
 */
class AppleMaps extends Parser
{
	/**
	 * Parses HTML document's meta tags, and fills document's metadata.
	 *
	 * @param HtmlDocument $document HTML document to scan for metadata.
	 * @return void
	 */
	public function handle(HtmlDocument $document): void
	{
		parse_str($document->getUri()->getQuery(), $outputParams);

		$document->setTitle(Loc::getMessage('MAIN_URL_PREVIEW_PARSER_APPLE_MAPS_TITLE'));

		$description = '';
		if(!empty($outputParams['q']))
		{
			$description .= $outputParams['q'];
		}
		if(!empty($outputParams['address']))
		{
			if(!empty($description))
			{
				$description .= ' ';
			}
			$description .= $outputParams['address'];
		}

		$document->setDescription($description);
	}
}