<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Loader;

/**
 * Class UIPageSliderWrapperComponent
 */
class UIPageSliderWrapperComponent extends \CBitrixComponent
{
	/** @var bool $isWrapperCalled */
	protected static $isWrapperCalled = false;

	/**
	 * Is page slider context.
	 */
	protected function isPageSliderContext(): bool
	{
		return
			$this->request->get('IFRAME') === 'Y' ||
			(isset($this->arParams['IFRAME_MODE']) && $this->arParams['IFRAME_MODE'] === true)
			;
	}

	/**
	 * Execute component.
	 */
	public function executeComponent()
	{
		global $USER;

		if (!isset($this->arParams['POPUP_COMPONENT_PARAMS']) || !is_array($this->arParams['POPUP_COMPONENT_PARAMS']))
		{
			$this->arParams['POPUP_COMPONENT_PARAMS'] = [];
		}

		$this->processSliderComponents();

		if (empty($this->arParams['EDITABLE_TITLE_DEFAULT']))
		{
			$this->arParams['EDITABLE_TITLE_DEFAULT'] = 'Default name';
		}
		if (empty($this->arParams['EDITABLE_TITLE_SELECTOR']))
		{
			$this->arParams['EDITABLE_TITLE_SELECTOR'] = null;
		}
		if (!isset($this->arParams['POPUP_COMPONENT_PARENT']))
		{
			$this->arParams['POPUP_COMPONENT_PARENT'] = false;
		}
		if (!isset($this->arParams['PREVENT_LOADING_WITHOUT_IFRAME']))
		{
			$this->arParams['PREVENT_LOADING_WITHOUT_IFRAME'] = true;
		}
		if (!isset($this->arParams['POPUP_COMPONENT_USE_BITRIX24_THEME']))
		{
			$this->arParams['POPUP_COMPONENT_USE_BITRIX24_THEME'] = "N";
		}
		elseif (
			!isset($this->arParams["POPUP_COMPONENT_BITRIX24_THEME_FOR_USER_ID"])
			|| (int)$this->arParams["POPUP_COMPONENT_BITRIX24_THEME_FOR_USER_ID"] < 0
		)
		{
			$this->arParams["POPUP_COMPONENT_BITRIX24_THEME_FOR_USER_ID"] = $USER->GetID();
		}

		$notification = [
			'content' => null,
			'autoHideDelay' => 5000,
		];
		if (isset($this->arParams['NOTIFICATION']))
		{
			if (is_string($this->arParams['NOTIFICATION']) && !empty($this->arParams['~NOTIFICATION']))
			{
				$notification['content'] = $this->arParams['~NOTIFICATION'];
			}
			elseif(is_array($this->arParams['NOTIFICATION']))
			{
				$notifyOptions = $this->arParams['NOTIFICATION'];
				foreach ($notification as $key => $defaultValue)
				{
					$notification[$key] = ($notifyOptions[$key] ?? $defaultValue);
				}
			}
		}
		$this->arParams['NOTIFICATION'] = $notification;

		$this->arParams['USE_LINK_TARGETS_REPLACING'] = isset($this->arParams['USE_LINK_TARGETS_REPLACING']) && $this->arParams['USE_LINK_TARGETS_REPLACING'];
		$this->arParams['PLAIN_VIEW'] = isset($this->arParams['PLAIN_VIEW']) && $this->arParams['PLAIN_VIEW'];
		$this->arParams['USE_PADDING'] = !isset($this->arParams['USE_PADDING']) || $this->arParams['USE_PADDING'];
		$this->arParams['USE_UI_TOOLBAR_MARGIN'] = !isset($this->arParams['USE_UI_TOOLBAR_MARGIN']) || $this->arParams['USE_UI_TOOLBAR_MARGIN'];
		$this->arParams['USE_BACKGROUND_CONTENT'] = !isset($this->arParams['USE_BACKGROUND_CONTENT']) || $this->arParams['USE_BACKGROUND_CONTENT'];
		$this->arParams['BUTTONS'] = $this->arParams['BUTTONS'] ?? [];
		$this->arParams['PAGE_MODE'] = !isset($this->arParams['PAGE_MODE']) || $this->arParams['PAGE_MODE'];
		$this->arParams['RETURN_CONTENT'] = isset($this->arParams['RETURN_CONTENT']) && $this->arParams['RETURN_CONTENT'];
		$this->arParams['PAGE_MODE_OFF_BACK_URL'] = $this->arParams['PAGE_MODE_OFF_BACK_URL'] ?? '/';
		$this->arParams['CLOSE_AFTER_SAVE'] = isset($this->arParams['CLOSE_AFTER_SAVE']) && $this->arParams['CLOSE_AFTER_SAVE'];
		$this->arParams['RELOAD_PAGE_AFTER_SAVE'] = isset($this->arParams['RELOAD_PAGE_AFTER_SAVE']) && $this->arParams['RELOAD_PAGE_AFTER_SAVE'];
		$this->arParams['RELOAD_GRID_AFTER_SAVE'] = isset($this->arParams['RELOAD_GRID_AFTER_SAVE'])
			?
			is_string($this->arParams['RELOAD_GRID_AFTER_SAVE'])
				?
				$this->arParams['RELOAD_GRID_AFTER_SAVE']
				:
				(bool) $this->arParams['RELOAD_GRID_AFTER_SAVE']
			:
			false;

		if ($this->request->isPost())
		{
			$this->arParams['CLOSE_AFTER_SAVE'] = false;
			$this->arParams['RELOAD_GRID_AFTER_SAVE'] = false;
			$this->arParams['RELOAD_PAGE_AFTER_SAVE'] = false;
		}

		$this->arResult["SKIP_NOTIFICATION"] = $this->request->get("notifyAfterSave") === "N";
		$this->arParams['USE_TOP_MENU'] =
			isset($this->arParams['USE_TOP_MENU']) && $this->arParams['USE_TOP_MENU'] === true
		;

		if ($this->arParams['USE_TOP_MENU'])
		{
			$this->arParams['TOP_MENU_TEMPLATE'] = $this->arParams['TOP_MENU_TEMPLATE'] ?? 'top_horizontal';
			$this->arParams['TOP_MENU_PARAMS'] = array_merge(
				[
					"ROOT_MENU_TYPE" => "left",
					"CHILD_MENU_TYPE" => "sub",
					"MENU_CACHE_TYPE" => "N",
					"MENU_CACHE_TIME" => "604800",
					"MENU_CACHE_USE_GROUPS" => "N",
					"MENU_CACHE_USE_USERS" => "Y",
					"CACHE_SELECTED_ITEMS" => "Y",
					"MENU_CACHE_GET_VARS" => array(),
					"MAX_LEVEL" => "3",
					"USE_EXT" => "Y",
					"DELAY" => "N",
					"ALLOW_MULTI_SELECT" => "N"
				],
				isset($this->arParams['TOP_MENU_PARAMS']) && is_array($this->arParams['TOP_MENU_PARAMS'])
					? $this->arParams['TOP_MENU_PARAMS']
					: []
			);
		}

		if (
			Loader::includeModule("intranet")
			&& $this->arParams["POPUP_COMPONENT_USE_BITRIX24_THEME"] === "Y"
			&& SITE_TEMPLATE_ID === "bitrix24"
		)
		{

			$this->arResult["SHOW_BITRIX24_THEME"] = "Y";
		}
		else
		{
			$this->arResult["SHOW_BITRIX24_THEME"] = "N";
		}

		if ($this->isPageSliderContext() && !self::$isWrapperCalled)
		{
			self::$isWrapperCalled = true;

			global $APPLICATION;
			$APPLICATION->RestartBuffer();
			$this->includeComponentTemplate();

			if ($this->arParams['RETURN_CONTENT'])
			{
				foreach (GetModuleEvents("main", "OnEpilog", true) as $arEvent)
				{
					ExecuteModuleEventEx($arEvent);
				}

				return $APPLICATION->EndBufferContentMan();
			}

			require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
			exit;
		}

		if ($this->arParams['PAGE_MODE'] || self::$isWrapperCalled)
		{
			$this->includeComponentTemplate('content');
		}
		elseif (!$this->arParams['PAGE_MODE'])
		{
			$this->includeComponentTemplate('loader');
		}
	}

	protected function processSliderComponents()
	{
		$this->arResult['SLIDER_COMPONENT_NAME_LIST'] = [];
		$this->arResult['SLIDER_COMPONENT_TEMPLATE_LIST'] = [];
		$this->arResult['SLIDER_COMPONENT_PARAMS_LIST'] = [];

		if (is_array($this->arParams['POPUP_COMPONENT_NAME']))
		{
			$this->arResult['SLIDER_COMPONENT_NAME_LIST'] = array_values($this->arParams['POPUP_COMPONENT_NAME']);

			if (is_array($this->arParams['POPUP_COMPONENT_TEMPLATE_NAME']))
			{
				$this->arParams['POPUP_COMPONENT_TEMPLATE_NAME'] = array_values($this->arParams['POPUP_COMPONENT_TEMPLATE_NAME']);
			}

			$defaultTemplateValue = (!is_array($this->arParams['POPUP_COMPONENT_TEMPLATE_NAME']) ? $this->arParams['POPUP_COMPONENT_TEMPLATE_NAME'] : '');

			foreach ($this->arResult['SLIDER_COMPONENT_NAME_LIST'] as $key => $value)
			{
				$this->arResult['SLIDER_COMPONENT_TEMPLATE_LIST'][$key] = (
					is_array($this->arParams['POPUP_COMPONENT_TEMPLATE_NAME'])
					&& isset($this->arParams['POPUP_COMPONENT_TEMPLATE_NAME'][$key])
						? $this->arParams['POPUP_COMPONENT_TEMPLATE_NAME'][$key]
						: $defaultTemplateValue
				);
				$this->arResult['SLIDER_COMPONENT_PARAMS_LIST'][$key] = (
					is_array($this->arParams['POPUP_COMPONENT_PARAMS'])
					&& isset($this->arParams['POPUP_COMPONENT_PARAMS'][$key])
					&& is_array($this->arParams['POPUP_COMPONENT_PARAMS'][$key])
						? $this->arParams['POPUP_COMPONENT_PARAMS'][$key]
						: []
				);

				$this->arResult['SLIDER_COMPONENT_PARAMS_LIST'][$key]['IFRAME'] = true;
			}
		}
		else
		{
			$this->arResult['SLIDER_COMPONENT_NAME_LIST'][] = $this->arParams['POPUP_COMPONENT_NAME'] ?? '';
			$this->arResult['SLIDER_COMPONENT_TEMPLATE_LIST'][] = $this->arParams['POPUP_COMPONENT_TEMPLATE_NAME'] ?? '';

			$this->arParams['POPUP_COMPONENT_PARAMS']['IFRAME'] = true;
			$this->arResult['SLIDER_COMPONENT_PARAMS_LIST'][] = $this->arParams['POPUP_COMPONENT_PARAMS'] ?? [];
		}
	}
}
