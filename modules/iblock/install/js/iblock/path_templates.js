if(!window.IBlockComponentProperties)
{
	window.IBlockComponentProperties = function(arParams)
	{
		window.IBlockComponentPropertiesObj.ShowMenu(arParams);
	};

	window.IBlockComponentPropertiesObj =
	{
		ShowMenu: function(arParams)
		{
			if(arParams.data.substr(0, 3) == '[\\\'')
				arParams.data = arParams.data.replace(/\\'/g, '\'');
			var menu = eval(arParams.data);

			var oButton = document.createElement("INPUT");
			oButton.setAttribute('type', 'button');
			oButton.value = '...';
			arParams.oCont.insertBefore(oButton, arParams.oCont.firstChild);

			var oInput = document.createElement("INPUT");
			oInput.setAttribute('type', 'text');
			oInput.setAttribute('size', '30');
			oInput.setAttribute('id', menu[0] + '_input');
			oInput.value = arParams.oInput.value;
			arParams.oCont.insertBefore(oInput, arParams.oCont.firstChild);
			oInput.onchange = function()
			{
				arParams.oInput.value = oInput.value;
				if(arParams.oInput.onchange)
					arParams.oInput.onchange();
			};

			oInput.onblur = function()
			{
				arParams.oInput.value = oInput.value;
				if(arParams.oInput.onchange)
					arParams.oInput.onchange();
			};

			window.IBlockComponentPropertiesObj[menu[0]] = new BX.COpener({
				DIV: oButton,
				MENU: menu[2],
				TYPE: 'click',
				ACTIVE_CLASS: 'adm-btn-active',
				CLOSE_ON_CLICK: true
			});
		},

		Action: function(id, menu_id)
		{
			var el = BX(menu_id + '_input');
			el.focus();

			var val = el.value, endIndex, range;
			if (typeof el.selectionStart != "undefined" && typeof el.selectionEnd != "undefined") {
				endIndex = el.selectionEnd;
				el.value = val.slice(0, el.selectionStart) + id + val.slice(endIndex);
				el.selectionStart = el.selectionEnd = endIndex + id.length;
			} else if (typeof document.selection != "undefined" && typeof document.selection.createRange != "undefined") {
				el.focus();
				range = document.selection.createRange();
				range.collapse(false);
				range.text = id;
				range.select();
			}

			BX.fireEvent(el, 'change');
			el.focus();
		}
	}
}