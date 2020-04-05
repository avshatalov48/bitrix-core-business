<?php
namespace Bitrix\Socialnetwork\Copy\Integration;

interface Helper
{
	/**
	 * Returns a module id for work with options.
	 * @return string
	 */
	public function getModuleId();

	/**
	 * Returns a map of option names.
	 *
	 * @return array [
		"queue" => "queueOption",
		"checker" => "checkerOption",
		"stepper" => "stepperOption",
		"error" => "errorOption"
		]
	 */
	public function getOptionNames();

	/**
	 * Returns a link to stepper class.
	 * @return string
	 */
	public function getLinkToStepperClass();

	/**
	 * Returns a text map.
	 * @return array [
	 * 	"title" => "Text title",
	 * 	"error" => "Error title"
	 * ]
	 */
	public function getTextMap();
}