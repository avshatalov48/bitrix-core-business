<?php

namespace Bitrix\Location\Common;

trait RepositoryTrait
{
	protected $repository = null;

	public function getRepository()
	{
		return $this->repository;
	}

	protected function setRepository($repository)
	{
		$this->repository = $repository;
		return $this;
	}
}
