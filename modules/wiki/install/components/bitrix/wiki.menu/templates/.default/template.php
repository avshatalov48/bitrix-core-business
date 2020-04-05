<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

if(!empty($arResult['FATAL_MESSAGE'])):
	?>
	<div class="wiki-errors">
		<div class="wiki-error-text">
			<?=$arResult['FATAL_MESSAGE']?>
		</div>
	</div>
	<?
else:

	$sBar = 'page';
	$arButtons = array();
	if (!is_array($arResult['TOPLINKS']))
		$arResult['TOPLINKS'] = array();

	foreach ($arResult['TOPLINKS'] as $arLink)
	{		
		if ($sBar != $arLink['TYPE'] && !empty($arButtons))
			$arButtons[] = array('SEPARATOR' => true);

		if ($arResult['TYPE'] != 'full' && $arLink['ID'] == 'discussion')
			continue;

		$sBar = $arLink['TYPE'];
		$arButtons[] = array(
			'TEXT' => $arLink['NAME'],
			'TITLE' => $arLink['TITLE'],
			'LINK' => $arLink['LINK'],
			'ICON' => "btn-$arLink[ID]"
		);
	}


	if ($this->__component->__parent && is_array($this->__component->__parent->arResult['arButtons']))
	{
		foreach ($this->__component->__parent->arResult['arButtons'] as $arButton)
		{
			$arButtons[] = $arButton;
		}
	}

	if (!empty($arButtons))
	{
		$APPLICATION->IncludeComponent(
			'bitrix:main.interface.toolbar',
			'',
			array(
				'BUTTONS' => $arButtons
			),
			($this->__component->__parent ? $this->__component->__parent : $component),
			array(
				'HIDE_ICONS' => 'Y'
			)
		);
	}
endif;
?>