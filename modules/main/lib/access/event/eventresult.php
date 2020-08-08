<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Access\Event;


class EventResult extends \Bitrix\Main\EventResult
{
	protected $isAccess = null;

	public function allowAccess(): self
	{
		$this->isAccess = true;
		return $this;
	}
	public function forbidAccess(): self
	{
		$this->isAccess = false;
		return $this;
	}

	public function isAccess(): ?bool
	{
		return $this->isAccess;
	}
}