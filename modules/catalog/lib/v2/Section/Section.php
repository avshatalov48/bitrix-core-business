<?php

namespace Bitrix\Catalog\v2\Section;

use Bitrix\Catalog\v2\BaseEntity;

/**
 * Class Section
 *
 * @package Bitrix\Catalog\v2\Section
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class Section extends BaseEntity
{
	public function __construct(SectionRepositoryContract $sectionRepository)
	{
		parent::__construct($sectionRepository);
	}

	public function setValue(int $value): self
	{
		$this->setField('VALUE', $value);

		return $this;
	}

	public function getValue(): int
	{
		return (int)$this->getField('VALUE');
	}
}