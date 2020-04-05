<?php
namespace Bitrix\Report\VisualConstructor\Internal\Error;

/**
 * Interface IErrorable
 * @package Bitrix\Report\VisualConstructor\Internal\Error
 */
interface IErrorable
{
	/**
	 * Get Errors collections.
	 * @return array
	 */
	public function getErrors();
}