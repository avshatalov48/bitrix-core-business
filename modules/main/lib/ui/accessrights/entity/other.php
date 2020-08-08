<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\UI\AccessRights\Entity;


use Bitrix\Main\Access\AccessCode;

class Other extends EntityBase
{
	public function getType(): string
	{
		return AccessCode::TYPE_OTHER;
	}

	public function getName(): string
	{
		return '';
	}

	public function getUrl(): string
	{
		return '';
	}

	public function getAvatar(int $width = 58, int $height = 58): ?string
	{
		return '';
	}

	protected function loadModel()
	{
		return null;
	}
}