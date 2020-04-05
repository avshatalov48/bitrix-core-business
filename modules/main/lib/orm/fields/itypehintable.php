<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2019 Bitrix
 */


namespace Bitrix\Main\ORM\Fields;


interface ITypeHintable
{
	public function getGetterTypeHint();

	public function getSetterTypeHint();
}