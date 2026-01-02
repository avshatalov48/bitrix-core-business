<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Common\Gateway;

use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\GoneException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotFoundException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\PreconditionFailedException;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Common\Dto\CalendarResponseInterface;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\ConflictException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\UnexpectedException;

interface SectionGatewayInterface
{
	/**
	 * @throws ConflictException
	 * @throws DtoValidationException
	 * @throws NotAuthorizedException
	 * @throws UnexpectedException
	 */
	public function createSection(Section $section): CalendarResponseInterface;

	/**
	 * @throws DtoValidationException
	 * @throws GoneException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws PreconditionFailedException
	 * @throws UnexpectedException
	 */
//	public function updateSection(SectionConnection $sectionConnection): CalendarResponseInterface;

	/**
	 * @throws GoneException
	 * @throws NotAuthorizedException
	 * @throws NotFoundException
	 * @throws UnexpectedException
	 */
	public function deleteSection(string $vendorSectionId): void;
}
