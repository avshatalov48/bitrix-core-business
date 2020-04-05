<?php

namespace Bitrix\Report\VisualConstructor\Entity;

use Bitrix\Report\VisualConstructor\BaseConfigField;
use Bitrix\Report\VisualConstructor\Fields\Valuable\BaseValuable;
use Bitrix\Report\VisualConstructor\Helper\Util;
use Bitrix\Report\VisualConstructor\Internal\Model;

/**
 * Common class for models which have configurations sub entities
 * @method addConfigurations(Configuration | Configuration[] $configuration) add configuration/configurations to this
 * @method deleteConfigurations(Configuration | Configuration[] $configuration) delete connection with Configuration, but not delete Configuration object
 * @package Bitrix\Report\VisualConstructor\Entity
 */
abstract class ConfigurableModel extends Model
{

	/** @var Configuration[] $configurations */
	protected $configurations = array();

	/**
	 * @return Configuration[]
	 */
	public function getConfigurations()
	{
		return $this->configurations;
	}

	/**
	 * Setter for Configuration colection.
	 *
	 * @param Configuration[] $configurations Configuration list.
	 * @return void
	 */
	public function setConfigurations($configurations)
	{
		$this->configurations = $configurations;
	}

	/**
	 * Build configuration entity from valuable $field and add to configurations list
	 *
	 * @param BaseValuable $field Field from create configuration.
	 * @return void
	 */
	public function addConfigurationField(BaseValuable $field)
	{
		$configuration = new Configuration();
		$configuration->setFieldClassName($field::getClassName());
		$configuration->setKey($field->getKey());
		$configuration->setGId(Util::generateUserUniqueId());
		$configuration->setValue($field->getDefaultValue());
		$configuration->setWeight(0);
		$this->addConfigurations($configuration);
	}
}