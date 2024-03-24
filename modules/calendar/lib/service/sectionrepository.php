<?php

namespace Bitrix\Calendar\Service;

interface SectionRepository
{
	public function getSectionById(int $sectionId): ?array;
}