<?php

namespace Bitrix\UI\FileUploader;

class RemoveResult extends \Bitrix\Main\Result implements \JsonSerializable
{
	/** @var string|int */
	protected $id;

	public function __construct($id)
	{
		$this->id = $id;

		parent::__construct();
	}

	public function getId()
	{
		return $this->id;
	}

	public function jsonSerialize(): array
	{
		return [
			'id' => $this->getId(),
			'errors' => $this->getErrors(),
			'success' => $this->isSuccess(),
		];
	}
}