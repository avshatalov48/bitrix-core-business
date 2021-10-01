<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Authentication\Policy;

class IpMaskRule extends GreaterRule
{
	protected $options = [
		'type' => 'text',
		'size' => 20,
	];

	/**
	 * Rule constructor.
	 * @param string $title
	 * @param string $value
	 * @param array|null $options
	 */
	public function __construct($title, string $value = '0.0.0.0', array $options = null)
	{
		parent::__construct($title, $value, $options);
	}
}
