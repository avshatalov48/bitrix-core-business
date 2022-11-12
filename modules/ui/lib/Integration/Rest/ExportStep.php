<?php

namespace Bitrix\UI\Integration\Rest;

use \Bitrix\Main;


abstract class ExportStep {
	//region input info
	public string $entityCode;
	public $entityId;
	public Main\Type\Dictionary $previous;
	public int $stepNumber;
	//end region
	//region OutputInfo
	public Main\Type\Dictionary $data;
	public Main\Type\Dictionary $files;
	public Main\Type\Dictionary $next;
	public Main\ErrorCollection $errorCollection;
	//endregion
	public function __construct(Main\Event $event)
	{
		$this->entityCode = $event->getParameter('CODE');
		$this->entityId = $event->getParameter('ITEM_CODE');
		$this->stepNumber = (int) $event->getParameter('STEP');
		$data = json_decode($event->getParameter('NEXT') ?: '', true);
		$this->previousStep = new Main\Type\Dictionary(($data ?: []));

		$this->data = new Main\Type\Dictionary();
		$this->files = new Main\Type\Dictionary();
		$this->nextStep = new Main\Type\Dictionary();
		$this->errorCollection = new Main\ErrorCollection();

		$this->init();
	}

	abstract public function init(): void;

	public function makeAnAnswer(): ?array
	{
		return [
			'FILE_NAME' =>
				implode('_',
					[
						(new \ReflectionClass(static::class))->getShortName(),
						$this->entityCode,
						$this->entityId,
						$this->stepNumber
					]
				),
			'CONTENT' => $this->data->toArray(),
			'FILES' => $this->files->toArray(),
			'NEXT' => $this->nextStep->count() <= 0
				? false : json_encode($this->nextStep->toArray()),

		] + (($error = $this->errorCollection->current()) ? [
			'ERROR_MESSAGE' => $error->getMessage(),
			'ERROR_ACTION' => $error->getCode(),
		] : []);
	}

	public static function fulfill(Main\Event $event): array
	{
		$step = new static($event);
		return $step->makeAnAnswer();
	}
}