<?php

namespace Bitrix\Catalog\Grid\Column;

use Bitrix\Main\Loader;
use Bitrix\Catalog\Access;
use Bitrix\Iblock;

Loader::requireModule('iblock');

abstract class CatalogProvider extends Iblock\Grid\Column\BaseElementProvider
{
	protected Access\AccessController $accessController;

	public function __construct(Iblock\Grid\Entity\ElementSettings $settings)
	{
		parent::__construct($settings);

		$this->accessController = Access\AccessController::getCurrent();
	}

	protected function allowProductEdit(): bool
	{
		return $this->accessController->check(Access\ActionDictionary::ACTION_PRODUCT_EDIT);
	}
}
