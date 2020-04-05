<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

final class SocialnetworkBlogPostComment extends CBitrixComponent
{
	const STATUS_SCOPE_MOBILE = 'mobile';
	const STATUS_SCOPE_WEB = 'web';
	private $scope;
	public $prepareMobileData;

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->prepareMobileData = IsModuleInstalled("mobile");
		$this->scope = self::STATUS_SCOPE_WEB;

		if (is_callable(array('\Bitrix\MobileApp\Mobile', 'getApiVersion')) && \Bitrix\MobileApp\Mobile::getApiVersion() >= 1 &&
			defined("BX_MOBILE") && BX_MOBILE === true)
			$this->scope = self::STATUS_SCOPE_MOBILE;

		if ($this->isWeb())
			$this->setTemplateName(".default");
		else
			$this->setTemplateName("mobile_app");
	}

	public function isWeb()
	{
		return ($this->scope == self::STATUS_SCOPE_WEB);
	}

	public function prepareUrls(&$arResult)
	{
		if ($this->prepareMobileData)
		{
			$url = SITE_DIR."mobile/log/index.php";
			$url .= (strpos($url, "?") === false ? "?" : "&").
				http_build_query(array(
					"detail_log_id" => $this->arParams["LOG_ID"],
					"comment_post_id" => $this->arParams["ID"]
				)
			);

			$arResult["urlMobileToPost"] = $url.'#LAST_LOG_TS#';
			$arResult["urlMobileToComment"] = $url."&".$this->arParams["COMMENT_ID_VAR"]."=#comment_id#";
			$arResult["urlMobileToDelete"] = $url."&delete_comment_id=#comment_id#";
			$arResult["urlMobileToHide"] = $url."&hide_comment_id=#comment_id#";
			$arResult["urlMobileToShow"] = $url."&show_comment_id=#comment_id#";
		}
	}

	public function executeComponent()
	{
		return $this->__includeComponent();
	}
}