<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Main\Mail;

/**
 * Class Multipart
 * @package Bitrix\Main\Mail
 */
class Multipart extends Part
{
	const MIXED = 'multipart/mixed';
	const ALTERNATIVE = 'multipart/alternative';

	/** @var Multipart[]|Part[] $parts Parts. */
	protected $parts = [];

	/** @var string $uniqueString Unique string. */
	protected $uniqueString;

	/** @var string $eol Symbol of end-of-line. */
	protected $eol;

	/**
	 * Multipart constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->uniqueString = substr(uniqid(mt_rand(100, 999)), 0, 10);
		$this->setContentType(self::MIXED);
	}

	/**
	 * Set EOL.
	 *
	 * @param string $eol
	 * @return $this
	 */
	public function setEol($eol)
	{
		parent::setEol($eol);
		foreach ($this->parts as $part)
		{
			$part->setEol($this->getEol());
		}
		return $this;
	}

	/**
	 * Set content type.
	 *
	 * @param string $type Type.
	 * @return $this
	 */
	public function setContentType($type)
	{
		$boundary = $this->getBoundary($type);
		$this->addHeader('Content-Type', "$type; boundary=\"$boundary\"");
		return $this;
	}

	/**
	 * Add part.
	 *
	 * @param Part $part Part.
	 * @return $this
	 */
	public function addPart(Part $part)
	{
		$part->setEol($this->getEol());
		$this->parts[] = $part;
		return $this;
	}

	/**
	 * Convert object to string.
	 *
	 * @return string
	 */
	public function toStringHeaders()
	{
		$count = count($this->parts);
		if ($count === 0)
		{
			return '';
		}
		elseif ($count === 1)
		{
			return  current($this->parts)->toStringHeaders();
		}

		return parent::toStringHeaders();
	}

	/**
	 * Convert object to string.
	 *
	 * @return string
	 */
	public function toStringBody()
	{
		$count = count($this->parts);
		if ($count === 0)
		{
			return '';
		}
		elseif ($count === 1)
		{
			return  current($this->parts)->toStringBody();
		}


		$result = '';
		$boundary = $this->getBoundary();
		foreach ($this->parts as $part)
		{
			$result .= '--' . $boundary . $this->eol;
			$result .= (string) $part;
		}
		$result .= '--' . $boundary . '--' . $this->eol;

		return $result;
	}

	/**
	 * Get part count.
	 *
	 * @return integer
	 */
	protected function getPartCount()
	{
		$count = 0;
		foreach ($this->parts as $part)
		{
			if ($part instanceof Multipart)
			{
				$count += $part->getPartCount();
			}
			else
			{
				$count += 1;
			}
		}

		return $count;
	}

	/**
	 * Get boundary.
	 *
	 * @param string $contentType Content type.
	 * @return integer
	 */
	protected function getBoundary($contentType = null)
	{
		$type = $contentType ?: $this->getHeader('Content-Type');
		$type = explode(';', $type);
		$type = $type[0];
		switch ($type)
		{
			case self::MIXED:
				$prefix = 'mix';
				break;

			case self::ALTERNATIVE:
				$prefix = 'alt';
				break;

			default:
				$prefix = '';
		}

		return '-------' . $prefix . $this->uniqueString;
	}
}
