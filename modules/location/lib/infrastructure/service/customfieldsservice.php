<?php

namespace Bitrix\Location\Infrastructure\Service;

use Bitrix\Location\Common\BaseService;
use Bitrix\Location\Entity\Area;
use Bitrix\Location\Geometry\Type\Point;
use Bitrix\Location\Infrastructure\Service\Config\Container;
use Bitrix\Location\Infrastructure\Service\CustomFieldsService\CrimeaCustomFields;
use Bitrix\Location\Infrastructure\Service\CustomFieldsService\CustomFields;
use Bitrix\Location\Infrastructure\Service\CustomFieldsService\DonetskCustomFields;
use Bitrix\Location\Infrastructure\Service\CustomFieldsService\KhersonCustomFields;
use Bitrix\Location\Infrastructure\Service\CustomFieldsService\LuganskCustomFields;
use Bitrix\Location\Infrastructure\Service\CustomFieldsService\SevastopolCustomFields;
use Bitrix\Location\Infrastructure\Service\CustomFieldsService\ZaporozhyeCustomFields;
use Bitrix\Location\Repository\AreaRepository;

class CustomFieldsService extends BaseService
{
	/** @var CustomFieldsService */
	protected static $instance;

	private const TYPE_CUSTOM_FIELDS = 'CUSTOM_FIELDS';
	private const CODE_CRIMEA = 'CRIMEA';
	private const CODE_SEVASTOPOL = 'SEVASTOPOL';
	private const CODE_DONETSK = 'DONETSK';
	private const CODE_LUGANSK = 'LUGANSK';
	private const CODE_ZAPOROZHYE = 'ZAPOROZHYE';
	private const CODE_KHERSON = 'KHERSON';

	/** @var AreaRepository */
	private $areaRepository;

	/** @var Area[]|null */
	private $customFieldsAreas;

	/**
	 * @param Point $point
	 * @return CustomFields|null
	 */
	public function getCustomFieldsByPoint(Point $point): ?CustomFields
	{
		$this->loadAreasWithCustomFields();

		foreach ($this->customFieldsAreas as $customFieldsArea)
		{
			if (!$customFieldsArea->containsPoint($point))
			{
				continue;
			}

			$className = null;
			switch ($customFieldsArea->getCode())
			{
				case self::CODE_CRIMEA:
					$className = CrimeaCustomFields::class;
					break;
				case self::CODE_SEVASTOPOL:
					$className = SevastopolCustomFields::class;
					break;
				case self::CODE_DONETSK:
					$className = DonetskCustomFields::class;
					break;
				case self::CODE_LUGANSK:
					$className = LuganskCustomFields::class;
					break;
				case self::CODE_ZAPOROZHYE:
					$className = ZaporozhyeCustomFields::class;
					break;
				case self::CODE_KHERSON:
					$className = KhersonCustomFields::class;
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

	private function loadAreasWithCustomFields(): void
	{
		if (!is_null($this->customFieldsAreas))
		{
			return;
		}

		$this->customFieldsAreas = $this->areaRepository->findByArguments([
			'filter' => [
				'=TYPE' => self::TYPE_CUSTOM_FIELDS,
			],
			'order' => [
				'SORT' => 'DESC',
			]
		]);
	}
}
