<?php

namespace Bitrix\Calendar\OpenEvents\Service;

use Bitrix\Calendar\Core\Common;
use Bitrix\Calendar\Core\Common as CommonConst;
use Bitrix\Calendar\Integration\HumanResources\Structure;
use Bitrix\Calendar\OpenEvents\Controller\Request\EventCategory\CreateEventCategoryDto;
use Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory;
use Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryTable;
use Bitrix\Main\Config;

/**
 * Default (primary) open event category, which store all open events without special category.
 * It created by default by Updater\Agent.
 * It selected by default in open event form.
 */
final class DefaultCategoryService
{
	public const PRIMARY_CATEGORY_OPTION_NAME = 'open_event_primary_category_id';
	public const DEFAULT_CATEGORY_NAME = 'CALENDAR_OPEN_EVENTS_DEFAULT_CATEGORY_NAME';
	public const DEFAULT_CATEGORY_DESCRIPTION = 'CALENDAR_OPEN_EVENTS_DEFAULT_CATEGORY_DESCRIPTION';

	private static ?self $instance;

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function createDefaultCategory(): void
	{
		if ($this->getCategory() !== null)
		{
			return;
		}

		$rootDepartmentId = Structure::getInstance()->getRootDepartmentId();

		$createEventCategoryDto = new CreateEventCategoryDto(
			self::DEFAULT_CATEGORY_NAME,
			self::DEFAULT_CATEGORY_DESCRIPTION,
			departmentIds: $rootDepartmentId ? [$rootDepartmentId] : [],
			isPrimary: true,
		);

		$category = CategoryService::getInstance()->createEventCategory(
			Common::SYSTEM_USER_ID,
			$createEventCategoryDto,
		);

		$this->setCategoryId($category->getId());
	}

	public function setCategoryId(int $categoryId): void
	{
		Config\Option::set(
			CommonConst::CALENDAR_MODULE_ID,
			self::PRIMARY_CATEGORY_OPTION_NAME,
			$categoryId
		);
	}

	public function getCategoryId(): ?int
	{
		return Config\Option::get(
			CommonConst::CALENDAR_MODULE_ID,
			self::PRIMARY_CATEGORY_OPTION_NAME,
			null
		);
	}

	public function getCategory(): ?OpenEventCategory
	{
		$category = OpenEventCategoryTable::query()
			->where('ID', $this->getCategoryId())
			->fetchObject();
		if (!$category)
		{
			return null;
		}

		return $category;
	}

	private function __constructor()
	{
	}
}
