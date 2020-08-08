<?php
namespace Bitrix\Main\Engine\ActionFilter;

use Bitrix\Main\Engine;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

/**
 * Class Content
 * @package Bitrix\Main\Engine\ActionFilter
 */
final class ContentType extends Base
{
	const JSON  = 'application/json';
	const ERROR_INVALID_CONTENT_TYPE = 'invalid_content_type';

	/** @var array  */
	private $allowedTypes;

	/**
	 * ContentType filter constructor.
	 *
	 * @param array $allowedTypes Allowed types.
	 */
	public function __construct(array $allowedTypes = [])
	{
		$this->allowedTypes = $allowedTypes;
		parent::__construct();
	}

	/**
	 * Event `onBeforeAction` handler.
	 *
	 * @param Event $event Event.
	 * @return EventResult|void|null
	 */
	public function onBeforeAction(Event $event)
	{
		$contentType = $this->getRequestContentType();
		if (!in_array($contentType, $this->allowedTypes, true))
		{
			$this->addError(new Error(
				"Wrong content type for current action. `"
				. implode('`, `', $this->allowedTypes)
				. "` expected, `{$contentType}` got.",
				self::ERROR_INVALID_CONTENT_TYPE
			));

			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		switch ($this->getRequestContentType())
		{
			case self::JSON:
				$this->setActionSourceParametersToMap(new Engine\JsonPayload());
				break;
		}

		return null;
	}

	protected function setActionSourceParametersToMap(Engine\JsonPayload $payload)
	{
		$payload = $payload->getData();
		if (!is_array($payload))
		{
			return;
		}

		$parameters = [];
		foreach ($payload as $key => $value)
		{
			if ($key && !is_numeric($key))
			{
				$parameters[$key] = $value;
			}
		}

		if (!$parameters)
		{
			return;
		}

		$controller = $this->getAction()->getController();
		$controller->setSourceParametersList(array_merge(
			$controller->getSourceParametersList(),
			[$parameters]
		));
	}

	protected function getRequestContentType()
	{
		return $this->getAction()->getController()->getRequest()->getHeaders()->getContentType();
	}
}