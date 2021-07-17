<?php
namespace Bitrix\Landing\Components\LandingEdit;

use Bitrix\Landing\Field;
use Bitrix\Main\Localization\Loc;
use Bitrix\Landing\Restriction;

class Template
{
	/**
	 * Result of template.
	 * @var array
	 */
	private $result = array();

	/**
	 * Constructor.
	 * @param array $result Result of template.
	 */
	public function __construct($result)
	{
		$this->result = $result;
	}

	/**
	 * Display simple hook.
	 * @param string $code Code of hook.
	 * @return void
	 */
	public function showSimple($code)
	{
		$code = mb_strtoupper($code);
		$hooks = isset($this->result['HOOKS'])
					? $this->result['HOOKS']
					: array();

		if (isset($hooks[$code]))
		{
			$pageFields = $hooks[$code]->getPageFields();

			?><div class="ui-checkbox-hidden-input"><?

				// use-checkbox
				if (isset($pageFields[$code . '_USE']))
				{
					$type = $pageFields[$code . '_USE']->getType();
					$pageFields[$code . '_USE']->viewForm(array(
					  	'class' => self::getCssByType($type),
						'id' => 'checkbox-'.mb_strtolower($code) . '-use',
						'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
					));
				}

				?><div class="ui-checkbox-hidden-input-inner"><?

				// use-label
				if (isset($pageFields[$code . '_USE']))
				{
					?>
						<label class="ui-checkbox-label" for="<?= 'checkbox-'.mb_strtolower($code) . '-use';?>">
							<?= $pageFields[$code . '_USE']->getLabel();?>
						</label>
					<?
					if ($hooks[$code]->isLocked())
					{
						echo Restriction\Manager::getLockIcon(
							Restriction\Hook::getRestrictionCodeByHookCode($code),
							['checkbox-' . mb_strtolower($code) . '-use']
						);
					}
					unset($pageFields[$code . '_USE']);
				}

				// display field
				foreach ($pageFields as $key => $field)
				{
					$type = $field->getType();
					echo '<div class="ui-checkbox-hidden-input-hook">';
					echo $field->viewForm(array(
						'id' => 'field-'.mb_strtolower($key) . '-use',
						'class' => self::getCssByType($type),
						'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
					));
					if ($type == 'checkbox')
					{
						echo '<label for="field-'.mb_strtolower($key) . '-use">' .
								$field->getLabel() .
							'</label>';
					}
					if ($help  = $field->getHelpValue())
					{
						echo '<div class="ui-checkbox-hidden-input-hook-help">' . $help . '</div>';
					}
					echo '</div>';
				}

				?></div><?

			?></div><?
		}
	}

	public function showMultiply(string $code, bool $alwaysOpen = false): void
	{
		$code = strtoupper($code);
		$hooks = $this->result['HOOKS'] ?? [];

		if (isset($hooks[$code]))
		{
			$pageFields = $hooks[$code]->getPageFields();
			?>

			<div class="ui-checkbox-hidden-input">

				<?php
				// use-checkbox
				if (isset($pageFields[$code . '_USE']))
				{
					if($alwaysOpen)
					{
						echo '<input type="hidden" name="fields[ADDITIONAL_FIELDS]['. $code . '_USE]" value="Y"/>';
					}
					else
					{
						$type = $pageFields[$code . '_USE']->getType();
						$pageFields[$code . '_USE']->viewForm([
							'class' => self::getCssByType($type),
							'id' => 'checkbox-' . strtolower($code) . '-use',
							'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
						]);
					}
				}
				?>

				<div class="ui-checkbox-hidden-input-inner <?=($alwaysOpen ? 'opened' : '')?>">

					<?php
					if (isset($pageFields[$code . '_USE']))
					{ ?>
						<?php if(!$alwaysOpen): ?>
							<label class="ui-checkbox-label" for="<?= 'checkbox-' . strtolower($code) . '-use'; ?>">
								<?= $pageFields[$code . '_USE']->getLabel(); ?>
							</label>
						<?php endif; ?>
						<?php unset($pageFields[$code . '_USE']);
					}
					?>

					<?php foreach ($pageFields as $key => $field): ?>
						<?php $type = $field->getType(); ?>
						<div class="ui-checkbox-hidden-input-hook">
							<?php if ($type !== 'checkbox'): ?>
								<label for="field-<?=strtolower($key)?>-use"><?=$field->getLabel()?></label>
							<?php endif; ?>

							<?=$field->viewForm([
								'id' => 'field-' . strtolower($key) . '-use',
								'class' => self::getCssByType($type),
								'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]'
							])?>

							<?php if ($type === 'checkbox'): ?>
								<label for="field-<?=strtolower($key)?>-use"><?=$field->getLabel()?></label>
							<?php endif; ?>

							<?php if ($help = $field->getHelpValue()): ?>
								<div class="ui-checkbox-hidden-input-hook-help"><?=$help?></div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>

				</div>
			</div>
			<?php
		}
	}

	public function showField(string $code, Field $field, array $additional = [])
	{
		$isHidden = $additional['hidden'] ?: false;
		// todo: add hits
		?>
		<div class="ui-control-wrap"<?= $isHidden ? ' hidden' : '' ?>>
			<div class="ui-form-control-label"><?= $field->getLabel();?></div>
			<div class="ui-form-control-field">
				<?php
				$field->viewForm([
					'id' => 'field-' . strtolower($code),
					'additional' => 'readonly',
					'class' => self::getCssByType($field->getType()) . ' ui-field-' . strtolower($code),
					'name_format' => 'fields[ADDITIONAL_FIELDS][#field_code#]',
				]);
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Display picture.
	 * @param \Bitrix\Landing\Field $field Picture field for display.
	 * @param string $imgPath Path to img by default.
	 * @param array $params Some params.
	 * @return void
	 */
	public function showPictureJS(\Bitrix\Landing\Field $field, $imgPath = '', $params = array())
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
						<?if ($imgId):?>
						,content: {
							src: '<?= \CUtil::jsEscape(str_replace(' ', '%20', \htmlspecialcharsbx((int) $imgId > 0 ? \Bitrix\Landing\File::getFilePath($imgId) : $imgId)));?>',
							id : <?= $imgId ? intval($imgId) : -1?>,
							alt : ''
						}
						<?else:?>
						,content: {
							src: '<?= \CUtil::jsEscape(str_replace(' ', '%20', \htmlspecialcharsbx($imgPath)));?>',
							id : -1,
							alt : ''
						}
						<?endif;?>
						<?if (isset($params['width']) && isset($params['height'])):?>
						,dimensions: {
							width: <?= (int)$params['width']?>,
							height: <?= (int)$params['height']?>
						}
						<?endif;?>
						<?if (isset($params['uploadParams']) && !empty($params['uploadParams'])):?>
						,uploadParams: <?= \CUtil::phpToJsObject($params['uploadParams']);?>
						<?endif;?>
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
							});
						}
						<?if (isset($params['imgEditId'])):?>
						BX.bind(BX('<?= $params['imgEditId']?>'), 'click', function (event)
						{
							imageField.onUploadClick(event);
						});
						<?endif;?>
					}
				}
			});
		</script>
		<?
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
	public static function getCssByType($type)
	{
		$css = '';

		switch ($type)
		{
			case 'select':
				{
					$css = 'ui-select';
					break;
				}
			case 'text':
				{
					$css = 'ui-input';
					break;
				}
			case 'checkbox':
				{
					$css = 'ui-checkbox';
					break;
				}
		}

		return $css;
	}
}
