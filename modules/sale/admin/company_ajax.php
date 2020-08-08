<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Services;
use Bitrix\Main\Application;
use Bitrix\Sale\Services\Company;
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

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/services/company/inputs.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if($arResult["ERROR"] === '' && $saleModulePermissions >= "W" && check_bitrix_sessid())
{
	$action = ($request->get('action') !== null) ? trim($request->get('action')): '';

	switch ($action)
	{
		case "get_rule_params_html":
			/** @var Bitrix\Sale\Services\Base\Restriction $className */
			$className = ($request->get('className') !== null) ? trim($request->get('className')): '';
			$params = ($request->get('params') !== null) ? $request->get('params') : array();
			$companyId = ($request->get('companyId') !== null) ? intval($request->get('companyId')) : 0;
			$sort = ($request->get('sort') !== null) ? intval($request->get('sort')) : 100;

			if(!$className)
				throw new \Bitrix\Main\ArgumentNullException("className");

			Company\Restrictions\Manager::getClassesList();
			$paramsStructure = $className::getParamsStructure($companyId);
			$params = $className::prepareParamsValues($params, $companyId);

			$paramsField = "<table>";

			if ($className == '\Bitrix\Sale\Services\Company\Restrictions\Price')
				$paramsField .= '<tr><td colspan=\'2\' style=\'padding-bottom:5px\'><b>'.Loc::getMessage('SALE_COMPANY_PRICE_INFO').'</b></td></tr>';

			foreach ($paramsStructure as $name => $param)
			{
				$paramsField .= "<tr>".
					"<td>".($param["LABEL"] <> '' ? $param["LABEL"].": " : "")."</td>".
					"<td>".\Bitrix\Sale\Internals\Input\Manager::getEditHtml("RULE[".$name."]", $param, (isset($params[$name]) ? $params[$name] : null))."</td>".
					"</tr>";
			}

			$paramsField .= '<tr>'.
				'<td>'.Loc::getMessage("SALE_COMPANY_RULE_SORT").': </td>'.
				'<td><input type="text" name="SORT" value="'.$sort.'"></td>'.
				'</tr>';

			$arResult["RULE_HTML"] = $paramsField."</table>";
			break;

		case "save_rule":
			Company\Restrictions\Manager::getClassesList();

			/** @var Bitrix\Sale\Services\Base\Restriction $className */
			$className = ($request->get('className') !== null) ? trim($request->get('className')): '';
			$params = ($request->get('params') !== null) ? $request->get('params') : array();
			$sort = ($request->get('sort') !== null) ? (int)$request->get('sort') : 100;
			$companyId = ($request->get('companyId') !== null) ? (int)$request->get('companyId') : 0;
			$ruleId = ($request->get('ruleId') !== null) ? (int)$request->get('ruleId') : 0;

			if(!class_exists($className))
				throw new \Bitrix\Main\ArgumentNullException("className");

			if(!$companyId)
				throw new \Bitrix\Main\ArgumentNullException("companyId");

			foreach ($className::getParamsStructure() as $key => $rParams)
			{
				$errors = \Bitrix\Sale\Internals\Input\Manager::getError($rParams, $params[$key]);
				if (!empty($errors))
					$arResult["ERROR"] .= Loc::getMessage('SALE_COMPANY_ERROR_FIELD').': "'.$rParams["LABEL"].'" '.implode("\n", $errors)."\n";
			}

			if (!$params)
				$arResult["ERROR"] = Loc::getMessage('SALE_COMPANY_ERROR_PARAMS');

			if ($arResult["ERROR"] == '')
			{
				$fields = array(
					"SERVICE_ID" => $companyId,
					"SERVICE_TYPE" => Company\Restrictions\Manager::SERVICE_TYPE_COMPANY,
					"SORT" => $sort,
					"PARAMS" => $params
				);

				/** @var \Bitrix\Sale\Result $res */
				$res = $className::save($fields, $ruleId);

				if (!$res->isSuccess())
					$arResult["ERROR"] .= implode(".", $res->getErrorMessages());
				$arResult["HTML"] = getRuleHtml($companyId);
			}

			break;

		case "delete_rule":
			Company\Restrictions\Manager::getClassesList();
			$ruleId = ($request->get('ruleId') !== null) ? (int)$request->get('ruleId') : 0;
			$companyId = ($request->get('companyId') !== null) ? (int)$request->get('companyId') : 0;

			if(!$ruleId)
				throw new \Bitrix\Main\ArgumentNullException('ruleId');

			$dbRes =  Company\Restrictions\Manager::getById($ruleId);

			if($fields = $dbRes->fetch())
			{
				/** @var \Bitrix\Sale\Result $res */
				$res = $fields["CLASS_NAME"]::delete($ruleId, $companyId);

				if(!$res->isSuccess())
					$arResult["ERROR"] .= implode(".", $res->getErrorMessages());
			}
			else
			{
				$arResult["ERROR"] .= "Can't find rule with id: ".$ruleId;
			}

			$arResult["HTML"] = getRuleHtml($companyId);

			break;
		default:
			$arResult["ERROR"] = "Error! Wrong action!";
			break;
	}
}
else
{
	if ($request->get('mode') == 'settings')
		getRuleHtml($request->get('ID'));
	elseif($arResult["ERROR"] == '')
		$arResult["ERROR"] = "Error! Access denied";
}

if($arResult["ERROR"] <> '')
	$arResult["RESULT"] = "ERROR";
else
	$arResult["RESULT"] = "OK";

if(mb_strtolower(SITE_CHARSET) != 'utf-8')
	$arResult = $APPLICATION->ConvertCharsetArray($arResult, SITE_CHARSET, 'utf-8');

header('Content-Type: application/json');
die(json_encode($arResult));

function getRuleHtml($companyId)
{
	if(intval($companyId) <= 0)
		throw new \Bitrix\Main\ArgumentNullException("companyId");

	$_REQUEST['table_id'] = 'table_company_rules';
	$_REQUEST['admin_history'] = 'Y';
	$_GET['ID'] = $companyId;

	ob_start();
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/admin/company_rules_list.php");
	$rulesHtml = ob_get_contents();
	ob_end_clean();

	return $rulesHtml;
}