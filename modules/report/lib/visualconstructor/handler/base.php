<?php
namespace Bitrix\Report\VisualConstructor\Handler;


use Bitrix\Report\VisualConstructor\Entity\Configuration;
use Bitrix\Report\VisualConstructor\Fields\Base as BaseFormElement;
use Bitrix\Report\VisualConstructor\Fields\Valuable\BaseValuable;
use Bitrix\Report\VisualConstructor\Helper\Util;
use Bitrix\Report\VisualConstructor\View;

/**
 * Class Base
 * @package Bitrix\Report\VisualConstructor\Handler
 */
abstract class Base
{
	protected $configurations = array();
	protected $view;

	/**
	 * @var BaseFormElement[]
	 */
	protected $formElementsList = array();

	/**
	 * @return string
	 */
	public static function getClassName()
	{
		return get_called_class();
	}

	/**
	 * @return BaseFormElement[]
	 */
	public function getCollectedFormElements()
	{
		$this->collectFormElements();
		return $this->getFormElements();
	}

	/**
	 * @param string $key Unique key to fine element in form elements list.
	 * @return BaseFormElement|BaseValuable|null
	 */
	public function getFormElementFromCollected($key)
	{
		static $collectedFormElements;
		if (!$collectedFormElements)
		{
			$collectedFormElements = $this->getCollectedFormElements();
		}
		return $this->getFormElement($key);
	}

	/**
	 * Collecting form elements for configuration form.
	 *
	 * @return void
	 */
	abstract protected function collectFormElements();


	/**
	 * @param BaseValuable $element
	 */
	private function addToConfiguration(BaseValuable $element)
	{
		$configuration = $this->getConfiguration($element->getKey());

		if (!$configuration)
		{
			$newConfiguration = new Configuration();
			$newConfiguration->setGId(Util::generateUserUniqueId());
			$newConfiguration->setFieldClassName($element::getClassName());
			$newConfiguration->setKey($element->getKey());
			$newConfiguration->setValue($element->getValue());
			$newConfiguration->setWeight($element->getWeight());
			$this->configurations[] = $newConfiguration;
		}
	}

	/**
	 * Add form element to end of elements list.
	 *
	 * @param BaseFormElement $element Element to add to form.
	 * @return void
	 */
	public function addFormElement(BaseFormElement $element)
	{
		if ($element->getKey())
		{
			$this->formElementsList[$element->getKey()] = $element;
		}
		else
		{
			$this->formElementsList[] = $element;
		}

		if ($element instanceof BaseValuable)
		{
			$this->addToConfiguration($element);
		}

	}

	/**
	 * Add form element before target element.
	 *
	 * @param BaseFormElement $newElement Element to add to form.
	 * @param BaseFormElement $targetElement Element before which need to add.
	 * @return void
	 */
	public function addFormElementBefore(BaseFormElement $newElement, BaseFormElement $targetElement)
	{
		$newFormElementsList = array();
		foreach ($this->formElementsList as $key => $element)
		{

			//add new element
			if ($element === $targetElement)
			{
				if ($newElement->getKey())
				{
					$newFormElementsList[$newElement->getKey()] = $newElement;
				}
				else
				{
					$newFormElementsList[] = $newElement;
				}

				if ($newElement instanceof BaseValuable)
				{
					$this->addToConfiguration($newElement);
				}
			}

			//rewrite old elements to new collection
			if ($element->getKey())
			{
				$newFormElementsList[$key] = $element;
			}
			else
			{
				$newFormElementsList[] = $element;
			}
		}
		$this->formElementsList = $newFormElementsList;
	}

	/**
	 * Add form element after target element.
	 *
	 * @param BaseFormElement $newElement Element to add to form.
	 * @param BaseFormElement $targetElement Element after which need to add.
	 * @return void
	 */
	public function addFormElementAfter(BaseFormElement $newElement, BaseFormElement $targetElement)
	{
		$newFormElementsList = [];
		foreach ($this->formElementsList as $key => $element)
		{
			//rewrite old elements to new collection
			if ($element->getKey())
			{
				$newFormElementsList[$key] = $element;
			}
			else
			{
				$newFormElementsList[] = $element;
			}

			//add new element
			if ($element === $targetElement)
			{
				if ($newElement->getKey())
				{
					$newFormElementsList[$newElement->getKey()] = $newElement;
				}
				else
				{
					$newFormElementsList[] = $newElement;
				}

				if ($newElement instanceof BaseValuable)
				{
					$this->addToConfiguration($newElement);

				}
			}
		}
		$this->formElementsList = $newFormElementsList;
	}

	/**
	 * Insert element to start of form elements list.
	 *
	 * @param BaseFormElement $newElement Element which need to insert to start of form.
	 * @return void
	 */
	public function addFormElementToStart(BaseFormElement $newElement)
	{
		$firstFormElement = reset($this->formElementsList);
		if ($firstFormElement)
		{
			$this->addFormElementBefore($newElement, $firstFormElement);
		}
	}

	/**
	 * Analog of add form element.
	 *
	 * @param BaseFormElement $newElement Element which need to insert to end of form.
	 * @return void
	 */
	public function addFormElementToEnd(BaseFormElement $newElement)
	{
		$this->addFormElement($newElement);
	}

	/**
	 * Setter to set form elements multiply.
	 *
	 * @param BaseFormElement[] $formElementList Form elements to set in form.
	 * @return void
	 */
	public function setFormElements($formElementList)
	{
		$this->formElementsList = [];
		$this->configurations = [];
		foreach ($formElementList as $element)
		{
			$this->addFormElement($element);
		}
	}

	/**
	 * @param BaseValuable|string $formElement Form element which need to update.
	 * @param mixed $value New value for form element.
	 * @return bool
	 */
	public function updateFormElementValue($formElement, $value)
	{
		if (is_string($formElement))
		{
			$formElement = $this->getFormElement($formElement);
		}

		if (!$formElement || !($formElement instanceof BaseValuable))
		{
			return false;
		}

		$formElement->setValue($value);
		$configuration = $this->getConfiguration($formElement->getKey());
		if ($configuration)
		{
			$configuration->setValue($formElement->getValue());
		}
		else
		{
			$this->addToConfiguration($formElement);
		}
		return true;
	}

	/**
	 * @return BaseFormElement[]
	 */
	public function getFormElements()
	{
		return $this->formElementsList;
	}

	/**
	 * In form elements list find form element with key $fieldKey.
	 *
	 * @param string $fieldKey Unique key to find form element.
	 * @return BaseFormElement|\Bitrix\Report\VisualConstructor\Fields\Valuable\BaseValuable|null
	 */
	public function getFormElement($fieldKey)
	{
		$formElements = $this->getFormElements();
		if (isset($formElements[$fieldKey]))
		{
			return $formElements[$fieldKey];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Returns value of the form element with given key.
	 *
	 * @param string $fieldKey The key of the field.
	 * @return mixed|null
	 */
	public function getFormElementValue($fieldKey)
	{
		$formElements = $this->getFormElements();
		if (isset($formElements[$fieldKey]))
		{
			return $formElements[$fieldKey]->getValue();
		}

		return null;
	}

	/**
	 * Find form element by attribute key value pair.
	 *
	 * @param string $attributeKey Attribute key to find in form elements list.
	 * @param string $value Value of attribute name for needle form element.
	 * @return BaseFormElement|null
	 */
	public function getFormElementByDataAttribute($attributeKey, $value)
	{
		$reportHandlerFormElements = $this->getFormElements();
		if ($reportHandlerFormElements)
		{
			foreach ($reportHandlerFormElements as $element)
			{
				if ($element->getDataAttribute($attributeKey) === $value)
				{
					return $element;
				}
			}
		}
		return null;
	}

	/**
	 * Remove from form elements list form element.
	 *
	 * @param BaseFormElement $element Element to remove.
	 * @return bool
	 */
	public function removeFormElement(BaseFormElement $element)
	{
		if ($element instanceof BaseValuable)
		{
			if (!empty($this->formElementsList[$element->getKey()]))
			{
				$configuration = $this->getConfiguration($element->getKey());
				foreach ($this->configurations as $i => $configurationFromList)
				{
					if ($configurationFromList === $configuration)
					{
						unset($this->configurations[$i]);
					}
				}
			}
		}

		foreach ($this->formElementsList  as $i => $elementFromList)
		{
			if ($element === $elementFromList)
			{
				unset($this->formElementsList[$i]);
				return true;
			}
		}
		return false;
	}

	/**
	 * @param Configuration[] $configurations Configuration list to set.
	 * @return void
	 */
	public function setConfigurations($configurations)
	{
		$this->configurations = $configurations;
	}

	/**
	 * @return Configuration[]
	 */
	public function getConfigurations()
	{
		return $this->configurations;
	}

	/**
	 * @return Configuration[]
	 */
	public function getConfigurationsGidKeyed()
	{
		$reports = $this->getConfigurations();
		$result = array();
		foreach ($reports as $configuration)
		{
			$result[$configuration->getGId()] = $configuration;
		}
		return $result;
}


	/**
	 * TODO@ optimise this
	 * @param string $key Unique key to find configuration.
	 * @return Configuration|null
	 */
	public function getConfiguration($key)
	{
		$configurations = $this->getConfigurations();
		if (!empty($configurations))
		{
			foreach ($configurations as $configuration)
			{
				if ($configuration->getKey() == $key)
					return $configuration;
			}
		}

		return null;
	}

	/**
	 * Construct form element, to render in form.
	 *
	 * @param BaseValuable $element Form element.
	 * @return string
	 */
	protected function getNameForFormElement(BaseValuable $element)
	{
		$name = '';

		$configuration = $this->getConfiguration($element->getKey());
		$id = '[new]';
		if ($configuration && $configuration->getId())
		{
			$id = '[old][' . $configuration->getGId() . ']';
		}

		$name .= '[configurations]' . $id . '[' . $element->getKey() . ']';
		return $name;
	}

	/**
	 * @return View|null
	 */
	public function getView()
	{
		return $this->view;
	}

	/**
	 * @param View $view View entity to set of handler.
	 * @return void
	 */
	public function setView(View $view)
	{
		$this->view = $view;
	}
}