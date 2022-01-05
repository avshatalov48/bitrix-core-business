<?php

namespace Bitrix\Seo\LeadAds\Response\Builder;

use Bitrix\Seo\LeadAds;
use Bitrix\Seo\LeadAds\LeadAdsForm;

class FacebookFormBuilder implements FormBuilderInterface
{
	private const EMPTY_FIELD_NAME = null;

	/**@var $mapper LeadAds\Mapper */
	private $mapper;

	/**
	 * @param LeadAds\Mapper $mapper
	 */
	public function __construct(LeadAds\Mapper $mapper)
	{
		$this->mapper = $mapper;
	}

	/**
	 * @param array $form
	 *
	 * @return LeadAdsForm
	 */
	public function buildForm(array $form): LeadAdsForm
	{
		return new LeadAdsForm(
			[
				"id" => $form["ID"],
				"name" => $form["NAME"],
				"description" => !$form['CONTEXT_CARD']['content']
					? null
					: implode(PHP_EOL, $form['CONTEXT_CARD']['content']),
				"title" => $form['CONTEXT_CARD']['title'],
				"fields" => $this->buildQuestions($form['QUESTIONS'] ?? []),
				"message" => $form['THANK_YOU_PAGE']['title'],
				"link" => $form['FOLLOW_UP_ACTION_URL'],
				"active" => $form['STATUS'] === 'ACTIVE'
			]
		);
	}

	/**
	 * @param array $questions
	 *
	 * @return LeadAds\Field[]
	 */
	private function buildQuestions(array $questions): array
	{
		$questionsResult = [];
		foreach ($questions as $field)
		{
			if ($this->mapper->getCrmName($field['type']))
			{
				$questionsResult[] = new LeadAds\Field(
					$field['type'],
					self::EMPTY_FIELD_NAME,
					$field['label'],
					$field['key']
				);
			}
			elseif ($field['type'] === "CUSTOM")
			{
				//if question is custom condition
				if (
					is_array($field['dependent_conditional_questions'])
					&& is_array($field['conditional_questions_choices'])
				)
				{
					$questionsResult[] = new LeadAds\Field(
						LeadAds\Field::TYPE_RADIO,
						self::EMPTY_FIELD_NAME,
						$field['label'],
						$field['key'],
						$this->getOptions($field['conditional_questions_choices'], 0)
					);
					foreach ($field['dependent_conditional_questions'] as $key => $question)
					{
						$questionsResult[] = new LeadAds\Field(
							LeadAds\Field::TYPE_RADIO,
							self::EMPTY_FIELD_NAME,
							$question['name'],
							$question['field_key'],
							$this->getOptions($field['conditional_questions_choices'], $key + 1)
						);
					}
				}
				elseif (is_array($field['options']))
				{
					$questionsResult[] = new LeadAds\Field(
						LeadAds\Field::TYPE_RADIO,
						self::EMPTY_FIELD_NAME,
						$field['label'],
						$field['key'],
						array_map(
							static function ($option): array {
								return [
									"key" => $option["key"],
									"label" => $option['value'],
								];
							},
							$field['options']
						)
					);
				}
				else
				{
					$questionsResult[] = new LeadAds\Field(
						LeadAds\Field::TYPE_INPUT,
						self::EMPTY_FIELD_NAME,
						$field['label'],
						$field['key']
					);
				}
			}
		}

		return $questionsResult;
	}

	/**
	 * @param array $options
	 * @param int $depth
	 *
	 * @return array<string,array>
	 */
	private function getOptions(array $options, int $depth): array
	{
		$result = [];
		foreach ($options as $value)
		{
			if ($depth === 0)
			{
				$result[$value['customized_token']] = ['key' => $value['customized_token'], 'label' => $value['value']];
			}
			elseif ($depth > 0 && $value['next_question_choices'])
			{
				foreach ($this->getOptions($value['next_question_choices'], $depth - 1) as $option)
				{
					if (array_key_exists($option["key"], $result))
					{
						continue;
					}

					$result[$option["key"]] = $option;
				}
			}
		}

		return $result;
	}
}
