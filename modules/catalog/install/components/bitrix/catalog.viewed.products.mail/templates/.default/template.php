<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */

use Bitrix\Main\Localization\Loc;

$skuTemplate = array();
?>
<? if(!empty($arResult['ITEMS'])):

if(is_array($arResult['SKU_PROPS']))
{
	foreach ($arResult['SKU_PROPS'] as $itemId => $skuProps)
	{
		$skuTemplate[$itemId] = array();
		foreach ($skuProps as &$arProp)
		{
			ob_start();
			?>
			<table>
			<tbody>
			<? if($arProp['SHOW_MODE'] == 'TEXT'): ?>

				<tr>
					<td>
						<p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
						color: #a8a8a8; font-size: 14px; margin:0 0 5px 3px;">
							<?= htmlspecialcharsex($arProp['NAME']); ?>
						</p>
						<table cellpadding="0" cellspacing="3">
							<tbody>
							<tr>
								<? foreach($arProp['VALUES'] as $key => $arOneValue): ?>

									<?
										if(isset($arOneValue['ALLOCATION']))
											$border = 2;
										else
											$border = 1;
									?>

								<td>
								<table cellpadding="0" cellspacing="0" bordercolor="#5d9728" border="<?=$border?>">
									<tbody>
										<tr>

											<td style="border: none;" width="30" height="30"
												valign="middle" align="middle">
												<span><?= htmlspecialcharsex($arOneValue['NAME']); ?></span>
											</td>

										</tr>
									</tbody>
								</table>
								</td>
								<? endforeach ?>
							</tr>
							</tbody>
						</table>
					</td>
				</tr>

				<tr><td height="15"></td></tr>

			<? elseif ($arProp['SHOW_MODE'] == 'PICT'): ?>

				<tr>
					<td>
						<p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
						color: #a8a8a8; font-size: 14px; margin:0 0 5px;">
							<?= htmlspecialcharsex($arProp['NAME']); ?>
						</p>
						<table cellpadding="0" cellspacing="3">
							<tbody>
							<tr>
						<? foreach ($arProp['VALUES'] as $arOneValue): ?>
							<td>
							<table cellpadding="0" cellspacing="0" bordercolor="#5d9728" border="2">
								<tbody>
								<tr>
									<td width="26" height="26" bgcolor="#000" style="border: none;
										background-image:url('<?=$arOneValue['PICT']['SRC']; ?>');">
									</td>
								</tr>
								</tbody>
							</table>
							</td>
						<? endforeach ?>
							</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr><td height="15"></td></tr>

			<? endif ?>
			</tbody>
			</table>
			<?
			$skuTemplate[$itemId][$arProp['CODE']] = ob_get_contents();
			ob_end_clean();
			unset($arProp);
		}
	}
}
?>

<table width="" cellpadding="0" cellspacing="0">

<thead>
	<tr>
		<td colspan="5">
			<span style="font-size: 32px; color: #000; font-family: 'OpenSans-Regular','Helvetica Neue',
				Helvetica, Arial, sans-serif;">
				<?=Loc::getMessage('CATALOG_VIEWED_PRODUCTS_MAIL_TITLE')?>
			</span>
		</td>
	</tr>
</thead>

<tbody>
	<? foreach($arResult['ITEMS'] as $item): ?>

	<tr><td colspan="5" height="25"></td></tr>

	<tr>
		<td colspan="5">
			<a href="<?=$item['DETAIL_PAGE_URL']?>" style="font-size: 14px; font-family: 'Helvetica Neue',
				Helvetica, Arial, sans-serif; font-weight: bold; color: #000; text-decoration: none;">
				<?=$item['NAME']?>
			</a>
		</td>
	</tr>

	<tr><td colspan="5" height="15px;"></td></tr>

	<tr>
		<td>

			<table cellpadding="0" cellspacing="0" valign="top" style="display: inline-block">
				<tbody>
				<tr>
					<td width="170">
						<table height="170" border="1" bordercolor="#ebebeb" cellpadding="0" cellspacing="0">
							<tbody>
								<tr>
									<td width="168" height="168">
										<a href="<?=$item['DETAIL_PAGE_URL']?>">
										<img src="<?=$item['PREVIEW_PICTURE']['src']?>" style="display: block; margin: auto">
										</a>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
					<td width="15"></td>
				</tr>
				<tr><td height="15"></td></tr>
				</tbody>
			</table>

			<? if(!empty($item['OFFERS']) && isset($skuTemplate[$item['ID']])): ?>

				<table cellpadding="0" cellspacing="0" style="display: inline-block" valign="top">
					<tbody>
						<tr>
							<td>
							<? foreach ($skuTemplate[$item['ID']] as $code => $template): ?>
								<?=$template?>
							<? endforeach ?>

							</td>
							<td width="45"></td>
						</tr>
					</tbody>
				</table>

			<? endif ?>

			<table style="display: inline-block" valign="top">
				<tbody>
				<tr>
					<td>
						<p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #3f3f3f;
						font-weight: bold; font-size: 14px; margin:0 0 5px;">
							<?=$item['MIN_PRICE']['PRINT_DISCOUNT_VALUE']?>
						</p>
						<? if($item['MIN_PRICE']['VALUE'] != $item['MIN_PRICE']['DISCOUNT_VALUE']): ?>
							<p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #adadad;
							text-decoration: line-through; font-size: 12px; margin:0 0 10px;">
								<?=$item['MIN_PRICE']['PRINT_VALUE']?>
							</p>
						<? endif ?>
					</td>
				</tr>
				<tr>
					<td width="112" height="29" bgcolor="#5d9728" valign="middle" align="middle">
						<a href="<?=$item['DETAIL_PAGE_URL']?>" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
						color: #fff; font-weight: bold; font-size: 12px; display: block; line-height: 29px;
						text-decoration: none;">
							<?=Loc::getMessage('CATALOG_VIEWED_PRODUCTS_MAIL_YET')?>
						</a>
					</td>
				</tr>
				<tr>
					<td height="15"></td>
				</tr>
				</tbody>
			</table>

		</td>
	</tr>
	<? endforeach ?>
</tbody>

</table>

<? endif ?>