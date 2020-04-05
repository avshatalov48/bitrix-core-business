<?php

namespace Bitrix\Conversion\AdminHelpers;

use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

function renderFilter(array $filter)
{
	?>
	<div class="adm-detail-toolbar">

		<div class="adm-detail-toolbar-right">
			<span class="adm-profit-filter"><?=Loc::getMessage('CONVERSION_FILTER_PERIOD')?>:</span>
			<div class="adm-input-wrap adm-input-wrap-calendar">
				<input id="bitrix-conversion-from" class="adm-input adm-input-calendar" type="text" name="from" value="<?=htmlspecialcharsbx($filter['from']) ?>">
				<span class="adm-calendar-icon" title="<?=Loc::getMessage("admin_lib_calend_title")?>" onclick="BX.calendar({node:this, field:'from', form: '', bTime: 'false', bHideTime: false});"></span>
			</div>
			<div class="adm-input-wrap adm-input-wrap-calendar">
				<input id="bitrix-conversion-to" class="adm-input adm-input-calendar" type="text" name="to" value="<?=htmlspecialcharsbx($filter['to']) ?>">
				<span class="adm-calendar-icon" title="<?=Loc::getMessage("admin_lib_calend_title")?>" onclick="BX.calendar({node:this, field:'to', form: '', bTime: 'false', bHideTime: false});"></span>
			</div>
			<span class="adm-btn" onclick="

				window.location.href = '?<?=http_build_query(array_diff_key($filter, array('from' => 1, 'to' => 1)))?>'
				+ '&from=' + BX('bitrix-conversion-from').value
				+ '&to='   + BX('bitrix-conversion-to'  ).value

				"><?=Loc::getMessage('CONVERSION_FILTER_APPLY')?></span>
		</div>
	</div>
	<?
}

function renderMenu($id, $items)
{
	?>
	<script>
		BX('<?=$id?>').onclick = function()
		{
			this.blur();

			BX.adminShowMenu(this,
				<?

				$json = array();

				foreach ($items as $name => $params)
				{
					$json []= array(
						'TEXT'    => $name,
						'ONCLICK' => "window.location.href = '?".http_build_query($params)."'",
					);
				}

				echo Json::encode($json);

				?>
				, {active_class: 'adm-btn-save-active'});

			return false;
		};
	</script>
	<?
}

function renderSite($siteName, array $siteMenu)
{
	?>
	<div class="adm-profit-title">
		<?=Loc::getMessage('CONVERSION_SITE')?>:
		<span id="bitrix-conversion-site" class="adm-profit-title-name"><?=htmlspecialcharsbx($siteName) ?></span>
		<span class="adm-profit-title-name-select"></span>
		<?renderMenu('bitrix-conversion-site', $siteMenu)?>
	</div>
	<?
}

function renderScale(array $param)
{
	?>
	<div class="adm-profit-scale-block">

		<?renderSite($param['SITE_NAME'], $param['SITE_MENU'])?>

		<br>

		<div class="adm-profit-scale">
			<div class="adm-profit-scale-part adm-profit-scale-part-1">
				<div class="adm-profit-scale-edge"></div>
				<div class="adm-profit-scale-inner"></div>
				<div class="adm-profit-scale-title"><?=Loc::getMessage('CONVERSION_SCALE_BAD')?></div>
			</div>
			<div class="adm-profit-scale-part adm-profit-scale-part-2">
				<div class="adm-profit-scale-inner"></div>
				<div class="adm-profit-scale-title"><?=Loc::getMessage('CONVERSION_SCALE_PASSABLE')?></div>
			</div>
			<div class="adm-profit-scale-part adm-profit-scale-part-3">
				<div class="adm-profit-scale-inner"></div>
				<div class="adm-profit-scale-title"><?=Loc::getMessage('CONVERSION_SCALE_OK')?></div>
			</div>
			<div class="adm-profit-scale-part adm-profit-scale-part-4">
				<div class="adm-profit-scale-inner"></div>
				<div class="adm-profit-scale-title"><?=Loc::getMessage('CONVERSION_SCALE_GOOD')?></div>
			</div>
			<div class="adm-profit-scale-part adm-profit-scale-part-5">
				<div class="adm-profit-scale-edge"></div>
				<div class="adm-profit-scale-inner"></div>
				<div class="adm-profit-scale-title"><?=Loc::getMessage('CONVERSION_SCALE_EXCELLENT')?></div>
			</div>
			<div class="adm-profit-scale-shadow"></div>
			<div class="adm-profit-scale-separation">
				<div class="adm-profit-scale-num-l">0%</div>
				<div class="adm-profit-scale-num-r"><?=end($param['SCALE'])?>%</div>
				<div class="adm-profit-scale-separation-inner">
					<?

					$conversion = $param['CONVERSION'];

					$shift = 100;

					$min = 0;

					foreach ($param['SCALE'] as $i => $max)
					{
						if ($conversion == $max)
						{
							$shift = ($i + 1) * 20;
							break;
						}
						elseif ($conversion < $max)
						{
							$shift = ($i * 20) + (($conversion - $min) * 20 / ($max - $min)); // TODO simplify
							break;
						}

						$min = $max;
					}

					?>
					<div id="conversion-scale-shift" class="adm-profit-scale-value" style="left: <?=$shift?>%">
						<div id="conversion-scale-conversion" class="adm-profit-scale-value-num"><?=number_format($conversion, 2)?>%</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="adm-description">
		<span class="adm-description-title"><?=Loc::getMessage('CONVERSION_DESCRIPTION_TITLE')?></span>
		<span class="adm-clarification"><?=Loc::getMessage('CONVERSION_DESCRIPTION')?></span>
	</div>
	<?
}

