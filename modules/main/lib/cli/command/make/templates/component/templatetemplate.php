<?php

namespace Bitrix\Main\Cli\Command\Make\Templates\Component;

use Bitrix\Main\Cli\Helper\Renderer\Template;

final class TemplateTemplate implements Template
{
	public function __construct(
		private readonly string $componentTitlePhrase,
		private readonly string $containerId,
		private readonly string $extensionName,
		private readonly string $extensionClass,
	)
	{}

	public function getContent(): string
	{
		return <<<PHP
<?php

use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array \$arParams
 * @var array \$arResult
 * @var CMain \$APPLICATION
 * @var CBitrixComponent \$component
 * @var CBitrixComponentTemplate \$this
 */
 
\$APPLICATION->SetTitle(Loc::getMessage('{$this->componentTitlePhrase}'));

// load your js extension (https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=12435)
//Extension::load('{$this->extensionName}');

?>

<div id="{$this->containerId}" class="{$this->containerId}"></div>

<!-- Example -->
Example of using params:<br>
Safe: <?= \$arParams['USERNAME'] ?> and <?= htmlspecialcharsbx(\$arParams['~USERNAME']) ?>

<script>
BX.ready(() => {
	const container = document.getElementById('{$this->containerId}');
	const fruits = <?= Json::encode(\$arResult['FRUITS']) ?>;

	// render extension inside container
	// new {$this->extensionClass}({
	// 	container,
	// 	fruits,
	// });
});
</script>

PHP;
	}
}
