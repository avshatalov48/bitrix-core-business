<?php

namespace Bitrix\Location\Infrastructure\Service;

use Bitrix\Location\Common\BaseService;
use Bitrix\Location\Entity\Area;
use Bitrix\Location\Geometry\Type\Point;
use Bitrix\Location\Infrastructure\Service\Config\Container;
use Bitrix\Location\Infrastructure\Service\DisputedAreaService\CrimeaDispute;
use Bitrix\Location\Infrastructure\Service\DisputedAreaService\Dispute;
use Bitrix\Location\Infrastructure\Service\DisputedAreaService\SevastopolDispute;
use Bitrix\Location\Repository\AreaRepository;

class DisputedAreaService extends BaseService
{
	/** @var DisputedAreaService */
	protected static $instance;

	private const TYPE_DISPUTED = 'DISPUTED';
	private const CODE_CRIMEA = 'CRIMEA';
	private const CODE_SEVASTOPOL = 'SEVASTOPOL';

	/** @var AreaRepository */
	private $areaRepository;

	/** @var Area[]|null */
	private $disputedAreas;

	/**
	 * @param Point $point
	 * @return Dispute|null
	 */
	public function getDisputeByPoint(Point $point): ?Dispute
	{
		$this->loadDisputedAreas();

		foreach ($this->disputedAreas as $disputedArea)
		{
			if (!$disputedArea->containsPoint($point))
			{
				continue;
			}

			$className = null;
			switch ($disputedArea->getCode())
			{
				case self::CODE_CRIMEA:
					$className = CrimeaDispute::class;
					break;
				case self::CODE_SEVASTOPOL:
					$className = SevastopolDispute::class;
					break;
			}

			if (!$className)
			{
				continue;
			}

			return new $className();
		}

		return null;
	}

	/**
	 * @inheritDoc
	 */
	protected function __construct(Container $config)
	{
		$this->areaRepository = new AreaRepository();

		parent::__construct($config);
	}

	private function loadDisputedAreas(): void
	{
		if (!is_null($this->disputedAreas))
		{
			return;
		}

		$this->disputedAreas = $this->areaRepository->findByArguments([
			'filter' => [
				'=TYPE' => self::TYPE_DISPUTED,
			],
			'order' => [
				'SORT' => 'DESC',
			]
		]);
	}
}
