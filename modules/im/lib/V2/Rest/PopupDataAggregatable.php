<?php

namespace Bitrix\Im\V2\Rest;

/**
 * An interface for classes that have entities within them that should be taken to the top level of a REST response.
 * For example, users in messages.
 */
interface PopupDataAggregatable
{
	/**
	 * Returns the data to be raised to the top of the REST response. Works on the principle of the composite pattern.
	 * @see RestAdapter::toRestFormat()
	 * @param string[] $excludedList
	 * @return PopupData
	 */
	public function getPopupData(array $excludedList = []): PopupData;
}