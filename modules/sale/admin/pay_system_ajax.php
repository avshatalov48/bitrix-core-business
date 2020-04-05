<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Services;
use Bitrix\Main\Application;
use Bitrix\Sale\Services\PaySystem\Restrictions\Manager;
use Bitrix\Sale\BusinessValue;
use Bitrix\Main\IO;

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_AGENT_CHECK", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$instance = Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();

$lang = ($request->get('lang') !== null) ? trim($request->get('lang')) : "ru";
\Bitrix\Main\Context::getCurrent()->setLanguage($lang);

Loc::loadMessages(__FILE__);

$arResult = array("ERROR" => "");

if (!\Bitrix\Main\Loader::includeModule('sale'))
	$arResult["ERROR"] = "Error! Can't include module \"Sale\"";

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/delivery/inputs.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if(strlen($arResult["ERROR"]) <= 0 && $saleModulePermissions >= "W" && check_bitrix_sessid())
{
	$action = ($request->get('action') !== null) ? trim($request->get('action')): '';

	switch ($action)
	{
		case "get_restriction_params_html":
			$className = ($request->get('className') !== null) ? trim($request->get('className')): '';
			$params = ($request->get('params') !== null) ? $request->get('params') : array();
			$paySystemId = ($request->get('paySystemId') !== null) ? intval($request->get('paySystemId')) : 0;
			$sort = ($request->get('sort') !== null) ? intval($request->get('sort')) : 100;

			if(!$className)
				throw new \Bitrix\Main\ArgumentNullException("className");

			Manager::getClassesList();
			$paramsStructure = $className::getParamsStructure($paySystemId);
			$params = $className::prepareParamsValues($params, $paySystemId);

			$paramsField = "<table>";

			if ($className == '\Bitrix\Sale\Services\PaySystem\Restrictions\Price')
				$paramsField .= '<tr><td colspan=\'2\' style=\'padding-bottom:5px\'><b>'.Loc::getMessage('SALE_PS_PRICE_INFO').'</b></td></tr>';

			foreach ($paramsStructure as $name => $param)
			{
				$paramsField .= "<tr>".
					"<td>".(strlen($param["LABEL"]) > 0 ? $param["LABEL"].": " : "")."</td>".
					"<td>".\Bitrix\Sale\Internals\Input\Manager::getEditHtml("RESTRICTION[".$name."]", $param, (isset($params[$name]) ? $params[$name] : null))."</td>".
					"</tr>";
			}

			$paramsField .= '<tr>'.
				'<td>'.Loc::getMessage("SALE_PS_SORT").': </td>'.
				'<td><input type="text" name="SORT" value="'.$sort.'"></td>'.
				'</tr>';

			$arResult["RESTRICTION_HTML"] = $paramsField."</table>";
			break;

		case "save_restriction":
			Manager::getClassesList();

			/** @var \Bitrix\Sale\Services\Base\Restriction $className */
			$className = ($request->get('className') !== null) ? trim($request->get('className')): '';
			$params = ($request->get('params') !== null) ? $request->get('params') : array();
			$sort = ($request->get('sort') !== null) ? (int)$request->get('sort') : 100;
			$paySystemId = ($request->get('paySystemId') !== null) ? (int)$request->get('paySystemId') : 0;
			$restrictionId = ($request->get('restrictionId') !== null) ? (int)$request->get('restrictionId') : 0;

			if(!class_exists($className) || !(is_subclass_of($className, '\Bitrix\Sale\Services\Base\Restriction')))
				throw new \Bitrix\Main\ArgumentNullException("className");

			if(!$paySystemId)
				throw new \Bitrix\Main\ArgumentNullException("paySystemId");

			foreach ($className::getParamsStructure() as $key => $rParams)
			{
				$errors = \Bitrix\Sale\Internals\Input\Manager::getError($rParams, $params[$key]);
				if (!empty($errors))
					$arResult["ERROR"] .= Loc::getMessage('SALE_PS_ERROR_FIELD').': "'.$rParams["LABEL"].'" '.implode("\n", $errors)."\n";
			}

			if ($arResult["ERROR"] == '')
			{
				$fields = array(
						"SERVICE_ID" => $paySystemId,
						"SERVICE_TYPE" => Manager::SERVICE_TYPE_PAYMENT,
						"SORT" => $sort,
						"PARAMS" => $params
				);

				/** @var \Bitrix\Sale\Result $res */
				$res = $className::save($fields, $restrictionId);

				if (!$res->isSuccess())
					$arResult["ERROR"] .= implode(".", $res->getErrorMessages());
				$arResult["HTML"] = getRestrictionHtml($paySystemId);
			}

			break;

		case "delete_restriction":
			Manager::getClassesList();

			$restrictionId = ($request->get('restrictionId') !== null) ? (int)$request->get('restrictionId') : 0;
			$paySystemId = ($request->get('paySystemId') !== null) ? (int)$request->get('paySystemId') : 0;

			if(!$restrictionId)
				throw new \Bitrix\Main\ArgumentNullException('restrictionId');

			$dbRes =  \Bitrix\Sale\Internals\ServiceRestrictionTable::getById($restrictionId);

			if($fields = $dbRes->fetch())
			{
				/** @var \Bitrix\Sale\Result $res */
				$res = $fields["CLASS_NAME"]::delete($restrictionId, $paySystemId);

				if(!$res->isSuccess())
					$arResult["ERROR"] .= implode(".", $res->getErrorMessages());
			}
			else
			{
				$arResult["ERROR"] .= "Can't find restriction with id: ".$restrictionId;
			}

			$arResult["HTML"] = getRestrictionHtml($paySystemId);

			break;
		case 'getHandlerDescription':
			require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/lib/helpers/admin/businessvalue.php');

			$handler = $request->get('handler');
			$paySystemId = (int)$request->get('paySystemId');

			$data = \Bitrix\Sale\PaySystem\Manager::getHandlerDescription($handler);

			if ($paySystemId <= 0)
			{
				$consumerKey = 'PAYSYSTEM_NEW';
				BusinessValue::addConsumer($consumerKey, $data);
			}
			else
			{
				$consumerKey = 'PAYSYSTEM_'.$paySystemId;
				BusinessValue::changeConsumer($consumerKey, $data);
			}

			$businessValueControl = new \Bitrix\Sale\Helpers\Admin\BusinessValueControl('PAYSYSTEM');

			$tariff = \Bitrix\Sale\PaySystem\Manager::getTariff($handler);
			if (!$tariff)
				$tariff = CSalePaySystemsHelper::getPaySystemTarif($handler, 0, 0);

			$tariffBlock = '';
			if($tariff)
			{
				$tariffBlock = '<tr class="heading"><td align="center" colspan="2">'.Loc::getMessage('SALE_PS_TARIFF').'</td></tr>';

				$arMultiControlQuery = array();
				foreach ($tariff as $fieldId => $arField)
				{
					if(!empty($arMultiControlQuery)
						&&
						(!isset($arField['MCS_ID'])|| !array_key_exists($arField['MCS_ID'], $arMultiControlQuery))
					)
					{
						$tariffBlock .= CSaleHelper::getAdminMultilineControl($arMultiControlQuery);
						$arMultiControlQuery = array();
					}

					$controlHtml = CSaleHelper::getAdminHtml($fieldId, $arField, 'TARIF', 'pay_sys_form');

					if($arField["TYPE"] == 'MULTI_CONTROL_STRING')
					{
						$arMultiControlQuery[$arField['MCS_ID']]['CONFIG'] = $arField;
						continue;
					}
					elseif(isset($arField['MCS_ID']))
					{
						$arMultiControlQuery[$arField['MCS_ID']]['ITEMS'][] = $controlHtml;
						continue;
					}

					$tariffBlock .= CSaleHelper::wrapAdminHtml($controlHtml, $arField);
				}

				if(!empty($arMultiControlQuery))
					$tariffBlock .= CSaleHelper::getAdminMultilineControl($arMultiControlQuery);
			}

			$arResult["TARIF"] = $tariffBlock;

			$map = CSalePaySystemAction::getOldToNewHandlersMap();
			if (isset($map[$handler]))
				$handler = $map[$handler];

			$className = \Bitrix\Sale\PaySystem\Manager::getClassNameFromPath($handler);

			$path = \Bitrix\Sale\PaySystem\Manager::getPathToHandlerFolder($handler);

			if (!class_exists($className) && IO\File::isFileExists($_SERVER['DOCUMENT_ROOT'].$path.'/handler.php'))
				require_once $_SERVER['DOCUMENT_ROOT'].$path.'/handler.php';

			if (class_exists($className))
			{
				$modeList = $className::getHandlerModeList();
				if ($modeList)
					$arResult["PAYMENT_MODE"] = Bitrix\Sale\Internals\Input\Enum::getEditHtml('PS_MODE', array('OPTIONS' => $modeList));
			}

			if (IO\File::isFileExists($_SERVER['DOCUMENT_ROOT'].$path.'/.description.php'))
			{
				require $_SERVER['DOCUMENT_ROOT'].$path.'/.description.php';

				if (isset($psDescription)) // for compatibility
				{
					$arResult["DESCRIPTION"] = $psDescription;
				}
				elseif (isset($description))
				{
					if (is_array($description))
						$arResult["DESCRIPTION"] = (array_key_exists('MAIN', $description)) ? $description['MAIN'] : implode("\n", $description);
					else
						$arResult["DESCRIPTION"] = $description;
				}

				if ($paySystemId <= 0)
				{
					if (isset($data))
						$arResult["NAME"] = $arResult["PSA_NAME"] = $data['NAME'];
					elseif (isset($psTitle))
						$arResult["NAME"] = $arResult["PSA_NAME"] = $psTitle;

					$arResult['SORT'] = 100;

					if (IO\File::isFileExists($_SERVER['DOCUMENT_ROOT'].'/bitrix/images/sale/sale_payments/'.$handler.'.png'))
					{
						$arResult['LOGOTIP']['NAME'] = $handler.'.png';
						$arResult['LOGOTIP']['PATH'] = '/bitrix/images/sale/sale_payments/'.$handler.'.png';
					}
				}
			}
			ob_start();
			$businessValueControl->renderMap(array('CONSUMER_KEY' => $consumerKey));
			$arResult["BUS_VAL"] = ob_get_contents();
			ob_end_clean();

			break;
		case 'checkHttps':
			$params = array(
				'waitResponse' => 10
			);
			$http = new \Bitrix\Main\Web\HttpClient();
			$response = @$http->get('https://'.$_SERVER['HTTP_HOST'].'/bitrix/tools/sale_ps_result.php');
			if ($response === false || $http->getStatus() != 200)
			{
				$arResult['CHECK_STATUS'] =  'ERROR';
				$arResult['CHECK_MESSAGE'] =  join('\n', $http->getError());
			}
			else
			{
				$arResult['CHECK_STATUS'] =  'OK';
				$arResult['CHECK_MESSAGE'] =  Loc::getMessage('SALE_PS_SSL_CHECK_MESSAGE');
			}
			break;
		default:
			$arResult["ERROR"] = "Error! Wrong action!";
			break;
	}
}
else
{
	if ($request->get('mode') == 'settings')
		getRestrictionHtml($request->get('ID'));
	elseif(strlen($arResult["ERROR"]) <= 0)
		$arResult["ERROR"] = "Error! Access denied";
}

if(strlen($arResult["ERROR"]) > 0)
	$arResult["RESULT"] = "ERROR";
else
	$arResult["RESULT"] = "OK";

if(strtolower(SITE_CHARSET) != 'utf-8')
	$arResult = $APPLICATION->ConvertCharsetArray($arResult, SITE_CHARSET, 'utf-8');

header('Content-Type: application/json');
die(json_encode($arResult));

function getRestrictionHtml($paySystemId)
{
	if(intval($paySystemId) <= 0)
		throw new \Bitrix\Main\ArgumentNullException("paySystemId");

	$_REQUEST['table_id'] = 'table_delivery_restrictions';
	$_REQUEST['admin_history'] = 'Y';
	$_GET['ID'] = $paySystemId;

	ob_start();
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/admin/pay_system_restrictions_list.php");
	$restrictionsHtml = ob_get_contents();
	ob_end_clean();

	return $restrictionsHtml;
}