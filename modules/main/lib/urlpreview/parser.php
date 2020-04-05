<?

namespace Bitrix\Main\UrlPreview;

abstract class Parser
{
	/**
	 * Method should parse document and fill document's metadata properties that were left unfilled by
	 * previous parsers in chain.
	 *
	 * @param HtmlDocument $document
	 */
	abstract public function handle(HtmlDocument $document);
}