<?php
namespace Bitrix\Landing\Copy\Implement;

use Bitrix\Landing\File;
use Bitrix\Landing\Hook;
use Bitrix\Landing\TemplateRef;
use Bitrix\Main\Copy\Container;
use Bitrix\Main\Copy\CopyImplementer;
use Bitrix\Main\Result;

/**
 * Class copies landings.
 *
 * if you want copy in another site then set $targetSiteId.
 * To put landing in folders, set the map ids of old and new folders.
 *
 * @package Bitrix\Landing\Copy\Implement
 */
class Landing extends CopyImplementer
{

	private $targetSiteId = 0;
	private $folderMapIds = [];

	public function __construct($folderMapIds = [])
	{
		parent::__construct();

		$this->folderMapIds = $folderMapIds;
	}

	/**
	 * Set $targetSiteId if you want copy in another site.
	 * @param int $targetSiteId
	 */
	public function setTargetSiteId(int $targetSiteId): void
	{
		$this->targetSiteId = $targetSiteId;
	}

	/**
	 * Adds landing.
	 *
	 * @param Container $container Container with data that is needed in the process of copying the entity.
	 * @param array $fields The landing fields.
	 * @return int|bool return landing id or false.
	 */
	public function add(Container $container, array $fields)
	{
		$addResult = \Bitrix\Landing\Landing::add($fields);
		if ($addResult->isSuccess())
		{
			return $addResult->getId();
		}
		else
		{
			$this->result->addErrors($addResult->getErrors());
			return false;
		}
	}

	/**
	 * Returns landing fields.
	 *
	 * @param Container $container Container with data that is needed in the process of copying the entity.
	 * @param int $landingId Landing id.
	 * @return array $fields
	 */
	public function getFields(Container $container, $landingId)
	{
		$queryObject = \Bitrix\Landing\Landing::getList([
			'select' => ['*'],
			'filter' => ['ID' => $landingId]
		]);
		return (($fields = $queryObject->fetch()) ? $fields : []);
	}

	/**
	 * Preparing data before creating a new landing.
	 *
	 * @param Container $container Container with data that is needed in the process of copying the entity.
	 * @param array $fields List landing fields.
	 * @return array $fields
	 */
	public function prepareFieldsToCopy(Container $container, array $fields)
	{
		$siteId = $this->getSiteId($fields);
		$folderId = $this->getFolderId($siteId, $fields);

		$fields['ACTIVE'] = 'N';
		$fields['PUBLIC'] = 'N';
		$fields['SITE_ID'] = $siteId;
		$fields['FOLDER'] = ($folderId ? 'N' : $fields['FOLDER']);
		$fields['FOLDER_ID'] = $folderId;
		$fields['RULE'] = '';

		unset($fields['ID']);

		return $fields;
	}

	/**
	 * Starts copying children entities.
	 *
	 * @param Container $container
	 * @param int $landingId Landing id.
	 * @param int $copiedLandingId Copied landing id.
	 * @return Result
	 */
	public function copyChildren(Container $container, $landingId, $copiedLandingId)
	{
		$results = [];

		\Bitrix\Landing\Landing::setEditMode();

		$landingInstance = \Bitrix\Landing\Landing::createInstance($copiedLandingId);
		if ($landingInstance->exist())
		{
			$results[] = $this->copyBlocks($landingId, $copiedLandingId);

			Hook::setEditMode();
			Hook::copyLanding($landingId, $copiedLandingId);

			File::copyLandingFiles($landingId, $copiedLandingId);
			if (($refs = TemplateRef::getForLanding($landingId)))
			{
				TemplateRef::setForLanding($copiedLandingId, $refs);
			}
		}

		$landingErrorCollection = $landingInstance->getError();
		if ($landingErrorCollection->getErrors())
		{
			$result = new Result();
			$result->addErrors($landingErrorCollection->getErrors());
			$results[] = $result;
		}

		return $this->getResult($results);
	}

	private function getSiteId(array $fields): int
	{
		return (int) ($this->targetSiteId ? $this->targetSiteId : $fields['SITE_ID']);
	}

	private function getFolderId(int $siteId, array $fields)
	{
		$folderId = null;
		if (array_key_exists($fields['FOLDER_ID'], $this->folderMapIds))
		{
			$folderId = (int)$this->folderMapIds[$fields['FOLDER_ID']];
		}
		else if ($siteId == $fields['SITE_ID'])
		{
			$folderId = (int)$fields['FOLDER_ID'];
		}
		return $folderId;
	}

	private function copyBlocks(int $landingId, int $copiedLandingId): Result
	{
		$result = new Result();

		$blockMapIds = [];

		$copiedLanding = \Bitrix\Landing\Landing::createInstance($copiedLandingId);
		$copiedLanding->copyAllBlocks($landingId, false, $blockMapIds);

		$result->setData(['LandingBlocks' => $blockMapIds]);

		return $result;
	}
}