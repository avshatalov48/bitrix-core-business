<?
namespace Bitrix\Sale\Delivery\Requests;

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;

/**
 * Class Result
 * @package Bitrix\Sale\Delivery\Requests
 */
class Result extends \Bitrix\Main\Result
{
	protected $messages = array();
	/** @var RequestResult[] || ShipmentResult[]  */
	protected $results = array();

	/**
	 * Result constructor.
	 */
	public function __construct()
	{
		$this->messages = new MessagesCollection();
		parent::__construct();
	}

	/**
	 * @param Message $message
	 */
	public function addMessage(Message $message)
	{
		$this->messages[] = $message;
	}

	/**
	 * @return Message[]
	 */
	public function getMessages()
	{
		return $this->messages->toArray();
	}

	/**
	 * @return array|MessagesCollection
	 */
	public function getMessagesCollection()
	{
		return $this->messages;
	}

	/**
	 * @return array
	 */
	public function getMessagesMessages()
	{
		$messages = array();

		foreach($this->getMessages() as $message)
			$messages[] = $message->getMessage();

		return $messages;
	}

	/**
	 * Adds array of Message objects
	 *
	 * @param Message[] $messages
	 * @return $this
	 */
	public function addMessages(array $messages)
	{
		$this->isSuccess = false;
		$this->errors->add($messages);
		return $this;
	}


	/**
	 * @return array
	 */
	public function getResults()
	{
		return $this->results;
	}

	/**
	 * @return ShipmentResult[]
	 */
	public function getShipmentResults()
	{
		$result = array();

		foreach($this->results as $res)
			if($res instanceof ShipmentResult)
				$result[] = $res;

		return $result;
	}

	/**
	 * @return RequestResult[]
	 */
	public function getRequestResults()
	{
		$result = array();

		foreach($this->results as $res)
			if($res instanceof RequestResult)
				$result[] = $res;

		return $result;
	}

	/**
	 * @param array $results
	 */
	public function setResults($results)
	{
		$this->results = $results;
	}

	/**
	 * @param Result
	 */
	public function addResult(Result $result)
	{
		$this->results[] = $result;
	}

	/**
	 * @param Result[] $results
	 */
	public function addResults(array $results)
	{
		foreach($results as $result)
			$this->results[] = $result;
	}
}

class MessagesCollection extends ErrorCollection {};
class Message extends Error{};