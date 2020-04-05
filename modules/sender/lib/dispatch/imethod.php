<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Dispatch;

use Bitrix\Main\Localization\Loc;

use Bitrix\Sender\Entity;

Loc::loadMessages(__FILE__);

interface iMethod
{
	/**
	 * Constructor.
	 *
	 * @param Entity\Letter $letter Letter.
	 */
	public function __construct(Entity\Letter $letter);

	/**
	 * Apply method.
	 *
	 * @return void
	 */
	public function apply();

	/**
	 * Revoke method.
	 *
	 * @return void
	 */
	public function revoke();

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function getCode();
}