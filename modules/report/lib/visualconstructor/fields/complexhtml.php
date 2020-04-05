<?php
namespace Bitrix\Report\VisualConstructor\Fields;

/**
 * Html element which can fire some js event
 *
 * @package Bitrix\Report\VisualConstructor\Fields
 */
class ComplexHtml extends Html
{
	const JS_EVENT_ON_CLICK = 'onClick';

	/**
	 * Construct html element and set id for it.
	 * After js listeners will listen events element by id.
	 *
	 * @param string $id Unique id for complex html field.
	 * @param string $contentHtml String which place into complex html node.
	 */
	public function __construct($id, $contentHtml = '')
	{
		parent::__construct($contentHtml);
		$this->setId('complex-html-wrapper-' . $id);
		$this->addClass('complex-html-wrapper');
	}


	/**
	 * Print complex html component content.
	 *
	 * @return void
	 */
	public function printContent()
	{
		$this->includeFieldComponent('complexhtml');
	}
}