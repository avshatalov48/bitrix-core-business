<?php


namespace Bitrix\Calendar\ICal\Basic;


use Bitrix\Calendar\ICal\Builder\ComponentCreator;

abstract class BasicComponent
{
	private $attachProperties = [];

	private $attachSubComponent = [];

	abstract public function getType() : string;

	abstract public function getProperties(): array;

	public function accessContent(): Content
	{
		$content = $this->setContent();

		foreach ($this->attachProperties as $attachProperty)
		{
			$content->property($attachProperty);
		}

		$content->subComponent(...$this->attachSubComponent);

		return $content;
	}

	public function toString(): string
	{
		$load = $this->accessContent();

		$this->hasRequiredProperties($load);

		$builder = new ComponentCreator($load);

		return $builder->build();
	}

	public function appendProperty(PropertyType $property): BasicComponent
	{
		$this->attachProperties[] = $property;

		return $this;
	}

	public function appendSubComponent(BasicComponent $component): BasicComponent
	{
		$this->attachSubComponent[] = $component;

		return $this;
	}

	protected function hasRequiredProperties(Content $componentLoad)
	{
		$providedProperties = [];

		foreach ($componentLoad->getProperties() as $property) {
			$providedProperties = array_merge(
				$providedProperties,
				$property->getNames()
			);
		}

		$requiredProperties = $this->getProperties();

		$sameItems = array_intersect($requiredProperties, $providedProperties);

		if (count($sameItems) !== count($requiredProperties)) {
			$missingProperties = array_diff($requiredProperties, $sameItems);

			throw InvalidComponent::requiredPropertyMissing($missingProperties, $this);
		}
	}
}