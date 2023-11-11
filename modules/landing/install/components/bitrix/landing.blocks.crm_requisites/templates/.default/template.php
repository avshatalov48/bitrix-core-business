<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$communications = $arResult['COMMUNICATIONS'] ?? null;
$requisites = $arResult['REQUISITES'] ?? [];
?>

<p><b><?= Loc::getMessage('LNDNG_BLPHB_TPL_HEADER_CONTACTS')?></b></p>

<?php if (!empty($communications['web'])):?>
	<p>
		<?= Loc::getMessage('LNDNG_BLPHB_TPL_CONTACTS_WEB')?>:
		<a href="<?= $communications['web'][0]?>" target="_blank"><?= preg_replace('#^https?://(.*?)/?$#', '$1', $communications['web'][0])?></a>
	</p>
<?php endif?>

<?php if (!empty($communications['phone'])):?>
	<p>
		<?= Loc::getMessage('LNDNG_BLPHB_TPL_CONTACTS_PHONE')?>:
		<?= $communications['phone'][0]?>
	</p>
<?php endif?>

<?php if (!empty($communications['email'])):?>
	<p>
		<?= Loc::getMessage('LNDNG_BLPHB_TPL_CONTACTS_EMAIL')?>:
		<a href="mailto:<?= $communications['email'][0]?>"><?= $communications['email'][0]?></a>
	</p>
<?php endif?>

<p><b><?= Loc::getMessage('LNDNG_BLPHB_TPL_HEADER_REQUISITES')?></b></p>

<?php foreach($requisites as $requisite):?>
	<p><b><?= $requisite['title']?>:</b> <?= $requisite['textValue']?></p>
<?php endforeach?>
