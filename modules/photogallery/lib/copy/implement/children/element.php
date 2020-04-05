<?php
namespace Bitrix\Photogallery\Copy\Implement\Children;

use Bitrix\Iblock\Copy\Implement\Children\Element as ElementBase;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Photogallery\Copy\Stepper\Section as SectionStepper;

class Element extends ElementBase
{
	protected $moduleId = "photogallery";

	protected function copySectionElements(int $sectionId, int $copiedSectionId)
	{
		if (!Loader::includeModule("photogallery"))
		{
			return $this->result;
		}

		$this->addToQueue($copiedSectionId, "SectionGroupQueue");

		Option::set($this->moduleId, "SectionGroupChecker_".$copiedSectionId, "Y");

		$queueOption = [
			"sectionId" => $sectionId,
			"copiedSectionId" => $copiedSectionId,
			"enumRatio" => ($this->enumRatio[$sectionId] ?: []),
			"sectionsRatio" => ($this->sectionsRatio[$sectionId] ?: [])
		];
		Option::set($this->moduleId, "SectionGroupStepper_".$copiedSectionId, serialize($queueOption));

		$agent = \CAgent::getList([], [
			"MODULE_ID" => $this->moduleId,
			"NAME" => SectionStepper::class."::execAgent();"
		])->fetch();
		if (!$agent)
		{
			SectionStepper::bind(1);
		}

		return $this->result;
	}
}