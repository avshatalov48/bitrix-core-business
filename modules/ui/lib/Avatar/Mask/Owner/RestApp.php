<?php
namespace Bitrix\UI\Avatar\Mask\Owner;

use Bitrix\UI\Avatar;
use Bitrix\Main;

class RestApp extends System
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
}