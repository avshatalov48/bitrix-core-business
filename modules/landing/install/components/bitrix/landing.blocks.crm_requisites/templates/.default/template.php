<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/** @var array $arParams */

\Bitrix\Main\UI\Extension::load([
	'ui.icon-set.main',
]);
?>

<?php
if ($arParams['TYPE'] === 'CONTACTS')
{
	$communications = $arResult['COMMUNICATIONS'] ?? null;
	$hideContactsData = $arResult['HIDE_CONTACTS_DATA'] ?? [];
	$isPrimaryIcon = $arResult['IS_PRIMARY_ICON'] ?? 'N';
	?>
	<div class="landing-crm-requisites__wrap">
		<div class="landing-crm-requisites__title"><?= Loc::getMessage('LNDNG_BLPHB_TPL_HEADER_CONTACTS')?></div>

		<div class="landing-crm-requisites_inner">
			<?php if (!empty($communications['web']) && !in_array('web', $hideContactsData, true)):?>
				<?php
				if (!str_contains($communications['web'][0], 'http'))
				{
					$communications['web'][0] = 'https://' . $communications['web'][0];
				}
				?>
				<div class="landing-crm-requisites__box">
					<div class="landing-crm-requisites_icon">
						<?php if ($isPrimaryIcon === 'Y') :?>
							<div class="fas fa-up-right-from-square g-font-size-18 g-color-primary"></div>
						<?php else: ?>
							<div class="fas fa-up-right-from-square g-font-size-18"></div>
						<?php endif?>
					</div>
					<div class="landing-crm-requisites_main">
						<div class="landing-crm-requisites__name"><?= Loc::getMessage('LNDNG_BLPHB_TPL_CONTACTS_WEB')?>:</div>
						<div class="landing-crm-requisites__value"><a href="<?= $communications['web'][0]?>" target="_blank"><?= preg_replace('#^https?://(.*?)/?$#', '$1', $communications['web'][0])?></a></div>
					</div>
				</div>
			<?php endif?>

			<?php if (!empty($communications['phone']) && !in_array('phone', $hideContactsData, true)):?>
				<div class="landing-crm-requisites__box">
					<div class="landing-crm-requisites_icon">
						<?php if ($isPrimaryIcon === 'Y') :?>
							<div class="fas fa-phone g-font-size-18 g-color-primary"></div>
						<?php else: ?>
							<div class="fas fa-phone g-font-size-18"></div>
						<?php endif?>
					</div>
					<div class="landing-crm-requisites_main">
						<div class="landing-crm-requisites__name"><?= Loc::getMessage('LNDNG_BLPHB_TPL_CONTACTS_PHONE')?>:</div>
						<div class="landing-crm-requisites__value">
							<a href="tel:<?= $communications['phone'][0]?>" target="_blank"><?= $communications['phone'][0]?></a>
						</div>
					</div>
				</div>
			<?php endif?>

			<?php if (!empty($communications['email']) && !in_array('email', $hideContactsData, true)):?>
				<div class="landing-crm-requisites__box">
					<div class="landing-crm-requisites_icon">
						<?php if ($isPrimaryIcon === 'Y') :?>
							<div class="fas fa-envelope g-font-size-18 g-color-primary"></div>
						<?php else: ?>
							<div class="fas fa-envelope g-font-size-18"></div>
						<?php endif?>
					</div>
					<div class="landing-crm-requisites_main">
						<div class="landing-crm-requisites__name"><?= Loc::getMessage('LNDNG_BLPHB_TPL_CONTACTS_EMAIL')?>:</div>
						<div class="landing-crm-requisites__value">
							<a href="mailto:<?= $communications['email'][0]?>" target="_blank"><?= $communications['email'][0]?></a>
						</div>
					</div>
				</div>
			<?php endif?>
		</div>
	</div>
	<?php
}
?>

<?php
if ($arParams['TYPE'] === 'REQUISITES')
{
	$requisites = $arResult['REQUISITES'] ?? [];
	$hideRequisitesData = $arResult['HIDE_REQUISITES_DATA'] ?? [];
	?>
	<div class="landing-crm-requisites__wrap">
		<div class="landing-crm-requisites__title"><?= Loc::getMessage('LNDNG_BLPHB_TPL_HEADER_REQUISITES')?></div>
		<div class="landing-crm-requisites__table">
			<?php foreach($requisites as $requisite):?>
				<?php if (!in_array($requisite["name"], $hideRequisitesData, true)) :?>
					<div class="landing-crm-requisites__table_row">
						<div class="landing-crm-requisites__table_cell"><?= $requisite['title']?>:</div>
						<div class="landing-crm-requisites__table_cell"><?= $requisite['textValue']?></div>
					</div>
				<?php endif?>
			<?php endforeach?>
		</div>
	</div>
	<?php
}
?>

<?php
if ($arParams['TYPE'] === 'BANK')
{
	$bankRequisites = $arResult['BANK_REQUISITES'] ?? [];
	$hideBankData = $arResult['HIDE_BANK_DATA'] ?? [];
	?>
	<div class="landing-crm-requisites__wrap">
		<div class="landing-crm-requisites__title">
			<?= Loc::getMessage('LNDNG_BLPHB_TPL_HEADER_BANK_REQUISITES')?>
		</div>
		<div class="landing-crm-requisites__table">
			<?php foreach($bankRequisites as $bankRequisite):?>
				<?php if (!in_array($bankRequisite["name"], $hideBankData, true)) :?>
					<div class="landing-crm-requisites__table_row">
						<div class="landing-crm-requisites__table_cell"><?= $bankRequisite['title']?>:</div>
						<div class="landing-crm-requisites__table_cell"><?= $bankRequisite['textValue']?></div>
					</div>
				<?php endif?>
			<?php endforeach?>
		</div>
	</div>
	<?php
}
?>
