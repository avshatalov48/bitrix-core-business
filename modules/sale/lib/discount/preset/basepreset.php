<?php

namespace Bitrix\Sale\Discount\Preset;

use Bitrix\Crm\Order\BuyerGroup;
use Bitrix\Main;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SiteTable;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Helpers\Admin\Blocks\OrderBasket;
use Bitrix\Sale\Internals\DiscountGroupTable;

abstract class BasePreset
{
	public const FINAL_STEP = 'FINALSTEP';
	public const STEP_NAME_VAR = '__next_step';

	public const RUN_PREV_STEP_NAME_VAR = '__run_prev_step';

	public const MODE_SHOW = 2;
	public const MODE_SAVE = 3;

	public const ACTION_TYPE_DISCOUNT = 'Discount';
	public const ACTION_TYPE_EXTRA = 'Extra';

	public const AVAILABLE_STATE_ALLOW = 0x01;
	public const AVAILABLE_STATE_DISALLOW = 0x02;
	public const AVAILABLE_STATE_TARIFF = 0x04;

	/** @var  ErrorCollection */
	protected $errorCollection;
	/** @var  \Bitrix\Main\HttpRequest */
	protected $request;
	/** @var string */
	protected $nextStep;
	/** @var string */
	protected $stepTitle;
	/** @var string */
	protected $stepDescription;
	/** @var string */
	private $stepResult;
	/** @var State */
	private $stepResultState;
	/** @var array */
	private array $discount;
	/** @var bool */
	private bool $restrictedGroupsMode = false;

	/** @var bool sign of the presence of Bitrix24 */
	protected bool $bitrix24Included;

	/**
	 * @return string the fully qualified name of this class.
	 */
	public static function className()
	{
		return get_called_class();
	}

	/**
	 * @param BasePreset $classObject
	 * @return string the short qualified name of this class.
	 */
	public static function classShortName(BasePreset $classObject)
	{
		return (new \ReflectionClass($classObject))->getShortName();
	}

	public function __construct()
	{
		$this->errorCollection = new ErrorCollection;
		$this->request = Context::getCurrent()->getRequest();
		$this->bitrix24Included = Loader::includeModule('bitrix24');

		$this->init();
	}

	public function enableRestrictedGroupsMode($state)
	{
		$this->restrictedGroupsMode = $state === true;
	}

	public function isRestrictedGroupsModeEnabled()
	{
		return $this->restrictedGroupsMode;
	}

	protected function init()
	{}

	public function hasErrors()
	{
		return !$this->errorCollection->isEmpty();
	}

	/**
	 * @return Error[]
	 */
	public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	public function getStepNumber()
	{
		if ($this->stepResultState)
		{
			return $this->stepResultState->getStepNumber();
		}

		return 1;
	}

	/**
	 * @return State
	 */
	protected function getState()
	{
		return State::createFromRequest($this->request);
	}

	public function getView()
	{
		return
			$this->beginForm($this->stepResultState)
			. $this->stepResult
			. $this->endForm($this->stepResultState)
		;
	}

	protected function isRunningPrevStep()
	{
		return (bool)$this->request->getPost(static::RUN_PREV_STEP_NAME_VAR);
	}

	public function executeAjaxAction($actionName)
	{
		$methodName = 'processAjaxAction' . $actionName;
		if (!method_exists($this, $methodName))
		{
			throw new SystemException("Could not find method {$methodName}");
		}

		$result = call_user_func_array([$this, $methodName], []);

		header('Content-Type:application/json; charset=UTF-8');
		\CMain::FinalActions(Json::encode($result));
	}

	public function processAjaxActionGetProductDetails(array $params = [])
	{
		$productId = !empty($params['productId']) ? $params['productId'] : $this->request->get('productId');
		if (is_array($productId))
		{
			$productId = array_pop($productId);
		}

		$quantity = !empty($params['quantity']) ? $params['quantity'] : $this->request->get('quantity');
		$quantity = (float)$quantity;

		$siteId = !empty($params['siteId']) ? $params['siteId'] : $this->request->get('siteId');
		$siteId = (string)$siteId;

		global $USER;
		$userId = $USER->getId();

		if (empty($productId))
		{
			throw new SystemException("Could not find product id");
		}
		if (empty($quantity))
		{
			$quantity = 1;
		}

		$productDetails = OrderBasket::getProductDetails($productId, $quantity, $userId, $siteId);
		if (!$productDetails || empty($productDetails['PRODUCT_ID']))
		{
			return $this->getProductInfo($productId);
		}

		return $productDetails;
	}

	private function getProductInfo($elementId): array
	{
		$elementId = (int)$elementId;
		if ($elementId <= 0)
		{
			return [];
		}

		$dbProduct = \CIBlockElement::getList(
			[],
			['ID' => $elementId],
			false,
			false,
			[
				'ID',
				'IBLOCK_ID',
				'IBLOCK_SECTION_ID',
				'DETAIL_PICTURE',
				'PREVIEW_PICTURE',
				'NAME',
				'XML_ID',
			]
		);
		$product = $dbProduct->fetch();
		unset($dbProduct);
		if (empty($product))
		{
			return [];
		}

		$imgCode = 0;
		if ($product["IBLOCK_ID"] > 0)
		{
			$product["EDIT_PAGE_URL"] = \CIBlock::getAdminElementEditLink(
				$product["IBLOCK_ID"],
				$elementId,
				["find_section_section" => $product["IBLOCK_SECTION_ID"]]
			);
		}

		if ($product["DETAIL_PICTURE"] > 0)
		{
			$imgCode = $product["DETAIL_PICTURE"];
		}
		elseif ($product["PREVIEW_PICTURE"] > 0)
		{
			$imgCode = $product["PREVIEW_PICTURE"];
		}

		if ($imgCode > 0)
		{
			$imgProduct = \CFile::resizeImageGet(
				\CFile::getFileArray($imgCode),
				[
					'width' => 80,
					'height' => 80,
				],
				BX_RESIZE_IMAGE_PROPORTIONAL,
				false,
				false
			);
			$product["PICTURE_URL"] = $imgProduct['src'];
		}
		$product['PRODUCT_ID'] = $product['ID'];

		return $product;
	}

	public function exec()
	{
		$isPost = $this->request->isPost();

		$stepName = $this->getStepName();
		$state = $this->getState();

		//edit existing discount
		if ($stepName === $this->getFirstStepName() && !$isPost && $this->isDiscountEditing())
		{
			$state = $this->generateState($this->discount);
		}

		if ($this->isRunningPrevStep())
		{
			$stepName = $state->getPrevStep();
		}

		if ($isPost && !$this->isRunningPrevStep())
		{
			/** @var State $state */
			list($state, $nextStep) = $this->runStep($stepName, $state, self::MODE_SAVE);

			if ($stepName != $nextStep)
			{
				$state->addStepChain($stepName);
			}

			$this->setNextStep($nextStep);
			if ($nextStep === static::FINAL_STEP)
			{
				$discountFields = $this->generateDiscount($state);

				if ($this->isDiscountEditing())
				{
					$this->updateDiscount($this->discount['ID'], $discountFields);
				}
				else
				{
					$this->addDiscount($discountFields);
				}

				if ($this->hasErrors())
				{
					$step = $state->popStepChain();
					$stepName = $step;

					$this->setNextStep($step);
				}
			}

			if (!$this->hasErrors())
			{
				$stepName = $nextStep;
			}
		}
		elseif ($this->isRunningPrevStep())
		{
			$step = $state->popStepChain();

			$this->setNextStep($step);
		}

		$this->stepResult = $this->runStep($stepName, $state, self::MODE_SHOW);
		$this->stepResultState = $state;

		return $this;
	}

	private function runStep($actionName, State $state, $mode = self::MODE_SHOW)
	{
		$methodName = '';
		if ($mode === self::MODE_SHOW)
		{
			$methodName = 'processShow' . $actionName;
		}
		elseif ($mode === self::MODE_SAVE)
		{
			$methodName = 'processSave' . $actionName;
		}

		if (!$methodName)
		{
			throw new SystemException("Unknown mode {$mode}");
		}

		if (!method_exists($this, $methodName))
		{
			throw new SystemException("Method {$methodName} is not exist");
		}

		return call_user_func_array(array($this, $methodName), array($state));
	}

	private function getStepName()
	{
		return $this->request->getPost(static::STEP_NAME_VAR) ?: static::getFirstStepName();
	}

	/**
	 * @return array
	 */
	public function getDiscount()
	{
		return
			$this->discount ?? []
		;
	}

	protected function isDiscountEditing()
	{
		return !empty($this->discount);
	}

	/**
	 * @param array $discount
	 * @return $this
	 */
	public function setDiscount($discount)
	{
		if (!is_array($discount))
		{
			$discount = [];
		}

		$this->discount = $this->prepareDiscountFields($discount);

		return $this;
	}

	/**
	 * @return string
	 */
	public function getStepTitle()
	{
		return $this->stepTitle;
	}

	/**
	 * @param string $stepTitle
	 */
	public function setStepTitle($stepTitle)
	{
		$this->stepTitle = $stepTitle;
	}

	/**
	 * @return string
	 */
	public function getStepDescription()
	{
		return $this->stepDescription;
	}

	/**
	 * @param string $stepDescription
	 * @return $this
	 */
	public function setStepDescription($stepDescription)
	{
		$this->stepDescription = $stepDescription;

		return $this;
	}

	/**
	 * Returns sort to sorting presets in category row.
	 * @return int
	 */
	public function getSort()
	{
		return 100;
	}

	/**
	 * @return string
	 */
	abstract public function getTitle();

	/**
	 * @deprecated
	 * @see BasePreset::getAvailableState
	 *
	 * Tells if preset is available or not. It's possible that preset can't work in some license.
	 *
	 * @return bool
	 */
	public function isAvailable()
	{
		return $this->getAvailableState() === self::AVAILABLE_STATE_ALLOW;
	}

	public function getPossible(): bool
	{
		return $this->getAvailableState() !== self::AVAILABLE_STATE_DISALLOW;
	}

	public function getAvailableState(): int
	{
		return self::AVAILABLE_STATE_ALLOW;
	}

	public function getAvailableHelpLink(): ?array
	{
		return null;
	}

	/**
	 * @return string
	 */
	abstract public function getDescription();

	public function getExtendedDescription()
	{
		return [
			'DISCOUNT_TYPE' => '',
			'DISCOUNT_VALUE' => '',
			'DISCOUNT_CONDITION' => '',
		];
	}

	/**
	 * @return string
	 */
	abstract public function getFirstStepName();

	/**
	 * @return int
	 */
	public function getCategory()
	{
		return Manager::CATEGORY_OTHER;
	}

	/**
	 * @return string
	 */
	public function getNextStep()
	{
		return $this->nextStep;
	}

	public function hasPrevStep()
	{
		$prevStep = $this->stepResultState->getPrevStep();

		return $prevStep && $prevStep != $this->getNextStep() && !$this->isLastStep();
	}

	public function isLastStep()
	{
		return $this->getNextStep() == static::FINAL_STEP;
	}

	/**
	 * @param string $nextStep
	 * @return $this
	 */
	public function setNextStep($nextStep)
	{
		$this->nextStep = $nextStep;

		return $this;
	}

	public function beginForm(State $state)
	{
		return '
			<form action="' . htmlspecialcharsbx($this->request->getRequestUri()) . '"'
			. ' enctype="multipart/form-data" method="post" name="__preset_form" id="__preset_form">'
			. $state->toString()
			. '<input type="hidden" name="' . static::STEP_NAME_VAR . '" id="' . static::STEP_NAME_VAR . '" value="' . htmlspecialcharsbx($this->getNextStep()) . '">
				<input type="hidden" name="' . static::RUN_PREV_STEP_NAME_VAR . '" id="' . static::RUN_PREV_STEP_NAME_VAR . '" value="">
				' . bitrix_sessid_post() . ' 
				<input type="hidden" name="lang" value="' . LANGUAGE_ID . '">
		';
	}

	public function endForm(State $state)
	{
		return '</form>';
	}

	protected function filterUserGroups(array $discountGroups)
	{
		if ($this->isRestrictedGroupsModeEnabled())
		{
			if (Main\Loader::includeModule('crm'))
			{
				$existingGroups = [];

				if ($this->isDiscountEditing())
				{
					$existingGroups = $this->getUserGroupsByDiscount($this->discount['ID']);
				}

				$discountGroups = BuyerGroup::prepareGroupIds($existingGroups, $discountGroups);
			}
		}

		return $discountGroups;
	}

	/**
	 * @param State $state
	 * @return array Discount fields.
	 */
	public function generateDiscount(State $state)
	{
		$siteId = $state->get('discount_lid');

		$discountGroups = $state->get('discount_groups') ?: [];
		$userGroups = $this->filterUserGroups($discountGroups);

		return [
			'LID' => $siteId,
			'NAME' => $state->get('discount_name'),
			'CURRENCY' => \Bitrix\Sale\Internals\SiteCurrencyTable::getSiteCurrency($siteId),
			'ACTIVE_FROM' => $state->get('discount_active_from'),
			'ACTIVE_TO' => $state->get('discount_active_to'),
			'ACTIVE' => 'Y',
			'SORT' => $state->get('discount_sort'),
			'PRIORITY' => $state->get('discount_priority'),
			'LAST_DISCOUNT' => $state->get('discount_last_discount'),
			'LAST_LEVEL_DISCOUNT' => $state->get('discount_last_level_discount'),
			'USER_GROUPS' => $userGroups,
		];
	}

	/**
	 * @param array $discountFields
	 * @return State $state
	 */
	public function generateState(array $discountFields)
	{
		return new State([
			'discount_id' => $discountFields['ID'],
			'discount_lid' => $discountFields['LID'],
			'discount_name' => $discountFields['NAME'],
			'discount_active_from' => $discountFields['ACTIVE_FROM'],
			'discount_active_to' => $discountFields['ACTIVE_TO'],
			'discount_last_discount' => $discountFields['LAST_DISCOUNT'],
			'discount_last_level_discount' => $discountFields['LAST_LEVEL_DISCOUNT'],
			'discount_priority' => $discountFields['PRIORITY'],
			'discount_sort' => $discountFields['SORT'],
			'discount_groups' => $this->getUserGroupsByDiscount($discountFields['ID']),
		]);
	}

	final protected function normalizeDiscountFields(array $discountFields): array
	{
		if (isset($discountFields['CONDITIONS']) && is_array($discountFields['CONDITIONS']))
		{
			$discountFields['CONDITIONS_LIST'] = $discountFields['CONDITIONS'];
		}

		if (isset($discountFields['CONDITIONS_LIST']) && is_string($discountFields['CONDITIONS_LIST']))
		{
			$discountFields['CONDITIONS_LIST'] = unserialize($discountFields['CONDITIONS_LIST'], ['allowed_classes' => false]);
		}

		if (isset($discountFields['CONDITIONS_LIST']) && is_array($discountFields['CONDITIONS_LIST']))
		{
			$discountFields['CONDITIONS'] = $discountFields['CONDITIONS_LIST'];
		}

		if (isset($discountFields['ACTIONS']) && is_array($discountFields['ACTIONS']))
		{
			$discountFields['ACTIONS_LIST'] = $discountFields['ACTIONS'];
		}

		if (isset($discountFields['ACTIONS_LIST']) && is_string($discountFields['ACTIONS_LIST']))
		{
			$discountFields['ACTIONS_LIST'] = unserialize($discountFields['ACTIONS_LIST'], ['allowed_classes' => false]);
		}

		if (isset($discountFields['ACTIONS_LIST']) && is_array($discountFields['ACTIONS_LIST']))
		{
			$discountFields['ACTIONS'] = $discountFields['ACTIONS_LIST'];
		}

		if (isset($discountFields['PREDICTIONS_LIST']) && is_string($discountFields['PREDICTIONS_LIST']))
		{
			$discountFields['PREDICTIONS_LIST'] = unserialize($discountFields['PREDICTIONS_LIST'], ['allowed_classes' => false]);
		}

		if (isset($discountFields['PREDICTIONS_LIST']) && is_array($discountFields['PREDICTIONS_LIST']))
		{
			$discountFields['PREDICTIONS'] = $discountFields['PREDICTIONS_LIST'];
		}

		return $discountFields;
	}

	protected function updateDiscount($id, array $discountFields)
	{
		$discountFields['PRESET_ID'] = $this->className();

		if (!\CSaleDiscount::update($id, $discountFields))
		{
			global $APPLICATION;
			$ex = $APPLICATION->getException();
			if ($ex)
			{
				$this->errorCollection[] = new Error($ex->getString());
			}
			else
			{
				$this->errorCollection[] = new Error(Loc::getMessage('SALE_BASE_PRESET_DISCOUNT_EDIT_ERR_UPDATE'));
			}
		}
	}

	protected function addDiscount(array $discountFields)
	{
		$discountFields['PRESET_ID'] = $this->className();

		$discountId = \CSaleDiscount::add($discountFields);

		if ($discountId <= 0)
		{
			global $APPLICATION;
			$ex = $APPLICATION->getException();
			if ($ex)
			{
				$this->errorCollection[] = new Error($ex->getString());
			}
			else
			{
				$this->errorCollection[] = new Error(Loc::getMessage('SALE_BASE_PRESET_DISCOUNT_EDIT_ERR_ADD'));
			}
		}
	}

	public function processShowFinalStep(State $state)
	{
		return Loc::getMessage(
			'SALE_BASE_PRESET_FINAL_OK',
			[
				'#NAME#' => htmlspecialcharsbx($state->get('discount_name')),
			]
		);
	}

	protected function getUserGroupsByDiscount($discountId)
	{
		$groups = [];
		$groupDiscountIterator = DiscountGroupTable::getList([
			'select' => [
				'GROUP_ID',
			],
			'filter' => [
				'DISCOUNT_ID' => $discountId,
				'=ACTIVE' => 'Y',
			],
		]);
		while ($groupDiscount = $groupDiscountIterator->fetch())
		{
			$groups[] = (int)$groupDiscount['GROUP_ID'];
		}
		unset(
			$groupDiscount,
			$groupDiscountIterator,
		);

		return $groups;
	}

	protected function getSiteList(): array
	{
		return $this->getSaleSiteList() ?: $this->getFullSiteList();
	}

	private function getSaleSiteList(): array
	{
		$siteList = [];
		$siteIterator = SiteTable::getList([
			'select' => [
				'LID',
				'NAME',
				'SORT',
			],
			'filter' => [
				'=ACTIVE' => 'Y',
			],
			'order' => [
				'SORT' => 'ASC',
			],
		]);
		while ($site = $siteIterator->fetch())
		{
			$saleSite = Option::get('sale', 'SHOP_SITE_' . $site['LID']);
			if ($site['LID'] == $saleSite)
			{
				$siteList[$site['LID']] = '(' . $site['LID'] . ') ' . $site['NAME'];
			}
		}
		unset(
			$site,
			$siteIterator,
		);

		return $siteList;
	}

	private function getFullSiteList(): array
	{
		$siteList = [];
		$siteIterator = SiteTable::getList([
			'select' => [
				'LID',
				'NAME',
				'SORT',
			],
			'order' => [
				'SORT' => 'ASC',
			],
		]);
		while ($site = $siteIterator->fetch())
		{
			$siteList[$site['LID']] = '(' . $site['LID'] . ') ' . $site['NAME'];
		}
		unset(
			$site,
			$siteIterator,
		);

		return $siteList;
	}

	protected function processShowInputNameInternal(State $state)
	{
		return '
			<table width="100%" border="0" cellspacing="7" cellpadding="0">
				<tbody>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:40%;"><strong>' . Loc::getMessage('SALE_BASE_PRESET_ORDERAMOUNT_FIELD_NAME') . ':</strong></td>
					<td class="adm-detail-content-cell-r" style="width:60%;">
						<input type="text" name="discount_name" value="' . htmlspecialcharsbx($state->get('discount_name')) . '" size="39" maxlength="100" style="width: 300px;">
					</td>
				</tr>
				<tr>
					<td class="adm-detail-content-cell-l"><strong>' . Loc::getMessage('SALE_BASE_PRESET_ORDERAMOUNT_LID') . ':</strong></td>
					<td class="adm-detail-content-cell-r">
						' . HtmlHelper::generateSelect('discount_lid', $this->getSiteList(), $state->get('discount_lid')) . '
					</td>
				</tr>
				</tbody>
			</table>
		';
	}

	protected function processSaveInputNameInternal(State $state, $nextStep)
	{
		if (!trim($state->get('discount_name')))
		{
			$this->errorCollection[] = new Error(Loc::getMessage('SALE_BASE_PRESET_ERROR_EMPTY_NAME'));
		}

		if (!trim($state->get('discount_lid')))
		{
			$this->errorCollection[] = new Error(Loc::getMessage('SALE_BASE_PRESET_ERROR_EMPTY_LID'));
		}

		if (!$this->errorCollection->isEmpty())
		{
			return array($state, 'InputName');
		}

		return array($state, $nextStep);
	}

	protected function addErrorEmptyActionValue(): void
	{
		$this->errorCollection[] = new Error(Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ERROR_EMPTY_VALUE_MSGVER_1'));
	}

	protected function processShowCommonSettingsInternal(State $state)
	{
		$groupList = $this->getAllowableUserGroups();

		switch (LANGUAGE_ID)
		{
			case 'en':
			case 'ru':
			case 'de':
				$hintLastDiscountImageName = 'hint_last_discount_' . LANGUAGE_ID .  '.png';
				break;
			default:
				$hintLastDiscountImageName = 'hint_last_discount_' . Main\Localization\Loc::getDefaultLang(LANGUAGE_ID) .  '.png';
				break;
		}

		$periodValue = '';
		if ($state->get('discount_active_from') || $state->get('discount_active_to'))
		{
			$periodValue = \CAdminCalendar::PERIOD_INTERVAL;
		}

		return '
			<table width="100%" border="0" cellspacing="7" cellpadding="0">
				<tbody>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:40%;"><strong>' . Loc::getMessage('SALE_BASE_PRESET_ORDERAMOUNT_USER_GROUPS') . ':</strong></td>
					<td class="adm-detail-content-cell-r">
						' . HtmlHelper::generateMultipleSelect('discount_groups[]', $groupList, $state->get('discount_groups', array()), array('size=8')) . '
					</td>
				</tr>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:40%;"><strong>' . Loc::getMessage('SALE_BASE_PRESET_ACTIVE_PERIOD') . ':</strong></td>
					<td class="adm-detail-content-cell-r">' .
			\CAdminCalendar::CalendarPeriodCustom(
				'discount_active_from',
				'discount_active_to',
				$state->get('discount_active_from'),
				$state->get('discount_active_to'),
				true,
				19,
				true,
				array(
					\CAdminCalendar::PERIOD_EMPTY => Loc::getMessage('SALE_BASE_PRESET_CALENDAR_PERIOD_EMPTY'),
					\CAdminCalendar::PERIOD_INTERVAL => Loc::getMessage('SALE_BASE_PRESET_CALENDAR_PERIOD_INTERVAL')
				),
				$periodValue
			) . '
					</td>
				</tr>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:40%;"><strong>' . Loc::getMessage('SALE_BASE_PRESET_ORDERAMOUNT_FIELD_PRIORITY') . ':</strong></td>
					<td class="adm-detail-content-cell-r" style="width:60%;">
						<input type="text" name="discount_priority" value="' . (int)$state->get('discount_priority', 1) . '" size="39" maxlength="100" style="width: 100px;">
					</td>
				</tr>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:40%;"><strong>' . Loc::getMessage('SALE_BASE_PRESET_ORDERAMOUNT_FIELD_SORT') . ':</strong></td>
					<td class="adm-detail-content-cell-r" style="width:60%;">
						<input type="text" name="discount_sort" value="' . (int)$state->get('discount_sort', 100) . '" size="39" maxlength="100" style="width: 100px;">
					</td>
				</tr>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:40%;">
						<script>BX.ready(function(){BX.hint_replace(BX("tr_HELP_notice"), \'<img style="padding-left: 16px;" width="545" height="353" src="/bitrix/images/sale/discount/' . $hintLastDiscountImageName . '" alt="">\');})</script>
						<span id="tr_HELP_notice"></span>
						<strong>' . Loc::getMessage('SALE_BASE_PRESET_LAST_LEVEL_DISCOUNT_LABEL') . ':</strong>
					</td>
					<td class="adm-detail-content-cell-r" style="width:60%;">
						<input type="checkbox" name="discount_last_level_discount" value="Y" ' . ($state->get('discount_last_level_discount', 'N') == 'Y'? 'checked' : '') . '>
					</td>
				</tr>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:40%;">
						<script>BX.ready(function(){BX.hint_replace(BX("tr_HELP_notice2"), \'<img style="padding-left: 16px;" width="545" height="353" src="/bitrix/images/sale/discount/' . $hintLastDiscountImageName . '" alt="">\');})</script>
						<span id="tr_HELP_notice2"></span>
						<strong>' . Loc::getMessage('SALE_BASE_PRESET_LAST_DISCOUNT_LABEL') . ':</strong>
					</td>
					<td class="adm-detail-content-cell-r" style="width:60%;">
						<input type="checkbox" name="discount_last_discount" value="Y" ' . ($state->get('discount_last_discount', 'Y') == 'Y'? 'checked' : '') . '>
					</td>
				</tr>
				</tbody>
			</table>
		';
	}

	protected function processSaveCommonSettingsInternal(State $state, $nextStep = self::FINAL_STEP)
	{
		if (!$state->get('discount_groups'))
		{
			$this->errorCollection[] = new Error(Loc::getMessage('SALE_BASE_PRESET_ERROR_EMPTY_USER_GROUPS'));
		}

		$priority = (int)$state->get('discount_priority');
		if ($priority <= 0)
		{
			$this->errorCollection[] = new Error(Loc::getMessage('SALE_BASE_PRESET_ERROR_EMPTY_PRIORITY'));
		}
		else
		{
			$state['discount_priority'] = $priority;
		}

		if ($state['discount_last_discount'] !== 'Y' || !$this->request->getPost('discount_last_discount'))
		{
			$state['discount_last_discount'] = 'N';
		}

		if ($state['discount_last_level_discount'] !== 'Y' || !$this->request->getPost('discount_last_level_discount'))
		{
			$state['discount_last_level_discount'] = 'N';
		}

		if (!$this->errorCollection->isEmpty())
		{
			return array($state, 'CommonSettings');
		}

		return array($state, $nextStep);
	}

	protected function getLabelDiscountValue()
	{
		return Loc::getMessage('SALE_BASE_PRESET_DISCOUNT_VALUE_LABEL');
	}

	protected function renderDiscountValue(State $state, $currency)
	{
		return '
			<tr>
				<td class="adm-detail-content-cell-l" style="width:40%;"><strong>' . $this->getLabelDiscountValue() . ':</strong></td>
				<td class="adm-detail-content-cell-r" style="width:60%;">
					<input type="text" name="discount_value" value="' . htmlspecialcharsbx($state->get('discount_value')) . '" maxlength="100" style="width: 100px;"> '
			. HtmlHelper::generateSelect('discount_type', array(
				'Perc' => Loc::getMessage('SHD_BT_SALE_ACT_GROUP_BASKET_SELECT_PERCENT'),
				'CurEach' => $currency,
			), $state->get('discount_type')) . '
				</td>
			</tr>
		';
	}

	protected function getTypeOfDiscount()
	{
		return static::ACTION_TYPE_DISCOUNT;
	}

	/**
	 * @return array
	 */
	protected function getAllowableUserGroups()
	{
		$groupList = [];

		if ($this->isRestrictedGroupsModeEnabled())
		{
			if (Main\Loader::includeModule('crm'))
			{
				foreach (BuyerGroup::getPublicList() as $group)
				{
					$groupList[$group['ID']] = $group['NAME'];
				}
			}
		}
		else
		{
			$groupIterator = Main\GroupTable::getList([
				'select' => [
					'ID',
					'NAME',
					'C_SORT',
				],
				'order' => [
					'C_SORT' => 'ASC',
					'ID' => 'ASC',
				],
			]);
			while ($group = $groupIterator->fetch())
			{
				$groupList[$group['ID']] = $group['NAME'];
			}
			unset(
				$group,
				$groupIterator,
			);
		}

		return $groupList;
	}

	protected function prepareDiscountFields(array $discount): array
	{
		$dateFields = [
			'ACTIVE_FROM',
			'ACTIVE_TO',
		];

		foreach ($dateFields as $fieldName)
		{
			if (
				isset($discount[$fieldName])
				&& $discount[$fieldName] instanceof Main\Type\DateTime
			)
			{
				$discount[$fieldName] = $discount[$fieldName]->toString();
			}
		}

		return $discount;
	}
}
