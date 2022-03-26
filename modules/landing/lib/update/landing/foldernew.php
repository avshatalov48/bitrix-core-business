<?php
namespace Bitrix\Landing\Update\Landing;

use Bitrix\Landing\Internals\LandingTable;
use Bitrix\Landing\Rights;
use Bitrix\Landing\Folder;
use Bitrix\Landing\Debug;
use Bitrix\Landing\Manager;
use Bitrix\Main\Update\Stepper;

//\Bitrix\Main\Update\Stepper::bindClass('Bitrix\Landing\Update\Landing\FolderNew', 'landing', 0);

class FolderNew extends Stepper
{
	protected static $moduleId = 'landing';

	/**
	 * One step of converter.
	 * @param array &$result Result array.
	 * @param int|null $siteId Site id for force update specific site.
	 * @return bool
	 */
	public function execute(array &$result, ?int $siteId = null): bool
	{
		Rights::setGlobalOff();
		$finished = true;

		$globalFilter = [
			'=FOLDER' => 'Y',
			//'=DELETED' => ['Y', 'N'],
			//'=SITE.DELETED' => ['Y', 'N']
		];

		if ($siteId)
		{
			$globalFilter['SITE_ID'] = $siteId;
		}

		// gets common quantity
		$res = LandingTable::getList([
			'select' => [
				new \Bitrix\Main\Entity\ExpressionField(
					'CNT', 'COUNT(*)'
				)
			],
			'filter' => $globalFilter
		]);
		if ($row = $res->fetch())
		{
			$result['count'] = $row['CNT'];
		}

		// gets group for update
		$res = LandingTable::getList([
			'select' => [
				'ID', 'TITLE', 'CODE', 'ACTIVE', 'DELETED', 'SITE_ID'
			],
			'filter' => $globalFilter,
			'order' => [
				'ID' => 'ASC'
			]
		]);
		while ($page = $res->fetch())
		{
			$folderId = null;
			// create new folder and update folder to page
			$resFolder = Folder::add([
				'SITE_ID' => $page['SITE_ID'],
				'TITLE' => $page['TITLE'],
				'CODE' => $page['CODE'],
				'ACTIVE' => $page['ACTIVE'],
				'DELETED' => $page['DELETED']
			]);
			if ($resFolder->isSuccess())
			{
				$folderId = $resFolder->getId();
			}
			else
			{
				foreach ($resFolder->getErrors() as $error)
				{
					if ($error->getCode() === 'FOLDER_IS_NOT_UNIQUE')
					{
						$resFolder = Folder::getList([
							'select' => [
								'ID'
							],
							'filter' => [
								'SITE_ID' => $page['SITE_ID'],
								'PARENT_ID' => null,
								'=CODE' => $page['CODE']
							]
						]);
						if ($rowFolder = $resFolder->fetch())
						{
							$folderId = $rowFolder['ID'];
							break;
						}
					}
				}
			}

			if ($folderId)
			{
				Folder::update($folderId, [
					'INDEX_ID' => $page['ID']
				]);
				LandingTable::update($page['ID'], [
					'FOLDER' => 'N',
					'FOLDER_ID' => $folderId
				]);
				Debug::log('FU:LandingTable::update', var_export([
					'ID' => $page['ID'],
					'FOLDER' => 'N',
					'FOLDER_ID' => $folderId
				], true));
				// fetch old folder's pages and move it to the new folder
				$resPage = LandingTable::getList([
					'select' => [
						'ID', 'FOLDER_ID'
					],
					'filter' => [
						'FOLDER_ID' => $page['ID'],
						//'=DELETED' => ['Y', 'N'],
						//'=SITE.DELETED' => ['Y', 'N']
					]
				]);
				while ($rowPage = $resPage->fetch())
				{
					LandingTable::update($rowPage['ID'], [
						'FOLDER_ID' => $folderId
					]);
					Debug::log('FU:LandingTable::update', var_export([
						'ID' => $rowPage['ID'],
						'FOLDER_ID_OLD' => $rowPage['FOLDER_ID'],
						'FOLDER_ID' => $folderId
					], true));
				}
			}

			$finished = false;
		}

		if ($finished && !$siteId)
		{
			Manager::setOption('landing_new', 'Y');
		}

		Rights::setGlobalOn();

		return !$finished;
	}
}