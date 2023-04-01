<?php

namespace Bitrix\Vote\Uf;

use Bitrix\Vote\Attach;
use Bitrix\Vote\Attachment\Connector;
use Bitrix\Vote\Attachment\DefaultConnector;
use Bitrix\Vote\Attachment\BlogPostConnector;
use Bitrix\Vote\Attachment\ForumMessageConnector;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\SystemException;

final class Manager
{
	/** @var  ErrorCollection */
	protected $errorCollection;

	protected $params;

	protected $additionalConnectorList = null;
	/** @var Attach[]  */
	protected $loadedAttachedObjects = array();

	protected static $instance = array();

	private $int = 0;

	/**
	 * Constructor of UserFieldManager.
	 * @param array $params
	 */
	public function __construct(array $params)
	{
		$this->params = $params;
		$this->params['ENTITY_VALUE_ID'] = $this->params['ENTITY_VALUE_ID'] ?? null;
		$this->params['VALUE_ID'] = $this->params['VALUE_ID'] ?? null;
		$this->errorCollection = new ErrorCollection();
	}

	/**
	 * Returns instance of Manager.
	 * @param array $params Array (
			[ID] => 29
			[ENTITY_ID] => BLOG_POST
			[FIELD_NAME] => UF_BLOG_POST_VOTE
			[USER_TYPE_ID] => vote
			[XML_ID] => UF_BLOG_POST_VOTE
			[SORT] => 100
			[MULTIPLE] => N
			[MANDATORY] => N
			[SHOW_FILTER] => N
			[SHOW_IN_LIST] => Y
			[EDIT_IN_LIST] => Y
			[IS_SEARCHABLE] => N
			[SETTINGS] => Array
				(
					[CHANNEL_ID] => 1
					[UNIQUE] => 8
					[UNIQUE_IP_DELAY] => Array
						(
							[DELAY] =>
							[DELAY_TYPE] => S
						)

					[NOTIFY] => I
				)

			[EDIT_FORM_LABEL] => UF_BLOG_POST_VOTE
			[LIST_COLUMN_LABEL] =>
			[LIST_FILTER_LABEL] =>
			[ERROR_MESSAGE] =>
			[HELP_MESSAGE] =>
			[USER_TYPE] => Array
				(
					[USER_TYPE_ID] => vote
					[CLASS_NAME] => Bitrix\Vote\Uf\VoteUserType
					[DESCRIPTION] => "Vote"
					[BASE_TYPE] => int
				)

			[VALUE] => 27
			[ENTITY_VALUE_ID] => 247)).
	 * @return Manager
	 */
	public static function getInstance(array $params)
	{
		$id = md5(serialize($params));
		if (!array_key_exists($id, self::$instance))
		{
			self::$instance[$id] = new static($params);
		}
		return self::$instance[$id];
	}

	/**
	 * Returns attached object by id.
	 * @param int $id Id of attached object.
	 * @return Attach
	 */
	public function loadFromAttachId($id)
	{
		if(!isset($this->loadedAttachedObjects[$id]))
		{
			$this->loadedAttachedObjects[$id] = \Bitrix\Vote\Attachment\Manager::loadFromAttachId($id);
		}
		return $this->loadedAttachedObjects[$id];
	}

	/**
	 * Returns vote object by id.
	 * @param int $id Id of vote.
	 * @return Attach
	 */
	public function loadFromVoteId($id)
	{
		$id1 = VoteUserType::NEW_VOTE_PREFIX.$id;
		if(!isset($this->loadedAttachedObjects[$id1]))
		{
			[$entityType, $moduleId] = $this->getConnectorDataByEntityType($this->params["ENTITY_ID"]);
			$attach = \Bitrix\Vote\Attachment\Manager::loadFromVoteId(array(
				"ENTITY_ID" => ($this->params["ENTITY_VALUE_ID"]  ?: $this->params["VALUE_ID"]), // http://hg.office.bitrix.ru/repos/modules/rev/b614a075ce64
				"ENTITY_TYPE" => $entityType,
				"MODULE_ID" => $moduleId), $id);
			$this->loadedAttachedObjects[$id1] = $attach;
		}
		return $this->loadedAttachedObjects[$id1];
	}

	/**
	 * @return \Bitrix\Vote\Attach
	 */
	public function loadEmptyObject()
	{
		[$entityType, $moduleId] = $this->getConnectorDataByEntityType($this->params["ENTITY_ID"]);
		return \Bitrix\Vote\Attachment\Manager::loadEmptyAttach(array(
			"ENTITY_ID" => ($this->params["ENTITY_VALUE_ID"] ?: $this->params["VALUE_ID"]), // http://hg.office.bitrix.ru/repos/modules/rev/b614a075ce64,
			"ENTITY_TYPE" => $entityType,
			"MODULE_ID" => $moduleId), array(
			"CHANNEL_ID" => $this->params["SETTINGS"]["CHANNEL_ID"]
		));
	}

	/**
	 * @return Attach[]
	 */
	public function loadFromEntity()
	{
		[$entityType, $moduleId] = $this->getConnectorDataByEntityType($this->params["ENTITY_ID"]);
		$res = array(
			"ENTITY_ID" => ($this->params["ENTITY_VALUE_ID"] ?: $this->params["VALUE_ID"]), // http://hg.office.bitrix.ru/repos/modules/rev/b614a075ce64
			"=ENTITY_TYPE" => $entityType,
			"=MODULE_ID" => $moduleId);
		return \Bitrix\Vote\Attachment\Manager::loadFromEntity($res);
	}
	/**
	 * Checks attitude attached object to entity.
	 * @param Attach $attachedObject Attached object.
	 * @param string $entityType Entity type (ex. blog_post).
	 * @param int $entityId Id of entity.
	 * @return bool
	 */
	public function belongsToEntity(Attach $attachedObject, $entityType, $entityId)
	{
		[$connectorClass, $moduleId] = $this->getConnectorDataByEntityType($entityType);

		return
			$attachedObject->getEntityId()   == $entityId &&
			$attachedObject->getModuleId()   == $moduleId &&
			$attachedObject->getEntityType() == $connectorClass;
	}
	/**
	 * Gets data which describes specific connector by entity type.
	 * @param string $entityType Entity type (ex. sonet_comment).
	 * @return array|null Array with two elements: connector class and module.
	 */
	public function getConnectorDataByEntityType($entityType)
	{
		$defaultConnectors = $this->getDefaultConnectors();
		$entityType = mb_strtolower($entityType);

		if(isset($defaultConnectors[$entityType]))
			return $defaultConnectors[$entityType];
		if (($connector = $this->getAdditionalConnector($entityType)) !== null)
			return $connector;
		return array(DefaultConnector::className(), 'vote');
	}

	/**
	 * Returns full list of available connectors for attached objects.
	 *
	 * @return array
	 */
	public function getConnectors()
	{
		return array_merge($this->getDefaultConnectors(), $this->getAdditionalConnectors());
	}

	private function getDefaultConnectors()
	{
		return array(
			'blog_post' => array(BlogPostConnector::className(), 'blog'),
			'forum_message' => array(ForumMessageConnector::className(), 'forum')
		);
	}


	private function getAdditionalConnectors()
	{
		if($this->additionalConnectorList === null)
		{
			$this->buildAdditionalConnectorList();
		}

		return $this->additionalConnectorList;
	}

	private function getAdditionalConnector($entityType)
	{
		$additionalConnectorList = $this->getAdditionalConnectors();

		return isset($additionalConnectorList[$entityType])? $additionalConnectorList[$entityType] : null;
	}

	private function buildAdditionalConnectorList()
	{
		$this->additionalConnectorList = array();

		$event = new Event("vote", "onBuildAdditionalConnectorList");
		$event->send();

		foreach($event->getResults() as $evenResult)
		{
			if($evenResult->getType() != EventResult::SUCCESS)
			{
				continue;
			}

			$result = $evenResult->getParameters();
			if(!is_array($result))
			{
				throw new SystemException('Wrong event result by building AdditionalConnectorList. Must be array.');
			}

			foreach($result as $connector)
			{
				if(empty($connector['ENTITY_TYPE']))
				{
					throw new SystemException('Wrong event result by building AdditionalConnectorList. Could not find ENTITY_TYPE.');
				}

				if(empty($connector['MODULE_ID']))
				{
					throw new SystemException('Wrong event result by building AdditionalConnectorList. Could not find MODULE_ID.');
				}

				if(empty($connector['CLASS']))
				{
					throw new SystemException('Wrong event result by building AdditionalConnectorList. Could not find CLASS.');
				}

				if(is_string($connector['CLASS']) && class_exists($connector['CLASS']) && is_a($connector['CLASS'], Connector::class, true))
				{
					$this->additionalConnectorList[mb_strtolower($connector['ENTITY_TYPE'])] = array(
						$connector['CLASS'],
						$connector['MODULE_ID']
					);
				}
				else
				{
					throw new SystemException('Wrong event result by building AdditionalConnectorList. Could not find class by CLASS.');
				}
			}
		}
	}

	/**
	 * Shows component to edit vote.
	 * @param array &$params Component parameters.
	 * @param array &$result Component results.
	 * @param null $component Component.
	 * @return void
	 */
	public function showEdit(&$params, &$result, $component = null)
	{
		global $APPLICATION;
		$APPLICATION->IncludeComponent(
			"bitrix:voting.uf",
			".default",
			array(
				'EDIT' => 'Y',
				'PARAMS' => $params,
				'RESULT' => $result,
			),
			$component,
			array("HIDE_ICONS" => "Y")
		);
	}

	/**
	 * Shows component to participate in vote
	 * @param array &$params Component parameters.
	 * @param array &$result Component results.
	 * @param null $component Component.
	 * @return void
	 */
	public function showView(&$params, &$result, $component = null)
	{
		global $APPLICATION;
		$APPLICATION->IncludeComponent(
			"bitrix:voting.uf",
			".default",
			array(
				"PARAMS" => $params,
				"RESULT" => $result
			),
			$component,
			array("HIDE_ICONS" => "Y")
		);
	}

	/**
	 * Getting array of errors.
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errorCollection->toArray();
	}
}