<?php
namespace Bitrix\Socialnetwork\Livefeed\RenderParts;

abstract class Base
{
	protected $options = array();

	public function __construct(array $options = array())
	{
		$this->options = $options;
	}

	public function getOptions()
	{
		return $this->options;
	}

	protected function getMetaResult()
	{
		return array(
			'id' => 0,
			'name' => '',
			'link' => ''
		);
	}

	public function getBBCodeText()
	{

	}

}