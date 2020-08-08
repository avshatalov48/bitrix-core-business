<?php
namespace Bitrix\Main\Engine\Response\ContentArea;

class Component implements ContentAreaInterface, DataSectionInterface
{
	private $componentName = null;
	private $componentTemplate = null;
	private $componentParams = [];
	private $componentResult = [];

	/** @var array|callable  */
	private $dataKeys = [];
	private $parentComponent = null;
	private $functionParams = [];

	/**
	 * Component constructor.
	 *
	 * @param $componentName
	 * @param string $componentTemplate
	 * @param array $componentParams
	 * @param mixed $dataKeys
	 */
	public function __construct($componentName, $componentTemplate = '', array $componentParams = [], $dataKeys = [])
	{
		$this->componentName = $componentName;
		$this->componentTemplate = $componentTemplate;
		$this->dataKeys = $dataKeys;
		$this->setParameters($componentParams);
	}

	/**
	 * @param array $params
	 *
	 * @return $this
	 */
	public function setParameters(array $params)
	{
		$this->componentParams = $params;

		return $this;
	}

	/**
	 * @param $parentComponent
	 *
	 * @return $this
	 */
	public function setParentComponent($parentComponent)
	{
		$this->parentComponent = $parentComponent;

		return $this;
	}

	/**
	 * @param array $functionParams
	 *
	 * @return $this
	 */
	public function setFunctionParameters(array $functionParams)
	{
		$this->functionParams = $functionParams;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getHtml()
	{
		global $APPLICATION;

		ob_start();
		$this->componentResult = $APPLICATION->IncludeComponent(
			$this->componentName,
			$this->componentTemplate,
			$this->componentParams,
			$this->parentComponent,
			$this->functionParams,
			!empty($this->dataKeys) // returnResult
		);

		return ob_get_clean();
	}

	/**
	 * @return string
	 */
	public function getSectionName(): string
	{
		return 'componentResult';
	}

	/**
	 * @return array
	 */
	public function getSectionData()
	{
		$result = [];

		if (
			is_array($this->dataKeys)
			&& !empty($this->dataKeys)
		)
		{
			$result = array_intersect_key($this->componentResult, array_combine($this->dataKeys, $this->dataKeys));
		}
		elseif (is_callable($this->dataKeys))
		{
			$result = call_user_func_array($this->dataKeys, [ $this->componentResult ]);
		}

		return $result;
	}
}