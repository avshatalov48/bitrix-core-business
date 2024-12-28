<?php

namespace Bitrix\Main\Grid\Row\Assembler;

use Bitrix\Main\Grid\Row\FieldAssembler;
use Bitrix\Main\Grid\Row\RowAssembler;

final class OnlyFieldsRowAssembler extends RowAssembler
{
	/**
	 * @var FieldAssembler[]
	 */
	private array $fieldAssemblers;

	/**
	 * @var string[] $visibleColumnIds
	 * @var FieldAssembler... $fieldAssemblers
	 */
	public function __construct(array $visibleColumnIds, FieldAssembler... $fieldAssemblers)
	{
		parent::__construct($visibleColumnIds);

		$this->fieldAssemblers = $fieldAssemblers;
	}

	/**
	 * @inheritDoc
	 */
	protected function prepareFieldAssemblers(): array
	{
		return $this->fieldAssemblers;
	}
}
