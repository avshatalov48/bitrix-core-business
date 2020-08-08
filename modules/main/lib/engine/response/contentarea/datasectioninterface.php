<?php
namespace Bitrix\Main\Engine\Response\ContentArea;

interface DataSectionInterface
{
	public function getSectionName(): string;

	/**
	 * @return array
	 */
	public function getSectionData();
}