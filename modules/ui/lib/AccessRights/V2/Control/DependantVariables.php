<?php

namespace Bitrix\UI\AccessRights\V2\Control;

use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Models\RightModel;
use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure\Configurator\RightItemConfigurator;
use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Structure\Control;
use Bitrix\UI\AccessRights\V2\Control\Trait\HasHintTitle;
use Bitrix\UI\AccessRights\V2\Control\Trait\HasSelectedVariablesAliases;
use Bitrix\UI\AccessRights\V2\Control\Trait\HasSharedOptions;
use Bitrix\UI\AccessRights\V2\Control\Value\DependantVariable;
use Bitrix\UI\AccessRights\V2\Options\RightSection\RightItem;
use Bitrix\UI\AccessRights\V2\Options\RightSection\RightItem\Type;
use Bitrix\UI\AccessRights\V2\Options\RightSection\RightItem\Variable;

class DependantVariables implements Control, RightItemConfigurator
{
	use HasSharedOptions;
	use HasSelectedVariablesAliases;
	use HasHintTitle;

	/** @var DependantVariable[] */
	protected array $variables = [];

	protected array $dependantVariablesPopupHint = [];

	public function getType(): Type
	{
		return Type::DependentVariables;
	}

	public function configureRightItem(RightItem $rightItem): void
	{
		foreach ($this->variables as $variable)
		{
			$rightItemVariable = (new Variable($variable->value(), $variable->title()))
				->setConflictsWith($variable->conflictsWith())
				->setRequires($variable->requires())
				->setIsSecondary($variable->isSecondary())
				->setHint($variable->hint());

			$rightItem->addVariable($rightItemVariable);
		}

		$this->configureRightItemBySharedOptions($rightItem);
		$this->configureRightItemBySelectedVariablesAliases($rightItem);
		$this->configureRightItemByHintTitle($rightItem);
	}



	/**
	 * @param DependantVariable[] $variables
	 * @return static
	 */
	public function variables(array $variables): static
	{
		$this->variables = $variables;

		return $this;
	}

	public function dependantVariablesPopupHint(array $dependantVariablesPopupHint): static
	{
		$this->dependantVariablesPopupHint = $dependantVariablesPopupHint;

		return $this;
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
