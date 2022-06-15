<?php
IncludeModuleLangFile(__FILE__);

abstract class CBitrixCloudWebService
{
	private $debug = false;
	private $timeout = 0;
	private $server = /*.(Bitrix\Main\Web\HttpClient).*/ null;
	/**
	 * Returns URL to update policy
	 *
	 * @param array[string]string $arParams
	 * @return string
	 *
	 */
	protected abstract function getActionURL($arParams = /*.(array[string]string).*/ array());
	/**
	 * Returns action response XML
	 *
	 * @param string $action
	 * @return CDataXML
	 * @throws CBitrixCloudException
	 */
	protected function action($action)
	{
		$url = $this->getActionURL(array(
			"action" => $action,
			"debug" => ($this->debug? "y": "n"),
		));

		$this->server = new Bitrix\Main\Web\HttpClient(array(
			"redirect" => true,
		));
		if ($this->timeout > 0)
			$this->server->setTimeout($this->timeout);

		$strXML = $this->server->get($url);
		if ($strXML === false || !$this->server->getStatus())
		{
			$errors = $this->server->getError();
			throw new CBitrixCloudException(GetMessage("BCL_CDN_WS_SERVER", array(
				"#STATUS#" => $errors? implode(" ", $errors): "-1",
			)), "");
		}

		if ($this->server->getStatus() != 200)
		{
			throw new CBitrixCloudException(GetMessage("BCL_CDN_WS_SERVER", array(
				"#STATUS#" => (string)$this->server->getStatus(),
			)), "");
		}

		$obXML = new CDataXML;
		if (!$obXML->LoadString($strXML))
		{
			throw new CBitrixCloudException(GetMessage("BCL_CDN_WS_XML_PARSE", array(
				"#CODE#" => "1",
			)), "");
		}

		$node = $obXML->SelectNodes("/error/code");
		if (is_object($node))
		{
			$error_code = $node->textContent();
			$message_id = "BCL_CDN_WS_".$error_code;
			/*
			GetMessage("BCL_CDN_WS_LICENSE_EXPIRE");
			GetMessage("BCL_CDN_WS_LICENSE_NOT_FOUND");
			GetMessage("BCL_CDN_WS_QUOTA_EXCEEDED");
			GetMessage("BCL_CDN_WS_CMS_LICENSE_NOT_FOUND");
			GetMessage("BCL_CDN_WS_DOMAIN_NOT_REACHABLE");
			GetMessage("BCL_CDN_WS_LICENSE_DEMO");
			GetMessage("BCL_CDN_WS_LICENSE_NOT_ACTIVE");
			GetMessage("BCL_CDN_WS_NOT_POWERED_BY_BITRIX_CMS");
			GetMessage("BCL_CDN_WS_WRONG_DOMAIN_SPECIFIED");
			*/

			$debug_content = "";
			$node = $obXML->SelectNodes("/error/debug");
			if(is_object($node))
				$debug_content = $node->textContent();

			if (HasMessage($message_id))
			{
				throw new CBitrixCloudException(GetMessage($message_id), $error_code, $debug_content);
			}
			else
			{
				throw new CBitrixCloudException(GetMessage("BCL_CDN_WS_SERVER", array(
					"#STATUS#" => $error_code,
				)), $error_code, $debug_content);
			}
		}

		return $obXML;
	}
	/**
	 * Sets debug mode for remote service.
	 * Returns previous mode value.
	 *
	 * @param bool $bActive
	 * @return bool
	 *
	 */
	public function setDebug($bActive)
	{
		$result = $this->debug;
		$this->debug = ($bActive === true);
		return $result;
	}
	/**
	 *
	 * @param int $timeout
	 * @return int
	 *
	 */
	public function setTimeout($timeout)
	{
		$this->timeout = $timeout > 0? intval($timeout): 0;
		return $this->timeout;
	}
	/**
	 * Returns remote server status.
	 * Return null if no action was performed.
	 *
	 * @return mixed
	 *
	 */
	public function getServerStatus()
	{
		return isset($this->server)? $this->server->getStatus(): null;
	}
	/**
	 * Returns remote server response body.
	 * Return null if no action was performed.
	 *
	 * @return mixed
	 *
	 */
	public function getServerResult()
	{
		return isset($this->server)? $this->server->getResult(): null;
	}
}
