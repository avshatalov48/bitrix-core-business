<?php

namespace Bitrix\Main\UI;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Uploader\Uploader;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

class FileInput
{
	public const UPLOAD_IMAGES = 'I';
	public const UPLOAD_EXTENTION_LIST = 'F';
	public const UPLOAD_ANY_FILES = 'A';

	protected $elementSetts = array(
		"name" => "FILE[n#IND#]",
		"description" => true,
		"delete" => true,
		"edit" => true,
		"thumbSize" => 640
	);
	protected $uploadSetts = array(
		"upload" => false,
		"uploadType" => "path",
		"medialib" => false,
		"fileDialog" => false,
		"cloud" => false,
		"maxCount" => 0,
		"maxSize" => 0
	);
	protected $id = "bx_iblockfileprop";
	protected $files = array();
	protected static $instance = null;
	protected $templates = array();

	public static $templatePatterns = array(
		'description' => <<<HTML
		<input type="text" id="#id#Description" name="#description_name#" value="#description#" class="adm-fileinput-item-description" />
HTML
		,
		'regularInput' => '<input class="bx-bxu-fileinput-value" type="hidden" id="#id#Value" name="#input_name#" value="#input_value#" />',
		'arrayInput' => <<<HTML
		<input type="hidden" id="#id#Value" name="#input_name#[tmp_name]" value="#input_value#" />
		<input type="hidden" name="#input_name#[type]" value="#type#" />
		<input type="hidden" name="#input_name#[name]" value="#name#" />
		<input type="hidden" name="#input_name#[size]" value="#size#" />
		<input type="hidden" name="#input_name#[error]" value="0" />
HTML
	,
		'new' => <<<HTML
	<div class="adm-fileinput-item">
		<div class="adm-fileinput-item-preview">
			<span class="adm-fileinput-item-loading">
				<span class="container-loading-title">#MESS_LOADING#</span>
				<span class="container-loading-bg"><span class="container-loading-bg-progress" style="width: 5%;" id="#id#Progress"></span></span>
			</span>
			<div class="adm-fileinput-item-preview-icon">
				<div class="bx-file-icon-container-medium icon-#ext#">
					<div class="bx-file-icon-cover">
						<div class="bx-file-icon-corner">
							<div class="bx-file-icon-corner-fix"></div>
						</div>
						<div class="bx-file-icon-images"></div>
					</div>
					<div class="bx-file-icon-label"></div>
				</div>
				<span class="container-doc-title" id="#id#Name">#name#</span>
			</div>
			<div class="adm-fileinput-item-preview-img">#preview#</div>
			<input class="bx-bxu-fileinput-value" type="hidden" id="#id#Value" name="#input_name#" value="#input_value#" />
		</div>
		#description#
		<div class="adm-fileinput-item-panel">
			<span class="adm-fileinput-item-panel-btn adm-btn-setting" id="#id#Edit">&nbsp;</span>
			<span class="adm-fileinput-item-panel-btn adm-btn-del" id="#id#Del">&nbsp;</span>
		</div>
		<div id="#id#Properties" class="adm-fileinput-item-properties">#properties#</div>
	</div>
HTML
		,
		'unsaved' => <<<HTML
<div class="adm-fileinput-item-wrapper" id="#id#Block">
	<div class="adm-fileinput-item">
		<div class="adm-fileinput-item-preview">
			<span class="adm-fileinput-item-loading">
				<span class="container-loading-title">#MESS_LOADING#</span>
				<span class="container-loading-bg"><span class="container-loading-bg-progress" style="width: 60%;" id="#id#Progress"></span></span>
			</span>
			<div class="adm-fileinput-item-preview-icon">
				<div class="bx-file-icon-container-medium icon-#ext#">
					<div class="bx-file-icon-cover">
						<div class="bx-file-icon-corner">
							<div class="bx-file-icon-corner-fix"></div>
						</div>
						<div class="bx-file-icon-images"></div>
					</div>
					<div class="bx-file-icon-label"></div>
				</div>
				<span class="container-doc-title" id="#id#Name">#name#</span>
			</div>
			<div class="adm-fileinput-item-preview-img" id="#id#Canvas"></div>
			#input#
		</div>
		#description#
		<div class="adm-fileinput-item-panel">
			<span class="adm-fileinput-item-panel-btn adm-btn-setting" id="#id#Edit">&nbsp;</span>
			<span class="adm-fileinput-item-panel-btn adm-btn-del" id="#id#Del">&nbsp;</span>
		</div>
		<div id="#id#Properties" class="adm-fileinput-item-properties">#properties#</div>
	</div>
</div>
HTML
	,
		/**
		 * adm-fileinput-item-saved - saved
		 * adm-fileinput-item-error - error
		 * adm-fileinput-item-image - file is image
		 *
		 */
		'uploaded' => <<<HTML
<div class="adm-fileinput-item-wrapper" id="#id#Block">
	<div class="adm-fileinput-item adm-fileinput-item-saved">
		<div class="adm-fileinput-item-preview">
			<span class="adm-fileinput-item-loading">
				<span class="container-loading-title">#MESS_LOADING#</span>
				<span class="container-loading-bg"><span class="container-loading-bg-progress" style="width: 60%;"></span></span>
			</span>
			<div class="adm-fileinput-item-preview-icon">
				<div class="bx-file-icon-container-medium icon-#ext#">
					<div class="bx-file-icon-cover">
						<div class="bx-file-icon-corner">
							<div class="bx-file-icon-corner-fix"></div>
						</div>
						<div class="bx-file-icon-images"></div>
					</div>
					<div class="bx-file-icon-label"></div>
				</div>
				<span class="container-doc-title" id="#id#Name">#name#</span>
			</div>
			<div class="adm-fileinput-item-preview-img" id="#id#Canvas"></div>
			<input style="display: none;" type="hidden" id="#id#Value" readonly="readonly" name="#input_name#" value="#input_value#" />
		</div>
		#description#
		<div class="adm-fileinput-item-panel">
			<span class="adm-fileinput-item-panel-btn adm-btn-setting" id="#id#Edit">&nbsp;</span>
			<span class="adm-fileinput-item-panel-btn adm-btn-del" id="#id#Del">&nbsp;</span>
		</div>
		<div id="#id#Properties" class="adm-fileinput-item-properties">#properties#</div>
	</div>
</div>
HTML
		,
		'unexisted' => <<<HTML
<div class="adm-fileinput-item-wrapper" id="#id#Block">
	<div class="adm-fileinput-item adm-fileinput-item-saved">
		<div class="adm-fileinput-item-preview">
			<span class="adm-fileinput-item-loading">
				<span class="container-loading-title">#MESS_LOADING#</span>
				<span class="container-loading-bg"><span class="container-loading-bg-progress" style="width: 60%;"></span></span>
			</span>
			<div class="adm-fileinput-item-preview-icon">
				<div class="bx-file-icon-container-medium icon-#ext#">
					<div class="bx-file-icon-cover">
						<div class="bx-file-icon-corner">
							<div class="bx-file-icon-corner-fix"></div>
						</div>
						<div class="bx-file-icon-images"></div>
					</div>
					<div class="bx-file-icon-label"></div>
				</div>
				<span class="container-doc-title" id="#id#Name">#name#</span>
			</div>
			<div class="adm-fileinput-item-preview-img" id="#id#Canvas"></div>
			<input style="display: none;" data-fileinput="Y" type="file" id="#id#Value" readonly="readonly" name="#input_name#" value="" />
		</div>
		#description#
		<div class="adm-fileinput-item-panel">
			<span class="adm-fileinput-item-panel-btn adm-btn-del" id="#id#Del">&nbsp;</span>
		</div>
		<div id="#id#Properties" class="adm-fileinput-item-properties">#properties#</div>
	</div>
</div>
HTML
);
	/**
	 * @param array $params
	 */
	public function __construct($params = array())
	{
		global $USER;
		$inputs = array_merge($this->elementSetts, $params);
		$this->elementSetts = array(
			"name" => $inputs["name"],
			"description" => !empty($inputs["description"]),
			"delete" => $inputs['delete'] !== false,
			"edit" => $inputs['edit'] !== false,
			"thumbSize" => 640,
			//"properties" => (is_array($inputs) ? $inputs : array()) //TODO It is needed to deal with additional properties
		);
		if (isset($params['id']))
			$this->elementSetts['id'] = $params['id'];
		$replace = array(
			"/\\#MESS_LOADING\\#/" => Loc::getMessage("BXU_LoadingProcess"),
			"/\\#description\\#/" => ($this->elementSetts["edit"] == true && $this->elementSetts["description"] == true ? self::$templatePatterns["description"] : ""),
			"/\\#properties\\#/" => "",
			"/[\n\t]+/" => ""
		);
		$this->templates["uploaded"] = preg_replace(array_keys($replace), array_values($replace), self::$templatePatterns["uploaded"]);
		$this->templates["unexisted"] = preg_replace(array_keys($replace), array_values($replace), self::$templatePatterns["unexisted"]);
		$this->templates["new"] = preg_replace(array_keys($replace), array_values($replace), self::$templatePatterns["new"]);
		$this->templates["unsaved"] = preg_replace(array_keys($replace), array_values($replace), self::$templatePatterns["unsaved"]);
		$replace = array(
			"#input_name#" => $inputs["name"],
			"#input_value#" => "",
			"#description_name#" => self::getInputName($inputs["name"], "_descr")
		);
		$this->templates["new"] = str_replace(array_keys($replace), array_values($replace), $this->templates["new"]);

		$this->templates["unsavedArray"] = str_replace("#input#", self::$templatePatterns["arrayInput"], $this->templates["unsaved"]);
		$this->templates["unsaved"] = str_replace("#input#", self::$templatePatterns["regularInput"], $this->templates["unsaved"]);

		$inputs = array_merge($this->uploadSetts, $params);

		$this->uploadSetts = array(
			"upload" => '',
			"uploadType" => "path",
			"medialib" =>
				isset($inputs['medialib'])
				&& $inputs['medialib'] === true
				&& \COption::GetOptionString('fileman', "use_medialib", "Y") != "N"
			,
			"fileDialog" =>
				(isset($inputs['file_dialog']) && $inputs['file_dialog'] === true)
				|| (isset($inputs['fileDialog']) && $inputs['fileDialog'] === true)
			,
			"cloud" =>
				isset($inputs['cloud'])
				&& $inputs['cloud'] === true
				&& $USER->CanDoOperation("clouds_browse")
				&& \CModule::IncludeModule("clouds")
				&& \CCloudStorage::HasActiveBuckets()
			,
			"maxCount" => isset($params["maxCount"]) && $params["maxCount"] > 0 ? $params["maxCount"] : 0,
			"maxSize" => isset($params["maxSize"]) && $params["maxSize"] > 0 ? $params["maxSize"] : 0,
			"allowUpload" => $params["allowUpload"] ?? self::UPLOAD_ANY_FILES,
			"allowUploadExt" => trim($params["allowUploadExt"] ?? ''),
			"allowSort" => isset($params["allowSort"]) && $params["allowSort"] === "N" ? "N" : "Y",
		);
		if (!in_array(
			$this->uploadSetts["allowUpload"],
			[
				self::UPLOAD_ANY_FILES,
				self::UPLOAD_IMAGES,
				self::UPLOAD_EXTENTION_LIST,
			]
		))
		{
			$this->uploadSetts["allowUpload"] = self::UPLOAD_ANY_FILES;
		}
		if ($this->uploadSetts["medialib"] === true)
			$this->uploadSetts["medialib"] = (\Bitrix\Main\Loader::includeModule("fileman") && \CMedialib::CanDoOperation('medialib_view_collection', 0));
		if($this->uploadSetts["fileDialog"] === true && !$USER->CanDoOperation('fileman_view_file_structure'))
			$this->uploadSetts["fileDialog"] = false;

		if (empty($this->uploadSetts["allowUploadExt"]) && $this->uploadSetts["allowUpload"] === self::UPLOAD_EXTENTION_LIST)
			$this->uploadSetts["allowUpload"] = self::UPLOAD_ANY_FILES;
		if (isset($this->elementSetts["id"]))
			$this->id = 'bx_file_'.mb_strtolower(preg_replace("/[^a-z0-9]/i", "_", $this->elementSetts["id"]));
		else
			$this->id = 'bx_file_'.mb_strtolower(preg_replace("/[^a-z0-9]/i", "_", $this->elementSetts["name"]));

		if ($inputs['upload'] === true)
		{
			$this->uploadSetts['upload'] = FileInputReceiver::sign(array(
				"id" => ($inputs['uploadType'] === "hash" ? "hash" : "path"),
				"allowUpload" => $this->uploadSetts["allowUpload"],
				"allowUploadExt" => $this->uploadSetts["allowUploadExt"]
			));
			$this->uploadSetts['uploadType'] = (in_array($inputs["uploadType"], array(/*"file",*/ "hash", "path")) ? $inputs["uploadType"] : "path");
		}
		self::$instance = $this;
	}

	/**
	 * @param array $params
	 * @param bool $hashIsID
	 * @return FileInput
	 */
	public static function createInstance($params = array(), $hashIsID = true)
	{
		$c = __CLASS__;
		return new $c($params, $hashIsID);
	}

	/**
	 * @param array $values
	 * @return string
	 */
	public function show($values = array(), $getDataFromRequest = false)
	{
		\CJSCore::Init(array('fileinput'));

		$files = '';
		if (!is_array($values) || array_key_exists("tmp_name", $values))
		{
			$values = array($this->elementSetts["name"] => $values);
		}
		$maxIndex = 0;
		$pattMaxIndex = mb_strpos($this->elementSetts["name"], "#IND#") > 0 ? str_replace("#IND#", "(\\d+)", preg_quote($this->elementSetts["name"])) : null;
		foreach($values as $inputName => $fileId)
		{
			if ($pattMaxIndex && preg_match("/".$pattMaxIndex."/", $inputName, $matches))
			{
				$maxIndex = max($maxIndex, intval($matches[1]));
			}
			if ($res = $this->getFile($fileId, $inputName, $getDataFromRequest))
			{
				$t = (isset($res["fileId"]) && $res["fileId"] > 0 ? $this->templates["uploaded"] : (is_array($fileId) ? $this->templates["unsavedArray"] : $this->templates["unsaved"]));
				if (!is_array($res))
				{
					$res = $this->formFile($fileId, $inputName);
					$t = $this->templates["unexisted"];
				}
				$patt = array();
				foreach ($res as $pat => $rep)
				{
					$patt["#".$pat."#"] = htmlspecialcharsbx($rep);
				}
				if (array_key_exists("#description#", $patt) && str_contains($patt["#description#"], "&amp;quot;"))
				{
					$patt["#description#"] = str_replace("&amp;quot;", "&quot;", $patt["#description#"]);
				}
				$files .= str_ireplace(array_keys($patt), array_values($patt), $t);
				$this->files[] = $res;
			}
		}

		$canDelete = true ? '' : 'adm-fileinput-non-delete'; // In case we can not delete files
		$canEdit = ($this->elementSetts["edit"] ? '' : 'adm-fileinput-non-edit');

		$settings = \CUserOptions::GetOption('main', 'fileinput');
		$settings = (is_array($settings) ? $settings : array(
			"frameFiles" => "N",
			"pinDescription" => "N",
			"mode" => "mode-pict",
			"presets" => array(
				array("width" => 200, "height" => 200, "title" => "200x200")
			),
			"presetActive" => 0
		));

		if ($this->uploadSetts["maxCount"] == 1)
		{
			if ($this->uploadSetts["allowUpload"] === self::UPLOAD_IMAGES)
				$hintMessage = Loc::getMessage("BXU_DNDMessage01");
			else if ($this->uploadSetts["allowUpload"] === self::UPLOAD_EXTENTION_LIST)
				$hintMessage = Loc::getMessage("BXU_DNDMessage02", array("#ext#" => htmlspecialcharsbx($this->uploadSetts["allowUploadExt"])));
			else
				$hintMessage = Loc::getMessage("BXU_DNDMessage03");

			if ($this->uploadSetts["maxSize"] > 0)
				$hintMessage .= Loc::getMessage("BXU_DNDMessage04", array("#size#" => \CFile::FormatSize($this->uploadSetts["maxSize"])));
		}
		else
		{
			$maxCount = ($this->uploadSetts["maxCount"] > 0 ? GetMessage("BXU_DNDMessage5", array("#maxCount#" => htmlspecialcharsbx($this->uploadSetts["maxCount"]))) : "");
			if ($this->uploadSetts["allowUpload"] === self::UPLOAD_IMAGES)
				$hintMessage = Loc::getMessage("BXU_DNDMessage1", array("#maxCount#" => $maxCount));
			else if ($this->uploadSetts["allowUpload"] == self::UPLOAD_EXTENTION_LIST)
				$hintMessage = Loc::getMessage("BXU_DNDMessage2", array("#ext#" => htmlspecialcharsbx($this->uploadSetts["allowUploadExt"]), "#maxCount#" => $maxCount));
			else
				$hintMessage = Loc::getMessage("BXU_DNDMessage3", array("#maxCount#" => $maxCount));
			if ($this->uploadSetts["maxSize"] > 0)
				$hintMessage .= Loc::getMessage("BXU_DNDMessage4", array("#size#" => \CFile::FormatSize($this->uploadSetts["maxSize"])));
		}

		$this->getExtDialogs();

		$uploadSetts = $this->uploadSetts + $settings;
		if (array_key_exists("presets", $settings))
		{
			$uploadSetts["presets"] = $settings["presets"];
			$uploadSetts["presetActive"] = $settings["presetActive"];
		}
		$uploadSetts["maxIndex"] = $maxIndex;
		$template = \CUtil::JSEscape($this->templates["new"]);
		$classSingle = (array_key_exists("maxCount", $uploadSetts) && intval($uploadSetts["maxCount"]) == 1 ? "adm-fileinput-wrapper-single" : "");
		$uploadSetts = Json::encode($uploadSetts);
		$elementSetts = Json::encode($this->elementSetts);
		$values = Json::encode($this->files);
		$mes = array(
			"preview" => GetMessage("BXU_Preview"),
			"nonPreview" => GetMessage("BXU_NonPreview")
		);

		$settings["modePin"] = (isset($settings["pinDescription"]) && $settings["pinDescription"] == "Y" && $this->elementSetts["description"] ? "mode-with-description" : "");
		$t = <<<HTML
<div class="adm-fileinput-wrapper {$classSingle}">
<div class="adm-fileinput-btn-panel">
	<span class="adm-btn add-file-popup-btn" id="{$this->id}_add"></span>
	<div class="adm-fileinput-mode {$settings["mode"]}" id="{$this->id}_mode">
		<a href="#" class="mode-pict" id="{$this->id}ThumbModePreview" title="{$mes["preview"]}"></a>
		<a href="#" class="mode-file" id="{$this->id}ThumbModeNonPreview" title="{$mes["nonPreview"]}"></a>
	</div>
</div>
<div id="{$this->id}_block" class="adm-fileinput-area {$canDelete} {$canEdit} {$settings['mode']} {$settings["modePin"]}">
	<div class="adm-fileinput-area-container" id="{$this->id}_container">{$files}</div>
	<span class="adm-fileinput-drag-area-hint" id="{$this->id}Notice">{$hintMessage}</span>
<script>
(function(BX)
{
	if (BX)
	{
		BX.ready(BX.defer(function(){
			new BX.UI.FileInput('{$this->id}', {$uploadSetts}, {$elementSetts}, {$values}, '{$template}');
		}));
	}
})(window["BX"] || top["BX"]);
</script>
</div>
</div>
HTML;
		return $t;
	}
	private function getExtDialogs()
	{
		if ($this->uploadSetts["medialib"] && Loader::includeModule("fileman"))
		{
			$this->uploadSetts["medialib"] = array(
				"click" => "OpenMedialibDialog".$this->id,
				"handler" => "SetValueFromMedialib".$this->id
			);
			\CMedialib::ShowDialogScript(array(
				"event" => $this->uploadSetts["medialib"]["click"],
				"arResultDest" => array(
					"FUNCTION_NAME" => $this->uploadSetts["medialib"]["handler"]
				)
			));
		}
		if ($this->uploadSetts["fileDialog"])
		{
			$this->uploadSetts["fileDialog"] = array(
				"click" => "OpenFileDialog".$this->id,
				"handler" => "SetValueFromFileDialog".$this->id
			);
			\CAdminFileDialog::ShowScript
			(
				Array(
					"event" => $this->uploadSetts["fileDialog"]["click"],
					"arResultDest" => array("FUNCTION_NAME" => $this->uploadSetts["fileDialog"]["handler"]),
					"arPath" => array("SITE" => SITE_ID, "PATH" =>"/upload"),
					"select" => 'F',// F - file only, D - folder only
					"operation" => 'O',
					"showUploadTab" => true,
					"allowAllFiles" => true,
					"SaveConfig" => true,
				)
			);
		}
	}
	private function formFile($fileId = "", $inputName = "file")
	{
		$result = array(
			'id' => $fileId,
			'name' => 'Unknown',
			'description_name' => self::getInputName($inputName, "_descr"),
			'description' => '',
			'size' => 0,
			'type' => 'unknown',
			'input_name' => $inputName,
			'input_value' => $fileId,
			'entity' => "file",
			'ext' => ''
		);
		if (!empty($this->elementSetts["properties"]))
		{
			foreach ($this->elementSetts["properties"] as $key)
			{
				$result[$key."_name"] = self::getInputName($inputName, "_".$key);
				$result[$key] = "";
			}
		}
		return $result;
	}
	private function getFile($fileId = "", $inputName = "file", $getDataFromRequest = false)
	{
		$result = null;
		$properties = array();
		if (is_array($fileId) && array_key_exists("ID", $fileId))
		{
			$properties = $fileId;
			unset($properties["ID"]);
			$fileId = $fileId["ID"];
		}

		if ($fileId > 0 && ($ar = \CFile::GetFileArray($fileId)) && is_array($ar))
		{
			$name = ($ar['ORIGINAL_NAME'] <> ''?$ar['ORIGINAL_NAME']:$ar['FILE_NAME']);
			$result = array(
				'fileId' => $fileId,
				'id' => $this->id.'_'.$fileId,
				'name' => $name,
				'description_name' => self::getInputName($inputName, "_descr"),
				'description' => str_replace('"', "&quot;", $ar['DESCRIPTION']),
				'size' => $ar['FILE_SIZE'],
				'type' => $ar['CONTENT_TYPE'],
				'input_name' => $inputName,
				'input_value' => $fileId,
				'entity' => (($ar["WIDTH"] > 0 && $ar["HEIGHT"] > 0) ? "image" : "file"),
				'ext' => GetFileExtension($name),
				'real_url' => $ar['SRC']
			);
			if ($result['entity'] == "image")
			{
				$result['tmp_url'] = FileInputUnclouder::getSrc($ar);
				$result['preview_url'] = FileInputUnclouder::getSrcWithResize($ar, array('width' => 200, 'height' => 200));
				$result['width'] = $ar["WIDTH"];
				$result['height'] = $ar["HEIGHT"];
			}
		}
		else
		{
			$file = null;
			if (is_array($fileId) && array_key_exists("tmp_name", $fileId))
				$file = array(
					"tmp_name" => $fileId["tmp_name"],
					"type" => (array_key_exists("type", $fileId) ? $fileId["type"] : null),
					"name" => (array_key_exists("name", $fileId) ? $fileId["name"] : null),
					"description" => (array_key_exists("description", $fileId) ? $fileId["description"] : null)
				);
			else if (is_string($fileId))
				$file = array(
					"tmp_name" => $fileId,
					"type" => null,
					"name" => null,
					"description" => null
				);
			if (is_array($file) && ($paths = Uploader::getPaths($file["tmp_name"])) &&
				($flTmp = \CBXVirtualIo::GetInstance()->GetFile($paths["tmp_name"])) && $flTmp->IsExists())
			{
				$name = is_string($file["name"]) && $file["name"] <> '' ? $file["name"] : $flTmp->getName();
				$result = array(
					'id' => md5($file["tmp_name"]),
					'name' => $name,
					'description_name' => self::getInputName($inputName, "_descr"),
					'description' => is_string($file["description"]) && $file["description"] <> '' ? $file["description"] : "",
					'size' => $flTmp->GetFileSize(),
					'type' => is_string($file["type"]) && $file["type"] <> '' ? $file["type"] : $flTmp->getType(),
					'input_name' => $inputName,
					'input_value' => $file["tmp_name"],
					'entity' => "file",
					'ext' => GetFileExtension($name),
					'real_url' => $paths["tmp_url"]
				);

				$info = (new \Bitrix\Main\File\Image($paths["tmp_name"]))->getInfo();
				if ($info)
				{
					$result['entity'] = "image";
					$result['tmp_url'] = $paths["tmp_url"];
					if (($mime = $info->getMime()) <> '')
						$result['type'] = $mime;
					$result['width'] = $info->getWidth();
					$result['height'] = $info->getHeight();
				}
			}
		}
		if (is_array($result) && !empty($this->elementSetts["properties"]))
		{
			$request = null;
			if ($getDataFromRequest === true)
			{
				$request = \Bitrix\Main\Context::getCurrent()->getRequest();
				$result["description"] = $request->isPost() ? $request->getPost($result["description_name"]) : $request->getQuery($result["description_name"]);
			}
			foreach ($this->elementSetts["properties"] as $key)
			{
				$result[$key."_name"] = self::getInputName($inputName, "_".$key);
				$result[$key] = (is_null($request) ? $properties[$key] : ($request->isPost() ? $request->getPost($result[$key."_name"]) : $request->getQuery($result[$key."_name"])));
			}
		}
		return $result;
	}

	private static function getInputName($inputName, $type = "")
	{
		if ($type == "")
			return $inputName;
		$p = mb_strpos($inputName, "[");
		return  ($p > 0) ? mb_substr($inputName, 0, $p).$type.mb_substr($inputName, $p) : $inputName.$type;
	}

	/**
	 * Prepares file array for saving. It is important to use if BX_TEMPORARY_FILES_DIRECTORY is defined.
	 * @param $file
	 * @return array|null
	 */
	public static function prepareFile($file)
	{
		$return = null;
		if (is_array($file) && isset($file["tmp_name"]) && !empty($file["tmp_name"]))
		{

		}
		return $file;
	}
}
?>