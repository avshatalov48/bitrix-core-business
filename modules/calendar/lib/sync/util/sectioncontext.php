<?php

namespace Bitrix\Calendar\Sync\Util;

use Bitrix\Calendar\Sync\Connection\SectionConnection;

class SectionContext extends Context
{
	/**
	 * @var SectionConnection|null
	 */
	private ?SectionConnection $sectionConnection = null;

	/**
	 * @param SectionConnection|null $sectionConnection
	 * @return SectionContext
	 */
	public function setSectionConnection(?SectionConnection $sectionConnection): SectionContext
	{
		$this->sectionConnection = $sectionConnection;

		return $this;
	}

	/**
	 * @return SectionConnection
	 */
	public function getSectionConnection(): ?SectionConnection
	{
		return $this->sectionConnection;
	}
}
