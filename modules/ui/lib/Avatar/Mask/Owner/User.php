<?php
namespace Bitrix\UI\Avatar\Mask\Owner;

use Bitrix\UI\Avatar;
use Bitrix\Main;

class User extends DefaultOwner
{
	protected int $id;

	public function __construct(int $id)
	{
		$this->id = $id;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getDefaultAccess(): array
	{
		return ['U' . $this->id];
	}
}