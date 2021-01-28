<?php
use Bitrix\Main\Localization\Loc;

/**
 * @var array $params
 */

Loc::loadMessages(__FILE__);
$messages = Loc::loadLanguageFile(__FILE__);
?>
<div class="mb-4" id="paysystem-orderdocument">
<?php
if (!empty($params['pdfUrl']))
{
	?>
	<p><?= Loc::getMessage('SALE_DOCUMENT_HANDLER_DOWNLOAD_DOCUMENT', ['#LINK#' => $params['pdfUrl']]); ?></p>
	<script>
		BX.ready(function() {
			window.location.href = '<?= CUtil::JSEscape($params['pdfUrl']) ?>'
		});
	</script>
	<?php
}
else
{
	\CJSCore::init(["loader", "documentpreview", "sidepanel"]);
	?>
	<div class="alert alert-success"><?= Loc::getMessage('SALE_DOCUMENT_HANDLER_WAIT_TRANSFORMATION'); ?></div>
	<script>
		<?php include_once 'script.js'; ?>
		BX.ready(function()
		{
			var options = <?=\CUtil::PhpToJSObject($params)?>;
			options.onReady = function(options)
			{
				BX.Sale.Orderdocument.init({
					paysystemBlockId: 'paysystem-orderdocument',
					ajaxUrl: '/bitrix/tools/sale_ps_ajax.php',
					paymentId: '<?= CUtil::JSEscape($params['PAYMENT_ID']) ?>',
					paySystemId: '<?= CUtil::JSEscape($params['PAYSYSTEM_ID']) ?>',
					template: '<?= CUtil::JSEscape(basename(__FILE__, '.php')) ?>',
				});
			};
			var preview = new BX.DocumentGenerator.DocumentPreview(options);
		});
	</script>
	<?php
	global $APPLICATION;
	$APPLICATION->IncludeComponent(
		"bitrix:pull.request",
		"",
		[],
		false,
		[
			"HIDE_ICONS" => "Y"
		]
	);
}
?>
</div>