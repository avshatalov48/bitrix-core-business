<?php

namespace Bitrix\Seo\Conversion;

interface ConversionObjectInterface
{
	/**
	 * @return string
	 */
	public function getType() : string;

	/**
	 * @return bool
	 */
	public function isAvailable() : bool;
	/**
	 * @param ConversionEventInterface $event
	 *
	 * @return $this
	 */
	public function addEvent(ConversionEventInterface $event) : self;

	/**
	 * @return ConversionEventInterface[]
	 */
	public function getEvents() : array;

	/**
	 * @return mixed
	 */
	public function fireEvents() : bool ;
}