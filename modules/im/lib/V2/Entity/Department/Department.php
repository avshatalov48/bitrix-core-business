<?php

namespace Bitrix\Im\V2\Entity\Department;

use Bitrix\Im\V2\Rest\RestEntity;
use Bitrix\Main\Loader;

class Department implements RestEntity
{
	private static ?array $structure = null;

	private int $id;

	public function __construct(int $id)
	{
		$this->id = $id;
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getDepth(): int
	{
		return (int)$this->get('DEPTH_LEVEL');
	}

	public function getName(): string
	{
		return $this->get('NAME') ?? '';
	}

	public function isExist(): bool
	{
		$this->fillStructure();

		return isset(self::$structure['DATA'][$this->id]);
	}

	public static function getRestEntityName(): string
	{
		return 'department';
	}

	public function toRestFormat(array $option = []): array
	{
		return ['id' => $this->id, 'name' => $this->getName()];
	}

	private function fillStructure(): void
	{
		if (isset(self::$structure))
		{
			return;
		}

		if (!Loader::includeModule('intranet'))
		{
			self::$structure = [];

			return;
		}

		$structure = \CIntranetUtils::GetStructure();

		if (!isset($structure) || !isset($structure['DATA']))
		{
			self::$structure = [];

			return;
		}

		self::$structure = $structure;
	}

	private function get(string $fieldName)
	{
		$this->fillStructure();

		return self::$structure['DATA'][$this->id][$fieldName] ?? null;
	}
}