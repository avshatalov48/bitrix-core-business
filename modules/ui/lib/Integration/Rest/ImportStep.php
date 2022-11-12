<?php
namespace Bitrix\UI\Integration\Rest;

use \Bitrix\Main;
use \Bitrix\Rest;


abstract class ImportStep {
	//region input info
	public string $entityCode;
	public Main\Type\Dictionary $data;
	public Main\Type\Dictionary $previousStep;
	public Rest\Configuration\Structure $structure;
	//end region
	//region OutputInfo
	public Main\Type\Dictionary $nextStep;
	public Main\ErrorCollection $errorCollection;
	//endregion
	public function __construct(Main\Event $event)
	{
		$this->entityCode = $event->getParameter('CODE');
		$this->data = new Main\Type\Dictionary($event->getParameter('CONTENT')['DATA']);
		$this->structure = new Rest\Configuration\Structure($event->getParameter('CONTEXT_USER'));

		$this->previousStep = new Main\Type\Dictionary($event->getParameter('CONTENT')['RATIO']);
		$this->nextStep = new Main\Type\Dictionary();
		$this->errorCollection = new Main\ErrorCollection();

		$this->init($event);
	}

	abstract public function init($event): void;

	abstract public function makeAStep(): void;

	public function makeAnAnswer(): ?array
	{
		return [
			'RATIO' => $this->nextStep->toArray(),
		] + (($error = $this->errorCollection->current()) ? [
			'ERROR_MESSAGE' => $error->getMessage(),
			'ERROR_ACTION' => $error->getCode(),
		] : []);
	}

	public static function fulfill(Main\Event $event): array
	{
		$step = new static($event);
		if ($step->errorCollection->isEmpty())
		{
			$step->makeAStep();
		}
		return $step->makeAnAnswer();
	}
}