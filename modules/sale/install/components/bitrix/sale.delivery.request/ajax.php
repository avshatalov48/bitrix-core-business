<?
use \Bitrix\Sale\Delivery\Requests,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Sale\Delivery\Services;

/**
 * Bitrix Framework
 * @global CMain $APPLICATION
 */

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_AGENT_CHECK", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$result = array("ERROR" => "");

if (!\Bitrix\Main\Loader::includeModule('sale'))
	$result["ERROR"] = "Error! Can't include module \"Sale\"";

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if($result["ERROR"] == '' && $saleModulePermissions >= "U" && check_bitrix_sessid())
{
	$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']): '';

	switch ($action)
	{
		case "createDeliveryRequest":
			$shipmentIds = isset($_REQUEST['shipmentIds']) && is_array($_REQUEST['shipmentIds']) ? $_REQUEST['shipmentIds'] : array();
			$deliveryId = isset($_REQUEST['deliveryId']) ? intval($_REQUEST['deliveryId']) : 0;
			$weight = isset($_REQUEST['weight']) ? round(floatval($_REQUEST['weight']), 2) : 0;
			$requestInputsValues = isset($_REQUEST['requestInputs']) && is_array($_REQUEST['requestInputs']) ? $_REQUEST['requestInputs']: array();
			$requestInputs = array();
			$dialogContent = '';

			if(!isset($_REQUEST['requestInputs']))
			{
				$requestInputs = Requests\Manager::getDeliveryRequestFormFields($deliveryId, Requests\Manager::FORM_FIELDS_TYPE_CREATE, $shipmentIds);

				if(!empty($requestInputs))
				{
					$shipmentIdsInput = '';

					foreach($shipmentIds as $shipmentId)
						$shipmentIdsInput .= '<input type="hidden" name="shipmentIds[]" value="'.intval($shipmentId).'">';

					$formFields = '';

					foreach($requestInputs as $name => $params)
					{
						$formFields .=
							'<tr>
								<td valign="top">'.($params["TITLE"] <> '' ? htmlspecialcharsbx($params["TITLE"]).": " : "").'</td>
								<td>'.\Bitrix\Sale\Internals\Input\Manager::getEditHtml("requestInputs[".$name."]", $params, (isset($params[$name]) ? $params[$name] : null)).'</td>
							</tr>';
					}

					$formFields = '<table width="100%">'.$formFields.'</table>';

					$dialogContent = '											
						<input type="hidden" name="deliveryId" value="'.$deliveryId.'">
						<input type="hidden" name="weight" value="'.$weight.'">
						<input type="hidden" name="action" value="'.htmlspecialcharsbx($action).'">'.
						$shipmentIdsInput.
						$formFields;

					$isFinal = false;
				}
			}

			if(empty($requestInputs))
			{
				$res = Requests\Manager::createDeliveryRequest($deliveryId, $shipmentIds, $requestInputsValues);
				$dialogContent = createMessagesHtml($res);
				$result['DELIVERY_BLOCK_HTML'] = getDeliveryBlockHtml($res, $deliveryId, $shipmentIds, 'CREATE', $weight);
				$result['DELIVERY_ID'] = $deliveryId;
				$isFinal = true;
			}

			if($dialogContent <> '')
			{
				$result['DAILOG_PARAMS'] = array(
					'TITLE' => Loc::getMessage('SALE_SDR_AJAX_CREATE'),
					'CONTENT' => $dialogContent,
					'IS_FINAL' => $isFinal
				);
			}

			break;

		case "addShipmentsToRequest":
			$requestId = isset($_REQUEST['requestId']) ? intval($_REQUEST['requestId']): 0;
			$shipmentIds = isset($_REQUEST['shipmentIds']) && is_array($_REQUEST['shipmentIds']) ? $_REQUEST['shipmentIds'] : array();
			$deliveryId = isset($_REQUEST['deliveryId']) ? intval($_REQUEST['deliveryId']) : 0;
			$weight = isset($_REQUEST['weight']) ? round(floatval($_REQUEST['weight']), 2) : 0;
			$dialogContent = '';

			if($requestId > 0)
			{
				$res = Requests\Manager::addShipmentsToDeliveryRequest($requestId, $shipmentIds);
				$dialogContent = createMessagesHtml($res);
				$result['DELIVERY_BLOCK_HTML'] = getDeliveryBlockHtml($res, $deliveryId, $shipmentIds, 'ADD', $weight);
				$result['DELIVERY_ID'] = $deliveryId;
				$isFinal = true;
			}
			else
			{
				$deliveryService = Services\Manager::getObjectById($deliveryId);

				if(!$deliveryService)
				{
					$result["ERRORS"][] = Loc::getMessage('SALE_SDR_AJAX_DELIVERY_NOT_FOUND', array('#DELIVERY_ID#' => $deliveryId));
					break;
				}

				$deliveryRequest = $deliveryService->getDeliveryRequestHandler();

				if(!$deliveryRequest)
				{
					$result["ERRORS"][] = Loc::getMessage('SALE_SDR_AJAX_NOT_SUPPORT', array('#DELIVERY_ID#' => $deliveryId));
					break;
				}

				$dbRes = Requests\RequestTable::getList(array(
					'filter' => array(
						'=DELIVERY_ID' => $deliveryRequest->getHandlingDeliveryServiceId(),
						'<STATUS' => Requests\Manager::STATUS_PROCESSED
				)));

				while($row = $dbRes->fetch())
				{
					$dialogContent .= '<option value="'.$row['ID'].'">"'.$row['ID'].'" '.$row['DATE'].' ( '.htmlspecialcharsbx($row['EXTERNAL_ID']).' )</option>';
				}

				if($dialogContent <> '')
				{
					$dialogContent = '<select name="requestId">'.$dialogContent.'</select>';
					$dialogContent = '<table width="100%"><tr><td>'.Loc::getMessage('SALE_SDR_AJAX_REQUEST_NUMBER').'</td><td>'.$dialogContent.'</td></tr></table>';
					$dialogContent .= '<input type="hidden" name="deliveryId" value="'.$deliveryId.'">';
					$dialogContent .= '<input type="hidden" name="weight" value="'.$weight.'">';
					$dialogContent .= '<input type="hidden" name="action" value="'.htmlspecialcharsbx($action).'">';

					foreach($shipmentIds as $id)
					{
						$dialogContent .= '<input type="hidden" name="shipmentIds[]" value="'.intval($id).'">';
					}

					$isFinal = false;
				}
				else
				{
					$dialogContent = '<div class="admin-delivery-request-confirm red">'.Loc::getMessage('SALE_SDR_AJAX_NO_REQUESTS').'</div>';
					$isFinal = true;
				}
			}

			if($dialogContent <> '')
			{
				$result['DAILOG_PARAMS'] = array(
					'TITLE' => Loc::getMessage('SALE_SDR_AJAX_SHIPMENTS_ADD'),
					'CONTENT' => $dialogContent,
					'IS_FINAL' => $isFinal
				);
			}

			break;

		default:
			$result["ERROR"] = "Error! Wrong action!";
			break;
	}
}
else
{
	if($result["ERROR"] == '')
		$result["ERROR"] = "Error! Access denied";
}

if($result["ERROR"] <> '')
	$result["RESULT"] = "ERROR";
else
	$result["RESULT"] = "OK";

if(mb_strtolower(SITE_CHARSET) != 'utf-8')
	$result = \Bitrix\Main\Text\Encoding::convertEncoding($result, SITE_CHARSET, 'utf-8');

$APPLICATION->RestartBuffer();
header('Content-Type: application/json');
echo json_encode($result);
\CMain::FinalActions();
die;

/**
 * @param Requests\Result $reqResult
 * @return string
 */
function createMessagesHtml(Requests\Result $reqResult)
{
	$result = '';
	$sanitizer = new CBXSanitizer;
	$sanitizer->SetLevel(CBXSanitizer::SECURE_LEVEL_MIDDLE);

	if(!$reqResult->isSuccess() )
		foreach($reqResult->getErrorMessages() as $message)
			$result .= '<div class="admin-delivery-request-confirm red">'.$sanitizer->SanitizeHtml($message).'</div>';

	foreach($reqResult->getMessagesMessages() as $message)
		$result .= '<div class="admin-delivery-request-confirm green">'.$sanitizer->SanitizeHtml($message).'</div>';

	return $result;
}

function extractSummaryData(Requests\Result $reqResult)
{
	$shipmentsErrors = 0;

	foreach($reqResult->getShipmentResults() as $shpRes)
		if(!$shpRes->isSuccess())
			$shipmentsErrors++;

	$successRequests = array();

	foreach($reqResult->getRequestResults() as $rRes)
	{
		if($rRes->isSuccess())
		{
			$goodShp = 0;
			$badShp = 0;

			/** @var Requests\ShipmentResult $shpRes */
			foreach($rRes->getShipmentResults() as $shpRes)
			{
				if($shpRes->isSuccess())
					$goodShp++;
				else
					$badShp++;
			}

			$successRequests[$rRes->getInternalId()] = array('SHIPMENTS_COUNT' => $goodShp);
			$shipmentsErrors += $badShp;
		}
	}

	return array(
		'SHIPMENTS_ERRORS' => $shipmentsErrors,
		'DELIVERY_REQUESTS' => $successRequests
	);
}

function getDeliveryBlockHtml($res, $deliveryId, $shipmentIds, $action, $weight)
{
	global $APPLICATION;
	$summary = extractSummaryData($res);

	ob_start();
	$APPLICATION->IncludeComponent(
		"bitrix:sale.delivery.request.processed",
		"",
		array(
			'SHIPMENTS_ERRORS' => $summary['SHIPMENTS_ERRORS'],
			'DELIVERY_ID' => $deliveryId,
			'DELIVERY_REQUESTS' => $summary['DELIVERY_REQUESTS'],
			'SHIPMENTS_COUNT' => count($shipmentIds),
			'ACTION' => $action,
			'WEIGHT' => $weight
		)
	);

	return ob_get_clean();
}