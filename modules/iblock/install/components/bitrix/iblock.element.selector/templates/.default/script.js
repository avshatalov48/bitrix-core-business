BX.namespace('BX.Iblock');

BX.Iblock.IblockElementSelector = (function ()
{
	var IblockElementSelector = function(parameters)
	{
		this.jsObject = parameters.jsObject;
		this.selectorId = parameters.selectorId;
		this.searchInputId = parameters.searchInputId;
		this.panelSelectedValues = parameters.panelSelectedValues === 'Y';
		this.popup = parameters.popup === 'Y';
		this.multiple = parameters.multiple === 'Y';
		this.iblockId = parameters.iblockId;
		this.onChange = parameters.onChange;
		this.onSelect = parameters.onSelect;
		this.onUnSelect = parameters.onUnSelect;
		this.currentElements = parameters.currentElements;
		this.lastElements = parameters.lastElements;
		this.inputName = parameters.inputName;
		this.onlyRead = parameters.onlyRead === 'Y';

		this.init();
	};

	IblockElementSelector.prototype.init = function ()
	{
		if(!this.selectorId)
		{
			return false;
		}

		this.ajaxUrl = '/bitrix/components/bitrix/iblock.element.selector/ajax.php';
		this.listElementsData = {};
		this.selectedElements = [];
		this.popupWindow = null;
		var k;

		if(this.currentElements.length)
		{
			BX.addClass(BX(this.selectorId+'_search'), 'ies-content-find-content-selected');
			var selectedElements = [];
			for(k in this.currentElements)
			{
				this.selectedElements[this.currentElements[k].ID] = {
					id: this.currentElements[k].ID, name: this.currentElements[k].NAME};
				selectedElements.push({id: this.currentElements[k].ID, name: this.currentElements[k].NAME});
			}
			this.setSelected(selectedElements);
		}

		if(this.lastElements.length)
		{
			for(k in this.lastElements)
			{
				this.listElementsData[this.lastElements[k].ID] = {
					id: this.lastElements[k].ID,
					name: this.lastElements[k].NAME
				};
			}
		}

		if(BX(this.searchInputId))
		{
			this.searchInput = BX(this.searchInputId);
		}
		else
		{
			this.searchInput = BX(this.selectorId+'_search_input');
		}

		if(this.searchInput)
		{
			BX.bind(this.searchInput, 'keyup', BX.proxy(this.search, this));
			BX.bind(this.searchInput, 'focus', BX.proxy(this._onFocus, this));
		}
		if(this.popup)
		{
			BX.bind(BX(this.selectorId+'_select_button'), 'click', BX.proxy(this.showSelector, this));
		}

		if(this.panelSelectedValues)
		{
			this.onChange = BX.proxy(this.setToSelectedValues, this);
		}

		if(this.onChange)
		{
			this.onChange(this.selectedElements);
		}
	};

	IblockElementSelector.prototype.search = function()
	{
		this.requestTimeout = clearTimeout(this.requestTimeout);

		if(typeof this.searchRequest === 'object')
		{
			this.searchRequest.abort();
			this.searchRequest = false;
		}

		if(this.searchInput.value.length > 0)
		{
			this.displayTab('search');
			BX.addClass(BX(this.selectorId+'_search'), 'ies-content-find-content-selected');

			this.url = this.ajaxUrl + '?sessid='+BX.bitrix_sessid()+'&mode=search&iblockId='+this.iblockId+'&string='+
				encodeURIComponent(this.searchInput.value);
			this.requestTimeout = setTimeout(BX.proxy(this.request, this), 400);
		}
	};

	IblockElementSelector.prototype.displayTab = function(tab)
	{
		BX.removeClass(BX(this.selectorId + '_last'), 'ies-content-tab-content-selected');
		BX.removeClass(BX(this.selectorId + '_search'), 'ies-content-tab-content-selected');
		BX.addClass(BX(this.selectorId + '_' + tab), 'ies-content-tab-content-selected');

		BX.removeClass(BX(this.selectorId + '_tab_last'), 'ies-content-tab-selected');
		BX.removeClass(BX(this.selectorId + '_tab_search'), 'ies-content-tab-selected');
		BX.addClass(BX(this.selectorId + '_tab_' + tab), 'ies-content-tab-selected');

		if(tab === 'search')
			this.searchInput.focus();
	};

	IblockElementSelector.prototype._onFocus = function()
	{
		this.searchInput.value = '';
	};

	IblockElementSelector.prototype.showResult = function(data)
	{
		var divResult = BX(this.selectorId+'_search');
		if(!data || !data.length)
		{
			divResult.innerHTML = '';
			return;
		}

		var elements = data;
		if(divResult)
		{
			divResult.innerHTML = '';
			var table = BX.create('table', {
				props : {
					className : 'ies-content-columns',
					cellspacing : '0'
				},
				children : [
					BX.create('tbody')
				]
			});
			var tr = BX.create('tr');
			table.firstChild.appendChild(tr);
			var td = BX.create('td');
			tr.appendChild(td);
			divResult.appendChild(table);
			for (var i = 0; i < elements.length; i++)
			{
				var elementRow;
				var selected = false;
				this.listElementsData[elements[i].ID] = {
					id : elements[i].ID,
					name : elements[i].NAME
				};
				var inputObject = BX.create('input', {
					props : {
						className : 'ies-hidden-input'
					}
				});
				if(this.multiple)
				{
					inputObject.name = this.selectorId + '[]';
					inputObject.type = 'checkbox';
				}
				else
				{
					inputObject.name = this.selectorId;
					inputObject.type = 'radio';
				}
				var inputs = document.getElementsByName(inputObject.name);
				var j = 0;
				while(!selected && j < inputs.length)
				{
					if(inputs[j].value === elements[i].ID && inputs[j].checked)
					{
						selected = true;
					}
					j++;
				}
				inputObject.value = elements[i].ID;
				var text = elements[i].NAME;
				elementRow = BX.create('div', {
					props : {
						className : 'ies-content-item' + (selected ? ' ies-content-item-selected' : ''),
						id: 'ies-anchor_element_id_' + parseInt(elements[i].ID)
					},
					events : {
						click : BX.proxy(this.select, this)
					},
					children : [
						inputObject,
						BX.create('div', {
							props : {
								className : 'ies-content-item-text'
							},
							text : text
						}),
						BX.create('div', {
							props : {
								className : 'ies-content-item-icon'
							}
						})
					]
				});
				td.appendChild(elementRow);
				if(i === Math.ceil(elements.length / 2) - 1)
				{
					td = BX.create('td');
					table.firstChild.appendChild(td);
				}
			}
		}
	};

	IblockElementSelector.prototype.select = function(event)
	{
		var currentTargetObject = null;
		if(event.currentTarget)
		{
			currentTargetObject = event.currentTarget;
		}
		var inputObject = BX.findChild(currentTargetObject, {tag: 'input'});

		var countSpan, inputs, i;
		if(!this.multiple)
		{
			inputs = document.getElementsByName(this.selectorId);
			for(i = 0; i < inputs.length; i++)
			{
				if(inputs[i].value !== inputObject.value)
				{
					BX.removeClass(inputs[i].parentNode, 'ies-content-item-selected');
				}
				else
				{
					BX.addClass(inputs[i].parentNode, 'ies-content-item-selected');
				}
			}
			inputObject.checked = true;
			BX.addClass(currentTargetObject, 'ies-content-item-selected');
			this.searchInput.value = this.listElementsData[inputObject.value].name;
			this.selectedElements = [];
			this.selectedElements[inputObject.value] = {
				id : inputObject.value,
				name : this.listElementsData[inputObject.value].name
			};
			if(BX(this.selectorId+'_hidden_values'))
			{
				BX(this.selectorId+'_hidden_values').innerHTML = '';
			}
		}
		else
		{
			inputs = document.getElementsByName(this.selectorId + '[]');
			for(i = 0; i < inputs.length; i++)
			{
				if(inputs[i].value === inputObject.value)
				{
					inputs[i].checked = false;
					BX.toggleClass(inputs[i].parentNode, 'ies-content-item-selected')
				}
			}
			if(BX.hasClass(inputObject.parentNode, 'ies-content-item-selected'))
			{
				inputObject.checked = true;
			}
			if(inputObject.checked)
			{
				var selectedObject = BX.findChild(BX(this.selectorId + '_selected_elements'), {
					className: 'ies-content-selected-items'});

				if(!BX(this.selectorId + '_element_selected_' + parseInt(inputObject.value)))
				{
					var nameDiv = BX.findChild(currentTargetObject, {tag:'div',className:'ies-content-item-text'},true);
					var elementRow = BX.create('div', {
						props : {
							className : 'ies-content-selected-item',
							id: this.selectorId + '_element_selected_' + parseInt(inputObject.value)
						},
						children : [
							BX.create('div', {
								props : {
									id: this.selectorId+'-element-unselect-'+parseInt(inputObject.value),
									className: 'ies-content-selected-item-icon'
								},
								attrs : {
									onclick:'BX.Iblock["'+this.jsObject+'"].unselect("'+parseInt(inputObject.value)+'");'
								}
							}),
							BX.create('span', {
								props : {
									className: 'ies-content-selected-item-text'
								},
								text : nameDiv.innerHTML
							})
						]
					});
					selectedObject.appendChild(elementRow);

					countSpan = BX(this.selectorId + '_current_count');
					countSpan.innerHTML = parseInt(countSpan.innerHTML) + 1;

					this.selectedElements[inputObject.value] = {
						id : inputObject.value,
						name : this.listElementsData[inputObject.value].name
					};
				}
			}
			else
			{
				BX.remove(BX(this.selectorId + '_element_selected_' + parseInt(inputObject.value)));
				BX.remove(BX(this.selectorId+'_selected_value_'+parseInt(inputObject.value)));
				countSpan = BX(this.selectorId + '_current_count');
				countSpan.innerHTML = parseInt(countSpan.innerHTML) - 1;
				this.selectedElements[inputObject.value] = null;
			}
		}

		if(this.onSelect)
		{
			if(this.multiple)
			{
				this.onSelect(this.selectedElements);
			}
			else
			{
				var emp = this.selectedElements.pop();
				this.selectedElements.push(emp);
				this.onSelect(emp);
			}
		}

		BX.onCustomEvent(this, 'on-change', [this.toObject(this.selectedElements)]);

		if(this.onChange)
		{
			this.onChange(this.selectedElements);
		}
	};

	IblockElementSelector.prototype.unselect = function(elementId, internalCall)
	{
		var link = BX(this.selectorId + '-element-unselect-' + elementId);
		var inputs = document.getElementsByName(this.selectorId + (this.multiple ? '[]' : ''));
		for(var i = 0; i < inputs.length; i++)
		{
			if(inputs[i].value === elementId)
			{
				inputs[i].checked = false;
				BX.removeClass(inputs[i].parentNode, 'ies-content-item-selected');
			}
		}
		if(this.multiple)
		{
			if(link)
			{
				BX.remove(link.parentNode);
			}
			var countSpan = BX(this.selectorId + '_current_count');
			countSpan.innerHTML = parseInt(countSpan.innerHTML) - 1;
		}

		this.selectedElements[elementId] = null;

		BX.onCustomEvent(this, 'un-select', [this.toObject(this.selectedElements)]);

		if(this.onChange)
		{
			this.onChange(this.selectedElements);
		}

		if(this.onUnSelect && !internalCall)
		{
			this.onUnSelect(this.selectedElements);
		}

		if(BX(this.selectorId+'_selected_value_'+parseInt(elementId)))
		{
			BX(this.selectorId+'_selected_value_'+parseInt(elementId)).value = 0;
		}
		if(this.searchInput && !this.multiple)
		{
			this.searchInput.value = '';
		}
	};

	IblockElementSelector.prototype.getSelected = function()
	{
		return this.selectedElements;
	};

	IblockElementSelector.prototype.setSelected = function(elements)
	{
		var i, count;
		for(i = 0, count = this.selectedElements.length; i < count; i++)
		{
			if(this.selectedElements[i] && this.selectedElements[i].id)
			{
				this.unselect(this.selectedElements[i].id, true);
			}
		}
		if(!elements.length)
		{
			return;
		}
		this.selectedElements = [];
		for(i = 0, count = elements.length; i < count; i++)
		{
			if(!elements[i] || !elements[i].id) continue;
			this.selectedElements[elements[i].id] = elements[i];
			if(this.multiple)
			{
				var selectedObject = BX.findChild(BX(this.selectorId + '_selected_elements'), {
					className: 'ies-content-selected-items'});
				var elementRow = BX.create('div', {
					props : {
						className : 'ies-content-selected-item',
						id: this.selectorId + '_element_selected_'+parseInt(elements[i].id)
					},
					children : [
						BX.create('div', {
							props : {
								id: this.selectorId+'-element-unselect-'+parseInt(elements[i].id),
								className : 'ies-content-selected-item-icon'
							},
							attrs : {
								onclick : 'BX.Iblock["'+this.jsObject+'"].unselect("'+parseInt(elements[i].id)+'");'
							}
						}),
						BX.create('span', {
							props : {
								className : 'ies-content-selected-item-text'
							},
							text : BX.util.htmlspecialchars(elements[i].name)
						})
					]
				});
				selectedObject.appendChild(elementRow);
			}
			var inputs = document.getElementsByName(this.selectorId + (this.multiple ? '[]' : ''));
			for(var j = 0; j < inputs.length; j++)
			{
				if(inputs[j].value === elements[i].id)
				{
					if(inputs[j].parentNode.className === '')
						continue;
					BX.toggleClass(inputs[j].parentNode, 'ies-content-item-selected')
				}
			}
		}
		if(this.multiple)
		{
			BX.adjust(BX(this.selectorId + '_current_count'), {text: elements.length});
		}
	};

	IblockElementSelector.prototype.toObject = function(brokenArray)
	{
		var result = {};
		for(var k in brokenArray)
		{
			k = parseInt(k);
			if(typeof k === 'number' && brokenArray[k] !== null)
			{
				result[k] = BX.clone(brokenArray[k]);
			}
		}
		return result;
	};

	IblockElementSelector.prototype.request = function()
	{
		var startTime = (new Date()).getTime();
		this.lastSearchTime = startTime;
		this.searchRequest = BX.ajax.loadJSON(this.url, BX.proxy(function(data) {
			if(this.lastSearchTime === startTime)
			{
				this.showResult(data);
			}
		}, this));
	};

	IblockElementSelector.prototype.showSelector = function()
	{
		if(!this.popupWindow)
		{
			this.popupWindow = new BX.PopupWindow(
				this.selectorId+'popup-window',
				BX(this.selectorId+'_select_button'),
				{
					offsetTop : 1,
					autoHide : true,
					content : BX(this.selectorId),
					zIndex: 3000
				}
			);
		}
		else
		{
			this.popupWindow.setBindElement(this);
		}

		if(this.popupWindow.popupContainer.style.display !== 'block')
		{
			this.popupWindow.show();
		}
	};

	IblockElementSelector.prototype.setToSelectedValues = function(selectedElements)
	{
		var listSelectedElements = '';
		for(var i = 0; i < selectedElements.length; i++)
		{
			var selectedElement = selectedElements[i];
			if(selectedElement)
			{
				if(!BX(this.selectorId+'_selected_value_'+selectedElement.id))
				{
					BX(this.selectorId+'_hidden_values').appendChild(
						BX.create('input', {
							props: {
								id: this.selectorId+'_selected_value_'+parseInt(selectedElement.id)
							},
							attrs: {
								type: 'hidden',
								value: parseInt(selectedElement.id),
								name: this.inputName+(this.multiple ? '[]' : '')
							}
						})
					);
				}
				listSelectedElements += '['+parseInt(selectedElement.id)+']'+BX.util.htmlspecialchars(selectedElement.name);
				if(!this.multiple && !this.onlyRead)
				{
					listSelectedElements += '<span class="ies-content-delete-icon" onclick="BX.Iblock[\''+this.jsObject+
						'\'].unselect(\''+parseInt(selectedElement.id)+'\')"></span>';
				}
				listSelectedElements += '<br>';
			}
		}
		BX(this.selectorId+'_visible_values').innerHTML = listSelectedElements;
	};

	return IblockElementSelector;
})();