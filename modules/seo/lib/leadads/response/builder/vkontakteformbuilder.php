<?php

namespace Bitrix\Seo\LeadAds\Response\Builder;

use Bitrix\Main\Localization\Loc;
use Bitrix\Seo\LeadAds;
use Bitrix\Seo\LeadAds\LeadAdsForm;

Loc::loadMessages(__FILE__);

class VkontakteFormBuilder implements FormBuilderInterface
{
	protected const PAGE_TYPE_QUESTION = 'question';
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
		$fields = $this->buildFields($form['CONTACT_FIELDS'] ?? []);
		$questions = $this->buildQuestions($form['PAGES'] ?? []);
		return new LeadAdsForm(
			[
				"id" => $form['ID'],
				"name" => $form['NAME'],
				// "description" => $form['DESCRIPTION'],
				"title" => $form['NAME'],
				"fields" => array_merge($fields, $questions)
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
	private function buildFields(array $questions) : array
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

	private function buildQuestions(array $pages)
	{
		$result = [];
		foreach ($pages as $page)
		{
			if ( ! (isset($page['blocks']) && is_array($page['blocks'])))
			{
				continue;
			}

			foreach ($page['blocks'] as $block)
			{
				if (
					! (isset($block['block_data']) && is_array($block['block_data']))
					&& $block['type'] !== self::PAGE_TYPE_QUESTION
					&& ! (isset($block['block_data']['data']) && is_array($block['block_data']['data']))
				)
				{
					continue;
				}


				if ($fieldName = $this->mapper->getCrmName(self::PAGE_TYPE_QUESTION))
				{
					$result[] = new LeadAds\Field(
						$fieldName,
						self::EMPTY_FIELD_NAME,
						$block['block_data']['data']['text'],
						$block['id']
					);
				}
			}
		}

		return $result;
	}
}
