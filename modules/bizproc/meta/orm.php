<?php

/* ORMENTITYANNOTATION:Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable:bizproc/lib/workflow/template/entity/workflowtemplate.php */
namespace Bitrix\Bizproc\Workflow\Template\Entity {
	/**
	 * Tpl
	 * @see \Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getModuleId()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl resetModuleId()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unsetModuleId()
	 * @method \string fillModuleId()
	 * @method \string getEntity()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setEntity(\string|\Bitrix\Main\DB\SqlExpression $entity)
	 * @method bool hasEntity()
	 * @method bool isEntityFilled()
	 * @method bool isEntityChanged()
	 * @method \string remindActualEntity()
	 * @method \string requireEntity()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl resetEntity()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unsetEntity()
	 * @method \string fillEntity()
	 * @method \string getDocumentType()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setDocumentType(\string|\Bitrix\Main\DB\SqlExpression $documentType)
	 * @method bool hasDocumentType()
	 * @method bool isDocumentTypeFilled()
	 * @method bool isDocumentTypeChanged()
	 * @method \string remindActualDocumentType()
	 * @method \string requireDocumentType()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl resetDocumentType()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unsetDocumentType()
	 * @method \string fillDocumentType()
	 * @method \string getDocumentStatus()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setDocumentStatus(\string|\Bitrix\Main\DB\SqlExpression $documentStatus)
	 * @method bool hasDocumentStatus()
	 * @method bool isDocumentStatusFilled()
	 * @method bool isDocumentStatusChanged()
	 * @method \string remindActualDocumentStatus()
	 * @method \string requireDocumentStatus()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl resetDocumentStatus()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unsetDocumentStatus()
	 * @method \string fillDocumentStatus()
	 * @method \int getAutoExecute()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setAutoExecute(\int|\Bitrix\Main\DB\SqlExpression $autoExecute)
	 * @method bool hasAutoExecute()
	 * @method bool isAutoExecuteFilled()
	 * @method bool isAutoExecuteChanged()
	 * @method \int remindActualAutoExecute()
	 * @method \int requireAutoExecute()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl resetAutoExecute()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unsetAutoExecute()
	 * @method \int fillAutoExecute()
	 * @method \string getName()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl resetName()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unsetName()
	 * @method \string fillName()
	 * @method \string getDescription()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl resetDescription()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unsetDescription()
	 * @method \string fillDescription()
	 * @method array getTemplate()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setTemplate(array|\Bitrix\Main\DB\SqlExpression $template)
	 * @method bool hasTemplate()
	 * @method bool isTemplateFilled()
	 * @method bool isTemplateChanged()
	 * @method array remindActualTemplate()
	 * @method array requireTemplate()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl resetTemplate()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unsetTemplate()
	 * @method array fillTemplate()
	 * @method array getParameters()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setParameters(array|\Bitrix\Main\DB\SqlExpression $parameters)
	 * @method bool hasParameters()
	 * @method bool isParametersFilled()
	 * @method bool isParametersChanged()
	 * @method array remindActualParameters()
	 * @method array requireParameters()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl resetParameters()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unsetParameters()
	 * @method array fillParameters()
	 * @method array getVariables()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setVariables(array|\Bitrix\Main\DB\SqlExpression $variables)
	 * @method bool hasVariables()
	 * @method bool isVariablesFilled()
	 * @method bool isVariablesChanged()
	 * @method array remindActualVariables()
	 * @method array requireVariables()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl resetVariables()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unsetVariables()
	 * @method array fillVariables()
	 * @method array getConstants()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setConstants(array|\Bitrix\Main\DB\SqlExpression $constants)
	 * @method bool hasConstants()
	 * @method bool isConstantsFilled()
	 * @method bool isConstantsChanged()
	 * @method array remindActualConstants()
	 * @method array requireConstants()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl resetConstants()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unsetConstants()
	 * @method array fillConstants()
	 * @method \Bitrix\Main\Type\DateTime getModified()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setModified(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $modified)
	 * @method bool hasModified()
	 * @method bool isModifiedFilled()
	 * @method bool isModifiedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualModified()
	 * @method \Bitrix\Main\Type\DateTime requireModified()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl resetModified()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unsetModified()
	 * @method \Bitrix\Main\Type\DateTime fillModified()
	 * @method \boolean getIsModified()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setIsModified(\boolean|\Bitrix\Main\DB\SqlExpression $isModified)
	 * @method bool hasIsModified()
	 * @method bool isIsModifiedFilled()
	 * @method bool isIsModifiedChanged()
	 * @method \boolean remindActualIsModified()
	 * @method \boolean requireIsModified()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl resetIsModified()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unsetIsModified()
	 * @method \boolean fillIsModified()
	 * @method \int getUserId()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl resetUserId()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unsetUserId()
	 * @method \int fillUserId()
	 * @method \string getSystemCode()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setSystemCode(\string|\Bitrix\Main\DB\SqlExpression $systemCode)
	 * @method bool hasSystemCode()
	 * @method bool isSystemCodeFilled()
	 * @method bool isSystemCodeChanged()
	 * @method \string remindActualSystemCode()
	 * @method \string requireSystemCode()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl resetSystemCode()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unsetSystemCode()
	 * @method \string fillSystemCode()
	 * @method \boolean getActive()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl resetActive()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unsetActive()
	 * @method \boolean fillActive()
	 * @method \string getOriginatorId()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setOriginatorId(\string|\Bitrix\Main\DB\SqlExpression $originatorId)
	 * @method bool hasOriginatorId()
	 * @method bool isOriginatorIdFilled()
	 * @method bool isOriginatorIdChanged()
	 * @method \string remindActualOriginatorId()
	 * @method \string requireOriginatorId()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl resetOriginatorId()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unsetOriginatorId()
	 * @method \string fillOriginatorId()
	 * @method \string getOriginId()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setOriginId(\string|\Bitrix\Main\DB\SqlExpression $originId)
	 * @method bool hasOriginId()
	 * @method bool isOriginIdFilled()
	 * @method bool isOriginIdChanged()
	 * @method \string remindActualOriginId()
	 * @method \string requireOriginId()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl resetOriginId()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unsetOriginId()
	 * @method \string fillOriginId()
	 * @method \Bitrix\Main\EO_User getUser()
	 * @method \Bitrix\Main\EO_User remindActualUser()
	 * @method \Bitrix\Main\EO_User requireUser()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl resetUser()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unsetUser()
	 * @method bool hasUser()
	 * @method bool isUserFilled()
	 * @method bool isUserChanged()
	 * @method \Bitrix\Main\EO_User fillUser()
	 * @method \boolean getIsSystem()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setIsSystem(\boolean|\Bitrix\Main\DB\SqlExpression $isSystem)
	 * @method bool hasIsSystem()
	 * @method bool isIsSystemFilled()
	 * @method bool isIsSystemChanged()
	 * @method \boolean remindActualIsSystem()
	 * @method \boolean requireIsSystem()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl resetIsSystem()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unsetIsSystem()
	 * @method \boolean fillIsSystem()
	 * @method \int getSort()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl resetSort()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unsetSort()
	 * @method \int fillSort()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl reset($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Workflow\Template\Tpl wakeUp($data)
	 */
	class EO_WorkflowTemplate {
		/* @var \Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Workflow\Template\Entity {
	/**
	 * EO_WorkflowTemplate_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getModuleIdList()
	 * @method \string[] fillModuleId()
	 * @method \string[] getEntityList()
	 * @method \string[] fillEntity()
	 * @method \string[] getDocumentTypeList()
	 * @method \string[] fillDocumentType()
	 * @method \string[] getDocumentStatusList()
	 * @method \string[] fillDocumentStatus()
	 * @method \int[] getAutoExecuteList()
	 * @method \int[] fillAutoExecute()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method array[] getTemplateList()
	 * @method array[] fillTemplate()
	 * @method array[] getParametersList()
	 * @method array[] fillParameters()
	 * @method array[] getVariablesList()
	 * @method array[] fillVariables()
	 * @method array[] getConstantsList()
	 * @method array[] fillConstants()
	 * @method \Bitrix\Main\Type\DateTime[] getModifiedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillModified()
	 * @method \boolean[] getIsModifiedList()
	 * @method \boolean[] fillIsModified()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \string[] getSystemCodeList()
	 * @method \string[] fillSystemCode()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \string[] getOriginatorIdList()
	 * @method \string[] fillOriginatorId()
	 * @method \string[] getOriginIdList()
	 * @method \string[] fillOriginId()
	 * @method \Bitrix\Main\EO_User[] getUserList()
	 * @method \Bitrix\Bizproc\Workflow\Template\Entity\EO_WorkflowTemplate_Collection getUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillUser()
	 * @method \boolean[] getIsSystemList()
	 * @method \boolean[] fillIsSystem()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Workflow\Template\Tpl $object)
	 * @method bool has(\Bitrix\Bizproc\Workflow\Template\Tpl $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Workflow\Template\Tpl $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Workflow\Template\Entity\EO_WorkflowTemplate_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_WorkflowTemplate_Collection merge(?EO_WorkflowTemplate_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_WorkflowTemplate_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable';
	}
}
namespace Bitrix\Bizproc\Workflow\Template\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_WorkflowTemplate_Result exec()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Template\Entity\EO_WorkflowTemplate_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_WorkflowTemplate_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Template\Entity\EO_WorkflowTemplate_Collection fetchCollection()
	 */
	class EO_WorkflowTemplate_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Workflow\Template\Entity\EO_WorkflowTemplate_Collection createCollection()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Workflow\Template\Entity\EO_WorkflowTemplate_Collection wakeUpCollection($rows)
	 */
	class EO_WorkflowTemplate_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable:bizproc/lib/workflow/entity/workflowinstance.php */
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * EO_WorkflowInstance
	 * @see \Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance setId(\string|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getModuleId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance resetModuleId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance unsetModuleId()
	 * @method \string fillModuleId()
	 * @method \string getEntity()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance setEntity(\string|\Bitrix\Main\DB\SqlExpression $entity)
	 * @method bool hasEntity()
	 * @method bool isEntityFilled()
	 * @method bool isEntityChanged()
	 * @method \string remindActualEntity()
	 * @method \string requireEntity()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance resetEntity()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance unsetEntity()
	 * @method \string fillEntity()
	 * @method \string getDocumentId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance setDocumentId(\string|\Bitrix\Main\DB\SqlExpression $documentId)
	 * @method bool hasDocumentId()
	 * @method bool isDocumentIdFilled()
	 * @method bool isDocumentIdChanged()
	 * @method \string remindActualDocumentId()
	 * @method \string requireDocumentId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance resetDocumentId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance unsetDocumentId()
	 * @method \string fillDocumentId()
	 * @method \int getWorkflowTemplateId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance setWorkflowTemplateId(\int|\Bitrix\Main\DB\SqlExpression $workflowTemplateId)
	 * @method bool hasWorkflowTemplateId()
	 * @method bool isWorkflowTemplateIdFilled()
	 * @method bool isWorkflowTemplateIdChanged()
	 * @method \int remindActualWorkflowTemplateId()
	 * @method \int requireWorkflowTemplateId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance resetWorkflowTemplateId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance unsetWorkflowTemplateId()
	 * @method \int fillWorkflowTemplateId()
	 * @method \string getWorkflow()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance setWorkflow(\string|\Bitrix\Main\DB\SqlExpression $workflow)
	 * @method bool hasWorkflow()
	 * @method bool isWorkflowFilled()
	 * @method bool isWorkflowChanged()
	 * @method \string remindActualWorkflow()
	 * @method \string requireWorkflow()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance resetWorkflow()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance unsetWorkflow()
	 * @method \string fillWorkflow()
	 * @method \string getWorkflowRo()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance setWorkflowRo(\string|\Bitrix\Main\DB\SqlExpression $workflowRo)
	 * @method bool hasWorkflowRo()
	 * @method bool isWorkflowRoFilled()
	 * @method bool isWorkflowRoChanged()
	 * @method \string remindActualWorkflowRo()
	 * @method \string requireWorkflowRo()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance resetWorkflowRo()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance unsetWorkflowRo()
	 * @method \string fillWorkflowRo()
	 * @method \Bitrix\Main\Type\DateTime getStarted()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance setStarted(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $started)
	 * @method bool hasStarted()
	 * @method bool isStartedFilled()
	 * @method bool isStartedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualStarted()
	 * @method \Bitrix\Main\Type\DateTime requireStarted()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance resetStarted()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance unsetStarted()
	 * @method \Bitrix\Main\Type\DateTime fillStarted()
	 * @method \int getStartedBy()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance setStartedBy(\int|\Bitrix\Main\DB\SqlExpression $startedBy)
	 * @method bool hasStartedBy()
	 * @method bool isStartedByFilled()
	 * @method bool isStartedByChanged()
	 * @method \int remindActualStartedBy()
	 * @method \int requireStartedBy()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance resetStartedBy()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance unsetStartedBy()
	 * @method \int fillStartedBy()
	 * @method \Bitrix\Main\EO_User getStartedUser()
	 * @method \Bitrix\Main\EO_User remindActualStartedUser()
	 * @method \Bitrix\Main\EO_User requireStartedUser()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance setStartedUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance resetStartedUser()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance unsetStartedUser()
	 * @method bool hasStartedUser()
	 * @method bool isStartedUserFilled()
	 * @method bool isStartedUserChanged()
	 * @method \Bitrix\Main\EO_User fillStartedUser()
	 * @method \int getStartedEventType()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance setStartedEventType(\int|\Bitrix\Main\DB\SqlExpression $startedEventType)
	 * @method bool hasStartedEventType()
	 * @method bool isStartedEventTypeFilled()
	 * @method bool isStartedEventTypeChanged()
	 * @method \int remindActualStartedEventType()
	 * @method \int requireStartedEventType()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance resetStartedEventType()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance unsetStartedEventType()
	 * @method \int fillStartedEventType()
	 * @method \int getStatus()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance setStatus(\int|\Bitrix\Main\DB\SqlExpression $status)
	 * @method bool hasStatus()
	 * @method bool isStatusFilled()
	 * @method bool isStatusChanged()
	 * @method \int remindActualStatus()
	 * @method \int requireStatus()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance resetStatus()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance unsetStatus()
	 * @method \int fillStatus()
	 * @method \Bitrix\Main\Type\DateTime getModified()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance setModified(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $modified)
	 * @method bool hasModified()
	 * @method bool isModifiedFilled()
	 * @method bool isModifiedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualModified()
	 * @method \Bitrix\Main\Type\DateTime requireModified()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance resetModified()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance unsetModified()
	 * @method \Bitrix\Main\Type\DateTime fillModified()
	 * @method \string getOwnerId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance setOwnerId(\string|\Bitrix\Main\DB\SqlExpression $ownerId)
	 * @method bool hasOwnerId()
	 * @method bool isOwnerIdFilled()
	 * @method bool isOwnerIdChanged()
	 * @method \string remindActualOwnerId()
	 * @method \string requireOwnerId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance resetOwnerId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance unsetOwnerId()
	 * @method \string fillOwnerId()
	 * @method \Bitrix\Main\Type\DateTime getOwnedUntil()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance setOwnedUntil(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $ownedUntil)
	 * @method bool hasOwnedUntil()
	 * @method bool isOwnedUntilFilled()
	 * @method bool isOwnedUntilChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualOwnedUntil()
	 * @method \Bitrix\Main\Type\DateTime requireOwnedUntil()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance resetOwnedUntil()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance unsetOwnedUntil()
	 * @method \Bitrix\Main\Type\DateTime fillOwnedUntil()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState getState()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState remindActualState()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState requireState()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance setState(\Bitrix\Bizproc\Workflow\WorkflowState $object)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance resetState()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance unsetState()
	 * @method bool hasState()
	 * @method bool isStateFilled()
	 * @method bool isStateChanged()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState fillState()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl getTemplate()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl remindActualTemplate()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl requireTemplate()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance setTemplate(\Bitrix\Bizproc\Workflow\Template\Tpl $object)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance resetTemplate()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance unsetTemplate()
	 * @method bool hasTemplate()
	 * @method bool isTemplateFilled()
	 * @method bool isTemplateChanged()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl fillTemplate()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance reset($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance wakeUp($data)
	 */
	class EO_WorkflowInstance {
		/* @var \Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * EO_WorkflowInstance_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getIdList()
	 * @method \string[] getModuleIdList()
	 * @method \string[] fillModuleId()
	 * @method \string[] getEntityList()
	 * @method \string[] fillEntity()
	 * @method \string[] getDocumentIdList()
	 * @method \string[] fillDocumentId()
	 * @method \int[] getWorkflowTemplateIdList()
	 * @method \int[] fillWorkflowTemplateId()
	 * @method \string[] getWorkflowList()
	 * @method \string[] fillWorkflow()
	 * @method \string[] getWorkflowRoList()
	 * @method \string[] fillWorkflowRo()
	 * @method \Bitrix\Main\Type\DateTime[] getStartedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillStarted()
	 * @method \int[] getStartedByList()
	 * @method \int[] fillStartedBy()
	 * @method \Bitrix\Main\EO_User[] getStartedUserList()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance_Collection getStartedUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillStartedUser()
	 * @method \int[] getStartedEventTypeList()
	 * @method \int[] fillStartedEventType()
	 * @method \int[] getStatusList()
	 * @method \int[] fillStatus()
	 * @method \Bitrix\Main\Type\DateTime[] getModifiedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillModified()
	 * @method \string[] getOwnerIdList()
	 * @method \string[] fillOwnerId()
	 * @method \Bitrix\Main\Type\DateTime[] getOwnedUntilList()
	 * @method \Bitrix\Main\Type\DateTime[] fillOwnedUntil()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState[] getStateList()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance_Collection getStateCollection()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection fillState()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl[] getTemplateList()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance_Collection getTemplateCollection()
	 * @method \Bitrix\Bizproc\Workflow\Template\Entity\EO_WorkflowTemplate_Collection fillTemplate()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance $object)
	 * @method bool has(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_WorkflowInstance_Collection merge(?EO_WorkflowInstance_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_WorkflowInstance_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable';
	}
}
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_WorkflowInstance_Result exec()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_WorkflowInstance_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance_Collection fetchCollection()
	 */
	class EO_WorkflowInstance_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance_Collection createCollection()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance_Collection wakeUpCollection($rows)
	 */
	class EO_WorkflowInstance_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable:bizproc/lib/workflow/entity/workflowstate.php */
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * WorkflowState
	 * @see \Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getId()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState setId(\string|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getModuleId()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState resetModuleId()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState unsetModuleId()
	 * @method \string fillModuleId()
	 * @method \string getEntity()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState setEntity(\string|\Bitrix\Main\DB\SqlExpression $entity)
	 * @method bool hasEntity()
	 * @method bool isEntityFilled()
	 * @method bool isEntityChanged()
	 * @method \string remindActualEntity()
	 * @method \string requireEntity()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState resetEntity()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState unsetEntity()
	 * @method \string fillEntity()
	 * @method \string getDocumentId()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState setDocumentId(\string|\Bitrix\Main\DB\SqlExpression $documentId)
	 * @method bool hasDocumentId()
	 * @method bool isDocumentIdFilled()
	 * @method bool isDocumentIdChanged()
	 * @method \string remindActualDocumentId()
	 * @method \string requireDocumentId()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState resetDocumentId()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState unsetDocumentId()
	 * @method \string fillDocumentId()
	 * @method \int getDocumentIdInt()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState setDocumentIdInt(\int|\Bitrix\Main\DB\SqlExpression $documentIdInt)
	 * @method bool hasDocumentIdInt()
	 * @method bool isDocumentIdIntFilled()
	 * @method bool isDocumentIdIntChanged()
	 * @method \int remindActualDocumentIdInt()
	 * @method \int requireDocumentIdInt()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState resetDocumentIdInt()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState unsetDocumentIdInt()
	 * @method \int fillDocumentIdInt()
	 * @method \int getWorkflowTemplateId()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState setWorkflowTemplateId(\int|\Bitrix\Main\DB\SqlExpression $workflowTemplateId)
	 * @method bool hasWorkflowTemplateId()
	 * @method bool isWorkflowTemplateIdFilled()
	 * @method bool isWorkflowTemplateIdChanged()
	 * @method \int remindActualWorkflowTemplateId()
	 * @method \int requireWorkflowTemplateId()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState resetWorkflowTemplateId()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState unsetWorkflowTemplateId()
	 * @method \int fillWorkflowTemplateId()
	 * @method \string getState()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState setState(\string|\Bitrix\Main\DB\SqlExpression $state)
	 * @method bool hasState()
	 * @method bool isStateFilled()
	 * @method bool isStateChanged()
	 * @method \string remindActualState()
	 * @method \string requireState()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState resetState()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState unsetState()
	 * @method \string fillState()
	 * @method \string getStateTitle()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState setStateTitle(\string|\Bitrix\Main\DB\SqlExpression $stateTitle)
	 * @method bool hasStateTitle()
	 * @method bool isStateTitleFilled()
	 * @method bool isStateTitleChanged()
	 * @method \string remindActualStateTitle()
	 * @method \string requireStateTitle()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState resetStateTitle()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState unsetStateTitle()
	 * @method \string fillStateTitle()
	 * @method \string getStateParameters()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState setStateParameters(\string|\Bitrix\Main\DB\SqlExpression $stateParameters)
	 * @method bool hasStateParameters()
	 * @method bool isStateParametersFilled()
	 * @method bool isStateParametersChanged()
	 * @method \string remindActualStateParameters()
	 * @method \string requireStateParameters()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState resetStateParameters()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState unsetStateParameters()
	 * @method \string fillStateParameters()
	 * @method \Bitrix\Main\Type\DateTime getModified()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState setModified(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $modified)
	 * @method bool hasModified()
	 * @method bool isModifiedFilled()
	 * @method bool isModifiedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualModified()
	 * @method \Bitrix\Main\Type\DateTime requireModified()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState resetModified()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState unsetModified()
	 * @method \Bitrix\Main\Type\DateTime fillModified()
	 * @method \Bitrix\Main\Type\DateTime getStarted()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState setStarted(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $started)
	 * @method bool hasStarted()
	 * @method bool isStartedFilled()
	 * @method bool isStartedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualStarted()
	 * @method \Bitrix\Main\Type\DateTime requireStarted()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState resetStarted()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState unsetStarted()
	 * @method \Bitrix\Main\Type\DateTime fillStarted()
	 * @method \int getStartedBy()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState setStartedBy(\int|\Bitrix\Main\DB\SqlExpression $startedBy)
	 * @method bool hasStartedBy()
	 * @method bool isStartedByFilled()
	 * @method bool isStartedByChanged()
	 * @method \int remindActualStartedBy()
	 * @method \int requireStartedBy()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState resetStartedBy()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState unsetStartedBy()
	 * @method \int fillStartedBy()
	 * @method \Bitrix\Main\EO_User getStartedUser()
	 * @method \Bitrix\Main\EO_User remindActualStartedUser()
	 * @method \Bitrix\Main\EO_User requireStartedUser()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState setStartedUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState resetStartedUser()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState unsetStartedUser()
	 * @method bool hasStartedUser()
	 * @method bool isStartedUserFilled()
	 * @method bool isStartedUserChanged()
	 * @method \Bitrix\Main\EO_User fillStartedUser()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance getInstance()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance remindActualInstance()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance requireInstance()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState setInstance(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance $object)
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState resetInstance()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState unsetInstance()
	 * @method bool hasInstance()
	 * @method bool isInstanceFilled()
	 * @method bool isInstanceChanged()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance fillInstance()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl getTemplate()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl remindActualTemplate()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl requireTemplate()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState setTemplate(\Bitrix\Bizproc\Workflow\Template\Tpl $object)
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState resetTemplate()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState unsetTemplate()
	 * @method bool hasTemplate()
	 * @method bool isTemplateFilled()
	 * @method bool isTemplateChanged()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl fillTemplate()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_Task_Collection getTasks()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_Task_Collection requireTasks()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_Task_Collection fillTasks()
	 * @method bool hasTasks()
	 * @method bool isTasksFilled()
	 * @method bool isTasksChanged()
	 * @method void addToTasks(\Bitrix\Bizproc\Workflow\Task $task)
	 * @method void removeFromTasks(\Bitrix\Bizproc\Workflow\Task $task)
	 * @method void removeAllTasks()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState resetTasks()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState unsetTasks()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata getMeta()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata remindActualMeta()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata requireMeta()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState setMeta(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata $object)
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState resetMeta()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState unsetMeta()
	 * @method bool hasMeta()
	 * @method bool isMetaFilled()
	 * @method bool isMetaChanged()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata fillMeta()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState reset($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Workflow\WorkflowState wakeUp($data)
	 */
	class EO_WorkflowState {
		/* @var \Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * EO_WorkflowState_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getIdList()
	 * @method \string[] getModuleIdList()
	 * @method \string[] fillModuleId()
	 * @method \string[] getEntityList()
	 * @method \string[] fillEntity()
	 * @method \string[] getDocumentIdList()
	 * @method \string[] fillDocumentId()
	 * @method \int[] getDocumentIdIntList()
	 * @method \int[] fillDocumentIdInt()
	 * @method \int[] getWorkflowTemplateIdList()
	 * @method \int[] fillWorkflowTemplateId()
	 * @method \string[] getStateList()
	 * @method \string[] fillState()
	 * @method \string[] getStateTitleList()
	 * @method \string[] fillStateTitle()
	 * @method \string[] getStateParametersList()
	 * @method \string[] fillStateParameters()
	 * @method \Bitrix\Main\Type\DateTime[] getModifiedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillModified()
	 * @method \Bitrix\Main\Type\DateTime[] getStartedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillStarted()
	 * @method \int[] getStartedByList()
	 * @method \int[] fillStartedBy()
	 * @method \Bitrix\Main\EO_User[] getStartedUserList()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection getStartedUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillStartedUser()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance[] getInstanceList()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection getInstanceCollection()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance_Collection fillInstance()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl[] getTemplateList()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection getTemplateCollection()
	 * @method \Bitrix\Bizproc\Workflow\Template\Entity\EO_WorkflowTemplate_Collection fillTemplate()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_Task_Collection[] getTasksList()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_Task_Collection getTasksCollection()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_Task_Collection fillTasks()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata[] getMetaList()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection getMetaCollection()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata_Collection fillMeta()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Workflow\WorkflowState $object)
	 * @method bool has(\Bitrix\Bizproc\Workflow\WorkflowState $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Workflow\WorkflowState $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_WorkflowState_Collection merge(?EO_WorkflowState_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_WorkflowState_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable';
	}
}
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_WorkflowState_Result exec()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_WorkflowState_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection fetchCollection()
	 */
	class EO_WorkflowState_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection createCollection()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection wakeUpCollection($rows)
	 */
	class EO_WorkflowState_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Script\Entity\ScriptTable:bizproc/lib/script/entity/script.php */
namespace Bitrix\Bizproc\Script\Entity {
	/**
	 * EO_Script
	 * @see \Bitrix\Bizproc\Script\Entity\ScriptTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getModuleId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script resetModuleId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script unsetModuleId()
	 * @method \string fillModuleId()
	 * @method \string getEntity()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script setEntity(\string|\Bitrix\Main\DB\SqlExpression $entity)
	 * @method bool hasEntity()
	 * @method bool isEntityFilled()
	 * @method bool isEntityChanged()
	 * @method \string remindActualEntity()
	 * @method \string requireEntity()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script resetEntity()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script unsetEntity()
	 * @method \string fillEntity()
	 * @method \string getDocumentType()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script setDocumentType(\string|\Bitrix\Main\DB\SqlExpression $documentType)
	 * @method bool hasDocumentType()
	 * @method bool isDocumentTypeFilled()
	 * @method bool isDocumentTypeChanged()
	 * @method \string remindActualDocumentType()
	 * @method \string requireDocumentType()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script resetDocumentType()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script unsetDocumentType()
	 * @method \string fillDocumentType()
	 * @method \string getName()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script resetName()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script unsetName()
	 * @method \string fillName()
	 * @method \string getDescription()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script resetDescription()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script unsetDescription()
	 * @method \string fillDescription()
	 * @method \int getWorkflowTemplateId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script setWorkflowTemplateId(\int|\Bitrix\Main\DB\SqlExpression $workflowTemplateId)
	 * @method bool hasWorkflowTemplateId()
	 * @method bool isWorkflowTemplateIdFilled()
	 * @method bool isWorkflowTemplateIdChanged()
	 * @method \int remindActualWorkflowTemplateId()
	 * @method \int requireWorkflowTemplateId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script resetWorkflowTemplateId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script unsetWorkflowTemplateId()
	 * @method \int fillWorkflowTemplateId()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl getWorkflowTemplate()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl remindActualWorkflowTemplate()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl requireWorkflowTemplate()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script setWorkflowTemplate(\Bitrix\Bizproc\Workflow\Template\Tpl $object)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script resetWorkflowTemplate()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script unsetWorkflowTemplate()
	 * @method bool hasWorkflowTemplate()
	 * @method bool isWorkflowTemplateFilled()
	 * @method bool isWorkflowTemplateChanged()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl fillWorkflowTemplate()
	 * @method \Bitrix\Main\Type\DateTime getCreatedDate()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script setCreatedDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $createdDate)
	 * @method bool hasCreatedDate()
	 * @method bool isCreatedDateFilled()
	 * @method bool isCreatedDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualCreatedDate()
	 * @method \Bitrix\Main\Type\DateTime requireCreatedDate()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script resetCreatedDate()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script unsetCreatedDate()
	 * @method \Bitrix\Main\Type\DateTime fillCreatedDate()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script resetCreatedBy()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime getModifiedDate()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script setModifiedDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $modifiedDate)
	 * @method bool hasModifiedDate()
	 * @method bool isModifiedDateFilled()
	 * @method bool isModifiedDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualModifiedDate()
	 * @method \Bitrix\Main\Type\DateTime requireModifiedDate()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script resetModifiedDate()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script unsetModifiedDate()
	 * @method \Bitrix\Main\Type\DateTime fillModifiedDate()
	 * @method \int getModifiedBy()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script setModifiedBy(\int|\Bitrix\Main\DB\SqlExpression $modifiedBy)
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \int remindActualModifiedBy()
	 * @method \int requireModifiedBy()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script resetModifiedBy()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script unsetModifiedBy()
	 * @method \int fillModifiedBy()
	 * @method \string getOriginatorId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script setOriginatorId(\string|\Bitrix\Main\DB\SqlExpression $originatorId)
	 * @method bool hasOriginatorId()
	 * @method bool isOriginatorIdFilled()
	 * @method bool isOriginatorIdChanged()
	 * @method \string remindActualOriginatorId()
	 * @method \string requireOriginatorId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script resetOriginatorId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script unsetOriginatorId()
	 * @method \string fillOriginatorId()
	 * @method \string getOriginId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script setOriginId(\string|\Bitrix\Main\DB\SqlExpression $originId)
	 * @method bool hasOriginId()
	 * @method bool isOriginIdFilled()
	 * @method bool isOriginIdChanged()
	 * @method \string remindActualOriginId()
	 * @method \string requireOriginId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script resetOriginId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script unsetOriginId()
	 * @method \string fillOriginId()
	 * @method \int getSort()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script setSort(\int|\Bitrix\Main\DB\SqlExpression $sort)
	 * @method bool hasSort()
	 * @method bool isSortFilled()
	 * @method bool isSortChanged()
	 * @method \int remindActualSort()
	 * @method \int requireSort()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script resetSort()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script unsetSort()
	 * @method \int fillSort()
	 * @method \boolean getActive()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script resetActive()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script unsetActive()
	 * @method \boolean fillActive()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script reset($fieldName)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Script\Entity\EO_Script wakeUp($data)
	 */
	class EO_Script {
		/* @var \Bitrix\Bizproc\Script\Entity\ScriptTable */
		static public $dataClass = '\Bitrix\Bizproc\Script\Entity\ScriptTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Script\Entity {
	/**
	 * EO_Script_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getModuleIdList()
	 * @method \string[] fillModuleId()
	 * @method \string[] getEntityList()
	 * @method \string[] fillEntity()
	 * @method \string[] getDocumentTypeList()
	 * @method \string[] fillDocumentType()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \int[] getWorkflowTemplateIdList()
	 * @method \int[] fillWorkflowTemplateId()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl[] getWorkflowTemplateList()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script_Collection getWorkflowTemplateCollection()
	 * @method \Bitrix\Bizproc\Workflow\Template\Entity\EO_WorkflowTemplate_Collection fillWorkflowTemplate()
	 * @method \Bitrix\Main\Type\DateTime[] getCreatedDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillCreatedDate()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getModifiedDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillModifiedDate()
	 * @method \int[] getModifiedByList()
	 * @method \int[] fillModifiedBy()
	 * @method \string[] getOriginatorIdList()
	 * @method \string[] fillOriginatorId()
	 * @method \string[] getOriginIdList()
	 * @method \string[] fillOriginId()
	 * @method \int[] getSortList()
	 * @method \int[] fillSort()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Script\Entity\EO_Script $object)
	 * @method bool has(\Bitrix\Bizproc\Script\Entity\EO_Script $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Script\Entity\EO_Script $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Script\Entity\EO_Script_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_Script_Collection merge(?EO_Script_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Script_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Script\Entity\ScriptTable */
		static public $dataClass = '\Bitrix\Bizproc\Script\Entity\ScriptTable';
	}
}
namespace Bitrix\Bizproc\Script\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Script_Result exec()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script fetchObject()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Script_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script fetchObject()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script_Collection fetchCollection()
	 */
	class EO_Script_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script_Collection createCollection()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_Script_Collection wakeUpCollection($rows)
	 */
	class EO_Script_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Script\Entity\ScriptQueueDocumentTable:bizproc/lib/script/entity/scriptqueuedocument.php */
namespace Bitrix\Bizproc\Script\Entity {
	/**
	 * EO_ScriptQueueDocument
	 * @see \Bitrix\Bizproc\Script\Entity\ScriptQueueDocumentTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getQueueId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument setQueueId(\int|\Bitrix\Main\DB\SqlExpression $queueId)
	 * @method bool hasQueueId()
	 * @method bool isQueueIdFilled()
	 * @method bool isQueueIdChanged()
	 * @method \int remindActualQueueId()
	 * @method \int requireQueueId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument resetQueueId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument unsetQueueId()
	 * @method \int fillQueueId()
	 * @method \string getDocumentId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument setDocumentId(\string|\Bitrix\Main\DB\SqlExpression $documentId)
	 * @method bool hasDocumentId()
	 * @method bool isDocumentIdFilled()
	 * @method bool isDocumentIdChanged()
	 * @method \string remindActualDocumentId()
	 * @method \string requireDocumentId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument resetDocumentId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument unsetDocumentId()
	 * @method \string fillDocumentId()
	 * @method \string getWorkflowId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument setWorkflowId(\string|\Bitrix\Main\DB\SqlExpression $workflowId)
	 * @method bool hasWorkflowId()
	 * @method bool isWorkflowIdFilled()
	 * @method bool isWorkflowIdChanged()
	 * @method \string remindActualWorkflowId()
	 * @method \string requireWorkflowId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument resetWorkflowId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument unsetWorkflowId()
	 * @method \string fillWorkflowId()
	 * @method \int getStatus()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument setStatus(\int|\Bitrix\Main\DB\SqlExpression $status)
	 * @method bool hasStatus()
	 * @method bool isStatusFilled()
	 * @method bool isStatusChanged()
	 * @method \int remindActualStatus()
	 * @method \int requireStatus()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument resetStatus()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument unsetStatus()
	 * @method \int fillStatus()
	 * @method \string getStatusMessage()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument setStatusMessage(\string|\Bitrix\Main\DB\SqlExpression $statusMessage)
	 * @method bool hasStatusMessage()
	 * @method bool isStatusMessageFilled()
	 * @method bool isStatusMessageChanged()
	 * @method \string remindActualStatusMessage()
	 * @method \string requireStatusMessage()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument resetStatusMessage()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument unsetStatusMessage()
	 * @method \string fillStatusMessage()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue getQueue()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue remindActualQueue()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue requireQueue()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument setQueue(\Bitrix\Bizproc\Script\Entity\EO_ScriptQueue $object)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument resetQueue()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument unsetQueue()
	 * @method bool hasQueue()
	 * @method bool isQueueFilled()
	 * @method bool isQueueChanged()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue fillQueue()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument reset($fieldName)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument wakeUp($data)
	 */
	class EO_ScriptQueueDocument {
		/* @var \Bitrix\Bizproc\Script\Entity\ScriptQueueDocumentTable */
		static public $dataClass = '\Bitrix\Bizproc\Script\Entity\ScriptQueueDocumentTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Script\Entity {
	/**
	 * EO_ScriptQueueDocument_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getQueueIdList()
	 * @method \int[] fillQueueId()
	 * @method \string[] getDocumentIdList()
	 * @method \string[] fillDocumentId()
	 * @method \string[] getWorkflowIdList()
	 * @method \string[] fillWorkflowId()
	 * @method \int[] getStatusList()
	 * @method \int[] fillStatus()
	 * @method \string[] getStatusMessageList()
	 * @method \string[] fillStatusMessage()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue[] getQueueList()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument_Collection getQueueCollection()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue_Collection fillQueue()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument $object)
	 * @method bool has(\Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_ScriptQueueDocument_Collection merge(?EO_ScriptQueueDocument_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_ScriptQueueDocument_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Script\Entity\ScriptQueueDocumentTable */
		static public $dataClass = '\Bitrix\Bizproc\Script\Entity\ScriptQueueDocumentTable';
	}
}
namespace Bitrix\Bizproc\Script\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ScriptQueueDocument_Result exec()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument fetchObject()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ScriptQueueDocument_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument fetchObject()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument_Collection fetchCollection()
	 */
	class EO_ScriptQueueDocument_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument_Collection createCollection()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueueDocument_Collection wakeUpCollection($rows)
	 */
	class EO_ScriptQueueDocument_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Script\Entity\ScriptQueueTable:bizproc/lib/script/entity/scriptqueue.php */
namespace Bitrix\Bizproc\Script\Entity {
	/**
	 * EO_ScriptQueue
	 * @see \Bitrix\Bizproc\Script\Entity\ScriptQueueTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getScriptId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue setScriptId(\int|\Bitrix\Main\DB\SqlExpression $scriptId)
	 * @method bool hasScriptId()
	 * @method bool isScriptIdFilled()
	 * @method bool isScriptIdChanged()
	 * @method \int remindActualScriptId()
	 * @method \int requireScriptId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue resetScriptId()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue unsetScriptId()
	 * @method \int fillScriptId()
	 * @method \Bitrix\Main\Type\DateTime getStartedDate()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue setStartedDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $startedDate)
	 * @method bool hasStartedDate()
	 * @method bool isStartedDateFilled()
	 * @method bool isStartedDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualStartedDate()
	 * @method \Bitrix\Main\Type\DateTime requireStartedDate()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue resetStartedDate()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue unsetStartedDate()
	 * @method \Bitrix\Main\Type\DateTime fillStartedDate()
	 * @method \int getStartedBy()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue setStartedBy(\int|\Bitrix\Main\DB\SqlExpression $startedBy)
	 * @method bool hasStartedBy()
	 * @method bool isStartedByFilled()
	 * @method bool isStartedByChanged()
	 * @method \int remindActualStartedBy()
	 * @method \int requireStartedBy()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue resetStartedBy()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue unsetStartedBy()
	 * @method \int fillStartedBy()
	 * @method \Bitrix\Main\EO_User getStartedUser()
	 * @method \Bitrix\Main\EO_User remindActualStartedUser()
	 * @method \Bitrix\Main\EO_User requireStartedUser()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue setStartedUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue resetStartedUser()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue unsetStartedUser()
	 * @method bool hasStartedUser()
	 * @method bool isStartedUserFilled()
	 * @method bool isStartedUserChanged()
	 * @method \Bitrix\Main\EO_User fillStartedUser()
	 * @method \int getStatus()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue setStatus(\int|\Bitrix\Main\DB\SqlExpression $status)
	 * @method bool hasStatus()
	 * @method bool isStatusFilled()
	 * @method bool isStatusChanged()
	 * @method \int remindActualStatus()
	 * @method \int requireStatus()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue resetStatus()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue unsetStatus()
	 * @method \int fillStatus()
	 * @method \Bitrix\Main\Type\DateTime getModifiedDate()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue setModifiedDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $modifiedDate)
	 * @method bool hasModifiedDate()
	 * @method bool isModifiedDateFilled()
	 * @method bool isModifiedDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualModifiedDate()
	 * @method \Bitrix\Main\Type\DateTime requireModifiedDate()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue resetModifiedDate()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue unsetModifiedDate()
	 * @method \Bitrix\Main\Type\DateTime fillModifiedDate()
	 * @method \int getModifiedBy()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue setModifiedBy(\int|\Bitrix\Main\DB\SqlExpression $modifiedBy)
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \int remindActualModifiedBy()
	 * @method \int requireModifiedBy()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue resetModifiedBy()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue unsetModifiedBy()
	 * @method \int fillModifiedBy()
	 * @method array getWorkflowParameters()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue setWorkflowParameters(array|\Bitrix\Main\DB\SqlExpression $workflowParameters)
	 * @method bool hasWorkflowParameters()
	 * @method bool isWorkflowParametersFilled()
	 * @method bool isWorkflowParametersChanged()
	 * @method array remindActualWorkflowParameters()
	 * @method array requireWorkflowParameters()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue resetWorkflowParameters()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue unsetWorkflowParameters()
	 * @method array fillWorkflowParameters()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue reset($fieldName)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue wakeUp($data)
	 */
	class EO_ScriptQueue {
		/* @var \Bitrix\Bizproc\Script\Entity\ScriptQueueTable */
		static public $dataClass = '\Bitrix\Bizproc\Script\Entity\ScriptQueueTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Script\Entity {
	/**
	 * EO_ScriptQueue_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getScriptIdList()
	 * @method \int[] fillScriptId()
	 * @method \Bitrix\Main\Type\DateTime[] getStartedDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillStartedDate()
	 * @method \int[] getStartedByList()
	 * @method \int[] fillStartedBy()
	 * @method \Bitrix\Main\EO_User[] getStartedUserList()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue_Collection getStartedUserCollection()
	 * @method \Bitrix\Main\EO_User_Collection fillStartedUser()
	 * @method \int[] getStatusList()
	 * @method \int[] fillStatus()
	 * @method \Bitrix\Main\Type\DateTime[] getModifiedDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillModifiedDate()
	 * @method \int[] getModifiedByList()
	 * @method \int[] fillModifiedBy()
	 * @method array[] getWorkflowParametersList()
	 * @method array[] fillWorkflowParameters()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Script\Entity\EO_ScriptQueue $object)
	 * @method bool has(\Bitrix\Bizproc\Script\Entity\EO_ScriptQueue $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Script\Entity\EO_ScriptQueue $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_ScriptQueue_Collection merge(?EO_ScriptQueue_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_ScriptQueue_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Script\Entity\ScriptQueueTable */
		static public $dataClass = '\Bitrix\Bizproc\Script\Entity\ScriptQueueTable';
	}
}
namespace Bitrix\Bizproc\Script\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ScriptQueue_Result exec()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue fetchObject()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ScriptQueue_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue fetchObject()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue_Collection fetchCollection()
	 */
	class EO_ScriptQueue_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue_Collection createCollection()
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Script\Entity\EO_ScriptQueue_Collection wakeUpCollection($rows)
	 */
	class EO_ScriptQueue_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Storage\Entity\ActivityStorageTable:bizproc/lib/storage/entity/activitystoragetable.php */
namespace Bitrix\Bizproc\Storage\Entity {
	/**
	 * EO_ActivityStorage
	 * @see \Bitrix\Bizproc\Storage\Entity\ActivityStorageTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getWorkflowTemplateId()
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage setWorkflowTemplateId(\int|\Bitrix\Main\DB\SqlExpression $workflowTemplateId)
	 * @method bool hasWorkflowTemplateId()
	 * @method bool isWorkflowTemplateIdFilled()
	 * @method bool isWorkflowTemplateIdChanged()
	 * @method \int remindActualWorkflowTemplateId()
	 * @method \int requireWorkflowTemplateId()
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage resetWorkflowTemplateId()
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage unsetWorkflowTemplateId()
	 * @method \int fillWorkflowTemplateId()
	 * @method \string getActivityName()
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage setActivityName(\string|\Bitrix\Main\DB\SqlExpression $activityName)
	 * @method bool hasActivityName()
	 * @method bool isActivityNameFilled()
	 * @method bool isActivityNameChanged()
	 * @method \string remindActualActivityName()
	 * @method \string requireActivityName()
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage resetActivityName()
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage unsetActivityName()
	 * @method \string fillActivityName()
	 * @method \string getKeyId()
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage setKeyId(\string|\Bitrix\Main\DB\SqlExpression $keyId)
	 * @method bool hasKeyId()
	 * @method bool isKeyIdFilled()
	 * @method bool isKeyIdChanged()
	 * @method \string remindActualKeyId()
	 * @method \string requireKeyId()
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage resetKeyId()
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage unsetKeyId()
	 * @method \string fillKeyId()
	 * @method \string getKeyValue()
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage setKeyValue(\string|\Bitrix\Main\DB\SqlExpression $keyValue)
	 * @method bool hasKeyValue()
	 * @method bool isKeyValueFilled()
	 * @method bool isKeyValueChanged()
	 * @method \string remindActualKeyValue()
	 * @method \string requireKeyValue()
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage resetKeyValue()
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage unsetKeyValue()
	 * @method \string fillKeyValue()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage reset($fieldName)
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage wakeUp($data)
	 */
	class EO_ActivityStorage {
		/* @var \Bitrix\Bizproc\Storage\Entity\ActivityStorageTable */
		static public $dataClass = '\Bitrix\Bizproc\Storage\Entity\ActivityStorageTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Storage\Entity {
	/**
	 * EO_ActivityStorage_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getWorkflowTemplateIdList()
	 * @method \int[] fillWorkflowTemplateId()
	 * @method \string[] getActivityNameList()
	 * @method \string[] fillActivityName()
	 * @method \string[] getKeyIdList()
	 * @method \string[] fillKeyId()
	 * @method \string[] getKeyValueList()
	 * @method \string[] fillKeyValue()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage $object)
	 * @method bool has(\Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_ActivityStorage_Collection merge(?EO_ActivityStorage_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_ActivityStorage_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Storage\Entity\ActivityStorageTable */
		static public $dataClass = '\Bitrix\Bizproc\Storage\Entity\ActivityStorageTable';
	}
}
namespace Bitrix\Bizproc\Storage\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_ActivityStorage_Result exec()
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage fetchObject()
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_ActivityStorage_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage fetchObject()
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage_Collection fetchCollection()
	 */
	class EO_ActivityStorage_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage_Collection createCollection()
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Storage\Entity\EO_ActivityStorage_Collection wakeUpCollection($rows)
	 */
	class EO_ActivityStorage_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\SchedulerEventTable:bizproc/lib/schedulerevent.php */
namespace Bitrix\Bizproc {
	/**
	 * EO_SchedulerEvent
	 * @see \Bitrix\Bizproc\SchedulerEventTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getWorkflowId()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent setWorkflowId(\string|\Bitrix\Main\DB\SqlExpression $workflowId)
	 * @method bool hasWorkflowId()
	 * @method bool isWorkflowIdFilled()
	 * @method bool isWorkflowIdChanged()
	 * @method \string remindActualWorkflowId()
	 * @method \string requireWorkflowId()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent resetWorkflowId()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent unsetWorkflowId()
	 * @method \string fillWorkflowId()
	 * @method \string getHandler()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent setHandler(\string|\Bitrix\Main\DB\SqlExpression $handler)
	 * @method bool hasHandler()
	 * @method bool isHandlerFilled()
	 * @method bool isHandlerChanged()
	 * @method \string remindActualHandler()
	 * @method \string requireHandler()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent resetHandler()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent unsetHandler()
	 * @method \string fillHandler()
	 * @method \string getEventModule()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent setEventModule(\string|\Bitrix\Main\DB\SqlExpression $eventModule)
	 * @method bool hasEventModule()
	 * @method bool isEventModuleFilled()
	 * @method bool isEventModuleChanged()
	 * @method \string remindActualEventModule()
	 * @method \string requireEventModule()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent resetEventModule()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent unsetEventModule()
	 * @method \string fillEventModule()
	 * @method \string getEventType()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent setEventType(\string|\Bitrix\Main\DB\SqlExpression $eventType)
	 * @method bool hasEventType()
	 * @method bool isEventTypeFilled()
	 * @method bool isEventTypeChanged()
	 * @method \string remindActualEventType()
	 * @method \string requireEventType()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent resetEventType()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent unsetEventType()
	 * @method \string fillEventType()
	 * @method \string getEntityId()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent setEntityId(\string|\Bitrix\Main\DB\SqlExpression $entityId)
	 * @method bool hasEntityId()
	 * @method bool isEntityIdFilled()
	 * @method bool isEntityIdChanged()
	 * @method \string remindActualEntityId()
	 * @method \string requireEntityId()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent resetEntityId()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent unsetEntityId()
	 * @method \string fillEntityId()
	 * @method \string getEventParameters()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent setEventParameters(\string|\Bitrix\Main\DB\SqlExpression $eventParameters)
	 * @method bool hasEventParameters()
	 * @method bool isEventParametersFilled()
	 * @method bool isEventParametersChanged()
	 * @method \string remindActualEventParameters()
	 * @method \string requireEventParameters()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent resetEventParameters()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent unsetEventParameters()
	 * @method \string fillEventParameters()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent set($fieldName, $value)
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent reset($fieldName)
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\EO_SchedulerEvent wakeUp($data)
	 */
	class EO_SchedulerEvent {
		/* @var \Bitrix\Bizproc\SchedulerEventTable */
		static public $dataClass = '\Bitrix\Bizproc\SchedulerEventTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc {
	/**
	 * EO_SchedulerEvent_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getWorkflowIdList()
	 * @method \string[] fillWorkflowId()
	 * @method \string[] getHandlerList()
	 * @method \string[] fillHandler()
	 * @method \string[] getEventModuleList()
	 * @method \string[] fillEventModule()
	 * @method \string[] getEventTypeList()
	 * @method \string[] fillEventType()
	 * @method \string[] getEntityIdList()
	 * @method \string[] fillEntityId()
	 * @method \string[] getEventParametersList()
	 * @method \string[] fillEventParameters()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\EO_SchedulerEvent $object)
	 * @method bool has(\Bitrix\Bizproc\EO_SchedulerEvent $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent getByPrimary($primary)
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\EO_SchedulerEvent $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\EO_SchedulerEvent_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_SchedulerEvent_Collection merge(?EO_SchedulerEvent_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_SchedulerEvent_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\SchedulerEventTable */
		static public $dataClass = '\Bitrix\Bizproc\SchedulerEventTable';
	}
}
namespace Bitrix\Bizproc {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_SchedulerEvent_Result exec()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent fetchObject()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_SchedulerEvent_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent fetchObject()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent_Collection fetchCollection()
	 */
	class EO_SchedulerEvent_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent_Collection createCollection()
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent wakeUpObject($row)
	 * @method \Bitrix\Bizproc\EO_SchedulerEvent_Collection wakeUpCollection($rows)
	 */
	class EO_SchedulerEvent_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\RestProviderTable:bizproc/lib/restprovider.php */
namespace Bitrix\Bizproc {
	/**
	 * EO_RestProvider
	 * @see \Bitrix\Bizproc\RestProviderTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Bizproc\EO_RestProvider setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getAppId()
	 * @method \Bitrix\Bizproc\EO_RestProvider setAppId(\string|\Bitrix\Main\DB\SqlExpression $appId)
	 * @method bool hasAppId()
	 * @method bool isAppIdFilled()
	 * @method bool isAppIdChanged()
	 * @method \string remindActualAppId()
	 * @method \string requireAppId()
	 * @method \Bitrix\Bizproc\EO_RestProvider resetAppId()
	 * @method \Bitrix\Bizproc\EO_RestProvider unsetAppId()
	 * @method \string fillAppId()
	 * @method \string getAppName()
	 * @method \Bitrix\Bizproc\EO_RestProvider setAppName(\string|\Bitrix\Main\DB\SqlExpression $appName)
	 * @method bool hasAppName()
	 * @method bool isAppNameFilled()
	 * @method bool isAppNameChanged()
	 * @method \string remindActualAppName()
	 * @method \string requireAppName()
	 * @method \Bitrix\Bizproc\EO_RestProvider resetAppName()
	 * @method \Bitrix\Bizproc\EO_RestProvider unsetAppName()
	 * @method \string fillAppName()
	 * @method \string getCode()
	 * @method \Bitrix\Bizproc\EO_RestProvider setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Bizproc\EO_RestProvider resetCode()
	 * @method \Bitrix\Bizproc\EO_RestProvider unsetCode()
	 * @method \string fillCode()
	 * @method \string getType()
	 * @method \Bitrix\Bizproc\EO_RestProvider setType(\string|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \string remindActualType()
	 * @method \string requireType()
	 * @method \Bitrix\Bizproc\EO_RestProvider resetType()
	 * @method \Bitrix\Bizproc\EO_RestProvider unsetType()
	 * @method \string fillType()
	 * @method \string getHandler()
	 * @method \Bitrix\Bizproc\EO_RestProvider setHandler(\string|\Bitrix\Main\DB\SqlExpression $handler)
	 * @method bool hasHandler()
	 * @method bool isHandlerFilled()
	 * @method bool isHandlerChanged()
	 * @method \string remindActualHandler()
	 * @method \string requireHandler()
	 * @method \Bitrix\Bizproc\EO_RestProvider resetHandler()
	 * @method \Bitrix\Bizproc\EO_RestProvider unsetHandler()
	 * @method \string fillHandler()
	 * @method \string getName()
	 * @method \Bitrix\Bizproc\EO_RestProvider setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Bizproc\EO_RestProvider resetName()
	 * @method \Bitrix\Bizproc\EO_RestProvider unsetName()
	 * @method \string fillName()
	 * @method \string getDescription()
	 * @method \Bitrix\Bizproc\EO_RestProvider setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Bizproc\EO_RestProvider resetDescription()
	 * @method \Bitrix\Bizproc\EO_RestProvider unsetDescription()
	 * @method \string fillDescription()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\EO_RestProvider set($fieldName, $value)
	 * @method \Bitrix\Bizproc\EO_RestProvider reset($fieldName)
	 * @method \Bitrix\Bizproc\EO_RestProvider unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\EO_RestProvider wakeUp($data)
	 */
	class EO_RestProvider {
		/* @var \Bitrix\Bizproc\RestProviderTable */
		static public $dataClass = '\Bitrix\Bizproc\RestProviderTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc {
	/**
	 * EO_RestProvider_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getAppIdList()
	 * @method \string[] fillAppId()
	 * @method \string[] getAppNameList()
	 * @method \string[] fillAppName()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getTypeList()
	 * @method \string[] fillType()
	 * @method \string[] getHandlerList()
	 * @method \string[] fillHandler()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\EO_RestProvider $object)
	 * @method bool has(\Bitrix\Bizproc\EO_RestProvider $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\EO_RestProvider getByPrimary($primary)
	 * @method \Bitrix\Bizproc\EO_RestProvider[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\EO_RestProvider $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\EO_RestProvider_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\EO_RestProvider current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_RestProvider_Collection merge(?EO_RestProvider_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_RestProvider_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\RestProviderTable */
		static public $dataClass = '\Bitrix\Bizproc\RestProviderTable';
	}
}
namespace Bitrix\Bizproc {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_RestProvider_Result exec()
	 * @method \Bitrix\Bizproc\EO_RestProvider fetchObject()
	 * @method \Bitrix\Bizproc\EO_RestProvider_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_RestProvider_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\EO_RestProvider fetchObject()
	 * @method \Bitrix\Bizproc\EO_RestProvider_Collection fetchCollection()
	 */
	class EO_RestProvider_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\EO_RestProvider createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\EO_RestProvider_Collection createCollection()
	 * @method \Bitrix\Bizproc\EO_RestProvider wakeUpObject($row)
	 * @method \Bitrix\Bizproc\EO_RestProvider_Collection wakeUpCollection($rows)
	 */
	class EO_RestProvider_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Workflow\Entity\WorkflowUserTable:bizproc/lib/workflow/entity/workflowusertable.php */
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * EO_WorkflowUser
	 * @see \Bitrix\Bizproc\Workflow\Entity\WorkflowUserTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getUserId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \string getWorkflowId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser setWorkflowId(\string|\Bitrix\Main\DB\SqlExpression $workflowId)
	 * @method bool hasWorkflowId()
	 * @method bool isWorkflowIdFilled()
	 * @method bool isWorkflowIdChanged()
	 * @method \int getIsAuthor()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser setIsAuthor(\int|\Bitrix\Main\DB\SqlExpression $isAuthor)
	 * @method bool hasIsAuthor()
	 * @method bool isIsAuthorFilled()
	 * @method bool isIsAuthorChanged()
	 * @method \int remindActualIsAuthor()
	 * @method \int requireIsAuthor()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser resetIsAuthor()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser unsetIsAuthor()
	 * @method \int fillIsAuthor()
	 * @method \int getWorkflowStatus()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser setWorkflowStatus(\int|\Bitrix\Main\DB\SqlExpression $workflowStatus)
	 * @method bool hasWorkflowStatus()
	 * @method bool isWorkflowStatusFilled()
	 * @method bool isWorkflowStatusChanged()
	 * @method \int remindActualWorkflowStatus()
	 * @method \int requireWorkflowStatus()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser resetWorkflowStatus()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser unsetWorkflowStatus()
	 * @method \int fillWorkflowStatus()
	 * @method \int getTaskStatus()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser setTaskStatus(\int|\Bitrix\Main\DB\SqlExpression $taskStatus)
	 * @method bool hasTaskStatus()
	 * @method bool isTaskStatusFilled()
	 * @method bool isTaskStatusChanged()
	 * @method \int remindActualTaskStatus()
	 * @method \int requireTaskStatus()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser resetTaskStatus()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser unsetTaskStatus()
	 * @method \int fillTaskStatus()
	 * @method \Bitrix\Main\Type\DateTime getModified()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser setModified(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $modified)
	 * @method bool hasModified()
	 * @method bool isModifiedFilled()
	 * @method bool isModifiedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualModified()
	 * @method \Bitrix\Main\Type\DateTime requireModified()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser resetModified()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser unsetModified()
	 * @method \Bitrix\Main\Type\DateTime fillModified()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter getFilter()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter remindActualFilter()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter requireFilter()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser setFilter(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter $object)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser resetFilter()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser unsetFilter()
	 * @method bool hasFilter()
	 * @method bool isFilterFilled()
	 * @method bool isFilterChanged()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter fillFilter()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser reset($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser wakeUp($data)
	 */
	class EO_WorkflowUser {
		/* @var \Bitrix\Bizproc\Workflow\Entity\WorkflowUserTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Entity\WorkflowUserTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * EO_WorkflowUser_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getUserIdList()
	 * @method \string[] getWorkflowIdList()
	 * @method \int[] getIsAuthorList()
	 * @method \int[] fillIsAuthor()
	 * @method \int[] getWorkflowStatusList()
	 * @method \int[] fillWorkflowStatus()
	 * @method \int[] getTaskStatusList()
	 * @method \int[] fillTaskStatus()
	 * @method \Bitrix\Main\Type\DateTime[] getModifiedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillModified()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter[] getFilterList()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser_Collection getFilterCollection()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter_Collection fillFilter()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser $object)
	 * @method bool has(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_WorkflowUser_Collection merge(?EO_WorkflowUser_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_WorkflowUser_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Workflow\Entity\WorkflowUserTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Entity\WorkflowUserTable';
	}
}
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_WorkflowUser_Result exec()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_WorkflowUser_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser_Collection fetchCollection()
	 */
	class EO_WorkflowUser_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser_Collection createCollection()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUser_Collection wakeUpCollection($rows)
	 */
	class EO_WorkflowUser_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Workflow\Entity\WorkflowUserCommentTable:bizproc/lib/workflow/entity/workflowusercommenttable.php */
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * EO_WorkflowUserComment
	 * @see \Bitrix\Bizproc\Workflow\Entity\WorkflowUserCommentTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getUserId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \string getWorkflowId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment setWorkflowId(\string|\Bitrix\Main\DB\SqlExpression $workflowId)
	 * @method bool hasWorkflowId()
	 * @method bool isWorkflowIdFilled()
	 * @method bool isWorkflowIdChanged()
	 * @method \int getUnreadCnt()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment setUnreadCnt(\int|\Bitrix\Main\DB\SqlExpression $unreadCnt)
	 * @method bool hasUnreadCnt()
	 * @method bool isUnreadCntFilled()
	 * @method bool isUnreadCntChanged()
	 * @method \int remindActualUnreadCnt()
	 * @method \int requireUnreadCnt()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment resetUnreadCnt()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment unsetUnreadCnt()
	 * @method \int fillUnreadCnt()
	 * @method \Bitrix\Main\Type\DateTime getModified()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment setModified(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $modified)
	 * @method bool hasModified()
	 * @method bool isModifiedFilled()
	 * @method bool isModifiedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualModified()
	 * @method \Bitrix\Main\Type\DateTime requireModified()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment resetModified()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment unsetModified()
	 * @method \Bitrix\Main\Type\DateTime fillModified()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment reset($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment wakeUp($data)
	 */
	class EO_WorkflowUserComment {
		/* @var \Bitrix\Bizproc\Workflow\Entity\WorkflowUserCommentTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Entity\WorkflowUserCommentTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * EO_WorkflowUserComment_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getUserIdList()
	 * @method \string[] getWorkflowIdList()
	 * @method \int[] getUnreadCntList()
	 * @method \int[] fillUnreadCnt()
	 * @method \Bitrix\Main\Type\DateTime[] getModifiedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillModified()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment $object)
	 * @method bool has(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_WorkflowUserComment_Collection merge(?EO_WorkflowUserComment_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_WorkflowUserComment_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Workflow\Entity\WorkflowUserCommentTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Entity\WorkflowUserCommentTable';
	}
}
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_WorkflowUserComment_Result exec()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_WorkflowUserComment_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment_Collection fetchCollection()
	 */
	class EO_WorkflowUserComment_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment_Collection createCollection()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowUserComment_Collection wakeUpCollection($rows)
	 */
	class EO_WorkflowUserComment_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Workflow\Entity\WorkflowFilterTable:bizproc/lib/workflow/entity/workflowfiltertable.php */
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * EO_WorkflowFilter
	 * @see \Bitrix\Bizproc\Workflow\Entity\WorkflowFilterTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getWorkflowId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter setWorkflowId(\string|\Bitrix\Main\DB\SqlExpression $workflowId)
	 * @method bool hasWorkflowId()
	 * @method bool isWorkflowIdFilled()
	 * @method bool isWorkflowIdChanged()
	 * @method \string getModuleId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter resetModuleId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter unsetModuleId()
	 * @method \string fillModuleId()
	 * @method \string getEntity()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter setEntity(\string|\Bitrix\Main\DB\SqlExpression $entity)
	 * @method bool hasEntity()
	 * @method bool isEntityFilled()
	 * @method bool isEntityChanged()
	 * @method \string remindActualEntity()
	 * @method \string requireEntity()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter resetEntity()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter unsetEntity()
	 * @method \string fillEntity()
	 * @method \string getDocumentId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter setDocumentId(\string|\Bitrix\Main\DB\SqlExpression $documentId)
	 * @method bool hasDocumentId()
	 * @method bool isDocumentIdFilled()
	 * @method bool isDocumentIdChanged()
	 * @method \string remindActualDocumentId()
	 * @method \string requireDocumentId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter resetDocumentId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter unsetDocumentId()
	 * @method \string fillDocumentId()
	 * @method \int getTemplateId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter setTemplateId(\int|\Bitrix\Main\DB\SqlExpression $templateId)
	 * @method bool hasTemplateId()
	 * @method bool isTemplateIdFilled()
	 * @method bool isTemplateIdChanged()
	 * @method \int remindActualTemplateId()
	 * @method \int requireTemplateId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter resetTemplateId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter unsetTemplateId()
	 * @method \int fillTemplateId()
	 * @method \Bitrix\Main\Type\DateTime getStarted()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter setStarted(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $started)
	 * @method bool hasStarted()
	 * @method bool isStartedFilled()
	 * @method bool isStartedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualStarted()
	 * @method \Bitrix\Main\Type\DateTime requireStarted()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter resetStarted()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter unsetStarted()
	 * @method \Bitrix\Main\Type\DateTime fillStarted()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter reset($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter wakeUp($data)
	 */
	class EO_WorkflowFilter {
		/* @var \Bitrix\Bizproc\Workflow\Entity\WorkflowFilterTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Entity\WorkflowFilterTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * EO_WorkflowFilter_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getWorkflowIdList()
	 * @method \string[] getModuleIdList()
	 * @method \string[] fillModuleId()
	 * @method \string[] getEntityList()
	 * @method \string[] fillEntity()
	 * @method \string[] getDocumentIdList()
	 * @method \string[] fillDocumentId()
	 * @method \int[] getTemplateIdList()
	 * @method \int[] fillTemplateId()
	 * @method \Bitrix\Main\Type\DateTime[] getStartedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillStarted()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter $object)
	 * @method bool has(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_WorkflowFilter_Collection merge(?EO_WorkflowFilter_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_WorkflowFilter_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Workflow\Entity\WorkflowFilterTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Entity\WorkflowFilterTable';
	}
}
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_WorkflowFilter_Result exec()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_WorkflowFilter_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter_Collection fetchCollection()
	 */
	class EO_WorkflowFilter_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter_Collection createCollection()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowFilter_Collection wakeUpCollection($rows)
	 */
	class EO_WorkflowFilter_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Workflow\Entity\WorkflowMetadataTable:bizproc/lib/workflow/entity/workflowmetadatatable.php */
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * EO_WorkflowMetadata
	 * @see \Bitrix\Bizproc\Workflow\Entity\WorkflowMetadataTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getWorkflowId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata setWorkflowId(\string|\Bitrix\Main\DB\SqlExpression $workflowId)
	 * @method bool hasWorkflowId()
	 * @method bool isWorkflowIdFilled()
	 * @method bool isWorkflowIdChanged()
	 * @method \string remindActualWorkflowId()
	 * @method \string requireWorkflowId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata resetWorkflowId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata unsetWorkflowId()
	 * @method \string fillWorkflowId()
	 * @method \int getStartDuration()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata setStartDuration(\int|\Bitrix\Main\DB\SqlExpression $startDuration)
	 * @method bool hasStartDuration()
	 * @method bool isStartDurationFilled()
	 * @method bool isStartDurationChanged()
	 * @method \int remindActualStartDuration()
	 * @method \int requireStartDuration()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata resetStartDuration()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata unsetStartDuration()
	 * @method \int fillStartDuration()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata reset($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata wakeUp($data)
	 */
	class EO_WorkflowMetadata {
		/* @var \Bitrix\Bizproc\Workflow\Entity\WorkflowMetadataTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Entity\WorkflowMetadataTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * EO_WorkflowMetadata_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getWorkflowIdList()
	 * @method \string[] fillWorkflowId()
	 * @method \int[] getStartDurationList()
	 * @method \int[] fillStartDuration()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata $object)
	 * @method bool has(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_WorkflowMetadata_Collection merge(?EO_WorkflowMetadata_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_WorkflowMetadata_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Workflow\Entity\WorkflowMetadataTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Entity\WorkflowMetadataTable';
	}
}
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_WorkflowMetadata_Result exec()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_WorkflowMetadata_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata_Collection fetchCollection()
	 */
	class EO_WorkflowMetadata_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata_Collection createCollection()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowMetadata_Collection wakeUpCollection($rows)
	 */
	class EO_WorkflowMetadata_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Workflow\Entity\WorkflowDurationStatTable:bizproc/lib/workflow/entity/workflowdurationstattable.php */
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * EO_WorkflowDurationStat
	 * @see \Bitrix\Bizproc\Workflow\Entity\WorkflowDurationStatTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getWorkflowId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat setWorkflowId(\string|\Bitrix\Main\DB\SqlExpression $workflowId)
	 * @method bool hasWorkflowId()
	 * @method bool isWorkflowIdFilled()
	 * @method bool isWorkflowIdChanged()
	 * @method \string remindActualWorkflowId()
	 * @method \string requireWorkflowId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat resetWorkflowId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat unsetWorkflowId()
	 * @method \string fillWorkflowId()
	 * @method \int getTemplateId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat setTemplateId(\int|\Bitrix\Main\DB\SqlExpression $templateId)
	 * @method bool hasTemplateId()
	 * @method bool isTemplateIdFilled()
	 * @method bool isTemplateIdChanged()
	 * @method \int remindActualTemplateId()
	 * @method \int requireTemplateId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat resetTemplateId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat unsetTemplateId()
	 * @method \int fillTemplateId()
	 * @method \int getDuration()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat setDuration(\int|\Bitrix\Main\DB\SqlExpression $duration)
	 * @method bool hasDuration()
	 * @method bool isDurationFilled()
	 * @method bool isDurationChanged()
	 * @method \int remindActualDuration()
	 * @method \int requireDuration()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat resetDuration()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat unsetDuration()
	 * @method \int fillDuration()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat reset($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat wakeUp($data)
	 */
	class EO_WorkflowDurationStat {
		/* @var \Bitrix\Bizproc\Workflow\Entity\WorkflowDurationStatTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Entity\WorkflowDurationStatTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * EO_WorkflowDurationStat_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getWorkflowIdList()
	 * @method \string[] fillWorkflowId()
	 * @method \int[] getTemplateIdList()
	 * @method \int[] fillTemplateId()
	 * @method \int[] getDurationList()
	 * @method \int[] fillDuration()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat $object)
	 * @method bool has(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_WorkflowDurationStat_Collection merge(?EO_WorkflowDurationStat_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_WorkflowDurationStat_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Workflow\Entity\WorkflowDurationStatTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Entity\WorkflowDurationStatTable';
	}
}
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_WorkflowDurationStat_Result exec()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_WorkflowDurationStat_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat_Collection fetchCollection()
	 */
	class EO_WorkflowDurationStat_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat_Collection createCollection()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowDurationStat_Collection wakeUpCollection($rows)
	 */
	class EO_WorkflowDurationStat_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Workflow\Task\TaskTable:bizproc/lib/workflow/task/tasktable.php */
namespace Bitrix\Bizproc\Workflow\Task {
	/**
	 * Task
	 * @see \Bitrix\Bizproc\Workflow\Task\TaskTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Bizproc\Workflow\Task setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getWorkflowId()
	 * @method \Bitrix\Bizproc\Workflow\Task setWorkflowId(\string|\Bitrix\Main\DB\SqlExpression $workflowId)
	 * @method bool hasWorkflowId()
	 * @method bool isWorkflowIdFilled()
	 * @method bool isWorkflowIdChanged()
	 * @method \string remindActualWorkflowId()
	 * @method \string requireWorkflowId()
	 * @method \Bitrix\Bizproc\Workflow\Task resetWorkflowId()
	 * @method \Bitrix\Bizproc\Workflow\Task unsetWorkflowId()
	 * @method \string fillWorkflowId()
	 * @method \string getActivity()
	 * @method \Bitrix\Bizproc\Workflow\Task setActivity(\string|\Bitrix\Main\DB\SqlExpression $activity)
	 * @method bool hasActivity()
	 * @method bool isActivityFilled()
	 * @method bool isActivityChanged()
	 * @method \string remindActualActivity()
	 * @method \string requireActivity()
	 * @method \Bitrix\Bizproc\Workflow\Task resetActivity()
	 * @method \Bitrix\Bizproc\Workflow\Task unsetActivity()
	 * @method \string fillActivity()
	 * @method \string getActivityName()
	 * @method \Bitrix\Bizproc\Workflow\Task setActivityName(\string|\Bitrix\Main\DB\SqlExpression $activityName)
	 * @method bool hasActivityName()
	 * @method bool isActivityNameFilled()
	 * @method bool isActivityNameChanged()
	 * @method \string remindActualActivityName()
	 * @method \string requireActivityName()
	 * @method \Bitrix\Bizproc\Workflow\Task resetActivityName()
	 * @method \Bitrix\Bizproc\Workflow\Task unsetActivityName()
	 * @method \string fillActivityName()
	 * @method null|\Bitrix\Main\Type\DateTime getCreatedDate()
	 * @method \Bitrix\Bizproc\Workflow\Task setCreatedDate(null|\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $createdDate)
	 * @method bool hasCreatedDate()
	 * @method bool isCreatedDateFilled()
	 * @method bool isCreatedDateChanged()
	 * @method null|\Bitrix\Main\Type\DateTime remindActualCreatedDate()
	 * @method null|\Bitrix\Main\Type\DateTime requireCreatedDate()
	 * @method \Bitrix\Bizproc\Workflow\Task resetCreatedDate()
	 * @method \Bitrix\Bizproc\Workflow\Task unsetCreatedDate()
	 * @method null|\Bitrix\Main\Type\DateTime fillCreatedDate()
	 * @method \Bitrix\Main\Type\DateTime getModified()
	 * @method \Bitrix\Bizproc\Workflow\Task setModified(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $modified)
	 * @method bool hasModified()
	 * @method bool isModifiedFilled()
	 * @method bool isModifiedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualModified()
	 * @method \Bitrix\Main\Type\DateTime requireModified()
	 * @method \Bitrix\Bizproc\Workflow\Task resetModified()
	 * @method \Bitrix\Bizproc\Workflow\Task unsetModified()
	 * @method \Bitrix\Main\Type\DateTime fillModified()
	 * @method null|\Bitrix\Main\Type\DateTime getOverdueDate()
	 * @method \Bitrix\Bizproc\Workflow\Task setOverdueDate(null|\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $overdueDate)
	 * @method bool hasOverdueDate()
	 * @method bool isOverdueDateFilled()
	 * @method bool isOverdueDateChanged()
	 * @method null|\Bitrix\Main\Type\DateTime remindActualOverdueDate()
	 * @method null|\Bitrix\Main\Type\DateTime requireOverdueDate()
	 * @method \Bitrix\Bizproc\Workflow\Task resetOverdueDate()
	 * @method \Bitrix\Bizproc\Workflow\Task unsetOverdueDate()
	 * @method null|\Bitrix\Main\Type\DateTime fillOverdueDate()
	 * @method \string getName()
	 * @method \Bitrix\Bizproc\Workflow\Task setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Bizproc\Workflow\Task resetName()
	 * @method \Bitrix\Bizproc\Workflow\Task unsetName()
	 * @method \string fillName()
	 * @method null|\string getDescription()
	 * @method \Bitrix\Bizproc\Workflow\Task setDescription(null|\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method null|\string remindActualDescription()
	 * @method null|\string requireDescription()
	 * @method \Bitrix\Bizproc\Workflow\Task resetDescription()
	 * @method \Bitrix\Bizproc\Workflow\Task unsetDescription()
	 * @method null|\string fillDescription()
	 * @method array getParameters()
	 * @method \Bitrix\Bizproc\Workflow\Task setParameters(array|\Bitrix\Main\DB\SqlExpression $parameters)
	 * @method bool hasParameters()
	 * @method bool isParametersFilled()
	 * @method bool isParametersChanged()
	 * @method array remindActualParameters()
	 * @method array requireParameters()
	 * @method \Bitrix\Bizproc\Workflow\Task resetParameters()
	 * @method \Bitrix\Bizproc\Workflow\Task unsetParameters()
	 * @method array fillParameters()
	 * @method \int getStatus()
	 * @method \Bitrix\Bizproc\Workflow\Task setStatus(\int|\Bitrix\Main\DB\SqlExpression $status)
	 * @method bool hasStatus()
	 * @method bool isStatusFilled()
	 * @method bool isStatusChanged()
	 * @method \int remindActualStatus()
	 * @method \int requireStatus()
	 * @method \Bitrix\Bizproc\Workflow\Task resetStatus()
	 * @method \Bitrix\Bizproc\Workflow\Task unsetStatus()
	 * @method \int fillStatus()
	 * @method \string getIsInline()
	 * @method \Bitrix\Bizproc\Workflow\Task setIsInline(\string|\Bitrix\Main\DB\SqlExpression $isInline)
	 * @method bool hasIsInline()
	 * @method bool isIsInlineFilled()
	 * @method bool isIsInlineChanged()
	 * @method \string remindActualIsInline()
	 * @method \string requireIsInline()
	 * @method \Bitrix\Bizproc\Workflow\Task resetIsInline()
	 * @method \Bitrix\Bizproc\Workflow\Task unsetIsInline()
	 * @method \string fillIsInline()
	 * @method \int getDelegationType()
	 * @method \Bitrix\Bizproc\Workflow\Task setDelegationType(\int|\Bitrix\Main\DB\SqlExpression $delegationType)
	 * @method bool hasDelegationType()
	 * @method bool isDelegationTypeFilled()
	 * @method bool isDelegationTypeChanged()
	 * @method \int remindActualDelegationType()
	 * @method \int requireDelegationType()
	 * @method \Bitrix\Bizproc\Workflow\Task resetDelegationType()
	 * @method \Bitrix\Bizproc\Workflow\Task unsetDelegationType()
	 * @method \int fillDelegationType()
	 * @method \string getDocumentName()
	 * @method \Bitrix\Bizproc\Workflow\Task setDocumentName(\string|\Bitrix\Main\DB\SqlExpression $documentName)
	 * @method bool hasDocumentName()
	 * @method bool isDocumentNameFilled()
	 * @method bool isDocumentNameChanged()
	 * @method \string remindActualDocumentName()
	 * @method \string requireDocumentName()
	 * @method \Bitrix\Bizproc\Workflow\Task resetDocumentName()
	 * @method \Bitrix\Bizproc\Workflow\Task unsetDocumentName()
	 * @method \string fillDocumentName()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState getWorkflowState()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState remindActualWorkflowState()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState requireWorkflowState()
	 * @method \Bitrix\Bizproc\Workflow\Task setWorkflowState(\Bitrix\Bizproc\Workflow\WorkflowState $object)
	 * @method \Bitrix\Bizproc\Workflow\Task resetWorkflowState()
	 * @method \Bitrix\Bizproc\Workflow\Task unsetWorkflowState()
	 * @method bool hasWorkflowState()
	 * @method bool isWorkflowStateFilled()
	 * @method bool isWorkflowStateChanged()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState fillWorkflowState()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser_Collection getTaskUsers()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser_Collection requireTaskUsers()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser_Collection fillTaskUsers()
	 * @method bool hasTaskUsers()
	 * @method bool isTaskUsersFilled()
	 * @method bool isTaskUsersChanged()
	 * @method void addToTaskUsers(\Bitrix\Bizproc\Workflow\Task\EO_TaskUser $taskUser)
	 * @method void removeFromTaskUsers(\Bitrix\Bizproc\Workflow\Task\EO_TaskUser $taskUser)
	 * @method void removeAllTaskUsers()
	 * @method \Bitrix\Bizproc\Workflow\Task resetTaskUsers()
	 * @method \Bitrix\Bizproc\Workflow\Task unsetTaskUsers()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Task set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Workflow\Task reset($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Task unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Workflow\Task wakeUp($data)
	 */
	class EO_Task {
		/* @var \Bitrix\Bizproc\Workflow\Task\TaskTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Task\TaskTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Workflow\Task {
	/**
	 * EO_Task_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getWorkflowIdList()
	 * @method \string[] fillWorkflowId()
	 * @method \string[] getActivityList()
	 * @method \string[] fillActivity()
	 * @method \string[] getActivityNameList()
	 * @method \string[] fillActivityName()
	 * @method null|\Bitrix\Main\Type\DateTime[] getCreatedDateList()
	 * @method null|\Bitrix\Main\Type\DateTime[] fillCreatedDate()
	 * @method \Bitrix\Main\Type\DateTime[] getModifiedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillModified()
	 * @method null|\Bitrix\Main\Type\DateTime[] getOverdueDateList()
	 * @method null|\Bitrix\Main\Type\DateTime[] fillOverdueDate()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method null|\string[] getDescriptionList()
	 * @method null|\string[] fillDescription()
	 * @method array[] getParametersList()
	 * @method array[] fillParameters()
	 * @method \int[] getStatusList()
	 * @method \int[] fillStatus()
	 * @method \string[] getIsInlineList()
	 * @method \string[] fillIsInline()
	 * @method \int[] getDelegationTypeList()
	 * @method \int[] fillDelegationType()
	 * @method \string[] getDocumentNameList()
	 * @method \string[] fillDocumentName()
	 * @method \Bitrix\Bizproc\Workflow\WorkflowState[] getWorkflowStateList()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_Task_Collection getWorkflowStateCollection()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection fillWorkflowState()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser_Collection[] getTaskUsersList()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser_Collection getTaskUsersCollection()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser_Collection fillTaskUsers()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Workflow\Task $object)
	 * @method bool has(\Bitrix\Bizproc\Workflow\Task $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Task getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Task[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Workflow\Task $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Workflow\Task\EO_Task_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Workflow\Task current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_Task_Collection merge(?EO_Task_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Task_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Workflow\Task\TaskTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Task\TaskTable';
	}
}
namespace Bitrix\Bizproc\Workflow\Task {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Task_Result exec()
	 * @method \Bitrix\Bizproc\Workflow\Task fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_Task_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Task_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Task fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_Task_Collection fetchCollection()
	 */
	class EO_Task_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Task createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_Task_Collection createCollection()
	 * @method \Bitrix\Bizproc\Workflow\Task wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_Task_Collection wakeUpCollection($rows)
	 */
	class EO_Task_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Workflow\Type\Entity\GlobalConstTable:bizproc/lib/workflow/type/entity/globalconst.php */
namespace Bitrix\Bizproc\Workflow\Type\Entity {
	/**
	 * EO_GlobalConst
	 * @see \Bitrix\Bizproc\Workflow\Type\Entity\GlobalConstTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getId()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst setId(\string|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst resetName()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst unsetName()
	 * @method \string fillName()
	 * @method \string getDescription()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst resetDescription()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getPropertyType()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst setPropertyType(\string|\Bitrix\Main\DB\SqlExpression $propertyType)
	 * @method bool hasPropertyType()
	 * @method bool isPropertyTypeFilled()
	 * @method bool isPropertyTypeChanged()
	 * @method \string remindActualPropertyType()
	 * @method \string requirePropertyType()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst resetPropertyType()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst unsetPropertyType()
	 * @method \string fillPropertyType()
	 * @method \boolean getIsRequired()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst setIsRequired(\boolean|\Bitrix\Main\DB\SqlExpression $isRequired)
	 * @method bool hasIsRequired()
	 * @method bool isIsRequiredFilled()
	 * @method bool isIsRequiredChanged()
	 * @method \boolean remindActualIsRequired()
	 * @method \boolean requireIsRequired()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst resetIsRequired()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst unsetIsRequired()
	 * @method \boolean fillIsRequired()
	 * @method \boolean getIsMultiple()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst setIsMultiple(\boolean|\Bitrix\Main\DB\SqlExpression $isMultiple)
	 * @method bool hasIsMultiple()
	 * @method bool isIsMultipleFilled()
	 * @method bool isIsMultipleChanged()
	 * @method \boolean remindActualIsMultiple()
	 * @method \boolean requireIsMultiple()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst resetIsMultiple()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst unsetIsMultiple()
	 * @method \boolean fillIsMultiple()
	 * @method \string getPropertyOptions()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst setPropertyOptions(\string|\Bitrix\Main\DB\SqlExpression $propertyOptions)
	 * @method bool hasPropertyOptions()
	 * @method bool isPropertyOptionsFilled()
	 * @method bool isPropertyOptionsChanged()
	 * @method \string remindActualPropertyOptions()
	 * @method \string requirePropertyOptions()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst resetPropertyOptions()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst unsetPropertyOptions()
	 * @method \string fillPropertyOptions()
	 * @method \string getPropertySettings()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst setPropertySettings(\string|\Bitrix\Main\DB\SqlExpression $propertySettings)
	 * @method bool hasPropertySettings()
	 * @method bool isPropertySettingsFilled()
	 * @method bool isPropertySettingsChanged()
	 * @method \string remindActualPropertySettings()
	 * @method \string requirePropertySettings()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst resetPropertySettings()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst unsetPropertySettings()
	 * @method \string fillPropertySettings()
	 * @method \string getPropertyValue()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst setPropertyValue(\string|\Bitrix\Main\DB\SqlExpression $propertyValue)
	 * @method bool hasPropertyValue()
	 * @method bool isPropertyValueFilled()
	 * @method bool isPropertyValueChanged()
	 * @method \string remindActualPropertyValue()
	 * @method \string requirePropertyValue()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst resetPropertyValue()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst unsetPropertyValue()
	 * @method \string fillPropertyValue()
	 * @method \Bitrix\Main\Type\DateTime getCreatedDate()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst setCreatedDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $createdDate)
	 * @method bool hasCreatedDate()
	 * @method bool isCreatedDateFilled()
	 * @method bool isCreatedDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualCreatedDate()
	 * @method \Bitrix\Main\Type\DateTime requireCreatedDate()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst resetCreatedDate()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst unsetCreatedDate()
	 * @method \Bitrix\Main\Type\DateTime fillCreatedDate()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst resetCreatedBy()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \string getVisibility()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst setVisibility(\string|\Bitrix\Main\DB\SqlExpression $visibility)
	 * @method bool hasVisibility()
	 * @method bool isVisibilityFilled()
	 * @method bool isVisibilityChanged()
	 * @method \string remindActualVisibility()
	 * @method \string requireVisibility()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst resetVisibility()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst unsetVisibility()
	 * @method \string fillVisibility()
	 * @method \Bitrix\Main\Type\DateTime getModifiedDate()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst setModifiedDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $modifiedDate)
	 * @method bool hasModifiedDate()
	 * @method bool isModifiedDateFilled()
	 * @method bool isModifiedDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualModifiedDate()
	 * @method \Bitrix\Main\Type\DateTime requireModifiedDate()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst resetModifiedDate()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst unsetModifiedDate()
	 * @method \Bitrix\Main\Type\DateTime fillModifiedDate()
	 * @method \int getModifiedBy()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst setModifiedBy(\int|\Bitrix\Main\DB\SqlExpression $modifiedBy)
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \int remindActualModifiedBy()
	 * @method \int requireModifiedBy()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst resetModifiedBy()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst unsetModifiedBy()
	 * @method \int fillModifiedBy()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst reset($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst wakeUp($data)
	 */
	class EO_GlobalConst {
		/* @var \Bitrix\Bizproc\Workflow\Type\Entity\GlobalConstTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Type\Entity\GlobalConstTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Workflow\Type\Entity {
	/**
	 * EO_GlobalConst_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getPropertyTypeList()
	 * @method \string[] fillPropertyType()
	 * @method \boolean[] getIsRequiredList()
	 * @method \boolean[] fillIsRequired()
	 * @method \boolean[] getIsMultipleList()
	 * @method \boolean[] fillIsMultiple()
	 * @method \string[] getPropertyOptionsList()
	 * @method \string[] fillPropertyOptions()
	 * @method \string[] getPropertySettingsList()
	 * @method \string[] fillPropertySettings()
	 * @method \string[] getPropertyValueList()
	 * @method \string[] fillPropertyValue()
	 * @method \Bitrix\Main\Type\DateTime[] getCreatedDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillCreatedDate()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \string[] getVisibilityList()
	 * @method \string[] fillVisibility()
	 * @method \Bitrix\Main\Type\DateTime[] getModifiedDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillModifiedDate()
	 * @method \int[] getModifiedByList()
	 * @method \int[] fillModifiedBy()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst $object)
	 * @method bool has(\Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_GlobalConst_Collection merge(?EO_GlobalConst_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_GlobalConst_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Workflow\Type\Entity\GlobalConstTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Type\Entity\GlobalConstTable';
	}
}
namespace Bitrix\Bizproc\Workflow\Type\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_GlobalConst_Result exec()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_GlobalConst_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst_Collection fetchCollection()
	 */
	class EO_GlobalConst_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst_Collection createCollection()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst_Collection wakeUpCollection($rows)
	 */
	class EO_GlobalConst_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Workflow\Type\Entity\GlobalVarTable:bizproc/lib/workflow/type/entity/globalvartable.php */
namespace Bitrix\Bizproc\Workflow\Type\Entity {
	/**
	 * EO_GlobalVar
	 * @see \Bitrix\Bizproc\Workflow\Type\Entity\GlobalVarTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getId()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar setId(\string|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar resetName()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar unsetName()
	 * @method \string fillName()
	 * @method \string getDescription()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar resetDescription()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getPropertyType()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar setPropertyType(\string|\Bitrix\Main\DB\SqlExpression $propertyType)
	 * @method bool hasPropertyType()
	 * @method bool isPropertyTypeFilled()
	 * @method bool isPropertyTypeChanged()
	 * @method \string remindActualPropertyType()
	 * @method \string requirePropertyType()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar resetPropertyType()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar unsetPropertyType()
	 * @method \string fillPropertyType()
	 * @method \boolean getIsRequired()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar setIsRequired(\boolean|\Bitrix\Main\DB\SqlExpression $isRequired)
	 * @method bool hasIsRequired()
	 * @method bool isIsRequiredFilled()
	 * @method bool isIsRequiredChanged()
	 * @method \boolean remindActualIsRequired()
	 * @method \boolean requireIsRequired()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar resetIsRequired()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar unsetIsRequired()
	 * @method \boolean fillIsRequired()
	 * @method \boolean getIsMultiple()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar setIsMultiple(\boolean|\Bitrix\Main\DB\SqlExpression $isMultiple)
	 * @method bool hasIsMultiple()
	 * @method bool isIsMultipleFilled()
	 * @method bool isIsMultipleChanged()
	 * @method \boolean remindActualIsMultiple()
	 * @method \boolean requireIsMultiple()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar resetIsMultiple()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar unsetIsMultiple()
	 * @method \boolean fillIsMultiple()
	 * @method \string getPropertyOptions()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar setPropertyOptions(\string|\Bitrix\Main\DB\SqlExpression $propertyOptions)
	 * @method bool hasPropertyOptions()
	 * @method bool isPropertyOptionsFilled()
	 * @method bool isPropertyOptionsChanged()
	 * @method \string remindActualPropertyOptions()
	 * @method \string requirePropertyOptions()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar resetPropertyOptions()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar unsetPropertyOptions()
	 * @method \string fillPropertyOptions()
	 * @method \string getPropertySettings()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar setPropertySettings(\string|\Bitrix\Main\DB\SqlExpression $propertySettings)
	 * @method bool hasPropertySettings()
	 * @method bool isPropertySettingsFilled()
	 * @method bool isPropertySettingsChanged()
	 * @method \string remindActualPropertySettings()
	 * @method \string requirePropertySettings()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar resetPropertySettings()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar unsetPropertySettings()
	 * @method \string fillPropertySettings()
	 * @method \string getPropertyValue()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar setPropertyValue(\string|\Bitrix\Main\DB\SqlExpression $propertyValue)
	 * @method bool hasPropertyValue()
	 * @method bool isPropertyValueFilled()
	 * @method bool isPropertyValueChanged()
	 * @method \string remindActualPropertyValue()
	 * @method \string requirePropertyValue()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar resetPropertyValue()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar unsetPropertyValue()
	 * @method \string fillPropertyValue()
	 * @method \Bitrix\Main\Type\DateTime getCreatedDate()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar setCreatedDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $createdDate)
	 * @method bool hasCreatedDate()
	 * @method bool isCreatedDateFilled()
	 * @method bool isCreatedDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualCreatedDate()
	 * @method \Bitrix\Main\Type\DateTime requireCreatedDate()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar resetCreatedDate()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar unsetCreatedDate()
	 * @method \Bitrix\Main\Type\DateTime fillCreatedDate()
	 * @method \int getCreatedBy()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar setCreatedBy(\int|\Bitrix\Main\DB\SqlExpression $createdBy)
	 * @method bool hasCreatedBy()
	 * @method bool isCreatedByFilled()
	 * @method bool isCreatedByChanged()
	 * @method \int remindActualCreatedBy()
	 * @method \int requireCreatedBy()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar resetCreatedBy()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar unsetCreatedBy()
	 * @method \int fillCreatedBy()
	 * @method \string getVisibility()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar setVisibility(\string|\Bitrix\Main\DB\SqlExpression $visibility)
	 * @method bool hasVisibility()
	 * @method bool isVisibilityFilled()
	 * @method bool isVisibilityChanged()
	 * @method \string remindActualVisibility()
	 * @method \string requireVisibility()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar resetVisibility()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar unsetVisibility()
	 * @method \string fillVisibility()
	 * @method \Bitrix\Main\Type\DateTime getModifiedDate()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar setModifiedDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $modifiedDate)
	 * @method bool hasModifiedDate()
	 * @method bool isModifiedDateFilled()
	 * @method bool isModifiedDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualModifiedDate()
	 * @method \Bitrix\Main\Type\DateTime requireModifiedDate()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar resetModifiedDate()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar unsetModifiedDate()
	 * @method \Bitrix\Main\Type\DateTime fillModifiedDate()
	 * @method \int getModifiedBy()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar setModifiedBy(\int|\Bitrix\Main\DB\SqlExpression $modifiedBy)
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method \int remindActualModifiedBy()
	 * @method \int requireModifiedBy()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar resetModifiedBy()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar unsetModifiedBy()
	 * @method \int fillModifiedBy()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar reset($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar wakeUp($data)
	 */
	class EO_GlobalVar {
		/* @var \Bitrix\Bizproc\Workflow\Type\Entity\GlobalVarTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Type\Entity\GlobalVarTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Workflow\Type\Entity {
	/**
	 * EO_GlobalVar_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getPropertyTypeList()
	 * @method \string[] fillPropertyType()
	 * @method \boolean[] getIsRequiredList()
	 * @method \boolean[] fillIsRequired()
	 * @method \boolean[] getIsMultipleList()
	 * @method \boolean[] fillIsMultiple()
	 * @method \string[] getPropertyOptionsList()
	 * @method \string[] fillPropertyOptions()
	 * @method \string[] getPropertySettingsList()
	 * @method \string[] fillPropertySettings()
	 * @method \string[] getPropertyValueList()
	 * @method \string[] fillPropertyValue()
	 * @method \Bitrix\Main\Type\DateTime[] getCreatedDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillCreatedDate()
	 * @method \int[] getCreatedByList()
	 * @method \int[] fillCreatedBy()
	 * @method \string[] getVisibilityList()
	 * @method \string[] fillVisibility()
	 * @method \Bitrix\Main\Type\DateTime[] getModifiedDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillModifiedDate()
	 * @method \int[] getModifiedByList()
	 * @method \int[] fillModifiedBy()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar $object)
	 * @method bool has(\Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_GlobalVar_Collection merge(?EO_GlobalVar_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_GlobalVar_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Workflow\Type\Entity\GlobalVarTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Type\Entity\GlobalVarTable';
	}
}
namespace Bitrix\Bizproc\Workflow\Type\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_GlobalVar_Result exec()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_GlobalVar_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar_Collection fetchCollection()
	 */
	class EO_GlobalVar_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar_Collection createCollection()
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalVar_Collection wakeUpCollection($rows)
	 */
	class EO_GlobalVar_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Workflow\Task\TaskUserTable:bizproc/lib/workflow/task/taskusertable.php */
namespace Bitrix\Bizproc\Workflow\Task {
	/**
	 * EO_TaskUser
	 * @see \Bitrix\Bizproc\Workflow\Task\TaskUserTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getUserId()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser setUserId(\int|\Bitrix\Main\DB\SqlExpression $userId)
	 * @method bool hasUserId()
	 * @method bool isUserIdFilled()
	 * @method bool isUserIdChanged()
	 * @method \int remindActualUserId()
	 * @method \int requireUserId()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser resetUserId()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser unsetUserId()
	 * @method \int fillUserId()
	 * @method \int getTaskId()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser setTaskId(\int|\Bitrix\Main\DB\SqlExpression $taskId)
	 * @method bool hasTaskId()
	 * @method bool isTaskIdFilled()
	 * @method bool isTaskIdChanged()
	 * @method \int remindActualTaskId()
	 * @method \int requireTaskId()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser resetTaskId()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser unsetTaskId()
	 * @method \int fillTaskId()
	 * @method \int getStatus()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser setStatus(\int|\Bitrix\Main\DB\SqlExpression $status)
	 * @method bool hasStatus()
	 * @method bool isStatusFilled()
	 * @method bool isStatusChanged()
	 * @method \int remindActualStatus()
	 * @method \int requireStatus()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser resetStatus()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser unsetStatus()
	 * @method \int fillStatus()
	 * @method null|\Bitrix\Main\Type\DateTime getDateUpdate()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser setDateUpdate(null|\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateUpdate)
	 * @method bool hasDateUpdate()
	 * @method bool isDateUpdateFilled()
	 * @method bool isDateUpdateChanged()
	 * @method null|\Bitrix\Main\Type\DateTime remindActualDateUpdate()
	 * @method null|\Bitrix\Main\Type\DateTime requireDateUpdate()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser resetDateUpdate()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser unsetDateUpdate()
	 * @method null|\Bitrix\Main\Type\DateTime fillDateUpdate()
	 * @method \int getOriginalUserId()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser setOriginalUserId(\int|\Bitrix\Main\DB\SqlExpression $originalUserId)
	 * @method bool hasOriginalUserId()
	 * @method bool isOriginalUserIdFilled()
	 * @method bool isOriginalUserIdChanged()
	 * @method \int remindActualOriginalUserId()
	 * @method \int requireOriginalUserId()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser resetOriginalUserId()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser unsetOriginalUserId()
	 * @method \int fillOriginalUserId()
	 * @method \Bitrix\Bizproc\Workflow\Task getUserTasks()
	 * @method \Bitrix\Bizproc\Workflow\Task remindActualUserTasks()
	 * @method \Bitrix\Bizproc\Workflow\Task requireUserTasks()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser setUserTasks(\Bitrix\Bizproc\Workflow\Task $object)
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser resetUserTasks()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser unsetUserTasks()
	 * @method bool hasUserTasks()
	 * @method bool isUserTasksFilled()
	 * @method bool isUserTasksChanged()
	 * @method \Bitrix\Bizproc\Workflow\Task fillUserTasks()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent getUserTasksSearchContent()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent remindActualUserTasksSearchContent()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent requireUserTasksSearchContent()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser setUserTasksSearchContent(\Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent $object)
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser resetUserTasksSearchContent()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser unsetUserTasksSearchContent()
	 * @method bool hasUserTasksSearchContent()
	 * @method bool isUserTasksSearchContentFilled()
	 * @method bool isUserTasksSearchContentChanged()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent fillUserTasksSearchContent()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser reset($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Workflow\Task\EO_TaskUser wakeUp($data)
	 */
	class EO_TaskUser {
		/* @var \Bitrix\Bizproc\Workflow\Task\TaskUserTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Task\TaskUserTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Workflow\Task {
	/**
	 * EO_TaskUser_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getUserIdList()
	 * @method \int[] fillUserId()
	 * @method \int[] getTaskIdList()
	 * @method \int[] fillTaskId()
	 * @method \int[] getStatusList()
	 * @method \int[] fillStatus()
	 * @method null|\Bitrix\Main\Type\DateTime[] getDateUpdateList()
	 * @method null|\Bitrix\Main\Type\DateTime[] fillDateUpdate()
	 * @method \int[] getOriginalUserIdList()
	 * @method \int[] fillOriginalUserId()
	 * @method \Bitrix\Bizproc\Workflow\Task[] getUserTasksList()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser_Collection getUserTasksCollection()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_Task_Collection fillUserTasks()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent[] getUserTasksSearchContentList()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser_Collection getUserTasksSearchContentCollection()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent_Collection fillUserTasksSearchContent()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Workflow\Task\EO_TaskUser $object)
	 * @method bool has(\Bitrix\Bizproc\Workflow\Task\EO_TaskUser $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Workflow\Task\EO_TaskUser $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Workflow\Task\EO_TaskUser_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_TaskUser_Collection merge(?EO_TaskUser_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_TaskUser_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Workflow\Task\TaskUserTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Task\TaskUserTable';
	}
}
namespace Bitrix\Bizproc\Workflow\Task {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_TaskUser_Result exec()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_TaskUser_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser_Collection fetchCollection()
	 */
	class EO_TaskUser_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser_Collection createCollection()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser_Collection wakeUpCollection($rows)
	 */
	class EO_TaskUser_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Workflow\Task\TaskSearchContentTable:bizproc/lib/workflow/task/tasksearchcontenttable.php */
namespace Bitrix\Bizproc\Workflow\Task {
	/**
	 * EO_TaskSearchContent
	 * @see \Bitrix\Bizproc\Workflow\Task\TaskSearchContentTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getTaskId()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent setTaskId(\int|\Bitrix\Main\DB\SqlExpression $taskId)
	 * @method bool hasTaskId()
	 * @method bool isTaskIdFilled()
	 * @method bool isTaskIdChanged()
	 * @method \string getWorkflowId()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent setWorkflowId(\string|\Bitrix\Main\DB\SqlExpression $workflowId)
	 * @method bool hasWorkflowId()
	 * @method bool isWorkflowIdFilled()
	 * @method bool isWorkflowIdChanged()
	 * @method \string remindActualWorkflowId()
	 * @method \string requireWorkflowId()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent resetWorkflowId()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent unsetWorkflowId()
	 * @method \string fillWorkflowId()
	 * @method \string getSearchContent()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent setSearchContent(\string|\Bitrix\Main\DB\SqlExpression $searchContent)
	 * @method bool hasSearchContent()
	 * @method bool isSearchContentFilled()
	 * @method bool isSearchContentChanged()
	 * @method \string remindActualSearchContent()
	 * @method \string requireSearchContent()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent resetSearchContent()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent unsetSearchContent()
	 * @method \string fillSearchContent()
	 * @method \Bitrix\Bizproc\Workflow\Task getTask()
	 * @method \Bitrix\Bizproc\Workflow\Task remindActualTask()
	 * @method \Bitrix\Bizproc\Workflow\Task requireTask()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent setTask(\Bitrix\Bizproc\Workflow\Task $object)
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent resetTask()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent unsetTask()
	 * @method bool hasTask()
	 * @method bool isTaskFilled()
	 * @method bool isTaskChanged()
	 * @method \Bitrix\Bizproc\Workflow\Task fillTask()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser_Collection getUsers()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser_Collection requireUsers()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser_Collection fillUsers()
	 * @method bool hasUsers()
	 * @method bool isUsersFilled()
	 * @method bool isUsersChanged()
	 * @method void addToUsers(\Bitrix\Bizproc\Workflow\Task\EO_TaskUser $taskUser)
	 * @method void removeFromUsers(\Bitrix\Bizproc\Workflow\Task\EO_TaskUser $taskUser)
	 * @method void removeAllUsers()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent resetUsers()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent unsetUsers()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent reset($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent wakeUp($data)
	 */
	class EO_TaskSearchContent {
		/* @var \Bitrix\Bizproc\Workflow\Task\TaskSearchContentTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Task\TaskSearchContentTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Workflow\Task {
	/**
	 * EO_TaskSearchContent_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getTaskIdList()
	 * @method \string[] getWorkflowIdList()
	 * @method \string[] fillWorkflowId()
	 * @method \string[] getSearchContentList()
	 * @method \string[] fillSearchContent()
	 * @method \Bitrix\Bizproc\Workflow\Task[] getTaskList()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent_Collection getTaskCollection()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_Task_Collection fillTask()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser_Collection[] getUsersList()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser_Collection getUsersCollection()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskUser_Collection fillUsers()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent $object)
	 * @method bool has(\Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_TaskSearchContent_Collection merge(?EO_TaskSearchContent_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_TaskSearchContent_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Workflow\Task\TaskSearchContentTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Task\TaskSearchContentTable';
	}
}
namespace Bitrix\Bizproc\Workflow\Task {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_TaskSearchContent_Result exec()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_TaskSearchContent_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent_Collection fetchCollection()
	 */
	class EO_TaskSearchContent_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent_Collection createCollection()
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Workflow\Task\EO_TaskSearchContent_Collection wakeUpCollection($rows)
	 */
	class EO_TaskSearchContent_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionDocumentTable:bizproc/lib/debugger/session/entity/debuggersessiondocumenttable.php */
namespace Bitrix\Bizproc\Debugger\Session\Entity {
	/**
	 * Document
	 * @see \Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionDocumentTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Bizproc\Debugger\Session\Document setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getSessionId()
	 * @method \Bitrix\Bizproc\Debugger\Session\Document setSessionId(\string|\Bitrix\Main\DB\SqlExpression $sessionId)
	 * @method bool hasSessionId()
	 * @method bool isSessionIdFilled()
	 * @method bool isSessionIdChanged()
	 * @method \string remindActualSessionId()
	 * @method \string requireSessionId()
	 * @method \Bitrix\Bizproc\Debugger\Session\Document resetSessionId()
	 * @method \Bitrix\Bizproc\Debugger\Session\Document unsetSessionId()
	 * @method \string fillSessionId()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session getSession()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session remindActualSession()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session requireSession()
	 * @method \Bitrix\Bizproc\Debugger\Session\Document setSession(\Bitrix\Bizproc\Debugger\Session\Session $object)
	 * @method \Bitrix\Bizproc\Debugger\Session\Document resetSession()
	 * @method \Bitrix\Bizproc\Debugger\Session\Document unsetSession()
	 * @method bool hasSession()
	 * @method bool isSessionFilled()
	 * @method bool isSessionChanged()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session fillSession()
	 * @method \string getDocumentId()
	 * @method \Bitrix\Bizproc\Debugger\Session\Document setDocumentId(\string|\Bitrix\Main\DB\SqlExpression $documentId)
	 * @method bool hasDocumentId()
	 * @method bool isDocumentIdFilled()
	 * @method bool isDocumentIdChanged()
	 * @method \string remindActualDocumentId()
	 * @method \string requireDocumentId()
	 * @method \Bitrix\Bizproc\Debugger\Session\Document resetDocumentId()
	 * @method \Bitrix\Bizproc\Debugger\Session\Document unsetDocumentId()
	 * @method \string fillDocumentId()
	 * @method \Bitrix\Main\Type\DateTime getDateExpire()
	 * @method \Bitrix\Bizproc\Debugger\Session\Document setDateExpire(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $dateExpire)
	 * @method bool hasDateExpire()
	 * @method bool isDateExpireFilled()
	 * @method bool isDateExpireChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualDateExpire()
	 * @method \Bitrix\Main\Type\DateTime requireDateExpire()
	 * @method \Bitrix\Bizproc\Debugger\Session\Document resetDateExpire()
	 * @method \Bitrix\Bizproc\Debugger\Session\Document unsetDateExpire()
	 * @method \Bitrix\Main\Type\DateTime fillDateExpire()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Debugger\Session\Document set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Debugger\Session\Document reset($fieldName)
	 * @method \Bitrix\Bizproc\Debugger\Session\Document unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Debugger\Session\Document wakeUp($data)
	 */
	class EO_DebuggerSessionDocument {
		/* @var \Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionDocumentTable */
		static public $dataClass = '\Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionDocumentTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Debugger\Session\Entity {
	/**
	 * EO_DebuggerSessionDocument_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getSessionIdList()
	 * @method \string[] fillSessionId()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session[] getSessionList()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionDocument_Collection getSessionCollection()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSession_Collection fillSession()
	 * @method \string[] getDocumentIdList()
	 * @method \string[] fillDocumentId()
	 * @method \Bitrix\Main\Type\DateTime[] getDateExpireList()
	 * @method \Bitrix\Main\Type\DateTime[] fillDateExpire()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Debugger\Session\Document $object)
	 * @method bool has(\Bitrix\Bizproc\Debugger\Session\Document $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Debugger\Session\Document getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Debugger\Session\Document[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Debugger\Session\Document $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionDocument_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Debugger\Session\Document current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_DebuggerSessionDocument_Collection merge(?EO_DebuggerSessionDocument_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_DebuggerSessionDocument_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionDocumentTable */
		static public $dataClass = '\Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionDocumentTable';
	}
}
namespace Bitrix\Bizproc\Debugger\Session\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_DebuggerSessionDocument_Result exec()
	 * @method \Bitrix\Bizproc\Debugger\Session\Document fetchObject()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionDocument_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_DebuggerSessionDocument_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Debugger\Session\Document fetchObject()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionDocument_Collection fetchCollection()
	 */
	class EO_DebuggerSessionDocument_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Debugger\Session\Document createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionDocument_Collection createCollection()
	 * @method \Bitrix\Bizproc\Debugger\Session\Document wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionDocument_Collection wakeUpCollection($rows)
	 */
	class EO_DebuggerSessionDocument_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionWorkflowContextTable:bizproc/lib/debugger/session/entity/debuggersessionworkflowcontexttable.php */
namespace Bitrix\Bizproc\Debugger\Session\Entity {
	/**
	 * WorkflowContext
	 * @see \Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionWorkflowContextTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getSessionId()
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext setSessionId(\string|\Bitrix\Main\DB\SqlExpression $sessionId)
	 * @method bool hasSessionId()
	 * @method bool isSessionIdFilled()
	 * @method bool isSessionIdChanged()
	 * @method \string remindActualSessionId()
	 * @method \string requireSessionId()
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext resetSessionId()
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext unsetSessionId()
	 * @method \string fillSessionId()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session getSession()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session remindActualSession()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session requireSession()
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext setSession(\Bitrix\Bizproc\Debugger\Session\Session $object)
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext resetSession()
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext unsetSession()
	 * @method bool hasSession()
	 * @method bool isSessionFilled()
	 * @method bool isSessionChanged()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session fillSession()
	 * @method \string getWorkflowId()
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext setWorkflowId(\string|\Bitrix\Main\DB\SqlExpression $workflowId)
	 * @method bool hasWorkflowId()
	 * @method bool isWorkflowIdFilled()
	 * @method bool isWorkflowIdChanged()
	 * @method \string remindActualWorkflowId()
	 * @method \string requireWorkflowId()
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext resetWorkflowId()
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext unsetWorkflowId()
	 * @method \string fillWorkflowId()
	 * @method \int getTemplateShardsId()
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext setTemplateShardsId(\int|\Bitrix\Main\DB\SqlExpression $templateShardsId)
	 * @method bool hasTemplateShardsId()
	 * @method bool isTemplateShardsIdFilled()
	 * @method bool isTemplateShardsIdChanged()
	 * @method \int remindActualTemplateShardsId()
	 * @method \int requireTemplateShardsId()
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext resetTemplateShardsId()
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext unsetTemplateShardsId()
	 * @method \int fillTemplateShardsId()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards getTemplateShards()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards remindActualTemplateShards()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards requireTemplateShards()
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext setTemplateShards(\Bitrix\Bizproc\Debugger\Session\TemplateShards $object)
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext resetTemplateShards()
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext unsetTemplateShards()
	 * @method bool hasTemplateShards()
	 * @method bool isTemplateShardsFilled()
	 * @method bool isTemplateShardsChanged()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards fillTemplateShards()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext reset($fieldName)
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Debugger\Session\WorkflowContext wakeUp($data)
	 */
	class EO_DebuggerSessionWorkflowContext {
		/* @var \Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionWorkflowContextTable */
		static public $dataClass = '\Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionWorkflowContextTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Debugger\Session\Entity {
	/**
	 * EO_DebuggerSessionWorkflowContext_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getSessionIdList()
	 * @method \string[] fillSessionId()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session[] getSessionList()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionWorkflowContext_Collection getSessionCollection()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSession_Collection fillSession()
	 * @method \string[] getWorkflowIdList()
	 * @method \string[] fillWorkflowId()
	 * @method \int[] getTemplateShardsIdList()
	 * @method \int[] fillTemplateShardsId()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards[] getTemplateShardsList()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionWorkflowContext_Collection getTemplateShardsCollection()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionTemplateShards_Collection fillTemplateShards()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Debugger\Session\WorkflowContext $object)
	 * @method bool has(\Bitrix\Bizproc\Debugger\Session\WorkflowContext $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Debugger\Session\WorkflowContext $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionWorkflowContext_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_DebuggerSessionWorkflowContext_Collection merge(?EO_DebuggerSessionWorkflowContext_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_DebuggerSessionWorkflowContext_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionWorkflowContextTable */
		static public $dataClass = '\Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionWorkflowContextTable';
	}
}
namespace Bitrix\Bizproc\Debugger\Session\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_DebuggerSessionWorkflowContext_Result exec()
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext fetchObject()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionWorkflowContext_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_DebuggerSessionWorkflowContext_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext fetchObject()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionWorkflowContext_Collection fetchCollection()
	 */
	class EO_DebuggerSessionWorkflowContext_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionWorkflowContext_Collection createCollection()
	 * @method \Bitrix\Bizproc\Debugger\Session\WorkflowContext wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionWorkflowContext_Collection wakeUpCollection($rows)
	 */
	class EO_DebuggerSessionWorkflowContext_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionTemplateShardsTable:bizproc/lib/debugger/session/entity/debuggersessiontemplateshardstable.php */
namespace Bitrix\Bizproc\Debugger\Session\Entity {
	/**
	 * TemplateShards
	 * @see \Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionTemplateShardsTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \int getTemplateId()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards setTemplateId(\int|\Bitrix\Main\DB\SqlExpression $templateId)
	 * @method bool hasTemplateId()
	 * @method bool isTemplateIdFilled()
	 * @method bool isTemplateIdChanged()
	 * @method \int remindActualTemplateId()
	 * @method \int requireTemplateId()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards resetTemplateId()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards unsetTemplateId()
	 * @method \int fillTemplateId()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl getTemplate()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl remindActualTemplate()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl requireTemplate()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards setTemplate(\Bitrix\Bizproc\Workflow\Template\Tpl $object)
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards resetTemplate()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards unsetTemplate()
	 * @method bool hasTemplate()
	 * @method bool isTemplateFilled()
	 * @method bool isTemplateChanged()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl fillTemplate()
	 * @method array getShards()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards setShards(array|\Bitrix\Main\DB\SqlExpression $shards)
	 * @method bool hasShards()
	 * @method bool isShardsFilled()
	 * @method bool isShardsChanged()
	 * @method array remindActualShards()
	 * @method array requireShards()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards resetShards()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards unsetShards()
	 * @method array fillShards()
	 * @method \string getTemplateType()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards setTemplateType(\string|\Bitrix\Main\DB\SqlExpression $templateType)
	 * @method bool hasTemplateType()
	 * @method bool isTemplateTypeFilled()
	 * @method bool isTemplateTypeChanged()
	 * @method \string remindActualTemplateType()
	 * @method \string requireTemplateType()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards resetTemplateType()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards unsetTemplateType()
	 * @method \string fillTemplateType()
	 * @method \Bitrix\Main\Type\DateTime getModified()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards setModified(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $modified)
	 * @method bool hasModified()
	 * @method bool isModifiedFilled()
	 * @method bool isModifiedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualModified()
	 * @method \Bitrix\Main\Type\DateTime requireModified()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards resetModified()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards unsetModified()
	 * @method \Bitrix\Main\Type\DateTime fillModified()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards reset($fieldName)
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Debugger\Session\TemplateShards wakeUp($data)
	 */
	class EO_DebuggerSessionTemplateShards {
		/* @var \Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionTemplateShardsTable */
		static public $dataClass = '\Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionTemplateShardsTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Debugger\Session\Entity {
	/**
	 * EO_DebuggerSessionTemplateShards_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \int[] getTemplateIdList()
	 * @method \int[] fillTemplateId()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl[] getTemplateList()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionTemplateShards_Collection getTemplateCollection()
	 * @method \Bitrix\Bizproc\Workflow\Template\Entity\EO_WorkflowTemplate_Collection fillTemplate()
	 * @method array[] getShardsList()
	 * @method array[] fillShards()
	 * @method \string[] getTemplateTypeList()
	 * @method \string[] fillTemplateType()
	 * @method \Bitrix\Main\Type\DateTime[] getModifiedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillModified()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Debugger\Session\TemplateShards $object)
	 * @method bool has(\Bitrix\Bizproc\Debugger\Session\TemplateShards $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Debugger\Session\TemplateShards $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionTemplateShards_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_DebuggerSessionTemplateShards_Collection merge(?EO_DebuggerSessionTemplateShards_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_DebuggerSessionTemplateShards_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionTemplateShardsTable */
		static public $dataClass = '\Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionTemplateShardsTable';
	}
}
namespace Bitrix\Bizproc\Debugger\Session\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_DebuggerSessionTemplateShards_Result exec()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards fetchObject()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionTemplateShards_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_DebuggerSessionTemplateShards_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards fetchObject()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionTemplateShards_Collection fetchCollection()
	 */
	class EO_DebuggerSessionTemplateShards_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionTemplateShards_Collection createCollection()
	 * @method \Bitrix\Bizproc\Debugger\Session\TemplateShards wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionTemplateShards_Collection wakeUpCollection($rows)
	 */
	class EO_DebuggerSessionTemplateShards_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionTable:bizproc/lib/debugger/session/entity/debuggersessiontable.php */
namespace Bitrix\Bizproc\Debugger\Session\Entity {
	/**
	 * Session
	 * @see \Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getId()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session setId(\string|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getModuleId()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session resetModuleId()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session unsetModuleId()
	 * @method \string fillModuleId()
	 * @method \string getEntity()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session setEntity(\string|\Bitrix\Main\DB\SqlExpression $entity)
	 * @method bool hasEntity()
	 * @method bool isEntityFilled()
	 * @method bool isEntityChanged()
	 * @method \string remindActualEntity()
	 * @method \string requireEntity()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session resetEntity()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session unsetEntity()
	 * @method \string fillEntity()
	 * @method \string getDocumentType()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session setDocumentType(\string|\Bitrix\Main\DB\SqlExpression $documentType)
	 * @method bool hasDocumentType()
	 * @method bool isDocumentTypeFilled()
	 * @method bool isDocumentTypeChanged()
	 * @method \string remindActualDocumentType()
	 * @method \string requireDocumentType()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session resetDocumentType()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session unsetDocumentType()
	 * @method \string fillDocumentType()
	 * @method \int getDocumentCategoryId()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session setDocumentCategoryId(\int|\Bitrix\Main\DB\SqlExpression $documentCategoryId)
	 * @method bool hasDocumentCategoryId()
	 * @method bool isDocumentCategoryIdFilled()
	 * @method bool isDocumentCategoryIdChanged()
	 * @method \int remindActualDocumentCategoryId()
	 * @method \int requireDocumentCategoryId()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session resetDocumentCategoryId()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session unsetDocumentCategoryId()
	 * @method \int fillDocumentCategoryId()
	 * @method \int getMode()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session setMode(\int|\Bitrix\Main\DB\SqlExpression $mode)
	 * @method bool hasMode()
	 * @method bool isModeFilled()
	 * @method bool isModeChanged()
	 * @method \int remindActualMode()
	 * @method \int requireMode()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session resetMode()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session unsetMode()
	 * @method \int fillMode()
	 * @method \string getTitle()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session setTitle(\string|\Bitrix\Main\DB\SqlExpression $title)
	 * @method bool hasTitle()
	 * @method bool isTitleFilled()
	 * @method bool isTitleChanged()
	 * @method \string remindActualTitle()
	 * @method \string requireTitle()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session resetTitle()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session unsetTitle()
	 * @method \string fillTitle()
	 * @method \int getStartedBy()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session setStartedBy(\int|\Bitrix\Main\DB\SqlExpression $startedBy)
	 * @method bool hasStartedBy()
	 * @method bool isStartedByFilled()
	 * @method bool isStartedByChanged()
	 * @method \int remindActualStartedBy()
	 * @method \int requireStartedBy()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session resetStartedBy()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session unsetStartedBy()
	 * @method \int fillStartedBy()
	 * @method \Bitrix\Main\Type\DateTime getStartedDate()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session setStartedDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $startedDate)
	 * @method bool hasStartedDate()
	 * @method bool isStartedDateFilled()
	 * @method bool isStartedDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualStartedDate()
	 * @method \Bitrix\Main\Type\DateTime requireStartedDate()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session resetStartedDate()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session unsetStartedDate()
	 * @method \Bitrix\Main\Type\DateTime fillStartedDate()
	 * @method \Bitrix\Main\Type\DateTime getFinishedDate()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session setFinishedDate(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $finishedDate)
	 * @method bool hasFinishedDate()
	 * @method bool isFinishedDateFilled()
	 * @method bool isFinishedDateChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualFinishedDate()
	 * @method \Bitrix\Main\Type\DateTime requireFinishedDate()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session resetFinishedDate()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session unsetFinishedDate()
	 * @method \Bitrix\Main\Type\DateTime fillFinishedDate()
	 * @method \boolean getActive()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session setActive(\boolean|\Bitrix\Main\DB\SqlExpression $active)
	 * @method bool hasActive()
	 * @method bool isActiveFilled()
	 * @method bool isActiveChanged()
	 * @method \boolean remindActualActive()
	 * @method \boolean requireActive()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session resetActive()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session unsetActive()
	 * @method \boolean fillActive()
	 * @method \boolean getFixed()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session setFixed(\boolean|\Bitrix\Main\DB\SqlExpression $fixed)
	 * @method bool hasFixed()
	 * @method bool isFixedFilled()
	 * @method bool isFixedChanged()
	 * @method \boolean remindActualFixed()
	 * @method \boolean requireFixed()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session resetFixed()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session unsetFixed()
	 * @method \boolean fillFixed()
	 * @method \int getDebuggerState()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session setDebuggerState(\int|\Bitrix\Main\DB\SqlExpression $debuggerState)
	 * @method bool hasDebuggerState()
	 * @method bool isDebuggerStateFilled()
	 * @method bool isDebuggerStateChanged()
	 * @method \int remindActualDebuggerState()
	 * @method \int requireDebuggerState()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session resetDebuggerState()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session unsetDebuggerState()
	 * @method \int fillDebuggerState()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionDocument_Collection getDocuments()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionDocument_Collection requireDocuments()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionDocument_Collection fillDocuments()
	 * @method bool hasDocuments()
	 * @method bool isDocumentsFilled()
	 * @method bool isDocumentsChanged()
	 * @method void addToDocuments(\Bitrix\Bizproc\Debugger\Session\Document $debuggerSessionDocument)
	 * @method void removeFromDocuments(\Bitrix\Bizproc\Debugger\Session\Document $debuggerSessionDocument)
	 * @method void removeAllDocuments()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session resetDocuments()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session unsetDocuments()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionWorkflowContext_Collection getWorkflowContexts()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionWorkflowContext_Collection requireWorkflowContexts()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionWorkflowContext_Collection fillWorkflowContexts()
	 * @method bool hasWorkflowContexts()
	 * @method bool isWorkflowContextsFilled()
	 * @method bool isWorkflowContextsChanged()
	 * @method void addToWorkflowContexts(\Bitrix\Bizproc\Debugger\Session\WorkflowContext $debuggerSessionWorkflowContext)
	 * @method void removeFromWorkflowContexts(\Bitrix\Bizproc\Debugger\Session\WorkflowContext $debuggerSessionWorkflowContext)
	 * @method void removeAllWorkflowContexts()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session resetWorkflowContexts()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session unsetWorkflowContexts()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Debugger\Session\Session set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Debugger\Session\Session reset($fieldName)
	 * @method \Bitrix\Bizproc\Debugger\Session\Session unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Debugger\Session\Session wakeUp($data)
	 */
	class EO_DebuggerSession {
		/* @var \Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionTable */
		static public $dataClass = '\Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Debugger\Session\Entity {
	/**
	 * EO_DebuggerSession_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string[] getIdList()
	 * @method \string[] getModuleIdList()
	 * @method \string[] fillModuleId()
	 * @method \string[] getEntityList()
	 * @method \string[] fillEntity()
	 * @method \string[] getDocumentTypeList()
	 * @method \string[] fillDocumentType()
	 * @method \int[] getDocumentCategoryIdList()
	 * @method \int[] fillDocumentCategoryId()
	 * @method \int[] getModeList()
	 * @method \int[] fillMode()
	 * @method \string[] getTitleList()
	 * @method \string[] fillTitle()
	 * @method \int[] getStartedByList()
	 * @method \int[] fillStartedBy()
	 * @method \Bitrix\Main\Type\DateTime[] getStartedDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillStartedDate()
	 * @method \Bitrix\Main\Type\DateTime[] getFinishedDateList()
	 * @method \Bitrix\Main\Type\DateTime[] fillFinishedDate()
	 * @method \boolean[] getActiveList()
	 * @method \boolean[] fillActive()
	 * @method \boolean[] getFixedList()
	 * @method \boolean[] fillFixed()
	 * @method \int[] getDebuggerStateList()
	 * @method \int[] fillDebuggerState()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionDocument_Collection[] getDocumentsList()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionDocument_Collection getDocumentsCollection()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionDocument_Collection fillDocuments()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionWorkflowContext_Collection[] getWorkflowContextsList()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionWorkflowContext_Collection getWorkflowContextsCollection()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSessionWorkflowContext_Collection fillWorkflowContexts()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Debugger\Session\Session $object)
	 * @method bool has(\Bitrix\Bizproc\Debugger\Session\Session $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Debugger\Session\Session getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Debugger\Session\Session[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Debugger\Session\Session $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSession_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Debugger\Session\Session current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_DebuggerSession_Collection merge(?EO_DebuggerSession_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_DebuggerSession_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionTable */
		static public $dataClass = '\Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionTable';
	}
}
namespace Bitrix\Bizproc\Debugger\Session\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_DebuggerSession_Result exec()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session fetchObject()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSession_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_DebuggerSession_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Debugger\Session\Session fetchObject()
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSession_Collection fetchCollection()
	 */
	class EO_DebuggerSession_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Debugger\Session\Session createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSession_Collection createCollection()
	 * @method \Bitrix\Bizproc\Debugger\Session\Session wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Debugger\Session\Entity\EO_DebuggerSession_Collection wakeUpCollection($rows)
	 */
	class EO_DebuggerSession_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Automation\Trigger\Entity\TriggerTable:bizproc/lib/automation/trigger/entity/trigger.php */
namespace Bitrix\Bizproc\Automation\Trigger\Entity {
	/**
	 * TriggerObject
	 * @see \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject resetName()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject unsetName()
	 * @method \string fillName()
	 * @method \string getCode()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject resetCode()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject unsetCode()
	 * @method \string fillCode()
	 * @method \string getModuleId()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject resetModuleId()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject unsetModuleId()
	 * @method \string fillModuleId()
	 * @method \string getEntity()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject setEntity(\string|\Bitrix\Main\DB\SqlExpression $entity)
	 * @method bool hasEntity()
	 * @method bool isEntityFilled()
	 * @method bool isEntityChanged()
	 * @method \string remindActualEntity()
	 * @method \string requireEntity()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject resetEntity()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject unsetEntity()
	 * @method \string fillEntity()
	 * @method \string getDocumentType()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject setDocumentType(\string|\Bitrix\Main\DB\SqlExpression $documentType)
	 * @method bool hasDocumentType()
	 * @method bool isDocumentTypeFilled()
	 * @method bool isDocumentTypeChanged()
	 * @method \string remindActualDocumentType()
	 * @method \string requireDocumentType()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject resetDocumentType()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject unsetDocumentType()
	 * @method \string fillDocumentType()
	 * @method \string getDocumentStatus()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject setDocumentStatus(\string|\Bitrix\Main\DB\SqlExpression $documentStatus)
	 * @method bool hasDocumentStatus()
	 * @method bool isDocumentStatusFilled()
	 * @method bool isDocumentStatusChanged()
	 * @method \string remindActualDocumentStatus()
	 * @method \string requireDocumentStatus()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject resetDocumentStatus()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject unsetDocumentStatus()
	 * @method \string fillDocumentStatus()
	 * @method \string getApplyRules()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject setApplyRules(\string|\Bitrix\Main\DB\SqlExpression $applyRules)
	 * @method bool hasApplyRules()
	 * @method bool isApplyRulesFilled()
	 * @method bool isApplyRulesChanged()
	 * @method \string remindActualApplyRules()
	 * @method \string requireApplyRules()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject resetApplyRules()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject unsetApplyRules()
	 * @method \string fillApplyRules()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject reset($fieldName)
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject wakeUp($data)
	 */
	class EO_Trigger {
		/* @var \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerTable */
		static public $dataClass = '\Bitrix\Bizproc\Automation\Trigger\Entity\TriggerTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Automation\Trigger\Entity {
	/**
	 * EO_Trigger_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getModuleIdList()
	 * @method \string[] fillModuleId()
	 * @method \string[] getEntityList()
	 * @method \string[] fillEntity()
	 * @method \string[] getDocumentTypeList()
	 * @method \string[] fillDocumentType()
	 * @method \string[] getDocumentStatusList()
	 * @method \string[] fillDocumentStatus()
	 * @method \string[] getApplyRulesList()
	 * @method \string[] fillApplyRules()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject $object)
	 * @method bool has(\Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_Trigger_Collection merge(?EO_Trigger_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Trigger_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerTable */
		static public $dataClass = '\Bitrix\Bizproc\Automation\Trigger\Entity\TriggerTable';
	}
}
namespace Bitrix\Bizproc\Automation\Trigger\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Trigger_Result exec()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject fetchObject()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Trigger_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject fetchObject()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger_Collection fetchCollection()
	 */
	class EO_Trigger_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger_Collection createCollection()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerObject wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger_Collection wakeUpCollection($rows)
	 */
	class EO_Trigger_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Service\Entity\TrackingTable:bizproc/lib/service/entity/trackingtable.php */
namespace Bitrix\Bizproc\Service\Entity {
	/**
	 * EO_Tracking
	 * @see \Bitrix\Bizproc\Service\Entity\TrackingTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getWorkflowId()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking setWorkflowId(\string|\Bitrix\Main\DB\SqlExpression $workflowId)
	 * @method bool hasWorkflowId()
	 * @method bool isWorkflowIdFilled()
	 * @method bool isWorkflowIdChanged()
	 * @method \string remindActualWorkflowId()
	 * @method \string requireWorkflowId()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking resetWorkflowId()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking unsetWorkflowId()
	 * @method \string fillWorkflowId()
	 * @method \int getType()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking setType(\int|\Bitrix\Main\DB\SqlExpression $type)
	 * @method bool hasType()
	 * @method bool isTypeFilled()
	 * @method bool isTypeChanged()
	 * @method \int remindActualType()
	 * @method \int requireType()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking resetType()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking unsetType()
	 * @method \int fillType()
	 * @method \Bitrix\Main\Type\DateTime getModified()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking setModified(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $modified)
	 * @method bool hasModified()
	 * @method bool isModifiedFilled()
	 * @method bool isModifiedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualModified()
	 * @method \Bitrix\Main\Type\DateTime requireModified()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking resetModified()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking unsetModified()
	 * @method \Bitrix\Main\Type\DateTime fillModified()
	 * @method \string getActionName()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking setActionName(\string|\Bitrix\Main\DB\SqlExpression $actionName)
	 * @method bool hasActionName()
	 * @method bool isActionNameFilled()
	 * @method bool isActionNameChanged()
	 * @method \string remindActualActionName()
	 * @method \string requireActionName()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking resetActionName()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking unsetActionName()
	 * @method \string fillActionName()
	 * @method \string getActionTitle()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking setActionTitle(\string|\Bitrix\Main\DB\SqlExpression $actionTitle)
	 * @method bool hasActionTitle()
	 * @method bool isActionTitleFilled()
	 * @method bool isActionTitleChanged()
	 * @method \string remindActualActionTitle()
	 * @method \string requireActionTitle()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking resetActionTitle()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking unsetActionTitle()
	 * @method \string fillActionTitle()
	 * @method \int getExecutionStatus()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking setExecutionStatus(\int|\Bitrix\Main\DB\SqlExpression $executionStatus)
	 * @method bool hasExecutionStatus()
	 * @method bool isExecutionStatusFilled()
	 * @method bool isExecutionStatusChanged()
	 * @method \int remindActualExecutionStatus()
	 * @method \int requireExecutionStatus()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking resetExecutionStatus()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking unsetExecutionStatus()
	 * @method \int fillExecutionStatus()
	 * @method \int getExecutionResult()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking setExecutionResult(\int|\Bitrix\Main\DB\SqlExpression $executionResult)
	 * @method bool hasExecutionResult()
	 * @method bool isExecutionResultFilled()
	 * @method bool isExecutionResultChanged()
	 * @method \int remindActualExecutionResult()
	 * @method \int requireExecutionResult()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking resetExecutionResult()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking unsetExecutionResult()
	 * @method \int fillExecutionResult()
	 * @method null|\string getActionNote()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking setActionNote(null|\string|\Bitrix\Main\DB\SqlExpression $actionNote)
	 * @method bool hasActionNote()
	 * @method bool isActionNoteFilled()
	 * @method bool isActionNoteChanged()
	 * @method null|\string remindActualActionNote()
	 * @method null|\string requireActionNote()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking resetActionNote()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking unsetActionNote()
	 * @method null|\string fillActionNote()
	 * @method null|\int getModifiedBy()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking setModifiedBy(null|\int|\Bitrix\Main\DB\SqlExpression $modifiedBy)
	 * @method bool hasModifiedBy()
	 * @method bool isModifiedByFilled()
	 * @method bool isModifiedByChanged()
	 * @method null|\int remindActualModifiedBy()
	 * @method null|\int requireModifiedBy()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking resetModifiedBy()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking unsetModifiedBy()
	 * @method null|\int fillModifiedBy()
	 * @method \string getCompleted()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking setCompleted(\string|\Bitrix\Main\DB\SqlExpression $completed)
	 * @method bool hasCompleted()
	 * @method bool isCompletedFilled()
	 * @method bool isCompletedChanged()
	 * @method \string remindActualCompleted()
	 * @method \string requireCompleted()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking resetCompleted()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking unsetCompleted()
	 * @method \string fillCompleted()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking reset($fieldName)
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Service\Entity\EO_Tracking wakeUp($data)
	 */
	class EO_Tracking {
		/* @var \Bitrix\Bizproc\Service\Entity\TrackingTable */
		static public $dataClass = '\Bitrix\Bizproc\Service\Entity\TrackingTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc\Service\Entity {
	/**
	 * EO_Tracking_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getWorkflowIdList()
	 * @method \string[] fillWorkflowId()
	 * @method \int[] getTypeList()
	 * @method \int[] fillType()
	 * @method \Bitrix\Main\Type\DateTime[] getModifiedList()
	 * @method \Bitrix\Main\Type\DateTime[] fillModified()
	 * @method \string[] getActionNameList()
	 * @method \string[] fillActionName()
	 * @method \string[] getActionTitleList()
	 * @method \string[] fillActionTitle()
	 * @method \int[] getExecutionStatusList()
	 * @method \int[] fillExecutionStatus()
	 * @method \int[] getExecutionResultList()
	 * @method \int[] fillExecutionResult()
	 * @method null|\string[] getActionNoteList()
	 * @method null|\string[] fillActionNote()
	 * @method null|\int[] getModifiedByList()
	 * @method null|\int[] fillModifiedBy()
	 * @method \string[] getCompletedList()
	 * @method \string[] fillCompleted()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Service\Entity\EO_Tracking $object)
	 * @method bool has(\Bitrix\Bizproc\Service\Entity\EO_Tracking $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Service\Entity\EO_Tracking $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Service\Entity\EO_Tracking_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_Tracking_Collection merge(?EO_Tracking_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_Tracking_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Service\Entity\TrackingTable */
		static public $dataClass = '\Bitrix\Bizproc\Service\Entity\TrackingTable';
	}
}
namespace Bitrix\Bizproc\Service\Entity {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Tracking_Result exec()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking fetchObject()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Tracking_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking fetchObject()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking_Collection fetchCollection()
	 */
	class EO_Tracking_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking_Collection createCollection()
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Service\Entity\EO_Tracking_Collection wakeUpCollection($rows)
	 */
	class EO_Tracking_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\RestActivityTable:bizproc/lib/restactivity.php */
namespace Bitrix\Bizproc {
	/**
	 * EO_RestActivity
	 * @see \Bitrix\Bizproc\RestActivityTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Bizproc\EO_RestActivity setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getAppId()
	 * @method \Bitrix\Bizproc\EO_RestActivity setAppId(\string|\Bitrix\Main\DB\SqlExpression $appId)
	 * @method bool hasAppId()
	 * @method bool isAppIdFilled()
	 * @method bool isAppIdChanged()
	 * @method \string remindActualAppId()
	 * @method \string requireAppId()
	 * @method \Bitrix\Bizproc\EO_RestActivity resetAppId()
	 * @method \Bitrix\Bizproc\EO_RestActivity unsetAppId()
	 * @method \string fillAppId()
	 * @method \string getAppName()
	 * @method \Bitrix\Bizproc\EO_RestActivity setAppName(\string|\Bitrix\Main\DB\SqlExpression $appName)
	 * @method bool hasAppName()
	 * @method bool isAppNameFilled()
	 * @method bool isAppNameChanged()
	 * @method \string remindActualAppName()
	 * @method \string requireAppName()
	 * @method \Bitrix\Bizproc\EO_RestActivity resetAppName()
	 * @method \Bitrix\Bizproc\EO_RestActivity unsetAppName()
	 * @method \string fillAppName()
	 * @method \string getCode()
	 * @method \Bitrix\Bizproc\EO_RestActivity setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Bizproc\EO_RestActivity resetCode()
	 * @method \Bitrix\Bizproc\EO_RestActivity unsetCode()
	 * @method \string fillCode()
	 * @method \string getInternalCode()
	 * @method \Bitrix\Bizproc\EO_RestActivity setInternalCode(\string|\Bitrix\Main\DB\SqlExpression $internalCode)
	 * @method bool hasInternalCode()
	 * @method bool isInternalCodeFilled()
	 * @method bool isInternalCodeChanged()
	 * @method \string remindActualInternalCode()
	 * @method \string requireInternalCode()
	 * @method \Bitrix\Bizproc\EO_RestActivity resetInternalCode()
	 * @method \Bitrix\Bizproc\EO_RestActivity unsetInternalCode()
	 * @method \string fillInternalCode()
	 * @method \string getHandler()
	 * @method \Bitrix\Bizproc\EO_RestActivity setHandler(\string|\Bitrix\Main\DB\SqlExpression $handler)
	 * @method bool hasHandler()
	 * @method bool isHandlerFilled()
	 * @method bool isHandlerChanged()
	 * @method \string remindActualHandler()
	 * @method \string requireHandler()
	 * @method \Bitrix\Bizproc\EO_RestActivity resetHandler()
	 * @method \Bitrix\Bizproc\EO_RestActivity unsetHandler()
	 * @method \string fillHandler()
	 * @method \int getAuthUserId()
	 * @method \Bitrix\Bizproc\EO_RestActivity setAuthUserId(\int|\Bitrix\Main\DB\SqlExpression $authUserId)
	 * @method bool hasAuthUserId()
	 * @method bool isAuthUserIdFilled()
	 * @method bool isAuthUserIdChanged()
	 * @method \int remindActualAuthUserId()
	 * @method \int requireAuthUserId()
	 * @method \Bitrix\Bizproc\EO_RestActivity resetAuthUserId()
	 * @method \Bitrix\Bizproc\EO_RestActivity unsetAuthUserId()
	 * @method \int fillAuthUserId()
	 * @method \string getUseSubscription()
	 * @method \Bitrix\Bizproc\EO_RestActivity setUseSubscription(\string|\Bitrix\Main\DB\SqlExpression $useSubscription)
	 * @method bool hasUseSubscription()
	 * @method bool isUseSubscriptionFilled()
	 * @method bool isUseSubscriptionChanged()
	 * @method \string remindActualUseSubscription()
	 * @method \string requireUseSubscription()
	 * @method \Bitrix\Bizproc\EO_RestActivity resetUseSubscription()
	 * @method \Bitrix\Bizproc\EO_RestActivity unsetUseSubscription()
	 * @method \string fillUseSubscription()
	 * @method \boolean getUsePlacement()
	 * @method \Bitrix\Bizproc\EO_RestActivity setUsePlacement(\boolean|\Bitrix\Main\DB\SqlExpression $usePlacement)
	 * @method bool hasUsePlacement()
	 * @method bool isUsePlacementFilled()
	 * @method bool isUsePlacementChanged()
	 * @method \boolean remindActualUsePlacement()
	 * @method \boolean requireUsePlacement()
	 * @method \Bitrix\Bizproc\EO_RestActivity resetUsePlacement()
	 * @method \Bitrix\Bizproc\EO_RestActivity unsetUsePlacement()
	 * @method \boolean fillUsePlacement()
	 * @method \string getName()
	 * @method \Bitrix\Bizproc\EO_RestActivity setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Bizproc\EO_RestActivity resetName()
	 * @method \Bitrix\Bizproc\EO_RestActivity unsetName()
	 * @method \string fillName()
	 * @method \string getDescription()
	 * @method \Bitrix\Bizproc\EO_RestActivity setDescription(\string|\Bitrix\Main\DB\SqlExpression $description)
	 * @method bool hasDescription()
	 * @method bool isDescriptionFilled()
	 * @method bool isDescriptionChanged()
	 * @method \string remindActualDescription()
	 * @method \string requireDescription()
	 * @method \Bitrix\Bizproc\EO_RestActivity resetDescription()
	 * @method \Bitrix\Bizproc\EO_RestActivity unsetDescription()
	 * @method \string fillDescription()
	 * @method \string getProperties()
	 * @method \Bitrix\Bizproc\EO_RestActivity setProperties(\string|\Bitrix\Main\DB\SqlExpression $properties)
	 * @method bool hasProperties()
	 * @method bool isPropertiesFilled()
	 * @method bool isPropertiesChanged()
	 * @method \string remindActualProperties()
	 * @method \string requireProperties()
	 * @method \Bitrix\Bizproc\EO_RestActivity resetProperties()
	 * @method \Bitrix\Bizproc\EO_RestActivity unsetProperties()
	 * @method \string fillProperties()
	 * @method \string getReturnProperties()
	 * @method \Bitrix\Bizproc\EO_RestActivity setReturnProperties(\string|\Bitrix\Main\DB\SqlExpression $returnProperties)
	 * @method bool hasReturnProperties()
	 * @method bool isReturnPropertiesFilled()
	 * @method bool isReturnPropertiesChanged()
	 * @method \string remindActualReturnProperties()
	 * @method \string requireReturnProperties()
	 * @method \Bitrix\Bizproc\EO_RestActivity resetReturnProperties()
	 * @method \Bitrix\Bizproc\EO_RestActivity unsetReturnProperties()
	 * @method \string fillReturnProperties()
	 * @method \string getDocumentType()
	 * @method \Bitrix\Bizproc\EO_RestActivity setDocumentType(\string|\Bitrix\Main\DB\SqlExpression $documentType)
	 * @method bool hasDocumentType()
	 * @method bool isDocumentTypeFilled()
	 * @method bool isDocumentTypeChanged()
	 * @method \string remindActualDocumentType()
	 * @method \string requireDocumentType()
	 * @method \Bitrix\Bizproc\EO_RestActivity resetDocumentType()
	 * @method \Bitrix\Bizproc\EO_RestActivity unsetDocumentType()
	 * @method \string fillDocumentType()
	 * @method \string getFilter()
	 * @method \Bitrix\Bizproc\EO_RestActivity setFilter(\string|\Bitrix\Main\DB\SqlExpression $filter)
	 * @method bool hasFilter()
	 * @method bool isFilterFilled()
	 * @method bool isFilterChanged()
	 * @method \string remindActualFilter()
	 * @method \string requireFilter()
	 * @method \Bitrix\Bizproc\EO_RestActivity resetFilter()
	 * @method \Bitrix\Bizproc\EO_RestActivity unsetFilter()
	 * @method \string fillFilter()
	 * @method \boolean getIsRobot()
	 * @method \Bitrix\Bizproc\EO_RestActivity setIsRobot(\boolean|\Bitrix\Main\DB\SqlExpression $isRobot)
	 * @method bool hasIsRobot()
	 * @method bool isIsRobotFilled()
	 * @method bool isIsRobotChanged()
	 * @method \boolean remindActualIsRobot()
	 * @method \boolean requireIsRobot()
	 * @method \Bitrix\Bizproc\EO_RestActivity resetIsRobot()
	 * @method \Bitrix\Bizproc\EO_RestActivity unsetIsRobot()
	 * @method \boolean fillIsRobot()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @property-read array $primary
	 * @property-read int $state @see \Bitrix\Main\ORM\Objectify\State
	 * @property-read \Bitrix\Main\Type\Dictionary $customData
	 * @property \Bitrix\Main\Authentication\Context $authContext
	 * @method mixed get($fieldName)
	 * @method mixed remindActual($fieldName)
	 * @method mixed require($fieldName)
	 * @method bool has($fieldName)
	 * @method bool isFilled($fieldName)
	 * @method bool isChanged($fieldName)
	 * @method \Bitrix\Bizproc\EO_RestActivity set($fieldName, $value)
	 * @method \Bitrix\Bizproc\EO_RestActivity reset($fieldName)
	 * @method \Bitrix\Bizproc\EO_RestActivity unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\EO_RestActivity wakeUp($data)
	 */
	class EO_RestActivity {
		/* @var \Bitrix\Bizproc\RestActivityTable */
		static public $dataClass = '\Bitrix\Bizproc\RestActivityTable';
		/**
		 * @param bool|array $setDefaultValues
		 */
		public function __construct($setDefaultValues = true) {}
	}
}
namespace Bitrix\Bizproc {
	/**
	 * EO_RestActivity_Collection
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int[] getIdList()
	 * @method \string[] getAppIdList()
	 * @method \string[] fillAppId()
	 * @method \string[] getAppNameList()
	 * @method \string[] fillAppName()
	 * @method \string[] getCodeList()
	 * @method \string[] fillCode()
	 * @method \string[] getInternalCodeList()
	 * @method \string[] fillInternalCode()
	 * @method \string[] getHandlerList()
	 * @method \string[] fillHandler()
	 * @method \int[] getAuthUserIdList()
	 * @method \int[] fillAuthUserId()
	 * @method \string[] getUseSubscriptionList()
	 * @method \string[] fillUseSubscription()
	 * @method \boolean[] getUsePlacementList()
	 * @method \boolean[] fillUsePlacement()
	 * @method \string[] getNameList()
	 * @method \string[] fillName()
	 * @method \string[] getDescriptionList()
	 * @method \string[] fillDescription()
	 * @method \string[] getPropertiesList()
	 * @method \string[] fillProperties()
	 * @method \string[] getReturnPropertiesList()
	 * @method \string[] fillReturnProperties()
	 * @method \string[] getDocumentTypeList()
	 * @method \string[] fillDocumentType()
	 * @method \string[] getFilterList()
	 * @method \string[] fillFilter()
	 * @method \boolean[] getIsRobotList()
	 * @method \boolean[] fillIsRobot()
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\EO_RestActivity $object)
	 * @method bool has(\Bitrix\Bizproc\EO_RestActivity $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\EO_RestActivity getByPrimary($primary)
	 * @method \Bitrix\Bizproc\EO_RestActivity[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\EO_RestActivity $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\EO_RestActivity_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\EO_RestActivity current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 * @method EO_RestActivity_Collection merge(?EO_RestActivity_Collection $collection)
	 * @method bool isEmpty()
	 */
	class EO_RestActivity_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\RestActivityTable */
		static public $dataClass = '\Bitrix\Bizproc\RestActivityTable';
	}
}
namespace Bitrix\Bizproc {
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_RestActivity_Result exec()
	 * @method \Bitrix\Bizproc\EO_RestActivity fetchObject()
	 * @method \Bitrix\Bizproc\EO_RestActivity_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_RestActivity_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\EO_RestActivity fetchObject()
	 * @method \Bitrix\Bizproc\EO_RestActivity_Collection fetchCollection()
	 */
	class EO_RestActivity_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\EO_RestActivity createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\EO_RestActivity_Collection createCollection()
	 * @method \Bitrix\Bizproc\EO_RestActivity wakeUpObject($row)
	 * @method \Bitrix\Bizproc\EO_RestActivity_Collection wakeUpCollection($rows)
	 */
	class EO_RestActivity_Entity extends \Bitrix\Main\ORM\Entity {}
}