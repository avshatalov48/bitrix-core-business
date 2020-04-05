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
	 */
	public function __construct($componentName, $componentTemplate = '', array $componentParams = [], array $additionalResponseParams = [])
	{
		$componentArea = new ContentArea\Component($componentName, $componentTemplate, $componentParams);
		parent::__construct($componentArea, self::STATUS_SUCCESS, null, $additionalResponseParams);
	}
}