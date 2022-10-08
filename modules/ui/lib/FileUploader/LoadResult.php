<?php

namespace Bitrix\UI\FileUploader;

class LoadResult extends \Bitrix\Main\Result implements \JsonSerializable
{
	/** @var string|int */
	protected $id;
	protected ?FileInfo $file = null;

	public function __construct($id)
	{
		$this->id = $id;

		parent::__construct();
	}

	public function getId()
	{
		return $this->id;
	}

	public function getFile(): ?FileInfo
	{
		return $this->file;
	}

	public function setFile(FileInfo $file): void
	{
		$this->file = $file;
	}

	public function jsonSerialize(): array
	{
		return [
			'id' => $this->getId(),
			'errors' => $this->getErrors(),
			'success' => $this->isSuccess(),
			'data' => [
				'file' => $this->getFile(),
			],
		];
	}
}