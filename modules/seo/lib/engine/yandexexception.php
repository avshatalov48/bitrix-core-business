<?php
namespace Bitrix\Seo\Engine;

use Bitrix\Main\Web;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

Loc::loadMessages(dirname(__FILE__).'/../../seo_search.php');

class YandexException
	extends \Exception
{
	protected $code;
	protected $message;
	
	protected $result;
	protected $status;
	
	public function __construct($queryResult, \Exception $previous = NULL)
	{
//		exception use two classes - new and old. Define them
		if ($queryResult)
		{
			if ($queryResult instanceof \CHTTP)
			{
				$this->result = $queryResult->result;
				$this->status = $queryResult->status;
			}
			elseif ($queryResult instanceof Web\HttpClient)
			{
				$this->result = $queryResult->getResult();
				$this->status = $queryResult->getStatus();
			}
		}
		
		if (!$queryResult)
		{
			parent::__construct('no result', 0, $previous);
		}
		elseif ($this->parseError())
		{
			$this->formatMessage();	//format and try translate message
			parent::__construct($this->message, $this->status, $previous);
		}
		else
		{
			parent::__construct($this->result, $this->status, $previous);
		}
	}
	
	public function getStatus()
	{
		return $this->status;
	}
	
	protected function parseError()
	{
		$matches = array();
//		old style dbg: maybe delete? In new webmaster API this format not using already
		if (preg_match("/<error code=\"([^\"]+)\"><message>([^<]+)<\/message><\/error>/", $this->result, $matches))
		{
			$this->code = $matches[1];
			$this->message = $matches[2];
			
//			Try translate error. If unknown error - write as is
			$codeTranslated = Loc::getMessage('YANDEX_ERROR__'.str_replace(' ','_',ToUpper($this->code)));
			$messageTranslated = Loc::getMessage('YANDEX_ERROR__'.str_replace(' ','_',ToUpper($this->message)));
			$this->code = (strlen($codeTranslated) > 0) ? $codeTranslated : $this->code;
			$this->message = (strlen($messageTranslated) > 0) ? $messageTranslated : $this->message;
			
			return true;
		}
		
//		new style
		if ($resultArray = Json::decode($this->result))
		{
			if (array_key_exists('error_code', $resultArray))
				$this->code = $resultArray["error_code"];
			if (array_key_exists('error_message', $resultArray))
				$this->message = $resultArray["error_message"];
			
			return true;
		}
		
		return false;
	}
	
	private function formatMessage()
	{
		$translateString = Loc::getMessage('SEO_ERROR_'.$this->code);
		if(strlen($translateString) > 0)
		{
			$this->message = $translateString.' ('.Loc::getMessage('SEO_ERROR_CODE').': '.$this->code.').';
		}
		else
		{
			$this->message = $this->code . ': ' . $this->message;
		}
	}
}
