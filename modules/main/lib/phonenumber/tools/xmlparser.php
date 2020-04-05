<?php
/**
 * Basic class for recursive xml parser
 */

namespace Bitrix\Main\PhoneNumber\Tools;


abstract class XmlParser
{
	/** @var XmlField[] */
	protected $attributesMapping = array();
	protected $properties = array();

	public function __construct()
	{
		$this->attributesMapping = $this->getMap();
	}

	/**
	 * @param \XMLReader $xmlReader
	 * @param string $path
	 * @return array
	 */
	public function parseElement(\XMLReader $xmlReader, $path)
	{
		$this->init();
		$this->walkDomTree($xmlReader, $path);
		return $this->properties;
	}

	protected function init()
	{
		$this->properties = array();
	}

	/**
	 * @param \XMLReader $xmlReader
	 * @param string $parentPath XPath to the element.
	 */
	protected function walkDomTree(\XMLReader $xmlReader, $parentPath)
	{
		$path = $parentPath . $xmlReader->localName . '/';
		$field = $this->getField($path);

		if(!is_null($field))
		{
			if ($field->isMultiple())
			{
				$this->properties[$field->getName()][] = $this->getElementValue($xmlReader, $parentPath, $field);
			}
			else
			{
				$this->properties[$field->getName()] = $this->getElementValue($xmlReader, $parentPath, $field);
			}

			// if element was parsed by subParser, XMLReader cursor will be moved to the end of the current element
			// and thus we should exit current level of recursion.
			if($xmlReader->nodeType == \XMLReader::END_ELEMENT)
				return;
		}

		if($xmlReader->nodeType == \XMLReader::ELEMENT && $xmlReader->hasAttributes)
		{
			$this->parseAttributes($xmlReader, $path);
		}

		// empty element does not have child elements
		if($xmlReader->isEmptyElement)
			return;

		// recursively reading child elements or leaving recursion on the end of the current element
		while($xmlReader->read())
		{
			if($xmlReader->nodeType == \XMLReader::ELEMENT)
				$this->walkDomTree($xmlReader, $path);
			else if($xmlReader->nodeType == \XMLReader::END_ELEMENT)
				return;
		}
	}

	protected function parseAttributes(\XMLReader $xmlReader, $parentPath)
	{
		$xmlReader->moveToFirstAttribute();

		do
		{
			$path = $parentPath . '@' . $xmlReader->localName;
			$field = $this->getField($path);

			if(!is_null($field))
			{
				$this->properties[$field->getName()] = $field->decodeValue($xmlReader->value);
			}
		} while(($xmlReader->moveToNextAttribute()));

		$xmlReader->moveToElement();
	}

	/**
	 * @param $path
	 * @return XmlField;
	 */
	protected function getField($path)
	{
		if(array_key_exists($path, $this->attributesMapping))
			return $this->attributesMapping[$path];
		else
			return null;
	}

	protected function getElementValue(\XMLReader $xmlReader, $path, XmlField $field)
	{
		$subParser = $field->getSubParser();
		if(is_null($subParser))
			return $field->decodeValue($xmlReader->readString());
		else
			return $subParser->parseElement($xmlReader, $path);
	}

	/**
	 * Function should return array
	 * @return XmlField[]
	 */
	abstract public function getMap();

}