<?php

namespace Bitrix\Sale\Delivery\Tracking;

use Bitrix\Main\Error;
use Bitrix\Sale\Result;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class RusPost
 * @package Bitrix\Sale\Delivery\Tracking
 * https://tracking.pochta.ru/specification
 */
class RusPost extends Base
{
	/**
	 * @return string
	 */
	public function getClassTitle()
	{
		return Loc::getMessage("SALE_DELIVERY_TRACKING_RUS_POST_TITLE");
	}

	/**
	 * @return string
	 */
	public function getClassDescription()
	{
		return Loc::getMessage(
			"SALE_DELIVERY_TRACKING_RUS_POST_DESCRIPTION",
			array(
				'#A1#' => '<a href="https://tracking.pochta.ru/">',
				'#A2#' => '</a>'
			)
		);
	}


	/**
	 * @param $trackingNumber
	 * @return \Bitrix\Sale\Delivery\Tracking\StatusResult.
	 */
	public function getStatus($trackingNumber)
	{
		$trackingNumber = trim($trackingNumber);
		$result = new StatusResult();

		if(!$this->checkTrackNumberFormat($trackingNumber))
			$result->addError(new Error(Loc::getMessage('SALE_DELIVERY_TRACKING_RUS_POST_ERROR_TRNUM_FORMAT')));

		if(empty($this->params['LOGIN']))
			$result->addError(new Error(Loc::getMessage("SALE_DELIVERY_TRACKING_RUS_POST_LOGIN_ERROR")));

		if(empty($this->params['PASSWORD']))
			$result->addError(new Error(Loc::getMessage("SALE_DELIVERY_TRACKING_RUS_POST_PASSWORD_ERROR")));

		if($result->isSuccess())
		{
			$t = new RusPostSingle(
				$this->params['LOGIN'],
				$this->params['PASSWORD']
			);

			$result = $t->getOperationHistory($trackingNumber);
		}

		return $result;
	}

	/**
	 * @param array $trackingNumbers
	 * @return StatusResult[]
	 * todo: by package of 3000 items
	 */
	public function getStatuses(array $trackingNumbers)
	{
		$data = array();

		foreach($trackingNumbers as $number)
			$data[$number] = $this->getStatus($number);

		return $data;
	}

	/**
	 * @return array
	 */
	public function getParamsStructure()
	{
		return array(
			"LOGIN" => array(
				'TYPE' => 'STRING',
				'LABEL' => Loc::getMessage("SALE_DELIVERY_TRACKING_RUS_POST_LOGIN")
			),
			"PASSWORD" => array(
				'TYPE' => 'STRING',
				'LABEL' => Loc::getMessage("SALE_DELIVERY_TRACKING_RUS_POST_PASSWORD")
			)
		);
	}

	/**
	 * Checks if tracknumber matches to required format.
	 * 14 - digit,
	 * 13 symbols like pattern XX123456789YY (UPU-S10)
	 * @param string $trackNumber
	 * @return bool
	 */
	protected function checkTrackNumberFormat($trackNumber)
	{
		if(mb_strlen($trackNumber) == 13)
			return preg_match('/^[A-Z]{2}?\d{9}?[A-Z]{2}$/i', $trackNumber) == 1;
		elseif(mb_strlen($trackNumber) == 14)
			return preg_match('/^\d{14}?$/', $trackNumber) == 1;
		else
			return false;
	}

	/**
	 * @param string $trackingNumber
	 * @return string Url were we can see tracking information
	 */
	public function getTrackingUrl($trackingNumber = '')
	{
		return 'https://pochta.ru/tracking'.($trackingNumber <> '' ? '#'.$trackingNumber : '');
	}
}

/**
 * Class RusPostSingle
 * @package Bitrix\Sale\Delivery\Tracking
 */
class RusPostSingle
{
	const LANG_RUS = "RUS";
	const LANG_ENG = "ENG";

	protected $client = null;
	protected $lang = "";
	protected $login = "";
	protected $password = "";

	protected static $url = 'https://tracking.russianpost.ru/rtm34';

	/**
	 * @param string $login
	 * @param string $password
	 * @param string $lang
	 */
	public function __construct($login, $password, $lang = self::LANG_RUS)
	{
		$this->httpClient = new \Bitrix\Main\Web\HttpClient(array(
			"version" => "1.1",
			"socketTimeout" => 15,
			"streamTimeout" => 15,
			"redirect" => true,
			"redirectMax" => 5,
		));

		$this->httpClient->setHeader("Content-Type", "application/soap+xml; charset=utf-8");
		$this->lang = $lang;
		$this->login = $login;
		$this->password = $password;
	}

	public function sendRequest($requestData)
	{
		$result = new Result();

		$httpRes = $this->httpClient->post(self::$url, $requestData);
		$errors = $this->httpClient->getError();

		if (!$httpRes && !empty($errors))
		{
			$strError = "";

			foreach($errors as $errorCode => $errMes)
				$strError .= $errorCode.": ".$errMes;

			$result->addError(new Error($strError));
		}
		else
		{
			$status = $this->httpClient->getStatus();

			$objXML = new \CDataXML();
			$objXML->LoadString($httpRes);
			$data = $objXML->GetArray();
			$result->addData($data);

			if ($status != 200)
			{
				$result->addError(new Error(Loc::getMessage('SALE_DELIVERY_TRACKING_RUS_POST_ERROR_HTTP_STATUS').': '.$status));

				if(!empty($data['Envelope']['#']['Body'][0]['#']['Fault'][0]['#']['Reason'][0]['#']['Text'][0]['#']))
					$result->addError(new Error($data['Envelope']['#']['Body'][0]['#']['Fault'][0]['#']['Reason'][0]['#']['Text'][0]['#']));

				if(!empty($data['Envelope']['#']['Body'][0]['#']['Fault'][0]['#']['Detail'][0]['#']['AuthorizationFaultReason'][0]['#']))
					$result->addError(new Error($data['Envelope']['#']['Body'][0]['#']['Fault'][0]['#']['Detail'][0]['#']['AuthorizationFaultReason'][0]['#']));
			}
		}

		return $result;
	}

	/**
	 * @param string $trackingNumber
	 * @return StatusResult
	 */
	public function getOperationHistory($trackingNumber)
	{
		$result = new StatusResult();
		$requestData = '
			<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:oper="http://russianpost.org/operationhistory" xmlns:data="http://russianpost.org/operationhistory/data" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
			   <soap:Header/>
			   <soap:Body>
				  <oper:getOperationHistory>
					 <data:OperationHistoryRequest>
						<data:Barcode>'.$trackingNumber.'</data:Barcode>
						<data:MessageType>0</data:MessageType>
						<data:Language>'.$this->lang.'</data:Language>
					 </data:OperationHistoryRequest>
					 <data:AuthorizationHeader soapenv:mustUnderstand="1">
						<data:login>'.$this->login.'</data:login>
						<data:password>'.$this->password.'</data:password>
					 </data:AuthorizationHeader>
				  </oper:getOperationHistory>
			   </soap:Body>
			</soap:Envelope>
		';

		$res = $this->sendRequest($requestData);

		if(!$res->isSuccess())
		{
			$result->addErrors($res->getErrors());
			return $result;
		}

		$lastOperation = $this->getLastOperation($res->getData());

		if(!$lastOperation)
		{
			$result->addError(new Error(Loc::getMessage('SALE_DELIVERY_TRACKING_RUS_POST_ERROR_LAST_OP')));
		}
		else
		{
			$result->status = $this->extractStatus($lastOperation);
			$result->description = $this->createDescription($trackingNumber);
			$lastOperationTS = $this->extractLastChangeDate($lastOperation);

			if($lastOperationTS > 0)
				$result->lastChangeTimestamp = $this->extractLastChangeDate($lastOperation);

			$result->trackingNumber = $trackingNumber;
		}

		return $result;
	}

	/**
	 * @param $lastOperation
	 * @return int
	 */
	protected function extractLastChangeDate($lastOperation)
	{
		if(empty($lastOperation['#']['OperationParameters'][0]['#']['OperDate'][0]['#']))
			return 0;

		$date = new \DateTime($lastOperation['#']['OperationParameters'][0]['#']['OperDate'][0]['#']);
		return $date->getTimestamp();
	}

	/**
	 * @param $answer
	 * @return mixed|null
	 */
	protected function getLastOperation(array $answer)
	{
		$history = $answer['Envelope']['#']['Body'][0]['#']['getOperationHistoryResponse'][0]['#']['OperationHistoryData'][0]['#']['historyRecord'];

		if(!is_array($history) || empty($history))
			return null;

		if(!$lastOperation = end($history))
			return null;

		return $lastOperation;
	}

	/**
	 * @param string $trackingNumber
	 * @return string
	 */
	protected function createDescription($trackingNumber)
	{
		$link = 'https://pochta.ru/tracking#'.$trackingNumber;
		return Loc::getMessage('SALE_DELIVERY_TRACKING_RUS_POST_STATUS_DESCR').': '.'<a href="'.$link.'">'.$link.'</a>';
	}

	/**
	 * @param $lastOperation
	 * @return int
	 */
	protected function extractStatus(array $lastOperation)
	{
		if(!isset($lastOperation['#']['OperationParameters']['0']['#']['OperType']['0']['#']['Id']['0']['#']))
			return Statuses::UNKNOWN;

		if(!isset($lastOperation['#']['OperationParameters'][0]['#']['OperAttr'][0]['#']['Id'][0]['#']))
			return Statuses::UNKNOWN;

		$oper = $lastOperation['#']['OperationParameters'][0]['#']['OperType'][0]['#']['Id'][0]['#'];
		$att = $lastOperation['#']['OperationParameters'][0]['#']['OperAttr'][0]['#']['Id'][0]['#'];

		return $this->mapStatus($oper, $att);
	}

	/**
	 * Maps outer operationCode and attributeCode to inner status enumerated in class Statuses
	 * @param $oper
	 * @param $attr
	 * @return int
	 */
	protected function mapStatus($oper, $attr)
	{
		if($oper == '')
			return Statuses::UNKNOWN;

		/*
		 * if innerStatus1 != innerStatus2 != .......
		 *
		 * opCode1 => array (
		 * 		attrCode1 => innerStatus1
		 * 		attrCode2 => innerStatus2
		 * 		...
		 * )
		 *
		 * if innerStatus1 == innerStatus2 == .......
		 *
		 * opCode => innerStatus
		 *
		 */
		$rusPostStatuses = array(
			1 => Statuses::WAITING_SHIPMENT,
			2 => array(
				1 => Statuses::HANDED,
				2 => Statuses::RETURNED,
				3 => Statuses::HANDED,
				4 => Statuses::RETURNED,
				5 => Statuses::HANDED,
				6 => Statuses::HANDED,
				7 => Statuses::RETURNED,
				8 => Statuses::HANDED,
				9 => Statuses::RETURNED,
				10 => Statuses::HANDED,
				11 => Statuses::HANDED,
				12 => Statuses::HANDED,
			),
			3 => Statuses::PROBLEM,
			4 => Statuses::ON_THE_WAY,
			5 => array(
				1 => Statuses::PROBLEM,
				2 => Statuses::PROBLEM,
				3 => Statuses::PROBLEM,
				8 => Statuses::PROBLEM,
				9 => Statuses::PROBLEM
			),
			6 => array(
				1 => Statuses::ARRIVED,
				2 => Statuses::ARRIVED,
				3 => Statuses::ARRIVED,
				4 => Statuses::ARRIVED,
				5 => Statuses::ON_THE_WAY,
			),
			7 => Statuses::PROBLEM,
			8 => array(
				0 => Statuses::ON_THE_WAY,
				1 => Statuses::ON_THE_WAY,
				2 => Statuses::ARRIVED,
				3 => Statuses::ON_THE_WAY,
				4 => Statuses::ON_THE_WAY,
				5 => Statuses::ON_THE_WAY,
				6 => Statuses::ON_THE_WAY,
				7 => Statuses::ON_THE_WAY,
				8 => Statuses::ON_THE_WAY,
				9 => Statuses::ARRIVED,
				10 => Statuses::ARRIVED,
				11 => Statuses::ON_THE_WAY,
				12 => Statuses::ARRIVED,
				13 => Statuses::ON_THE_WAY,
				14 => Statuses::ARRIVED,
				15 => Statuses::ON_THE_WAY,
				16 => Statuses::ON_THE_WAY,
				17 => Statuses::ON_THE_WAY,
				18 => Statuses::ON_THE_WAY,
				19 => Statuses::ON_THE_WAY,

			),
			9 => Statuses::ON_THE_WAY,
			10 => Statuses::ON_THE_WAY,
			11 => Statuses::ON_THE_WAY,
			12 => array(
				1 => Statuses::ARRIVED,
				2 => Statuses::ARRIVED,
				3 => Statuses::PROBLEM,
				4 => Statuses::PROBLEM,
				5 => Statuses::PROBLEM,
				6 => Statuses::PROBLEM,
				7 => Statuses::PROBLEM,
				8 => Statuses::PROBLEM,
				9 => Statuses::ARRIVED,
				10 => Statuses::PROBLEM,
				11 => Statuses::ARRIVED,
				12 => Statuses::PROBLEM,
				13 => Statuses::PROBLEM,
				14 => Statuses::PROBLEM,
				15 => Statuses::ARRIVED,
				16 => Statuses::PROBLEM,
				17 => Statuses::ARRIVED,
				18 => Statuses::ARRIVED,
				19 => Statuses::PROBLEM,
				20 => Statuses::PROBLEM,
				21 => Statuses::PROBLEM,
				22 => Statuses::ARRIVED,
				23 => Statuses::PROBLEM,
				24 => Statuses::PROBLEM,
				25 => Statuses::ARRIVED,
				26 => Statuses::PROBLEM,
				27 => Statuses::ARRIVED,
				28 => Statuses::PROBLEM,
			),
			13 => Statuses::ON_THE_WAY,
			14 => Statuses::ON_THE_WAY,
			15 => Statuses::ARRIVED,
			16 => Statuses::PROBLEM,
			17 => Statuses::ARRIVED,
			18 => Statuses::PROBLEM,
			19 => Statuses::ON_THE_WAY,
			20 => Statuses::ON_THE_WAY,
			21 => Statuses::ON_THE_WAY,
			22 => Statuses::PROBLEM,
			23 => Statuses::ON_THE_WAY,
			24 => Statuses::PROBLEM,
			25 => Statuses::ON_THE_WAY,
			26 => Statuses::PROBLEM,
			27 => Statuses::ON_THE_WAY,
			28 => Statuses::NO_INFORMATION,
			29 => Statuses::ON_THE_WAY,
			30 => Statuses::ON_THE_WAY,
			31 => Statuses::ON_THE_WAY,
			32 => Statuses::ON_THE_WAY,
			33 => Statuses::ON_THE_WAY,
		);

		if(!isset($rusPostStatuses[$oper]))
			return Statuses::UNKNOWN;

		if(!is_array($rusPostStatuses[$oper]))
			return $rusPostStatuses[$oper];

		if($attr == '')
			return Statuses::UNKNOWN;

		if(!isset($rusPostStatuses[$oper][$attr]))
			return Statuses::UNKNOWN;

		return $rusPostStatuses[$oper][$attr];
	}
}
