<?

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class SenderMessageAudioComponent extends CBitrixComponent
{
	/** @var ErrorCollection $errors */
	protected $errors;

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	public function onPrepareComponentParams($arParams)
	{
		if (!$arParams['INPUT_NAME'])
			$arParams['INPUT_NAME'] = 'FILE';

		if (!$arParams['CONTROL_ID'])
			$arParams['CONTROL_ID'] = 'FILE';

		if (!$arParams['INPUT_ID'])
			$arParams['INPUT_ID'] = 'FILE';

		return $arParams;
	}

	protected function getAudioFileData($json)
	{
		$audioFile = (new \Bitrix\Sender\Integration\VoxImplant\Audio())
			->withJsonString($json)
			->withMessageCode($this->arParams['MESSAGE_CODE']);

		return [
			'VALUE' => $audioFile->createdFromPreset() ? $audioFile->getPreset() : $audioFile->getFileId(),
			'FILE_PATH' => $json ? $audioFile->getFileUrl() : $audioFile->getDefaultFileUrl(),
			'CREATED_FROM_PRESET' => $audioFile->createdFromPreset()
		];
	}

	public function executeComponent()
	{
		$this->errors = new \Bitrix\Main\ErrorCollection();
		if (!Bitrix\Main\Loader::includeModule('sender'))
		{
			$this->errors->setError(new Error('Module `sender` is not installed.'));
			$this->printErrors();
			return;
		}
		Loader::includeModule('fileman');

		$this->arResult['AUDIO_FILE'] = $this->getAudioFileData($this->arParams['~VALUE']);

		$this->includeComponentTemplate();
	}
}