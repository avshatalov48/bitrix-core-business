<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @var array $arResult
 */

$event = isset($arResult['EXTERNAL_EVENT']) && is_array($arResult['EXTERNAL_EVENT'])
	? $arResult['EXTERNAL_EVENT'] : array();

$isCanceled = isset($event['IS_CANCELED']) ? $event['IS_CANCELED'] : false;

if($isCanceled):?>

	<div class="lists-view-message"><?=Loc::getMessage('LISTS_EDIT_EVENT_CANCELED')?></div>

<?else:
	$elementInfo = isset($event['PARAMS']['elementInfo']) && is_array($event['PARAMS']['elementInfo']) ?
		$event['PARAMS']['elementInfo'] : array(); ?>

	<div class="lists-view-message">
	<?=Loc::getMessage('LISTS_EDIT_EVENT_SUCCESSFULLY_CREATED',
		array(
			'#URL#' => isset($elementInfo['elementUrl']) ? htmlspecialcharsbx($elementInfo['elementUrl']) : '',
			'#TITLE#' => isset($elementInfo['elementName']) ? htmlspecialcharsbx($elementInfo['elementName']) : ''
		))?>
	</div>
	
<? endif; ?>

<script>
	BX.ready(
		function()
		{
			if(window.opener)
			{
				window.opener.focus();
			}

			BX.localStorage.set(
				"<?=CUtil::JSEscape($event['NAME'])?>",
				<?=CUtil::PhpToJSObject($event['PARAMS'])?>,
				10
			);
		}
	);
</script>