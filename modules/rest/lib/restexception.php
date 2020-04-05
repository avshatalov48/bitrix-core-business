<?
namespace Bitrix\Rest;

class RestException
	extends \Exception
{
	const ERROR_INTERNAL_WRONG_TRANSPORT = 'INTERNAL_WRONG_TRANSPORT';
	const ERROR_INTERNAL_WRONG_HANDLER_CLASS = 'INTERNAL_WRONG_HANDLER_CLASS';
	const ERROR_INTERNAL_WRONG_FILE_HANDLER = 'INTERNAL_WRONG_FILE_HANDLER';
	const ERROR_INTERNAL_PORTAL_DELETED = 'PORTAL_DELETED';

	const ERROR_OAUTH = 'ERROR_OAUTH';
	const ERROR_METHOD_NOT_FOUND = 'ERROR_METHOD_NOT_FOUND';
	const ERROR_CORE = 'ERROR_CORE';
	const ERROR_ARGUMENT = 'ERROR_ARGUMENT';
	const ERROR_NOT_FOUND = 'ERROR_NOT_FOUND';

	protected $status;
	protected $error_code;
	protected $error_additional = array();

	public function __construct($message, $code = "", $status = 0, \Exception $previous = null)
	{
		$this->status = $status;
		$this->error_code = $code;
		parent::__construct($message, intval($code), $previous);
	}

	public function getErrorCode()
	{
		return $this->error_code;
	}

	public function setErrorCode($error_code)
	{
		$this->error_code = $error_code;
		$this->code = intval($error_code);
	}

	public function getStatus()
	{
		return $this->status == 0 ? \CRestServer::STATUS_WRONG_REQUEST : $this->status;
	}

	public function setStatus($status)
	{
		$this->status = $status;
	}

	public function setMessage($msg)
	{
		$this->message = $msg;
	}

	public function getAdditional()
	{
		return $this->error_additional;
	}

	public function setAdditional($error_additional)
	{
		$this->error_additional = $error_additional;
	}

	public function setApplicationException(\CApplicationException $ex)
	{
		if($ex->getId())
		{
			$this->setErrorCode($ex->getId());
		}
		else
		{
			$this->setErrorCode(self::ERROR_CORE);
		}

		$this->message = $ex->getString();
	}

	public static function initFromException(\Exception $e)
	{
		$ex = null;

		if (is_a($e, '\Bitrix\Main\DB\SqlException'))
		{
			$ex = new self(
				"SQL query error!",
				self::ERROR_CORE,
				\CRestServer::STATUS_INTERNAL,
				$e->getPrevious()
			);
		}
		elseif(is_a($e, '\Bitrix\Main\SystemException'))
		{
			if(is_a($e, '\Bitrix\Main\ArgumentException'))
			{
				$ex = new self(
					$e->getMessage(),
					self::ERROR_ARGUMENT,
					\CRestServer::STATUS_WRONG_REQUEST,
					$e->getPrevious()
				);

				$ex->setAdditional(array(
					"argument" => $e->getParameter(),
				));
			}
		}

		if(!$ex)
		{
			$ex = new self(
				$e->getMessage(),
				$e->getCode() ? $e->getCode() : self::ERROR_CORE,
				\CRestServer::STATUS_WRONG_REQUEST,
				$e->getPrevious()
			);
		}

		return $ex;
	}
}
?>