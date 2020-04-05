<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$htmlFormId = htmlspecialcharsbx('main_mail_form_'.$arParams['FORM_ID']);

$renderField = function($htmlFormId, $field, $isExt = false)
{
	global $APPLICATION;

	if (in_array($field['type'], array('editor', 'files')))
		return;

	$htmlFieldId = sprintf('%s_%s', $htmlFormId, htmlspecialcharsbx($field['id']));

	?><tr id="<?=$htmlFieldId ?>"
		<? if (!empty($field['hidden']) || !empty($field['folded'])): ?> style="display: none; "<? endif ?>><?

		$titleSubClass = 'main-mail-form-field-title-cell';
		if (!empty($field['required']))
			$titleSubClass .= ' main-mail-form-field-title-required';

		$valueSubClass = 'main-mail-form-field-value-cell';
		if (!empty($field['short']))
			$valueSubClass .= ' main-mail-form-field-value-short';
		if (!empty($field['menu']))
			$valueSubClass .= ' main-mail-form-field-value-menu-ext';

		switch ($field['type'])
		{
			case 'separator':
				?>
				<td colspan="2" class="main-mail-form-fields-table-cell">
					<div class="main-mail-form-border-bottom"></div>
				</td>
				<?
				break;

			case 'list':
				?>
				<td class="main-mail-form-fields-table-cell <?=$titleSubClass ?>">
					<span class="main-mail-form-field-spacer-25"></span>
					<span class="main-mail-form-field-title"><?=preg_replace('/[\r\n]+/', '<br>', htmlspecialcharsbx($field['title'])) ?>:</span>
				</td>
				<td class="main-mail-form-fields-table-cell <?=$valueSubClass ?>">
					<input type="hidden"
						id="<?=$htmlFieldId ?>_value"
						name="<?=htmlspecialcharsbx($field['name']) ?>"
						value="<?=htmlspecialcharsbx($field['value']) ?>">
					<span class="main-mail-form-field-spacer-25"></span>
					<span class="main-mail-form-field-title main-mail-form-field-value-menu"><?
						echo htmlspecialcharsbx($field['list'][$field['value']] ?: $field['placeholder']);
					?></span>
				</td>
				<?
				break;

			case 'from':
				?>
				<td class="main-mail-form-fields-table-cell <?=$titleSubClass ?>">
					<span class="main-mail-form-field-spacer-25"></span>
					<span class="main-mail-form-field-title"><?=preg_replace('/[\r\n]+/', '<br>', htmlspecialcharsbx($field['title'])) ?>:</span>
				</td>
				<td class="main-mail-form-fields-table-cell <?=$valueSubClass ?>">
					<? $mailboxes = $APPLICATION->includeComponent('bitrix:main.mail.confirm', '', array()); ?>
					<input type="hidden"
						id="<?=$htmlFieldId ?>_value"
						name="<?=htmlspecialcharsbx($field['name']) ?>"
						value="<?=htmlspecialcharsbx($field['value']) ?>">
					<span class="main-mail-form-field-spacer-25"></span>
					<span class="main-mail-form-field-from-icon"></span>
					<span class="main-mail-form-field-title main-mail-form-field-value-menu"><?
						echo htmlspecialcharsbx($field['value'] ?: $field['placeholder']);
					?></span>
				</td>
				<?
				break;

			case 'rcpt':
				$valueSubClass .= ' main-mail-form-field-value-rcpt';
				?>
				<td class="main-mail-form-fields-table-cell <?=$titleSubClass ?>">
					<span class="main-mail-form-field-spacer"></span>
					<span class="main-mail-form-field-title"><?=preg_replace('/[\r\n]+/', '<br>', htmlspecialcharsbx($field['title'])) ?>:</span>
				</td>
				<td class="main-mail-form-fields-table-cell <?=$valueSubClass ?>">
					<div class="main-mail-form-field-value-wrapper">
						<span class="main-mail-form-field-rcpt-more-wrapper" style="display: none; ">
							<span class="feed-add-post-destination main-mail-form-field-rcpt-item-more"
								title="<?=getMessage('MAIN_MAIL_FORM_RCPT_MORE_HINT', array('#NUM#' => 0)) ?>">...</span>
						</span>
						<span class="main-mail-form-field-rcpt-value-wrapper" style="display: none; ">
							<input class="main-mail-form-field-value main-mail-form-field-rcpt-value"
								type="text" id="<?=$htmlFieldId ?>_fvalue">
						</span>
						<a class="feed-add-destination-link main-mail-form-field-rcpt-add-link" href="javascript:void(0)"><?
							echo htmlspecialcharsbx($field['placeholder']);
						?></a>
					</div>
				</td>
				<?
				break;

			case 'custom':
				$titleSpacerSubClass = isset($field['height']) && $field['height'] > 0
					? sprintf('main-mail-form-field-spacer-%u', $field['height']) : '';
				?>
				<td class="main-mail-form-fields-table-cell <?=$titleSubClass ?>">
					<span class="main-mail-form-field-spacer <?=$titleSpacerSubClass ?>"></span>
					<span class="main-mail-form-field-title"><?=preg_replace('/[\r\n]+/', '<br>', htmlspecialcharsbx($field['title'])) ?>:</span>
				</td>
				<td class="main-mail-form-fields-table-cell <?=$valueSubClass ?>">
					<?=(isset($field['render']) && is_callable($field['render']) ? $field['render']($field) : $field['value']); ?>
				</td>
				<?
				break;

			case 'text':
			default:
				?>
				<td class="main-mail-form-fields-table-cell <?=$titleSubClass ?>">
					<span class="main-mail-form-field-spacer"></span>
					<span class="main-mail-form-field-title"><?=preg_replace('/[\r\n]+/', '<br>', htmlspecialcharsbx($field['title'])) ?>:</span>
				</td>
				<td class="main-mail-form-fields-table-cell <?=$valueSubClass ?>">
					<div class="main-mail-form-field-value-wrapper">
						<input class="main-mail-form-field-value" type="text"
							id="<?=$htmlFieldId ?>_value"
							name="<?=htmlspecialcharsbx($field['name']) ?>"
							value="<?=htmlspecialcharsbx($field['value']) ?>"
							placeholder="<?=htmlspecialcharsbx($field['placeholder']) ?>">
						<span class="main-mail-form-field-value-menu-ext-button"></span>
					</div>
				</td>
				<?
		}

	?></tr><?
};

\Bitrix\Main\UI\Extension::load('ui.buttons');

?>
<div class="main-mail-form-wrapper" id="<?=$htmlFormId ?>">
	<div class="main-mail-form-fields-wrapper">
		<table class="main-mail-form-fields-table">
			<?
			foreach ($arParams['FIELDS'] as $field)
				$renderField($htmlFormId, $field);
			?>
			<tr id="<?=sprintf('%s_fields_footer', $htmlFormId) ?>">
				<td class="main-mail-form-fields-footer-cell" colspan="2">
					<div class="main-mail-form-fields-buttons">
						<? foreach ($arParams['FIELDS'] as $field): ?>
							<? if (in_array($field['type'], array('editor', 'files', 'separator'))) continue; ?>
							<span class="main-mail-form-field-button"
								data-target="<?=sprintf('%s_%s', $htmlFormId, htmlspecialcharsbx($field['id'])) ?>"
								<? if (empty($field['folded']) || !empty($field['hidden'])): ?> style="display: none; "<? endif ?>><?
								echo htmlspecialcharsbx($field['title'])
							?></span>
						<? endforeach ?>
					</div>
				</td>
			</tr>

		</table>
	</div>

	<? $editorHeight = isset($arParams['EDITOR']['height']) && $arParams['EDITOR']['height'] > 0 ? (int) $arParams['EDITOR']['height'] : 200; ?>
	<div id="<?=sprintf('%s_%s', $htmlFormId, htmlspecialcharsbx($arParams['EDITOR']['id'])) ?>"
		class="main-mail-form-editor-wrapper <? if (!empty($arParams['EDITOR']['menu'])): ?> main-mail-form-field-value-menu-ext<? endif ?>"
		style="min-height: <?=$editorHeight ?>px; ">
		<? $APPLICATION->includeComponent(
			'bitrix:main.post.form', '',
			array(
				'FORM_ID' => $htmlFormId,
				'SHOW_MORE' => 'N',
				'PARSER' => array(
					'Bold', 'Italic', 'Underline', 'Strike', 'ForeColor',
					'FontList', 'FontSizeList', 'RemoveFormat',
					'Quote', 'Code', 'Source', 'Table',
					'CreateLink', 'Image', 'UploadImage',
					'Justify', 'InsertOrderedList', 'InsertUnorderedList',
				),
				'BUTTONS' => array_merge(
					!empty($arParams['FOLD_QUOTE']) ? array('ReplyQuote') : array(),
					array('UploadImage', 'UploadFile', 'Panel')
				),
				'BUTTONS_HTML' => array(
					'ReplyQuote' => '<span class="main-mail-form-quote-button-wrapper"><span class="main-mail-form-quote-button">...</span></span>',
					'Panel' => '<span class="feed-add-post-form-but-cnt"><span class="bxhtmled-top-bar-btn feed-add-post-form-editor-btn"></span></span>',
				),
				'TEXT' => array(
					'INPUT_NAME' => 'dummy_'.$arParams['EDITOR']['name'],
					'VALUE' => '',
					'SHOW' => !empty($arParams['EDITOR_TOOLBAR']) ? 'Y' : 'N',
				),
				'PROPERTIES' => array(
					array(
						'USER_TYPE_ID' => 'disk_file',
						'USER_TYPE'    => array('TAG' => 'ATTACHMENT'),
						'FIELD_NAME'   => $arParams['FILES']['name'].'[]',
						'VALUE'        => $arParams['FILES']['value'],
						'HIDE_CHECKBOX_ALLOW_EDIT' => 'Y',
					),
				),
				'LHE' => array(
					'id' => sprintf('%s_editor', $htmlFormId),
					'documentCSS' => 'body { color:#434343; }',
					'fontFamily' => "'Helvetica Neue', Helvetica, Arial, sans-serif",
					'fontSize' => '15px',
					'height' => $editorHeight,
					'lazyLoad' => true,
					'bbCode' => false,
					'setFocusAfterShow' => true,
					'iframeCss' => 'body { padding-left: 10px !important; font-size: 15px; }',
					'useFileDialogs' => false,
					'useLinkStat' => false,
					'uploadImagesFromClipboard' => false,
					'controlsMap' => array(
						array('id' => 'Bold', 'compact' => true, 'sort' => 10),
						array('id' => 'Italic', 'compact' => true, 'sort' => 20),
						array('id' => 'Underline', 'compact' => true, 'sort' => 30),
						array('id' => 'Strikeout', 'compact' => true, 'sort' => 40),
						array('id' => 'RemoveFormat', 'compact' => true, 'sort' => 50),
						array('id' => 'Color', 'compact' => true, 'sort' => 60),
						array('id' => 'FontSelector', 'compact' => false, 'sort' => 70),
						array('id' => 'FontSize', 'compact' => false, 'sort' => 80),
						array('separator' => true, 'compact' => false, 'sort' => 90),
						array('id' => 'OrderedList', 'compact' => true, 'sort' => 100),
						array('id' => 'UnorderedList', 'compact' => true, 'sort' => 110),
						array('id' => 'AlignList', 'compact' => false, 'sort' => 120),
						array('separator' => true, 'compact' => false, 'sort' => 130),
						array('id' => 'InsertLink', 'compact' => true, 'sort' => 140),
						array('id' => 'InsertImage', 'compact' => false, 'sort' => 150),
						array('id' => 'InsertTable', 'compact' => false, 'sort' => 170),
						array('id' => 'Code', 'compact' => true, 'sort' => 180),
						array('id' => 'Quote', 'compact' => true, 'sort' => 190),
						array('separator' => true, 'compact' => false, 'sort' => 200),
						array('id' => 'Fullscreen', 'compact' => false, 'sort' => 210),
						array('id' => 'BbCode', 'compact' => false, 'sort' => 220),
						array('id' => 'More', 'compact' => true, 'sort' => 400),
					),
				),
			),
			false,
			array('HIDE_ICONS' => 'Y', 'ACTIVE_COMPONENT' => 'Y')
		); ?>
		<span class="main-mail-form-field-value-menu-ext-button"></span>
	</div>

	<? if (!empty($arParams['FIELDS_EXT'])): ?>
		<div class="main-mail-form-docs-wrapper main-mail-form-border-bottom">
			<table class="main-mail-form-fields-table">
				<?
				foreach ($arParams['FIELDS_EXT'] as $field)
					$renderField($htmlFormId, $field, true);
				?>
				<tr id="<?=sprintf('%s_fields_ext_footer', $htmlFormId) ?>">
					<td class="main-mail-form-fields-footer-cell" colspan="2">
						<div class="main-mail-form-fields-buttons">
							<? foreach ($arParams['FIELDS_EXT'] as $field): ?>
								<? if (in_array($field['type'], array('editor', 'files', 'separator'))) continue; ?>
								<span class="main-mail-form-field-button"
									data-target="<?=sprintf('%s_%s', $htmlFormId, htmlspecialcharsbx($field['id'])) ?>"
									<? if (empty($field['folded']) || !empty($field['hidden'])): ?> style="display: none; "<? endif ?>><?
									echo htmlspecialcharsbx($field['title'])
								?></span>
							<? endforeach ?>
						</div>
					</td>
				</tr>
			</table>
		</div>
	<? else: ?>
		<div class="main-mail-form-border-bottom"></div>
	<? endif ?>

	<div class="main-mail-form-error" style="display: none; "></div>
	<div class="main-mail-form-footer-wrapper">
		<div class="main-mail-form-footer">
			<div class="main-mail-form-footer-buttons-wrapper">
				<? foreach ($arParams['BUTTONS'] as $type => $item)
				{
					if (empty($item['class']))
					{
						if ('submit' == $type)
							$item['class'] = 'ui-btn-success';
						else if ('cancel' == $type)
							$item['class'] = 'ui-btn-link';
						else
							$item['class'] = 'ui-btn-light-border';
					}

					if ('submit' == $type)
						$item['class'] .= ' main-mail-form-submit-button';
					else if ('cancel' == $type)
						$item['class'] .= ' main-mail-form-cancel-button';

					?><button class="ui-btn main-mail-form-footer-button <?=htmlspecialcharsbx($item['class']) ?>" type="button"><?=htmlspecialcharsbx($item['title']) ?></button><?
				}
				?>
			</div>
			<div><?=$arParams['~FOOTER'] ?></div>
		</div>
	</div>

	<input id="<?=htmlspecialcharsbx($htmlFormId) ?>_<?=htmlspecialcharsbx($arParams['EDITOR']['id']) ?>_value"
		type="hidden" name="<?=htmlspecialcharsbx($arParams['EDITOR']['name']) ?>">
	<input type="submit" name="<?=sprintf('%s_submit', $htmlFormId) ?>" value="Y" style="display: none; ">
</div>

<script type="text/javascript">

BX.message({
	BXEdBbCode: '<?=\CUtil::jsEscape(getMessage('MAIN_MAIL_FORM_EDITOR_HTML_MODE_BTN_HINT')) ?>'
});

BX.ready(function()
{
	var form = new BXMainMailForm(
		'<?=\CUtil::jsEscape($arParams['FORM_ID']) ?>',
		<?=\Bitrix\Main\Web\Json::encode(array_merge(
			array_values($arParams['FIELDS']),
			array_values($arParams['FIELDS_EXT'])
		)) ?>,
		<?=\Bitrix\Main\Web\Json::encode(array(
			'submitAjax' => !empty($arParams['SUBMIT_AJAX']),
			'foldQuote'  => !empty($arParams['FOLD_QUOTE']),
			'foldFiles'  => !empty($arParams['FOLD_FILES']),
		)) ?>
	);

	<? if (empty($arParams['LAYOUT_ONLY'])): ?>
		form.init();
	<? endif ?>
});
	
</script>
