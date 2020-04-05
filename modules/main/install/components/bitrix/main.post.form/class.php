<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

final class MainPostForm extends CBitrixComponent
{
	const STATUS_SCOPE_MOBILE = 'mobile';
	const STATUS_SCOPE_WEB = 'web';
	private $scope;

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->scope = self::STATUS_SCOPE_WEB;
		if (is_callable(array('\Bitrix\MobileApp\Mobile', 'getApiVersion')) && \Bitrix\MobileApp\Mobile::getApiVersion() >= 1 &&
			defined("BX_MOBILE") && BX_MOBILE === true)
			$this->scope = self::STATUS_SCOPE_MOBILE;

		$templateName = $this->getTemplateName();

		if ((empty($templateName) || $templateName == ".default" || $templateName == "bitrix24"))
		{
			if ($this->isWeb())
				$this->setTemplateName(".default");
			else
				$this->setTemplateName("mobile_app");
		}
	}

	protected function isWeb()
	{
		return ($this->scope == self::STATUS_SCOPE_WEB);
	}

	private function prepareParams(&$arParams)
	{
		if(strlen($arParams["FORM_ID"]) <= 0)
			$arParams["FORM_ID"] = "POST_FORM_".RandString(3);
		$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? \CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), "", $arParams["NAME_TEMPLATE"]);
	}

	public function executeComponent()
	{
		$this->prepareParams($this->arParams);

		$this->includeComponentTemplate();
	}
}