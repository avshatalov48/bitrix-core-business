<?php

use Bitrix\Bizproc;
use Bitrix\Bizproc\FieldType;

class CBPDocumentService extends CBPRuntimeService
{
	const FEATURE_MARK_MODIFIED_FIELDS = 'FEATURE_MARK_MODIFIED_FIELDS';
	const FEATURE_SET_MODIFIED_BY = 'FEATURE_SET_MODIFIED_BY';

	private $arDocumentsCache = [];
	private $documentTypesCache = [];
	private $documentFieldsCache = [];
	private $typesMapCache = [];

	private $tzFlag;

	public function getEntityName($moduleId, $entity)
	{
		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity, 'getEntityName'))
		{
			return call_user_func_array(array($entity, "getEntityName"), array($entity));
		}
		return null;
	}

	public function getDocument($parameterDocumentId, $parameterDocumentType = null)
	{
		$this->checkCache();
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		$documentType = ($parameterDocumentType && is_array($parameterDocumentType)) ? $parameterDocumentType[2] : null;

		$k = $moduleId."@".$entity."@".$documentId.($documentType ? '@'.$documentType : '');
		if (array_key_exists($k, $this->arDocumentsCache))
		{
			return $this->arDocumentsCache[$k];
		}

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity, 'GetDocument'))
		{
			$this->arDocumentsCache[$k] = call_user_func_array([$entity, "GetDocument"], [$documentId, $documentType]);
			return $this->arDocumentsCache[$k];
		}

		return null;
	}

	public function isDocumentExists(array $parameterDocumentId): bool
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity, 'isDocumentExists'))
		{
			return (bool)call_user_func_array([$entity, 'isDocumentExists'], [$documentId]);
		}

		//if no API
		$document = $this->getDocument($parameterDocumentId);
		if ($document instanceof Bizproc\Document\ValueCollection)
		{
			return (bool)$document['ID'];
		}

		return is_array($document) && count($document) > 0;
	}

	public function getFieldValue($parameterDocumentId, $fieldId, $parameterDocumentType = null)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);
		$documentType = ($parameterDocumentType && is_array($parameterDocumentType)) ? $parameterDocumentType[2] : null;

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}
		if (class_exists($entity) && method_exists($entity, 'getFieldValue'))
		{
			return call_user_func_array([$entity, "getFieldValue"], [$documentId, $fieldId, $documentType]);
		}

		$document = $this->GetDocument($parameterDocumentId, $parameterDocumentType);

		return $document[$fieldId] ?? null;
	}

	public function updateDocument($parameterDocumentId, $arFields, $modifiedBy = null)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		$this->clearCache();

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity))
		{
			$modifiedById = $modifiedBy ? CBPHelper::ExtractUsers($modifiedBy, $parameterDocumentId, true) : null;
			return call_user_func_array([$entity, 'UpdateDocument'], [$documentId, $arFields, $modifiedById]);
		}

		return false;
	}

	public function createDocument($parameterDocumentId, $arFields)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity))
			return call_user_func_array(array($entity, "CreateDocument"), array($documentId, $arFields));

		return false;
	}

	public function createTestDocument(array $parameterDocumentType, array $fields, int $createdById)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity,'createTestDocument'))
		{
			return call_user_func_array([$entity, 'createTestDocument'], [$documentType, $fields, $createdById]);
		}

		return null;
	}

	public function publishDocument($parameterDocumentId)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		$this->clearCache();

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity))
		{
			$r = call_user_func_array(array($entity, "PublishDocument"), array($documentId));
			if ($r)
				$r = array($moduleId, $entity, $r);

			return $r;
		}

		return false;
	}

	public function unpublishDocument($parameterDocumentId)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		$this->clearCache();

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity))
		{
			return call_user_func_array(array($entity, "UnpublishDocument"), array($documentId));
		}

		return false;
	}

	public function lockDocument($parameterDocumentId, $workflowId)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		$this->clearCache();

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity))
			return call_user_func_array(array($entity, "LockDocument"), array($documentId, $workflowId));

		return false;
	}

	public function unlockDocument($parameterDocumentId, $workflowId)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		$this->clearCache();

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity))
		{
			return call_user_func_array(array($entity, "UnlockDocument"), array($documentId, $workflowId));
		}

		return false;
	}

	public function deleteDocument($parameterDocumentId)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		$this->clearCache();

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity))
			return call_user_func_array(array($entity, "DeleteDocument"), array($documentId));

		return false;
	}

	public function isDocumentLocked($parameterDocumentId, $workflowId)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity))
			return call_user_func_array(array($entity, "IsDocumentLocked"), array($documentId, $workflowId));

		return false;
	}

	public function subscribeOnUnlockDocument($parameterDocumentId, $workflowId,  $eventName)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);
		RegisterModuleDependences($moduleId, $entity."_OnUnlockDocument", "bizproc", "CBPDocumentService", "OnUnlockDocument", 100, "", array($workflowId,  $eventName));
	}

	public function unsubscribeOnUnlockDocument($parameterDocumentId, $workflowId, $eventName)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);
		UnRegisterModuleDependences($moduleId, $entity."_OnUnlockDocument", "bizproc", "CBPDocumentService", "OnUnlockDocument", "", array($workflowId,  $eventName));
	}

	public static function onUnlockDocument($workflowId, $eventName, $documentId = [])
	{
		CBPRuntime::SendExternalEvent($workflowId, $eventName, []);
	}

	public function getDocumentType($parameterDocumentId)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		$k = $moduleId."@".$entity."@".$documentId;
		if (isset($this->documentTypesCache[$k]))
			return $this->documentTypesCache[$k];

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity, "GetDocumentType"))
		{
			$this->documentTypesCache[$k] = [$moduleId, $entity, call_user_func_array([$entity, "GetDocumentType"], [$documentId])];
			return $this->documentTypesCache[$k];
		}

		return null;
	}

	public function normalizeDocumentId($parameterDocumentId, string $docType = null)
	{
		$normalized = $parameterDocumentId;
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity, "normalizeDocumentId"))
		{
			$normalized = [$moduleId, $entity, call_user_func_array([$entity, "normalizeDocumentId"], [$documentId, $docType])];
		}

		return $normalized;
	}

	public function getDocumentFields($parameterDocumentType, $importExportMode = false)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		$k = $moduleId."@".$entity."@".$documentType;
		if (isset($this->documentFieldsCache[$k]))
		{
			return $this->documentFieldsCache[$k];
		}

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity))
		{
			$fields = call_user_func_array(array($entity, "GetDocumentFields"), array($documentType, $importExportMode));
			if (is_array($fields))
			{
				foreach ($fields as $key => $prop)
				{
					if ($prop["Type"] === 'integer')
					{
						$fields[$key]["Type"] = 'int';
					}
					if (empty($prop['BaseType']))
					{
						$baseTypes = [
							"int",
							"double",
							"date",
							"datetime",
							"user",
							"string",
							"bool",
							"file",
							"text",
							"select",
							'time',
						];

						$fields[$key]["BaseType"] =
							in_array($prop["Type"], $baseTypes, true)
								? $prop["Type"]
								: 'string'
						;
					}
				}
			}

			$this->documentFieldsCache[$k] = $fields;
			return $this->documentFieldsCache[$k];
		}

		return null;
	}

	public function getDocumentFieldTypes($parameterDocumentType)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity, "GetDocumentFieldTypes"))
			return call_user_func_array(array($entity, "GetDocumentFieldTypes"), array($documentType));

		return CBPHelper::GetDocumentFieldTypes();
	}

	public function addDocumentField($parameterDocumentType, $arFields)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity))
		{
			$result = call_user_func_array([$entity, 'AddDocumentField'], [$documentType, $arFields]);
			if ($result)
			{
				$k = $moduleId."@".$entity."@".$documentType;
				unset($this->documentFieldsCache[$k]);
			}

			return $result;
		}

		return false;
	}

	public function updateDocumentField($parameterDocumentType, $arFields)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity, 'UpdateDocumentField'))
			return call_user_func_array(array($entity, "UpdateDocumentField"), array($documentType, $arFields));

		return false;
	}

	public function getJSFunctionsForFields($parameterDocumentType, $objectName, $arDocumentFields = array(), $arDocumentFieldTypes = array())
	{
		if (!is_array($arDocumentFields) || count($arDocumentFields) <= 0)
			$arDocumentFields = self::GetDocumentFields($parameterDocumentType);
		if (!is_array($arDocumentFieldTypes) || count($arDocumentFieldTypes) <= 0)
			$arDocumentFieldTypes = self::GetDocumentFieldTypes($parameterDocumentType);

		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		$documentFieldsString = "";
		foreach ($arDocumentFields as $fieldKey => $arFieldValue)
		{
			if ($documentFieldsString <> '')
				$documentFieldsString .= ",";

			$documentFieldsString .= "'".Cutil::JSEscape($fieldKey)."':{";

			$documentFieldsString .= "'Name':'".CUtil::JSEscape($arFieldValue["Name"])."',";
			$documentFieldsString .= "'Type':'".CUtil::JSEscape($arFieldValue["Type"])."',";
			$documentFieldsString .= "'Multiple':'".CUtil::JSEscape(!empty($arFieldValue["Multiple"]) ? "Y" : "N")."',";
			$documentFieldsString .= "'Complex':'".CUtil::JSEscape(!empty($arFieldValue["Complex"]) ? "Y" : "N")."',";

			$documentFieldsString .= "'Options':";
			if (array_key_exists("Options", $arFieldValue))
			{
				if (is_array($arFieldValue["Options"]))
				{
					$documentFieldsString .= "{";
					$flTmp = false;
					foreach ($arFieldValue["Options"] as $k => $v)
					{
						if (!is_scalar($v))
						{
							continue;
						}

						if ($flTmp)
							$documentFieldsString .= ",";
						$documentFieldsString .= "'".CUtil::JSEscape($k)."':'".CUtil::JSEscape($v)."'";
						$flTmp = true;
					}
					$documentFieldsString .= "}";
				}
				else
				{
					$documentFieldsString .= "'".CUtil::JSEscape($arFieldValue["Options"])."'";
				}
			}
			else
			{
				$documentFieldsString .= "''";
			}

			if (isset($arFieldValue["Options"]) && CBPHelper::IsAssociativeArray($arFieldValue["Options"]))
			{
				$documentFieldsString .= ", 'OptionsSort':";
				$documentFieldsString .= CUtil::PhpToJSObject(array_keys($arFieldValue["Options"]));
			}

			if(isset($arFieldValue["Settings"]) && is_array($arFieldValue["Settings"]))
			{
				$documentFieldsString .= ", 'Settings':";
				$documentFieldsString .= CUtil::PhpToJSObject($arFieldValue["Settings"]);
			}

			$documentFieldsString .= "}";
		}

		$fieldTypesString = "";
		$ind = -1;
		foreach ($arDocumentFieldTypes as $typeKey => $arTypeValue)
		{
			$ind++;
			if ($fieldTypesString <> '')
				$fieldTypesString .= ",";

			$fieldTypesString .= "'".CUtil::JSEscape($typeKey)."':{";

			$fieldTypesString .= "'Name':'".CUtil::JSEscape($arTypeValue["Name"])."',";
			$fieldTypesString .= "'BaseType':'".CUtil::JSEscape($arTypeValue["BaseType"])."',";
			$fieldTypesString .= "'Complex':'".CUtil::JSEscape(!empty($arTypeValue["Complex"]) ? "Y" : "N")."',";
			$fieldTypesString .= "'Index':".$ind."";

			$fieldTypesString .= "}";
		}

		$documentTypeString = CUtil::PhpToJSObject($parameterDocumentType);
		$bitrixSessId = bitrix_sessid();

$result = <<<EOS
<script>
var $objectName = {};

$objectName.arDocumentFields = { $documentFieldsString };
$objectName.arFieldTypes = { $fieldTypesString };

$objectName.AddField = function(fldCode, fldName, fldType, fldMultiple, fldOptions)
{
	this.arDocumentFields[fldCode] = {};
	this.arDocumentFields[fldCode]["Name"] = fldName;
	this.arDocumentFields[fldCode]["Type"] = fldType;
	this.arDocumentFields[fldCode]["Multiple"] = fldMultiple;
	this.arDocumentFields[fldCode]["Options"] = fldOptions;
}

$objectName._PrepareResponse = function(v)
{
	v = v.replace(/^\s+|\s+$/g, '');
	while (v.length > 0 && v.charCodeAt(0) == 65279)
		v = v.substring(1);

	if (v.length <= 0)
		return undefined;

	eval("v = " + v);

	return v;
}

$objectName.GetFieldInputControl4Type = function(type, value, name, subtypeFunctionName, func)
{
	this.GetFieldInputControlInternal(
		type,
		value,
		name,
		function(v)
		{
			var p = v.indexOf('<!--__defaultOptionsValue:');
			if (p >= 0)
			{
				p = p + '<!--__defaultOptionsValue:'.length;
				var p1 = v.indexOf('-->', p);
				type['Options'] = v.substring(p, p1);
			}

			var newPromt = "";

			p = v.indexOf('<!--__modifyOptionsPromt:');
			if (p >= 0)
			{
				p = p + '<!--__modifyOptionsPromt:'.length;
				p1 = v.indexOf('-->', p);
				newPromt = v.substring(p, p1);
			}

			func(v, newPromt);
		},
		false,
		subtypeFunctionName,
		'Type'
	);
}

$objectName.GetFieldInputControl4Subtype = function(type, value, name, func)
{
	$objectName.GetFieldInputControlInternal(type, value, name, func, false, '', '');
}

$objectName.GetFieldInputControl = function(type, value, name, func, als)
{
	$objectName.GetFieldInputControlInternal(type, value, name, func, als, '', '');
}

$objectName.GetFieldInputControlInternal = function(type, value, name, func, als, subtypeFunctionName, mode)
{
	if (typeof name == "undefined" || name.length <= 0)
		name = "BPVDDefaultValue";

	if (typeof type != "object")
		type = {'Type' : type, 'Multiple' : 0, 'Required' : 0, 'Options' : null};

	if (typeof name != "object")
		name = {'Field' : name, 'Form' : null};

	BX.ajax.post(
		'/bitrix/tools/bizproc_get_field.php',
		{
			'DocumentType' : $documentTypeString,
			'Field' : name,
			'Value' : value,
			'Type' : type,
			'Als' : als ? 1 : 0,
			'rnd' : Math.random(),
			'Mode' : mode,
			'Func' : subtypeFunctionName,
			'sessid' : '$bitrixSessId'
		},
		func
	);
}

$objectName.GetFieldValueByTagName = function(tag, name, form)
{
	var fieldValues = {};

	var ar;
	if (form && (form.length > 0))
	{
		var obj = document.getElementById(form);
		if (!obj)
		{
			for (var i in document.forms)
			{
				if (document.forms[i].name == form)
				{
					obj = document.forms[i];
					break;
				}
			}
		}

		if (!obj)
			return;

		ar = obj.getElementsByTagName(tag);
	}
	else
	{
		ar = document.getElementsByTagName(tag);
	}

	for (var i in ar)
	{
		if (ar[i] && ar[i].name && (ar[i].name.length >= name.length) && (ar[i].name.substr(0, name.length) == name))
		{
			if (ar[i].type.substr(0, "select".length) == "select")
			{
				if (ar[i].multiple)
				{
					var newName = ar[i].name.replace(/\[\]/g, "");
					for (var j = 0; j < ar[i].options.length; j++)
					{
						if (ar[i].options[j].selected)
						{
							if ((typeof(fieldValues[newName]) != 'object') || !(fieldValues[newName] instanceof Array))
							{
								if (fieldValues[newName])
									fieldValues[newName] = [fieldValues[newName]];
								else
									fieldValues[newName] = [];
							}
							fieldValues[newName][fieldValues[newName].length] = ar[i].options[j].value;
						}
					}
				}
				else
				{
					if (ar[i].selectedIndex >= 0)
					{
						const name = ar[i].name;
						const value = ar[i].options[ar[i].selectedIndex].value;

						if (name.indexOf("[]", 0) >= 0)
						{
							const newName = name.replace(/\[\]/g, "");
							if ((typeof(fieldValues[newName]) !== 'object') || !(fieldValues[newName] instanceof Array))
							{
								if (fieldValues[newName])
								{
									fieldValues[newName] = [fieldValues[newName]];
								}
								else
								{
									fieldValues[newName] = [];
								}
							}

							fieldValues[newName][fieldValues[newName].length] = value;
						}
						else
						{
							fieldValues[name] = value;
						}
					}
				}
			}
			else
			{
				if (ar[i].name.indexOf("[]", 0) >= 0)
				{
					var newName = ar[i].name.replace(/\[\]/g, "");

					if ((typeof(fieldValues[newName]) != 'object') || !(fieldValues[newName] instanceof Array))
					{
						if (fieldValues[newName])
							fieldValues[newName] = [fieldValues[newName]];
						else
							fieldValues[newName] = [];
					}

					fieldValues[newName][fieldValues[newName].length] = ar[i].value;
				}
				else
				{
					fieldValues[ar[i].name] = ar[i].value;
				}
			}
		}
	}

	return fieldValues;
}

$objectName.GetFieldInputValue = function(type, name, func)
{
	if (typeof name == "undefined" || name.length <= 0)
		name = "BPVDDefaultValue";

	if (typeof type != "object")
		type = {'Type' : type, 'Multiple' : 0, 'Required' : 0, 'Options' : null};

	if (typeof name != "object")
		name = {'Field' : name, 'Form' : null};

	var s = {
		'DocumentType' : $documentTypeString,
		'Field' : name,
		'Type' : type,
		'rnd' : Math.random(),
		'sessid' : '$bitrixSessId'
	};
	
	if (name['Form'])
	{
		var objForm = document.getElementById(name['Form']);
		if (!objForm)
		{
			for (var i in document.forms)
			{
				if (document.forms[i].name == name['Form'])
				{
					objForm = document.forms[i];
					break;
				}
			}
		}

		if (objForm)
		{
			BX.ajax.prepareForm(objForm, s);
		}
	}

	if (type != null && type['Type'] != "F")
	{
		var ar = this.GetFieldValueByTagName('input', name['Field'], name['Form']);
		for (var v in ar)
			s[v] = ar[v];
		ar = this.GetFieldValueByTagName('select', name['Field'], name['Form']);
		for (var v in ar)
			s[v] = ar[v];
		ar = this.GetFieldValueByTagName('textarea', name['Field'], name['Form']);
		for (var v in ar)
			s[v] = ar[v];
		ar = this.GetFieldValueByTagName('hidden', name['Field'], name['Form']);
		for (var v in ar)
			s[v] = ar[v];
	}

	BX.ajax.post('/bitrix/tools/bizproc_set_field.php', s, function(v){v = $objectName._PrepareResponse(v); func(v);});
}

$objectName.HtmlSpecialChars = function(string, quote)
{
	string = string.toString();
	string = string.replace(/&/g, '&amp;');
	string = string.replace(/</g, '&lt;');
	string = string.replace(/>/g, '&gt;');
	string = string.replace(/"/g, '&quot;');

	if (quote)
		string = string.replace(/'/g, '&#039;');

	return string;
}

$objectName.GetGUITypeEdit = function(type)
{
	return "";
}

$objectName.SetGUITypeEdit = function(type)
{
	return "";
}

function __dump_bx(arr, limitLevel, txt)
{
	if (limitLevel == undefined)
		limitLevel = 3;
	if (txt == undefined)
		txt = "";
	else
		txt += ":\\n";
	alert(txt+__dumpInternal_bx(arr, 0, limitLevel));
}
function __dumpInternal_bx(arr, level, limitLevel) {
	var dumped_text = "";
	if(!level) level = 0;
	if (level > limitLevel)
		return "";
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";
	if(typeof(arr) == 'object') {
		for(var item in arr) {
			var value = arr[item];
			if(typeof(value) == 'object') {
				dumped_text += level_padding + "'" + item + "' ...\\n";
				dumped_text += __dumpInternal_bx(value, level+1, limitLevel);
			} else {
				dumped_text += level_padding + "'" + item + "' => '" + value + "'\\n";
			}
		}
	} else {
		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}

	return dumped_text;
}

</script>
EOS;

		if (class_exists($entity) && method_exists($entity, "GetJSFunctionsForFields"))
		{
			$result .= call_user_func_array(array($entity, "GetJSFunctionsForFields"), array($documentType, $objectName, $arDocumentFields, $arDocumentFieldTypes));
		}
		else
		{
			if (!is_array($arDocumentFields) || count($arDocumentFields) <= 0)
				$arDocumentFields = $this->GetDocumentFields($parameterDocumentType);
			if (!is_array($arDocumentFieldTypes) || count($arDocumentFieldTypes) <= 0)
				$arDocumentFieldTypes = $this->GetDocumentFieldTypes($parameterDocumentType);

			$result .= CBPHelper::GetJSFunctionsForFields($objectName, $arDocumentFields, $arDocumentFieldTypes);
		}

		return $result;
	}

	public function getFieldInputControlOptions($parameterDocumentType, &$fieldType, $jsFunctionName, &$value)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		$arFieldType = FieldType::normalizeProperty($fieldType);
		if ((string) $arFieldType["Type"] == "")
			return "";

		$fieldTypeObject = $this->getFieldTypeObject($parameterDocumentType, $arFieldType);
		if ($fieldTypeObject)
		{
			return $fieldTypeObject->renderControlOptions($jsFunctionName, $value);
		}

		$fieldType = $arFieldType;

		if (class_exists($entity) && method_exists($entity, "GetFieldInputControlOptions"))
			return call_user_func_array(array($entity, "GetFieldInputControlOptions"), array($documentType, &$fieldType, $jsFunctionName, &$value));

		return "";
	}

	public function getFieldInputControl($parameterDocumentType, $fieldType, $fieldName, $fieldValue, $bAllowSelection = false, $publicMode = false)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		$arFieldType = FieldType::normalizeProperty($fieldType);
		if ((string) $arFieldType["Type"] == "")
			return "";

		if (is_array($fieldName))
		{
			$arFieldName = array(
				'Form' => null,
				'Field' => null,
			);
			foreach ($fieldName as $key => $val)
			{
				switch(mb_strtoupper($key))
				{
					case "FORM":
					case "0":
						$arFieldName["Form"] = $val;
						break;
					case "FIELD":
					case "1":
						$arFieldName["Field"] = $val;
						break;
				}
			}
		}
		else
		{
			$arFieldName = array("Form" => null, "Field" => $fieldName);
		}
		if ((string) $arFieldName["Field"] == "" || preg_match("#[^a-z0-9_\[\]]#i", $arFieldName["Field"]))
			return "";
		if ((string) $arFieldName["Form"] != "" && preg_match("#[^a-z0-9_]#i", $arFieldName["Form"]))
			return "";

		if ($publicMode && !array_key_exists("BP_AddShowParameterInit_".$moduleId."_".$entity."_".$documentType, $GLOBALS))
		{
			$GLOBALS["BP_AddShowParameterInit_".$moduleId."_".$entity."_".$documentType] = 1;
			CBPDocument::AddShowParameterInit($moduleId, "only_users", $documentType, $entity);
		}

		$fieldTypeObject = $this->getFieldTypeObject($parameterDocumentType, $arFieldType);
		if ($fieldTypeObject)
		{
			$renderMode = $publicMode ? FieldType::RENDER_MODE_PUBLIC : FieldType::RENDER_MODE_DESIGNER;
			if (defined('ADMIN_SECTION') && ADMIN_SECTION)
			{
				$renderMode |= FieldType::RENDER_MODE_ADMIN;
				$renderMode &= ~FieldType::RENDER_MODE_PUBLIC;
			}

			if (defined('BX_MOBILE') && BX_MOBILE)
			{
				$renderMode |= FieldType::RENDER_MODE_MOBILE;
			}

			if ($renderMode & FieldType::RENDER_MODE_PUBLIC)
			{
				CUtil::InitJSCore(['bp_field_type']);
			}

			return $fieldTypeObject->renderControl($arFieldName, $fieldValue, $bAllowSelection, $renderMode);
		}

		if (class_exists($entity))
		{
			if (method_exists($entity, "GetFieldInputControl"))
				return call_user_func_array(array($entity, "GetFieldInputControl"), array($documentType, $arFieldType, $arFieldName, $fieldValue, $bAllowSelection, $publicMode));

			if (method_exists($entity, "GetGUIFieldEdit"))
				return call_user_func_array(array($entity, "GetGUIFieldEdit"), array($documentType, $arFieldName["Form"], $arFieldName["Field"], $fieldValue, $arFieldType, $bAllowSelection));
		}

		return CBPHelper::GetFieldInputControl($parameterDocumentType, $arFieldType, $arFieldName, $fieldValue, $bAllowSelection);
	}

	public function getFieldInputValue($parameterDocumentType, $fieldType, $fieldName, $arRequest, &$arErrors)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		$arFieldType = FieldType::normalizeProperty($fieldType);
		if ((string) $arFieldType["Type"] == "")
			return "";

		if (is_array($fieldName))
		{
			$arFieldName = array("Form" => null, "Field" => null);
			foreach ($fieldName as $key => $val)
			{
				switch(mb_strtoupper($key))
				{
					case "FORM":
					case "0":
						$arFieldName["Form"] = $val;
						break;
					case "FIELD":
					case "1":
						$arFieldName["Field"] = $val;
						break;
				}
			}
		}
		else
		{
			$arFieldName = array("Form" => null, "Field" => $fieldName);
		}
		if ((string) $arFieldName["Field"] == "" || preg_match("#[^a-z0-9_\[\]]#i", $arFieldName["Field"]))
			return "";
		if ((string) $arFieldName["Form"] != "" && preg_match("#[^a-z0-9_]#i", $arFieldName["Form"]))
			return "";

		$fieldTypeObject = $this->getFieldTypeObject($parameterDocumentType, $arFieldType);
		if ($fieldTypeObject)
		{
			return $fieldTypeObject->extractValue($arFieldName, $arRequest, $arErrors);
		}

		if (class_exists($entity))
		{
			if (method_exists($entity, "GetFieldInputValue"))
				return call_user_func_array(array($entity, "GetFieldInputValue"), array($documentType, $arFieldType, $arFieldName, $arRequest, &$arErrors));

			if (method_exists($entity, "SetGUIFieldEdit"))
				return call_user_func_array(array($entity, "SetGUIFieldEdit"), array($documentType, $arFieldName["Field"], $arRequest, &$arErrors, $arFieldType));
		}

		return CBPHelper::GetFieldInputValue($parameterDocumentType, $arFieldType, $arFieldName, $arRequest, $arErrors);
	}

	public function getFieldInputValuePrintable($parameterDocumentType, $fieldType, $fieldValue)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		$arFieldType = FieldType::normalizeProperty($fieldType);
		if ((string) $arFieldType["Type"] == "")
			return "";

		$fieldTypeObject = $this->getFieldTypeObject($parameterDocumentType, $arFieldType);
		if ($fieldTypeObject)
		{
			return $fieldTypeObject->formatValue($fieldValue, 'printable');
		}

		if (class_exists($entity))
		{
			if (method_exists($entity, "GetFieldInputValuePrintable"))
				return call_user_func_array(array($entity, "GetFieldInputValuePrintable"), array($documentType, $arFieldType, $fieldValue));

			if (method_exists($entity, "GetFieldValuePrintable"))
				return call_user_func_array(array($entity, "GetFieldValuePrintable"), array(null, "", $arFieldType["Type"], $fieldValue, $arFieldType));
		}

		return CBPHelper::GetFieldInputValuePrintable($parameterDocumentType, $arFieldType, $fieldValue);
	}

	public function getFieldValuePrintable($parameterDocumentId, $fieldName, $fieldType, $fieldValue, $arFieldType = null)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity, "GetFieldValuePrintable"))
			return call_user_func_array(array($entity, "GetFieldValuePrintable"), array($documentId, $fieldName, $fieldType, $fieldValue, $arFieldType));

		return CBPHelper::GetFieldValuePrintable($fieldName, $fieldType, $fieldValue, $arFieldType);
	}

	/**
	 * @param array $parameterDocumentType
	 * @return array
	 */
	public function getTypesMap(array $parameterDocumentType)
	{
		$k = implode('@', $parameterDocumentType);

		if (isset($this->typesMapCache[$k]))
		{
			return $this->typesMapCache[$k];
		}

		$result = FieldType::getBaseTypesMap();

		$documentFieldTypes = $this->GetDocumentFieldTypes($parameterDocumentType);
		foreach ($documentFieldTypes as $name => $field)
		{
			if (isset($field['typeClass']))
			{
				$result[mb_strtolower($name)] = $field['typeClass'];
			}
		}

		$this->typesMapCache[$k] = $result;

		return $result;
	}

	public function getTypesConversionMap(array $parameterDocumentType)
	{
		$typesMap = $this->getTypesMap($parameterDocumentType);
		$typesConversionMap = array();

		/** @var \Bitrix\Bizproc\BaseType\Base $typeClass */
		foreach ($typesMap as $documentTypeName => $typeClass)
		{
			if (!isset($typesConversionMap[$documentTypeName]))
				$typesConversionMap[$documentTypeName] = array();

			$typeMap = $typeClass::getConversionMap();
			if (!empty($typeMap[0]))
			{
				$typesConversionMap[$documentTypeName] = array_merge($typesConversionMap[$documentTypeName], $typeMap[0]);
			}

			if (!empty($typeMap[1]))
			{
				foreach ($typeMap[1] as $from)
				{
					if (!isset($typesConversionMap[$from]))
						$typesConversionMap[$from] = array();

					$typesConversionMap[$from][] = $documentTypeName;
				}
			}
		}

		return $typesConversionMap;
	}

	/**
	 * @param array $parameterDocumentType
	 * @param string $type
	 * @return null|string
	 */
	public function getTypeClass(array $parameterDocumentType, $type)
	{
		$typeClass = null;
		$map = $this->getTypesMap($parameterDocumentType);
		$type = mb_strtolower($type);
		if (isset($map[$type]))
			$typeClass = $map[$type];

		return $typeClass;
	}

	/**
	 * @param array $parameterDocumentType
	 * @param array $property
	 * @return null|FieldType
	 */
	public function getFieldTypeObject(array $parameterDocumentType, array $property)
	{
		$type = $property['Type'] ?? null;
		$typeClass = $this->getTypeClass($parameterDocumentType, $type);
		if ($typeClass && class_exists($typeClass))
		{
			return new FieldType($property, $parameterDocumentType, $typeClass);
		}
		return null;
	}

	/**
	 * @deprecated
	 * @param $parameterDocumentType
	 * @param $formName
	 * @param $fieldName
	 * @param $fieldValue
	 * @param array $arDocumentField
	 * @param bool $bAllowSelection
	 * @return mixed|string
	 * @throws CBPArgumentNullException
	 */
	public function getGUIFieldEdit($parameterDocumentType, $formName, $fieldName, $fieldValue, $arDocumentField = array(), $bAllowSelection = false)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (!is_array($arDocumentField) || count($arDocumentField) <= 0)
		{
			$arDocumentFields = $this->GetDocumentFields($parameterDocumentType);
			$arDocumentField = $arDocumentFields[$fieldName];
		}

		if (!array_key_exists("BP_AddShowParameterInit_".$moduleId."_".$entity."_".$documentType, $GLOBALS))
		{
			$GLOBALS["BP_AddShowParameterInit_".$moduleId."_".$entity."_".$documentType] = 1;
			CBPDocument::AddShowParameterInit($moduleId, "only_users", $documentType, $entity);
		}

		if (class_exists($entity) && method_exists($entity, "GetGUIFieldEdit"))
			return call_user_func_array(array($entity, "GetGUIFieldEdit"), array($documentType, $formName, $fieldName, $fieldValue, $arDocumentField, $bAllowSelection));

		return CBPHelper::GetGUIFieldEdit($parameterDocumentType, $formName, $fieldName, $fieldValue, $arDocumentField, $bAllowSelection);
	}

	/**
	 * @deprecated
	 * @param $parameterDocumentType
	 * @param $fieldName
	 * @param $arRequest
	 * @param $arErrors
	 * @param array $arDocumentField
	 * @return array|mixed|null
	 * @throws CBPArgumentNullException
	 */
	public function setGUIFieldEdit($parameterDocumentType, $fieldName, $arRequest, &$arErrors, $arDocumentField = array())
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (!is_array($arDocumentField) || count($arDocumentField) <= 0)
		{
			$arDocumentFields = $this->GetDocumentFields($parameterDocumentType);
			$arDocumentField = $arDocumentFields[$fieldName];
		}

		if (class_exists($entity) && method_exists($entity, "SetGUIFieldEdit"))
			return call_user_func_array(array($entity, "SetGUIFieldEdit"), array($documentType, $fieldName, $arRequest, &$arErrors, $arDocumentField));

		return CBPHelper::SetGUIFieldEdit($parameterDocumentType, $fieldName, $arRequest, $arErrors, $arDocumentField);
	}

	public function getDocumentAdminPage($parameterDocumentId)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity))
			return call_user_func_array(array($entity, "GetDocumentAdminPage"), array($documentId));

		return "";
	}

	public function getDocumentDetailUrl(array $parameterDocumentId, array $options = [])
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity))
		{
			if (method_exists($entity, 'getDocumentDetailUrl'))
			{
				return call_user_func_array(
					[$entity, 'getDocumentDetailUrl'],
					[[$moduleId, $entity, $documentId], $options]
				);
			}

			if (method_exists($entity, 'GetDocumentAdminPage'))
			{
				return call_user_func_array([$entity, 'GetDocumentAdminPage'], [$documentId]);
			}
		}

		return '';
	}

	public function getDocumentName($parameterDocumentId)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity, "getDocumentName"))
			return call_user_func_array(array($entity, "getDocumentName"), array($documentId));

		return "";
	}

	public function getDocumentCategories($parameterDocumentType)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity, 'getDocumentCategories'))
		{
			return call_user_func_array([$entity, 'getDocumentCategories'], [$documentType]);
		}

		return null;
	}

	public function getDocumentTypeName($parameterDocumentType)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity))
		{
			if (method_exists($entity, "getDocumentTypeName"))
				return call_user_func_array(array($entity, "getDocumentTypeName"), array($documentType));
			if (method_exists($entity, "getEntityName"))
				return call_user_func_array(array($entity, "getEntityName"), array($entity));
		}

		return null;
	}

	public function getDocumentTypeCaption($parameterDocumentType)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity))
		{
			if (method_exists($entity, 'getDocumentTypeCaption'))
			{
				return call_user_func_array([$entity, 'getDocumentTypeCaption'], [$documentType]);
			}
		}

		return $this->getDocumentTypeName($parameterDocumentType);
	}

	public function getDocumentIcon($parameterDocumentId)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity, 'getDocumentIcon'))
			return call_user_func_array(array($entity, 'getDocumentIcon'), array($documentId));

		return null;
	}

	public function getDocumentResponsible(array $parameterDocumentId)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity, 'getDocumentResponsible'))
		{
			return call_user_func_array([$entity, 'getDocumentResponsible'], [$documentId]);
		}

		return null;

	}

	public function getDocumentForHistory($parameterDocumentId, $historyIndex)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity))
			return call_user_func_array(array($entity, "GetDocumentForHistory"), array($documentId, $historyIndex));

		return null;
	}

	public function recoverDocumentFromHistory($parameterDocumentId, $arDocument)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity))
			return call_user_func_array(array($entity, "RecoverDocumentFromHistory"), array($documentId, $arDocument));

		return false;
	}

	public function getUsersFromUserGroup($group, $parameterDocumentId)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity))
			return call_user_func_array(array($entity, "GetUsersFromUserGroup"), array($group, $documentId));

		return array();
	}

	public function getAllowableUserGroups($parameterDocumentType, $withExtended = false)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity))
		{
			$result = call_user_func_array(array($entity, "GetAllowableUserGroups"), array($documentType, $withExtended));
			$result1 = array();
			foreach ($result as $key => $value)
				$result1[mb_strtolower($key)] = $value;
			return $result1;
		}

		return array();
	}

	public function getAllowableOperations($parameterDocumentType)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity))
			return call_user_func_array(array($entity, "GetAllowableOperations"), array($documentType));

		return array();
	}

	public function setPermissions($parameterDocumentId, $workflowId, $arPermissions, $bRewrite = true)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity, "SetPermissions"))
			return call_user_func_array(array($entity, "SetPermissions"), array($documentId, $workflowId, $arPermissions, $bRewrite));

		return false;
	}

	public function isFeatureEnabled($parameterDocumentType, $feature)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity, 'isFeatureEnabled'))
			return call_user_func_array(array($entity, 'isFeatureEnabled'), array($documentType, $feature));

		return false;
	}

	public function isExtendedPermsSupported($parameterDocumentType)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity, "isExtendedPermsSupported"))
			return call_user_func_array(array($entity, "isExtendedPermsSupported"), array($documentType));

		return false;
	}

	public function toInternalOperations($parameterDocumentType, $permissions)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity, "toInternalOperations"))
			return call_user_func_array(array($entity, "toInternalOperations"), array($documentType, $permissions));

		return $permissions;
	}

	public function toExternalOperations($parameterDocumentType, $permissions)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity, "toExternalOperations"))
			return call_user_func_array(array($entity, "toExternalOperations"), array($documentType, $permissions));

		return $permissions;
	}

	public function onTaskChange($parameterDocumentId, $taskId, $taskData, $status)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity, "onTaskChange"))
			return call_user_func_array(array($entity, "onTaskChange"), array($documentId, $taskId, $taskData, $status));

		return false;
	}

	public function onWorkflowStatusChange($parameterDocumentId, $workflowId, $status, $rootActivity = null)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		if (
			$rootActivity
			&& $rootActivity->workflow->isNew()
			&& $status === CBPWorkflowStatus::Running
		)
		{
			$this->clearCache();
		}

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity, "onWorkflowStatusChange"))
			return call_user_func_array(array($entity, "onWorkflowStatusChange"), array($documentId, $workflowId, $status, $rootActivity));

		return false;
	}

	public function onDebugSessionDocumentStatusChanged(array $parameterDocumentId, int $userId, string $status)
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($parameterDocumentId);

		if (!\Bitrix\Bizproc\Debugger\Session\DocumentStatus::isStatus($status) || $userId <= 0)
		{
			return;
		}

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity,'onDebugSessionDocumentStatusChanged'))
		{
			return call_user_func_array([$entity, 'onDebugSessionDocumentStatusChanged'], [$documentId, $userId, $status]);
		}

		return;
	}

	public function createAutomationTarget($parameterDocumentType)
	{
		[$moduleId, $entity, $documentType] = CBPHelper::ParseDocumentId($parameterDocumentType);

		if ($moduleId)
		{
			CModule::IncludeModule($moduleId);
		}

		if (class_exists($entity) && method_exists($entity, "createAutomationTarget"))
		{
			/** @var \Bitrix\Bizproc\Automation\Target\BaseTarget $target */
			$target = call_user_func_array([$entity, "createAutomationTarget"], [$documentType]);

			return $target;
		}

		return null;
	}

	private function clearCache()
	{
		$this->arDocumentsCache = [];
	}

	private function checkCache()
	{
		$state = \CTimeZone::Enabled();
		if ($this->tzFlag !== null && $this->tzFlag !== $state)
		{
			$this->clearCache();
		}
		$this->tzFlag = $state;
	}
}
