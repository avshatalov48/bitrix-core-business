<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Authentication\Policy;

class BooleanRule extends Rule
{
	protected $options = [
		'type' => 'checkbox',
	];

	/**
	 * Rule constructor.
	 * @param string $title
	 * @param bool $value
	 * @param array|null $options
	 */
	public function __construct($title, bool $value = false, array $options = null)
	{
		parent::__construct($title, $value, $options);
	}

	/**
	 * @inheritdoc
	 */
	public function compare($value): bool
	{
		return ($value && !$this->value);
	}
}
