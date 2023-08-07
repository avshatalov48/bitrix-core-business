<?php

namespace Bitrix\Report\VisualConstructor;

use Bitrix\Main\Page\Asset;

class BoardButton
{
	private $html = '';
	private $jsList = [];
	private $cssList = [];
	private $stringList = [];


	public function __construct($html)
	{
		$this->setHtml($html);
	}

	public function getHtml()
	{
		return $this->html;
	}

	public function setHtml($html)
	{
		$this->html = $html;
	}

	public function getJsList()
	{
		return $this->jsList;
	}

	public function setJsList(array $jsList)
	{
		$this->jsList = $jsList;
	}


	public function addJs($jsPath)
	{
		$this->jsList[] = $jsPath;
 	}


	public function getStringList()
	{
		return $this->stringList;
	}

	public function setStringList(array $stringList)
	{
		$this->stringList = $stringList;
	}


	public function addString($string)
	{
		$this->stringList[] = $string;
	}


	public function getCssList()
	{
		return $this->cssList;
	}


	public function setCssList(array $cssList)
	{
		$this->cssList = $cssList;
	}

	public function addCss($jsPath)
	{
		$this->jsList[] = $jsPath;
	}

	public function process()
	{
		return $this;
	}

	public function flush()
	{
		foreach ($this->getJsList() as $jsPath)
		{
			Asset::getInstance()->addJs($jsPath);
		}

		foreach ($this->getCssList() as $cssPath)
		{
			Asset::getInstance()->addCss($cssPath);
		}

		echo implode('', $this->getStringList());
		echo $this->getHtml();

	}
}