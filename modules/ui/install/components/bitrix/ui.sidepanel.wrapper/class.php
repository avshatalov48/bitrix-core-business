<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

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
	protected function isPageSliderContext()
	{
		return
			$this->request->get('IFRAME') === 'Y' ||
			isset($this->arParams['IFRAME_MODE']) && $this->arParams['IFRAME_MODE'] === true
		;
	}

	/**
	 * Execute component.
	 */
	public function executeComponent()
	{
		if (!isset($this->arParams['POPUP_COMPONENT_PARAMS']) || !is_array($this->arParams['POPUP_COMPONENT_PARAMS']))
		{
			$this->arParams['POPUP_COMPONENT_PARAMS'] = [];
		}
		$this->arParams['POPUP_COMPONENT_PARAMS']['IFRAME'] = true;

		if (empty($this->arParams['EDITABLE_TITLE_DEFAULT']))
		{
			$this->arParams['EDITABLE_TITLE_DEFAULT'] = 'Default name';
		}
		if (empty($this->arParams['EDITABLE_TITLE_SELECTOR']))
		{
			$this->arParams['EDITABLE_TITLE_SELECTOR'] = null;
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
					$notification[$key] = isset($notifyOptions[$key]) ? $notifyOptions[$key] : $defaultValue;
				}
			}
		}
		$this->arParams['NOTIFICATION'] = $notification;

		$this->arParams['USE_LINK_TARGETS_REPLACING'] = isset($this->arParams['USE_LINK_TARGETS_REPLACING']) ? (bool) $this->arParams['USE_LINK_TARGETS_REPLACING'] : false;
		$this->arParams['PLAIN_VIEW'] = isset($this->arParams['PLAIN_VIEW']) ? (bool) $this->arParams['PLAIN_VIEW'] : false;
		$this->arParams['USE_PADDING'] = isset($this->arParams['USE_PADDING']) ? (bool) $this->arParams['USE_PADDING'] : true;
		$this->arParams['BUTTONS'] = isset($this->arParams['BUTTONS']) ? $this->arParams['BUTTONS'] : [];
		$this->arParams['PAGE_MODE'] = isset($this->arParams['PAGE_MODE']) ? (bool) $this->arParams['PAGE_MODE'] : true;
		$this->arParams['PAGE_MODE_OFF_BACK_URL'] = isset($this->arParams['PAGE_MODE_OFF_BACK_URL']) ? $this->arParams['PAGE_MODE_OFF_BACK_URL'] : '/';
		$this->arParams['CLOSE_AFTER_SAVE'] = isset($this->arParams['CLOSE_AFTER_SAVE']) ? (bool) $this->arParams['CLOSE_AFTER_SAVE'] : false;
		$this->arParams['RELOAD_PAGE_AFTER_SAVE'] = isset($this->arParams['RELOAD_PAGE_AFTER_SAVE']) ? (bool) $this->arParams['RELOAD_PAGE_AFTER_SAVE'] : false;
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

		if ($this->isPageSliderContext() && !self::$isWrapperCalled)
		{
			self::$isWrapperCalled = true;

			/** @var \CAllMain $APPLICATION */
			global $APPLICATION;
			$APPLICATION->RestartBuffer();
			$this->includeComponentTemplate();

			require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
			exit;
		}
		elseif ($this->arParams['PAGE_MODE'] || self::$isWrapperCalled)
		{
			$this->includeComponentTemplate('content');
		}
		elseif (!$this->arParams['PAGE_MODE'])
		{
			$this->includeComponentTemplate('loader');
		}
	}
}