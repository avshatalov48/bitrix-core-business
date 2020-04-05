<html>
	<head>
		<?$APPLICATION->ShowHeadStrings();?>
		<?$APPLICATION->ShowHeadScripts();?>
	</head>
	<body>
		<?php

		use Bitrix\Main\Localization;

		Localization\Loc::loadMessages(__FILE__);

		if ($params['imageUrl'])
		{
			if (isset($_REQUEST['pdf'])
				&& isset($params['pdfUrl'])
				&& isset($_REQUEST['DOWNLOAD'])
				&& $_REQUEST['DOWNLOAD'] === 'Y'
			)
			{
				LocalRedirect($params['pdfUrl']);
			}
			else
			{
				$APPLICATION->IncludeComponent('bitrix:pdf.viewer', '', [
					'PATH' => $params['pdfUrl'],
					'IFRAME' => 'Y',
					'WIDTH' => 1000,
					'HEIGHT' => 1200,
					'PRINT' => 'Y'
				]);
			}
		}
		else
		{
			\CJSCore::init(["loader", "documentpreview", "sidepanel"]);
		?>
			<h2>
				<?=Localization\Loc::getMessage('SALE_DOCUMENT_HANDLER_WAIT_TRANSFORMATION_2');?>
			</h2>
			<script>
				BX.ready(function()
				{
					var options = <?=\CUtil::PhpToJSObject($params)?>;
					options.onReady = function(options)
					{
						location.reload();
					};
					var preview = new BX.DocumentGenerator.DocumentPreview(options);
				});
			</script>
			<?
			$APPLICATION->IncludeComponent("bitrix:pull.request", "", [], false, ["HIDE_ICONS" => "Y"]);
			?>
		<?
		}
		?>
	</body>
</html>
