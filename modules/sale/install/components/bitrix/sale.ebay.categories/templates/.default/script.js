;(function(window) {

	if (BX.Sale && BX.Sale.EbayCategories)
		return;

	if (!BX.Sale)
		BX.Sale = {};

	BX.Sale.EbayCategories = {

		isKeyCtrlShiftDown: false,
		ajaxUrl: "",
		categoriesSelectId: "",
		variationsBlockId: "",
		ebayVarSelectName: "",
		bitrixPropsSelectName: "",
		bitrixCategoryId: 0,
		iBlockId: 0,
		siteId: "",

		init: function(params)
		{
			BX.Sale.EbayCategories.ajaxUrl = params.ajaxUrl;
			BX.Sale.EbayCategories.categoriesSelectId = params.categoriesSelectId;
			BX.Sale.EbayCategories.variationsBlockId = params.variationsBlockId;
			BX.Sale.EbayCategories.ebayVarSelectName = params.ebayVarSelectName;
			BX.Sale.EbayCategories.bitrixPropsSelectName = params.bitrixPropsSelectName;
			BX.Sale.EbayCategories.siteId = params.siteId;
			BX.Sale.EbayCategories.bitrixCategoryId = params.bitrixCategoryId;
			BX.Sale.EbayCategories.iBlockId = params.iBlockId;
		},

		addEvent: function(elem, type, handler)
		{
			if (elem.addEventListener)
				elem.addEventListener(type, handler, false);
			else
				elem.attachEvent("on"+type, handler);

			return arguments.callee;
		},

		hideVariations: function()
		{
			var variationsBlock = BX(BX.Sale.EbayCategories.variationsBlockId);
			variationsBlock.style.display = "none";
		},

		showVariations: function()
		{
			var variationsBlock = BX(BX.Sale.EbayCategories.variationsBlockId);
			variationsBlock.style.display = "";
		},

		getVariations: function(categories)
		{
			var data = {
				'category': categories.value,
				'siteId': BX.Sale.EbayCategories.siteId,
				'action': 'get_variations_list',
				'sessid': BX.bitrix_sessid()
			};

			BX.showWait();

			BX.ajax({
				data: data,
				method: 'POST',
				dataType: 'json',
				url: BX.Sale.EbayCategories.ajaxUrl,
				onsuccess: function(result)
				{
					BX.closeWait();
					if(result)
					{
						if(!result.ERROR)
						{
							if(result.VARIATIONS_LIST)
								BX.Sale.EbayCategories.setVariations(categories.value, result.VARIATIONS_LIST);
						}
						else
						{
							BX.debug(result.ERROR);
						}
					}
					else
					{
						BX.debug("Error: getVariations");
					}
				},
				onfailure: function() { BX.debug('onfailure: getCategoriesList'); }
			});
		},

		setVariations: function(categoryName, variationsList)
		{
			var variationsBlock = BX(BX.Sale.EbayCategories.variationsBlockId);

			for(var i = 0, j = variationsBlock.children.length-2; i < j; i++)
				variationsBlock.removeChild(variationsBlock.children[0]);

			for(i = variationsBlock.firstElementChild.firstElementChild.options.length-1; i > 0; i--)
				variationsBlock.firstElementChild.firstElementChild.remove(i);

			for(i in variationsList)
			{
				var option = document.createElement("option");
				option.value = i;
				option.text = variationsList[i].NAME;
				variationsBlock.firstElementChild.firstElementChild.add(option);
			}

			variationsBlock.firstElementChild.firstElementChild.name = BX.Sale.EbayCategories.ebayVarSelectName + "[]";
			variationsBlock.firstElementChild.lastElementChild.name = BX.Sale.EbayCategories.bitrixPropsSelectName + "[]";

			if(variationsBlock.firstElementChild.firstElementChild.options.length > 1)
			{
				BX.Sale.EbayCategories.setRequiredVariations(variationsList);
				BX.Sale.EbayCategories.showVariations();
			}
			else
			{
				BX.Sale.EbayCategories.hideVariations();
			}
		},

		setRequiredVariations: function(variationsList)
		{
			var variationsBlock = BX(BX.Sale.EbayCategories.variationsBlockId);

			for(var j in variationsList)
			{
				if(variationsList[j].REQUIRED == "Y")
				{
					var newNode = variationsBlock.lastElementChild.previousElementSibling.cloneNode(true);
					newNode.firstElementChild.value = j;
					newNode.appendChild(BX.create("span", {html: BX.message("SALE_EBAY_SEC_REQUIRED"), style: {'color': 'red'}}));
					variationsBlock.insertBefore(newNode, variationsBlock.lastElementChild.previousElementSibling);
				}
			}
		},

		addEmptyVariation: function()
		{
			var variationsBlock = BX(BX.Sale.EbayCategories.variationsBlockId);
			var newNode = variationsBlock.lastElementChild.previousElementSibling.cloneNode(true);
			variationsBlock.insertBefore(newNode, variationsBlock.lastElementChild.previousElementSibling);
		},


		createCategoryProperty: function(iblockIds, obj)
		{
			var variationId = obj.previousElementSibling.previousElementSibling.value,
				variationName = obj.previousElementSibling.previousElementSibling.selectedOptions[0].text;

			var data = {
				'variationId': variationId,
				'action': 'get_variation_values',
				'sessid': BX.bitrix_sessid()
			};

			BX.showWait();

			BX.ajax({
				data: data,
				method: 'POST',
				dataType: 'json',
				url: BX.Sale.EbayCategories.ajaxUrl,
				onsuccess: function(result)
				{
					BX.closeWait();
					if(result)
					{
						if(!result.ERROR)
						{
							var iblocksCount = 0,
								iblockId = 0;

							for(iblockId in iblockIds)
								iblocksCount++;

							if(typeof result.VARIATION_VALUES != 'undefined')
							{
								if(iblocksCount > 1)
								{
									BX.Sale.EbayCategories.showIblockChooseDialog({
										iblockIds: iblockIds,
										variationName: variationName,
										values: result.VARIATION_VALUES
									});
								}
								else
								{
									if(!result.VARIATION_VALUES)
										result.VARIATION_VALUES = {};

									BX.Sale.EbayCategories.showCreatePropertyDialog({
										iblockId: iblockId,
										variationName: variationName,
										values: result.VARIATION_VALUES
									});
								}
							}
							else
							{
								BX.debug("Error: createCategoryProperty VARIATION_VALUES doesn't exist");
							}
						}
						else
						{
							BX.debug(result.ERROR);
						}
					}
					else
					{
						BX.debug("Error: createCategoryProperty");
					}
				},
				onfailure: function() {BX.debug('onfailure: createCategoryProperty');}
			});
		},

		showCreatePropertyDialog: function(params)
		{
			var ID = "n0",
				propValues = {},
				values = params.values;

			for(var i= 0, l=values.length; i<l-1; i++)
			{
				propValues[i*(-1)] = {
					'VALUE': values[i],
					'XML_ID': BX.translit(values[i],{
						change_case: 'U',
						replace_space: '_',
						max_len: 20
					})
				};
			}

			var arResult = {
				'sessid': BX.bitrix_sessid(),
				'PARAMS': {
					'PREFIX': "PREFIX_",
					'ID': ID,
					'IBLOCK_ID': params.iblockId,
					'TITLE': BX.message("SALE_EBAY_SEC_JS_CREATE_NEW_CATEGORY_PROP"),
					'RECEIVER': "obIBProps"
				},
				'PROP': {
					'NAME': params.variationName,
					'PROPERTY_TYPE': 'L',
					'ACTIVE': 'Y',
					'MULTIPLE': 'N',
					'SORT': '500',
					'IS_REQUIRED': 'N',
					'CODE': BX.translit(params.variationName,{
						change_case: 'U',
						replace_space: '_',
						max_len: 20
					})
				},
				'PROPERTY_VALUES': propValues
			};

			(new BX.CDialog({
				'title': BX.message("SALE_EBAY_SEC_JS_CREATE_NEW_CATEGORY_PROP"),
				'content_url': '/bitrix/admin/iblock_edit_property.php?lang='+BX.message('LANGUAGE_ID')+'&propedit='+ID+'&bxpublic=Y&receiver=obIBProps&return_url=section_edit',
				'content_post': arResult,
				'draggable': true,
				'resizable': true,
				'buttons': [BX.CDialog.btnSave, BX.CDialog.btnCancel]
			})).Show();
		},

		showIblockChooseDialog: function(params)
		{
			var selectId = "category_prop_iblock_choose";

			var btnOk = {
				title: BX.message("SALE_EBAY_SEC_JS_CONTINUE"),
				id: 'btnOk',
				name: 'btnOk',
				className: 'adm-btn-save',

				action: function () {
					this.parentWindow.Close();
					var select = BX(selectId);
					BX.Sale.EbayCategories.showCreatePropertyDialog({
						iblockId: select.value,
						variationName: params.variationName,
						values: params.values
					})
				}
			};

			var btnCancel = {
				title: BX.message("SALE_EBAY_SEC_JS_CANCEL"),
				id: 'btnCancel',
				name: 'btnCancel',

				action: function () {
					this.parentWindow.Close();
				}
			};

			var content = '<select name="category_prop_iblock_choose" id="category_prop_iblock_choose">';

			for(var i in params.iblockIds)
				content += '<option value="'+i+'">'+params.iblockIds[i]+'</option>';

			content += '</select>';

			this.dialogWindow = new BX.CDialog({
				title: BX.message("SALE_EBAY_SEC_JS_PROP_KIND"),
				content: content,
				resizable: false,
				height: 200,
				width: 400,
				buttons: [ btnOk, btnCancel]
			});

			this.dialogWindow.adjustSizeEx();
			this.dialogWindow.Show();
		},

		linkPropertyToCategory: function(bitrixCategoryId, properyId)
		{
			var data = {
				'bitrixCategoryId': bitrixCategoryId,
				'properyId': properyId,
				'action': 'set_category_property_link',
				'sessid': BX.bitrix_sessid()
			};

			BX.showWait();

			BX.ajax({
				data: data,
				method: 'POST',
				dataType: 'json',
				url: BX.Sale.EbayCategories.ajaxUrl,
				onsuccess: function(result)
				{
					if(result)
					{
						if(!result.ERROR)
						{
							//	window.location.reload();
							var d = BX .findChild(document, {attribute: {'name': 'apply'}}, true );
							if (d)
								d.click();
						}
						else
							BX.debug(result.ERROR);
					}
					else
					{
						BX.debug("Error: linkPropertyToCategory");
					}
				},
				onfailure: function() { BX.debug('onfailure: linkPropertyToCategory'); }
			});
		},

		deleteChildrenCategoriesSelects: function(categorySelect)
		{
			var nextSibling;
			while(nextSibling = categorySelect.parentNode.nextElementSibling)
				nextSibling.parentNode.removeChild(nextSibling);
		},

		createChildCategorySelect: function(categoryChildren, level)
		{
			var	newId = "sale_ebay_category_"+level;
			var childSelectNode = BX.create('SELECT', {props: {id: newId, name: newId}, attrs: {'onchange': "BX.Sale.EbayCategories.onCategoryChange(this, "+level+");"}}),
				oOption = BX.create('OPTION');

			childSelectNode.appendChild(oOption);

			for(var i in categoryChildren)
			{
				oOption = BX.create('OPTION');
				oOption.appendChild(document.createTextNode(categoryChildren[i].NAME));
				oOption.setAttribute("value", i);
				childSelectNode.appendChild(oOption);
			}

			return childSelectNode;
		},

		onCategoryChange: function(ebayCategorySelect, level)
		{
			var ebayCategoryId = ebayCategorySelect.value,
				categoryInput = BX("SALE_EBAY_CATEGORY_ID");

			categoryInput.value = ebayCategoryId;

			if(!ebayCategoryId && level == 1)
			{
				BX.Sale.EbayCategories.hideVariations();
				BX.Sale.EbayCategories.deleteCategoryMap();
			}
			else
			{
				BX.Sale.EbayCategories.getCategoryChildren(ebayCategorySelect, level);
			}
		},

		deleteCategoryMap: function()
		{
			if(!BX.Sale.EbayCategories.bitrixCategoryId)
				return;

			var data = {
				'bitrixCategoryId': BX.Sale.EbayCategories.bitrixCategoryId,
				'iBlockId': BX.Sale.EbayCategories.iBlockId,
				'action': 'delete_category_map',
				'sessid': BX.bitrix_sessid()
			};

			BX.showWait();

			BX.ajax({
				data: data,
				method: 'POST',
				dataType: 'json',
				url: BX.Sale.EbayCategories.ajaxUrl,
				onsuccess: function(result)
				{
					if(result)
					{
						if(!result.ERROR)
						{
							window.location.reload(true);
						}
						else
						{
							BX.debug(result.ERROR);
						}
					}
					else
					{
						BX.debug("Error: deleteCategoryMap");
					}
				},
				onfailure: function() { BX.debug('onfailure: deleteCategoryMap'); }
			});
		},

		getCategoryChildren: function(ebayCategorySelect, level)
		{
			var ebayCategoryId = ebayCategorySelect.value,
				categoryInput = BX("SALE_EBAY_CATEGORY_ID");

			categoryInput.value = ebayCategoryId;

			if(!ebayCategoryId)
			{
				BX.Sale.EbayCategories.hideVariations();
				return;
			}

			BX.Sale.EbayCategories.deleteChildrenCategoriesSelects(ebayCategorySelect);

			var data = {
				'ebayCategoryId': ebayCategoryId,
				'action': 'get_category_children',
				'sessid': BX.bitrix_sessid()
			};

			BX.showWait();

			BX.ajax({
				data: data,
				method: 'POST',
				dataType: 'json',
				url: BX.Sale.EbayCategories.ajaxUrl,
				onsuccess: function(result)
				{
					BX.closeWait();
					if(result)
					{
						if(!result.ERROR)
						{
							if(result.CATEGORY_CHILDREN)
							{
								var newSelect = BX.Sale.EbayCategories.createChildCategorySelect(result.CATEGORY_CHILDREN, level+1);
								var newDiv = BX.create('DIV', {style:{'padding-top': '10px'}});
								newDiv.appendChild(newSelect);
								ebayCategorySelect.parentElement.parentElement.appendChild(newDiv);
							}

							BX.Sale.EbayCategories.getVariations(ebayCategorySelect);
						}
						else
						{
							BX.debug(result.ERROR);
						}
					}
					else
					{
						BX.debug("Error: getCategoryChildren");
					}
				},
				onfailure: function() { BX.debug('onfailure: getCategoryChildren'); }
			});
		}
	};

})(window);
