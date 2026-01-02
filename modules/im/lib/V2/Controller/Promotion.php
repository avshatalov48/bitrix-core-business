<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Controller;

use Bitrix\Im\V2;
use Bitrix\Im\V2\Promotion\Internals\DeviceType;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Engine\AutoWire\Parameter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Im\V2\Promotion\Entity;
use Bitrix\Main\Validation\Engine\AutoWire\ValidationParameter;

class Promotion extends Controller
{
	private V2\Promotion\Promotion $promoService;

	public function getAutoWiredParameters(): array
	{
		return [
			new ValidationParameter(
				Entity\Promotion::class,
				function (): Entity\Promotion {
					$request = $this->getRequest();
					$requestData = $request->getPostList()->toArray();

					return new Entity\Promotion(
						$requestData['id'] ?? '',
						$requestData['params'] ?? [],
					);
				}
			),
			new Parameter(
				DeviceType::class,
				function (): ?DeviceType {
					$request = $this->getRequest();
					$requestData = $request->getPostList()->toArray();

					$type = $requestData['type'] ?? DeviceType::ALL->value;

					return DeviceType::tryFrom($type);
				}
			),
		];
	}

	/**
	 * @restMethod im.v2.Promotion.read
	 */
	public function readAction(Entity\Promotion $promo): array
	{
		return [
			'result' => $this->promoService->markAsViewed($promo)->isSuccess(),
		];
	}

	/**
	 * @restMethod im.v2.Promotion.listActive
	 */
	public function listActiveAction(?DeviceType $type): array
	{
		if (null === $type)
		{
			return [];
		}

		return $this->promoService->getActive($type)->toRestFormat();
	}

	protected function init(): void
	{
		parent::init();

		$this->promoService = ServiceLocator::getInstance()->get('Im.Services.Promotion');
	}
}
