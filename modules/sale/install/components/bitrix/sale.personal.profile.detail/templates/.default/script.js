BX.namespace('BX.Sale.PersonalProfileComponent');

(function() {
	BX.Sale.PersonalProfileComponent.PersonalProfileDetail = {
		init: function (params)
		{
			if (BX.type.isPlainObject(params))
				this.ajaxUrl = params.ajaxUrl;

			var propertyFileList = document.getElementsByClassName('sale-personal-profile-detail-property-file');
			Array.prototype.forEach.call(propertyFileList, function(propertyFile)
			{
				var deleteFileElement = propertyFile.getElementsByClassName('profile-property-input-delete-file')[0];
				var inputFile = propertyFile.getElementsByClassName('sale-personal-profile-detail-input-file')[0];
				var labelFileInfo = propertyFile.getElementsByClassName('sale-personal-profile-detail-load-file-info')[0];
				var cancelButton = propertyFile.getElementsByClassName('sale-personal-profile-detail-load-file-cancel')[0];

				BX.bindDelegate(propertyFile, 'click', { 'class': 'profile-property-check-file' }, BX.proxy(function(event)
				{
					if (deleteFileElement.value != "")
					{
						idList = deleteFileElement.value.split(';');
						if (idList.indexOf(event.target.value) === -1)
						{
							deleteFileElement.value = deleteFileElement.value + ";" + event.target.value;
						}
						else
						{
							idList.splice(idList.indexOf(event.target.value), 1);
							deleteFileElement.value = idList.join(";");
						}
					}
					else
					{
						deleteFileElement.value = event.target.value;
					}
				}, this));

				BX.bind(inputFile, 'change', BX.delegate(
					function(event)
					{
						if (event.target.files.length > 1)
						{
							labelFileInfo.innerHTML = BX.message('SPPD_FILE_COUNT') + event.target.files.length;
							cancelButton.classList.remove("sale-personal-profile-hide");
						}
						else if (event.target.files.length == 1)
						{
							fileName = event.target.files[0].name;
							if (fileName.length > 40)
							{
								labelFileInfo.innerHTML = fileName.substr(0,9) + "..." + fileName.substr(-9);
							}
							else
							{
								labelFileInfo.innerHTML = event.target.files[0].name;
							}
							cancelButton.classList.remove("sale-personal-profile-hide");
						}
						else
						{
							cancelButton.classList.add("sale-personal-profile-hide");
							labelFileInfo.innerHTML = BX.message('SPPD_FILE_NOT_SELECTED');
						}
					}, this)
				);

				var dateFileList = document.querySelectorAll('.sale-personal-profile-detail-form-date');
				for (var i=0; i < dateFileList.length; i++)
				{
					BX.bind(dateFileList[i], 'click', BX.delegate(function(e){
						parentBlock = e.target.parentNode;
						if (e.target.classList.contains('bx-calendar'))
						{
							parentBlock = parentBlock.parentNode;
						}

						var dateInputField = parentBlock.querySelector('input');
						if (!BX.type.isDomNode(dateInputField))
						{
							return;
						}
						BX.calendar({
							node: dateInputField,
							field: dateInputField.name,
							form: '',
							bTime: false,
							bHideTime: true
						});
					}, this));
				}

				BX.bind(cancelButton, 'click', BX.delegate(
					function()
					{
						cancelButton.classList.add("sale-personal-profile-hide");
						labelFileInfo.innerHTML = BX.message('SPPD_FILE_NOT_SELECTED');
						inputFile.value = "";
						inputFile.files = [];
					}, this)
				);
			});

			var multiLocationList = document.getElementsByClassName('input-add-multiple');
			for (var key in multiLocationList)
			{
				var hiddenLocation = multiLocationList[key];
				if (!BX.type.isDomNode(hiddenLocation) && BX.type.isNotEmptyString(this.ajaxUrl))
					continue;
				BX.bind(hiddenLocation, 'click', BX.delegate(
					function(event)
					{
						switch (event.target.getAttribute('data-add-type'))
						{
							case "LOCATION" : this.createLocationInput(event);
								break;
							case "TEXT" : this.createTextInput(event);
								break;
						}
					}, this)
				);
			}
		},
		createTextInput : function(event)
		{
			if (!BX.type.isDomNode(event.target))
				return;

			var newInput = BX.create('input',{attrs:{
				className: 'form-control',
				type: 'text',
				name: event.target.getAttribute('data-add-name')
			}});

			event.target.parentNode.insertBefore(newInput, event.target);
		},
		createLocationInput : function(event)
		{
			var newKey = parseInt(event.target.getAttribute('data-add-last-key')) + 1;
			BX.ajax(
				{
					method: 'POST',
					dataType: 'html',
					url: this.ajaxUrl,
					data:
						{
							sessid: BX.bitrix_sessid(),
							params: {
								LOCATION_NAME: event.target.getAttribute('data-add-name'),
								LOCATION_TEMPLATE: event.target.getAttribute('data-add-template'),
								LOCATION_KEY: newKey,
								ACTION: 'getLocationHtml'
							},
							signedParamsString: this.signedParams
						},
					onsuccess: BX.proxy(function(result)
					{
						var wrapper = BX.create("div");
						wrapper.innerHTML = result;
						event.target.parentNode.insertBefore(wrapper,event.target);
						event.target.setAttribute('data-add-last-key', newKey)
					},this),
					onfailure: BX.proxy(function()
					{
						return this;
					}, this)
				}, this
			);
		}
	}
})();