<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="order_item_description ">
	<h3><?=GetMessage("SOA_TEMPL_PAY_SYSTEM")?></h3>
	<div class="ordering_container">
		<ul>
	<?
	if ($arResult["PAY_FROM_ACCOUNT"]=="Y")
	{
		?>
		<li>
			<div class="ordering_li_container <?if($arResult["USER_VALS"]["PAY_CURRENT_ACCOUNT"]=="Y") echo "checked"?>">
				<table>
					<tr>
						<td>
							<input type="hidden" name="PAY_CURRENT_ACCOUNT" value="N">
							<span class="inputcheckbox"><input type="checkbox" name="PAY_CURRENT_ACCOUNT" id="PAY_CURRENT_ACCOUNT" <?if($arResult["USER_VALS"]["PAY_CURRENT_ACCOUNT"]=="Y"):?> checked="checked" value="Y"<?else:?>value="N"<?endif?> onChange="if (BX(this).value=='Y') BX(this).value='N'; else BX(this).value='Y'; submitForm()"></span>
						</td>
						<td>
							<label for="PAY_CURRENT_ACCOUNT">
								<span class="posttarif"><?=GetMessage("SOA_TEMPL_PAY_ACCOUNT")?></span>
								<span class="postdescription"><?=GetMessage("SOA_TEMPL_PAY_ACCOUNT1")?> <b><?=$arResult["CURRENT_BUDGET_FORMATED"]?></b>, <?=GetMessage("SOA_TEMPL_PAY_ACCOUNT2")?></span>
							</label>
						</td>
					</tr>
				</table>
			</div>
		</li>
		<?
	}
	?>

	<?

	foreach($arResult["PAY_SYSTEM"] as $arPaySystem)
	{
		if(count($arResult["PAY_SYSTEM"]) == 1)
		{
			?>
			<li>
				<div class="ordering_li_container">
					<input type="hidden" name="PAY_SYSTEM_ID" value="<?=$arPaySystem["ID"]?>">
					<span class="posttarif"><b><?=$arPaySystem["NAME"];?></b></span>
					<?
					if (strlen($arPaySystem["DESCRIPTION"])>0)
					{
						?>
						<span class="postdescription"><?=$arPaySystem["DESCRIPTION"]?></span>
						<?
					}
					?>
				</div>
			</li>
			<?
		}
		else
		{
			//if (!isset($_POST['PAY_CURRENT_ACCOUNT']) OR $_POST['PAY_CURRENT_ACCOUNT'] == "N") {
			?>
			<li>
				<div class="ordering_li_container <?if ($arPaySystem["CHECKED"]=="Y") echo " checked"?>">
					<table>
						<tr>
							<td><span class="inputradio"><input type="radio" id="ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>" name="PAY_SYSTEM_ID" value="<?= $arPaySystem["ID"] ?>"<?if ($arPaySystem["CHECKED"]=="Y") echo " checked=\"checked\"";?> onClick="submitForm();" <?//=($arParams["DELIVERY_TO_PAYSYSTEM"]=="p2d")?"onClick=\"submitForm();\"":"";?> ></span></td>
							<td>
								<label for="ID_PAY_SYSTEM_ID_<?= $arPaySystem["ID"] ?>">
									<span class="posttarif"><?= $arPaySystem["PSA_NAME"] ?></span>
								<?
								if (strlen($arPaySystem["DESCRIPTION"])>0)
								{
									?>
									<span class="postdescription"><?=$arPaySystem["DESCRIPTION"]?></span>
									<?
								}
								?>
								</label>
						</tr>
					</table>
				</div>
			</li>
			<?
			//}
		}
	}
	?>
		</ul>
	</div>
</div>