;(function(window) {

	if (!BX.Sale)
		BX.Sale = {};

	if (!BX.Sale.EbayAdmin)
		BX.Sale.EbayAdmin = {};

	BX.Sale.EbayAdmin = {

		ajaxUrl: '/bitrix/admin/sale_ebay_ajax.php',

		startFeed: function(feedType, siteId, startPos)
		{
			BX.showWait();

			var postData = {
				action: "startFeed",
				type: feedType,
				siteId: siteId,
				sessid: BX.bitrix_sessid()
			};

			if(startPos)
				postData.startPos = startPos;

			BX.ajax({
				timeout:   120,
				method:   'POST',
				dataType: 'json',
				url:       BX.Sale.EbayAdmin.ajaxUrl,
				data:      postData,

				onsuccess: function(result)
				{
					BX.closeWait();

					if(result && result.COMPLETED)
					{
						alert(BX.message("SALE_EBAY_EXCHANGE_OK"));
					}
					else if(result && result.ERROR)
					{
						alert(BX.message("SALE_EBAY_EXCHANGE_ERROR")+".\n"+result.ERROR);
					}
					else if(result)
					{
						var endPos = result.END_ROW || 0;
						BX.Sale.EbayAdmin.startFeed(feedType, endPos);
					}
					else
					{
						alert(BX.message("SALE_EBAY_EXCHANGE_ERROR"));
					}
				},

				onfailure: function()
				{
					BX.debug('Feed failure!');
				}
			});
		},

		addIblockSelect: function()
		{
			var node = BX("SALE_EBAY_IBLOCK_CHOOSE").lastElementChild.cloneNode(true);
			BX("SALE_EBAY_IBLOCK_CHOOSE").appendChild(node);

			if(node.firstElementChild.options["0"])
				node.firstElementChild.value="0";

			node.firstElementChild.name =  node.firstElementChild.id = BX.Sale.EbayAdmin.iblockSelectNameIncrement(node.firstElementChild.name);
			node.firstElementChild.setAttribute('onchange', BX.Sale.EbayAdmin.iblockSelectNameIncrement(node.firstElementChild.getAttribute('onchange')));

			if(node.firstElementChild.options["0"])
				node.firstElementChild.value="0";

			node.lastElementChild.name = node.lastElementChild.id = BX.Sale.EbayAdmin.iblockSelectNameIncrement(node.lastElementChild.name);
		},

		iblockSelectNameIncrement: function(str)
		{
			if(!str || !str.replace)
				return;

			return  str.replace(/(.*)\[(\d+)\](.*)/,'$1[$21]$3');
		},

		refreshCategoriesData: function(siteId)
		{
			BX.showWait();

			var postData = {
				action: "refreshCategoriesData",
				siteId: siteId,
				sessid: BX.bitrix_sessid()
			};

			BX.ajax({
				timeout:   300,
				method:   'POST',
				dataType: 'json',
				url:       BX.Sale.EbayAdmin.ajaxUrl,
				data:      postData,

				onsuccess: function(result)
				{
					BX.closeWait();

					if(result && result.COUNT)
					{
						alert('Refreshed '+result.COUNT+' categories.');
					}
					else if(result && result.ERROR)
					{
						alert(result.ERROR);
					}
					else
					{
						BX.debug('BX.Sale.EbayAdmin.refreshCategoriesData error!');
					}
				},

				onfailure: function()
				{
					BX.debug('BX.Sale.EbayAdmin.refreshCategoriesData failure!');
				}
			});
		},

		refreshCategoriesPropsData: function(siteId)
		{
			BX.showWait();

			var postData = {
				action: "refreshCategoriesPropsData",
				siteId: siteId,
				sessid: BX.bitrix_sessid()
			};

			BX.ajax({
				timeout:   120,
				method:   'POST',
				dataType: 'json',
				url:       BX.Sale.EbayAdmin.ajaxUrl,
				data:      postData,

				onsuccess: function(result)
				{
					BX.closeWait();

					if(result && result.COUNT)
					{
						alert('Refreshed properties for '+result.COUNT+' categories.');
					}
					else if(result && result.ERROR)
					{
						alert(result.ERROR);
					}
					else
					{
						BX.debug('BX.Sale.EbayAdmin.refreshCategoriesPropsData error!');
					}
				},

				onfailure: function()
				{
					BX.debug('BX.Sale.EbayAdmin.refreshCategoriesPropsData failure!');
				}
			});
		},

		setOpenerFieldsFromHash: function(messageType)
		{
			var result = true,
				jsonString = "{";

			if(window.location.hash)
			{
				var splitted = window.location.hash.substring(1).split("&");

				for(var i in splitted)
				{
					if(!splitted.hasOwnProperty(i))
						continue;

					var keyValue = splitted[i].split("=");

					if(!keyValue)
						continue;

					var res = BX.Sale.EbayAdmin.setOpenerFieldFromHash(keyValue[0], keyValue[1]);
					result = result && res;

					if(jsonString != "{")
						jsonString +=", ";

					jsonString += '"'+keyValue[0]+'":"'+keyValue[1]+'"';
				}
			}

			if(jsonString != "{")
				jsonString +=", ";

			jsonString +='"messageType":"'+messageType+'"}';

			if(parent.window.opener)
				parent.window.opener.postMessage(jsonString, window.location.origin);

			window.addEventListener(
				"message",
				function(event){
					if(event.data == "MESSAGE_RECEIVED")
						window.close();
				},
				false
			);

			return result;
		},

		setOpenerFieldFromHash: function(key, value)
		{
			var fieldId = "SALE_EBAY_SETTINGS_"+key,
				node = null,
				opener = false;

			if(parent.window.opener !== null)
			{
				try
				{
					node = parent.window.opener.document.getElementById(fieldId);
					opener = true;
				}
				catch (e){}
			}

			if(!node)
				node = BX(fieldId);

			if(node)
			{
				value = decodeURIComponent(value);

				if(node.type == "text")
					node.value = value;
				else if(node.type == "textarea")
					node.value = value;
			}

			return opener;
		},

		showAlertOpener: function(message)
		{
			if(parent.window.opener !== null)
			{
				try
				{
					parent.window.opener.alert(message);
					return true;
				}
				catch(e){}
			}

			window.alert(message);
			return false;
		},

		addSftpTokenEventListener: function(params, submit)
		{
			window.addEventListener(
				"message",
				function(event)
				{
					if (event.origin == window.location.origin
						|| event.origin == 'http://www.1c-bitrix.ru.smn'
						|| event.origin == 'https://www.1c-bitrix.ru'
					)
					{
						var tokenInput = BX("SALE_EBAY_SETTINGS_SFTP_TOKEN"),
							tokenExp = BX("SALE_EBAY_SETTINGS_SFTP_TOKEN_EXP"),
							data = JSON.parse(event.data);

						if(!data.messageType || data.messageType != "SFTP_TOKEN")
							return;

						if(tokenExp && data.SFTP_TOKEN_EXP)
							tokenExp.value = decodeURIComponent(data.SFTP_TOKEN_EXP);

						if(tokenInput && data.SFTP_TOKEN)
							tokenInput.value = decodeURIComponent(data.SFTP_TOKEN);

						if((data.SFTP_ACCOUNT_STATE == "ACTIVE" || data.SFTP_ACCOUNT_STATE == "SUBSCRIBED") && data.SFTP_TOKEN != "")
						{
							alert(params.messageOk);

							if(params.submit && tokenExp && tokenExp.form)
								tokenExp.form.submit();
						}
						else
						{
							alert(params.messageError);
						}

						event.source.postMessage("MESSAGE_RECEIVED", event.origin);
					}
				},
				false
			);
		},

		addApiTokenListener: function(params)
		{
			window.addEventListener(
				"message",
				function(event)
				{
					if (event.origin == window.location.origin
						|| event.origin == 'http://www.1c-bitrix.ru.smn'
						|| event.origin == 'http://www.1c-bitrix.ru'
					)
					{
						var tokenArea = BX("SALE_EBAY_SETTINGS_API_TOKEN"),
							tokenExpInp = BX("SALE_EBAY_SETTINGS_API_TOKEN_EXP"),
							data = JSON.parse(event.data);

						if(!data.messageType || data.messageType != "API_TOKEN")
							return;

						if(tokenExpInp && data.API_TOKEN_EXP)
							tokenExpInp.value = decodeURIComponent(data.API_TOKEN_EXP);

						if(tokenArea && data.API_TOKEN)
						{
							tokenArea.value = decodeURIComponent(data.API_TOKEN);
							event.source.postMessage("MESSAGE_RECEIVED", event.origin);
							alert(params.messageOk);
						}
						else
						{
							alert(params.messageError);
						}
					}
				},
				false
			);
		}
	};

})(window);