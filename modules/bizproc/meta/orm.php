<?php

/* ORMENTITYANNOTATION:Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable:bizproc/lib/workflow/template/entity/workflowtemplate.php:91a75000306bd105b298c4506f4e1d6a */
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
	 */
	class EO_WorkflowTemplate_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Template\Entity\WorkflowTemplateTable';
	}
}
namespace Bitrix\Bizproc\Workflow\Template\Entity {
	/**
	 * @method static EO_WorkflowTemplate_Query query()
	 * @method static EO_WorkflowTemplate_Result getByPrimary($primary, array $parameters = array())
	 * @method static EO_WorkflowTemplate_Result getById($id)
	 * @method static EO_WorkflowTemplate_Result getList(array $parameters = array())
	 * @method static EO_WorkflowTemplate_Entity getEntity()
	 * @method static \Bitrix\Bizproc\Workflow\Template\Tpl createObject($setDefaultValues = true)
	 * @method static \Bitrix\Bizproc\Workflow\Template\Entity\EO_WorkflowTemplate_Collection createCollection()
	 * @method static \Bitrix\Bizproc\Workflow\Template\Tpl wakeUpObject($row)
	 * @method static \Bitrix\Bizproc\Workflow\Template\Entity\EO_WorkflowTemplate_Collection wakeUpCollection($rows)
	 */
	class WorkflowTemplateTable extends \Bitrix\Main\ORM\Data\DataManager {}
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
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable:bizproc/lib/workflow/entity/workflowinstance.php:2467265dd42ce7f761f4dc4be1a67ce8 */
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
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState getState()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState remindActualState()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState requireState()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance setState(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState $object)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance resetState()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance unsetState()
	 * @method bool hasState()
	 * @method bool isStateFilled()
	 * @method bool isStateChanged()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState fillState()
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
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState[] getStateList()
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
	 */
	class EO_WorkflowInstance_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable';
	}
}
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * @method static EO_WorkflowInstance_Query query()
	 * @method static EO_WorkflowInstance_Result getByPrimary($primary, array $parameters = array())
	 * @method static EO_WorkflowInstance_Result getById($id)
	 * @method static EO_WorkflowInstance_Result getList(array $parameters = array())
	 * @method static EO_WorkflowInstance_Entity getEntity()
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance createObject($setDefaultValues = true)
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance_Collection createCollection()
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance wakeUpObject($row)
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance_Collection wakeUpCollection($rows)
	 */
	class WorkflowInstanceTable extends \Bitrix\Main\ORM\Data\DataManager {}
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
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable:bizproc/lib/workflow/entity/workflowstate.php:0ce40efc0ec507388526be92d78bf1aa */
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * EO_WorkflowState
	 * @see \Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \string getId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState setId(\string|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getModuleId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState resetModuleId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState unsetModuleId()
	 * @method \string fillModuleId()
	 * @method \string getEntity()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState setEntity(\string|\Bitrix\Main\DB\SqlExpression $entity)
	 * @method bool hasEntity()
	 * @method bool isEntityFilled()
	 * @method bool isEntityChanged()
	 * @method \string remindActualEntity()
	 * @method \string requireEntity()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState resetEntity()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState unsetEntity()
	 * @method \string fillEntity()
	 * @method \string getDocumentId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState setDocumentId(\string|\Bitrix\Main\DB\SqlExpression $documentId)
	 * @method bool hasDocumentId()
	 * @method bool isDocumentIdFilled()
	 * @method bool isDocumentIdChanged()
	 * @method \string remindActualDocumentId()
	 * @method \string requireDocumentId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState resetDocumentId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState unsetDocumentId()
	 * @method \string fillDocumentId()
	 * @method \int getDocumentIdInt()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState setDocumentIdInt(\int|\Bitrix\Main\DB\SqlExpression $documentIdInt)
	 * @method bool hasDocumentIdInt()
	 * @method bool isDocumentIdIntFilled()
	 * @method bool isDocumentIdIntChanged()
	 * @method \int remindActualDocumentIdInt()
	 * @method \int requireDocumentIdInt()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState resetDocumentIdInt()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState unsetDocumentIdInt()
	 * @method \int fillDocumentIdInt()
	 * @method \int getWorkflowTemplateId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState setWorkflowTemplateId(\int|\Bitrix\Main\DB\SqlExpression $workflowTemplateId)
	 * @method bool hasWorkflowTemplateId()
	 * @method bool isWorkflowTemplateIdFilled()
	 * @method bool isWorkflowTemplateIdChanged()
	 * @method \int remindActualWorkflowTemplateId()
	 * @method \int requireWorkflowTemplateId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState resetWorkflowTemplateId()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState unsetWorkflowTemplateId()
	 * @method \int fillWorkflowTemplateId()
	 * @method \string getState()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState setState(\string|\Bitrix\Main\DB\SqlExpression $state)
	 * @method bool hasState()
	 * @method bool isStateFilled()
	 * @method bool isStateChanged()
	 * @method \string remindActualState()
	 * @method \string requireState()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState resetState()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState unsetState()
	 * @method \string fillState()
	 * @method \string getStateParameters()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState setStateParameters(\string|\Bitrix\Main\DB\SqlExpression $stateParameters)
	 * @method bool hasStateParameters()
	 * @method bool isStateParametersFilled()
	 * @method bool isStateParametersChanged()
	 * @method \string remindActualStateParameters()
	 * @method \string requireStateParameters()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState resetStateParameters()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState unsetStateParameters()
	 * @method \string fillStateParameters()
	 * @method \Bitrix\Main\Type\DateTime getModified()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState setModified(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $modified)
	 * @method bool hasModified()
	 * @method bool isModifiedFilled()
	 * @method bool isModifiedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualModified()
	 * @method \Bitrix\Main\Type\DateTime requireModified()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState resetModified()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState unsetModified()
	 * @method \Bitrix\Main\Type\DateTime fillModified()
	 * @method \Bitrix\Main\Type\DateTime getStarted()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState setStarted(\Bitrix\Main\Type\DateTime|\Bitrix\Main\DB\SqlExpression $started)
	 * @method bool hasStarted()
	 * @method bool isStartedFilled()
	 * @method bool isStartedChanged()
	 * @method \Bitrix\Main\Type\DateTime remindActualStarted()
	 * @method \Bitrix\Main\Type\DateTime requireStarted()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState resetStarted()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState unsetStarted()
	 * @method \Bitrix\Main\Type\DateTime fillStarted()
	 * @method \int getStartedBy()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState setStartedBy(\int|\Bitrix\Main\DB\SqlExpression $startedBy)
	 * @method bool hasStartedBy()
	 * @method bool isStartedByFilled()
	 * @method bool isStartedByChanged()
	 * @method \int remindActualStartedBy()
	 * @method \int requireStartedBy()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState resetStartedBy()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState unsetStartedBy()
	 * @method \int fillStartedBy()
	 * @method \Bitrix\Main\EO_User getStartedUser()
	 * @method \Bitrix\Main\EO_User remindActualStartedUser()
	 * @method \Bitrix\Main\EO_User requireStartedUser()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState setStartedUser(\Bitrix\Main\EO_User $object)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState resetStartedUser()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState unsetStartedUser()
	 * @method bool hasStartedUser()
	 * @method bool isStartedUserFilled()
	 * @method bool isStartedUserChanged()
	 * @method \Bitrix\Main\EO_User fillStartedUser()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance getInstance()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance remindActualInstance()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance requireInstance()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState setInstance(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance $object)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState resetInstance()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState unsetInstance()
	 * @method bool hasInstance()
	 * @method bool isInstanceFilled()
	 * @method bool isInstanceChanged()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowInstance fillInstance()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl getTemplate()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl remindActualTemplate()
	 * @method \Bitrix\Bizproc\Workflow\Template\Tpl requireTemplate()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState setTemplate(\Bitrix\Bizproc\Workflow\Template\Tpl $object)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState resetTemplate()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState unsetTemplate()
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
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState reset($fieldName)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState wakeUp($data)
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
	 *
	 * Common methods:
	 * ---------------
	 *
	 * @property-read \Bitrix\Main\ORM\Entity $entity
	 * @method void add(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState $object)
	 * @method bool has(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_WorkflowState_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable';
	}
}
namespace Bitrix\Bizproc\Workflow\Entity {
	/**
	 * @method static EO_WorkflowState_Query query()
	 * @method static EO_WorkflowState_Result getByPrimary($primary, array $parameters = array())
	 * @method static EO_WorkflowState_Result getById($id)
	 * @method static EO_WorkflowState_Result getList(array $parameters = array())
	 * @method static EO_WorkflowState_Entity getEntity()
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState createObject($setDefaultValues = true)
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection createCollection()
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState wakeUpObject($row)
	 * @method static \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection wakeUpCollection($rows)
	 */
	class WorkflowStateTable extends \Bitrix\Main\ORM\Data\DataManager {}
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_WorkflowState_Result exec()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_WorkflowState_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState fetchObject()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection fetchCollection()
	 */
	class EO_WorkflowState_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection createCollection()
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Workflow\Entity\EO_WorkflowState_Collection wakeUpCollection($rows)
	 */
	class EO_WorkflowState_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Automation\Trigger\Entity\TriggerTable:bizproc/lib/automation/trigger/entity/trigger.php:20d912d401de8edd3f8f54d2b64412aa */
namespace Bitrix\Bizproc\Automation\Trigger\Entity {
	/**
	 * EO_Trigger
	 * @see \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerTable
	 *
	 * Custom methods:
	 * ---------------
	 *
	 * @method \int getId()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger setId(\int|\Bitrix\Main\DB\SqlExpression $id)
	 * @method bool hasId()
	 * @method bool isIdFilled()
	 * @method bool isIdChanged()
	 * @method \string getName()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger setName(\string|\Bitrix\Main\DB\SqlExpression $name)
	 * @method bool hasName()
	 * @method bool isNameFilled()
	 * @method bool isNameChanged()
	 * @method \string remindActualName()
	 * @method \string requireName()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger resetName()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger unsetName()
	 * @method \string fillName()
	 * @method \string getCode()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger setCode(\string|\Bitrix\Main\DB\SqlExpression $code)
	 * @method bool hasCode()
	 * @method bool isCodeFilled()
	 * @method bool isCodeChanged()
	 * @method \string remindActualCode()
	 * @method \string requireCode()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger resetCode()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger unsetCode()
	 * @method \string fillCode()
	 * @method \string getModuleId()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger setModuleId(\string|\Bitrix\Main\DB\SqlExpression $moduleId)
	 * @method bool hasModuleId()
	 * @method bool isModuleIdFilled()
	 * @method bool isModuleIdChanged()
	 * @method \string remindActualModuleId()
	 * @method \string requireModuleId()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger resetModuleId()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger unsetModuleId()
	 * @method \string fillModuleId()
	 * @method \string getEntity()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger setEntity(\string|\Bitrix\Main\DB\SqlExpression $entity)
	 * @method bool hasEntity()
	 * @method bool isEntityFilled()
	 * @method bool isEntityChanged()
	 * @method \string remindActualEntity()
	 * @method \string requireEntity()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger resetEntity()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger unsetEntity()
	 * @method \string fillEntity()
	 * @method \string getDocumentType()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger setDocumentType(\string|\Bitrix\Main\DB\SqlExpression $documentType)
	 * @method bool hasDocumentType()
	 * @method bool isDocumentTypeFilled()
	 * @method bool isDocumentTypeChanged()
	 * @method \string remindActualDocumentType()
	 * @method \string requireDocumentType()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger resetDocumentType()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger unsetDocumentType()
	 * @method \string fillDocumentType()
	 * @method \string getDocumentStatus()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger setDocumentStatus(\string|\Bitrix\Main\DB\SqlExpression $documentStatus)
	 * @method bool hasDocumentStatus()
	 * @method bool isDocumentStatusFilled()
	 * @method bool isDocumentStatusChanged()
	 * @method \string remindActualDocumentStatus()
	 * @method \string requireDocumentStatus()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger resetDocumentStatus()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger unsetDocumentStatus()
	 * @method \string fillDocumentStatus()
	 * @method \string getApplyRules()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger setApplyRules(\string|\Bitrix\Main\DB\SqlExpression $applyRules)
	 * @method bool hasApplyRules()
	 * @method bool isApplyRulesFilled()
	 * @method bool isApplyRulesChanged()
	 * @method \string remindActualApplyRules()
	 * @method \string requireApplyRules()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger resetApplyRules()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger unsetApplyRules()
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
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger set($fieldName, $value)
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger reset($fieldName)
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger unset($fieldName)
	 * @method void addTo($fieldName, $value)
	 * @method void removeFrom($fieldName, $value)
	 * @method void removeAll($fieldName)
	 * @method \Bitrix\Main\ORM\Data\Result delete()
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method mixed[] collectValues($valuesType = \Bitrix\Main\ORM\Objectify\Values::ALL, $fieldsMask = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL)
	 * @method \Bitrix\Main\ORM\Data\AddResult|\Bitrix\Main\ORM\Data\UpdateResult|\Bitrix\Main\ORM\Data\Result save()
	 * @method static \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger wakeUp($data)
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
	 * @method void add(\Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger $object)
	 * @method bool has(\Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger $object)
	 * @method bool hasByPrimary($primary)
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger getByPrimary($primary)
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger[] getAll()
	 * @method bool remove(\Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger $object)
	 * @method void removeByPrimary($primary)
	 * @method void fill($fields = \Bitrix\Main\ORM\Fields\FieldTypeMask::ALL) flag or array of field names
	 * @method static \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger_Collection wakeUp($data)
	 * @method \Bitrix\Main\ORM\Data\Result save($ignoreEvents = false)
	 * @method void offsetSet() ArrayAccess
	 * @method void offsetExists() ArrayAccess
	 * @method void offsetUnset() ArrayAccess
	 * @method void offsetGet() ArrayAccess
	 * @method void rewind() Iterator
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger current() Iterator
	 * @method mixed key() Iterator
	 * @method void next() Iterator
	 * @method bool valid() Iterator
	 * @method int count() Countable
	 */
	class EO_Trigger_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Automation\Trigger\Entity\TriggerTable */
		static public $dataClass = '\Bitrix\Bizproc\Automation\Trigger\Entity\TriggerTable';
	}
}
namespace Bitrix\Bizproc\Automation\Trigger\Entity {
	/**
	 * @method static EO_Trigger_Query query()
	 * @method static EO_Trigger_Result getByPrimary($primary, array $parameters = array())
	 * @method static EO_Trigger_Result getById($id)
	 * @method static EO_Trigger_Result getList(array $parameters = array())
	 * @method static EO_Trigger_Entity getEntity()
	 * @method static \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger createObject($setDefaultValues = true)
	 * @method static \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger_Collection createCollection()
	 * @method static \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger wakeUpObject($row)
	 * @method static \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger_Collection wakeUpCollection($rows)
	 */
	class TriggerTable extends \Bitrix\Main\ORM\Data\DataManager {}
	/**
	 * Common methods:
	 * ---------------
	 *
	 * @method EO_Trigger_Result exec()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger fetchObject()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger_Collection fetchCollection()
	 *
	 * Custom methods:
	 * ---------------
	 *
	 */
	class EO_Trigger_Query extends \Bitrix\Main\ORM\Query\Query {}
	/**
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger fetchObject()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger_Collection fetchCollection()
	 */
	class EO_Trigger_Result extends \Bitrix\Main\ORM\Query\Result {}
	/**
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger createObject($setDefaultValues = true)
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger_Collection createCollection()
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger wakeUpObject($row)
	 * @method \Bitrix\Bizproc\Automation\Trigger\Entity\EO_Trigger_Collection wakeUpCollection($rows)
	 */
	class EO_Trigger_Entity extends \Bitrix\Main\ORM\Entity {}
}
/* ORMENTITYANNOTATION:Bitrix\Bizproc\RestActivityTable:bizproc/lib/restactivity.php:7296a9af8fd010b1cd884811c4063ff6 */
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
	 */
	class EO_RestActivity_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\RestActivityTable */
		static public $dataClass = '\Bitrix\Bizproc\RestActivityTable';
	}
}
namespace Bitrix\Bizproc {
	/**
	 * @method static EO_RestActivity_Query query()
	 * @method static EO_RestActivity_Result getByPrimary($primary, array $parameters = array())
	 * @method static EO_RestActivity_Result getById($id)
	 * @method static EO_RestActivity_Result getList(array $parameters = array())
	 * @method static EO_RestActivity_Entity getEntity()
	 * @method static \Bitrix\Bizproc\EO_RestActivity createObject($setDefaultValues = true)
	 * @method static \Bitrix\Bizproc\EO_RestActivity_Collection createCollection()
	 * @method static \Bitrix\Bizproc\EO_RestActivity wakeUpObject($row)
	 * @method static \Bitrix\Bizproc\EO_RestActivity_Collection wakeUpCollection($rows)
	 */
	class RestActivityTable extends \Bitrix\Main\ORM\Data\DataManager {}
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
/* ORMENTITYANNOTATION:Bitrix\Bizproc\RestProviderTable:bizproc/lib/restprovider.php:7a8e8c1db411aec805bf95ac0df8d59a */
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
	 */
	class EO_RestProvider_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\RestProviderTable */
		static public $dataClass = '\Bitrix\Bizproc\RestProviderTable';
	}
}
namespace Bitrix\Bizproc {
	/**
	 * @method static EO_RestProvider_Query query()
	 * @method static EO_RestProvider_Result getByPrimary($primary, array $parameters = array())
	 * @method static EO_RestProvider_Result getById($id)
	 * @method static EO_RestProvider_Result getList(array $parameters = array())
	 * @method static EO_RestProvider_Entity getEntity()
	 * @method static \Bitrix\Bizproc\EO_RestProvider createObject($setDefaultValues = true)
	 * @method static \Bitrix\Bizproc\EO_RestProvider_Collection createCollection()
	 * @method static \Bitrix\Bizproc\EO_RestProvider wakeUpObject($row)
	 * @method static \Bitrix\Bizproc\EO_RestProvider_Collection wakeUpCollection($rows)
	 */
	class RestProviderTable extends \Bitrix\Main\ORM\Data\DataManager {}
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
/* ORMENTITYANNOTATION:Bitrix\Bizproc\SchedulerEventTable:bizproc/lib/schedulerevent.php:f27de84e74b3a3d810e5bce8c9aba110 */
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
	 */
	class EO_SchedulerEvent_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\SchedulerEventTable */
		static public $dataClass = '\Bitrix\Bizproc\SchedulerEventTable';
	}
}
namespace Bitrix\Bizproc {
	/**
	 * @method static EO_SchedulerEvent_Query query()
	 * @method static EO_SchedulerEvent_Result getByPrimary($primary, array $parameters = array())
	 * @method static EO_SchedulerEvent_Result getById($id)
	 * @method static EO_SchedulerEvent_Result getList(array $parameters = array())
	 * @method static EO_SchedulerEvent_Entity getEntity()
	 * @method static \Bitrix\Bizproc\EO_SchedulerEvent createObject($setDefaultValues = true)
	 * @method static \Bitrix\Bizproc\EO_SchedulerEvent_Collection createCollection()
	 * @method static \Bitrix\Bizproc\EO_SchedulerEvent wakeUpObject($row)
	 * @method static \Bitrix\Bizproc\EO_SchedulerEvent_Collection wakeUpCollection($rows)
	 */
	class SchedulerEventTable extends \Bitrix\Main\ORM\Data\DataManager {}
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
/* ORMENTITYANNOTATION:Bitrix\Bizproc\Workflow\Type\Entity\GlobalConstTable:bizproc/lib/workflow/type/entity/globalconst.php:8a062147e8af7c16c97914db6a026be7 */
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
	 */
	class EO_GlobalConst_Collection implements \ArrayAccess, \Iterator, \Countable {
		/* @var \Bitrix\Bizproc\Workflow\Type\Entity\GlobalConstTable */
		static public $dataClass = '\Bitrix\Bizproc\Workflow\Type\Entity\GlobalConstTable';
	}
}
namespace Bitrix\Bizproc\Workflow\Type\Entity {
	/**
	 * @method static EO_GlobalConst_Query query()
	 * @method static EO_GlobalConst_Result getByPrimary($primary, array $parameters = array())
	 * @method static EO_GlobalConst_Result getById($id)
	 * @method static EO_GlobalConst_Result getList(array $parameters = array())
	 * @method static EO_GlobalConst_Entity getEntity()
	 * @method static \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst createObject($setDefaultValues = true)
	 * @method static \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst_Collection createCollection()
	 * @method static \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst wakeUpObject($row)
	 * @method static \Bitrix\Bizproc\Workflow\Type\Entity\EO_GlobalConst_Collection wakeUpCollection($rows)
	 */
	class GlobalConstTable extends \Bitrix\Main\ORM\Data\DataManager {}
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