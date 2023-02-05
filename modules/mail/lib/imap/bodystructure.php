<?php

namespace Bitrix\Mail\Imap;

/**
 * https://tools.ietf.org/html/rfc3501#section-7.4.2
 *
 * multipart:
 * 	- parts array
 * 	- subtype
 * 	+ params array
 * 	+ disposition array
 * 	+ language
 * 	+ location
 *
 * basic:
 * 	- type
 * 	- subtype
 * 	- params array
 * 	- content id
 * 	- description
 * 	- encoding
 * 	- size
 * 	+ body MD5
 * 	+ disposition array
 * 	+ language
 * 	+ location
 *
 * text:
 * 	- type
 * 	- subtype
 * 	- params array
 * 	- content id
 * 	- description
 * 	- encoding
 * 	- size
 * 	- size in text lines
 * 	+ body MD5
 * 	+ disposition array
 * 	+ language
 * 	+ location
 *
 * message/rfc822:
 * 	- type
 * 	- subtype
 * 	- params array
 * 	- content id
 * 	- description
 * 	- encoding
 * 	- size
 * 	- envelope structure
 * 	- body structure
 * 	- size in text lines
 * 	+ body MD5
 * 	+ disposition array
 * 	+ language
 * 	+ location
 */

class BodyStructure
{
	protected $number;
	protected $data = array();
	protected $isMultipart = false, $partsCount = 0;
	protected const TYPE_INDEX = 0;
	protected const SUBTYPE_INDEX = 1;
	protected const ENCODING_INDEX = 5;

	protected const DEFAULT_PROPERTIES = [
		'text',
		'html',
		null,
		null,
		null,
		'8bit',
	];

	protected function formatProperty($property, $propertyIndex)
	{
		if(is_string($property) && !empty($property))
		{
			$property = mb_strtolower($property);
		}
		else
		{
			$property = self::DEFAULT_PROPERTIES[$propertyIndex];
		}

		return $property;
	}

	public function __construct(array $bodystructure, $number = null)
	{
		$this->number = $number;
		$this->data = &$bodystructure;

		if (is_array($bodystructure[0]))
		{
			$this->isMultipart = true;

			$this->partsCount = count($bodystructure[0]);
			for ($i = 0; $i < $this->partsCount; $i++)
			{
				$bodystructure[0][$i] = new static(
					$bodystructure[0][$i],
					(string) (!is_null($number) ? sprintf('%s.%u', $number, $i + 1) : $i + 1)
				);
			}
		}
		else
		{
			if (is_null($number))
			{
				$this->number = 1;
			}

			$bodystructure[self::TYPE_INDEX] = $this->formatProperty($bodystructure[self::TYPE_INDEX], self::TYPE_INDEX);
			$bodystructure[self::ENCODING_INDEX] = $this->formatProperty($bodystructure[self::ENCODING_INDEX], self::ENCODING_INDEX);
		}

		$bodystructure[self::SUBTYPE_INDEX] = $this->formatProperty($bodystructure[self::SUBTYPE_INDEX], self::SUBTYPE_INDEX);

		if (!empty($bodystructure[2]) && is_array($bodystructure[2]))
		{
			$params = array();

			$count = count($bodystructure[2]);
			for ($i = 0; $i < $count; $i += 2)
			{
				$params[mb_strtolower($bodystructure[2][$i])] = $bodystructure[2][$i + 1];
			}

			$bodystructure[2] = $params;
		}

		$disposition = &$bodystructure[$this->getDispositionIndex()];

		$disposition[0] = mb_strtolower($disposition[0]);
		if (!empty($disposition[1]) && is_array($disposition[1]))
		{
			$params = array();

			$count = count($disposition[1]);
			for ($i = 0; $i < $count; $i += 2)
			{
				$params[mb_strtolower($disposition[1][$i])] = $disposition[1][$i + 1];
			}

			$disposition[1] = $params;
		}
	}

	public function getNumber()
	{
		return $this->number;
	}

	public function getType()
	{
		return ($this->isMultipart ? 'multipart' : $this->data[0]);
	}

	public function getSubtype()
	{
		return $this->data[1];
	}

	public function getParams()
	{
		if(is_array($this->data[2]))
		{
			return $this->data[2];
		}
		else if(is_string($this->data[2]))
		{
			return ['name' => $this->data[2]];
		}
		else
		{
			return ['name' => 'file'];
		}
	}

	public function getId()
	{
		return $this->isMultipart ? false : $this->data[3];
	}

	public function getEncoding()
	{
		return ($this->isMultipart ? false : $this->data[5]);
	}

	protected function getDispositionIndex()
	{
		switch ($this->getType())
		{
			case 'multipart':
				return 3;
			case 'message':
				return 'rfc822' === $this->getSubtype() ? 11 : 8;
			case 'text':
				return 9;
			default:
				return 8;
		}
	}

	public function getDisposition()
	{
		return $this->data[$this->getDispositionIndex()];
	}

	public function isMultipart()
	{
		return $this->isMultipart;
	}

	public function isText()
	{
		return $this->getType() === 'text';
	}

	public function isAttachment()
	{
		return $this->getDisposition()[0] === 'attachment';
	}

	public function isBodyText()
	{
		return $this->isText() && !$this->isAttachment() && $this->getSubtype() != 'calendar';
	}

	public function traverse(callable $callback, $flat = false)
	{
		$items = array();

		if ($this->isMultipart)
		{
			for ($i = 0; $i < $this->partsCount; $i++)
			{
				$items[] = $this->data[0][$i]->traverse($callback, $flat);
			}
		}

		$result = array($callback($this, $items));

		if ($flat)
		{
			$result = array_merge($result, ...$items);
		}
		else
		{
			$result[] = $items;
		}

		return $result;
	}

}
