<?php
namespace Bitrix\MobileApp\Data;

use Bitrix\Main\Text\Encoding;

class Lists
{
	private $categoryCodes = array();
	private $categoryNames = array();
	private $sections = array();
	private $items = array();


	public function addItem($categoryCode = "", $item = array())
	{
		$this->createCategory($categoryCode);
		$this->items[$categoryCode][] = $item;
	}
	public function addItems($categoryCode = "", $items = array())
	{
		$this->createCategory($categoryCode);
		foreach ($items as $item)
		{
			$this->items[$categoryCode][] = $item;
		}
	}

	public function addSection($categoryCode = "", $sectionCode, $sectionName)
	{
		$this->createCategory($categoryCode);

		$this->sections[$categoryCode][] = array(
			"ID"=> $sectionCode,
			"NAME"=>$sectionName
		);

	}

	public function setCategoryName($categoryCode = "", $name = "")
	{
		$this->categoryNames[$categoryCode] = $name;
	}

	private function createCategory($categoryCode)
	{
		if(!array_key_exists($categoryCode,$this->categoryCodes))
		{
			$this->categoryCodes[$categoryCode] = array();
			$this->sections[$categoryCode] = array();
			$this->categoryNames[$categoryCode] = "";

		}
	}


	public function showJSON()
	{
		$listData = array(
			"data" => $this->items,
			"sections" => $this->sections,
			"names" => $this->categoryNames,
		);

		header("Content-Type: application/x-javascript");
		echo json_encode($listData);
	}


}