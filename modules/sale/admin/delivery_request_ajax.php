<?
/**
 * Bitrix Framework
 * @global CMain $APPLICATION
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\Requests;

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_AGENT_CHECK", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$lang = isset($_REQUEST['lang']) ? trim($_REQUEST['lang']) : "ru";
\Bitrix\Main\Context::getCurrent()->setLanguage($lang);

Loc::loadMessages(__FILE__);

$arResult = array(
	"ERRORS" => array(),
	"WARNINGS" => array(),
	"MESSAGES" => array()
);

if (!\Bitrix\Main\Loader::includeModule('sale'))
	$arResult["ERRORS"][] = "Error! Can't include module \"Sale\"";

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/lib/delivery/inputs.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if(empty($arResult["ERRORS"]) && $saleModulePermissions >= "U" && check_bitrix_sessid())
{
	$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']): '';

	switch ($action)
	{
		case "getShipmentContent":

			$shipmentId = isset($_REQUEST['shipmentId']) ? intval($_REQUEST['shipmentId']): 0;
			$requestId = isset($_REQUEST['requestId']) ? intval($_REQUEST['requestId']): 0;
			$contentResult = Requests\Manager::getDeliveryRequestShipmentContent($requestId, $shipmentId);
			$content = '';

			if($contentResult->isSuccess())
			{
				$fields = $contentResult->getData();

				if(!empty($fields))
				{
					$white = false;

					foreach($fields as $name => $value)
					{
						$content .= '<tr><td class="adm-sale-delivery-request-content'.($white ? ' white' : '').'" width="50%">'.htmlspecialcharsbx($value['TITLE']).'</td>
							<td class="adm-sale-delivery-request-content'.($white ? ' white' : '').'" width="50%">'.htmlspecialcharsbx($value['VALUE']).'</td></tr>';

						$white = !$white;
					}
				}
			}
			else
			{
				$content .= '<tr><td>'.implode("<br>\n", $contentResult->getErrorMessages()).'</td></tr>';
			}

			$content = "<table>".$content."</table>";
			die($content);
			break;

		case "actionExecute":
		case "actionShipmentExecute":

			$requestId = isset($_REQUEST['requestId']) ? intval($_REQUEST['requestId']): 0;
			$deliveryId = isset($_REQUEST['deliveryId']) ? intval($_REQUEST['deliveryId']) : 0;
			$shipmentIds = isset($_REQUEST['shipmentIds']) && is_array($_REQUEST['shipmentIds']) ? $_REQUEST['shipmentIds'] : array();
			$requestAction = isset($_REQUEST['requestAction']) && strlen($_REQUEST['requestAction']) > 0 ? trim($_REQUEST['requestAction']) : '';
			$requestInputsValues = isset($_REQUEST['requestInputs']) && is_array($_REQUEST['requestInputs']) ? $_REQUEST['requestInputs']: array();
			$requestInputs = array();
			$actionsTypesList = Requests\Manager::getDeliveryRequestActions($requestId);
			$content = '';

			if(!isset($_REQUEST['requestInputs']))
			{
				$additional = array('ACTION_TYPE' => $requestAction);
				$requestInputs = Requests\Manager::getDeliveryRequestFormFields($deliveryId, Requests\Manager::FORM_FIELDS_TYPE_ACTION, $shipmentIds, $additional);

				if(!empty($requestInputs))
				{
					foreach($requestInputs as $name => $params)
					{
						$content .=
							'<tr>
								<td valign="top">'.(strlen($params["TITLE"]) > 0 ? htmlspecialcharsbx($params["TITLE"]).": " : "").'</td>
								<td>'.\Bitrix\Sale\Internals\Input\Manager::getEditHtml("requestInputs[".$name."]", $params, (isset($params[$name]) ? $params[$name] : null)).'</td>
							</tr>';
					}

					$content = '<table width="100%">'.$content.'</table>';

					$content .= '											
						<input type="hidden" name="deliveryId" value="'.$deliveryId.'">
						<input type="hidden" name="action" value="'.htmlspecialcharsbx($action).'">
						<input type="hidden" name="requestId" value="'.$requestId.'">
						<input type="hidden" name="requestAction" value="'.htmlspecialcharsbx($requestAction).'">';

					if(!empty($shipmentIds))
						foreach($shipmentIds as $shipmentId)
							$content .= '<input type="hidden" name="shipmentIds[]" value="'.intval($shipmentId).'">';

					$isFinal = false;
				}
			}

			if(empty($requestInputs))
			{
				if($action == 'actionExecute')
					$res = Requests\Manager::executeDeliveryRequestAction($requestId, $requestAction, $requestInputsValues);
				else
					$res = Requests\Manager::executeDeliveryRequestShipmentAction($requestId, $shipmentIds, $requestAction, $requestInputsValues);

				if($res instanceof Requests\ResultFile && $res->isSuccess())
				{
					$fileContent = $res->getFileContent();
					$fileName = $res->getFileName();
					$tmpDir = \CTempFile::GetDirectoryName(1);
					CheckDirPath($tmpDir);
					$filePath = $tmpDir.$fileName;
					$res = \Bitrix\Main\IO\File::putFileContents($filePath, $fileContent);

					if($res !== false)
					{
						$file = new \Bitrix\Main\IO\File($fileName);
						$arResult['FILE_PATH'] = substr($tmpDir, strlen(\CTempFile::GetAbsoluteRoot()));
						$arResult['FILE_NAME'] = $fileName;
					}
					else
					{
						$arResult["ERRORS"][] = Loc::getMessage('SALE_DELIVERY_REQ_AJAX_FILE_SAVE_ERROR');
					}

					break;
				}
				else
				{
					if($res->isSuccess())
						$res->addMessage(new Requests\Message(Loc::getMessage('SALE_DELIVERY_REQ_AJAX_ACTION_DONE', array('#ACTION_NAME#' => $actionsTypesList[$requestAction]))));

					$content = createMessagesHtml($res);
					$isFinal = true;
				}
			}

			$arResult['DAILOG_PARAMS'] = array(
				'TITLE' => Loc::getMessage('SALE_DELIVERY_REQ_AJAX_ACTION').' "'.$actionsTypesList[$requestAction].'"',
				'CONTENT' => $content,
				'IS_FINAL' => $isFinal
			);

			break;

		case "downloadFile":

			$fileName = isset($_REQUEST['fileName']) ? trim($_REQUEST['fileName']) : '';
			$fileName = str_replace(array("\r", "\n"), "", $fileName);
			$filePath = isset($_REQUEST['filePath']) ? trim($_REQUEST['filePath']) : '';

			try
			{
				$filePath = \CTempFile::GetAbsoluteRoot().\Bitrix\Main\IO\Path::normalize($filePath.$fileName);
			}
			catch(\Bitrix\Main\SystemException $e)
			{
				die();
			}

			if(!\Bitrix\Main\IO\File::isFileExists($filePath))
			{
				die();
			}

			$file = new \Bitrix\Main\IO\File($filePath);

			/** Requests\ResultFile $res */
			header("Content-Type: application/force-download; name=\"".$fileName."\"");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".$file->getSize());
			header("Content-Disposition: attachment; filename=\"".$fileName."\"");
			header("Expires: 0");
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");
			header('Connection: close');
			echo $file->getContents();
			die();

			break;

		case "deleteShipmentsFromDeliveryRequest":
			$shipmentIds = isset($_REQUEST['shipmentIds']) && is_array($_REQUEST['shipmentIds']) ? $_REQUEST['shipmentIds'] : array();

			if(empty($shipmentIds))
			{
				$arResult["ERRORS"][] = Loc::getMessage('SALE_DELIVERY_REQ_AJAX_SLIST_EMPTY');
				break;
			}

			$dbRes = Requests\ShipmentTable::getList(array(
				'filter' => array(
					'=SHIPMENT_ID' => $shipmentIds
				)
			));

			$grouppedByRequestId = array();

			while($row = $dbRes->fetch())
			{
				if(intval($row['REQUEST_ID']) <= 0)
					continue;

				if(!isset($grouppedByRequestId[$row['REQUEST_ID']]))
					$grouppedByRequestId[$row['REQUEST_ID']] = array();

				$grouppedByRequestId[$row['REQUEST_ID']][] = $row['SHIPMENT_ID'];
			}

			$content = '';

			foreach($grouppedByRequestId as $requestId => $reqShipmentIds)
			{
				$res = Requests\Manager::deleteShipmentsFromDeliveryRequest($requestId, $reqShipmentIds);

				foreach($res->getShipmentResults() as $shpRes)
				{
					if($shpRes->isSuccess())
					{
						$shpRes->addMessage(
							new Requests\Message(
								Loc::getMessage(
									'SALE_DELIVERY_REQ_AJAX_DEL_SHIPMENT_SUCCESS',
									array(
										'#SHIPMENT_ID#' => $shpRes->getInternalId(),
										'#REQUEST_ID#' => $requestId
									)
						)));
					}

					$content .= createMessagesHtml($shpRes);
				}

				$content .= createMessagesHtml($res);
			}

			$arResult['DAILOG_PARAMS'] = array(
				'TITLE' => Loc::getMessage('SALE_DELIVERY_REQ_AJAX_DELETE_SHIPMENT'),
				'CONTENT' => $content,
				'IS_FINAL' => true
			);

			break;

		case "updateShipmentsFromDeliveryRequest":
			$shipmentIds = isset($_REQUEST['shipmentIds']) && is_array($_REQUEST['shipmentIds']) ? $_REQUEST['shipmentIds'] : array();

			if(empty($shipmentIds))
			{
				$arResult["ERRORS"][] = Loc::getMessage('SALE_DELIVERY_REQ_AJAX_SLIST_EMPTY');
				break;
			}

			$dbRes = Requests\ShipmentTable::getList(array(
				'filter' => array(
					'=SHIPMENT_ID' => $shipmentIds
				)
			));

			$grouppedByRequestId = array();

			while($row = $dbRes->fetch())
			{
				if(intval($row['REQUEST_ID']) <= 0)
					continue;

				if(!isset($grouppedByRequestId[$row['REQUEST_ID']]))
					$grouppedByRequestId[$row['REQUEST_ID']] = array();

				$grouppedByRequestId[$row['REQUEST_ID']][] = $row['SHIPMENT_ID'];
			}

			$content = '';

			foreach($grouppedByRequestId as $requestId => $reqShipmentIds)
			{
				$res = Requests\Manager::updateShipmentsFromDeliveryRequest($requestId, $reqShipmentIds);

				/** @var Requests\ShipmentResult $uRes */
				foreach($res->getResults() as $uRes)
				{
					if($uRes->isSuccess())
					{
						$uRes->addMessage(
							new Requests\Message(
								Loc::getMessage(
									'SALE_DELIVERY_REQ_AJAX_UPDATE_SHIPMENT_SUCCESS',
									array('#SHIPMENT_ID#' => $uRes->getInternalId())
						)));
					}

					$content .= createMessagesHtml($uRes);
				}
			}

			$arResult['DAILOG_PARAMS'] = array(
				'TITLE' => Loc::getMessage('SALE_DELIVERY_REQ_AJAX_UPDATE_SHIPMENT'),
				'CONTENT' => $content,
				'IS_FINAL' => true
			);

			break;

		default:
			$arResult["ERRORS"][] = "Error! Wrong action!";
			break;
	}
}
else
{
	if(empty($arResult["ERRORS"]))
		$arResult["ERRORS"][] = "Error! Access denied";
}

if(!empty($arResult["ERRORS"]))
	$arResult["RESULT"] = "ERROR";
else
	$arResult["RESULT"] = "OK";

if(strtolower(SITE_CHARSET) != 'utf-8')
	$arResult = $APPLICATION->ConvertCharsetArray($arResult, SITE_CHARSET, 'utf-8');

header('Content-Type: application/json');
die(json_encode($arResult));

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