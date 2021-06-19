<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
\Bitrix\Main\Loader::includeModule('ui');
\Bitrix\Main\UI\Extension::load("ui.ears");

if (!empty($templateData) && is_array($templateData))
{
	if (!empty($templateData['JS_OBJ']) && !empty($templateData['REQUEST_KEY']))
	{
		$request = \Bitrix\Main\Context::getCurrent()->getRequest();
		$offset = $request[$templateData['REQUEST_KEY']];
		if (is_numeric($offset))
		{
			$offset = (float)$offset;
			if ($offset > 0 && $offset <= 100)
			{
				?>
				<script>
					BX.ready(BX.defer(function(){
						if (!!window.<?=$templateData['JS_OBJ']?>)
						{
							window.<?=$templateData['JS_OBJ']?>.setCurrentOffset(<?=$offset;?>);
						}
					}));
				</script>
				<?php
			}
		}
	}
}
