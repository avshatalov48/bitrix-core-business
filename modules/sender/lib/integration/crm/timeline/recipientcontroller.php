<?php
namespace Bitrix\Sender\Integration\Crm\Timeline;

use Bitrix\Crm\Timeline;

use Bitrix\Main\ArgumentException;
use Bitrix\Sender\Entity;
use Bitrix\Sender\PostingRecipientTable;

/**
 * Class RecipientController
 * @package Bitrix\Sender\Integration\Crm\Timeline
 */
class RecipientController extends Timeline\EntityController
{
	/** @var static|null */
	protected static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return static
	 */
	public static function getInstance()
	{
		if(self::$instance === null)
		{
			self::$instance = new static();
		}
		return self::$instance;
	}

	/**
	 * Get entity type ID.
	 *
	 * @return int
	 */
	public function getEntityTypeID()
	{
		return \CCrmOwnerType::Wait;
	}

	/**
	 * Handler of event 'onCreate'.
	 *
	 * @param integer $id ID.
	 * @param array $params Parameters.
	 */
	public function onCreate($id, array $params)
	{

	}

	/**
	 * Handler of event 'onModify'.
	 *
	 * @param integer $id ID.
	 * @param array $params Parameters.
	 */
	public function onModify($id, array $params)
	{

	}

	/**
	 * Handler of event 'onDelete'.
	 *
	 * @param integer $ownerID Owner ID.
	 * @param array $params Parameters.
	 */
	public function onDelete($ownerID, array $params)
	{

	}

	/**
	 * Prepare history data model.
	 *
	 * @param array $data Data.
	 * @param array|null $options Options.
	 * @return array
	 */
	public function prepareHistoryDataModel(array $data, array $options = null)
	{
		$settings = (object) ((isset($data['SETTINGS']) && is_array($data['SETTINGS'])) ? $data['SETTINGS'] : array());
		$data = parent::prepareHistoryDataModel($data, $options);

		try
		{

			if ($settings->isAds)
			{
				$entity = new Entity\Ad($settings->letterId);
				$settings->path = '/marketing/ads/edit/' . $settings->letterId . '/';
				$settings->messageName = $entity->getMessage()->getName();
			}
			else
			{
				$entity = new Entity\Letter($settings->letterId);
				$settings->path = '/marketing/letter/edit/' . $settings->letterId . '/';
				$settings->messageName = $entity->getMessage()->getName();
			}
			$settings->letterTitle = $entity->get('TITLE');

			if ($settings->recipient)
			{
				$row = PostingRecipientTable::getRow([
					'select' => ['IS_READ', 'IS_CLICK', 'IS_UNSUB', 'STATUS'],
					'filter' => ['=ID' => $settings->recipient['id']]
				]);
				$settings->isRead = $row ? $row['IS_READ'] == 'Y' : false;
				$settings->isClick = $row ? $row['IS_CLICK'] == 'Y' : false;
				$settings->isUnsub = $row ? $row['IS_UNSUB'] == 'Y' : false;
				$settings->isError = $row ? $row['STATUS'] === PostingRecipientTable::SEND_RESULT_ERROR : false;
			}


			$data['SETTINGS'] = (array) $settings;
		}
		catch (ArgumentException $e)
		{
			return $data;
		}

		return $data;
	}
}