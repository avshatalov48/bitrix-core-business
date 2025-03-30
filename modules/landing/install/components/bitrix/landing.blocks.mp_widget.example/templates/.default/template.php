<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */

/** @var array $arResult */

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$title = \htmlspecialcharsbx($arResult['TITLE']);
$isShowEmptyState = $arResult['SHOW_EMPTY_STATE'];
?>

<div class="landing-widget-birthdays">
	<p>
		<?= $title ?>:
	</p>

	<?php
	if ($isShowEmptyState)
	{
		echo Loc::getMessage('BLOCK_MP_WIDGET_EXAMPLE_EMPTY_STATE_TEXT');
	}

	else
	{
		foreach ($arResult['USERS'] ?? [] as $user)
		{
			echo '<p>';
			echo "User {$user['ID']} ({$user['LOGIN']}): {$user['NAME']} {$user['LAST_NAME']}";
			echo " &mdash; {$user['PERSONAL_BIRTHDAY']}";
			echo '</p>';
		}
	}
	?>
</div>
