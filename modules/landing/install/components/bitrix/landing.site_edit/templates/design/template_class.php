<?php
namespace Bitrix\Landing\Components\LandingEdit;

use Bitrix\Landing\Field;
use Bitrix\Landing\File;
use CUtil;
use function htmlspecialcharsbx;

class Template
{
	/**
	 * Result of template.
	 * @var array
	 */
	private $result;

	/**
	 * Constructor.
	 * @param array $result Result of template.
	 */
	public function __construct(array $result)
	{
		$this->result = $result;
	}

	/**
	 * Print field title in new ui-form-layout format
	 * @param string $code
	 */
	public function showFieldTitle(string $code): void
	{
		$code = strtoupper($code);
		$hooks = $this->result['HOOKS'] ?? [];

		if (isset($hooks[$code]))
		{
			$pageFields = $hooks[$code]->getPageFields();
			$pageFieldsKey = $code . '_USE';
			if(!isset($pageFields[$pageFieldsKey]))
			{
				return;
			}
			?>
			<div class="ui-form-label" data-form-row-hidden>
				<label class="ui-ctl ui-ctl-checkbox">
					<input type="hidden" name="fields[ADDITIONAL_FIELDS]['. <?= $code ?> . '_USE]" value="Y"/>
					<?php $type = $pageFields[$code . '_USE']->getType(); ?>
					<?= $pageFields[$code . '_USE']->viewForm([
						'class' => self::getCssByType($type),
						'id' => 'checkbox-' . strtolower($code) . '-use',
						'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
					])?>

					<div class="ui-ctl-label-text">
						<?= $pageFields[$code . '_USE']->getLabel() ?>
					</div>
					<?php if ($desc = $hooks[$code]->getDescription()): ?>
						<span data-hint="<?= $desc ?>" class="ui-hint">
							<span class="ui-hint-icon"></span>
						</span>
					<?php endif; ?>
				</label>
			</div>
		<?php
		}
	}

	/**
	 * Print field in new ui-form-layout format
	 * @param string $name
	 * @param Field $field
	 */
	public function showField(string $name, Field $field): void
	{
		$type = $field->getType();
		$code = $field->getCode();
		?>
		<div class="ui-form-row">
			<?php if ($type !== 'checkbox'): ?>
				<div class="ui-form-label">
					<label class="ui-ctl-label-text" for="field-<?=strtolower($name)?>-use"><?=$field->getLabel()?></label>
					<?php if ($help = $field->getHelpValue()): ?>
						<span data-hint="<?= $help ?>" class="ui-hint">
							<span class="ui-hint-icon"></span>
						</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="<?= self::getCssByType($type) ?>">
				<?php if ($type === 'select'): ?>
					<div class="ui-ctl-after ui-ctl-icon-angle"></div>
				<?php endif; ?>
				<?php if ($code === 'THEMEFONTS_CODE' || $code === 'THEMEFONTS_CODE_H'): ?>
					<div class="ui-ctl-after ui-ctl-icon-angle fa-rotate-270"></div>
				<?php endif; ?>
				<?=$field->viewForm([
					'id' => 'field-' . strtolower($name),
					'additional' => 'readonly',
					'class' => 'ui-ctl-element ui-field-'.strtolower($name),
					'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
				])?>
			</div>
		</div>
		<?php
	}

	/**
	 * Display picture.
	 *
	 * @param Field $field Picture field for display.
	 * @param string $imgPath Path to img by default.
	 * @param array $params Some params.
	 *
	 * @return void
	 */
	public function showPictureJS(Field $field, $imgPath = '', $params = array()): void
	{
		if (!isset($params['imgId']))
		{
			return;
		}

		$imgId = $field->getValue();
		$code = mb_strtolower($field->getCode());
		$code = preg_replace('/[^a-z]+/', '', $code);
		?>
		<script type="text/javascript">
			BX.ready(function()
			{
				var imageFieldWrapper = BX('<?= $params['imgId']?>');
				var imageFieldInput = BX('landing-form-<?= $code?>-input');

				if (imageFieldWrapper)
				{
					var imageField = new BX.Landing.UI.Field.Image({
						id: 'page_settings_<?= $code?>',
						disableLink: true,
                        disableAltField: true,
                        allowClear: true
						<?php if ($imgId):?>
						,content: {
							src: '<?= CUtil::jsEscape(str_replace(' ', '%20', htmlspecialcharsbx((int) $imgId > 0 ? File::getFilePath($imgId) : $imgId))) ?>',
							id : <?= $imgId ? (int)$imgId : -1?>,
							alt : ''
						}
						<?php else:?>
						,content: {
							src: '<?= CUtil::jsEscape(str_replace(' ', '%20', htmlspecialcharsbx($imgPath))) ?>',
							id : -1,
							alt : ''
						}
						<?php endif;?>
						<?if (isset($params['width'], $params['height'])):?>
						,dimensions: {
							width: <?= (int)$params['width']?>,
							height: <?= (int)$params['height']?>
						}
						<?php endif;?>
						<?if (isset($params['uploadParams']) && !empty($params['uploadParams'])):?>
						,uploadParams: <?= CUtil::phpToJsObject($params['uploadParams']) ?>
						<?php endif;?>
					});

					if (imageFieldWrapper)
					{
						imageFieldWrapper.appendChild(imageField.layout);
						if (imageFieldInput)
						{
							imageField.layout.addEventListener('input', function()
							{
								var img = imageField.getValue();
								imageFieldInput.value = parseInt(img.id) > 0
													? img.id
													: img.src;
								BX.onCustomEvent('BX.Landing.UI.Field.Image:onChangeImage');
							});
						}
						<?php if (isset($params['imgEditId'])):?>
						BX.bind(BX('<?= $params['imgEditId']?>'), 'click', function (event)
						{
							imageField.onUploadClick(event);
						});
						<?php endif;?>
					}
					this.image = imageField;
				}
			});
		</script>
		<?php
		$field->viewForm(array(
			'id' => 'landing-form-' . $code . '-input',
			'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
		));
	}

	/**
	 * Get css-class by field type.
	 * @param $type
	 * @return string
	 */
	public static function getCssByType($type): string
	{
		$css = '';

		switch ($type)
		{
			case 'select':
				{
					$css = 'ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100';
					break;
				}
			case 'text':
				{
					$css = 'ui-ctl-textbox ui-ctl';
					break;
				}
			case 'checkbox':
				{
					$css = 'ui-ctl-element';
					break;
				}
		}

		return $css;
	}
}
