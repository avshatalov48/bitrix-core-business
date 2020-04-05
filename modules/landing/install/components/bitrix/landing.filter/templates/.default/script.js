var landingAjaxSend = false;

BX.ready(function()
{
	BX.addCustomEvent('BX.Main.Filter:apply', function()
	{
		if (landingAjaxSend)
		{
			return;
		}
		landingAjaxSend = true;

		var loading = BX.create('div',{
			props: {
				className: 'landing-filter-loading'
			}
		});
		var workArea = BX('workarea-content');
		var timeout = setTimeout(function()
		{
			workArea.appendChild(loading);
		}, 100);

		BX.ajax({
			method: 'POST',
			dataType: 'html',
			url: landingAjaxPath,
			onsuccess: function(data)
			{
				landingAjaxSend = false;
				clearTimeout(timeout);
				workArea.innerHTML = data;
			}
		});
	});
});