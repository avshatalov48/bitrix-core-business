<?php
namespace Bitrix\Landing\Components\LandingEdit;

use Bitrix\Landing\Field;
use Bitrix\Landing\File;
use Bitrix\Main\Localization\Loc;
use Bitrix\Landing\Restriction;
use Bitrix\Main\Security\Random;

class Template
{
	/**
	 * Result of template.
	 * @var array
	 */
	private $result = [];

	/**
	 * For save originality of fields ID for any page
	 * @var string
	 */
	private string $uniqueId;

	/**
	 * Constructor.
	 * @param array $result Result of template.
	 */
	public function __construct(array $result)
	{
		$this->result = $result;
		$this->uniqueId = Random::getString(8);
	}

	/**
	 * Get unique string, then added for all fields IDs
	 * @return string
	 */
	public function getUniqueId(): string
	{
		return $this->uniqueId;
	}

	/**
	 * Build field id string with unique
	 * @param string $code
	 * @param bool $isUiField
	 * @param string $prefix
	 * @return string
	 */
	public function getFieldId(string $code, bool $isUiField = false, string $prefix = 'field'): string
	{
		return $this->getFieldClass($code, $isUiField, $prefix) . '-' . $this->uniqueId;
	}

	/**
	 * Build field class string (with no unification)
	 * @param string $code
	 * @param bool $isUiField
	 * @param string $prefix
	 * @return string
	 */
	public function getFieldClass(string $code, bool $isUiField = false, string $prefix = 'field'): string
	{
		return  $prefix. '-' . ($isUiField ? 'ui-' : '') . strtolower($code);
	}

	/**
	 * Print field in new ui-form-layout format
	 * @param Field $field
	 * @param array $params [
	 *  bool|string title => title for field. If true - get from field, or set your our string
	 *  string additional => some additional params for form as is
	 *  bool disabled => if field must be disabled
	 *  bool needWrapper => if rtue - add .ui-form-row wrapper
	 * ]
	 */
	public function showField(Field $field, array $params = []): void
	{
		$type = $field->getType();
		$code = $field->getCode();
		$additional = $params['additional'] ?? '';
		$disabled = $params['disabled'] ?? false;
		$readonly = $params['readonly'] ?? false;
		$needWrapper = $params['needWrapper'] ?? false;

		$isTitle = (bool)($params['title'] ?? null);
		$title = $field->getLabel();
		if (is_string($params['title'] ?? null) && $params['title'])
		{
			$title = $params['title'];
		}

		$fieldWrapperTag = ($isTitle && $type === 'checkbox') ? 'label' : 'div';
		$help = $field->getHelpValue();
		$htmlHelp = $field->isHtmlHelp();
		$isHelpLink = $help && strpos($help, '<a href=') !== false;

		?>
		<?php if ($needWrapper): ?>
		<div class="ui-form-row">
		<?php endif; ?>
 		<?php if ($isTitle && $type !== 'checkbox'): ?>
			<div class="ui-form-label">
				<label class="ui-ctl-label-text" for="<?=$this->getFieldId($code)?>"><?=$title?></label>
				<?php if ($help && !$isHelpLink): ?>
					<?php if ($htmlHelp): ?>
						<span data-hint="<?= $help ?>" data-hint-html class="ui-hint">
							<span class="ui-hint-icon"></span>
						</span>
					<?php else:?>
						<span data-hint="<?= $help ?>" class="ui-hint">
							<span class="ui-hint-icon"></span>
						</span>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		<?php elseif (!$isTitle  && $type !== 'checkbox'): ?>
			<?php if ($help && !$isHelpLink): ?>
				<div class="landing-form-control-label-help"><?= $help ?></div>
			<?php endif; ?>
		<?php endif; ?>

		<<?= $fieldWrapperTag ?> class="<?= self::getCssByType($type) ?>">
			<?php if (
				$code === 'THEMEFONTS_CODE'
				|| $code === 'THEMEFONTS_CODE_H'
				|| ($type === 'select' && !$field->isMulti())
			): ?>
				<div class="ui-ctl-after ui-ctl-icon-angle "></div>
			<?php endif; ?>
			<?=$field->viewForm([
				'id' => $this->getFieldId($code),
				'additional' => $additional,
				'class' => 'ui-ctl-element ui-field-'.strtolower($code),
				'disabled' => $disabled,
				'readonly' => $readonly,
				'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
			])?>
			<?php if ($isTitle && $type === 'checkbox'): ?>
				<div class="ui-ctl-label-text" for="<?=$this->getFieldId($code)?>"><?=$title?></div>
				<?php if ($help && !$isHelpLink): ?>
					<span data-hint="<?= $help ?>" class="ui-hint">
						<span class="ui-hint-icon"></span>
					</span>
				<?php endif; ?>
			<?php endif; ?>
		</<?= $fieldWrapperTag ?>>
		<?php if ($help && $isHelpLink) : ?>
			<div class="ui-checkbox-hidden-input-hook-help"><?= $help ?></div>
		<?php endif;?>
		<?php if ($needWrapper): ?>
		</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * @param string $code - string code of the hook
	 * @param array $params. [
	 *  string useTitle => title for use field
	 * ]
	 * @return void
	 */
	public function showFieldWithToggle(string $code, array $params = []): void
	{
		$code = mb_strtoupper($code);
		$hooks = $this->result['HOOKS'] ?? [];

		if (isset($hooks[$code]))
		{
			$pageFields = $hooks[$code]->getPageFields();
			$useField = $pageFields[$code . '_USE'];
			// if locked - no need 'row-hidden' functionality, because it set unnecessary click handler
			$isLocked = $hooks[$code]->isLocked();
			$isLockedRowHide = $isLocked && $useField->getValue() !== 'Y';
			$useTitle = true;
			if (($params['useTitle'] ?? null) && is_string($params['useTitle']))
			{
				$useTitle = $params['useTitle'];
			}
			if (($params['restrictionCode'] ?? null) && is_string($params['restrictionCode']))
			{
				$restrictionCode = $params['restrictionCode'];
			}
			else
			{
				$restrictionCode = $code;
			}
			$fieldId = $this->getFieldId($code);

			if ($useField && $useField->getType() === 'checkbox')
			{
				?>
				<div class="ui-form-row" id="<?= $fieldId ?>">
					<div
						class="ui-form-label<?= $isLocked ? ' landing-form-label__locked' : ''?>"
						<?= $isLocked ? '' : 'data-form-row-hidden'?>
					>
						<?php
							$this->showField($useField, [
								'title' => $useTitle,
							]);
						?>
						<?php
							if ($isLocked && isset($restrictionCode))
							{
								echo Restriction\Manager::getLockIcon(
									Restriction\Hook::getRestrictionCodeByHookCode($restrictionCode),
									[$fieldId]
								);
							}
						?>
					</div>
					<?php if (!$isLockedRowHide): ?>
						<div class="<?= $isLocked ? 'ui-form-row-hidden__locked' : 'ui-form-row-hidden'?>">
							<div class="ui-form-row">
							<?php
								unset($pageFields[$code . '_USE']);
								foreach ($pageFields as $field)
								{
									$this->showField($field, [
										'additional' => $hooks[$code]->isLocked() ? 'disabled' : ''
									]);
								}
							?>
							</div>
						</div>
					<?php endif; ?>
				</div>
				<?php
			}
		}
	}

	/**
	 * Display picture.
	 * @param Field $field Picture field for display.
	 * @param string $imgPath Path to img by default.
	 * @param array $params Some params.
	 * @return void
	 */
	public function showPictureJS(Field $field, string $imgPath = '', array $params = []): void
	{
		$imgId = $field->getValue();
		$code = mb_strtolower($field->getCode());
		// $code = preg_replace('/[^a-z]+/', '', $code);
		$codeWrapper = $code . '_form';
		$codeEdit = (isset($params['imgEdit']) && $params['imgEdit']) ? $code . '_edit' : null;
		?>
		<script type="text/javascript">
			BX.ready(function()
			{
				const imageFieldWrapper = BX('<?= $this->getFieldId($codeWrapper) ?>');
				const imageFieldInput = BX('<?= $this->getFieldId($code) ?>');

				if (imageFieldWrapper)
				{
					const imageField = new BX.Landing.UI.Field.Image({
						id: '<?= $this->getFieldId($code, true) ?>',
						disableLink: true,
                        disableAltField: true,
						compactMode: true,
                        allowClear: true
						<?php if ($imgId):?>
						,content: {
							src: '<?= \CUtil::jsEscape(str_replace(' ', '%20', \htmlspecialcharsbx((int) $imgId > 0 ? File::getFilePath($imgId) : $imgId))) ?>',
							id : <?= (int)$imgId ?>,
							alt : ''
						}
						<?php else:?>
						,content: {
							src: '<?= \CUtil::jsEscape(str_replace(' ', '%20', \htmlspecialcharsbx($imgPath))) ?>',
							id : -1,
							alt : ''
						}
						<?php endif;?>
						<?if (isset($params['width'], $params['height'])):?>
						,dimensions: {
							maxWidth: <?= (int)$params['width']?>,
							maxHeight: <?= (int)$params['height']?>
						}
						<?php endif;?>
						<?php if (isset($params['uploadParams']) && !empty($params['uploadParams'])):?>
						,uploadParams: <?= \CUtil::phpToJsObject($params['uploadParams']) ?>
						<?php endif;?>
					});

					if (imageFieldWrapper)
					{
						imageFieldWrapper.appendChild(imageField.layout);
						if (imageFieldInput)
						{
							imageField.layout.addEventListener('input', function()
							{
								const img = imageField.getValue();
								imageFieldInput.value = parseInt(img.id) > 0
													? img.id
													: img.src;
								BX.onCustomEvent('BX.Landing.UI.Field.Image:onChangeImage');
							});
						}
						<?php if ($codeEdit):?>
							BX.bind(BX('<?= $this->getFieldId($codeEdit) ?>'), 'click', function (event) {
								imageField.onUploadClick(event);
							});
						<?php endif;?>
					}
					this.image = imageField;
				}
			});
		</script>
		<div
			id="<?= $this->getFieldId($codeWrapper) ?>"
			class="<?= $this->getFieldClass($codeWrapper) ?> ui-ctl-w100">
		</div>
		<?php if ($codeEdit):?>
			<div
				id="<?= $this->getFieldId($codeEdit) ?>"
				class="landing-form-social-img-edit">
			</div>
		<?php endif; ?>
		<?php
		$field->viewForm([
			'id' => $this->getFieldId($code),
			'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
		]);
	}

	/**
	 * Get css-class by field type.
	 * @param $type
	 * @return string
	 */
	public static function getCssByType($type)
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
				$css = 'ui-ctl ui-ctl-textbox ui-ctl-w100';
				break;
			}
			case 'checkbox':
			{
				$css = 'ui-ctl ui-ctl-checkbox';
				break;
			}
			case 'textarea':
			{
				$css = 'ui-ctl ui-ctl-textarea ui-ctl-resize-x';
				break;
			}
		}

		return $css;
	}
}
