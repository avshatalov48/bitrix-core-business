<?php
namespace
{
	use \Bitrix\Main\Localization\Loc;
	use \Bitrix\Sale\TradingPlatform\Ebay\Wizard;
	use \Bitrix\Sale\TradingPlatform\Ebay\Ebay;

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

	Loc::loadMessages(__FILE__);
	global $APPLICATION;
	$APPLICATION->SetTitle(Loc::getMessage("SALE_EBAY_W_TITLE"));
	$cleanCache = isset($_REQUEST['CLEAN_CACHE']) && $_REQUEST['CLEAN_CACHE'] == 'Y'? true : false;

	if (!CModule::IncludeModule('sale'))
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
		CAdminMessage::ShowMessage(array(
			"MESSAGE" => Loc::getMessage('SALE_EBAY_W_ERROR'),
			"DETAILS" => Loc::getMessage('SALE_EBAY_W_SALE_NOT_INSTALLED'),
			"HTML" => true,
			"TYPE" => "ERROR"
		));

		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		return;
	}

	if ($APPLICATION->GetGroupRight("sale") < "W")
		$APPLICATION->AuthForm(Loc::getMessage("SALE_ACCESS_DENIED"));

	$ebay = Ebay::getInstance();

	if(!$ebay->isActive())
		LocalRedirect("/bitrix/admin/sale_ebay_general.php?lang=".LANG."&back_url=".urlencode($APPLICATION->GetCurPageParam()));

	$step = !empty($_REQUEST['STEP']) ? intval($_REQUEST['STEP']) : 0;

	if(!empty($_REQUEST['NEXT_STEP']))
		$step++;
	elseif(!empty($_REQUEST['PREV_STEP']))
		$step--;

	$siteList = array();
	$defaultSite = '';

	$rsSites = CSite::GetList($by = "sort", $order = "asc", Array("ACTIVE"=> "Y"));

	while($arRes = $rsSites->Fetch())
	{
		$siteList[$arRes['ID']] = $arRes['NAME'];

		if($arRes["DEF"] == "Y")
			$defaultSite = $arRes['ID'];
	}

	if(!empty($_REQUEST["SITE_ID_SELECTED"]) && array_key_exists($_REQUEST["SITE_ID_SELECTED"], $siteList))
		$siteId = $_REQUEST["SITE_ID_SELECTED"];
	elseif(!empty($_REQUEST["SITE_ID"]) && array_key_exists($_REQUEST["SITE_ID"], $siteList))
		$siteId = $_REQUEST["SITE_ID"];
	else
		$siteId = $defaultSite;

	$stepClassesList = array(
		'StepWelcome',
		'StepSite',
		'StepPersonType',
		'StepEbayAccount',
		'StepGetApiToken',
		'StepPayPalAccount',
		'StepConfirmContacts',
		'StepLinkPaypal',
		'StepEbayAccountRussianConfirm',
		'StepMIPConnect',
		'StepEbayPolicies',
		'StepEbayDefaultPolicies',
		'StepPaymentMapping',
		'StepShipmentMapping',
		'StepImportEbayCategories',
		'StepIblock',
		'StepCategoriesMap',
		'StepStartExchange',
		'StepFinish'
	);

	$ebay = Ebay::getInstance();
	$settings = $ebay->getSettings();
	$wizardPrevStep = null;

	if($step >= 1) // Till now we will save smth.
	{
		if(
			$_SERVER["REQUEST_METHOD"] == "POST"
			&& empty($_POST["PREV_STEP"])
			&& !empty($_POST["EBAY_SETTINGS"])
			&& is_array($_POST["EBAY_SETTINGS"])
			&& check_bitrix_sessid()
		)
		{
			/** @var Wizard\Step  $wizardPrevStep */
			$className = '\Bitrix\Sale\TradingPlatform\Ebay\Wizard\\'.$stepClassesList[$step-1];

			if(!is_array($settings[$siteId]))
				$settings[$siteId] = array();

			$settings[$siteId] = array_replace_recursive(
				$settings[$siteId],
				$_POST["EBAY_SETTINGS"]
			);

			$wizardPrevStep = new $className(
				$siteId,
				$settings
			);

			$wizardPrevStep->save();
			$settings = $ebay->getSettings();
		}
	}

	/** @var Wizard\Step  $wizardStep */
	$wizardStepClassName = '\Bitrix\Sale\TradingPlatform\Ebay\Wizard\\'.$stepClassesList[$step];
	$wizardStep = new $wizardStepClassName($siteId, $settings, $cleanCache);
	\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/ebay_admin.js", true);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	?>
		<style>
			#result {
				width: 800px;
				min-height: 100px;
				background: #fff;
				border-radius: 10px;
				padding : 20px;
				font-size : 14px;
			}

			.adm-sale-ebay-wizard-state{
				width: 300px;
				min-height: 100px;
				background: #fff;
				border-radius: 10px;
				padding : 20px;
				font-size : 14px;
			}
			.adm-sale-ebay-wizard-state-header {
				font-size : 16px;
				font-weight: bold;
				text-align: center;
			}
			.adm-sale-ebay-wizard-state-item {
				padding: 10px;
			}
			.adm-sale-ebay-wizard-state-item.active{
				background: #ccd7db;
			}


		</style>
		<span id="adm-sale-ebay-wiazard-admin-msg"></span>
		<table>
			<tr><td style="vertical-align: top;">
				<form method="POST" action="<?echo $APPLICATION->GetCurPageParam('', array('STEP', 'SITE_ID', 'CLEAN_CACHE'))?>" name="form">
				<?=bitrix_sessid_post();?>
				<div id="result">
					<table width="100%">
						<tr>
							<td width="120px">
								<img alt="eBay logo" src="/bitrix/images/sale/ebay/logo.png" style="width: 100px; height: 67px;">
							</td><td style="vertical-align: middle; text-align: left;">
								<span style="font-weight: bold; font-size: 16px;"><?=$wizardStep->getName();?></span>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>
								<?=$wizardStep->getHtml()?>
							</td>
						</tr>
					</table>
				</div>
				<input type="hidden" name="STEP" value="<?=$step?>">
				<input type="hidden" name="lang" value="<?=LANGUAGE_ID;?>"><br>
				<input type="hidden" name="SITE_ID" value="<?=$siteId?>">
				<?if($step > 0):?>
					<input type="submit" value="<-- <?=Loc::getMessage('SALE_EBAY_W_BACK')?>" class="adm-btn-save" name="PREV_STEP">
				<?endif;?>
				<?if((count($stepClassesList) > ($step+1))
					&& (
						!$wizardPrevStep
						|| !$wizardPrevStep->mustBeCompletedBeforeNext()
						|| (
							$wizardPrevStep->mustBeCompletedBeforeNext()
							&& $wizardPrevStep->isSucceed($siteId, $settings)
							)
						)
					):?>
					<input type="submit" value="<?=($step <= 0) ? Loc::getMessage('SALE_EBAY_W_START_SETTING') : Loc::getMessage('SALE_EBAY_W_FURTHER').' -->'?>" class="adm-btn-save" name="NEXT_STEP">
				<?endif;?>
			</form>
			</td><td style="vertical-align: top;">
				<div class="adm-sale-ebay-wizard-state">
					<div class="adm-sale-ebay-wizard-state-header"><?=Loc::getMessage('SALE_EBAY_W_CONTENTS')?></div>
					<div class="adm-sale-ebay-wizard-state-body">
						<?/** @var Wizard\Step $fullClassName */?>
						<?$prevStepCompleted = true;?>
						<?foreach($stepClassesList as $classStep => $class):?>
							<?$fullClassName = '\Bitrix\Sale\TradingPlatform\Ebay\Wizard\\'.$class;?>
							<div class="adm-sale-ebay-wizard-state-item<?=($classStep == $step ? ' active' : '')?>">
								<?if($prevStepCompleted && $fullClassName::hasState()):?>
									<?=Wizard\Step::getLampHtml($fullClassName::isSucceed($siteId, $settings))?>
								<?else:?>
									<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
								<?endif;?>
								<?if($prevStepCompleted):?>
									<a href="<?=$APPLICATION->GetCurPageParam(
										'lang='.LANGUAGE_ID.'&STEP='.$classStep.'&SITE_ID='.$siteId,
										array('STEP', 'lang', 'SITE_ID', 'CLEAN_CACHE'))
										?>">
										<?=htmlspecialcharsbx($fullClassName::getName())?>
									</a>
								<?else:?>
									<?=htmlspecialcharsbx($fullClassName::getName())?>
								<?endif?>
							</div>
							<?$prevStepCompleted = $prevStepCompleted && (!$fullClassName::mustBeCompletedBeforeNext() || ($fullClassName::mustBeCompletedBeforeNext() && $fullClassName::isSucceed($siteId, $settings)));?>
						<?endforeach;?>
					</div>
				</div>
				<br><input type="button" value="<?=Loc::getMessage('SALE_EBAY_W_CLEAN_CACHE')?>" class="adm-btn-save" name="CLEAN_CACHE_BUTT" onclick="window.location.href='<?=$APPLICATION->GetCurPageParam('lang='.LANGUAGE_ID.'&STEP='.$step.'&SITE_ID='.$siteId.'&CLEAN_CACHE=Y', array('SITE_ID', 'STEP', 'lang', 'CLEAN_CACHE'))?>';">
			</td></tr>
		</table>

		<?if($adminMessage = $wizardStep->getAdminMessage()):?>
			<script type="text/javascript">
				BX.ready( function(){
					BX("adm-sale-ebay-wiazard-admin-msg").innerHTML = "<?=CUtil::JSEscape($adminMessage->Show())?>";
				});
			</script>
		<?endif;?>
	<?
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}
namespace Bitrix\Sale\TradingPlatform\Ebay\Wizard
{

	use Bitrix\Main\Config\Option;
	use Bitrix\Sale\TradingPlatform\Ebay\Api\Caller;
	use Bitrix\Sale\TradingPlatform\Ebay\Ebay;
	use Bitrix\Sale\TradingPlatform\Helper;
	use Bitrix\Sale\TradingPlatform\Logger;
	use Bitrix\Sale\TradingPlatform\Xml2Array;
	use Bitrix\Main\Localization\Loc;

	Loc::loadMessages(__FILE__);

	abstract class Step
	{
		protected $siteId = "";
		protected $ebaySettings = array();
		public static $useCache = true;
		protected static $errors = array();

		abstract public function getHtml();
		public static function hasState() { return false; }
		public static function isSucceed($siteId, array $ebaySettings) { return true; }
		public static function mustBeCompletedBeforeNext() { return false; }
		public static function getName() { return ""; }


		public function __construct($siteId, array $ebaySettings, $cleanCache = false)
		{
			$this->siteId = $siteId;
			$this->ebaySettings = $ebaySettings;

			if($cleanCache)
			{
				Step::$useCache = false;
				self::cleanCache();
			}
		}

		/**
		 * @return \CAdminMessage|null
		 */
		public function getAdminMessage()
		{
			if(empty(self::$errors))
				return null;

			return new \CAdminMessage(array(
				"TYPE" => "ERROR",
				"MESSAGE" => implode("<br>\n", self::$errors),
				"HTML" => true
			));
		}

		public function save()
		{
			$ebay = \Bitrix\Sale\TradingPlatform\Ebay\Ebay::getInstance();
			$settings = $ebay->getSettings();

			if(!is_array($settings[$this->siteId]))
				$settings[$this->siteId] = array();

			$settings[$this->siteId] = array_replace_recursive(
				$settings[$this->siteId],
				$this->ebaySettings[$this->siteId]
			);

			return $ebay->saveSettings($settings);
		}

		public static function getLampHtml($isGreen = true)
		{
			return '<img src="/bitrix/images/sale/'.($isGreen ? 'green.gif' : 'red.gif').'" hspace="4">';
		}

		protected static function getUserId($siteId, $ebaySettings)
		{
			$result = "";

			if(!empty($ebaySettings[$siteId]["SFTP_LOGIN"]) && strlen(($ebaySettings[$siteId]["SFTP_LOGIN"])) > 0)
				$result = $ebaySettings[$siteId]["SFTP_LOGIN"];

			return $result;
		}

		protected static function getUserInfo($siteId, array $ebaySettings)
		{
			if(strlen($siteId) <= 0 || empty($ebaySettings))
				return array();

			if(empty($ebaySettings[$siteId]["API"]["AUTH_TOKEN"]))
				return array();

			$userId = self::getUserId($siteId, $ebaySettings);

			if(strlen($userId) <= 0)
				return array();

			$cacheManager = \Bitrix\Main\Application::getInstance()->getManagedCache();
			$ttl = 86400;
			$cacheId = __FILE__.":USER_INFO";

			if(Step::$useCache && $cacheManager->read($ttl, $cacheId))
			{
				$result = $cacheManager->get($cacheId);
			}
			else
			{
				if(!Step::$useCache)
					$cacheManager->clean($cacheId);

				$ebay = \Bitrix\Sale\TradingPlatform\Ebay\Ebay::getInstance();
				$settings = $ebay->getSettings();

				if(empty($settings[$siteId]["API"]["AUTH_TOKEN"]))
					return array();

				$data = '<?xml version="1.0" encoding="utf-8"?>
					<GetUserRequest xmlns="urn:ebay:apis:eBLBaseComponents">
					<RequesterCredentials>
						<eBayAuthToken>'.$settings[$siteId]["API"]["AUTH_TOKEN"].'</eBayAuthToken>
					</RequesterCredentials>
					<UserID>'.$userId.'</UserID>
					</GetUserRequest>';

				$caller = new Caller(array(
					"URL" => \Bitrix\Sale\TradingPlatform\Ebay\Ebay::getApiUrl()
				));

				$xmpRes = $caller->sendRequest("GetUser", $data);
				$result = Xml2Array::convert($xmpRes);
				$cacheManager->set($cacheId, $result);
			}

			if(!empty($result['Errors']) && empty(self::$errors[__METHOD__]))
			{
				$errorMessage = '';

				if(!empty($result['Errors']['LongMessage']))
					$errorMessage .= htmlspecialcharsbx($result['Errors']['LongMessage']);
				elseif(!empty($result['Errors']['ShortMessage']))
					$errorMessage .= htmlspecialcharsbx($result['Errors']['ShortMessage']);

				if(!empty($result['Errors']['ErrorCode']))
					$errorMessage .= ' (ErrorCode: '.htmlspecialcharsbx($result['Errors']['ErrorCode']).')';

				if(!empty($errorMessage))
					self::$errors[__METHOD__] = $errorMessage;
				else
					self::$errors[__METHOD__] = Loc::getMessage('SALE_EBAY_W_USER_INFO_ERROR');
			}

			return $result;
		}

		protected static function getPolicyInfo($siteId, $ebaySettings)
		{
			if(strlen($siteId) <= 0 || empty($ebaySettings[$siteId]["API"]["AUTH_TOKEN"]))
				return array();

			$cacheManager = \Bitrix\Main\Application::getInstance()->getManagedCache();
			$ttl = 86400;
			$cacheId = __FILE__.":POLICY_INFO";

			if(Step::$useCache && $cacheManager->read($ttl, $cacheId))
			{
				$result = $cacheManager->get($cacheId);
			}
			else
			{
				if(!Step::$useCache)
					$cacheManager->clean($cacheId);

				$policy = new \Bitrix\Sale\TradingPlatform\Ebay\Policy($ebaySettings[$siteId]["API"]["AUTH_TOKEN"], $siteId);
				$result =  $policy->getItemsList();
				$cacheManager->set($cacheId, $result);
			}

			return $result;
		}

		protected static function cleanCache()
		{
			$cacheManager = \Bitrix\Main\Application::getInstance()->getManagedCache();
			$cacheManager->clean(__FILE__.":POLICY_INFO");
			$cacheManager->clean(__FILE__.":USER_INFO");
		}
	}

	class StepWelcome extends Step
	{
		public static function getName()
		{
			return Loc::getMessage('SALE_EBAY_W_EBAY_CONNECT');
		}

		public function getHtml()
		{
			return Loc::getMessage('SALE_EBAY_W_STEP_WELCOME');
		}

		public static function hasState() { return false; }
	}

	class StepSite extends Step
	{
		public static function getName()
		{
			return Loc::getMessage('SALE_EBAY_W_STEP_SITE');
		}

		public function getHtml()
		{
			$logLevel = !empty($this->ebaySettings[$this->siteId]["LOG_LEVEL"]) ? htmlspecialcharsbx($this->ebaySettings[$this->siteId]["LOG_LEVEL"]) : Logger::LOG_LEVEL_ERROR;


			if(!empty($this->ebaySettings[$this->siteId]["EMAIL_ERRORS"]))
				$notificationEmail = htmlspecialcharsbx($this->ebaySettings[$this->siteId]["EMAIL_ERRORS"]);
			else
				$notificationEmail = Option::get("sale", "order_email", "");

			$domainName = "";

			if(!empty($this->ebaySettings[$this->siteId]["DOMAIN_NAME"]))
			{
				$domainName = $this->ebaySettings[$this->siteId]["DOMAIN_NAME"];
			}
			else
			{
				$dbRes = \Bitrix\Main\SiteTable::getById($this->siteId);

				if($site = $dbRes->fetch())
					$domainName = $site["SERVER_NAME"];

				if (strlen($domainName) <=0)
				{
					if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME)>0)
						$domainName = SITE_SERVER_NAME;
					else
						$domainName = \COption::GetOptionString("main", "server_name", "www.bitrixsoft.com");
				}
			}

			$domainName = htmlspecialcharsbx($domainName);

			return
				'<table>'.
					'<tr><td>'.Loc::getMessage('SALE_EBAY_W_STEP_SITE_CHOOSE').':</td><td>'.\CLang::SelectBox("SITE_ID_SELECTED", $this->siteId).
					'<input type="hidden" name="EBAY_SETTINGS[LOG_LEVEL]" value="'.$logLevel.'">'.
					'<input type="hidden" name="EBAY_SETTINGS[DOMAIN_NAME]" value="'.$domainName.'">'.
					'<input type="hidden" name="EBAY_SETTINGS[API][SITE_ID]" value="215">'.
					'</td></tr>'.
					'<tr><td>'.Loc::getMessage('SALE_EBAY_W_STEP_EMAIL').': '.'</td><td>'.
					'<input type="text" name="EBAY_SETTINGS[EMAIL_ERRORS]" size="45" maxlength="255" value="'.$notificationEmail.'">'.
					'</td></tr>'.
				'</table>';
		}

		public static function mustBeCompletedBeforeNext() { return true; }
		public static function hasState() { return true; }

		public static function isSucceed($siteId, array $ebaySettings)
		{
			return (!empty($ebaySettings[$siteId]) && is_array($ebaySettings[$siteId]));
		}
	}

	class StepPersonType extends Step
	{
		public static function getName()
		{
			return Loc::getMessage('SALE_EBAY_W_STEP_PAYER');
		}

		public function getHtml()
		{
			$personTypeId = $this->ebaySettings[$this->siteId]["PERSON_TYPE"];
			$result ='';

			foreach (Helper::getPersonTypesList($this->siteId) as $ptId => $ptName)
			{
				if($result == '' && intval($personTypeId) <= 0)
					$personTypeId = $ptId;

				$result .= '<option value="'.$ptId.'"'.($personTypeId == $ptId ? " selected" : "").'>'.htmlspecialcharsbx($ptName).'</option>';
			}

			$result =
				Loc::getMessage('SALE_EBAY_W_STEP_PAYER_CONNECTED').': '.
				' <select name="EBAY_SETTINGS[PERSON_TYPE]">'.
				$result.
				'</select>'.
				$this->createPropsMapHtml($personTypeId).
				$this->createStatusMapHtml().
				'<br><br><hr><br>'.
				Loc::getMessage('SALE_EBAY_W_STEP_PAYER_CHOOSE').'.';

			return $result;
		}


		protected function createStatusMapHtml()
		{
			$result = "";

			$defaultValues = array(
				"Canceled" => "CANCELED"
			);

			foreach(\Bitrix\Sale\TradingPlatform\Ebay\Helper::getEbayOrderStatuses() as $ebayStatus)
			{
				$value = isset($this->ebaySettings[$this->siteId]["STATUS_MAP"][$ebayStatus]) ? $this->ebaySettings[$this->siteId]["STATUS_MAP"][$ebayStatus] : '';

				if(strlen($value) <= 0 && !empty($defaultValues[$ebayStatus]))
					$value = $defaultValues[$ebayStatus];

				$result .= '<input type="hidden" name="EBAY_SETTINGS[STATUS_MAP]['.$ebayStatus.']" value="'.$value.'">';
			}

			$value = isset($this->ebaySettings[$this->siteId]["STATUS_MAP"]["ORDER_READY_MAP"]) ? $this->ebaySettings[$this->siteId]["STATUS_MAP"]["ORDER_READY_MAP"] : 'PAYED';
			$result .= '<input type="hidden" name="EBAY_SETTINGS[ORDER_READY_MAP][ORDER_READY_MAP]" value="'.$value.'">';

			return $result;
		}

		protected function createPropsMapHtml($personTypeId)
		{
			if(intval($personTypeId) <= 0)
				return "";

			$result = "";
			$requiredOrderProperties = Helper::getRequiredOrderProps();
			$orderPropsList = Helper::getOrderPropsList($personTypeId);

			foreach($requiredOrderProperties as $orderPropertyCode)
			{
				$propIdForCode = 0;

				if(!empty($this->ebaySettings[$this->siteId]["ORDER_PROPS"][$orderPropertyCode]))
				{
					$propIdForCode = $this->ebaySettings[$this->siteId]["ORDER_PROPS"][$orderPropertyCode];
				}
				else
				{
					foreach($orderPropsList as $propParams)
					{
						if($propParams["CODE"] == $orderPropertyCode)
						{
							$propIdForCode = $propParams["ID"];
							break;
						}
					}
				}

				$result .= '<input type="hidden" name="EBAY_SETTINGS[ORDER_PROPS]['.$orderPropertyCode.']" value="'.$propIdForCode.'">';
			}

			return $result;
		}

		public static function mustBeCompletedBeforeNext() { return true; }
		public static function hasState() { return true; }

		public static function isSucceed($siteId, array $ebaySettings)
		{
			return (!empty($ebaySettings[$siteId]["PERSON_TYPE"]) && intval($ebaySettings[$siteId]["PERSON_TYPE"]) > 0);
		}
	}

	class StepEbayAccount extends Step
	{
		public static function getName()
		{
			return Loc::getMessage('SALE_EBAY_W_STEP_ACCOUNT');
		}

		public function getHtml()
		{
			return  Loc::getMessage('SALE_EBAY_W_STEP_ACCOUNT_SET').': '.
				'<input type="text" name="EBAY_SETTINGS[SFTP_LOGIN]" size="25" maxlength="255" value="'.(isset($this->ebaySettings[$this->siteId]["SFTP_LOGIN"]) ? htmlspecialcharsbx($this->ebaySettings[$this->siteId]["SFTP_LOGIN"]) : "").'">'.
				'<br><br><hr><br>'.
				Loc::getMessage('SALE_EBAY_W_STEP_ACCOUNT_REGISTER', array(
					'#R1#' => '<a href="https://reg.ebay.com/reg/FullReg?firstname=&lastname=&userid=&email=&countryId=168&ru=http%3A%2F%2Fwww.ebay.com" target="blank">',
					'#R2#' => '</a>',
					'#I1#' => '<a href="http://pages.ebay.com/ru/ru-ru/kak-prodavat-na-ebay-spravka/registratsiya-prodavtsa.html" target="blank">',
					'#I2#' => '</a>'
				));
		}

		public static function mustBeCompletedBeforeNext() { return true; }
		public static function hasState() { return true; }

		public static function isSucceed($siteId, array $ebaySettings)
		{
			return (!empty($ebaySettings[$siteId]["SFTP_LOGIN"]) && strlen(($ebaySettings[$siteId]["SFTP_LOGIN"])) > 0);
		}

		public function save()
		{
			Step::$useCache = false;
			return parent::save();
		}
	}

	class StepGetApiToken extends Step
	{
		public static function getName()
		{
			return Loc::getMessage('SALE_EBAY_W_STEP_TOKEN');
		}

		public function getHtml()
		{
			$token = isset($this->ebaySettings[$this->siteId]["API"]["AUTH_TOKEN"]) ? htmlspecialcharsbx($this->ebaySettings[$this->siteId]["API"]["AUTH_TOKEN"]) : "";

			return'<p>'.Loc::getMessage('SALE_EBAY_W_STEP_TOKEN_DESCR').'</p>'.
				'<input type="button" value="'.Loc::getMessage('SALE_EBAY_W_STEP_TOKEN_GET').'" onclick="window.open(\''.Ebay::getApiTokenUrl().'\', \'gettingToken\');">
				<br><br><textarea id="SALE_EBAY_SETTINGS_API_TOKEN" name="EBAY_SETTINGS[API][AUTH_TOKEN]" cols="70" rows="15" readonly>'.
				$token.
				'</textarea>
				<script>BX.Sale.EbayAdmin.addApiTokenListener({
							messageOk: \''.Loc::getMessage('SALE_EBAY_W_STEP_TOKEN_GET_OK').'\',
							messageError: \''.Loc::getMessage('SALE_EBAY_W_STEP_TOKEN_ERROR').'\'
						});
				</script>
				';
		}

		public static function mustBeCompletedBeforeNext() { return true; }
		public static function hasState() { return true; }

		public static function isSucceed($siteId, array $ebaySettings)
		{
			return (!empty($ebaySettings[$siteId]["API"]["AUTH_TOKEN"]) && strlen(($ebaySettings[$siteId]["API"]["AUTH_TOKEN"])) > 0);
		}
	}

	class StepPayPalAccount extends Step
	{
		public function __construct($siteId, array $ebaySettings, $cleanCache = false)
		{
			Step::$useCache = false;
			parent::__construct($siteId, $ebaySettings, true);
		}

		public static function getName()
		{
			return Loc::getMessage('SALE_EBAY_W_STEP_ACCOUNT_PP');
		}

		public function getHtml()
		{
			$data = self::getUserInfo($this->siteId, $this->ebaySettings);
			$isVerified = !empty($data["User"]["PayPalAccountLevel"]) && $data["User"]["PayPalAccountLevel"] == "Verified";
			$isBusiness = !empty($data["User"]["PayPalAccountType"]) && $data["User"]["PayPalAccountType"] == "Business";
			$isActive = !empty($data["User"]["PayPalAccountStatus"]) && $data["User"]["PayPalAccountStatus"] == "Active";

			$result =
				self::getLampHtml($isActive).($isActive ? Loc::getMessage('SALE_EBAY_W_STEP_ACTIVE') : Loc::getMessage('SALE_EBAY_W_STEP_NOT_ACTIVE')).'<br>'.
				self::getLampHtml($isVerified).($isVerified ? Loc::getMessage('SALE_EBAY_W_STEP_VERIFIED') : Loc::getMessage('SALE_EBAY_W_STEP_NOT_VERIFIED')).'<br>'.
				self::getLampHtml($isBusiness).($isBusiness ? Loc::getMessage('SALE_EBAY_W_STEP_CORPORATE') : Loc::getMessage('SALE_EBAY_W_STEP_NOT_CORPORATE')).'<br>'.
				'<br><br><hr><br>'.
				Loc::getMessage('SALE_EBAY_W_STEP_REGISTER_PP', array(
					'#R1#' => '<a href="https://www.paypal.com/ru/signup/account" target="blank">',
					'#R2#' => '</a>',
					'#I1#' => '<a href="http://p.ebaystatic.com/aw/ru/pdf/PP-signup_flow-v7-(no-CC).pdf" target="blank">',
					'#I2#' => '</a>',
					'#S1#' => '<a href="https://www.paypal.com/selfhelp/contact/call" target="blank">',
					'#S2#' => '</a>'
				));

			return $result;
		}

		public static function hasState() { return true; }

		public static function isSucceed($siteId, array $ebaySettings)
		{
			$data = self::getUserInfo($siteId, $ebaySettings);

			if(empty($data["User"]["PayPalAccountLevel"]) || $data["User"]["PayPalAccountLevel"] != "Verified")
				return false;

			if(empty($data["User"]["PayPalAccountType"]) || $data["User"]["PayPalAccountType"] != "Business")
				return false;

			if(empty($data["User"]["PayPalAccountStatus"]) || $data["User"]["PayPalAccountStatus"] != "Active")
				return false;

			return true;
		}
	}

	class StepConfirmContacts extends Step
	{

		public function __construct($siteId, array $ebaySettings, $cleanCache = false)
		{
			Step::$useCache = false;
			parent::__construct($siteId, $ebaySettings, true);
		}

		public static function getName()
		{
			return Loc::getMessage('SALE_EBAY_W_STEP_CONTACTS_CONFIRM');
		}

		public function getHtml()
		{
			$isConfirmed = $this->isSucceed($this->siteId, $this->ebaySettings);

			return
				self::getLampHtml($isConfirmed).' '.Loc::getMessage('SALE_EBAY_W_STEP_CONTACTS_DETAILS').' '.($isConfirmed ? Loc::getMessage('SALE_EBAY_W_STEP_CONFIRMED') : Loc::getMessage('SALE_EBAY_W_STEP_CONFIRMED')).'.'.
				'<br><br><hr><br>'.
				Loc::getMessage('SALE_EBAY_W_STEP_CONFIRMED_DETAIL',array(
					'#C1#' => '<a href="http://scgi.ebay.com/ws/eBayISAPI.dll?SellerSignin2&clientapptype=7" target="blank">',
					'#C2#' => '</a>',
					'#I1#' => '<a href="http://pages.ebay.com/ru/ru-ru/kak-prodavat-na-ebay-spravka/podtverjdeniye-dannih.html" target="blank">',
					'#I2#' => '</a>'
				)).'.';
		}

		public static function hasState() { return true; }

		public static function isSucceed($siteId, array $ebaySettings)
		{
			$data = self::getUserInfo($siteId, $ebaySettings);
			return !empty($data["User"]["Status"]) && $data["User"]["Status"] == "Confirmed";
		}
	}

	class StepLinkPaypal extends Step
	{
		public static function getName()
		{
			return Loc::getMessage('SALE_EBAY_W_STEP_EBAY_PP_LINK');
		}

		public function getHtml()
		{
			$succeed = $this->isSucceed($this->siteId, $this->ebaySettings);

			return
				self::getLampHtml($succeed).'&nbsp;'.Loc::getMessage('SALE_EBAY_W_STEP_PP_ACCOUNT').' '.($succeed ? Loc::getMessage('SALE_EBAY_W_STEP_LINKED') : Loc::getMessage('SALE_EBAY_W_STEP_NOT_LINKED')).".".
				'<br><br><hr><br>'.
				Loc::getMessage('SALE_EBAY_W_STEP_EBAY_PP_LINK_DESCR',array(
					'#I1#' => '<a href="http://pages.ebay.com/ru/ru-ru/kak-prodavat-na-ebay-spravka/privyazka-akkaunt-paypal-ebay.html" target="blank">',
					'#I2#' => '</a>'
				)).'.';
		}

		public static function hasState() { return true; }

		public static function isSucceed($siteId, array $ebaySettings)
		{
			$data = self::getUserInfo($siteId, $ebaySettings);
			$result = !empty($data["User"]["SellerInfo"]["PaymentMethod"]) &&  $data["User"]["SellerInfo"]["PaymentMethod"] == "PayPal";

			if(!$result && empty(self::$errors['PaymentMethod']))
			{
				self::$errors['PaymentMethod'] = Loc::getMessage(
					'SALE_EBAY_W_PAYMENT_METHOD_ERROR',
					array('#PAYMENT_METHOD#' => $data["User"]["SellerInfo"]["PaymentMethod"])
				);
			}

			return $result;
		}
	}

	class StepEbayAccountRussianConfirm extends Step
	{
		public static function getName()
		{
			return Loc::getMessage('SALE_EBAY_W_STEP_ACCOUNT_CONFIRM');
		}

		public function getHtml()
		{
			$succeed = $this->isSucceed($this->siteId, $this->ebaySettings);

			return
				self::getLampHtml($succeed).' '.Loc::getMessage('SALE_EBAY_W_STEP_EBAY_ACCOUNT').' '.($succeed ? Loc::getMessage('SALE_EBAY_W_STEP_EBAY_ACCOUNT_CONFIRMED') : Loc::getMessage('SALE_EBAY_W_STEP_EBAY_ACCOUNT_NOT_CONFIRMED')).'.'.
				'<br><br><hr><br>'.
				Loc::getMessage('SALE_EBAY_W_STEP_EBAY_ACCOUNT_DESCR', array(
					'#C1#' => '<a href="https://ocsnext.ebay.com/ocs/reghome" target="blank">',
					'#C2#' => '</a>',
					'#I1#' => '<a href="http://pages.ebay.com/ru/ru-ru/kak-prodavat-na-ebay-spravka/podtverjdenie-uchetnoy-zapisi.html" target="blank">',
					'#I2#' => '</a>'
				)).'.';
		}

		public static function hasState() { return true; }

		public static function isSucceed($siteId, array $ebaySettings)
		{
			$data = self::getUserInfo($siteId, $ebaySettings);
			$result = !empty($data["User"]["Site"]) && $data["User"]["Site"] == "Russia";

			if(!$result && empty(self::$errors['Site']))
			{
				self::$errors['Site'] = Loc::getMessage(
					'SALE_EBAY_W_SITE_ERROR',
					array('#SITE#' => $data["User"]["Site"])
				);
			}

			return $result;

		}
	}

	class StepEbayPolicies extends Step
	{
		public static function getName()
		{
			return Loc::getMessage('SALE_EBAY_W_STEP_POLICY');
		}

		public function getHtml()
		{
			$data = self::getPolicyInfo($this->siteId, $this->ebaySettings);
			$isPayments = !empty($data["paymentProfileList"]);
			$isReturns = !empty($data["returnPolicyProfileList"]);
			$isShipments = !empty($data["shippingPolicyProfile"]);

			return
				self::getLampHtml($isPayments).' '.Loc::getMessage('SALE_EBAY_W_STEP_POLICY_PAY').' '.($isPayments ? Loc::getMessage('SALE_EBAY_W_STEP_POLICY_CREATED') : Loc::getMessage('SALE_EBAY_W_STEP_POLICY_NOT_CREATED')).'.<br>'.
				self::getLampHtml($isReturns).' '.Loc::getMessage('SALE_EBAY_W_STEP_POLICY_RETURN').' '.($isReturns ? Loc::getMessage('SALE_EBAY_W_STEP_POLICY_CREATED') : Loc::getMessage('SALE_EBAY_W_STEP_POLICY_NOT_CREATED')).'.<br>'.
				self::getLampHtml($isShipments).' '.Loc::getMessage('SALE_EBAY_W_STEP_POLICY_SHIPMENT').' '.($isShipments ? Loc::getMessage('SALE_EBAY_W_STEP_POLICY_CREATED') : Loc::getMessage('SALE_EBAY_W_STEP_POLICY_NOT_CREATED')).'.<br>'.
				'<br><br><hr><br>'.
				Loc::getMessage('SALE_EBAY_W_STEP_POLICY_DESCR', array(
					'#P1#' => '<a href="http://www.bizpolicy.ebay.ru/businesspolicy/manage?totalPages=1" target="blank">',
					'#P2#' => '</a>',
					'#I1#' => '<a href="http://pages.ebay.com/ru/ru-ru/kak-prodavat-na-ebay-spravka/politiki.html" target="blank">',
					'#I2#' => '</a>'
				)).'.';
		}

		public static function hasState() { return true; }
		public static function mustBeCompletedBeforeNext() { return true; }

		public static function isSucceed($siteId, array $ebaySettings)
		{
			$data = self::getPolicyInfo($siteId, $ebaySettings);

			if(empty($data["returnPolicyProfileList"]))
				return false;

			if(empty($data["paymentProfileList"]))
				return false;

			if(empty($data["shippingPolicyProfile"]))
				return false;

			return true;
		}
	}

	class StepEbayDefaultPolicies extends Step
	{
		public static function getName()
		{
			return Loc::getMessage('SALE_EBAY_W_STEP_DEFAULT_POLICY');
		}

		public function getHtml()
		{
			$policy = new \Bitrix\Sale\TradingPlatform\Ebay\Policy($this->ebaySettings[$this->siteId]["API"]["AUTH_TOKEN"], $this->siteId);
			$hiddenPolicyFields = "";
			$result = '<tr><td>'.Loc::getMessage('SALE_EBAY_W_STEP_DEFAULT_POLICY_R').': </td><td>';
			$result .= '<select name="EBAY_SETTINGS[POLICY][RETURN][DEFAULT]">';

			foreach($policy->getPoliciesNames(\Bitrix\Sale\TradingPlatform\Ebay\Policy::TYPE_RETURN) as $policyId => $policyName)
			{
				$result .='<option value="'.htmlspecialcharsbx($policyId).'"'.(isset($this->ebaySettings[$this->siteId]["POLICY"]["RETURN"]["DEFAULT"]) && $this->ebaySettings[$this->siteId]["POLICY"]["RETURN"]["DEFAULT"] == $policyId ? " selected" : "").'>'.$policyName.'</option>';
				$hiddenPolicyFields .= '<input type="hidden" name="EBAY_SETTINGS[POLICY][RETURN][LIST]['.$policyId.']" value="'.htmlspecialcharsbx($policyName).'">';
			}

			$result .= '</select></td></tr>';
			$result .= '<tr><td>'.Loc::getMessage('SALE_EBAY_W_STEP_DEFAULT_POLICY_S').': </td><td>';
			$result .= '<select name="EBAY_SETTINGS[POLICY][SHIPPING][DEFAULT]">';

			foreach($policy->getPoliciesNames(\Bitrix\Sale\TradingPlatform\Ebay\Policy::TYPE_SHIPPING) as $policyId => $policyName)
			{
				$result .='<option value="'.htmlspecialcharsbx($policyId).'"'.(isset($this->ebaySettings[$this->siteId]["POLICY"]["SHIPPING"]["DEFAULT"]) && $this->ebaySettings[$this->siteId]["POLICY"]["SHIPPING"]["DEFAULT"] == $policyId ? " selected" : "").'>'.$policyName.'</option>';
				$hiddenPolicyFields .= '<input type="hidden" name="EBAY_SETTINGS[POLICY][SHIPPING][LIST]['.$policyId.']" value="'.htmlspecialcharsbx($policyName).'">';
			}

			$result .= '</select></td></tr>';
			$result .= '<tr><td>'.Loc::getMessage('SALE_EBAY_W_STEP_DEFAULT_POLICY_P').': </td><td>';
			$result .= '<select name="EBAY_SETTINGS[POLICY][PAYMENT][DEFAULT]">';

			foreach($policy->getPoliciesNames(\Bitrix\Sale\TradingPlatform\Ebay\Policy::TYPE_PAYMENT) as $policyId => $policyName)
			{
				$result .='<option value="'.htmlspecialcharsbx($policyId).'"'.(isset($this->ebaySettings[$this->siteId]["POLICY"]["PAYMENT"]["DEFAULT"]) && $this->ebaySettings[$this->siteId]["POLICY"]["PAYMENT"]["DEFAULT"] == $policyId ? " selected" : "").'>'.$policyName.'</option>';
				$hiddenPolicyFields .= '<input type="hidden" name="EBAY_SETTINGS[POLICY][PAYMENT][LIST]['.$policyId.']" value="'.htmlspecialcharsbx($policyName).'">';
			}

			$result .= '</select></td></tr>';
			$result = '<table>'.$result.'</table>';
			$result .= $hiddenPolicyFields;

			$result .= '<br><br><hr><br>'.
				Loc::getMessage('SALE_EBAY_W_STEP_DEFAULT_POLICY_DESCR');

			return $result;
		}

		public static function hasState() { return true; }

		public static function isSucceed($siteId, array $ebaySettings)
		{
			return (
				!empty($ebaySettings[$siteId]["POLICY"]["SHIPPING"]["LIST"])
				&& !empty($ebaySettings[$siteId]["POLICY"]["PAYMENT"]["LIST"])
				&& !empty($ebaySettings[$siteId]["POLICY"]["RETURN"]["LIST"])
				&& !empty($ebaySettings[$siteId]["POLICY"]["RETURN"]["DEFAULT"])
				&& !empty($ebaySettings[$siteId]["POLICY"]["PAYMENT"]["DEFAULT"])
				&& !empty($ebaySettings[$siteId]["POLICY"]["SHIPPING"]["DEFAULT"])
			);
		}
	}

	class StepPaymentMapping extends Step
	{
		public static function getName()
		{
			return Loc::getMessage('SALE_EBAY_W_STEP_PAY_LINK');
		}

		public function getHtml()
		{
			global $APPLICATION, $stepClassesList;

			if(empty($this->ebaySettings[$this->siteId]["PERSON_TYPE"]))
			{
				return
					self::getLampHtml(false).
					Loc::getMessage('SALE_EBAY_W_STEP_PAY_LINK_BEFORE', array(
						'#A1#' => '<a href="'.$APPLICATION->GetCurPageParam('lang='.LANGUAGE_ID.'&STEP='.$stepClassesList['StepPersonType']).'">',
						'#A2#' => '</a>'
					));
			}

			$result = '';
			$details = new \Bitrix\Sale\TradingPlatform\Ebay\Api\Details($this->siteId);

			if($details)
			{
				foreach($details->getListPayments() as $paymentOption =>  $paymentDescription)
				{
					$result .= '
					<tr>
						<td>'.Loc::getMessage('SALE_EBAY_W_STEP_PAY_LINK_SITE',array('#PAYMENT_DESCRIPTION#' => $paymentDescription)).':</td>
						<td>'.
							Helper::makeSelectorFromPaySystems(
								"EBAY_SETTINGS[MAPS][PAYMENT][".$paymentOption."]",
								!empty($this->ebaySettings[$this->siteId]["MAPS"]["PAYMENT"][$paymentOption]) ? $this->ebaySettings[$this->siteId]["MAPS"]["PAYMENT"][$paymentOption] : "",
								$this->ebaySettings[$this->siteId]["PERSON_TYPE"]
							).'
						</td>
					</tr>';
				}

				$result = '<table>'.$result.'</table>'.
					'<br><br><hr><br>'.Loc::getMessage('SALE_EBAY_W_STEP_PAY_LINK_DESCR').'.';
			}


			return $result;
		}

		public static function hasState() { return true; }

		public static function isSucceed($siteId, array $ebaySettings)
		{
			return !empty($ebaySettings[$siteId]["MAPS"]["PAYMENT"]) && is_array($ebaySettings[$siteId]["MAPS"]["PAYMENT"]);
		}
	}

	class StepShipmentMapping extends Step
	{
		public static function getName()
		{
			return Loc::getMessage('SALE_EBAY_W_STEP_SHP_LINK');
		}

		public function getHtml()
		{
			$result = '';
			$details = new \Bitrix\Sale\TradingPlatform\Ebay\Api\Details($this->siteId);

			if($details)
			{
				$arDeliveryList = Helper::getDeliveryList($this->siteId);
				foreach($details->getListShipping() as $service =>  $serviceDescription)
				{
					$result .= '
					<tr>
						<td>'.$serviceDescription.':</td>
						<td>
							<select name="EBAY_SETTINGS[MAPS][SHIPMENT]['.$service.']">
								<option value="">'.Loc::getMessage('SALE_EBAY_W_STEP_SHP_LINK_NOT_SET').'</option>';

					foreach($arDeliveryList as $deliveryId => $deliveryName)
					{
						$result .= '<option value="'.$deliveryId.'"'.
							(isset($this->ebaySettings[$this->siteId]["MAPS"]["SHIPMENT"][$service]) && $this->ebaySettings[$this->siteId]["MAPS"]["SHIPMENT"][$service] ==  $deliveryId ? " selected" : "").'>'.
							$deliveryName.'</option>';
					}

					$result .='
							</select>
						</td>
					</tr>
					';
				}

				$result = '<table>'.$result.'</table>'.
					'<br><br><hr><br>'.Loc::getMessage('SALE_EBAY_W_STEP_SHP_LINK_DESCR').'.';
			}

			return $result;
		}

		public static function hasState() { return true; }

		public static function isSucceed($siteId, array $ebaySettings)
		{
			return !empty($ebaySettings[$siteId]["MAPS"]["SHIPMENT"]) && is_array($ebaySettings[$siteId]["MAPS"]["SHIPMENT"]);
		}
	}

	class StepMIPConnect extends Step
	{
		public static function getName()
		{
			return Loc::getMessage('SALE_EBAY_W_STEP_MIP_CONNECT');
		}

		public function getHtml()
		{
			$sftpToken	= !empty($this->ebaySettings[$this->siteId]["SFTP_PASS"]) ? $this->ebaySettings[$this->siteId]["SFTP_PASS"] : "";
			$sftpLogin	= !empty($this->ebaySettings[$this->siteId]["SFTP_LOGIN"]) ? $this->ebaySettings[$this->siteId]["SFTP_LOGIN"] : "";
			$sftpTokenExp	= !empty($this->ebaySettings[$this->siteId]["SFTP_TOKEN_EXP"]) ? $this->ebaySettings[$this->siteId]["SFTP_TOKEN_EXP"] : "";

			return
				self::getLampHtml(strlen($sftpToken) > 0).' '.Loc::getMessage('SALE_EBAY_W_STEP_MIP_MIP').' '.(strlen($sftpToken) > 0 ? Loc::getMessage('SALE_EBAY_W_STEP_MIP_CONNECTED') : Loc::getMessage('SALE_EBAY_W_STEP_MIP_NOT_CONNECTED')).'.<br>'.
				'<br><br><hr><br>'.
				'<input type="button" value="'.Loc::getMessage('SALE_EBAY_W_STEP_MIP_TO_CONNECT').'" onclick="window.open(\''.Ebay::getSftpTokenUrl($sftpLogin).'\', \'gettingOAuthToken\');">'.
				'<input type="hidden" id="SALE_EBAY_SETTINGS_SFTP_TOKEN" name="EBAY_SETTINGS[SFTP_PASS]" value="'.$sftpToken.'">'.
				'<input type="hidden" id="SALE_EBAY_SETTINGS_SFTP_TOKEN_EXP" name="EBAY_SETTINGS[SFTP_TOKEN_EXP]" value="'.$sftpTokenExp.'">'.
				'<p>'.Loc::getMessage('SALE_EBAY_W_STEP_MIP_DESCR').'</p>'.
				'<script>BX.Sale.EbayAdmin.addSftpTokenEventListener({
					messageOk: \''.Loc::getMessage('SALE_EBAY_W_STEP_MIP_OK').'\',
					messageError: \''.Loc::getMessage('SALE_EBAY_W_STEP_MIP_ERROR').'\',
					submit: true
				});</script>';
		}

		public static function mustBeCompletedBeforeNext() { return true; }
		public static function hasState() { return true; }

		public static function isSucceed($siteId, array $ebaySettings)
		{
			return !empty($ebaySettings[$siteId]["SFTP_PASS"]);
		}
	}

	class StepImportEbayCategories extends Step
	{
		public static function getName()
		{
			return Loc::getMessage('SALE_EBAY_W_STEP_IMPORT');
		}

		public function getHtml()
		{
			$succeed = $this->isSucceed($this->siteId, $this->ebaySettings);

			return
				self::getLampHtml($succeed).($succeed ? Loc::getMessage('SALE_EBAY_W_STEP_IMPORT_LOADED', array('#COUNT#' => self::getCategoriesCount())) : Loc::getMessage('SALE_EBAY_W_STEP_IMPORT_NOT_LOADED')).'.'.
				'<br><br><hr><br>'.
				'<input type="button" value="'.Loc::getMessage('SALE_EBAY_W_STEP_IMPORT_TO_LOAD').'" onclick="BX.Sale.EbayAdmin.refreshCategoriesData(\''.$this->siteId.'\')">'.
				'<br><br>'.Loc::getMessage('SALE_EBAY_W_STEP_IMPORT_DESCR');
		}

		public static function hasState() { return true; }

		protected static function getCategoriesCount()
		{
			static $result = null;

			if($result !== null)
				return $result;

			$res = \Bitrix\Sale\TradingPlatform\Ebay\CategoryTable::getList(array(
				"select" => array("CNT"),
				"runtime" => array(
					new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)')
				)
			));

			if($cat = $res->fetch())
				$result = $cat["CNT"];

			return $result;
		}

		public static function isSucceed($siteId, array $ebaySettings)
		{
			return self::getCategoriesCount() > 0;
		}
	}

	class StepIblock extends Step
	{
		public static function getName()
		{
			return Loc::getMessage('SALE_EBAY_W_STEP_CATALOG');
		}

		public function getHtml()
		{
			$result = '<div id="SALE_EBAY_IBLOCK_CHOOSE">';

			if(!is_array($this->ebaySettings[$this->siteId]["IBLOCK_ID"]) || !isset($this->ebaySettings[$this->siteId]["IBLOCK_ID"]))
				$this->ebaySettings[$this->siteId]["IBLOCK_ID"] = array();

			$this->ebaySettings[$this->siteId]["IBLOCK_ID"][] = "";

			for($i = 0; $i < count($this->ebaySettings[$this->siteId]["IBLOCK_ID"]); $i++)
			{
				$result .= '<div  style="padding-top: 10px;">'.
						GetIBlockDropDownListEx(
							$this->ebaySettings[$this->siteId]["IBLOCK_ID"][$i],
							'EBAY_SETTINGS[IBLOCK_TYPE_ID]['.$i.']',
							'EBAY_SETTINGS[IBLOCK_ID]['.$i.']',
							array(
								'ID' => array_keys(
									Helper::getIblocksIds()
								),
								'ACTIVE' => 'Y',
								'CHECK_PERMISSIONS' => 'Y',
								'MIN_PERMISSION' => 'W',
								'SITE_ID' => $this->siteId
							),
							'',
							'this.form.submit();'
							).
					'&nbsp;'.
					Helper::getBitrixCategoryPropsHtml(
						"EBAY_SETTINGS[MORE_PHOTO_PROP][".$this->ebaySettings[$this->siteId]["IBLOCK_ID"][$i]."]",
						$this->ebaySettings[$this->siteId]["IBLOCK_ID"][$i],
						0,
						$this->ebaySettings[$this->siteId]["MORE_PHOTO_PROP"][$this->ebaySettings[$this->siteId]["IBLOCK_ID"][$i]]
					).
					'</div>';
			}

			$result .= '</div>'.
					'<input type="button" value="'.Loc::getMessage('SALE_EBAY_W_STEP_CATALOG_ADD').'" onclick="BX.Sale.EbayAdmin.addIblockSelect();" style="margin-top: 10px;">'.
					'<br><br><hr><br>'.Loc::getMessage('SALE_EBAY_W_STEP_CATALOG_DESCR').'.';

			return $result;
		}

		public function save()
		{
			$ebay = \Bitrix\Sale\TradingPlatform\Ebay\Ebay::getInstance();
			$settings = $ebay->getSettings();

			foreach(array('IBLOCK_ID', 'IBLOCK_TYPE_ID', 'MORE_PHOTO_PROP') as $param)
			{
				foreach($this->ebaySettings[$this->siteId][$param] as $key => $value)
					if(strlen($value) <= 0)
						unset($this->ebaySettings[$this->siteId][$param][$key]);

				$settings[$this->siteId][$param] = $this->ebaySettings[$this->siteId][$param];
			}

			$result = $ebay->saveSettings($settings);
			$this->ebaySettings = $ebay->getSettings();
			return $result;
		}

		public static function hasState() { return true; }

		public static function isSucceed($siteId, array $ebaySettings)
		{
			return !empty($ebaySettings[$siteId]["IBLOCK_TYPE_ID"])
				&& !empty($ebaySettings[$siteId]["IBLOCK_ID"]);
		}
	}

	class StepCategoriesMap extends Step
	{
		public static function getName()
		{
			return Loc::getMessage('SALE_EBAY_W_STEP_CATEGORIES');
		}

		public function getHtml()
		{
			$succeed = $this->isSucceed($this->siteId, $this->ebaySettings);

			return
				self::getLampHtml($succeed).Loc::getMessage('SALE_EBAY_W_STEP_CATEGORIES_COUNT').': '.self::getMappedCount($this->siteId, $this->ebaySettings).'.'.
				'<br><br><hr><br>'.
				Loc::getMessage('SALE_EBAY_W_STEP_CATEGORIES_DESCR', array(
					'#M1#' => '<a href="sale_ebay_general.php?lang='.LANGUAGE_ID.'&SITE_ID='.$this->siteId.'">',
					'#M2#' => '</a>'
				));
		}

		public static function hasState() { return true; }

		protected static function getMappedCount($siteId, $ebaySettings)
		{
			static $result = array();

			if(isset($result[$siteId]))
				return $result[$siteId];

			$result[$siteId] = 0;

			if(empty($ebaySettings[$siteId]['IBLOCK_ID']) || !is_array($ebaySettings[$siteId]['IBLOCK_ID']))
				return 0;

			foreach($ebaySettings[$siteId]['IBLOCK_ID'] as $ib)
			{
				$entityId = \Bitrix\Sale\TradingPlatform\Ebay\MapHelper::getCategoryEntityId($ib);
				$mapRes = \Bitrix\Sale\TradingPlatform\MapTable::getList(array(
					'filter' => array(
						'=ENTITY_ID' => $entityId
					),
					"select" => array("CNT"),
					"runtime" => array(
						new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)')
					)
				));

				if($map = $mapRes->fetch())
					$result[$siteId] += $map["CNT"];
			}

			return $result[$siteId];
		}

		public static function isSucceed($siteId, array $ebaySettings)
		{
			return self::getMappedCount($siteId, $ebaySettings) > 0;
		}
	}

	class StepStartExchange extends Step
	{
		public static function getName()
		{
			return Loc::getMessage('SALE_EBAY_W_STEP_EXCHANGE');
		}

		public function getHtml()
		{
			$exchangeStarted = $this->isSucceed($this->siteId, $this->ebaySettings);
			$result = self::getLampHtml($exchangeStarted).Loc::getMessage('SALE_EBAY_W_STEP_EXCHANGE_DATA').' '.($exchangeStarted ? Loc::getMessage('SALE_EBAY_W_STEP_EXCHANGE_STARTED') : Loc::getMessage('SALE_EBAY_W_STEP_EXCHANGE_NOT_STARTED')).'.';

			if(!$exchangeStarted)
			{
				$result .= '<br><br><hr><br>'.Loc::getMessage('SALE_EBAY_W_STEP_EXCHANGE_DESCR').'.';
				$defaultFeedIntervals = \Bitrix\Sale\TradingPlatform\Helper::getDefaultFeedIntervals();

				foreach(array("PRODUCT", "INVENTORY", "ORDER") as $feedType) //"IMAGE
				{
					$result .= '<input type="hidden" name="EBAY_SETTINGS[FEEDS]['.$feedType.'][INTERVAL]" value="'.(isset($this->ebaySettings[$this->siteId]["FEEDS"][$feedType]["INTERVAL"]) ? htmlspecialcharsbx($this->ebaySettings[$this->siteId]["FEEDS"][$feedType]["INTERVAL"]) : $defaultFeedIntervals[$feedType]).'">'.
						'<input type="hidden" name="EBAY_SETTINGS[FEEDS]['.$feedType.'][AGENT_ID]" value="'.(isset($this->ebaySettings[$this->siteId]["FEEDS"][$feedType]["AGENT_ID"]) ? htmlspecialcharsbx($this->ebaySettings[$this->siteId]["FEEDS"][$feedType]["AGENT_ID"]) : 0).'">';
				}
			}

			return $result;
		}

		public static function hasState() { return true; }

		public static function isSucceed($siteId, array $ebaySettings)
		{
			return !empty($ebaySettings[$siteId]["FEEDS"]) && is_array($ebaySettings[$siteId]["FEEDS"]);
		}

		public function save()
		{
			if(!empty($this->ebaySettings[$this->siteId]["FEEDS"]))
			{
				$this->ebaySettings[$this->siteId]["FEEDS"] = \Bitrix\Sale\TradingPlatform\Ebay\Agent::update(
					$this->siteId,
					$this->ebaySettings[$this->siteId]["FEEDS"]
				);
			}

			return parent::save();
		}
	}

	class StepFinish extends Step
	{
		public static function getName()
		{
			return Loc::getMessage('SALE_EBAY_W_STEP_FINISH');
		}


		public function getHtml()
		{
			return Loc::getMessage('SALE_EBAY_W_STEP_FINISH_DESCR');
		}
	}
}