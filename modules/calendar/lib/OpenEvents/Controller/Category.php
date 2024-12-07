<?php

namespace Bitrix\Calendar\OpenEvents\Controller;

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\EventCategoryAccessController;
use Bitrix\Calendar\OpenEvents\Controller\Filter\EventCategory as ValidateFilter;
use Bitrix\Calendar\OpenEvents\Controller\Request\EventCategory as RequestDto;
use Bitrix\Calendar\Core\EventCategory\EventCategory;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\Event\Service\OpenEventPullService;
use Bitrix\Calendar\EventCategory\Service\EventCategoryPullService;
use Bitrix\Calendar\EventCategory\Helper\EventCategoryResponseHelper;
use Bitrix\Calendar\OpenEvents\Item;
use Bitrix\Calendar\OpenEvents\Provider;
use Bitrix\Calendar\OpenEvents\Service\CategoryBanService;
use Bitrix\Calendar\OpenEvents\Service\CategoryMuteService;
use Bitrix\Calendar\OpenEvents\Service\CategoryService;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Engine\AutoWire\BinderArgumentException;
use Bitrix\Main\Engine\AutoWire\ExactParameter;
use Bitrix\Main\Engine\AutoWire\Parameter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;

final class Category extends Controller
{
	use FeatureTrait;

	protected const CATEGORIES_PAGE_SIZE = 20;

	protected int $userId;
	protected Provider\CategoryProvider $categoryProvider;

	protected function init(): void
	{
		parent::init();

		$this->userId = (int)CurrentUser::get()->getId();
		$this->categoryProvider = new Provider\CategoryProvider($this->userId);
	}

	public function configureActions(): array
	{
		return [
			'add' => [
				'+prefilters' => [
					new ValidateFilter\ValidateEventCategoryCreate(),
				],
			],
			'update' => [
				'+prefilters' => [
					new ValidateFilter\ValidateEventCategoryUpdate(),
				],
			],
			'setMute' => [
				'+prefilters' => [
					new ValidateFilter\ValidateSetMuteEventCategory(),
				],
			],
		];
	}

	/**
	 * @return Parameter[]
	 * @throws BinderArgumentException
	 */
	public function getAutoWiredParameters(): array
	{
		$request = $this->getRequest();

		return [
			new ExactParameter(
				EventCategory::class,
				'eventCategory',
				static function(string $className, ?int $id = null): ?EventCategory
				{
					/** @var Factory $mapperFactory */
					$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');

					return $id !== null && $id >= 0 ? $mapperFactory->getEventCategory()->getById($id) : null;
				},
			),
			new Parameter(
				RequestDto\ListDto::class,
				static fn () => RequestDto\ListDto::fromRequest($request->toArray()),
			),
			new Parameter(
				RequestDto\CreateEventCategoryDto::class,
				static fn () => RequestDto\CreateEventCategoryDto::fromRequest($request->toArray()),
			),
			new Parameter(
				RequestDto\UpdateEventCategoryDto::class,
				static fn () => RequestDto\UpdateEventCategoryDto::fromRequest($request->toArray()),
			),
			new Parameter(
				RequestDto\SetMuteEventCategoryDto::class,
				static fn () => RequestDto\SetMuteEventCategoryDto::fromRequest($request->toArray()),
			),
			new Parameter(
				RequestDto\SetBanDto::class,
				static fn () => RequestDto\SetBanDto::fromRequest($request->toArray()),
			),
		];
	}

	/**
	 * @return Item\Category[]
	 */
	public function listAction(RequestDto\ListDto $listDto): array
	{
		$filter = new Provider\Category\Filter(
			query: $listDto->query ?? '',
			isBanned: $listDto->isBanned,
			categoryId: $listDto->categoryId,
		);
		$categoryQuery = new Provider\Category\Query(
			filter: $filter,
			limit: self::CATEGORIES_PAGE_SIZE,
			page: $listDto->page ?? 0,
		);

		$categories = $this->categoryProvider->list($categoryQuery);

		foreach ($categories as $category)
		{
			if ($category->closed)
			{
				EventCategoryPullService::getInstance()->addToWatch($this->userId, $category->id);
			}
		}

		EventCategoryPullService::getInstance()->addToWatch($this->userId);
		OpenEventPullService::getInstance()->addToWatch($this->userId);

		return [
			$this->categoryProvider->getAllCategory(),
			...$categories,
		];
	}

	public function addAction(RequestDto\CreateEventCategoryDto $createEventCategoryDto): ?Item\Category
	{
		$canAdd = EventCategoryAccessController::can(
			$this->userId,
			ActionDictionary::ACTION_EVENT_CATEGORY_ADD,
		);

		if (!$canAdd)
		{
			$this->addError(new Error('no create access', 'no_create_access'));

			return null;
		}

		$eventCategory = CategoryService::getInstance()
			->createEventCategory($this->userId, $createEventCategoryDto)
		;

		$eventCategoryPullService = EventCategoryPullService::getInstance();
		if ($eventCategory->getClosed())
		{
			$eventCategoryPullService->addToWatch($this->userId, $eventCategory->getId());
		}

		return EventCategoryResponseHelper::prepareEventCategoryForUserResponse(
			eventCategory: $eventCategory,
			userId: $this->userId,
			isMuted: !$eventCategory->getClosed(),
		);
	}

	public function updateAction(
		EventCategory $eventCategory,
		RequestDto\UpdateEventCategoryDto $updateEventCategoryDto,
	): ?Item\Category
	{
		$canEdit = EventCategoryAccessController::can(
			$this->userId,
			ActionDictionary::ACTION_EVENT_CATEGORY_EDIT,
			$eventCategory->getId(),
		);

		if (!$canEdit)
		{
			$this->addError(new Error('no edit access', 'no_edit_access'));

			return null;
		}

		CategoryService::getInstance()->updateEventCategory($this->userId, $eventCategory, $updateEventCategoryDto);

		return EventCategoryResponseHelper::prepareEventCategoryForUserResponse(
			eventCategory: $eventCategory,
		);
	}

	public function deleteAction(EventCategory $eventCategory): void
	{
		$canDelete = EventCategoryAccessController::can(
			$this->userId,
			ActionDictionary::ACTION_EVENT_CATEGORY_DELETE,
			$eventCategory->getId(),
		);

		if (!$canDelete)
		{
			$this->addError(new Error('no delete access', 'no_delete_access'));

			return;
		}

		CategoryService::getInstance()->deleteEventCategory($eventCategory);
	}

	public function setMuteAction(EventCategory $eventCategory, RequestDto\SetMuteEventCategoryDto $setMuteDto): void
	{
		CategoryMuteService::getInstance()->setMute($this->userId, $eventCategory->getId(), $setMuteDto->muteState);
	}

	public function setBanAction(EventCategory $eventCategory, RequestDto\SetBanDto $setBanDto): void
	{
		CategoryBanService::getInstance()->setBan($this->userId, $eventCategory->getId(), $setBanDto->banState);
	}

	public function getChannelInfoAction(EventCategory $eventCategory): ?array
	{
		$canView = EventCategoryAccessController::can(
			$this->userId,
			ActionDictionary::ACTION_EVENT_CATEGORY_VIEW,
			$eventCategory->getId(),
		);

		if (!$canView)
		{
			$this->addError(new Error('no view access', 'no_view_access'));

			return null;
		}

		$imCategoryService = new \Bitrix\Calendar\Integration\Im\EventCategoryService();

		return $imCategoryService->getChannelInfo($eventCategory->getChannelId());
	}
}
