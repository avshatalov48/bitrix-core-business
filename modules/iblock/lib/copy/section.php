<?php
namespace Bitrix\Iblock\Copy;

use Bitrix\Iblock\Copy\Implement\Section as SectionImplementer;
use Bitrix\Main\Copy\EntityCopier;

class Section extends EntityCopier
{
	public function __construct(SectionImplementer $implementer)
	{
		$implementer->setSectionCopier($this);

		parent::__construct($implementer);
	}
}