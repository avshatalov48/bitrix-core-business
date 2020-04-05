<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Preset;

use Bitrix\Main\Localization\Loc;

use Bitrix\Sender\Templates;
use Bitrix\Sender\TemplateTable;

Loc::loadMessages(__FILE__);

/**
 * Class Template
 * @package Bitrix\Sender\Preset
 * @deprecated
 * @internal
 */
class Template
{
	/**
	 * Get list by type.
	 *
	 * @return array
	 */
	public static function getListByType()
	{
		return Templates\Selector::create()->getCategorized();
	}

	/**
	 * Get type list.
	 *
	 * @return array
	 */
	public static function getTypeList()
	{
		return Templates\Category::getNamedCodes();
	}

	/**
	 * Get template.
	 *
	 * @param string $type Template type.
	 * @param string $id Template ID.
	 * @return array|null
	 */
	public static function getById($type, $id)
	{
		return Templates\Selector::create()
			->withTypeId($type)
			->withId($id)
			->get();
	}

	/**
	 * Get list.
	 *
	 * @param array $parameters Parameters.
	 * @return array
	 */
	public static function getList(array $parameters = array())
	{
		$selector = Templates\Selector::create();
		if (isset($parameters[0]))
		{
			$selector->withTypeId($parameters[0]);
		}
		if (isset($parameters[1]))
		{
			$selector->withId($parameters[1]);
		}
		return $selector->withDefaultMessageCode()->getList();
	}

	/**
	 * Get template list html.
	 *
	 * @param string $containerId Container ID.
	 * @return string
	 * @deprecated Use component bitrix:sender.template.selector.
	 */
	public static function getTemplateListHtml($containerId = 'TEMPLATE_CONTAINER')
	{
		static $templateListByType;

		if(!$templateListByType)
			$templateListByType = \Bitrix\Sender\Preset\Template::getListByType();

		$templateTypeList = \Bitrix\Sender\Preset\Template::getTypeList();

		ob_start();
		?>
		<script>
			BX.ready(function(){
				letterManager = new SenderLetterManager;
				if(!letterManager.get('<?=$containerId?>'))
				{
					letterManager.add('<?=$containerId?>', {'container': BX('<?=$containerId?>')});
				}
			});
		</script>
		<div class="sender-template-cont">
			<div>
				<table style="width: 100%;">
					<tr>
					<td style="vertical-align: top;">
						<div class="sender-template-type-selector">
							<?
							$firstTemplateType = null;
							foreach($templateTypeList as $templateType => $templateTypeName):
								if(!$firstTemplateType) $firstTemplateType = $templateType;
								?>
								<div class="sender-template-type-selector-button sender-template-type-selector-button-type-<?=$templateType?>"
										data-bx-sender-tmpl-type="<?=htmlspecialcharsbx($templateType)?>">
									<?=$templateTypeName?>
								</div>
							<?endforeach;?>
						</div>
					</td>
					<td style="vertical-align: top; width: 100%;">
						<div class="sender-template-list-container">
							<?foreach($templateTypeList as $templateType => $templateTypeName):?>
								<div id="sender-template-list-type-container-<?=$templateType?>" class="sender-template-list-type-container sender-template-list-type-container-<?=$templateType?>" style="display: none;">
									<?
									if(isset($templateListByType[$templateType]))
										foreach($templateListByType[$templateType] as $templateNum => $template):
											$isContentForBlockEditor = TemplateTable::isContentForBlockEditor($template['HTML']);
									?>
										<div class="sender-template-list-type-block">
											<div class="sender-template-list-type-block-caption sender-template-list-block-selector"
												 data-bx-sender-tmpl-version="<?=($isContentForBlockEditor?'block':'visual')?>"
												 data-bx-sender-tmpl-name="<?=htmlspecialcharsbx($template['NAME'])?>"
												 data-bx-sender-tmpl-type="<?=htmlspecialcharsbx($template['TYPE'])?>"
												 data-bx-sender-tmpl-code="<?=htmlspecialcharsbx($template['ID'])?>"
												 data-bx-sender-tmpl-lang="<?=LANGUAGE_ID?>">
												<a class="sender-link-email" href="javascript: void(0);">
													<?=htmlspecialcharsbx($template['NAME'])?>
												</a>
												<?if(!$isContentForBlockEditor):?>
													<br>
													<span style="font-size: 10px;"><?=Loc::getMessage('SENDER_PRESET_TEMPLATE_OLD_EDITOR')?></span>
												<?endif;?>
											</div>
											<div class="sender-template-list-type-block-img sender-template-list-block-selector"
													data-bx-sender-tmpl-version="<?=($isContentForBlockEditor?'block':'visual')?>"
													data-bx-sender-tmpl-name="<?=htmlspecialcharsbx($template['NAME'])?>"
													data-bx-sender-tmpl-type="<?=htmlspecialcharsbx($template['TYPE'])?>"
													data-bx-sender-tmpl-code="<?=htmlspecialcharsbx($template['ID'])?>"
													data-bx-sender-tmpl-lang="<?=LANGUAGE_ID?>">
												<?if(!empty($template['ICON'])):?>
													<img src="<?=$template['ICON']?>">
												<?endif;?>
											</div>
											<?if(!empty($template['HTML'])):?>
												<div class="sender-template-message-preview-btn"
													data-bx-sender-tmpl-name="<?=htmlspecialcharsbx($template['NAME'])?>"
													data-bx-sender-tmpl-type="<?=htmlspecialcharsbx($template['TYPE'])?>"
													data-bx-sender-tmpl-code="<?=htmlspecialcharsbx($template['ID'])?>"
													data-bx-sender-tmpl-lang="<?=LANGUAGE_ID?>">
													<a class="sender-link-email " href="javascript: void(0);"><?=Loc::getMessage('SENDER_PRESET_TEMPLATE_BTN_PREVIEW')?></a>
												</div>
											<?endif;?>
										</div>
									<?endforeach;?>
									<?if(empty($templateListByType[$templateType])):?>
										<div class="sender-template-list-type-blockempty">
											<?=Loc::getMessage('SENDER_PRESET_TEMPLATE_NO_TMPL')?>
										</div>
									<?endif;?>
								</div>
							<?endforeach;?>
						</div>
					</td>
					<td style="vertical-align: top;">
						<span class="sender-template-btn-close" title="<?=Loc::getMessage('SENDER_PRESET_TEMPLATE_BTN_CLOSE')?>"></span>
					</td>
					</tr>
				</table>
			</div>
		</div>
		<?
		return ob_get_clean();
	}
}