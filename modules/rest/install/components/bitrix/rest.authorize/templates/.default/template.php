<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Bitrix vars
 * @global array $arParams
 * @global array $arResult
 */

if(isset($arResult["OAUTH_PARAMS"])):
?>
	<div id="bx_auth_float" class="bx-auth-float">
		<div style="width:180px; text-align: center;" class="oauth-code-shower">
			<h3><?=GetMessage('OAUTH_CODE').":";?></h3>
			<h1 style="position: static; border: 1px solid; zoom:1.2; width:150px; text-align: center;"><?=$arResult["OAUTH_PARAMS"]["code"]?></h1>
		</div>
	</div>
<?
endif;
