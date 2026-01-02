<?php

namespace Bitrix\UI\AccessRights\V2\Control;

use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Models\RightModel;
use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure\Configurator\RightItemConfigurator;
use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure\Control;
use Bitrix\UI\AccessRights\V2\Control\Trait\HasSharedOptions;
use Bitrix\UI\AccessRights\V2\Control\Value\Variable;
use Bitrix\UI\AccessRights\V2\Options\RightSection\RightItem;
use Bitrix\UI\AccessRights\V2\Options\RightSection\RightItem\Type;

class Variables implements Control, RightItemConfigurator
{
	use HasSharedOptions;

	/** @var Variable[] */
	protected array $variables = [];

	public function getType(): Type
	{
		return Type::Variables;
	}

	/**
	 * @param Variable[] $variables
	 * @return $this
	 */
	public function variables(array $variables): static
	{
		$this->variables = $variables;

		return $this;
	}

	public function configureRightItem(RightItem $rightItem): void
	{
		foreach ($this->variables as $variable)
		{
			$rightItemVariable = new RightItem\Variable($variable->value(), $variable->title());
			$rightItem->addVariable($rightItemVariable);
		}

		$this->configureRightItemBySharedOptions($rightItem);
	}

	public function convertUIValueToModelValue(int|string $value): int|string
	{
		return $value;
	}

	public function convertModelValueToUIValue(RightModel $model): int|string
	{
		return $model->getValue();
	}
}
