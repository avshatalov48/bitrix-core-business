<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Block;
use \Bitrix\Landing\Landing;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Rights;
use \Bitrix\Landing\Transfer\Import;
use \Bitrix\Landing\Internals\BlockTable;
use \Bitrix\Main\ModuleManager;

/**
 * Class LandingRestPendingComponent
 * This component for situation when during the import some application
 * blocks are not installed yet. After block will appear, this component
 * will rewrite the block content.
 */
class LandingRestPendingComponent extends \CBitrixComponent
{
	/**
	 * Returns application info by its code.
	 * @param string $appCode Application code.
	 * @return array|null
	 */
	protected function getAppInfo(string $appCode): ?array
	{
		$res = \Bitrix\Rest\AppTable::getList([
			'filter' => [
				'=CODE' => $appCode
			]
		]);
		$return = $res->fetch();
		return $return ? $return : null;
	}

	/**
	 * Finds repository block and return it id.
	 * @param string $appCode Application code.
	 * @param string $xmlId External id application.
	 * @return array|null
	 */
	protected function getRepoId(string $appCode, string $xmlId): ?array
	{
		$res = \Bitrix\Landing\Repo::getList([
			'select' => [
				'ID', 'APP_CODE'
			],
			'filter' => [
				'=APP_CODE' => $appCode,
				'=XML_ID' => $xmlId
			]
 		]);
		if ($row = $res->fetch())
		{
			return $row;
		}

		return null;
	}

	/**
	 * Updates site in Bitrix24 env for clearing cache.
	 * @param int $id Site id.
	 * @return void
	 */
	protected function updateSite(int $id): void
	{
		static $updatedIds = [];

		if (!ModuleManager::isModuleInstalled('bitrix24'))
		{
			return;
		}

		if (!in_array($id, $updatedIds))
		{
			Rights::setGlobalOff();
			$updatedIds[] = $id;
			Site::update($id, []);
			Rights::setGlobalOn();
		}
	}

	/**
	 * Save new data in the block.
	 * @param int $blockId Block id.
	 * @param int $repoId Repo id.
	 * @param array $data New data for block.
	 * @param bool $view Output new block content after save.
	 * @return bool
	 */
	protected function updateBlock(int $blockId, int $repoId, array $data, bool $view = true): bool
	{
		$newCode = 'repo_' . $repoId;
		// get current block data
		$blockData = BlockTable::getList([
			'select' => [
				'ID', 'PARENT_ID'
			],
			'filter' => [
				'ID' => $blockId
			]
		])->fetch();
		if (!$blockData)
		{
			return false;
		}
		// save new new block data
		$content = Block::getContentFromRepository($newCode);
		$res = BlockTable::update($blockId, [
			'CODE' => $newCode,
			'CONTENT' => $content
		]);
		// and update by API block content
		if ($res->isSuccess())
		{
			$blockInstance = new Block($blockId);
			Import\Landing::saveDataToBlock(
				$blockInstance,
				$data
			);
			// replace links
			$replace = [];
			foreach (['landing', 'block'] as $key)
			{
				if (
					isset($data['replace'][$key]) &&
					is_array($data['replace'][$key])
				)
				{
					foreach ($data['replace'][$key] as $oldId => $newId)
					{
						$replace['#' . $key . $oldId] = '#' . $key . $newId;
					}
				}
			}
			if ($replace)
			{
				$blockInstance->saveContent(
					str_replace(
						array_keys($replace),
						array_values($replace),
						$blockInstance->getContent()
					)
				);
			}
			// save
			$blockInstance->save();
			$this->updateSite(
				$blockInstance->getSiteId()
			);
			if ($view)
			{
				$blockInstance->view(Landing::getEditMode());
			}
			if ($blockData['PARENT_ID'])
			{
				return $this->updateBlock($blockData['PARENT_ID'], $repoId, $data, false);
			}
		}
		return true;
	}

	/**
	 * Base executable method.
	 * @return void
	 */
	public function executeComponent(): void
	{
		if (
			!isset($this->arParams['~BLOCK_ID']) ||
			$this->arParams['~BLOCK_ID'] <= 0
		)
		{
			return;
		}
		if (!\Bitrix\Main\Loader::includeModule('landing'))
		{
			return;
		}
		if (!\Bitrix\Main\Loader::includeModule('rest'))
		{
			return;
		}

		$this->arResult['APP_INFO'] = null;
		$this->arResult['APP_CODE'] = 'Unknown application';

		if (isset($this->arParams['~DATA']))
		{
			$replace = [];
			$blockId = intval($this->arParams['~BLOCK_ID']);
			$data = unserialize(base64_decode($this->arParams['~DATA']));
			if (isset($this->arParams['~REPLACE']))
			{
				$replace = unserialize(base64_decode($this->arParams['~REPLACE']));
				if (!is_array($replace))
				{
					$replace = [];
				}
			}
			if (
				is_array($data) &&
				isset($data['repo_block']['app_code']) &&
				isset($data['repo_block']['xml_id']) &&
				is_string($data['repo_block']['app_code']) &&
				is_string($data['repo_block']['xml_id'])
			)
			{
				$this->arResult['APP_CODE'] = $data['repo_block']['app_code'];
				$this->arResult['APP_INFO'] = $this->getAppInfo(
					$data['repo_block']['app_code']
				);
				$repoRow = $this->getRepoId(
					$data['repo_block']['app_code'],
					$data['repo_block']['xml_id']
				);
				if ($repoRow)
				{
					$data['replace'] = $replace;
					$this->updateBlock($blockId, $repoRow['ID'], $data);
				}
			}
		}

		if (Landing::getEditMode())
		{
			$this->includeComponentTemplate();
		}
	}
}