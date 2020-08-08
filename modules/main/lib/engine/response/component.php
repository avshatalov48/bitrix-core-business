<?php
namespace Bitrix\Main\Engine\Response;

final class Component extends HtmlContent
{
	/**
	 * Component constructor.
	 *
	 * @param string $componentName
	 * @param string $componentTemplate
	 * @param array $componentParams
	 * @param array $additionalResponseParams
	 * @param mixed $dataKeys
	 */
	public function __construct($componentName, $componentTemplate = '', array $componentParams = [], array $additionalResponseParams = [], $dataKeys = [])
	{
		$componentArea = new ContentArea\Component($componentName, $componentTemplate, $componentParams, $dataKeys);
		parent::__construct($componentArea, self::STATUS_SUCCESS, null, $additionalResponseParams);
	}
}