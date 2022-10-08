<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Sync\Exceptions\ConflictException;
use Bitrix\Calendar\Sync\Exceptions\NotFoundException;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Calendar\Sync\Util\SectionContext;

interface SectionManagerInterface
{
	/**
	 * @param Section $section
	 * @param SectionContext $context
	 *
	 * @return Result
	 *
	 * @throws ConflictException
	 */
	public function create(Section $section, SectionContext $context): Result;

	/**
	 * @param Section $section
	 * @param SectionContext $context
	 *
	 * @return Result
	 *
	 * @throws NotFoundException
	 */
	public function update(Section $section, SectionContext $context): Result;

	public function delete(Section $section, SectionContext $context): Result;

	public function getAvailableExternalType(): array;

	//todo move to other interface
	// public function getSections(Connection $connection): array;
	//
	// public function subscribe(SectionConnection $link): Result;
	//
	// public function resubscribe(Push $push): Result;
}
