(function ()
{
	'use strict';

	BX(function ()
	{
		BX.addCustomEvent('onFormFrameLoad', t);
	});

	function t(e)
	{
		// debugger;
		// console.log(e.type + '_' + e.id);
		setTimeout(function(){
			console.log("oppa");
			Bitrix24FormLoader.setFrameHeight(e.type + '_' + e.id, 660)
		}, 5000);

		// var f = e.node.querySelector('iframe');
		// console.log("f", f);
		// console.log("t");

		// this.iframe = form.iframe;
		// this.sendFrameMessage({'options': this.formOptions}, uniqueLoadId);
	}
})();