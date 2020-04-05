<?php
/**
* Bitrix Framework
* @package bitrix
* @subpackage security
* @copyright 2001-2013 Bitrix
*/

class CSecurityXSSDetectVariables
{
	private $parsed = false;
	private $searchValuesWithQuotes = array();
	private $searchValuesWithoutQuotes = array();
	private $originalValues = array();

	/**
	 * @param string $name
	 * @param string $value
	 */
	public function addVariable($name, $value)
	{
		$this->originalValues[$name] = $value;
	}

	/**
	 * @return array
	 */
	public function getQuoteSearchValues()
	{
		$this->lazyParseVariables();
		return $this->searchValuesWithQuotes;
	}

	/**
	 * @return array
	 */
	public function getSearchValues()
	{
		$this->lazyParseVariables();
		return $this->searchValuesWithoutQuotes;
	}

	/**
	 * @return bool
	 */
	public function isEmpty()
	{
		return (empty($this->originalValues));
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param bool $containsQuote
	 * @return bool
	 */
	protected function pushValue($name, $value, $containsQuote = false)
	{
		if($containsQuote)
			$this->searchValuesWithQuotes[] = array("name" => $name, "value" => $value);
		else
			$this->searchValuesWithoutQuotes[] = array("name" => $name, "value" => $value);

		return true;
	}

	/**
	 * @param string $name
	 * @param string $value
	 */
	protected function parseVariable($name, $value)
	{
		if(!$value)
			return;

		if(preg_match("/[^\\\](((\\\)(\\\))*+')+/s"," ".$value))
		{
			$encodedValue = htmlspecialcharsbx($value);
			$this->pushValue(
				$name,
				$value,
				true
			);

			if ($encodedValue && $encodedValue !== $value)
			{
				$this->pushValue(
					$name,
					$encodedValue,
					true
				);
			}
		}
		elseif(preg_match("/[^\\\](((\\\)(\\\))*+\")+/s"," ".$value))
		{
			$this->pushValue(
				$name,
				$value,
				true
			);
		}
		else
		{
			$encodedValue = htmlspecialcharsbx($value);
			$this->pushValue(
					$name,
					$value,
					false
			);

			if ($encodedValue && $encodedValue !== $value)
			{
				$this->pushValue(
					$name,
					$encodedValue,
					false
				);
			}
		}
	}

	/**
	 *
	 */
	protected function lazyParseVariables()
	{
		if ($this->parsed)
			return;

		$this->parsed = true;
		foreach($this->originalValues as $name => $value)
		{
			$this->parseVariable($name, $value);
		}
	}
}
