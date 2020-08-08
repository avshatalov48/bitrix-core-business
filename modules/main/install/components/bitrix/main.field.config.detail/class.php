<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Engine;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;
use Bitrix\Main\UserField\ConfigComponent;
use Bitrix\Main\UserField\Engine\SettingsArea;
use Bitrix\Main\UserFieldLangTable;
use Bitrix\Main\UserFieldTable;
use Bitrix\UI\Toolbar\Facade\Toolbar;

class MainUfDetailComponent extends ConfigComponent implements Engine\Contract\Controllerable
{
	protected const DEFAULT_USER_TYPE_ID = 'string';
	protected const DEFAULT_SORT = 100;

	protected $fieldId;
	protected $data;

	protected function init(): void
	{
		parent::init();

		if(!$this->errorCollection->isEmpty())
		{
			return;
		}

		$fieldId = (int) ($this->arParams['fieldId'] ?: Application::getInstance()->getContext()->getRequest()->get('fieldId'));
		if($fieldId > 0)
		{
			if(!$this->access->canRead($fieldId))
			{
				$this->errorCollection[] = $this->getAccessDeniedError();
				return;
			}
			$this->data = UserFieldTable::getFieldData($fieldId);
			if(!is_array($this->data))
			{
				$this->errorCollection[] = new Error(Loc::getMessage('MAIN_FIELD_CONFIG_DETAIL_FIELD_NOT_FOUND_ERROR'));
			}
		}
		else
		{
			$this->entityId = $this->arParams['entityId'] ?: Application::getInstance()->getContext()->getRequest()->get('entityId');
			if(empty($this->entityId))
			{
				$this->errorCollection[] = new Error(Loc::getMessage('MAIN_FIELD_CONFIG_DETAIL_NO_ENTITY_ID_ERROR'));
			}
		}
	}

	public function executeComponent()
	{
		$this->init();
		if(!$this->errorCollection->isEmpty())
		{
			$this->arResult['errors'] = $this->errorCollection->toArray();
			$this->includeComponentTemplate();

			return;
		}
		if(Loader::includeModule('ui'))
		{
			Toolbar::deleteFavoriteStar();
		}

		$this->arResult['field'] = $this->prepareField();
		$this->arResult['form'] = $this->prepareForm($this->arResult['field']);
		$this->arResult['types'] = $this->getUserTypes();
		$this->arResult['jsParams'] = [
			'id' => $this->arResult['field']['ID'],
			'moduleId' => $this->moduleId,
		];

		if($this->arResult['field']['ID'] > 0)
		{
			$this->arResult['title'] = Loc::getMessage('MAIN_FIELD_CONFIG_DETAIL_TITLE_EDIT');
		}
		else
		{
			$this->arResult['title'] = Loc::getMessage('MAIN_FIELD_CONFIG_DETAIL_TITLE_ADD');
		}
		$this->setTitle($this->arResult['title']);

		$this->includeComponentTemplate();
	}

	protected function prepareField(): array
	{
		if(is_array($this->data))
		{
			return $this->data;
		}

		return [
			'MANDATORY' => 'N',
			'MULTIPLE' => 'N',
			'SHOW_FILTER' => 'E',
			'SHOW_IN_LIST' => 'Y',
			'IS_SEARCHABLE' => 'Y',
			'ENTITY_ID' => $this->entityId,
			'USER_TYPE_ID' => static::DEFAULT_USER_TYPE_ID,
			'FIELD_NAME' => $this->generateFieldName(),
			'SORT' => static::DEFAULT_SORT,
			'EDIT_FORM_LABEL' => [
				Loc::getCurrentLang() => Loc::getMessage('MAIN_FIELD_CONFIG_DETAIL_DEFAULT_LABEL'),
			],
		];
	}

	protected function prepareForm(array $field): array
	{
		$form = [];
		$entity = UserFieldTable::getEntity();
		$labelsEntity = UserFieldLangTable::getEntity();

		$labelTitle = $labelsEntity->getField('EDIT_FORM_LABEL')->getTitle();
		$languages = $this->getLanguages();
		foreach($languages as $language)
		{
			$form['editFormLabel'][] = [
				'label' => $labelTitle,
				'language' => $language,
			];
		}

		$form['fieldName'] = [
			'label' => $entity->getField('FIELD_NAME')->getTitle(),
			'prefix' => $this->getFieldPrefix(),
		];
		$form['sort'] = [
			'label' => $entity->getField('SORT')->getTitle(),
		];
		$form['multiple'] = [
			'label' => $entity->getField('MULTIPLE')->getTitle(),
		];
		$form['mandatory'] = [
			'label' => $entity->getField('MANDATORY')->getTitle(),
		];
		$form['showFilter'] = [
			'label' => $entity->getField('SHOW_FILTER')->getTitle(),
		];
		$form['isSearchable'] = [
			'label' => $entity->getField('IS_SEARCHABLE')->getTitle(),
		];
		$form['userTypeId'] = [
			'label' => $entity->getField('USER_TYPE_ID')->getTitle(),
		];
		$settingsArea = new SettingsArea($field);
		$form['settings'] = [
			'label' => $entity->getField('SETTINGS')->getTitle(),
			'html' => $settingsArea->getHtml(),
		];

		return $form;
	}

	protected function getLanguages(): array
	{
		$currentLanguageId = Loc::getCurrentLang();
		$isCurrentLanguageFound = false;
		$languages = [];
		$list = LanguageTable::getList([
			'order' => ['SORT' => 'ASC'],
		])->fetchAll();
		foreach($list as $item)
		{
			$language = [
				'name' => $item['NAME'],
				'id' => $item['LID'],
			];

			if($item['LID'] === $currentLanguageId)
			{
				$language['isCurrent'] = true;
				$isCurrentLanguageFound = true;
			}

			$languages[] = $language;
		}

		if(!$isCurrentLanguageFound)
		{
			$languages[0]['isCurrent'] = true;
		}

		return $languages;
	}

	public function configureActions(): array
	{
		return [];
	}

	public function getSettingsAction(string $userTypeId): Engine\Response\HtmlContent
	{
		return new Engine\Response\HtmlContent(new SettingsArea(['USER_TYPE_ID' => $userTypeId]));
	}

	protected function generateFieldName(): string
	{
		return $this->getFieldPrefix() . time();
	}

	protected function getFieldPrefix(): string
	{
		return 'UF_' . $this->entityId . '_';
	}
}