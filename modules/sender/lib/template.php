<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender;

use Bitrix\Main\DB;
use Bitrix\Main\ORM;
use Bitrix\Main\Type as MainType;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Fileman\Block\Editor as BlockEditor;
use Bitrix\Fileman\Block\EditorMail as BlockEditorMail;

Loc::loadMessages(__FILE__);

/**
 * Class TemplateTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Template_Query query()
 * @method static EO_Template_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Template_Result getById($id)
 * @method static EO_Template_Result getList(array $parameters = array())
 * @method static EO_Template_Entity getEntity()
 * @method static \Bitrix\Sender\EO_Template createObject($setDefaultValues = true)
 * @method static \Bitrix\Sender\EO_Template_Collection createCollection()
 * @method static \Bitrix\Sender\EO_Template wakeUpObject($row)
 * @method static \Bitrix\Sender\EO_Template_Collection wakeUpCollection($rows)
 */
class TemplateTable extends ORM\Data\DataManager
{
	const LOCAL_DIR_IMG = '/images/sender/preset/template/';

	/**
	 * Handler of event that return array of templates
	 *
	 * @param string|null $templateType
	 * @param string|null $templateId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function onPresetTemplateList($templateType = null, $templateId = null)
	{
		$resultList = array();
		if($templateType && $templateType !== 'USER')
		{
			return $resultList;
		}

		$localPathOfIcon = static::LOCAL_DIR_IMG . 'my.png';
		//$fullPathOfIcon = Loader::getLocal($localPathOfIcon);

		// return only active templates, but if requested template by id return any
		$filter = array();
		if($templateId)
		{
			$filter['ID'] = $templateId;
		}
		else
		{
			$filter['ACTIVE'] = 'Y';
		}

		$templateDb = static::getList(array('filter' => $filter, 'order' => array('ID' => 'DESC')));
		while($template = $templateDb->fetch())
		{
			$resultList[] = array(
				'TYPE' => 'USER',
				'ID' => $template['ID'],
				'NAME' => $template['NAME'],
				'ICON' => '',//(!empty($fullPathOfIcon) ? '/bitrix'.$localPathOfIcon : ''),
				'FIELDS' => array(
					'MESSAGE' => array(
						'CODE' => 'MESSAGE',
						'VALUE' => Security\Sanitizer::fixTemplateStyles($template['CONTENT']),
						'ON_DEMAND' => static::isContentForBlockEditor($template['CONTENT'])
					),
					'SUBJECT' => array(
						'CODE' => 'SUBJECT',
						'VALUE' => $template['NAME'],
					),
				)
			);
		}

		return $resultList;
	}

	/**
	 * Increment use counter.
	 *
	 * @return bool
	 */
	public static function incUseCount($id)
	{
 		return static::update($id, array(
			'USE_COUNT' => new DB\SqlExpression('?# + 1', 'USE_COUNT'),
			'DATE_USE' => new MainType\DateTime()
		))->isSuccess();
	}

	/**
	 * Get table name
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_preset_template';
	}

	/**
	 * Return the map
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'autocomplete' => true,
				'primary' => true,
			),
			'ACTIVE' => array(
				'data_type' => 'string',
				'required' => true,
				'default_value' => 'Y',
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('SENDER_ENTITY_TEMPLATE_FIELD_TITLE_NAME')
			),
			'CONTENT' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('SENDER_ENTITY_TEMPLATE_FIELD_TITLE_CONTENT'),
				'save_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getSaveModificator'),
				'fetch_data_modification' => array('\Bitrix\Main\Text\Emoji', 'getFetchModificator'),
			),
			'USE_COUNT' => array(
				'data_type' => 'integer',
				'default_value' => 0,
				'required' => true,
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => new MainType\DateTime(),
			),
			'DATE_USE' => array(
				'data_type' => 'datetime',
			),
		);
	}

	/**
	 * @param ORM\Event $event
	 * @return ORM\EventResult
	 */
	public static function onBeforeAdd(ORM\Event $event)
	{
		$result = new ORM\EventResult;
		$data = $event->getParameters();
		$data['fields']['CONTENT'] = Security\Sanitizer::fixTemplateStyles($data['fields']['CONTENT']);
		$result->modifyFields($data['fields']);

		return $result;
	}

	public static function onAfterAdd(ORM\Event $event)
	{
		$result = new ORM\EventResult;
		$data = $event->getParameters();
		if (isset($data['fields']['CONTENT']))
		{
			\Bitrix\Sender\FileTable::syncFiles($data['primary']['ID'], 1, $data['fields']['CONTENT']);
		}

		return $result;
	}

	/**
	 * Handler of before delete event.
	 *
	 * @param ORM\Event $event Event.
	 * @return ORM\EventResult
	 */
	public static function onBeforeUpdate(ORM\Event $event)
	{
		$result = new ORM\EventResult;

		$data = $event->getParameters();
		if (array_key_exists('CONTENT', $data['fields']))
		{
			$data['fields']['CONTENT'] = Security\Sanitizer::fixTemplateStyles($data['fields']['CONTENT']);
			$result->modifyFields($data['fields']);
			\Bitrix\Sender\FileTable::syncFiles($data['primary']['ID'], 1, $data['fields']['CONTENT']);
		}

		return $result;
	}
	
	/**
	 * Handler of before delete event.
	 * 
	 * @param ORM\Event $event Event.
	 * @return ORM\EventResult
	 */
	public static function onBeforeDelete(ORM\Event $event)
	{
		$result = new ORM\EventResult;
		$data = $event->getParameters();
		$chainListDb = MailingChainTable::getList(array(
			'select' => array('ID', 'SUBJECT', 'MAILING_ID', 'MAILING_NAME' => 'TITLE'),
			'filter' => array('TEMPLATE_TYPE' => 'USER', 'TEMPLATE_ID' => $data['primary']['ID']),
			'order' => array('MAILING_NAME' => 'ASC', 'ID')
		));

		if($chainListDb->getSelectedRowsCount() > 0)
		{
			$template = static::getRowById($data['primary']['ID']);
			$messageList = array();
			while($chain = $chainListDb->fetch())
			{
				$messageList[$chain['MAILING_NAME']] = '[' . $chain['ID'] . '] ' . htmlspecialcharsbx($chain['SUBJECT']) . "\n";
			}

			$message = Loc::getMessage('SENDER_ENTITY_TEMPLATE_DELETE_ERROR_TEMPLATE', array('#NAME#' => $template['NAME'])) . "\n";
			foreach($messageList as $mailingName => $messageItem)
			{
				$message .= Loc::getMessage('SENDER_ENTITY_TEMPLATE_DELETE_ERROR_MAILING', array('#NAME#' => $mailingName)) . "\n" . $messageItem . "\n";
			}

			$result->addError(new ORM\EntityError($message));
		}

		if (!$result->getErrors())
		{
			\Bitrix\Sender\FileTable::syncFiles($data['primary']['ID'], 1, '');
		}
		return $result;
	}

	/**
	 * Function return true if html in $content is supported by Block Editor
	 *
	 * @param string $content
	 * @return boolean
	 */
	public static function isContentForBlockEditor($content)
	{
		Loader::includeModule('fileman');
		return BlockEditor::isContentSupported($content);
	}

	/**
	 * Init editor
	 *
	 * @param array $params
	 * @return string
	 */
	public static function initEditor(array $params)
	{
		$fieldName = $params['FIELD_NAME'];
		$fieldValue = $params['FIELD_VALUE'];
		$isUserHavePhpAccess = $params['HAVE_USER_ACCESS'];
		$showSaveTemplate = isset($params['SHOW_SAVE_TEMPLATE']) ? $params['SHOW_SAVE_TEMPLATE'] : true;
		$site = isset($params['SITE']) ? $params['SITE'] : '';
		$charset = isset($params['CHARSET']) ? $params['CHARSET'] : '';
		$contentUrl = isset($params['CONTENT_URL']) ? $params['CONTENT_URL'] : '';
		$templateTypeInput = isset($params['TEMPLATE_TYPE_INPUT']) ? $params['TEMPLATE_TYPE_INPUT'] : 'TEMPLATE_TYPE';
		$templateIdInput = isset($params['TEMPLATE_ID_INPUT']) ? $params['TEMPLATE_ID_INPUT'] : 'TEMPLATE_ID';
		$templateType = isset($params['TEMPLATE_TYPE']) ? $params['TEMPLATE_TYPE'] : '';
		$templateId = isset($params['TEMPLATE_ID']) ? $params['TEMPLATE_ID'] : '';
		$isTemplateMode = isset($params['IS_TEMPLATE_MODE']) ? (bool) $params['IS_TEMPLATE_MODE'] : true;
		if(!empty($params['PERSONALIZE_LIST']) && is_array($params['PERSONALIZE_LIST']))
		{
			PostingRecipientTable::setPersonalizeList($params['PERSONALIZE_LIST']);
		}

		static $isInit;

		$isDisplayBlockEditor = ($templateType && $templateId) || static::isContentForBlockEditor($fieldValue);

		$editorHeight = '650px';
		$editorWidth = '100%';

		Loader::includeModule('fileman');

		\CJSCore::RegisterExt("sender_editor", Array(
			"js" => array("/bitrix/js/sender/editor/htmleditor.js"),
			"rel" => array()
		));
		\CJSCore::Init(array("sender_editor"));

		ob_start();
		?>
		<div id="bx-sender-visual-editor-<?=$fieldName?>" style="<?if($isDisplayBlockEditor):?>display: none;<?endif;?>">
			<script>
				BX.ready(function(){
					<?if(!$isInit): $isInit = true;?>
						var letterManager = new SenderLetterManager;
						letterManager.setPlaceHolderList(<?=\CUtil::PhpToJSObject(PostingRecipientTable::getPersonalizeList());?>);
					<?endif;?>
				});

				BX.message({
					"BXEdPlaceHolderSelectorTitle" : "<?=Loc::getMessage('SENDER_TEMPLATE_EDITOR_PLACEHOLDER')?>"
				});
			</script>
			<textarea id="bxed_<?=htmlspecialcharsbx($fieldName)?>"
				name="<?=htmlspecialcharsbx($fieldName)?>"
				style="height: <?=htmlspecialcharsbx($editorHeight)?>; width: <?=htmlspecialcharsbx($editorWidth)?>;"
				class="typearea"
			><?=htmlspecialcharsbx($fieldValue)?></textarea>

		</div>

		<div id="bx-sender-block-editor-<?=htmlspecialcharsbx($fieldName)?>" style="<?if(!$isDisplayBlockEditor):?>display: none;<?endif;?>">
			<br/>
			<input type="hidden" name="<?=htmlspecialcharsbx($templateTypeInput)?>" value="<?=htmlspecialcharsbx($templateType)?>" />
			<input type="hidden" name="<?=htmlspecialcharsbx($templateIdInput)?>" value="<?=htmlspecialcharsbx($templateId)?>" />
			<?
			$url = '';
			if($isDisplayBlockEditor)
			{
				if($templateType && $templateId)
				{
					$url = '/bitrix/admin/sender_template_admin.php?';
					$url .= 'action=get_template&template_type=' . $templateType . '&template_id=' . $templateId;
					$url .= '&lang=' . LANGUAGE_ID . '&' . bitrix_sessid_get();
				}
				else
				{
					$url = $contentUrl;
				}
			}
			echo BlockEditorMail::show(array(
				'id' => $fieldName,
				'charset' => $charset,
				'site' => $site,
				'own_result_id' => 'bxed_' . $fieldName,
				'url' => $url,
				'templateType' => $templateType,
				'templateId' => $templateId,
				'isTemplateMode' => $isTemplateMode,
				'isUserHavePhpAccess' => $isUserHavePhpAccess,
			));
			?>
		</div>

		<?
		if($showSaveTemplate):
		?>
		<script>
			function ToggleTemplateSaveDialog()
			{
				BX('TEMPLATE_ACTION_SAVE_NAME_CONT').value = '';

				var currentDisplay =  BX('TEMPLATE_ACTION_SAVE_NAME_CONT').style.display;
				BX('TEMPLATE_ACTION_SAVE_NAME_CONT').style.display = BX.toggle(currentDisplay, ['inline', 'none']);
			}
		</script>
		<div class="adm-detail-content-item-block-save">
			<span>
				<input type="checkbox" value="Y" name="TEMPLATE_ACTION_SAVE" id="TEMPLATE_ACTION_SAVE" onclick="ToggleTemplateSaveDialog();">
				<label for="TEMPLATE_ACTION_SAVE"><?=Loc::getMessage('SENDER_TEMPLATE_EDITOR_SAVE')?></label>
			</span>
			<span id="TEMPLATE_ACTION_SAVE_NAME_CONT" style="display: none;"> <?=Loc::getMessage('SENDER_TEMPLATE_EDITOR_SAVE_NAME')?> <input type="text" name="TEMPLATE_ACTION_SAVE_NAME"></span>
		</div>
		<?
		endif;

		return ob_get_clean();
	}
}