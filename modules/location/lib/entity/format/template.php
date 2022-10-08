<?php

namespace Bitrix\Location\Entity\Format;

/**
 * Contain template for the conversion address to string
 *
 * Class Template
 * @package Bitrix\Location\Entity\Format
 */
class Template
{
	/** @var string */
	private $type;
	/** @var string */
	private $template;

	/**
	 * Template constructor.
	 * @param string $type See TemplateType
	 * @param string $template
	 */
	public function __construct(string $type, string $template)
	{
		$this->type = $type;
		$this->template = $template;
	}

	/**
	 * @return string
	 * @see TemplateType
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function getTemplate(): string
	{
		return $this->template;
	}
}
