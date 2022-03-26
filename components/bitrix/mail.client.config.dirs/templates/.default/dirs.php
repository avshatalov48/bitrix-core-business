<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$dirs = isset($arResult['DIRS']) ? $arResult['DIRS'] : [];
$maxLevel = isset($arResult['MAX_LEVEL']) ? $arResult['MAX_LEVEL'] : 0;

$getTree = function ($dirs, $currentLevel = 1) use (&$getTree, $maxLevel)
{
	$result = '';

	foreach ($dirs as $dir)
	{
		$items = '';

		if ($dir->hasChildren() && $currentLevel < $maxLevel)
		{
			$items = sprintf(
				'<div class="mail-config-dirs-submenu">%s</div>',
				$getTree($dir->getChildren(), $currentLevel + 1)
			);
		}

		$countSync = $dir->getCountSyncChildren();
		$countChild = $dir->getCountChildren();
		$hasChild = (bool)preg_match('/(HasChildren)/ix', $dir->getFlags());

		$button = '<span class="mail-config-dirs-level-box">
			<span class="mail-config-dirs-plus-icon mail-config-dirs-level-button"></span>
		</span>';

		$input = sprintf(
			'<input
				class="mail-config-dirs-item-input-check"
				type="checkbox"
				name="dirs[%1$s]"
				value="%2$s"
				data-path="%1$s"
				data-id="%2$s"
				data-haschild="%3$s"
				data-level="%4$s"
				id="%2$s"
				%5$s
				%6$s
			/>',
			htmlspecialcharsbx($dir->getPath()),
			$dir->getDirMd5(),
			$hasChild,
			$dir->getLevel(),
			$dir->isSync() ? 'checked ' : '',
			$dir->isDisabled() ? 'disabled ' : ''
		);

		$label = sprintf(
			'<label for="%s">
				%s
				<span class="child-counter-container%s">
					(<span class="sync-child-counter">%s</span>/<span class="total-child-counter">%s</span>)
				</span>
			</label>',
			$dir->getDirMd5(),
			htmlspecialcharsbx($dir->getName()),
			$countSync > 0 ? ' show' : '',
			$countSync,
			$countChild
		);

		$result .= sprintf(
			'<div class="mail-config-dirs-item">
				<div class="mail-config-dirs-item-content%1$s">
					%2$s %3$s %4$s
				</div>
				%5$s
			</div>',
			$hasChild ? ' hasChild' : '',
			$hasChild ? $button : '',
			$input,
			$label,
			$items
		);
	}

	return $result;
};

echo $getTree($dirs);