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
			[
				"id" => $form['ID'],
				"name" => $form['NAME'],
				// "description" => $form['DESCRIPTION'],
				"title" => $form['NAME'],
				"fields" => $this->buildQuestions($form['CONTACT_FIELDS'] ?? []),
				// "message" => $form['CONFIRMATION'],
				// "link" => $form['URL'],
			]
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
			if ($fieldName = $this->mapper->getCrmName($externalField))
			{
				$questions[$key] = new LeadAds\Field(
					$fieldName,
					self::EMPTY_FIELD_NAME,
					$this->getFormLabel($fieldName),
					$externalField
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
