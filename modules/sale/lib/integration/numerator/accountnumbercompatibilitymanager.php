<?php
namespace Bitrix\Sale\Integration\Numerator;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity\Event;
use Bitrix\Main\Entity\EventResult;
use Bitrix\Main\Numerator\Generator\NumberGenerator;
use Bitrix\Main\Numerator\Generator\SequentNumberGenerator;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Registry;

/**
 * Class AccountNumberCompatibilityManager
 * @package Bitrix\Sale\Integration\Numerator
 */
class AccountNumberCompatibilityManager
{
	/** For compatibility - in the past users could add a custom type
	 * to the select list of number generation template
	 * Now if you want to add new template words and extend numerator functionality
	 * you should create your own generator (extending from NumberGenerator)
	 * and write your logic there
	 * @param \Bitrix\Main\Event $event
	 * @return array
	 */
	public static function onBuildNumeratorTemplateWordsList(\Bitrix\Main\Event $event)
	{
		$parameters = [];
		if ($event->getParameter('numeratorType') === Registry::REGISTRY_TYPE_ORDER)
		{
			$event = new \Bitrix\Main\Event('sale', 'OnBuildAccountNumberTemplateList', []);
			$event->send();

			if ($event->getResults())
			{
				/** @var \Bitrix\Main\EventResult $eventResult */
				foreach ($event->getResults() as $eventResult)
				{
					$parameters[] = $eventResult->getParameters();
				}
			}
			return $parameters;
		}
		return $parameters;
	}

	/**
	 * @param Event $event
	 * @return EventResult
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function resetAccountNumberType(Event $event)
	{
		Option::set("sale", "account_number_template", '');
		return new EventResult();
	}

	/** If numerator template is the same as it was in an old version of API
	 * we save account_number_template into b_option as if it was before
	 * for compatibility reasons
	 * @param Event $event
	 * @return EventResult
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function updateAccountNumberType(Event $event)
	{
		$result = new EventResult();
		$numeratorFields = $event->getParameter("fields");
		if ($numeratorFields['TYPE'] === Registry::REGISTRY_TYPE_ORDER)
		{
			$numberTemplate = isset($numeratorFields['TEMPLATE']) ? $numeratorFields['TEMPLATE'] : '';
			$settings = Json::decode($numeratorFields['SETTINGS']);
			if ($numberTemplate)
			{
				$type = '';
				switch ($numberTemplate)
				{
					case '{NUMBER}':
						$settingsSequent = $settings[SequentNumberGenerator::getType()];
						if (isset($settingsSequent['step']) && ($settingsSequent['step'] == 1)
							&&
							key_exists('periodicBy', $settingsSequent) && ($settingsSequent['periodicBy'] == null)
						)
						{
							$type = 'NUMBER';
						}
						break;
					case '{PREFIX}{ORDER_ID}':
						$type = 'PREFIX';
						break;
					case '{RANDOM}':
						$type = 'RANDOM';
						break;
					case '{USER_ID_ORDERS_COUNT}':
						$type = 'USER';
						break;
					case '{DAY}{MONTH}{YEAR} / {NUMBER}':
						$settingsSequent = $settings[SequentNumberGenerator::getType()];
						if (isset($settingsSequent['step']) && $settingsSequent['step'] == 1
							&&
							key_exists('periodicBy', $settingsSequent) && $settingsSequent['periodicBy'] == SequentNumberGenerator::DAY
						)
						{
							$type = 'DATE';
						}
						break;
					case '{MONTH}{YEAR} / {NUMBER}':
						$settingsSequent = $settings[SequentNumberGenerator::getType()];
						if (isset($settingsSequent['step']) && $settingsSequent['step'] == 1
							&&
							key_exists('periodicBy', $settingsSequent) && $settingsSequent['periodicBy'] == SequentNumberGenerator::MONTH
						)
						{
							$type = 'DATE';
						}
						break;
					case '{YEAR} / {NUMBER}':
						$settingsSequent = $settings[SequentNumberGenerator::getType()];
						if (isset($settingsSequent['step']) && $settingsSequent['step'] == 1
							&&
							key_exists('periodicBy', $settingsSequent) && $settingsSequent['periodicBy'] == SequentNumberGenerator::YEAR
						)
						{
							$type = 'DATE';
						}
						break;
					default:
						if (!$type)
						{
							// check if template is a custom type - it should contain only custom word
							$isStartIsUserDefinedPattern = strncmp($numberTemplate, NumberGenerator::USER_DEFINED_SYMBOL_START, strlen(NumberGenerator::USER_DEFINED_SYMBOL_START)) === 0;
							$isEndIsUserDefinedPattern = substr($numberTemplate, -strlen(NumberGenerator::USER_DEFINED_SYMBOL_END)) === NumberGenerator::USER_DEFINED_SYMBOL_END;
							if ($isStartIsUserDefinedPattern && $isEndIsUserDefinedPattern)
							{
								$type = substr(substr($numberTemplate, strlen(NumberGenerator::USER_DEFINED_SYMBOL_START)), 0, -strlen(NumberGenerator::USER_DEFINED_SYMBOL_END));
							}
						}
						break;
				}

				Option::set("sale", "account_number_template", $type);
			}
		}

		return $result;
	}
}