<?php

namespace Bitrix\Main\Grid\UI\Response;

use Bitrix\Main\Grid\MessageType;
use Bitrix\Main\Grid\UI\GridResponse;
use Bitrix\Main\Result;

class GridResponseFactory
{
	/**
	 * Create grid response with error messages of result.
	 *
	 * @param Result $result
	 *
	 * @return GridResponse
	 */
	public function createFromResult(Result $result): GridResponse
	{
		$self = new GridResponse();

		foreach ($result->getErrorMessages() as $message)
		{
			$self->addMessage($message, MessageType::ERROR);
		}

		if (!empty($result->getData()))
		{
			$self->setPayload($result->getData());
		}

		return $self;
	}
}
