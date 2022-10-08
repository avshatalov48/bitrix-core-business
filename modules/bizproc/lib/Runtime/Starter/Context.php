<?php

namespace Bitrix\Bizproc\Runtime\Starter;

use Bitrix\Bizproc;
use Bitrix\Main\Engine\CurrentUser;

class Context
{
	protected const FACE_WEB = 'web';
	protected const FACE_MOBILE = 'mob';
	protected const FACE_REST = 'rest';
	protected const FACE_BIZPROC = 'bizproc';

	protected string $face = self::FACE_WEB;
	protected string $moduleId = 'bizproc';
	protected int $userId;
	protected bool $isManual = false;

	public function setFace(string $face): self
	{
		$this->face = $face;

		return $this;
	}

	public function setModuleId(string $moduleId): self
	{
		$this->moduleId = $moduleId;

		return $this;
	}

	public function setUserId(int $userId): self
	{
		$this->userId = $userId;

		return $this;
	}

	public function setIsManual(): self
	{
		$this->isManual = true;
		$this->setUserIdFromCurrent();

		return $this;
	}

	protected function setUserIdFromCurrent(): self
	{
		$id = CurrentUser::get()->getId();
		$this->setUserId((int)$id);

		return $this;
	}

	public function isManualOperation(): bool
	{
		return $this->isManual;
	}
}
