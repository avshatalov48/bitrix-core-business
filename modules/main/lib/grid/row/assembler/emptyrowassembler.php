<?php

namespace Bitrix\Main\Grid\Row\Assembler;

use Bitrix\Main\Grid\Row\RowAssembler;

/**
 * Assembler that returns strings without transformations.
 */
final class EmptyRowAssembler extends RowAssembler
{
	/**
	 * @inheritDoc
	 */
	protected function prepareFieldAssemblers(): array
	{
		return [];
	}
}
