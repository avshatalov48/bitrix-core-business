<?php
namespace Bitrix\UI\Avatar\Mask\Owner;

use Bitrix\Main;
use Bitrix\UI\Avatar;

abstract class DefaultOwner
{
	abstract public function getId();

	abstract public function getDefaultAccess(): array;

	public function delete(): Main\Entity\DeleteResult
	{
		return Avatar\Mask\Item::deleteByFilter([
			'=OWNER_TYPE' => static::class,
			'=OWNER_ID' => $this->getId()
		]);
	}
}