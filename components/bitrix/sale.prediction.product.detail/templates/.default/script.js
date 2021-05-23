(function (window) {
})(window);

function bx_sale_prediction_product_detail_load(injectId, localAjaxData, additionalData)
{
	localAjaxData = localAjaxData || {};
	additionalData = additionalData || {};

	BX.ajax({
		url: '/bitrix/components/bitrix/sale.prediction.product.detail/ajax.php',
		method: 'POST',
		data: BX.merge(localAjaxData, additionalData),
		dataType: 'html',
		processData: false,
		start: true,
		onsuccess: function (html) {
			var ob = BX.processHTML(html);

			// inject
			BX(injectId).innerHTML = ob.HTML;
			BX.ajax.processScripts(ob.SCRIPT);
		}
	});
}