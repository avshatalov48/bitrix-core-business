<?php

namespace Bitrix\Seo\LeadAds\Response\Builder;

use Bitrix\Main\Localization\Loc;
use Bitrix\Seo\LeadAds;
use Bitrix\Seo\LeadAds\LeadAdsForm;

Loc::loadMessages(__FILE__);

class VkontakteFormBuilder implements FormBuilderInterface
{
	/**@var LeadAds\Mapper $mapper*/
	private $mapper;

	private const EMPTY_FIELD_NAME = null;

	/**
	 * @param LeadAds\Mapper $mapper
	 */
	public function __construct(LeadAds\Mapper $mapper)
	{
		$this->mapper = $mapper;
	}

	/**
	 * build Leadads form
	 * @param array $form
	 *
	 * @return LeadAdsForm
	 */
	public function buildForm(array $form): LeadAdsForm
	{
		return new LeadAdsForm(
			array(
				"id" => $form['FORM_ID'],
				"name" => $form['NAME'],
				"description" => $form['DESCRIPTION'],
				"title" => $form['TITLE'],
				"fields" => $this->buildQuestions($form['QUESTIONS'] ?? []),
				"message" => $form['CONFIRMATION'],
				"link" => $form['URL'],
			)
		);
	}

	/**
	 * @param array $questions
	 *
	 * @return LeadAds\Field[]
	 */
	private function buildQuestions(array $questions) : array
	{
		foreach ($questions  as $key => $externalField)
		{
			if ($fieldName = $this->mapper->getCrmName($externalField['type']))
			{
				$questions[$key] = new LeadAds\Field(
					$fieldName,
					self::EMPTY_FIELD_NAME,
					$this->getFormLabel($fieldName),
					$externalField['key']
				);
			}
			elseif (in_array($externalField['type'], LeadAds\Field::getTypes(), true))
			{
				$questions[$key] = new LeadAds\Field(
					$externalField['type'],
					self::EMPTY_FIELD_NAME,
					$externalField['label'],
					$externalField['key'],
					array_map(
						static function($option) : array {
							return [
								"key" => $option["key"] ?? (string) $option["label"],
								"label" => $option["label"]
							];
						},
						$externalField['options'] ?? []
					)
				);
			}
			else
			{
				unset($questions[$key]);
			}
		}

		return $questions;
	}

	/**
	 * @param string $type
	 * @return string
	 */
	private function getFormLabel(string $type) : string
	{
		if (!$phrase = Loc::getMessage("SEO_VKONTAKTE_FORM_BUILDER_TYPE_{$type}"))
		{
			$phrase = Loc::getMessage("SEO_VKONTAKTE_FORM_BUILDER_UNKNOWN_TYPE");
		}

		return $phrase;
	}

}