<?php

namespace Bitrix\Im\V2\Marketplace;

use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Marketplace\Types\Role;
use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Main\Error;
use Bitrix\Main\Web\Uri;
use Bitrix\Rest\PlacementTable;

class Application implements RestConvertible
{
	use ContextCustomer;
	private const DEFAULT_ORDER = 50;
	private const SETTING_NAME = 'im_placement';

	public function __construct(?int $userId = null)
	{
		$context = (new Context())->setUserId($userId);

		$this->setContext($context);
	}

	/**
	 * @return Application\Entity[]
	 */
	public function getApplications(): array
	{
		$orderList = $this->getOrderList();
		$result = [];
		foreach ($this->generateApplicationList() as $application)
		{
			if ($this->isApplicationAvailable($application))
			{
				$application->order = $orderList[$application->id] ?? self::DEFAULT_ORDER;
				$result[] = $application;
			}
		}

		return $result;
	}

	public function getLoadUri(): string
	{
		$url = '/bitrix/components/bitrix/app.layout/lazyload.ajax.php?' . bitrix_sessid_get();

		return
			(new Uri($url))
				->addParams(['site' => SITE_ID])
				->getUri()
		;
	}

	public function setOrder(int $applicationsId, int $order): Result
	{
		$orderList = $this->getOrderList();
		$orderList[$applicationsId] = $order;
		$orderList = $this->filterDeletedApplications($orderList);

		return $this->setOrderList($orderList);
	}

	private function getOrderList(): array
	{
		return \CUserOptions::GetOption('im', self::SETTING_NAME, []);
	}

	private function setOrderList(array $orderList): Result
	{
		$result = new Result();
		$isSuccess = \CUserOptions::SetOption('im', self::SETTING_NAME, $orderList);

		if (!$isSuccess)
		{
			$result->addError(new Error('Error writing the parameter order', 'SERVER_ERROR'));
		}

		return $result;
	}

	private function isApplicationAvailable(Application\Entity $application): bool
	{
		return
			$this->checkRole($application->options['role'])
			&& $this->checkExtranet($application->options['extranet'])
		;
	}

	private function checkExtranet(string $extranetOption): bool
	{
		if ($this->getContext()->getUser()->isExtranet())
		{
			return $extranetOption === 'Y';
		}

		return true;
	}

	private function checkRole(string $roleOptions): bool
	{
		if (!$this->getContext()->getUser()->isAdmin())
		{
			return mb_strtoupper($roleOptions) === Role::USER;
		}

		return true;
	}

	private function filterDeletedApplications(array $userApplications): array
	{
		$applicationIdList = $this->getApplicationIdList();

		$result = [];
		foreach ($userApplications as $applicationId => $order)
		{
			if (in_array($applicationId, $applicationIdList, true))
			{
				$result[$applicationId] = $order;
			}
		}

		return $result;
	}

	/**
	 * @return \Generator<Application\Entity>
	 */
	private function generateApplicationList(): \Iterator
	{
		foreach (Placement::getPlacementList() as $placement)
		{
			foreach (PlacementTable::getHandlersList($placement) as $handler)
			{
				yield new Application\Entity([
					'id' => $handler['ID'],
					'placement' => $placement,
					'options' => $handler['OPTIONS'],
					'restApplicationId' => $handler['APP_ID'],
					'title' => $handler['TITLE'],
				]);
			}
		}
	}

	private function getApplicationIdList(): array
	{
		$result = [];
		foreach ($this->generateApplicationList() as $application)
		{
			if ($this->isApplicationAvailable($application))
			{
				$result[] = $application->id;
			}
		}

		return $result;
	}

	public static function getRestEntityName(): string
	{
		return 'placementApplications';
	}

	public function toRestFormat(array $option = []): array
	{
		return [
			'items' => array_map(
				static fn ($appEntity) => $appEntity->toRestFormat(),
				$this->getApplications()
			),
			'links' => [
				'load' => $this->getLoadUri(),
			],
		];
	}
}