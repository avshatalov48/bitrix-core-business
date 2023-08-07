<?php

use Bitrix\Main\Grid\Column\Type;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 */

?>
<html>
<head>
	<title><?= $APPLICATION->GetTitle() ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset='<?= LANG_CHARSET ?>'">
	<style>
		td {mso-number-format:\@;}
		.number0 {mso-number-format:0;}
		.number2 {mso-number-format:Fixed;}
	</style>
</head>
<body>
	<table border="1">
		<!-- columns -->
		<tr>
			<?php foreach($arResult['COLUMNS'] as $col): ?>
				<th><?= htmlspecialcharsbx($col['name']) ?></th>
			<?php endforeach; ?>
		</tr>

		<!-- rows -->
		<?php foreach($arResult['ROWS'] as $row): ?>
			<?php
			if ($row['id'] === 'template_0')
			{
				continue;
			}
			?>
			<tr>
				<?php foreach($arResult['COLUMNS'] as $col): ?>
					<?php
					$id = $col['id'];
					$type = $col['type'] ?? null;
					$value = $row['columns'][$id] ?? null;

					if ($type === Type::CHECKBOX && in_array($value, ['Y', 'N'], true))
					{
						$html =
							$value === 'Y'
								? Loc::getMessage('interface_grid_yes')
								: Loc::getMessage('interface_grid_no')
						;
					}
					elseif ($type === Type::LABELS && is_array($value))
					{
						$parts = [];

						foreach ($value as $label)
						{
							if (isset($label['html']) && is_string($label['html']))
							{
								$parts[] = $label['html'] . ', ';
							}
							elseif (isset($label['text']))
							{
								$parts[] = htmlspecialcharsbx($label['text']) . ', ';
							}
						}

						$html = join(', ', $parts);
					}
					elseif ($type === Type::TAGS && isset($value['items']) && is_array($value['items']))
					{
						$parts = [];

						foreach ($value['items'] as $tag)
						{
							if (isset($tag['html']) && is_string($tag['html']))
							{
								$parts[] = $tag['html'] . ', ';
							}
							elseif (isset($tag['text']))
							{
								$parts[] = htmlspecialcharsbx($tag['text']) . ', ';
							}
						}

						$html = join(', ', $parts);
					}
					else
					{
						$html = $value;
					}
					?>
					<td><?= $html ?></td>
				<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
	</table>
</body>
</html>
