<?php

use Bitrix\Main;

/**
* Workflow persistence service.
*/
class CBPWorkflowPersister
{
	protected $serviceInstanceId = "";
	protected $ownershipDelta = 300;
	protected $useGZipCompression = false;

	private static $instance;

	public function __clone()
	{
		trigger_error('Clone in not allowed.', E_USER_ERROR);
	}

	protected function __construct()
	{
		$this->serviceInstanceId = uniqid("", true);
		$this->useGZipCompression = \CBPWorkflowTemplateLoader::useGZipCompression();
	}

	/**
	 * @return self
	 */
	public static function getPersister()
	{
		if (!isset(self::$instance))
		{
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}

	private function retrieveWorkflow($instanceId, $silent = false)
	{
		global $DB;

		$queryCondition = $this->getLockerQueryCondition();

		if (!$silent && !$this->lock($instanceId))
		{
			throw new Exception(GetMessage("BPCGWP_WF_LOCKED"), \CBPRuntime::EXCEPTION_CODE_INSTANCE_LOCKED);
		}

		$dbResult = $DB->Query(
			"SELECT WORKFLOW, WORKFLOW_RO, case when ". $queryCondition . " then 'Y' else 'N' end as UPDATEABLE ".
			"FROM b_bp_workflow_instance ".
			"WHERE ID = '".$DB->ForSql($instanceId)."' "
		);
		if ($arResult = $dbResult->Fetch())
		{
			if ($arResult["UPDATEABLE"] == "Y" && !$silent)
			{
				$sqlUpdate = $DB->PrepareUpdate(
					'b_bp_workflow_instance',
					[
						'OWNER_ID' => $this->serviceInstanceId,
						'OWNED_UNTIL' => Main\Type\DateTime::createFromTimestamp($this->GetOwnershipTimeout()),
					]
				);

				$DB->Query(
					'UPDATE b_bp_workflow_instance SET ' . $sqlUpdate . ' WHERE ID = \''.$DB->ForSql($instanceId).'\' '
				);
			}
			elseif (!$silent)
			{
				$this->unlock($instanceId);
				throw new Exception(GetMessage("BPCGWP_WF_LOCKED"), \CBPRuntime::EXCEPTION_CODE_INSTANCE_LOCKED);
			}

			if (!$silent)
			{
				$this->unlock($instanceId);
			}

			return [$arResult["WORKFLOW"], $arResult["WORKFLOW_RO"]];
		}

		if (!$silent)
		{
			$this->unlock($instanceId);
		}

		throw new Exception(GetMessage("BPCGWP_INVALID_WF"), \CBPRuntime::EXCEPTION_CODE_INSTANCE_NOT_FOUND);
	}

	protected function insertWorkflow($id, $buffer, $status, $bUnlocked, array $creationData = [])
	{
		global $DB;

		$queryCondition = $this->getLockerQueryCondition();

		if ($status == CBPWorkflowStatus::Completed || $status == CBPWorkflowStatus::Terminated)
		{
			$DB->Query(
				"DELETE FROM b_bp_workflow_instance ".
				"WHERE ID = '".$DB->ForSql($id)."'"
			);
		}
		else
		{
			$dbResult = $DB->Query(
				"SELECT ID, case when " . $queryCondition . " then 'Y' else 'N' end as UPDATEABLE ".
				"FROM b_bp_workflow_instance ".
				"WHERE ID = '".$DB->ForSql($id)."' "
			);
			if ($arResult = $dbResult->Fetch())
			{
				if ($arResult["UPDATEABLE"] == "Y")
				{
					$sqlUpdate = $DB->PrepareUpdate(
						'b_bp_workflow_instance',
						[
							'WORKFLOW' => $buffer,
							'STATUS' => (int)$status,
							'MODIFIED' => new Main\Type\DateTime(),
							'OWNER_ID' => $bUnlocked ? false : $this->serviceInstanceId,
							'OWNED_UNTIL' => $bUnlocked ? false : Main\Type\DateTime::createFromTimestamp($this->GetOwnershipTimeout()),
						]
					);

					$DB->Query(
						'UPDATE b_bp_workflow_instance SET ' . $sqlUpdate . ' WHERE ID = \''.$DB->ForSql($id).'\' '
					);
				}
				else
				{
					throw new Exception(GetMessage('BPCGWP_WF_LOCKED'), \CBPRuntime::EXCEPTION_CODE_INSTANCE_LOCKED);
				}
			}
			else
			{
				$status = (int) $status;
				$ownerId = ($bUnlocked ? false : $this->serviceInstanceId);
				$ownedUntil = ($bUnlocked ? false : Main\Type\DateTime::createFromTimestamp($this->GetOwnershipTimeout()));

				$moduleId = isset($creationData['MODULE_ID']) ? $creationData['MODULE_ID'] : '';
				$entity = isset($creationData['ENTITY']) ? $creationData['ENTITY'] : '';
				$documentId = isset($creationData['DOCUMENT_ID']) ? $creationData['DOCUMENT_ID'] : '';
				$tplId = isset($creationData['WORKFLOW_TEMPLATE_ID']) ? (int) $creationData['WORKFLOW_TEMPLATE_ID'] : 0;
				$startedBy = isset($creationData['STARTED_BY']) ? (int) $creationData['STARTED_BY'] : 0;
				$startedEventType = isset($creationData['STARTED_EVENT_TYPE']) ? (int) $creationData['STARTED_EVENT_TYPE'] : 0;
				$ro = isset($creationData['RO']) ? $creationData['RO'] : false;
				$nowTime = new Main\Type\DateTime();

				$sqlInsert = $DB->PrepareInsert(
					'b_bp_workflow_instance',
					[
						'ID' => $id,
						'WORKFLOW' => $buffer,
						'WORKFLOW_RO' => $ro,
						'STATUS' => $status,
						'MODIFIED' => $nowTime,
						'OWNER_ID' => $ownerId,
						'OWNED_UNTIL' => $ownedUntil,
						'MODULE_ID' => $moduleId,
						'ENTITY' => $entity,
						'DOCUMENT_ID' => $documentId,
						'WORKFLOW_TEMPLATE_ID' => $tplId,
						'STARTED' => $nowTime,
						'STARTED_BY' => $startedBy,
						'STARTED_EVENT_TYPE' => $startedEventType,
					]
				);

				$DB->Query(
					'INSERT INTO b_bp_workflow_instance (' . $sqlInsert[0] . ') VALUES (' . $sqlInsert[1] .')'
				);
			}
		}
	}

	protected function getOwnershipTimeout()
	{
		return time() + $this->ownershipDelta;
	}

	public function loadWorkflow($instanceId, $silent = false)
	{
		[$state, $ro] = $this->RetrieveWorkflow($instanceId, $silent);

		if ($state)
		{
			return $this->RestoreFromSerializedForm($state, $ro);
		}

		throw new Exception("WorkflowNotFound");
	}

	protected function restoreFromSerializedForm($buffer, $ro)
	{
		if ($this->useGZipCompression)
		{
			$buffer = gzuncompress($buffer);
			$ro = $ro ? gzuncompress($ro) : null;
		}

		if ($buffer == '')
		{
			throw new Exception("EmptyWorkflowInstance");
		}

		/** @var CBPCompositeActivity $activity */
		$activity = CBPActivity::Load($buffer);

		if ($ro)
		{
			$ro = Main\Web\Json::decode($ro);
			if (is_array($ro))
			{
				$activity->setReadOnlyData($ro);
			}
		}

		return $activity;
	}

	public function saveWorkflow(CBPActivity $rootActivity, $bUnlocked)
	{
		$creationData = [];
		if ($rootActivity->workflow->isNew())
		{
			$dt = $rootActivity->GetDocumentId();
			$creationData['MODULE_ID'] = $dt[0];
			$creationData['ENTITY'] = $dt[1];
			$creationData['DOCUMENT_ID'] = $dt[2];
			$creationData['WORKFLOW_TEMPLATE_ID'] = $rootActivity->GetWorkflowTemplateId();
			$creationData['STARTED_EVENT_TYPE'] = $rootActivity->getDocumentEventType();

			$startedBy = $rootActivity->{\CBPDocument::PARAM_TAGRET_USER};
			if ($startedBy)
			{
				$creationData['STARTED_BY'] = \CBPHelper::StripUserPrefix($startedBy);
			}
			/** @var CBPCompositeActivity $rootActivity */
			$creationData['RO'] = $this->getJsonCompressed($rootActivity->pullReadOnlyData());
		}
		else
		{
			/** @var CBPCompositeActivity $rootActivity */
			$rootActivity->pullReadOnlyData();
		}

		$workflowStatus = $rootActivity->GetWorkflowStatus();

		if ($rootActivity->workflow->isAbandoned())
		{
			$workflowStatus = CBPWorkflowStatus::Completed;
		}

		$buffer = "";
		if (($workflowStatus != CBPWorkflowStatus::Completed) && ($workflowStatus != CBPWorkflowStatus::Terminated))
		{
			$buffer = $this->GetSerializedForm($rootActivity);
		}

		$this->InsertWorkflow($rootActivity->GetWorkflowInstanceId(), $buffer, $workflowStatus, $bUnlocked, $creationData);
	}

	protected function getSerializedForm(CBPActivity $rootActivity)
	{
		$buffer = $rootActivity->Save();

		if ($this->useGZipCompression)
			$buffer = gzcompress($buffer, 9);
		return $buffer;
	}

	private function getJsonCompressed($data): string
	{
		$buffer = Main\Web\Json::encode($data);

		if ($this->useGZipCompression)
		{
			$buffer = gzcompress($buffer, 9);
		}
		return $buffer;
	}

	public function unlockWorkflow(CBPActivity $rootActivity)
	{
		global $DB;

		if ($rootActivity == null)
			throw new Exception("rootActivity");

		$DB->Query(
			"UPDATE b_bp_workflow_instance SET ".
			"	OWNER_ID = NULL, ".
			"	OWNED_UNTIL = NULL ".
			"WHERE ID = '".$DB->ForSql($rootActivity->GetWorkflowInstanceId())."' ".
			"	AND ( ".
			"		(OWNER_ID = '".$DB->ForSql($this->serviceInstanceId)."' ".
			"			AND OWNED_UNTIL >= ".$DB->CurrentTimeFunction().") ".
			"		OR ".
			"		(OWNER_ID IS NULL) ".
			"		OR ".
			"		(OWNER_ID IS NOT NULL ".
			"			AND OWNED_UNTIL < ".$DB->CurrentTimeFunction().") ".
			"	)"
		);
	}

	protected function getLockerQueryCondition()
	{
		global $DB;

		return "(OWNER_ID IS NULL OR OWNER_ID = '".$DB->ForSql($this->serviceInstanceId)."')";
	}

	private function lock(string $workflowId): bool
	{
		if (!$this->useDbLock())
		{
			return true;
		}

		return $this->lockDb($workflowId);
	}

	private function unlock(string $workflowId): bool
	{
		if (!$this->useDbLock())
		{
			return true;
		}

		return $this->lockDb($workflowId, true);
	}

	private function useDbLock()
	{
		static $use;

		if ($use === null)
		{
			$use = (Main\Config\Option::get('bizproc', 'workflow_dblock', 'N') === 'Y');
		}

		return $use;
	}

	private function lockDb(string $workflowId, bool $release = false): bool
	{
		$name = 'bizproc_' . $workflowId;
		$connection = Main\Application::getInstance()->getConnection();

		if ($release)
		{
			return $connection->unlock($name);
		}

		return $connection->lock($name);
	}
}
