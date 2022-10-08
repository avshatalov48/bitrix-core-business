<?php

use Bitrix\Location\Entity\Source\Config;
use Bitrix\Location\Entity\Source\ConfigItem;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Location\Service\FormatService;
use Bitrix\Location\Repository\SourceRepository;
use Bitrix\Location\Entity\Source\OrmConverter;
use Bitrix\Location\Infrastructure\Service\LoggerService\LogLevel;
use Bitrix\Main\Config\Option;

$module_id = 'location';
$moduleAccess = $APPLICATION::GetGroupRight($module_id);

if($moduleAccess >= 'W' && Loader::includeModule($module_id)):

	/**
	 * @global CUser $USER
	 * @global CMain $APPLICATION
	 **/

	IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/options.php');
	IncludeModuleLangFile(__FILE__);

	$aTabs = array(
		array('DIV' => 'edit1', 'TAB' => Loc::getMessage('LOCATION_OPT_TAB_OPTIONS'), 'ICON' => "", 'TITLE' => Loc::getMessage('LOCATION_OPT_TAB_OPTIONS')),
		array('DIV' => 'edit2', 'TAB' => Loc::getMessage('LOCATION_OPT_TAB_SOURCES_OPTIONS'), 'ICON' => "", 'TITLE' => Loc::getMessage('LOCATION_OPT_TAB_SOURCES_OPTIONS')),
		array('DIV' => 'edit3', 'TAB' => Loc::getMessage('MAIN_TAB_RIGHTS'), 'ICON' => "", 'TITLE' => Loc::getMessage('MAIN_TAB_TITLE_RIGHTS')),
	);

	$tabControl = new CAdminTabControl('tabControl', $aTabs);

	$sourceRepository = new SourceRepository(new OrmConverter());
	$sources = $sourceRepository->findAll();

	if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_REQUEST['Update'] !== "" && check_bitrix_sessid())
	{
		/**
		 * Common settings
		 */
		if(isset($_REQUEST['address_format_code']))
		{
			Bitrix\Location\Infrastructure\FormatCode::setCurrent($_REQUEST['address_format_code']);
		}

		if(isset($_REQUEST['log_level']))
		{
			Option::set('location', 'log_level', (string)$_REQUEST['log_level']);
		}

		/**
		 * Sources
		 */
		foreach ($sources as $source)
		{
			$sourceCode = $source->getCode();
			$sourceConfig = $source->getConfig() ?? new Config();

			if (!isset($_REQUEST['SOURCE'][$sourceCode]))
			{
				continue;
			}
			$sourceRequest = $_REQUEST['SOURCE'][$sourceCode];

			/**
			 * Update source config
			 */
			$sourceConfigRequest = $_REQUEST['SOURCE'][$sourceCode]['CONFIG'] ?? [];
			/** @var ConfigItem $configItem */
			foreach ($sourceConfig as $configItem)
			{
				if (!$configItem->isVisible())
				{
					continue;
				}
				if (!isset($sourceConfigRequest[$configItem->getCode()]))
				{
					continue;
				}

				$value = null;
				if ($configItem->getType() === ConfigItem::STRING_TYPE)
				{
					$value = $sourceConfigRequest[$configItem->getCode()];
				}
				elseif ($configItem->getType() === ConfigItem::BOOL_TYPE)
				{
					$value = $sourceConfigRequest[$configItem->getCode()] === 'Y';
				}

				$configItem->setValue($value);
			}
			$source->setConfig($sourceConfig);

			/**
			 * Save updated source to database
			 */
			$sourceRepository->save($source);
		}

		ob_start();
		require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/admin/group_rights.php');
		ob_end_clean();

		if($_REQUEST['back_url_settings'] <> '')
			LocalRedirect($_REQUEST['back_url_settings']);

		LocalRedirect($APPLICATION->GetCurPage().'?mid='.urlencode($module_id).'&lang='.urlencode(LANGUAGE_ID).'&'.$tabControl->ActiveTabParam());
	}

	$formatCode = Bitrix\Location\Infrastructure\FormatCode::getCurrent();
	$formatList = [];
	$formatDescriptionList = [];
	$formatDescription = '';

	foreach(FormatService::getInstance()->findAll(LANGUAGE_ID) as $format)
	{
		$formatList[$format->getCode()] = $format->getName();
		$formatDescriptionList[$format->getCode()] = $format->getDescription();

		if($format->getCode() === $formatCode)
		{
			$formatDescription = $format->getDescription();
		}
	}

	$currentLogLevel = (int)Option::get('location', 'log_level', LogLevel::ERROR);
	$logLevels = [
		LogLevel::NONE => loc::getMessage('LOCATION_OPT_LOG_LEVEL_NONE'),
		LogLevel::ERROR => loc::getMessage('LOCATION_OPT_LOG_LEVEL_ERROR'),
		LogLevel::INFO => loc::getMessage('LOCATION_OPT_LOG_LEVEL_INFO'),
		LogLevel::DEBUG => loc::getMessage('LOCATION_OPT_LOG_LEVEL_DEBUG')
	];

	$tabControl->Begin();
	?>
	<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($module_id)?>&amp;lang=<?=LANGUAGE_ID?>">
	<?$tabControl->BeginNextTab();?>
		<tr>
			<td width="40%" valign="top"><?=Loc::getMessage("LOCATION_OPT_FORMAT")?>:</td>
			<td width="60%">
				<select name="address_format_code" onchange="onLocationOptionFormatChanged(this.value);">
					<?foreach($formatList as $code => $name):?>
						<option
								value="<?=htmlspecialcharsbx($code)?>"
								<?=$formatCode === $code ? ' selected' : ''?>>
									<?=htmlspecialcharsbx($name)?>
						</option>
					<?endforeach;?>
				</select>
				<?=BeginNote();?>
					<div id="location_address_format_description">
						<?=$formatDescription?>
					</div>
				<?=EndNote();?>
			</td>
		</tr>
		<tr>
			<td width="40%" valign="top"><?=Loc::getMessage("LOCATION_OPT_LOG_LEVEL")?>:</td>
			<td width="60%">
				<select name="log_level">
					<?foreach($logLevels as $level => $name):?>
						<option value="<?=$level?>"<?=($level === $currentLogLevel ? ' selected' : '')?>><?=$name?></option>
					<?endforeach;?>
				</select>
			</td>
		</tr>
	<?$tabControl->BeginNextTab();?>
		<?foreach ($sources as $source):
			$sourceCode = $source->getCode();
			if (
				$sourceCode === \Bitrix\Location\Entity\Source\Factory::OSM_SOURCE_CODE
				&& !\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24')
			)
			{
				continue;
			}

			$config = $source->getConfig();
		?>
			<tr class="heading">
				<td colspan="2"><b><?=htmlspecialcharsbx($source->getName())?></b></td>
			</tr>

			<?if (!is_null($config)):?>
				<?
				/** @var ConfigItem $configItem */
				foreach ($config as $configItem):
					if (!$configItem->isVisible())
					{
						continue;
					}

					$code = $configItem->getCode();

					$inputName = sprintf(
						'SOURCE[%s][CONFIG][%s]',
						$sourceCode,
						$code
					);
					$name = Loc::getMessage(
						sprintf(
							'LOCATION_OPT_SOURCE_%s_%s',
							$sourceCode,
							$code
						)
					);
					$note = Loc::getMessage(
						sprintf(
							'LOCATION_OPT_SOURCE_%s_%s_NOTE',
							$sourceCode,
							$code
						)
					);
				?>
					<tr>
						<td width="40%" valign="top"><?=$name?>:</td>
						<td width="60%">
							<?if ($configItem->getType() == ConfigItem::STRING_TYPE):?>
								<input type="text" name="<?=htmlspecialcharsbx($inputName)?>" size="40" value="<?=htmlspecialcharsbx($configItem->getValue())?>">
							<?elseif ($configItem->getType() == ConfigItem::BOOL_TYPE):?>
								<input type="hidden" name="<?=htmlspecialcharsbx($inputName)?>" value="N">
								<input type="checkbox" name="<?=htmlspecialcharsbx($inputName)?>" value="Y" <?=($configItem->getValue() ? ' checked' : '')?> >
							<?endif;?>
							<?if ($note):?>
								<?=BeginNote();?><?=$note?><?=EndNote();?>
							<?endif;?>
						</td>
					</tr>
				<?endforeach;?>
			<?endif;?>
		<?endforeach;?>
	<?$tabControl->BeginNextTab();?>
		<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>

	<?$tabControl->Buttons();?>
		<input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>" class="adm-btn-save">
		<?=bitrix_sessid_post();?>
		<?if($_REQUEST["back_url_settings"] <> ''):?>
			<input type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" onclick="window.location="<?echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"]))?>''>
			<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
		<?endif;?>
	<?$tabControl->End();?>
	</form>

	<script>
		function onLocationOptionFormatChanged(formatCode)
		{
			var formatDescriptionsList = <?=CUtil::PhpToJSObject($formatDescriptionList)?>;
			var note = document.getElementById('location_address_format_description');
			note.innerHTML = formatDescriptionsList[formatCode];
		}
	</script>
<?endif;?>