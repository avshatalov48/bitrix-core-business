<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Location\Admin\Helper;
use Bitrix\Sale\Location\Import\ImportProcess;

/** @global CMain $APPLICATION */

const NO_AGENT_CHECK = true;
const NO_KEEP_STATISTIC = true;

$initialTime = time();
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sale/prolog.php';

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$APPLICATION->SetTitle(Loc::getMessage('SALE_LOCATION_IMPORT_TITLE'));

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

// check for indexes
$indexes = ImportProcess::getIndexMap();
$absent = [];
if (!empty($indexes) && is_array($indexes))
{
	foreach ($indexes as $name => $params)
	{
		if ((string)$params['TABLE'] !== '' && !($params['DROP_ONLY'] ?? false))
		{
			if (!\Bitrix\Sale\Location\DB\Helper::checkIndexNameExists($name, $params['TABLE']))
			{
				$absent[] =
					'create index ' . $name . ' on ' . $params['TABLE']
					. ' (' . implode(', ', $params['COLUMNS']) . ')'
					. \Bitrix\Sale\Location\DB\Helper::getQuerySeparatorSql()
				;
			}
		}
	}
}

if (!empty($absent) && !$adminSidePanelHelper->isPublicSidePanel())
{
	?>
	<span style="color: #ff0000">
		<?= Loc::getMessage(
			'SALE_LOCATION_IMPORT_NO_INDEXES_WARNING',
			[
				'#ANCHOR_SQL_CONSOLE#' => '<a href="/bitrix/admin/sql.php" target="_blank">',
				'#ANCHOR_END#' => '</a>',
			]
		); ?>
	</span>
	<br />
	<br />
	<pre>
<?= (implode("\n", $absent));?>
	</pre>
	<?php
}
else
{
	$APPLICATION->IncludeComponent(
		'bitrix:sale.location.import',
		'admin',
		[
			'PATH_TO_IMPORT' => Helper::getImportUrl(),
			'INITIAL_TIME' => time(),
		],
		false,
		['HIDE_ICONS' => 'Y']
	);
}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
