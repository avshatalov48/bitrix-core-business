<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Folder;
use Bitrix\Landing\Landing;
use Bitrix\Landing\Rights;
use Bitrix\Landing\Site;
use Bitrix\Main\Event;

\CBitrixComponent::includeComponentClass('bitrix:landing.base.form');

class LandingFolderEditComponent extends LandingBaseFormComponent
{
	/**
	 * Class of current element.
	 * @var string
	 */
	protected $class = 'Folder';

	/**
	 * Local version of table map with available fields for change.
	 * @return array
	 */
	protected function getMap(): array
	{
		return [
			'TITLE', 'CODE', 'INDEX_ID'
		];
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		$init = $this->init();
		$siteId = null;
		$folder = null;
		$indexId = null;

		$this->checkParam('TYPE', '');
		$this->checkParam('ACTION_FOLDER', 'folderId');
		$this->checkParam('PAGE_URL_LANDING_EDIT', '');
		$this->checkParam('PAGE_URL_LANDING_VIEW', '');
		$this->checkParam('FOLDER_ID', 0);

		// gets data and check access
		if ($init)
		{
			\Bitrix\Landing\Site\Type::setScope(
				$this->arParams['TYPE']
			);

			\Bitrix\Landing\Hook::setEditMode();
			\Bitrix\Landing\Landing::setEditMode();

			$this->id = $this->arParams['FOLDER_ID'];
			$this->arResult['FOLDER'] = $folder = $this->getRow();

			$siteId = $this->arResult['FOLDER']['SITE_ID']['STORED'] ?? 0;
			$deleted = ($this->arResult['FOLDER']['DELETED']['STORED'] ?? 'Y') === 'Y';
			$indexId = $this->arResult['FOLDER']['INDEX_ID']['STORED'] ?? 0;
			$access = Rights::hasAccessForSite($siteId, Rights::ACCESS_TYPES['edit']);

			if (!$folder || !$siteId || $deleted || !$access)
			{
				$init = false;
				$this->addError(
					'ACCESS_DENIED',
					'',
					true
				);
			}
		}

		if ($init)
		{
			$this->arResult['INDEX_LANDING'] = null;
			$this->arResult['INDEX_META'] = null;

			// prepares landing create url
			if ($this->arParams['PAGE_URL_LANDING_EDIT'])
			{
				$this->arParams['~PAGE_URL_LANDING_EDIT'] = str_replace(
					['#site_show#', '#landing_edit#'],
					[$siteId, 0],
					$this->arParams['~PAGE_URL_LANDING_EDIT']
				);
				$this->arParams['~PAGE_URL_LANDING_EDIT'] = $this->getPageParam($this->arParams['~PAGE_URL_LANDING_EDIT'], [
					$this->arParams['ACTION_FOLDER'] => $this->id,
					'frameMode' => 'Y'
				]);
				$this->arParams['PAGE_URL_LANDING_EDIT'] = htmlspecialcharsbx($this->arParams['~PAGE_URL_LANDING_EDIT']);
			}

			if ($this->arParams['PAGE_URL_LANDING_VIEW'])
			{
				$this->arParams['~PAGE_URL_LANDING_VIEW'] = str_replace(
					['#site_show#'],
					[$siteId],
					$this->arParams['~PAGE_URL_LANDING_VIEW']
				);
				$this->arParams['PAGE_URL_LANDING_VIEW'] = htmlspecialcharsbx($this->arParams['~PAGE_URL_LANDING_VIEW']);
			}

			// gets index landing
			$this->arResult['INDEX_LANDING'] = Landing::getList([
				'select' => [
					'*'
				],
				'filter' => [
					[
						'LOGIC' => 'OR',
						!$indexId ? [] : [
							'ID' => $indexId,
							'FOLDER_ID' => $this->id,
							'SITE_ID' => $siteId
						],
						[
							'FOLDER_ID' => $this->id,
							'SITE_ID' => $siteId,
							'=CODE' => $folder['CODE']['CURRENT']
						]
					]
				],
				'limit' => 1
			])->fetch();

			// get rest of data
			$this->arResult['SITE_PATH'] = Site::getPublicUrl($siteId);
			$this->arResult['FOLDER_PATH'] = $folder['PARENT_ID']['STORED']
				? Folder::getFullPath($folder['PARENT_ID']['STORED'], $siteId)
				: '/';
			$this->arResult['FOLDER_EMPTY'] = !Landing::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'SITE_ID' => $siteId,
					'FOLDER_ID' => $this->id
				]
			])->fetch();

			if ($this->arResult['INDEX_LANDING']['ID'] ?? null)
			{
				$this->arResult['INDEX_META'] = Landing::getAdditionalFieldsAsArray(
					$this->arResult['INDEX_LANDING']['ID'],
					false
				);
			}

			// after folder update
			Folder::callback('OnAfterUpdate',
				function(Event $event)
				{
					$fields = $event->getParameter('fields');

					if ($fields['INDEX_ID'] ?? null)
					{
						$fieldsNew = [
							'METAOG_TITLE' => $this->request('METAOG_TITLE'),
							'METAOG_DESCRIPTION' => $this->request('METAOG_DESCRIPTION'),
							'METAOG_IMAGE' => $this->request('METAOG_IMAGE')
						];
						if (intval($fieldsNew['METAOG_IMAGE']) < 0)
						{
							unset($fieldsNew['METAOG_IMAGE']);
						}
						Landing::saveAdditionalFields($fields['INDEX_ID'], $fieldsNew);
						$this->arResult['INDEX_META'] = Landing::getAdditionalFieldsAsArray($fields['INDEX_ID'], false);
					}
				}
			);
		}

		parent::executeComponent();
	}
}
