<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

class MainAccessConfig extends CBitrixComponent
{
	public function executeComponent()
	{
		$this->IncludeComponentTemplate();
	}
}