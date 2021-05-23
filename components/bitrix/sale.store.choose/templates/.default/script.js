BX.namespace("BX.Sale.Store.Choose");

BX.Sale.Store.Choose =
{
	ajaxUrl: '',
	deliveryStores: {},
	index: '',
	mapType: '',
	inputId: '',

	setChangeStore: function(id, objName)
	{
		var store = this.deliveryStores[id],
			tbl = BX('store_table'+this.index),
			children = BX.findChildren(tbl, {'tag' : 'tr'}, true);

		for (var i in children)
			if (children[i].style.display != 'none')
				BX.hide(children[i]);

		var obRow = BX("row"+this.index+"_"+id);

		if (!!obRow)
			BX.show(obRow);

		var obStoreName = BX('store_name'+this.index);

		if (obStoreName)
			BX.html(obStoreName, BX.util.htmlspecialchars(store['TITLE']));

		if(this.mapType != 'none')
		{
			if (parseFloat(store["GPS_N"]) > 0 && parseFloat(store["GPS_S"]) > 0)
			{
				if (window.GLOBAL_arMapObjects[objName])
				{
					if(this.mapType == 'yandex')
						window.GLOBAL_arMapObjects[objName].panTo([parseFloat(store["GPS_N"]), parseFloat(store["GPS_S"])], {flying: 1});
					else if (this.mapType == 'google')
						window.GLOBAL_arMapObjects[objName].panTo(new google.maps.LatLng(parseFloat(store["GPS_N"]), parseFloat(store["GPS_S"])));
				}
			}
		}

		BX(this.inputId).value = id;
	},

	showFullInfo: function(obj)
	{
		var nextSibling = BX.findNextSibling(obj, {'tag' : 'div'});
		BX.toggle(nextSibling);
	},

	setChangeMapType: function (type)
	{
		if(type == 'google')
		{
			this.showDialog(
				BX.message('SALE_SSC_GOOGLE_MAP_INFO'),
				BX.message('SALE_SSC_GOOGLE_MAP_INFO_TITLE'),
				function () {
					this.sendChangeMapTypeAjax(type);
				}
			)
		}
		else
		{
			this.sendChangeMapTypeAjax(type);
		}
	},

	showDialog: function(text, title, callback)
	{
		var _this = this,
			dialog = new BX.PopupWindow(
			'adm-sale-order-alert-dialog',
			null,
			{
				autoHide: false,
				draggable: true,
				offsetLeft: 0,
				offsetTop: 0,
				bindOptions: { forceBindPosition: false },
				closeByEsc: true,
				closeIcon: true,
				titleBar: title || BX.message('SALE_ORDEREDIT_MESSAGE'),
				contentColor: 'white',
				content: BX.create(
					'span',
					{
						html: text,
						style: {backgroundColor: "white"}
					}
				)
			}
		);

		dialog.setButtons([
			new BX.PopupWindowButton(
				{
					text: BX.message('SALE_SSC_DIALOG_CLOSE'),
					className: "popup-window-button-link-cancel",
					events:
					{
						click : BX.delegate(
							function(){
								dialog.close();
								dialog.destroy();

								if(callback && typeof callback == 'function')
									callback.call(_this);
							}
							,
							dialog
						)
					}
				}
			)]);

		dialog.show();
	},

	sendChangeMapTypeAjax: function (type)
	{
		BX.ajax({
			timeout:    60,
			method:     'POST',
			dataType:   'json',
			url:        this.ajaxUrl,
			data:       {
				sessid: BX.bitrix_sessid(),
				action: 'set_map_type',
				map_type: type
			},

			onsuccess: function(result)
			{
				if(result)
				{
					if(!result.ERROR)
						window.location.reload();
					else
						alert(result.ERROR);
				}
				else
				{
					alert(BX.message('SALE_SSC_MAP_TYPE_CHANGE_ERROR'));
				}
			},

			onfailure: function(status)
			{
				BX.debug("Ajax onfailure. Status: "+ status);
			}
		});
	}
};
