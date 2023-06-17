<?php

namespace Bitrix\Sale\Helpers\Admin\Blocks;

use Bitrix\Main\Entity\EntityError;
use Bitrix\Sale\Cashbox\CheckManager;
use Bitrix\Sale\Cashbox\Internals\CashboxTable;
use Bitrix\Sale\Exchange\Integration\Admin\Link;
use Bitrix\Sale\Exchange\Integration\Admin\Registry;
use Bitrix\Sale\Helpers\Admin\OrderEdit;
use Bitrix\Sale\Internals\PaySystemActionTable;
use Bitrix\Sale\OrderStatus;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\Services\Company;
use Bitrix\Sale\Services\PaySystem\Restrictions;
use Bitrix\Sale\Internals\CompanyTable;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Result;
use Bitrix\Sale;
use Bitrix\Main;
use Bitrix\Sale\UserMessageException;

Loc::loadMessages(__FILE__);

class OrderPayment
{
	/** @var $order \Bitrix\Sale\Order */
	private static $order = null;
	private static $defaultFields = null;

	private static function prepareData($item, $error = false)
	{
		/** @var $item \Bitrix\Sale\Payment */

		global $USER, $APPLICATION;
		static $users = array();
		static $userCompanyList = array();

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Sale\Order $orderClass */
		$orderClass = $registry->getOrderClassName();

		if (is_null(self::$order))
			self::$order = $item->getCollection()->getOrder();

		$fields = ($error) ? self::$defaultFields : $item->getFieldValues();

		$fields['EMP_PAID_ID_NAME'] = '';
		$fields['EMP_PAID_ID_LAST_NAME'] = '';

		$empPaidId = (int)($fields['EMP_PAID_ID'] ?? 0);
		if ($empPaidId > 0)
		{
			if (!array_key_exists($item->getField('EMP_PAID_ID'), $users))
				$users[$empPaidId] = $USER->GetByID($empPaidId)->Fetch();
			$fields['EMP_PAID_ID_NAME'] = $users[$empPaidId]['NAME'];
			$fields['EMP_PAID_ID_LAST_NAME'] = $users[$empPaidId]['LAST_NAME'];
		}

		$fields['ORDER_ID'] = self::$order->getId();
		$fields['SUM'] = $item->getSum();
		$fields['ORDER_PRICE'] = self::$order->getPrice();
		$fields['ORDER_PAYMENT_SUM'] = $item->getCollection()->getSum();
		$fields['CURRENCY'] = self::$order->getCurrency();
		$fields['PERSON_TYPE_ID'] = self::$order->getPersonTypeId();
		$fields['SITE_ID'] = self::$order->getSiteId();
		$fields['STATUS_ID'] = self::$order->getField('STATUS_ID');
		$fields['ORDER_LOCKED'] = $orderClass::isLocked($fields['ORDER_ID']);

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		if($saleModulePermissions == "P")
		{
			if (empty($userCompanyList))
			{
				$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());
			}

			$isUserResponsible = false;

			if (self::$order->getField('RESPONSIBLE_ID') == $USER->GetID()
				|| ($fields['RESPONSIBLE_ID'] == $USER->GetID()))
			{
				$isUserResponsible = true;
			}

			$isAllowCompany = in_array($item->getField('COMPANY_ID'), $userCompanyList) || in_array(self::$order->getField('COMPANY_ID'), $userCompanyList);


			if (!$isUserResponsible && !$isAllowCompany)
			{
				foreach ($fields as $fieldName => $fieldValue)
				{
					if (in_array($fieldName, static::getDisallowFields()))
					{
						unset($fields[$fieldName]);
					}
				}
			}

			$fields['IS_USER_RESPONSIBLE'] = $isUserResponsible;
			$fields['IS_ALLOW_COMPANY'] = $isAllowCompany;
		}

		$fields['PAY_SYSTEM_LIST'] = self::getPaySystemList($item);

		$fields['CHECK'] = CheckManager::getCheckInfo($item);

		$paySystemId = (int)($fields['PAY_SYSTEM_ID'] ?? 0);
		$fields['CAN_PRINT_CHECK'] = $fields['PAY_SYSTEM_LIST'][$paySystemId]['CAN_PRINT_CHECK'] ?? 'N';
		if (Sale\Cashbox\Manager::isEnabledPaySystemPrint())
		{
			$fields['CAN_PRINT_CHECK'] = 'N';
		}

		$dbRes = CashboxTable::getList(array('filter' => array('=ACTIVE' => 'Y', '=ENABLED' => 'Y')));
		$fields['HAS_ENABLED_CASHBOX'] = ($dbRes->fetch()) ? 'Y' : 'N';

		$service = $item->getPaySystem();
		$fields['HAS_PREVIEW'] = $service && $service->isAffordPdf();

		return $fields;
	}


	private static function getDisallowFields()
	{
		return array(
			'EMP_PAID_ID_NAME',
			'EMP_PAID_ID_LAST_NAME',
		);
	}

	/**
	 * @param $paySystemId
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	public static function getPaySystemParams($paySystemId)
	{
		static $result = array();

		if (!isset($result[$paySystemId]))
		{
			$data = array();
			if ($paySystemId > 0)
			{
				$data = PaySystemActionTable::getRow(array(
					'select' => array('NAME', 'LOGOTIP', 'HAVE_RESULT', 'RESULT_FILE', 'ACTION_FILE'),
					'filter' => array('ID' => $paySystemId),
				));
			}

			$result[$paySystemId] =  $data;

			$logotip = (int)($data["LOGOTIP"] ?? 0);
			if($logotip > 0)
			{
				$tmp = \CFile::ResizeImageGet($logotip, array('width' => 100, 'height' => 60));
				$result[$paySystemId]["LOGOTIP_PATH"] = $tmp['src'];
				$tmp = \CFile::ResizeImageGet($logotip, array('width' => 80, 'height' => 50));
				$result[$paySystemId]["LOGOTIP_SHORT_PATH"] = $tmp['src'];
			}
			else
			{
				$result[$paySystemId]["LOGOTIP_PATH"] = '/bitrix/images/sale/nopaysystem.gif';
				$result[$paySystemId]["LOGOTIP_SHORT_PATH"] = '/bitrix/images/sale/nopaysystem.gif';
			}
		}

		return $result[$paySystemId];
	}

	/**
	 * @param $payment
	 * @param $data
	 * @return mixed
	 */
	public static function modifyData($payment, $data)
	{
		/** @var \Bitrix\sale\Order $order */

		if (is_null(self::$order))
			self::$order = $payment->getCollection()->getOrder();

		if (!$data['ERROR'])
			$data['ERROR'] = 'Y';
		$data['ID'] = $data['PAYMENT_ID'];
		$data['CURRENCY'] = self::$order->getCurrency();
		$data['PERSON_TYPE_ID'] = self::$order->getPersonTypeId();
		$data['SITE_ID'] = self::$order->getSiteId();

		return $data;
	}

	/**
	 * @param \Bitrix\Sale\Payment $payment
	 * @param int $index
	 * @param $dataForRecovery
	 * @return string
	 */

	public static function getEdit($payment, $index = 1, $dataForRecovery = array())
	{
		global $USER, $APPLICATION;

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		$data = self::prepareData($payment, !empty($dataForRecovery));

		$data['COMPANIES'] = Company\Manager::getListWithRestrictions($payment, Company\Restrictions\Manager::MODE_MANAGER);

		$userCompanyId = null;
		if($saleModulePermissions == "P")
		{
			$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());
			if (!empty($userCompanyList) && is_array($userCompanyList) && count($userCompanyList) == 1)
			{
				$userCompanyId = reset($userCompanyList);
			}

			if ($payment->getId() == 0)
			{
				if (intval($userCompanyId) > 0)
				{
					$payment->setField('COMPANY_ID', $userCompanyId);
				}
				$payment->setField('RESPONSIBLE_ID', $USER->GetID());
			}
		}

		$result = self::getEditTemplate($data, $index, $dataForRecovery);

		return $result;
	}

	/**
	 * @param $payment
	 * @param int $index
	 * @param string $form
	 * @return string
	 */

	public static function getView($payment, $index = 1, $form='')
	{
		$data = self::prepareData($payment);
		return self::getViewTemplate($data, $index, $form);
	}

	/**
	 * @return string
	 */

	public static function getScripts()
	{
		Asset::getInstance()->addJs("/bitrix/js/sale/admin/order_payment.js");
		$imgPathList = self::getImgPathList();
		$message = array(
			'PAYMENT_PAID_NO' => Loc::getMessage('SALE_ORDER_PAYMENT_STATUS_NO'),
			'PAYMENT_PAID_YES' => Loc::getMessage('SALE_ORDER_PAYMENT_STATUS_YES'),
			'PAYMENT_PAID_RETURN' => Loc::getMessage('SALE_ORDER_PAYMENT_RETURN'),
			'PAYMENT_PAID_CANCEL' => Loc::getMessage('SALE_ORDER_PAYMENT_CANCEL'),
			'PAYMENT_RETURN_COMMENT' => Loc::getMessage('SALE_ORDER_PAYMENT_RETURN_COMMENT'),
			'PAYMENT_RETURN_NUM' => Loc::getMessage('SALE_ORDER_PAYMENT_RETURN_NUM'),
			'PAYMENT_RETURN_DATE' => Loc::getMessage('SALE_ORDER_PAYMENT_RETURN_DATE'),
			'PAYMENT_OPERATION_TITLE' => Loc::getMessage('SALE_ORDER_PAYMENT_OPERATION_TITLE'),
			'PAYMENT_OPERATION_RETURN' => Loc::getMessage('SALE_ORDER_PAYMENT_OPERATION_RETURN'),
			'PAYMENT_RETURN_DATE_ALT' => Loc::getMessage('SALE_ORDER_PAYMENT_RETURN_DATE_ALT'),
			'PAYMENT_WINDOW_RETURN_TITLE' => Loc::getMessage('SALE_ORDER_PAYMENT_WINDOW_RETURN_TITLE'),
			'PAYMENT_WINDOW_CANCEL_TITLE' => Loc::getMessage('SALE_ORDER_PAYMENT_WINDOW_CANCEL_TITLE'),
			'PAYMENT_WINDOW_RETURN_BUTTON_SAVE' => Loc::getMessage('SALE_ORDER_PAYMENT_WINDOW_RETURN_BUTTON_SAVE'),
			'PAYMENT_RETURN_NUM_DOC' => Loc::getMessage('SALE_ORDER_PAYMENT_RETURN_NUM_DOC'),
			'PAYMENT_RETURN_SUM' => Loc::getMessage('SALE_ORDER_PAYMENT_RETURN_SUM'),
			'PAYMENT_OPERATION_CANCEL' => Loc::getMessage('SALE_ORDER_PAYMENT_OPERATION_CANCEL'),
			'PAYMENT_TOGGLE_DOWN' => Loc::getMessage('SALE_ORDER_PAYMENT_TOGGLE_DOWN'),
			'PAYMENT_TOGGLE_UP' => Loc::getMessage('SALE_ORDER_PAYMENT_TOGGLE_UP'),
			'PAYMENT_PAY_VOUCHER_NUM' => Loc::getMessage('SALE_ORDER_PAYMENT_PAY_VOUCHER_NUM'),
			'PAYMENT_PAY_VOUCHER_DATE' => Loc::getMessage('SALE_ORDER_PAYMENT_PAY_VOUCHER_DATE'),
			'PAYMENT_WINDOW_VOUCHER_TITLE' => Loc::getMessage('SALE_ORDER_PAYMENT_WINDOW_VOUCHER_TITLE'),
			'PAYMENT_USE_INNER_BUDGET' => Loc::getMessage('SALE_ORDER_PAYMENT_USE_INNER_BUDGET'),
			'PAYMENT_ORDER_STATUS' => Loc::getMessage('SALE_ORDER_PAYMENT_ORDER_STATUS'),
			'PAYMENT_CONFIRM_DELETE' => Loc::getMessage('SALE_ORDER_PAYMENT_CONFIRM_DELETE'),
			'PAYMENT_CASHBOX_CHECK_ADD_WINDOW_TITLE' => Loc::getMessage('PAYMENT_CASHBOX_CHECK_ADD_WINDOW_TITLE')
		);
		return '<script type="text/javascript">
			BX.message('.\CUtil::PhpToJSObject($message).');
			logoList = '.\CUtil::PhpToJSObject($imgPathList).';

			BX.ready(function(){
				if(BX.Sale.Admin.OrderEditPage && BX.Sale.Admin.OrderEditPage.registerFieldsUpdaters)
					BX.Sale.Admin.OrderEditPage.registerFieldsUpdaters( BX.Sale.Admin.OrderPayment.prototype.getCreateOrderFieldsUpdaters() );
			});

		</script>';
	}

	private static function getEditTemplate($data, $index, $post = array())
	{
		global $USER, $APPLICATION;

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		$paid = ($post) ? htmlspecialcharsbx($post['PAID']) : $data['PAID'];
		$id = ($post) ? (int)$post['PAYMENT_ID'] : ($data['ID'] ?? 0);
		$priceCod = ($post) ? htmlspecialcharsbx($post['PRICE_COD']) : ($data['PRICE_COD'] ?? 0);
		$paidString = ($paid == 'Y') ? 'YES' : 'NO';
		if (!$post)
		{
			if ($data['SUM'] > 0)
				$sum = $data['SUM'];
			else
				$sum = ($data['ORDER_PRICE'] - $data['ORDER_PAYMENT_SUM'] <= 0) ? 0 : $data['ORDER_PRICE'] - $data['ORDER_PAYMENT_SUM'];
		}
		else
		{
			$sum = $post['SUM'];
		}

		$psData = self::getPaySystemParams(
			(isset($post['PAY_SYSTEM_ID'])) ? $post['PAY_SYSTEM_ID'] : ($data['PAY_SYSTEM_ID'] ?? 0)
		);

		if (isset($psData["LOGOTIP_PATH"]))
			$data['PAY_SYSTEM_LOGOTIP'] = $psData["LOGOTIP_PATH"];

		$allowedOrderStatusesPayment = OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('payment'));
		$isAllowPayment = in_array($data["STATUS_ID"], $allowedOrderStatusesPayment);
		$triangle = ($isAllowPayment) ? '<span class="triangle"> &#9662;</span>' : '';
		$class = ($paid != 'Y') ? 'notpay' : '';
		$class .= (!$isAllowPayment) ? ' not_active' : '';

		$paymentStatus = '<span><span id="BUTTON_PAID_'.$index.'" class="'.$class.'">'.Loc::getMessage('SALE_ORDER_PAYMENT_STATUS_'.$paidString).'</span>'.$triangle.'</span>';

		$note = BeginNote();
		$note .= Loc::getMessage('SALE_ORDER_PAYMENT_RETURN_ALERT');
		$note .= EndNote();

		$hiddenPaySystemInnerId = '';
		if ($index == 1)
			$hiddenPaySystemInnerId = '<input type="hidden" value="'.PaySystem\Manager::getInnerPaySystemId().'" id="PAYMENT_INNER_BUDGET_ID">';

		$notPaidBlock = ($paid == 'N' && !empty($data['EMP_PAID_ID'])) ? '' : 'style="display:none;"';

		$return = isset($post['IS_RETURN']) && $post['IS_RETURN'] === 'Y' ? '' : 'style="display:none;"';

		$option = '<option value="Y">'.Loc::getMessage('SALE_ORDER_PAYMENT_RETURN_ACCOUNT').'</option>';

		if (isset($data['PAY_SYSTEM_ID']) && $data['PAY_SYSTEM_ID'] != PaySystem\Manager::getInnerPaySystemId())
		{
			/** @var \Bitrix\Sale\PaySystem\Service $service */
			$service = PaySystem\Manager::getObjectById($data['PAY_SYSTEM_ID']);
			if ($service && $service->isRefundable())
				$option .= '<option value="P">'.htmlspecialcharsbx($service->getField('NAME')).'</option>';
		}

		$payReturnNum = $post['PAY_RETURN_NUM'] ?? $data['PAY_RETURN_NUM'] ?? '';
		$payReturnDate = $post['PAY_RETURN_DATE'] ?? $data['PAY_RETURN_DATE'] ?? '';
		$payReturnComment = $post['PAY_RETURN_COMMENT'] ?? $data['PAY_RETURN_COMMENT'] ?? '';

		$returnInformation = '
		<tr '.$return.' class="return">
			<td class="adm-detail-content-cell-l fwb">'.Loc::getMessage('SALE_ORDER_PAYMENT_RETURN_TO').':</td>
			<td class="adm-detail-content-cell-r">
				<select name="PAYMENT['.$index.'][OPERATION_ID]" id="OPERATION_ID_'.$index.'" class="adm-bus-select">
					'.$option.'
				</select>
			</td>
		</tr>
		<tr '.$return.' class="return">
			<td colspan="2" style="text-align: center">'.$note.'</td>
		</tr>
		<tr '.$notPaidBlock.' class="not_paid">
			<td class="adm-detail-content-cell-l" width="40%"><br>'.Loc::getMessage('SALE_ORDER_PAYMENT_PAY_RETURN_NUM').':</td>
			<td class="adm-detail-content-cell-r tal">
				<br>
				<input type="text" class="adm-bus-input" name="PAYMENT['.$index.'][PAY_RETURN_NUM]" id="PAYMENT_RETURN_NUM_'.$index.'" value="'.htmlspecialcharsbx($payReturnNum).'" maxlength="20">
			</td>
		</tr>
		<tr '.$notPaidBlock.' class="not_paid">
			<td class="adm-detail-content-cell-l" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PAY_RETURN_DATE').':</td>
			<td class="adm-detail-content-cell-r tal">
				<div class="adm-input-wrap adm-calendar-second" style="display: inline-block;">
					<input type="text" class="adm-input adm-calendar-to" name="PAYMENT['.$index.'][PAY_RETURN_DATE]" id="PAYMENT_RETURN_DATE_'.$index.'" size="15" value="'.htmlspecialcharsbx($payReturnDate).'">
					<span class="adm-calendar-icon" title="'.Loc::getMessage('SALE_ORDER_PAYMENT_CHOOSE_DATE').'" onclick="BX.calendar({node:this, field:\'PAYMENT_RETURN_DATE_'.$index.'\', form: \'\', bTime: false, bHideTime: false});"></span>
				</div>
			</td>
		</tr>
		<tr '.$notPaidBlock.' class="not_paid">
			<td class="adm-detail-content-cell-l" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_RETURN_COMMENT').':</td>
			<td class="adm-detail-content-cell-r tal">
				<div class="adm-input-wrap adm-calendar-second" style="display: inline-block;">
					<textarea name="PAYMENT['.$index.'][PAY_RETURN_COMMENT]" id="PAYMENT_RETURN_COMMENTS_'.$index.'">'.htmlspecialcharsbx($payReturnComment).'</textarea>
				</div>
			</td>
		</tr>';

		$lang = Main\Application::getInstance()->getContext()->getLanguage();

		if ($id > 0)
		{
			$dateBill = new Date($data['DATE_BILL']);
			$title = Loc::getMessage('SALE_ORDER_PAYMENT_BLOCK_EDIT_PAYMENT_TITLE', array('#ID#' => $data['ID'], '#DATE_BILL#' => $dateBill));
		}
		else
		{
			$title = Loc::getMessage('SALE_ORDER_PAYMENT_BLOCK_NEW_PAYMENT_TITLE');
		}

		$disabled = isset($data['PAID']) && $data['PAID'] === 'Y' ? 'readonly' : '';

		$companyList = $data['COMPANIES'];
		$companies = '';

		if (!empty($companyList))
		{
			if ($saleModulePermissions == "P")
			{
				$userCompanyId = null;
				$userCompanyList = Company\Manager::getUserCompanyList($USER->GetID());
				if (is_array($userCompanyList) && count($userCompanyList) == 1)
				{
					$userCompanyId = reset($userCompanyList);
					$companyName = $data['COMPANIES'][$userCompanyId]["NAME"]." [".$data['COMPANIES'][$userCompanyId]["ID"]."]";
					$companies = htmlspecialcharsbx($companyName);
				}
				else
				{
					foreach ($data['COMPANIES'] as $companyId => $companyData)
					{
						$foundCompany = false;

						if (!empty($userCompanyList) && is_array($userCompanyList))
						{
							foreach ($userCompanyList as $userCompanyId)
							{
								if ($userCompanyId == $companyId)
								{
									$foundCompany = true;
									break;
								}
							}
						}

						if (!$foundCompany)
						{
							unset($data['COMPANIES'][$companyId]);
						}
					}

					if (count($data['COMPANIES']) == 1)
					{
						$company = reset($data['COMPANIES']);
						$companies = htmlspecialcharsbx($company["NAME"]." [".$company["ID"]."]");
					}
				}
			}

			if (empty($companies))
			{
				$companies = OrderEdit::makeSelectHtmlWithRestricted(
					'PAYMENT['.$index.'][COMPANY_ID]',
					$companyList,
					isset($post["COMPANY_ID"]) ? $post["COMPANY_ID"] : $data["COMPANY_ID"],
					true,
					array(
						"class" => "adm-bus-select",
						"id" => "PAYMENT_COMPANY_ID_".$index
					)
				);
			}
		}
		else
		{
			if ($saleModulePermissions >= "W")
			{
				$companies = str_replace("#URL#", "/bitrix/admin/sale_company_edit.php?lang=".$lang, Loc::getMessage('SALE_ORDER_PAYMENT_ADD_COMPANY'));
			}
		}

		$checkLink = '';
		$checkLink .= '<tr><td class="tac" id="PAYMENT_CHECK_LIST_ID_'.($data['ID'] ?? 0).'">';
		if (!empty($data['CHECK']))
		{
			$checkLink .= static::buildCheckHtml($data['CHECK']);
		}
		$checkLink .= '</td></tr>';
		if ($data['CAN_PRINT_CHECK'] == 'Y' && $data['HAS_ENABLED_CASHBOX'] === 'Y')
		{
			$checkLink .= '<tr><td class="adm-detail-content-cell-r tac"><a href="javascript:void(0);" onclick="BX.Sale.Admin.OrderPayment.prototype.showCreateCheckWindow('.$data['ID'].');">'.Loc::getMessage('SALE_ORDER_PAYMENT_CHECK_ADD').'</a></td></tr>';
		}

		$isReturn = $post['IS_RETURN'] ?? 'N';
		$paySystemId = $post['PAY_SYSTEM_ID'] ?? $data['PAY_SYSTEM_ID'] ?? 0;
		$payVoucherNum = $post['PAY_VOUCHER_NUM'] ?? $data['PAY_VOUCHER_NUM'] ?? '';
		$payVoucherDate = $post['PAY_VOUCHER_DATE'] ?? $data['PAY_VOUCHER_DATE'] ?? '';

		$data['DATE_PAID'] ??= '';
		$data['EMP_PAID_ID'] ??= '';
		$data['EMP_PAID_ID_NAME'] ??= '';
		$data['EMP_PAID_ID_LAST_NAME'] ??= '';

		$result = '<div>
			<div class="adm-bus-pay" id="payment_container_'.$index.'">
				<input type="hidden" name="PAYMENT['.$index.'][PAYMENT_ID]" id="payment_id_'.$index.'" value="'.$id.'">
				<input type="hidden" name="PAYMENT['.$index.'][INDEX]" value="'.$index.'" class="index">
				<input type="hidden" name="PAYMENT['.$index.'][PAID]" id="PAYMENT_PAID_'.$index.'" value="'.(empty($paid) ? 'N' : $paid).'">
				<input type="hidden" name="PAYMENT['.$index.'][IS_RETURN]" id="PAYMENT_IS_RETURN_'.$index.'" value="'.(htmlspecialcharsbx($isReturn)).'">
				<input type="hidden" name="PAYMENT['.$index.'][IS_RETURN_CHANGED]" id="PAYMENT_IS_RETURN_CHANGED_'.$index.'" value="N">
				'.$hiddenPaySystemInnerId.'
				<div class="adm-bus-component-content-container">
					<div class="adm-bus-pay-section">
						<div class="adm-bus-pay-section-title-container">
							<div class="adm-bus-pay-section-title">'.$title.'</div>
							<div class="adm-bus-pay-section-action-block">'.
								((!isset($data['ID']) || $data['ID'] <= 0) ? '<div class="adm-bus-pay-section-action" id="SECTION_'.$index.'_DELETE">'.Loc::getMessage('SALE_ORDER_PAYMENT_DELETE').'</div>' : '')
							.'</div>
						</div>
						<div class="adm-bus-pay-section-content" id="SECTION_'.$index.'">
							<div class="adm-bus-pay-section-sidebar">
								<div style="background: url(\''.$data['PAY_SYSTEM_LOGOTIP'].'\')" class="adm-shipment-block-logo" id="LOGOTIP_'.$index.'"></div>
							</div>
							<div class="adm-bus-pay-section-right">
								<div class="adm-bus-table-container caption border">
									<div class="adm-bus-table-caption-title" style="background: #eef5f5;">'.Loc::getMessage('SALE_ORDER_PAYMENT_METHOD').'</div>
									<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table ">
										<tbody>
											<tr>
												<td class="adm-detail-content-cell-l fwb" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PAY_SYSTEM').':</td>
												<td class="adm-detail-content-cell-r">'.
												OrderEdit::makeSelectHtmlWithRestricted(
													'PAYMENT['.$index.'][PAY_SYSTEM_ID]',
													$data['PAY_SYSTEM_LIST'],
													$paySystemId,
													false,
													array(
														"class" => "adm-bus-select",
														"id" => "PAY_SYSTEM_ID_".$index
													)
												)
												.'</td>
											</tr>
										</tbody>
									</table>
								</div>
								<div class="adm-bus-table-container caption border">
									<div class="adm-bus-table-caption-title" style="background: #eef5f5;">'.Loc::getMessage('SALE_ORDER_PAYMENT_SUM').'</div>
									<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table ">
										<tbody>
											<tr>
												<td class="adm-detail-content-cell-l" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PAYABLE_SUM').':</td>
												<td class="adm-detail-content-cell-r tal">'
												. \CCurrencyLang::getPriceControl(
													'<input type="text" class="adm-bus-input-price" name="PAYMENT['.$index.'][SUM]" id="PAYMENT_SUM_'.$index.'" value="'.round($sum, 2).'" '.$disabled.'>',
													$data['CURRENCY']
												)
												. '<br></td>
											</tr>
											<tr '.($priceCod > 0 ?: 'style="display: none"').'>
												<td class="adm-detail-content-cell-l" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PAYABLE_PRICE_COD').':</td>
												<td class="adm-detail-content-cell-r tal">'
												. \CCurrencyLang::getPriceControl(
													'<input type="text" class="adm-bus-input-price" name="PAYMENT['.$index.'][PRICE_COD]" id="PAYMENT_PRICE_COD_'.$index.'" value="'.round($priceCod, 2).'" readonly>',
													$data['CURRENCY']
												)
												. '<br></td>
											</tr>
										</tbody>
									</table>
								</div>
								<div class="adm-bus-table-container caption border" style="padding-top:10px;">
									<div class="adm-bus-table-caption-title" style="background: #eef5f5;">'.Loc::getMessage('SALE_ORDER_PAYMENT_STATUS').'</div>
									<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table alternation edit-table" id="PAYMENT_BLOCK_STATUS_'.$index.'">
										<tbody>
											<tr>
												<td class="adm-detail-content-cell-l vat payment-status" width="40%">
													'.$paymentStatus.'
												</td>
												<td class="adm-detail-content-cell-r tal" id="PAYMENT_CHANGE_USER_INFO_'.$index.'">
													'.$data['DATE_PAID'].'
													<a href="/bitrix/admin/user_edit.php?lang='.$lang.'&ID='.$data['EMP_PAID_ID'].'">'.htmlspecialcharsbx($data['EMP_PAID_ID_NAME']).' '.htmlspecialcharsbx($data['EMP_PAID_ID_LAST_NAME']).'</a>
												</td>
											</tr>
										</tbody>
									</table>
									<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table" id="PAYMENT_BLOCK_STATUS_INFO_'.$index.'">
										<tbody>
											<tr>
												<td class="adm-detail-content-cell-l" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PAY_VOUCHER_NUM').':</td>
												<td class="adm-detail-content-cell-r tal">
													<input type="text" class="adm-bus-input" id="PAYMENT_NUM" name="PAYMENT['.$index.'][PAY_VOUCHER_NUM]" value="'.htmlspecialcharsbx($payVoucherNum).'" maxlength="20">
												</td>
											</tr>
											<tr>
												<td class="adm-detail-content-cell-l" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PAY_VOUCHER_DATE').':</td>
												<td class="adm-detail-content-cell-r tal">
													<div class="adm-input-wrap adm-calendar-second" style="display: inline-block;">
														<input type="text" class="adm-input adm-calendar-to" id="PAYMENT_DATE_'.$index.'" name="PAYMENT['.$index.'][PAY_VOUCHER_DATE]" size="15" value="'.htmlspecialcharsbx($payVoucherDate).'">
														<span class="adm-calendar-icon" title="'.Loc::getMessage('SALE_ORDER_PAYMENT_CHOOSE_DATE').'" onclick="BX.calendar({node:this, field:\'PAYMENT_DATE_'.$index.'\', form: \'\', bTime: false, bHideTime: false});"></span>
													</div>
												</td>
											</tr>
											'.$returnInformation.'
										</tbody>
									</table>
								</div>';
		if ($data['CAN_PRINT_CHECK'] == 'Y' && $data['ID'] > 0)
		{
			$result .= '<div class="adm-bus-table-container caption border" style="padding-top:10px;">
						<div class="adm-bus-table-caption-title" style="background: #eef5f5;">'.Loc::getMessage('SALE_ORDER_PAYMENT_CHECK_LINK_TITLE').'</div>
						<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table">
							<tbody>
							'.$checkLink.'
							</tbody>
						</table>
					</div>';
		}

		if (isset($data['PS_STATUS']) && !empty($data['PS_STATUS']))
		{
			$result .= '<div class="adm-bus-table-container caption border">
									<div class="adm-bus-table-caption-title" style="background: #eef5f5;">'.Loc::getMessage('SALE_ORDER_PAYMENT_PS_STATUS_TITLE').'</div>
									<a href="javascript:void(0);" id="PS_INFO_'.$index.'">'.Loc::getMessage('SALE_ORDER_PAYMENT_TOGGLE_DOWN').'</a>
									<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table" style="display: none">
										<tbody>
										<tr>
											<td class="adm-detail-content-cell-l vat" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PS_STATUS').':</td>
											<td class="adm-detail-content-cell-r tal">'.$data['PS_STATUS'].'</td>
										</tr>
										<tr>
											<td class="adm-detail-content-cell-l vat" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PS_STATUS_CODE').':</td>
											<td class="adm-detail-content-cell-r tal">'.$data['PS_STATUS_CODE'].'</td>
										</tr>
										<tr>
											<td class="adm-detail-content-cell-l vat" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PS_STATUS_DESCRIPTION').':</td>
											<td class="adm-detail-content-cell-r tal">'.$data['PS_STATUS_DESCRIPTION'].'</td>
										</tr>
										<tr>
											<td class="adm-detail-content-cell-l vat" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PS_CURRENCY').':</td>
											<td class="adm-detail-content-cell-r tal">'.$data['PS_CURRENCY'].'</td>
										</tr>
										<tr>
											<td class="adm-detail-content-cell-l vat" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PS_STATUS_MESSAGE').':</td>
											<td class="adm-detail-content-cell-r tal">'.$data['PS_STATUS_MESSAGE'].'</td>
										</tr>
										<tr>
											<td class="adm-detail-content-cell-l vat" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PS_SUM').':</td>
											<td class="adm-detail-content-cell-r tal">'.$data['PS_SUM'].'</td>
										</tr>
										<tr>
											<td class="adm-detail-content-cell-l vat" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PS_DATE').':</td>
											<td class="adm-detail-content-cell-r tal">'.$data['PS_RESPONSE_DATE'].'</td>
										</tr>
										</tbody>
									</table>
								</div>';
		}
		if ($companies !== '')
		{
			$result .= '<div class="adm-bus-table-container caption border">
							<div class="adm-bus-table-caption-title" style="background: #eef5f5;">'.Loc::getMessage('SALE_ORDER_PAYMENT_BLOCK_COMPANY').'</div>
							<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table ">
								<tbody>
									<tr>
										<td class="adm-detail-content-cell-l" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_COMPANY_BY').':</td>
										<td class="adm-detail-content-cell-r">'.$companies.'</td>
									</tr>
								</tbody>
							</table>
						</div>';
		}
		$result .= '</div><div class="clb"></div></div></div></div></div></div>';

		$refundablePs = array();
		if (isset($data['ID']) && $data['ID'] > 0)
		{
			$innerService = PaySystem\Manager::getObjectById(PaySystem\Manager::getInnerPaySystemId());
			$refundablePs[Payment::RETURN_INNER] = $innerService->getField('NAME');
			if ($data['PAY_SYSTEM_ID'] != $innerService->getField('ID'))
			{
				$service = PaySystem\Manager::getObjectById($data['PAY_SYSTEM_ID']);
				if ($service && $service->isRefundable())
					$refundablePs[Payment::RETURN_PS] = $service->getField('NAME');
			}
		}

		$params = array(
			'index' => $index,
			'functionOnSave' => 'saveInHiddenFields',
			'isPaid' => isset($data['PAID']) && $data['PAID'] === 'Y',
			'viewForm' => false,
			'isAvailableChangeStatus' => $isAllowPayment,
			'psToReturn' => $refundablePs
		);
		$result .= self::initJsPayment($params);
		return $result;
	}

	private static function getViewTemplate($data, $index, $form)
	{
		global $USER;

		$isUserResponsible = null;
		$isAllowCompany = null;
		$link = Link::getInstance();

		if (array_key_exists('IS_USER_RESPONSIBLE', $data))
		{
			$isUserResponsible = $data['IS_USER_RESPONSIBLE'];
		}

		if (array_key_exists('IS_ALLOW_COMPANY', $data))
		{
			$isAllowCompany = $data['IS_ALLOW_COMPANY'];
		}

		$psData = self::getPaySystemParams($data['PAY_SYSTEM_ID']);

		if ($isAllowCompany === false)
		{
			$data['PAY_VOUCHER_NUM'] = '';
			$data['PAY_VOUCHER_DATE'] = '';
			$data['PAY_RETURN_NUM'] = '';
			$data['PAY_RETURN_DATE'] = '';
			$data['PS_STATUS'] = '';
		}
		else
		{
			if (isset($psData["LOGOTIP_PATH"]))
			{
				$data['PAY_SYSTEM_LOGOTIP'] = $psData["LOGOTIP_PATH"];
				$data['PAY_SYSTEM_LOGOTIP_SHORT'] = $psData["LOGOTIP_SHORT_PATH"];
			}
		}

		$psResult = '';
		if ($psData['HAVE_RESULT'] == 'Y' && $form != 'edit')
		{
			$psResult .= '&nbsp;&nbsp;&nbsp;<span style="border-bottom: 1px dashed #658d0f; color: #658d0f; cursor: pointer" id="ps_update_'.$index.'">'.Loc::getMessage('SALE_ORDER_PAYMENT_PAY_SYSTEM_CHECK').'</span>';
		}

		$lang = Main\Application::getInstance()->getContext()->getLanguage();
		$paidString = ($data['PAID'] == 'Y') ? 'YES' : 'NO';

		$allowedOrderStatusesPayment = OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('payment'));
		$isAllowPayment = in_array($data["STATUS_ID"], $allowedOrderStatusesPayment);

		$isActive = ($form != 'edit' && $form != 'archive') && !$data['ORDER_LOCKED'] && $isAllowPayment;
		$triangle = ($isActive) ? '<span class="triangle"> &#9662;</span>' : '';

		if ($data['PAID'] == 'Y')
			$class = (!$isActive) ? 'class="not_active"' : '';
		else
			$class = (!$isActive) ? 'class="notpay not_active"' : 'class="notpay"';
		$paymentStatus = '<span><span id="BUTTON_PAID_'.$index.'" '.$class.'>'.Loc::getMessage('SALE_ORDER_PAYMENT_STATUS_'.$paidString).'</span>'.$triangle.'</span>';

		$res = CompanyTable::getList(array(
			'select' => array('NAME'),
			'filter' => array('ID' => $data['COMPANY_ID'])
		));
		$company = $res->fetch();

		$paymentStatusBlockVoucherNum = '';
		if ($data['PAY_VOUCHER_NUM'] <> '')
		{
			$paymentStatusBlockVoucherNum = '<tr>
										<td class="adm-detail-content-cell-l" width="40%"><br>'.Loc::getMessage('SALE_ORDER_PAYMENT_PAY_VOUCHER_NUM').':</td>
										<td class="adm-detail-content-cell-r tal">
											<br>
											'.htmlspecialcharsbx($data['PAY_VOUCHER_NUM']).'
										</td>
									</tr>';
		}

		$paymentStatusBlockVoucherDate = '';
		if ($data['PAY_VOUCHER_DATE'] <> '')
		{
			$paymentStatusBlockVoucherDate = '<tr>
												<td class="adm-detail-content-cell-l" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PAY_VOUCHER_DATE').':</td>
												<td class="adm-detail-content-cell-r tal">
													<div class="adm-input-wrap adm-calendar-second" style="display: inline-block;">'.htmlspecialcharsbx($data['PAY_VOUCHER_DATE']).'</div>
												</td>
											</tr>';
		}

		$paymentStatusBlockReturnNum = '';
		if ($data['PAY_RETURN_NUM'] <> '')
		{
			$paymentStatusBlockReturnNum = '<tr>
			<td class="adm-detail-content-cell-l" width="40%"><br>'.Loc::getMessage('SALE_ORDER_PAYMENT_PAY_RETURN_NUM').':</td>
			<td class="adm-detail-content-cell-r tal">
				<br>'.htmlspecialcharsbx($data['PAY_RETURN_NUM']).'</td>
			</tr>';
		}

		$paymentStatusBlockReturnDate = '';
		if ($data['PAY_RETURN_DATE'] <> '')
		{
			$paymentStatusBlockReturnDate = '<tr>
				<td class="adm-detail-content-cell-l" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PAY_RETURN_DATE').':</td>
				<td class="adm-detail-content-cell-r tal">
					<div class="adm-input-wrap adm-calendar-second" style="display: inline-block;">'.htmlspecialcharsbx($data['PAY_RETURN_DATE']).'</div>
				</td>
			</tr>';
		}

		$dateBill = new Date($data['DATE_BILL']);

		$allowedOrderStatusesEdit = OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('update'));
		$isAllowEdit = in_array($data["STATUS_ID"], $allowedOrderStatusesEdit) && $form != 'archive';
		$sectionEdit = '';
		$href = $link
			->create()
			->setPageByType(Registry::SALE_ORDER_PAYMENT_EDIT)
			->setFilterParams(false)
			->fill()
			->setField('order_id', $data['ORDER_ID'])
			->setField('payment_id', $data['ID'])
			->setField('backurl', $_SERVER['REQUEST_URI'])
			->build();

		if ($isAllowEdit && !$data['ORDER_LOCKED'])
			$sectionEdit = '<div class="adm-bus-pay-section-action" id="SECTION_'.$index.'_EDIT"><a href="'.$href.'">'.Loc::getMessage('SALE_ORDER_PAYMENT_EDIT').'</a></div>';

		$allowedOrderStatusesDelete = OrderStatus::getStatusesUserCanDoOperations($USER->GetID(), array('delete'));
		$isAllowDelete = in_array($data["STATUS_ID"], $allowedOrderStatusesDelete) && $form != 'archive';
		$sectionDelete = '';
		if ($isAllowDelete && !$data['ORDER_LOCKED'])
			$sectionDelete = '<div class="adm-bus-pay-section-action" id="SECTION_'.$index.'_DELETE">'.Loc::getMessage('SALE_ORDER_PAYMENT_DELETE').'</div>';

		$checkLink = '<tr><td class="tac" id="PAYMENT_CHECK_LIST_ID_'.$data['ID'].'">';
		if (!empty($data['CHECK']))
		{
			$checkLink .= static::buildCheckHtml($data['CHECK']);
		}
		$checkLink .= "</td></tr>";

		if($form != 'archive' && $data['CAN_PRINT_CHECK'] == 'Y' && $data['HAS_ENABLED_CASHBOX'] === 'Y')
		{
			$checkLink .= '<tr><td class="adm-detail-content-cell-r tac"><a href="javascript:void(0);" onclick="BX.Sale.Admin.OrderPayment.prototype.showCreateCheckWindow('.$data['ID'].');">'.Loc::getMessage('SALE_ORDER_PAYMENT_CHECK_ADD').'</a></td></tr>';
		}

		if ($isAllowCompany === false && $isUserResponsible === false)
		{
			$psName = Loc::getMessage('SALE_ORDER_PAYMENT_HIDDEN');
		}
		else
		{
			$psName = htmlspecialcharsbx($data['PAY_SYSTEM_NAME']).' ['.$data['PAY_SYSTEM_ID'].'] '.$psResult;
			if ($data['HAS_PREVIEW'])
			{
				$psName .= " (<a target='_blank' href='/bitrix/admin/sale_payment_doc_preview.php?PAYMENT_ID={$data['ID']}&ORDER_ID={$data['ORDER_ID']}'>".Loc::getMessage('SALE_ORDER_PAYMENT_DOC_PREVIEW')."</a>)";
			}
		}

		$result = '
		<div>
			<div class="adm-bus-pay" id="payment_container_'.$index.'">
				<input type="hidden" name="PAYMENT['.$index.'][PAYMENT_ID]" id="PAYMENT_ID_'.$index.'" value="'.$data['ID'].'">
				<div class="adm-bus-component-content-container">
					<div class="adm-bus-pay-section">
						<div class="adm-bus-pay-section-title-container">
							<div class="adm-bus-pay-section-title" id="payment_'.$data['ID'].'">'.$tabTitle = Loc::getMessage('SALE_ORDER_PAYMENT_BLOCK_EDIT_PAYMENT_TITLE', array('#ID#' => $data['ID'], '#DATE_BILL#' => $dateBill)).'</div>
							<div class="adm-bus-pay-section-action-block">
								'.$sectionDelete.$sectionEdit.'
								<div class="adm-bus-pay-section-action" id="SECTION_'.$index.'_TOGGLE">'.Loc::getMessage('SALE_ORDER_PAYMENT_TOGGLE_DOWN').'</div>
							</div>
						</div>
						<div class="adm-bus-pay-section-content" id="SECTION_'.$index.'"  style="display:none">
							<div class="adm-bus-pay-section-sidebar">
								<div style="background: url(\''.$data['PAY_SYSTEM_LOGOTIP'].'\')" class="adm-shipment-block-logo" id="LOGOTIP_'.$index.'"></div>
							</div>
							<div class="adm-bus-pay-section-right">
								<div class="adm-bus-table-container caption border">
									<div class="adm-bus-table-caption-title" style="background: #eef5f5;">'.Loc::getMessage('SALE_ORDER_PAYMENT_METHOD').'</div>
									<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table ">
										<tbody>
											<tr>
												<td class="adm-detail-content-cell-l" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PAY_SYSTEM').':</td>
												<td class="adm-detail-content-cell-r">'.$psName.'</td>
											</tr>
										</tbody>
									</table>
								</div>
								<div class="adm-bus-table-container caption border">
									<div class="adm-bus-table-caption-title" style="background: #eef5f5;">'.Loc::getMessage('SALE_ORDER_PAYMENT_SUM').'</div>
									<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table ">
										<tbody>
											<tr>
												<td class="adm-detail-content-cell-l" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PAYABLE_SUM').':</td>
												<td class="adm-detail-content-cell-r tal">'.SaleFormatCurrency($data['SUM'], $data['CURRENCY']).'<br></td>
											</tr>
											<tr '.($data['PRICE_COD'] > 0 ?: 'style="display: none"').'>
												<td class="adm-detail-content-cell-l" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PAYABLE_PRICE_COD').':</td>
												<td class="adm-detail-content-cell-r tal" id="PAYMENT_PRICE_COD_'.$index.'">'.SaleFormatCurrency($data['PRICE_COD'], $data['CURRENCY']).'</td>
											</tr>
										</tbody>
									</table>
								</div>
								<div class="adm-bus-table-container caption border" style="padding-top:10px;">
									<div class="adm-bus-table-caption-title" style="background: #eef5f5;">'.Loc::getMessage('SALE_ORDER_PAYMENT_STATUS').'</div>
									<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table alternation edit-table" id="PAYMENT_BLOCK_STATUS_'.$index.'">
										<tbody>
											<tr>
												<td class="adm-detail-content-cell-l vat payment-status" width="40%">
													'.$paymentStatus.'
												</td>
												<td class="adm-detail-content-cell-r tal" id="PAYMENT_CHANGE_USER_INFO_'.$index.'">
													'.$data['DATE_PAID'].'
													<a href="/bitrix/admin/user_edit.php?lang='.$lang.'&ID='.$data['EMP_PAID_ID'].'">'.htmlspecialcharsbx($data['EMP_PAID_ID_NAME']).' '.htmlspecialcharsbx($data['EMP_PAID_ID_LAST_NAME']).'</a>
												</td>
											</tr>
										</tbody>
									</table>
									<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table">
										<tbody>
										'.$paymentStatusBlockVoucherNum.$paymentStatusBlockVoucherDate.$paymentStatusBlockReturnNum.$paymentStatusBlockReturnDate.'
										</tbody>
									</table>
								</div>';
		if ($checkLink)
		{
			$result .= '<div class="adm-bus-table-container caption border" style="padding-top:10px;">
						<div class="adm-bus-table-caption-title" style="background: #eef5f5;">'.Loc::getMessage('SALE_ORDER_PAYMENT_CHECK_LINK_TITLE').'</div>
						<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table">
							<tbody>
							'.$checkLink.'
							</tbody>
						</table>
					</div>';
		}

		if (isset($data['PS_STATUS']) && !empty($data['PS_STATUS']))
		{
			$result .= '<div class="adm-bus-table-container caption border">
									<div class="adm-bus-table-caption-title" style="background: #eef5f5;">'.Loc::getMessage('SALE_ORDER_PAYMENT_PS_STATUS_TITLE').'</div>
									<a href="javascript:void(0);" id="PS_INFO_'.$index.'">'.Loc::getMessage('SALE_ORDER_PAYMENT_TOGGLE_UP').'</a>
									<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table">
										<tbody>
										<tr>
											<td class="adm-detail-content-cell-l vat" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PS_STATUS').':</td>
											<td class="adm-detail-content-cell-r tal">'.htmlspecialcharsbx($data['PS_STATUS']).'</td>
										</tr>
										<tr>
											<td class="adm-detail-content-cell-l vat" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PS_STATUS_CODE').':</td>
											<td class="adm-detail-content-cell-r tal">'.htmlspecialcharsbx($data['PS_STATUS_CODE']).'</td>
										</tr>
										<tr>
											<td class="adm-detail-content-cell-l vat" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PS_STATUS_DESCRIPTION').':</td>
											<td class="adm-detail-content-cell-r tal">'.htmlspecialcharsbx($data['PS_STATUS_DESCRIPTION']).'</td>
										</tr>
										<tr>
											<td class="adm-detail-content-cell-l vat" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PS_CURRENCY').':</td>
											<td class="adm-detail-content-cell-r tal">'.htmlspecialcharsbx($data['PS_CURRENCY']).'</td>
										</tr>
										<tr>
											<td class="adm-detail-content-cell-l vat" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PS_STATUS_MESSAGE').':</td>
											<td class="adm-detail-content-cell-r tal">'.htmlspecialcharsbx($data['PS_STATUS_MESSAGE']).'</td>
										</tr>
										<tr>
											<td class="adm-detail-content-cell-l vat" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PS_SUM').':</td>
											<td class="adm-detail-content-cell-r tal">'.htmlspecialcharsbx($data['PS_SUM']).'</td>
										</tr>
										<tr>
											<td class="adm-detail-content-cell-l vat" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_PS_DATE').':</td>
											<td class="adm-detail-content-cell-r tal">'.htmlspecialcharsbx($data['PS_RESPONSE_DATE']).'</td>
										</tr>
										</tbody>
									</table>
								</div>';
		}
		$result .= '<div class="adm-bus-table-container caption border">
									<div class="adm-bus-table-caption-title" style="background: #eef5f5;">'.Loc::getMessage('SALE_ORDER_PAYMENT_BLOCK_COMPANY').'</div>
									<table border="0" cellspacing="0" cellpadding="0" width="100%" class="adm-detail-content-table edit-table ">
										<tbody>
											<tr>
												<td class="adm-detail-content-cell-l" width="40%">'.Loc::getMessage('SALE_ORDER_PAYMENT_COMPANY_BY').':</td>
												<td class="adm-detail-content-cell-r">'.
													($isAllowCompany === false && $isUserResponsible === false ? Loc::getMessage('SALE_ORDER_PAYMENT_HIDDEN') :
													(isset($company['NAME']) && !empty($company['NAME']) ? htmlspecialcharsbx($company['NAME']).' ['.$data['COMPANY_ID'].']' : Loc::getMessage('SALE_ORDER_PAYMENT_NO_COMPANY')))

												.'</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
							<div class="clb"></div>
						</div>
						'.self::getShortViewTemplate($data, $index, $form).'
					</div>
				</div>
			</div>
		</div>';

		$innerService = PaySystem\Manager::getObjectById(PaySystem\Manager::getInnerPaySystemId());
		if ($innerService)
		{
			$refundablePs = array(Payment::RETURN_INNER => $innerService->getField('NAME'));

			if ($data['PAY_SYSTEM_ID'] != $innerService->getField('ID'))
			{
				$service = PaySystem\Manager::getObjectById($data['PAY_SYSTEM_ID']);
				if ($service && $service->isRefundable())
					$refundablePs[Payment::RETURN_PS] = $service->getField('NAME');
			}
		}
		else
		{
			$refundablePs = array();
		}

		$params = array(
			'index' => $index,
			'functionOnSave' => 'sendAjax',
			'viewForm' => true,
			'isPaid' => ($data['PAID'] == 'Y'),
			'isAvailableChangeStatus' => $isActive,
			'psToReturn' => $refundablePs
		);
		$result .= self::initJsPayment($params);
		return $result;
	}

	private static function getShortViewTemplate($data, $index, $form)
	{
		global $USER;
		$lang = Main\Application::getInstance()->getContext()->getLanguage();
		$paidString = ($data['PAID'] == 'Y') ? 'YES' : 'NO';

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

		$isUserResponsible = null;
		$isAllowCompany = null;

		if (array_key_exists('IS_USER_RESPONSIBLE', $data))
		{
			$isUserResponsible = $data['IS_USER_RESPONSIBLE'];
		}

		if (array_key_exists('IS_ALLOW_COMPANY', $data))
		{
			$isAllowCompany = $data['IS_ALLOW_COMPANY'];
		}

		/** @var Sale\OrderStatus $orderStatusClass */
		$orderStatusClass = $registry->getOrderStatusClassName();

		$allowedOrderStatusesPayment = $orderStatusClass::getStatusesUserCanDoOperations($USER->GetID(), array('payment'));
		$isAllowPayment = in_array($data["STATUS_ID"], $allowedOrderStatusesPayment);

		$isActive = ($form != 'edit' && $form != 'archive') && !$data['ORDER_LOCKED'] && $isAllowPayment;
		$triangle = ($isActive) ? '<span class="triangle"> &#9662;</span>' : '';

		if ($data['PAID'] == 'Y')
			$class = (!$isActive) ? 'class="not_active"' : '';
		else
			$class = (!$isActive) ? 'class="notpay not_active"' : 'class="notpay"';
		$paymentStatus = '<span><span id="BUTTON_PAID_'.$index.'_SHORT" '.$class.'>'.Loc::getMessage('SALE_ORDER_PAYMENT_STATUS_'.$paidString).'</span>'.$triangle.'</span>';

		$checkLink = '';
		if (($data['CAN_PRINT_CHECK'] == 'Y' && $form != 'archive' && $data['HAS_ENABLED_CASHBOX'] === 'Y') || !empty($data['CHECK']))
		{
			$checkLink = '<td width="80px">'.Loc::getMessage('SALE_ORDER_PAYMENT_CHECK_LINK_TITLE').':</td><td>';
			$checkLink .= '<div id="PAYMENT_CHECK_LIST_ID_SHORT_VIEW'.$data['ID'].'">';
			if (!empty($data['CHECK']))
			{
				$checkLink .= static::buildCheckHtml($data['CHECK']);
			}
			$checkLink .= "</div>";
			if ($form != 'archive' && $data['CAN_PRINT_CHECK'] == 'Y' && $data['HAS_ENABLED_CASHBOX'] === 'Y')
			{
				$checkLink .= '<div><a href="javascript:void(0);" onclick="BX.Sale.Admin.OrderPayment.prototype.showCreateCheckWindow('.$data['ID'].');">'.Loc::getMessage('SALE_ORDER_PAYMENT_CHECK_ADD').'</a></div>';
			}
			$checkLink .='</td>';
		}

		if ($isAllowCompany === false && $isUserResponsible === false)
		{
			$psName = Loc::getMessage('SALE_ORDER_PAYMENT_HIDDEN');
		}
		else
		{
			$psName = htmlspecialcharsbx($data['PAY_SYSTEM_NAME']);

			if ($data['HAS_PREVIEW'])
			{
				$psName .= " (<a target='_blank' href='/bitrix/admin/sale_payment_doc_preview.php?PAYMENT_ID={$data['ID']}&ORDER_ID={$data['ORDER_ID']}'>".Loc::getMessage('SALE_ORDER_PAYMENT_DOC_PREVIEW')."</a>)";
			}
		}

		$result = '
			<div class="adm-bus-section-container-section-content" style="padding: 10px;" id="SECTION_SHORT_'.$index.'">
				<table class="adm-bus-section-container-section-status" id="PAYMENT_BLOCK_STATUS_'.$index.'">
					<tr>
						<td>
							<div style="background: url(\''.$data['PAY_SYSTEM_LOGOTIP_SHORT'].'\');" class="adm-shipment-block-short-logo" id="LOGOTIP_SHORT_'.$index.'"></div>
						</td>
						<td class="adm-bus-section-container-section-status-service">'.Loc::getMessage('SALE_ORDER_PAYMENT_PAY_SYSTEM').': '.$psName.'</td>
						<td class="adm-bus-section-container-section-status-summ">'.Loc::getMessage('SALE_ORDER_PAYMENT_PAYABLE_SUM').': '.SaleFormatCurrency($data['SUM'], $data['CURRENCY']).'</td>
						<td class="adm-bus-section-container-section-status-status payment-status">'.Loc::getMessage('SALE_ORDER_PAYMENT_STATUS').': '.$paymentStatus.'</td>
						'.$checkLink.'
						<td class="adm-bus-section-container-section-status-others" id="PAYMENT_CHANGE_USER_INFO_'.$index.'">'.$data['DATE_PAID'].'
							<a href="/bitrix/admin/user_edit.php?lang='.$lang.'&ID='.$data['EMP_PAID_ID'].'">'.htmlspecialcharsbx($data['EMP_PAID_ID_NAME']).' '.htmlspecialcharsbx($data['EMP_PAID_ID_LAST_NAME']).'</a>
						</td>
					</tr>
				</table>
			</div>';

		return $result;
	}

	private static function initJsPayment($params)
	{
		return "<script>
				BX.ready( function(){
					var obPayment_".$params['index']." = new BX.Sale.Admin.OrderPayment(".\CUtil::PhpToJSObject($params).");
				});
				</script>";
	}

	/**
	 * @param $checkList
	 *
	 * @return string
	 */
	public static function buildCheckHtml($checkList)
	{
		$result = '';
		foreach ($checkList as $check)
		{
			$result .= '<div>';

			if ($check['LINK'] <> '')
			{
				$result .= '<a href="'.$check['LINK'].'" target="_blank">'.Loc::getMessage('SALE_ORDER_PAYMENT_CHECK_LINK', array('#CHECK_ID#' => $check['ID'])).'</a>';
			}
			else
			{
				$result .='<tspan>'.Loc::getMessage('SALE_ORDER_PAYMENT_CHECK_LINK', array('#CHECK_ID#' => $check['ID']));
				if ($check['STATUS'] === 'P')
				{
					$result .= ' (<a href="javascript:void(0);" onclick="BX.Sale.Admin.OrderPayment.prototype.sendQueryCheckStatus('.$check['ID'].');">'.Loc::getMessage('SALE_ORDER_PAYMENT_CHECK_CHECK_STATUS').'</a>)';
				}
				$result .= '</tspan>';
			}

			$result .= '</div>';
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return array
	 */
	public static function getPaySystemList(Payment $payment)
	{
		$result = array();

		$result[] = array(
			'ID' => '0',
			'NAME' => Loc::getMessage('SALE_ORDER_PAYMENT_NO_PAYSYSTEM')
		);

		if (self::$order === null)
		{
			/** @var \Bitrix\Sale\PaymentCollection $collection */
			$collection = $payment->getCollection();

			/** @var \Bitrix\Sale\Order $order */
			self::$order = $collection->getOrder();
		}

		$paySystems = PaySystem\Manager::getListWithRestrictions($payment, Restrictions\Manager::MODE_MANAGER);

		foreach ($paySystems as $paySystem)
		{
			$params = array(
				'ID' => $paySystem['ID'],
				'NAME' => "[".$paySystem["ID"]."] ".$paySystem["NAME"],
				'CAN_PRINT_CHECK' => $paySystem['CAN_PRINT_CHECK']
			);

			if (isset($paySystem['RESTRICTED']))
				$params['RESTRICTED'] = $paySystem['RESTRICTED'];

			$result[$paySystem['ID']] = $params;
		}

		return $result;
	}

	private static function getImgPathList()
	{
		$dbRes = PaySystem\Manager::getList(
			array(
				'select' => array('ID', 'LOGOTIP')
			)
		);
		$paySystems = $dbRes->fetchAll();

		$logotypes = array('/bitrix/images/sale/nopaysystem.gif');
		foreach ($paySystems as $paySystem)
		{
			if (empty($paySystem['LOGOTIP']))
			{
				$logotypes[$paySystem['ID']] = $logotypes[0];
			}
			else
			{
				$image = \CFile::ResizeImageGet($paySystem["LOGOTIP"], array('width' => 100, 'height' => 60));
				$logotypes[$paySystem['ID']] = $image['src'];
			}
		}

		return $logotypes;
	}

	/**
	 * @param $formType
	 * @return string
	 */
	public static function createButtonAddPayment($params=[])
	{
		return '<input type="button" class="adm-order-block-add-button" value="'.Loc::getMessage('SALE_ORDER_PAYMENT_BUTTON_ADD').'" onclick="BX.Sale.Admin.GeneralPayment.addNewPayment(this,'.\CUtil::PhpToJSObject($params).')">';
	}

	/**
	 * @param Sale\Order $order
	 * @param $payments
	 * @param bool $canSetPaid
	 * @return Result
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectNotFoundException
	 * @throws UserMessageException
	 */
	public static function updateData(Sale\Order &$order, $payments, $canSetPaid = false)
	{
		global $USER, $APPLICATION;

		$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

		$result = new Result();
		$data['PAYMENT'] = array();

		if (!$order)
			throw new UserMessageException('Order does not exist');

		foreach ($payments as $payment)
		{
			$paymentId = intval($payment['PAYMENT_ID']);
			$isNew = ($paymentId <= 0);
			$paymentCollection = $order->getPaymentCollection();

			/** @var \Bitrix\Sale\Payment $paymentItem */
			if ($isNew)
			{
				$paymentItem = $paymentCollection->createItem();
			}
			else
			{
				$paymentItem = $paymentCollection->getItemById($paymentId);

				if (!$paymentItem)
					throw new UserMessageException('Payment does not exist');
			}

			self::$defaultFields = $paymentItem->getFieldValues();

			$isReturn = (isset($payment['IS_RETURN']) && ($payment['IS_RETURN'] == 'Y' || $payment['IS_RETURN'] == 'P'));

			$psService = null;
			if ($payment['PAY_SYSTEM_ID'] > 0)
				$psService = PaySystem\Manager::getObjectById($payment['PAY_SYSTEM_ID']);

			$paymentFields = array(
				'PAY_SYSTEM_ID' => $payment['PAY_SYSTEM_ID'],
				'COMPANY_ID' => (isset($payment['COMPANY_ID']) && intval($payment['COMPANY_ID']) > 0) ? intval($payment['COMPANY_ID']) : 0,
				'PAY_VOUCHER_NUM' => $payment['PAY_VOUCHER_NUM'],
				'PAY_RETURN_NUM' => $payment['PAY_RETURN_NUM'],
				'PAY_RETURN_COMMENT' => $payment['PAY_RETURN_COMMENT'],
				'COMMENTS' => $payment['COMMENTS'] ?? null,
				'PAY_SYSTEM_NAME' => ($psService) ? $psService->getField('NAME') : ''
			);

			if ($isNew && $saleModulePermissions == "P")
			{
				if (empty($payment['COMPANY_ID']))
				{
					$paymentFields['COMPANY_ID'] = $order->getField('COMPANY_ID');
				}
				if (empty($payment['RESPONSIBLE_ID']))
				{
					$paymentFields['RESPONSIBLE_ID'] = $order->getField('RESPONSIBLE_ID');
					$paymentFields['EMP_RESPONSIBLE_ID'] = $USER->GetID();
					$paymentFields['DATE_RESPONSIBLE_ID'] = new DateTime();
				}

			}

			if (!$paymentItem->isPaid())
				$paymentFields['SUM'] = (float)str_replace(',', '.', $payment['SUM']);

			if ($payment['PRICE_COD'])
				$paymentFields['PRICE_COD'] = $payment['PRICE_COD'];

			if ($isNew)
				$paymentFields['DATE_BILL'] = new DateTime();

			if (!empty($payment['PAY_RETURN_DATE']))
			{
				try
				{
					$paymentFields['PAY_RETURN_DATE'] = new Date($payment['PAY_RETURN_DATE']);
				}
				catch (Main\ObjectException $exception)
				{
					$result->addError(
						new EntityError(Loc::getMessage('SALE_ORDER_PAYMENT_ERROR_RETURN_DATE_FORMAT'))
					);
				}
			}

			if (!empty($payment['PAY_VOUCHER_DATE']))
			{
				try
				{
					$paymentFields['PAY_VOUCHER_DATE'] = new Date($payment['PAY_VOUCHER_DATE']);
				}
				catch (Main\ObjectException $exception)
				{
					$result->addError(
						new EntityError(Loc::getMessage('SALE_ORDER_PAYMENT_ERROR_VOUCHER_DATE_FORMAT'))
					);
				}
			}

			if (isset($payment['RESPONSIBLE_ID']))
			{
				$paymentFields['RESPONSIBLE_ID'] = !empty($payment['RESPONSIBLE_ID']) ? $payment['RESPONSIBLE_ID'] : $USER->GetID();
				if ($payment['RESPONSIBLE_ID'] != $paymentItem->getField('RESPONSIBLE_ID'))
				{
					$paymentFields['DATE_RESPONSIBLE_ID'] = new DateTime();
					if (!$isNew)
						$paymentFields['EMP_RESPONSIBLE_ID'] = $USER->GetID();
				}
			}

			if ($result->isSuccess())
			{
				$setResult = $paymentItem->setFields($paymentFields);
				if (!$setResult->isSuccess())
					$result->addErrors($setResult->getErrors());

				if ($isReturn && $payment['OPERATION_ID'])
				{
					$setResult = $paymentItem->setReturn($payment['OPERATION_ID']);
					if (!$setResult->isSuccess())
						$result->addErrors($setResult->getErrors());
				}

				$isReturnChanged = $payment['IS_RETURN_CHANGED'] === 'Y';

				if (!$canSetPaid && !$isReturnChanged)
				{
					$setResult = $paymentItem->setPaid($payment['PAID']);
					if (!$setResult->isSuccess())
						$result->addErrors($setResult->getErrors());
				}

				if (isset($payment['ORDER_STATUS_ID']) && $payment['ORDER_STATUS_ID'])
				{
					$order->setField('STATUS_ID', $payment['ORDER_STATUS_ID']);
				}
			}

			$verify = $paymentItem->verify();
			if (!$verify->isSuccess())
			{
				$result->addErrors($verify->getErrors());
			}

			$data['PAYMENT'][] = $paymentItem;
		}

		$result->setData($data);

		return $result;
	}
}
