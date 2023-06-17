<?php
/**
 * Bitrix Framework
 * @global CMain $APPLICATION
 */

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Restrictions;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NO_AGENT_CHECK = true;
const NOT_CHECK_PERMISSIONS = true;

if (isset($_REQUEST['publicMode']) && $_REQUEST['publicMode'] === 'Y')
{
	define("PUBLIC_MODE", true);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if (isset($_REQUEST['publicMode']) && $_REQUEST['publicMode'] === 'Y')
{
	define('SELF_FOLDER_URL', '/shop/settings/');
}

$instance = Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();

$lang = isset($_REQUEST['lang']) ? trim($_REQUEST['lang']) : "ru";
$context->setLanguage($lang);

$arResult = array("ERROR" => "");

if (!\Bitrix\Main\Loader::includeModule('sale'))
{
	$arResult["ERROR"] = "Error! Can't include module \"Sale\"";
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/delivery/inputs.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if($arResult["ERROR"] == '' && $saleModulePermissions >= "W" && check_bitrix_sessid())
{
	$action = trim((string)$request->get('action'));

	switch ($action)
	{
		case "get_group_dialog_content":
			$selectedGroupId = isset($_REQUEST['selectedGroupId']) ? trim($_REQUEST['selectedGroupId']): '';
			die(
				"<table><tr><td>".
					\Bitrix\Sale\Delivery\Helper::getGroupChooseControl(
						$selectedGroupId,
						"DELIVERY_GROUP[]",
						' size="10" style="width: 300px;"'
					).
				"</td></tr></table>"
			);
			break;

		case "get_restriction_params_html":
			Restrictions\Manager::getClassesList();
			$className = trim((string)$request->get('className'));
			$params = $request->get('params');
			if (!is_array($params))
			{
				$params = [];
			}
			$sort = (int)($request->get('sort') ?? 100);
			$deliveryId = (int)$request->get('deliveryId');

			if (!($className && class_exists($className) && is_subclass_of($className, '\Bitrix\Sale\Services\Base\Restriction')))
			{
				throw new \Bitrix\Main\ArgumentNullException('className');
			}

			/** @var \Bitrix\Sale\Delivery\Restrictions\Base $className*/
			$paramsStructure = $className::getParamsStructure($deliveryId);
			$params = htmlspecialcharsback($params);
			$params = $className::prepareParamsValues($params, $deliveryId);

			$paramsField = '<table width="100%">';

			foreach($paramsStructure as $name => $param)
			{
				$param['LABEL'] = (string)($param['LABEL'] ?? '');
				$paramsField .= '<tr>'
					. '<td valign="top" style="padding-right:20px;">'
					.($param['LABEL'] !== '' ? $param['LABEL'] . ': ' : '')
					. '</td>'
					. '<td>'
					. \Bitrix\Sale\Internals\Input\Manager::getEditHtml(
						'RESTRICTION[' . $name . ']',
						$param,
						$params[$name] ?? null
					)
					. '</td>'
					. '</tr>'
				;
			}

			$paramsField .= '<tr>'.
				'<td>'.Loc::getMessage("SALE_DA_SORT") .':</td>'.
				'<td><input type="text" name="SORT" value="'.$sort.'"></td>'.
				'</tr>';

			$description = (string)$className::getClassDescription();
			if ($description !== '')
			{
				$paramsField .=
					'<tr>'
					. '<td>'
					. Loc::getMessage("SALE_DA_DESCR")
					. ':</td>'
					. '<td><div class="adm-sale-delivery-restriction-descr">'
					. $description
					. '</div></td>'
					. '</tr>'
				;
			}

			$arResult["RESTRICTION_HTML"] = $paramsField."</table>";
			break;

		case "save_restriction":
			Restrictions\Manager::getClassesList();
			$className = trim((string)$request->get('className'));
			$params = $request->get('params');
			if (!is_array($params))
			{
				$params = [];
			}
			$sort = (int)($request->get('sort') ?? 100);
			$deliveryId = (int)$request->get('deliveryId');
			$restrictionId = (int)$request->get('restrictionId');

			if (!($className && class_exists($className) && is_subclass_of($className, '\Bitrix\Sale\Services\Base\Restriction')))
			{
				throw new \Bitrix\Main\ArgumentNullException('className');
			}

			if(!$deliveryId)
			{
				throw new \Bitrix\Main\ArgumentNullException('deliveryId');
			}

			/** @var \Bitrix\Sale\Delivery\Restrictions\Base $className*/
			foreach($className::getParamsStructure() as $key => $rParams)
			{
				$errors = \Bitrix\Sale\Internals\Input\Manager::getError($rParams, $params[$key]);

				if (!empty($errors))
				{
					$arResult['ERROR'] = 'Field: "' . $rParams['LABEL'] . '" ' . implode('<br>', $errors) . "<br>\n";
				}
			}

			$fields = array(
				"SERVICE_ID" => $deliveryId,
				"SERVICE_TYPE" => \Bitrix\Sale\Services\Base\RestrictionManager::SERVICE_TYPE_SHIPMENT,
				"SORT" => $sort,
				"PARAMS" => $params
			);

			$res = $className::save($fields, $restrictionId);

			if(!$res->isSuccess())
				$arResult["ERROR"] .= implode(".",$res->getErrorMessages());

			$arResult["HTML"] = getRestrictionHtml($deliveryId);

			break;

		case "delete_restriction":
			Restrictions\Manager::getClassesList();
			$deliveryId = (int)$request->get('deliveryId');
			$restrictionId = (int)$request->get('restrictionId');

			if(!$restrictionId)
				throw new \Bitrix\Main\ArgumentNullException('restrictionId');

			$dbRes =  \Bitrix\Sale\Internals\ServiceRestrictionTable::getById($restrictionId);
			if($fields = $dbRes->fetch())
			{
				$res = $fields["CLASS_NAME"]::delete($restrictionId, $deliveryId);

				if(!$res->isSuccess())
					$arResult["ERROR"] .= implode(".",$res->getErrorMessages());
			}
			else
			{
				$arResult["ERROR"] .= "Can't find restriction with id: ".$restrictionId;
			}

			$arResult["HTML"] = getRestrictionHtml($deliveryId);

			break;

		default:
			$arResult["ERROR"] = "Error! Wrong action!";
			break;
	}
}
else
{
	if($arResult["ERROR"] == '')
		$arResult["ERROR"] = "Error! Access denied";
}

if($arResult["ERROR"] <> '')
	$arResult["RESULT"] = "ERROR";
else
	$arResult["RESULT"] = "OK";

$APPLICATION->RestartBuffer();
header('Content-Type: application/json;  charset=UTF-8');
try
{
	$body = \Bitrix\Main\Web\Json::encode($arResult);
}
catch (\Bitrix\Main\ArgumentException $e)
{
	$body = json_encode([
		'RESULT' => 'ERROR',
		'ERROR' => $e->getMessage(),
	]);
}
\CMain::FinalActions($body);

function getRestrictionHtml($deliveryId)
{
	if(intval($deliveryId) <= 0)
		throw new \Bitrix\Main\ArgumentNullException("deliveryId");

	$_REQUEST['table_id'] = 'table_delivery_restrictions';
	$_REQUEST['admin_history'] = 'Y';
	$_GET['ID'] = $deliveryId;

	ob_start();
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/admin/delivery_restrictions_list.php");
	$restrictionsHtml = ob_get_contents();
	ob_end_clean();

	return $restrictionsHtml;
}
