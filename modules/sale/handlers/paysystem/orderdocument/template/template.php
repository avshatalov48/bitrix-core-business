<html>
	<head>
		<?php
		/** @global CMain $APPLICATION */
		$APPLICATION->ShowHeadStrings();
		$APPLICATION->ShowHeadScripts();
		?>
	</head>
	<body>
		<?php
		/** @var array $params */

		use Bitrix\Main\Localization;

		Localization\Loc::loadMessages(__FILE__);

		if (isset($params['imageUrl']) && $params['imageUrl'])
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
					'IFRAME' => (isset($params['IFRAME']) && $params['IFRAME'] === 'Y') ? 'Y' : 'N',
					'WIDTH' => 1000,
					'HEIGHT' => 1200,
					'PRINT' => (isset($params['PRINT']) && $params['PRINT'] === 'Y') ? 'Y' : 'N',
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
			<?php
			$APPLICATION->IncludeComponent("bitrix:pull.request", "", [], false, ["HIDE_ICONS" => "Y"]);
			?>
		<?php
		}
		?>
	</body>
</html>
