<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Hook\Page;

/** @var array $arResult */

// Web forms backward compatibility hooks.

if ($arResult['SPECIAL_TYPE'] != 'crm_forms')
{
	return;
}

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler('landing', 'onHookExec',
	function(\Bitrix\Main\Event $event) use ($arResult)
	{
		$result = new \Bitrix\Main\Entity\EventResult;

		/**
		 * Returns web form data for current page.
		 * @return array
		 */
		$getFormData = function() use($arResult)
		{
			static $data = null;
			if ($data !== null)
			{
				return $data;
			}

			$data = [];

			if (!\Bitrix\Main\Loader::includeModule('crm'))
			{
				return $data;
			}

			$res = \Bitrix\Crm\WebForm\Internals\FormTable::getList([
				'select' => [
					'BACKGROUND_IMAGE',
					'GOOGLE_ANALYTICS_ID',
					'YANDEX_METRIC_ID'
				],
				'filter' => [
					'LANDING.LANDING_ID' => $arResult['LANDING']->getId()
				]
			]);
			if ($row = $res->fetch())
			{
				$data = $row;
			}

			return $data;
		};

		$result->modifyFields([
			'BACKGROUND' => function(Page $hook) use($getFormData)
			{
				$fields = $hook->getFields();
				$use = $fields['USE']->getValue();
				$picture = \htmlspecialcharsbx(trim($fields['PICTURE']->getValue()));
				$color = \htmlspecialcharsbx(trim($fields['COLOR']->getValue()));
				$position = trim($fields['POSITION']->getValue());

				if ($use != 'Y')
				{
					$data = $getFormData();
					if ($data['BACKGROUND_IMAGE'] ?? false)
					{
						$picture = $data['BACKGROUND_IMAGE'];
						$picture = \CFile::getPath($picture);
						$color = $position = null;
						$use = 'Y';
					}
				}

				if ($use == 'Y')
				{
					Page\Background::setBackground(
						$picture,
						$color,
						$position
					);
				}

				return true;
			},
			'GACOUNTER' => function(Page $hook) use($getFormData)
			{
				$fields = $hook->getFields();
				$use = $fields['USE']->getValue();

				if ($use != 'Y')
				{
					$data = $getFormData();
					if ($data['GOOGLE_ANALYTICS_ID'] ?? false)
					{
						Page\GaCounter::setCounter($data['GOOGLE_ANALYTICS_ID']);
						return true;
					}
				}

				return false;
			},
			'YACOUNTER' => function(Page $hook) use($getFormData)
			{
				$fields = $hook->getFields();
				$use = $fields['USE']->getValue();

				if ($use != 'Y')
				{
					$data = $getFormData();
					if ($data['YANDEX_METRIC_ID'] ?? false)
					{
						Page\YaCounter::setCounter($data['YANDEX_METRIC_ID']);
						return true;
					}
				}

				return false;
			}
		]);

		return $result;
	}
);