<?php

namespace Bitrix\Calendar\OpenEvents\Controller;

use Bitrix\Calendar\OpenEvents\Exception\EventBusyException;
use Bitrix\Calendar\OpenEvents\Exception\MaxAttendeesReachedException;
use Bitrix\Calendar\OpenEvents\Provider;
use Bitrix\Calendar\OpenEvents\Controller\Filter\OpenEvent as ValidateFilter;
use Bitrix\Calendar\OpenEvents\Controller\Request\OpenEvent as RequestDto;
use Bitrix\Calendar\Event\Service\OpenEventPullService;
use Bitrix\Calendar\Internals\Exception\PermissionDenied;
use Bitrix\Calendar\OpenEvents\Service\OpenEventAttendeeService;
use Bitrix\Calendar\OpenEvents\Service\OpenEventService;
use Bitrix\Calendar\Ui\CountersManager;
use Bitrix\Main\Engine\AutoWire\Parameter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\Main\Type\Date;

final class Event extends Controller
{
	use FeatureTrait;

	protected int $userId;
	protected Provider\EventProvider $eventProvider;

	protected function init(): void
	{
		parent::init();

		$this->userId = (int)CurrentUser::get()->getId();
		$this->eventProvider = new Provider\EventProvider($this->userId);
	}

	public function configureActions(): array
	{
		return [
			'list' => [
				'+prefilters' => [
					new ValidateFilter\ValidateGetOpenEventList(),
				],
			],
			'setAttendeeStatus' => [
				'+prefilters' => [
					new ValidateFilter\ValidateSetEventAttendeeStatus(),
				],
			],
			'setWatched' => [
				'+prefilters' => [
					new ValidateFilter\ValidateSetEventsWatched(),
				],
			],
		];
	}

	public function getAutoWiredParameters(): array
	{
		$request = $this->getRequest();

		return [
			new Parameter(
				RequestDto\SetEventAttendeeStatusDto::class,
				static fn () => RequestDto\SetEventAttendeeStatusDto::fromRequest($request->toArray())
			),
			new Parameter(
				RequestDto\GetOpenEventListDto::class,
				static function () use ($request)
				{
					$requestData = $request->toArray();
					$today = new \DateTimeImmutable();
					$plus3month = new \DateTimeImmutable('+3 month');
					$fromYear = $request['fromYear'] ?? $today->format('Y');
					$fromMonth = $request['fromMonth'] ?? $today->format('m');
					$toYear = $request['toYear'] ?? $plus3month->format('Y');
					$toMonth = $request['toMonth'] ?? $plus3month->format('m');

					return RequestDto\GetOpenEventListDto::fromRequest([
						...$requestData,
						'fromYear' => $fromYear,
						'fromMonth' => $fromMonth,
						'toYear' => $toYear,
						'toMonth' => $toMonth,
					]);
				}
			),
			new Parameter(
				RequestDto\SetEventWatchedDto::class,
				static fn () => RequestDto\SetEventWatchedDto::fromRequest($request->toArray())
			)
		];
	}

	public function listAction(RequestDto\GetOpenEventListDto $getOpenEventListDto): array
	{
		OpenEventPullService::getInstance()->addToWatch($this->userId);

		$fromDate = "01.$getOpenEventListDto->fromMonth.$getOpenEventListDto->fromYear";
		$toMonth = $getOpenEventListDto->toMonth + 1;
		$toDate = "00.$toMonth.$getOpenEventListDto->toYear";

		$filter = new Provider\Event\Filter(
			categoriesIds: $getOpenEventListDto->categoryId ? [$getOpenEventListDto->categoryId] : null,
			fromDate: (new Date($fromDate, 'd.m.Y'))->toString(),
			toDate: (new Date($toDate, 'd.m.Y'))->toString(),
		);

		return $this->eventProvider->list($filter);
	}

	public function getTsRangeAction(int $categoryId): array
	{
		$filter = new Provider\Event\Filter(
			categoriesIds: $categoryId ? [$categoryId] : null,
		);

		return $this->eventProvider->getTsRange($filter);
	}

	public function setAttendeeStatusAction(RequestDto\SetEventAttendeeStatusDto $setEventAttendeeStatusDto): void
	{
		try
		{
			OpenEventAttendeeService::getInstance()->setEventAttendeeStatus($this->userId, $setEventAttendeeStatusDto);
		}
		catch (PermissionDenied)
		{
			$this->addError(new Error('permission denied', 'permission_denied'));

			return;
		}
		catch (MaxAttendeesReachedException)
		{
			$this->addError(new Error('max attendees reached', 'max_attendees_reached'));

			return;
		}
		catch (EventBusyException)
		{
			$this->addError(new Error('internal error', 'internal_error'));

			return;
		}
	}

	public function setWatchedAction(RequestDto\SetEventWatchedDto $setEventWatchedDto): void
	{
		OpenEventService::getInstance()->setEventsWatched($this->userId, $setEventWatchedDto->eventIds);
	}
}
