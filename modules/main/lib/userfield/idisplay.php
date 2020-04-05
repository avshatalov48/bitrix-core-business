<?php
namespace Bitrix\Main\UserField;


interface IDisplay
{
	public function display();
	public function getField();
	public function setField(array $field);
	public function setAdditionalParameter($param, $value);
	public function clear();
}