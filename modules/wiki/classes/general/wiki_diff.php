<?

IncludeModuleLangFile(__FILE__);

class CWikiDiff
{
	/**
	 * @deprecated Use Bitrix\Wiki\Diff::getDiffHtml() instead.
	 * @param string $a First version of text to be compared.
	 * @param string $b Second version of text to be compared.
	 * @return string Formatted result of comparison.
	 */
	public static function getDiff($a, $b)
	{
		$diff = new Bitrix\Wiki\Diff();
		return $diff->getDiffHtml($a, $b);
	}
}
