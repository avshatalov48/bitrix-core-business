<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage security
 * @copyright 2001-2024 Bitrix
 */
namespace Bitrix\Security\Filter\Auditor;

/**
 * Xss security auditor
 * Searching XSS like strings, for example: <img src=x onerror=prompt(/Hi_big_guy/)>
 *
 * @package Bitrix\Security\Filter\Auditor
 * @since 14.0.0
 */
class SimpleXss extends Base
{
	protected $name = 'SIMPLE_XSS';

	private array $whiteList = array();

	/**
	 * Process and save filtered value
	 * Return true if value triggered auditor filtration
	 * For get filtered value use SimpleXss::getFilteredValue
	 *
	 * Simple example:
	 * <code>
	 * $ob = new SimpleXss();
	 * if ($ob->process($someValue))
	 *     $someValue = $ob->getFilteredValue();
	 * </code>
	 * @param string $value
	 * @return bool
	 */
	public function process($value)
	{
		if (!preg_match('#[(){}\[\]=+&%<>]?#', $value))
			return false;

		$this->initializeFilters();
		$this->setFilteredValue('');
		$found = false;

		$targetValue = $value;

		$last = '';
		$current = $targetValue;
		while ($last != $current)
		{
			$last = $current;
			foreach ($this->filters as $searchChar => $filters)
			{
				if ($searchChar && mb_strpos($current, $searchChar) === false)
					continue;

				$current = preg_replace($filters['search'], $filters['replace'], $current);
			}
		}

		if ($last != $targetValue)
		{
			$this->setFilteredValue($last);
			$found = true;
		}

		return $found;
	}

	protected function initializeWhiteList()
	{
		if (!$this->whiteList)
		{
			$this->whiteList = $this->getWhiteList();
		}
	}

	protected function getFilters()
	{
		$_M = '(?:[\x09\x0a\x0d\\\\]*)';
		$_M3 = '(?:[\x09\x0a\x0d\\\\\s]*)';
		$_M2 = '(?:(?:[\x09\x0a\x0d\\\\\s]|(?:\/\*.*?\*\/))*)';

		$_Al = '(?<![a-z0-9&_?-])';

		$_Jj = '(?:j|%[64]a|\\\\x[64]a|(?:\\\\0*[64]a))';
		$_Ja = '(?:a|%[64]1|\\\\x[64]1|(?:\\\\0*[64]1))';
		$_Jb = '(?:b|%[64]2|\\\\x[64]2|(?:\\\\0*[64]2))';

		$_Jv = '(?:v|%[75]6|\\\\x[75]6|(?:\\\\0*[75]6))';
		$_Js = '(?:s|%[75]3|\\\\x[75]3|(?:\\\\0*[75]3))';
		$_Jc = '(?:c|%[64]3|\\\\x[64]3|(?:\\\\0*[64]3))';
		$_Jr = '(?:r|%[75]2|\\\\x[75]2|(?:\\\\0*[75]2))';
		$_Ji = '(?:i|%[64]9|\\\\x[64]9|(?:\\\\0*[64]9))';
		$_Jp = '(?:p|%[75]0|\\\\x[75]0|(?:\\\\0*[75]0))';
		$_Jt = '(?:t|%[75]4|\\\\x[75]4|(?:\\\\0*[75]4))';

		$_Je = '(?:e|%[64]5|\\\\x[64]5|(?:\\\\0*[64]5))';
		$_Jx = '(?:x|%[75]8|\\\\x[75]8|(?:\\\\0*[75]8))';
		$_Jo = '(?:o|%[64]f|\\\\x[64]f|(?:\\\\0*[64]f))';
		$_Jn = '(?:n|%[64]e|\\\\x[64]e|(?:\\\\0*[64]e))';

		$_Jh = '(?:h|%[64]8|\\\\x[64]8|(?:\\\\0*[64]8))';

		$_Jdd = '(?:\\:|=|%3a|%3d|\\\\x3a|\\\\x3d|(?:\\\\0*3a)|(?:\\\\0*3d))';
		$_Jss = '(?:\\(|%28|\\\\x28|(?:\\\\0*28))';

		$_WS_OPT = '(?:[\\x00\\x09\\x0A\\x0B\\x0C\\x0D\\s\\\\]|\\xE2\\x80\\xA9)*'; //not modified
		$replacePattern = $this->getSplittingString(2);
		$filters = array(
			0 => array(
				'search' => array(
					"/$_Al({$_Jb}{$_M}{$_Je}{$_M}{$_Jh}{$_M})({$_Ja}{$_M}{$_Jv}{$_M}{$_Ji}{$_M}{$_Jo}{$_M}{$_Jr}{$_WS_OPT}{$_Jdd})/is",
					"/({$_Jj}{$_M3}{$_Ja}{$_M3}{$_Jv}{$_M3})({$_Ja}{$_M3}{$_Js}{$_M3}{$_Jc}{$_M3}{$_Jr}{$_M3}{$_Ji}{$_M3}{$_Jp}{$_M3}{$_Jt}{$_M3}{$_Jdd})/is",
					"/({$_Jv}{$_M3}{$_Jb}{$_M3})({$_Js}{$_M3}{$_Jc}{$_M3}{$_Jr}{$_M3}{$_Ji}{$_M3}{$_Jp}{$_M3}{$_Jt}{$_M3}{$_Jdd})/is",
					"/({$_Je}{$_M2}{$_Jx}{$_M2})({$_Jp}{$_M2}{$_Jr}{$_M2}{$_Je}{$_M2}{$_Js}{$_M2}{$_Js}{$_M2}{$_Ji}{$_M2}{$_Jo}{$_M2}{$_Jn}{$_M2}{$_Jss})/is",
				),
				'replace' => $this->getSplittingString(2, " * ")
			),

			"<" => array(
				'search' => array(
					"/(<{$_M}s{$_M}c{$_M})(r{$_M}i{$_M}p{$_M}t)(?!\\w)/is",
					"/(<{$_M}\\/{$_M}s{$_M}c{$_M})(r{$_M}i{$_M}p{$_M}t)/is",
					"/(<{$_M}x{$_M}:{$_M}s{$_M}c{$_M})(r{$_M}i{$_M}p{$_M}t)(?!\\w)/is",
					"/(<{$_M}a{$_M}p{$_M}p{$_M})(l{$_M}e{$_M}t)(?!\\w)/is",
					"/(<{$_M}e{$_M}m{$_M}b)(e{$_M}d)(?!\\w)/is",
					"/(<{$_M}f{$_M}r{$_M}a{$_M})(m{$_M}e)(?!\\w)/is",
					"/(<{$_M}i{$_M}f{$_M}r{$_M})(a{$_M}m{$_M}e)(?!\\w)/is",
					"/(<{$_M}f{$_M}o{$_M})(r{$_M}m)(?!\\w)/is",
					"/(<{$_M}o{$_M}b{$_M})(j{$_M}e{$_M}c{$_M}t)(?!\\w)/is",
					"/(<{$_M}i{$_M}s{$_M}i{$_M})(n{$_M}d{$_M}e{$_M}x)(?!\\w)/is",
				),
				'replace' => $replacePattern
			),

			"=" => array(
				'search' => array(
					"/{$_Al}(f{$_M}o{$_M}r{$_M})(m{$_M}a{$_M}c{$_M}t{$_M}i{$_M}o{$_M}n{$_WS_OPT}=)/is",
					"/{$_Al}(o{$_M}n{$_M}(?:[a-z]{$_M})*?)(([a-z]{$_M}){3}{$_WS_OPT}=)/is",
					"/{$_Al}(s{$_M}e{$_M}e{$_M})(k{$_M}S{$_M}e{$_M}g{$_M}m{$_M}e{$_M}n{$_M}t{$_M}T{$_M}i{$_M}m{$_M}e{$_WS_OPT}=)/is",
					"/{$_Al}(F{$_M}S{$_M}C{$_M})(o{$_M}m{$_M}m{$_M}a{$_M}n{$_M}d{$_WS_OPT}=)/is",
				),
				'replace' => $replacePattern
			),

			":" => array(
				'search' => array(
					"/(u{$_M}r{$_M}n{$_M2}:{$_M2}s{$_M})(c{$_M}h{$_M}e{$_M}m{$_M}a{$_M}s{$_M}-{$_M}m{$_M}i{$_M}c{$_M}r{$_M}o{$_M}s{$_M}o{$_M}f{$_M}t{$_M}-{$_M}c{$_M}o{$_M}m{$_M2}:)/",
					"/((?:'|\"|=|:|;){$_M3}d{$_M}a{$_M}t{$_M})(a{$_M}:)(?![0-9]|image)/is",
				),
				'replace' => $replacePattern
			),

			"-" => array(
				'search' => array(
					"/(-{$_M}m{$_M}o{$_M}z{$_M}-{$_M}b{$_M}i{$_M})(n{$_M}d{$_M}i{$_M}n{$_M}g{$_M}{$_WS_OPT}:{$_WS_OPT}{$_M}u{$_M}r{$_M}l)/is",
				),
				'replace' => $replacePattern
			),

		);

		return $filters;
	}
}
