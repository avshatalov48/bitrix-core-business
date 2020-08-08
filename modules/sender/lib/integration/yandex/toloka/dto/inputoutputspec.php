<?php

namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO;

class InputOutputSpec implements TolokaTransferObject
{
	public const TYPES = [
		'URL'     => 'url',
		'BOOLEAN' => 'boolean',
		'INTEGER' => 'integer',
		'STRING'  => 'string',
		'FLOAT'   => 'float',
		'JSON'    => 'json',
		'FILE'    => 'file',
	];
	/**
	 * @var string
	 */
	private $identificator;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var bool
	 */
	private $required = true;

	/**
	 * @return string
	 */
	public function getIdentificator(): string
	{
		return $this->identificator;
	}

	/**
	 * @param string $identificator
	 *
	 * @return InputOutputSpec
	 */
	public function setIdentificator(string $identificator): InputOutputSpec
	{
		$this->identificator = $identificator;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 *
	 * @return InputOutputSpec
	 */
	public function setType(string $type): InputOutputSpec
	{
		$this->type = $type;

		return $this;
	}

	public function toArray(): array
	{
		return [
			$this->identificator => [
				"type"     => $this->type,
				"required" => (bool)$this->required,
			]
		];
	}
}