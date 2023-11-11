<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Viewer;
use Bitrix\Disk\ZipNginx;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @var array $message */

?>
<?php
if (!empty($message['__files'])):
	$viewerItemAttributes = function ($item) use (&$message)
	{
		$attributes = Viewer\ItemAttributes::tryBuildByFileId($item['fileId'], $item['url'])
			->setTitle($item['name'])
			->setGroupBy(sprintf('mail_msg_%u_file', $message['ID']))
			->addAction(array(
				'type' => 'download',
			));

		if (isset($item['objectId']) && $item['objectId'] > 0)
		{
			$attributes->addAction(array(
				'type' => 'copyToMe',
				'text' => Loc::getMessage('MAIL_DISK_ACTION_SAVE_TO_OWN_FILES'),
				'action' => 'BX.Disk.Viewer.Actions.runActionCopyToMe',
				'params' => array(
					'objectId' => $item['objectId'],
				),
				'extension' => 'disk.viewer.actions',
				'buttonIconClass' => 'ui-btn-icon-cloud',
			));
		}

		return $attributes;
	};

	$diskFiles = $message['__diskFiles'] ?? [];
	?>
	<div id="mail_msg_<?=$message['ID'] ?>_files_images_list" class="mail-msg-view-file-inner">
		<?php foreach ($message['__files'] as $item): ?>
			<?php if (empty($item['preview'])) continue; ?>
			<div class="mail-msg-view-file-item-image">
				<span class="mail-msg-view-file-link-image">
					<img class="mail-msg-view-file-item-img" src="<?=htmlspecialcharsbx($item['preview']) ?>"
					<?=$viewerItemAttributes($item) ?>>
				</span>
			</div>
		<?php endforeach ?>
	</div>
	<div class="mail-msg-view-file-inner">
		<?php foreach ($message['__files'] as $item): ?>
			<?php if (!empty($item['preview'])) continue; ?>
			<div class="mail-msg-view-file-item diskuf-files-entity">
				<span class="feed-com-file-icon feed-file-icon-<?=htmlspecialcharsbx(\Bitrix\Main\IO\Path::getExtension($item['name'])) ?>"></span>
				<a class="mail-msg-view-file-link" href="<?=htmlspecialcharsbx($item['url']) ?>" target="_blank"
					<?php if (preg_match('/^n\d+$/i', $item['id'])) echo $viewerItemAttributes($item); ?>>
					<?=htmlspecialcharsbx($item['name']) ?>
				</a>
				<div class="mail-msg-view-file-link-info"><?=htmlspecialcharsbx($item['size']) ?></div>
			</div>
		<?php endforeach ?>
	</div>
	<?php if (\Bitrix\Main\Loader::includeModule('disk') && count($diskFiles) > 1 && ZipNginx\Configuration::isEnabled()): ?>
		<div class="mail-msg-view-file-archive-block">
			<?php $href = \Bitrix\Disk\Driver::getInstance()->getUrlManager()->getUrlDownloadController('downloadArchive', array(
				'fileId' => 0,
				'objectIds' => array_column($diskFiles, 'objectId'),
				'signature' => \Bitrix\Disk\Security\ParameterSigner::getArchiveSignature(array_column($diskFiles, 'objectId')),
				'mail_uf_message_token' => (string)($_REQUEST['mail_uf_message_token'] ?? ''),
			)) ?>
			<a class="mail-msg-view-file-archive-link" href="<?=htmlspecialcharsbx($href) ?>"><?=Loc::getMessage('MAIL_DISK_FILE_DOWNLOAD_ARCHIVE') ?></a>
			<div class="mail-msg-view-file-link-info">&nbsp;(<?=\CFile::formatSize(array_sum(array_column($diskFiles, 'bytes'))) ?>)</div>
		</div>
	<?php endif ?>
<?php endif ?>
