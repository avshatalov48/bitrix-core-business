<?php
namespace Bitrix\Main\Engine\Response\ContentArea;

class Component implements ContentAreaInterface
{
	private $componentName = null;
	private $componentTemplate = null;
	private $componentParams = [];
	private $parentComponent = null;
	private $functionParams = [];

	/**
	 * Component constructor.
	 *
	 * @param $componentName
	 * @param string $componentTemplate
	 * @param array $componentParams
	 */
	public function __construct($componentName, $componentTemplate = '', array $componentParams = [])
	{
		$this->componentName = $componentName;
		$this->componentTemplate = $componentTemplate;
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
		$APPLICATION->IncludeComponent(
			$this->componentName,
			$this->componentTemplate,
			$this->componentParams,
			$this->parentComponent,
			$this->functionParams
		);

		return ob_get_clean();
	}
}