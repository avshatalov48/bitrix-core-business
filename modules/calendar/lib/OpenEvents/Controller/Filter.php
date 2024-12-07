<?php

namespace Bitrix\Calendar\OpenEvents\Controller;

use Bitrix\Calendar\OpenEvents\Provider;
use Bitrix\Calendar\OpenEvents\Controller\Request\Filter as RequestDto;
use Bitrix\Main\Engine\AutoWire\Parameter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main;
use Bitrix\Main\UI\Filter\Options;

final class Filter extends Controller
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

	public function getAutoWiredParameters(): array
	{
		$request = $this->getRequest();

		return [
			new Parameter(
				RequestDto\QueryDto::class,
				static function () use ($request)
				{
					$requestData = $request->toArray();
					$today = new \DateTimeImmutable();
					$plus3month = new \DateTimeImmutable('+3 month');
					$fromYear = $request['fromYear'] ?? $today->format('Y');
					$fromMonth = $request['fromMonth'] ?? $today->format('m');
					$fromDate = $request['fromDate'] ?? $today->format('d');
					$toYear = $request['toYear'] ?? $plus3month->format('Y');
					$toMonth = $request['toMonth'] ?? $plus3month->format('m');
					$toDate = $request['toDate'] ?? $plus3month->format('d');

					return RequestDto\QueryDto::fromRequest([
						...$requestData,
						'fromYear' => $fromYear,
						'fromMonth' => $fromMonth,
						'fromDate' => $fromDate,
						'toYear' => $toYear,
						'toMonth' => $toMonth,
						'toDate' => $toDate,
					]);
				}
			),
		];
	}

	public function getTsRangeAction(RequestDto\QueryDto $queryDto): array
	{
		if (empty($queryDto->filterId))
		{
			return [
				'from' => gmmktime(0, 0, 0, 1, 1, 2038),
				'to' => 0,
			];
		}

		$filterOptions = $this->getFilterOptions($queryDto->filterId);
		$fields = $filterOptions->getFilter();
		if (!empty($fields['DATE_from']) && !empty($fields['DATE_to']))
		{
			$utc = new \DateTimeZone('UTC');

			return [
				'from' => (new Main\Type\DateTime($fields['DATE_from'], null, $utc))->getTimestamp(),
				'to' => (new Main\Type\DateTime($fields['DATE_to'], null, $utc))->getTimestamp(),
			];
		}

		$filter = $this->prepareFilter($queryDto, $filterOptions);

		return $this->eventProvider->getTsRange($filter);
	}

	public function queryAction(RequestDto\QueryDto $queryDto): array
	{
		if (empty($queryDto->filterId))
		{
			return [];
		}

		$filterOptions = $this->getFilterOptions($queryDto->filterId);
		$filter = $this->prepareFilter($queryDto, $filterOptions);

		return $this->eventProvider->list($filter);
	}

	protected function prepareFilter(RequestDto\QueryDto $queryDto, Options $filterOptions): Provider\Event\Filter
	{
		$fields = $filterOptions->getFilter();
		$query = $filterOptions->getSearchString();

		$fromDate = "$queryDto->fromDate.$queryDto->fromMonth.$queryDto->fromYear";
		$toDate = "$queryDto->toDate.$queryDto->toMonth.$queryDto->toYear";

		return new Provider\Event\Filter(
			categoriesIds: $fields['CATEGORIES_IDS'] ?? [],
			fromDate: $fields['DATE_from'] ?? (new Main\Type\Date($fromDate, 'd.m.Y'))->toString(),
			toDate: $fields['DATE_to'] ?? (new Main\Type\Date($toDate, 'd.m.Y'))->toString(),
			creatorId: $fields['CREATOR_ID'] ?? null,
			iAmAttendee: !empty($fields['I_AM_ATTENDEE']) ? ($fields['I_AM_ATTENDEE'] === 'Y') : null,
			query: $query,
		);
	}

	protected function getFilterOptions(string $filterId): Options
	{
		return new Options($filterId);
	}
}
