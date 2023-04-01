<?

namespace Bitrix\Seo\WebHook;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Bitrix\Main\Web\Json;
use Bitrix\Seo\Engine\Bitrix as EngineBitrix;

/**
 * Class Service
 * @package Bitrix\Seo\WebHook
 */
class Service
{
	const ANSWER_ERROR_SYSTEM = '001';
	const ANSWER_ERROR_NO_CODE = '002';
	const ANSWER_ERROR_NO_EXT_ID = '003';
	const ANSWER_ERROR_NO_PAYLOAD = '004';
	const ANSWER_ERROR_NO_SEC_CODE = '005';
	const ANSWER_ERROR_WRONG_SEC_CODE = '006';

	/** @var  ErrorCollection $errorCollection Error collection. */
	protected $errorCollection;

	protected $type;

	protected $externalId;

	/** @var Payload\Batch $payload Payload instance. */
	protected $payload;

	protected $errors = array();

	protected $data = null;

	/**
	 * Create instance.
	 *
	 * @param string $type Type.
	 * @param string $externalId External ID.
	 * @return static
	 */
	public static function create($type, $externalId)
	{
		return new static($type, $externalId);
	}

	/**
	 * Service constructor.
	 *
	 * @param string $type Type.
	 * @param string $externalId External ID.
	 */
	public function __construct($type, $externalId)
	{
		$this->errorCollection = new ErrorCollection();

		$this->type = $type;
		$this->externalId = $externalId;

		$this->data = self::getData($this->type, $this->externalId);
	}

	protected static function answer(array $answer)
	{
		/** @var \CMain $APPLICATION */
		global $APPLICATION;
		$APPLICATION->restartBuffer();
		header('Content-Type:application/json; charset=UTF-8');

		echo Json::encode($answer);

		\CMain::finalActions();
		exit;
	}

	protected static function answerError($code = null, $text = null)
	{
		if (!$code)
		{
			$code = self::ANSWER_ERROR_SYSTEM;
		}

		if (!$text)
		{
			$errorMessages = array(
				self::ANSWER_ERROR_SYSTEM => 'Error.',
				self::ANSWER_ERROR_NO_CODE => 'Parameter `code` not found.',
				self::ANSWER_ERROR_NO_EXT_ID => 'Parameter `externalId` not found.',
				self::ANSWER_ERROR_NO_SEC_CODE => 'Parameter `sec` not found.',
				self::ANSWER_ERROR_WRONG_SEC_CODE => 'Wrong `sec` parameter.',
			);

			$text = $errorMessages[$code];
		}

		self::answer(array(
			'error' => array('code' => $code, 'text' => $text),
			'data' => array()
		));
	}

	protected static function answerData(array $data = array())
	{
		self::answer(array(
			'error' => false,
			'data' => $data
		));
	}

	/**
	 * Listen web hooks.
	 *
	 * @return void
	 */
	public static function listen()
	{
		$request = Context::getCurrent()->getRequest();
		$type = $request->get('code');
		if (!$type)
		{
			self::answerError(self::ANSWER_ERROR_NO_CODE);
			return;
		}

		$securityCode = $request->get('sec');
		if (!$securityCode)
		{
			self::answerError(self::ANSWER_ERROR_NO_SEC_CODE);
			return;
		}
		$externalId = $request->get('externalId');
		if (!$externalId)
		{
			self::answerError(self::ANSWER_ERROR_NO_EXT_ID);
			return;
		}

		try
		{
			$payload = Json::decode($request->get('payload'));
			$payload = (new Payload\Batch())->setArray($payload);
		}
		catch (ArgumentException $e)
		{
			self::answerError(self::ANSWER_ERROR_NO_PAYLOAD);
			return;
		}
		$instance = self::create($type, $externalId);
		if (!$instance->checkSecurityCode($securityCode))
		{
			self::answerError(self::ANSWER_ERROR_WRONG_SEC_CODE);
		}

		try
		{
			$instance->handle($payload);
		}
		catch (\Exception $e)
		{
			self::answerError($e->getCode(), $e->getMessage());
			return;
		}



		foreach ($instance->getErrorCollection()->toArray() as $error)
		{
			/** @var Error $error Error. */
			self::answerError($error->getCode(), $error->getMessage());
		}

		self::answerData();
	}

	/**
	 * Handle web hook.
	 *
	 * @param Payload\Batch $payload Payload instance.
	 * @return $this
	 */
	public function handle(Payload\Batch $payload)
	{
		$this->payload = $payload;
		$this->sendEvent();

		return $this;
	}

	/**
	 * Register web hook.
	 *
	 * @param array $parameters Parameters.
	 * @return bool
	 */
	public function register(array $parameters = [])
	{
		if (!$this->data)
		{
			$addParameters = [
				'TYPE' => $this->type,
				'EXTERNAL_ID' => $this->externalId,
			];
			if (!empty($parameters['SECURITY_CODE']))
			{
				$addParameters['SECURITY_CODE'] = $parameters['SECURITY_CODE'];
			}
			$addResult = Internals\WebHookTable::add($addParameters);
			if (!$addResult->isSuccess())
			{
				return false;
			}

			$this->data = self::getData($this->type, $this->externalId);
		}

		$result = self::queryHookRegister(
			'seo.client.webhook.register',
			array(
				'CODE' => $this->data['TYPE'],
				'EXTERNAL_ID' => $this->data['EXTERNAL_ID'],
				'SECURITY_CODE' => $this->data['SECURITY_CODE'],
				'CONFIRMATION_CODE' => isset($parameters['CONFIRMATION_CODE']) ?
					$parameters['CONFIRMATION_CODE']
					:
					null,
			)
		);

		return $result;
	}

	public static function registerForm($formId)
	{
		return self::queryHookRegister(
			'seo.client.form.register',
			[
				'FORM_ID' => $formId,
			]
		);
	}

	public static function unregisterForm($formId)
	{
		return self::queryHookRegister(
			'seo.client.form.unregister',
			[
				'FORM_ID' => $formId,
			]
		);
	}

	/**
	 * Remove web hook.
	 *
	 * @return bool
	 */
	public function remove()
	{
		$result = self::queryHookRegister(
			'seo.client.webhook.remove',
			array(
				'CODE' => $this->type,
				'EXTERNAL_ID' => $this->externalId
			)
		);

		if ($result)
		{
			if ($this->data)
			{
				$deleteResult = Internals\WebHookTable::delete($this->data['ID']);
				$result = $deleteResult->isSuccess();
			}
		}

		return $result;
	}

	protected static function getData($type, $externalId)
	{
		$list = Internals\WebHookTable::getList(array(
			'filter' => array(
				'=TYPE' => $type,
				'=EXTERNAL_ID' => $externalId,
			)
		));

		return $list->fetch();
	}

	protected static function queryHookRegister($methodName, array $parameters)
	{
		$engine = new EngineBitrix();
		if (!$engine->isRegistered())
		{
			return false;
		}

		$response = $engine->getInterface()->getTransport()->call($methodName, $parameters);
		return (isset($response['result']['RESULT']) && $response['result']['RESULT']);
	}

	/**
	 * Check security code.
	 *
	 * @param string $securityCode Code.
	 * @return bool
	 */
	public function checkSecurityCode($securityCode)
	{
		return ($this->data && $this->data['SECURITY_CODE'] === $securityCode);
	}

	/**
	 * Get error collection.
	 *
	 * @return ErrorCollection
	 */
	public function getErrorCollection()
	{
		return $this->errorCollection;
	}

	protected function sendEvent()
	{
		$event = new Event('seo', 'OnWebHook', array(
			'PAYLOAD' => $this->payload,
		));
		EventManager::getInstance()->send($event);
		foreach ($event->getResults() as $result)
		{
			$parameters = $result->getParameters();
			if (!empty($parameters['ERROR_COLLECTION']))
			{
				/** @var ErrorCollection $resultErrorCollection */
				$resultErrorCollection = $parameters['ERROR_COLLECTION'];
				$this->errorCollection->add($resultErrorCollection->toArray());
			}
		}
	}
}
