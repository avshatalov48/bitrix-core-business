;(function()
{
	'use strict';

	BX.namespace('BX.Fileman.UserField');

	if(typeof BX.Fileman.UserField.TypeAddress !== 'undefined')
	{
		return;
	}

	/**
	 * Money type handler class. Will be initialized in Factory.
	 *
	 * @constructor
	 */
	BX.Fileman.UserField.TypeAddress = function()
	{
	};
	BX.extend(BX.Fileman.UserField.TypeAddress, BX.Main.UF.BaseType);

	BX.Fileman.UserField.TypeAddress.USER_TYPE_ID = 'address';

	BX.Fileman.UserField.TypeAddress.prototype.focus = function(field)
	{
		var node = this.getNode(field);

		if(!BX.isNodeInDom(node))
		{
			console.error('Node for field ' + field + ' is already removed from DOM');
		}

		var input = BX.findChild(node, {
			tagName: 'INPUT',
			attribute: {
				type: 'text'
			}
		}, true);

		if(input)
		{
			BX.focus(input);
		}
	};

	BX.Fileman.UserField.Address = function(node, param)
	{
		this.node = node;

		this.inputListNode = null;

		this.value = param.value;

		this.autocomplete = param.autocomplete || null;
		this.geocoder = param.geocoder || null;
		this.map = param.map || null;
		this.resultDisplay = param.resultDisplay || null;

		this.multiple = !!param.multiple;

		this.inputObjects = [];

		BX.ready(BX.delegate(this.init, this));
	};

	BX.Fileman.UserField.Address.prototype.init = function()
	{
		this.inputListNode = this.node.appendChild(BX.create('DIV'));

		for(var i = 0; i < this.value.length; i++)
		{
			this.addSearchInput(this.value[i]);

			if(!this.multiple)
			{
				break;
			}
		}

		if(this.multiple)
		{
			BX.addClass(this.inputListNode, 'multiple');
			this.inputListNode.appendChild(BX.create('INPUT', {
				attrs: {
					type: 'button',
					value: BX.message('UF_ADDRESS_ADD')
				},
				events: {
					click: BX.delegate(function(){this.addSearchInput(false);}, this)
				}
			}));
		}

		BX.defer(this.callChangeEvent, this)();
	};

	BX.Fileman.UserField.Address.prototype.isMapEnabled = function()
	{
		return true;
	};

	BX.Fileman.UserField.Address.prototype.callChangeEvent = function(input, value)
	{
		BX.onCustomEvent(this, 'UserFieldAddress::Change', [this.getValue()]);
	};

	BX.Fileman.UserField.Address.prototype.getValue = function()
	{
		var value = [];
		for(var i = 0; i < this.inputObjects.length; i++)
		{
			if(!!this.inputObjects[i])
			{
				value.push(this.inputObjects[i].getResult());
			}
		}

		return value;
	};

	BX.Fileman.UserField.Address.prototype.addSearchInput = function(value)
	{
		var inputContainer = BX.create('DIV', {props: {className: "field-item"}});
		this.inputListNode.insertBefore(inputContainer, this.inputListNode.lastChild);

		var input = BX.create('input', {attrs: {type: 'text', tabindex: '0'}, props: {className: 'uf-address-search-input'}});
		inputContainer.appendChild(input);

		var searchField = new BX.Fileman.UserField.AddressSearchField(this, input, value);

		var index = this.inputObjects.length;
		this.inputObjects.push(searchField);

		if(this.multiple)
		{
			inputContainer.appendChild(BX.create('SPAN', {
				props :{
					className : "uf-address-search-input-remove"
				},
				events: {
					click: BX.delegate(function()
					{
						this.inputObjects[index] = null;
						BX.cleanNode(inputContainer, true);
						this.callChangeEvent();
					}, this)
				}
			}));
		}
	};

	BX.Fileman.UserField.Address.prototype.getAutoComplete = function()
	{
		if(this.autocomplete === null)
		{
			this.setDefaultAutoComplete();
		}

		return this.autocomplete;
	};

	BX.Fileman.UserField.Address.prototype.setAutoComplete = function(autocomplete)
	{
		this.autocomplete = autocomplete;
	};

	BX.Fileman.UserField.Address.prototype.setDefaultAutoComplete = function()
	{
		this.setAutoComplete(new BX.Fileman.Google.AutoComplete());
	};

	BX.Fileman.UserField.Address.prototype.getGeoCoder = function()
	{
		if(this.geocoder === null)
		{
			this.setDefaultGeoCoder();
		}

		return this.geocoder;
	};

	BX.Fileman.UserField.Address.prototype.setGeoCoder = function(geocoder)
	{
		this.geocoder = geocoder;
	};

	BX.Fileman.UserField.Address.prototype.setDefaultGeoCoder = function()
	{
		this.setGeoCoder(new BX.Fileman.Google.GeoCoder());
	};

	BX.Fileman.UserField.Address.prototype.getMap = function()
	{
		return this.map;
	};

	BX.Fileman.UserField.Address.prototype.setMap = function(map)
	{
		this.map = map;
	};

	BX.Fileman.UserField.Address.prototype.getResultDisplay = function()
	{
		if(!this.resultDisplay)
		{
			this.setDefaultResultDisplay();
		}

		return this.resultDisplay;
	};

	BX.Fileman.UserField.Address.prototype.setResultDisplay = function(resultDisplay)
	{
		this.resultDisplay = resultDisplay;
	};

	BX.Fileman.UserField.Address.prototype.setDefaultResultDisplay = function()
	{
		this.setResultDisplay(new BX.Fileman.UserField.AddressSearchResultDisplay(this));
	};

	/**
	 * Search Field item
	 *
	 * @param dispatcher
	 * @param input
	 * @param value
	 * @constructor
	 */

	BX.Fileman.UserField.AddressSearchField = function(dispatcher, input, value)
	{
		this.dispatcher = dispatcher;

		this.input = input;

		this.text = '';
		this.coords = null;

		this.tmpCoords = null;

		if(!!value)
		{
			this.text = value;
			if(value.indexOf('|') >= 0)
			{
				value = value.split('|');
				this.text = value[0];

				if(!!value[1] && value[1].indexOf(';') > 0)
				{
					this.coords = value[1].split(';');
				}
			}
		}

		this.input.value = this.text;

		var changeHandler = BX.debounce(this.onChangeValue, 150, this);

		BX.bind(this.input, 'click', BX.proxy(this.captureMap, this));
		BX.bind(this.input, 'bxchange', changeHandler);
		BX.unbind(this.input, 'blur', changeHandler);
		BX.unbind(this.input, 'change', changeHandler);
	};

	BX.Fileman.UserField.AddressSearchField.prototype.hasCoordinates = function()
	{
		return this.coords !== null;
	};

	BX.Fileman.UserField.AddressSearchField.prototype.getResult = function()
	{
		return {
			coords: this.coords,
			text: this.text
		};
	};

	BX.Fileman.UserField.AddressSearchField.prototype.getNode = function()
	{
		return this.input;
	};

	BX.Fileman.UserField.AddressSearchField.prototype.display = function()
	{
		if(this.hasCoordinates())
		{
			this.dispatcher.getResultDisplay().display(this, [{
				text: this.text,
				coords: this.coords,
				local: true
			}], BX.proxy(this.saveValue, this), BX.proxy(this.displayChanged, this));
		}
		else
		{
			this.dispatcher.getResultDisplay().close();
		}
	};

	BX.Fileman.UserField.AddressSearchField.prototype.captureMap = function()
	{
		this.display();
	};

	BX.Fileman.UserField.AddressSearchField.prototype.onChangeValue = function(e)
	{
		if(this.input.value !== this.text || !this.coords)
		{
			this.text = this.input.value;

			if(this.text.length > 0)
			{
				this.dispatcher.getAutoComplete().search(
					this.text,
					BX.proxy(this.searchCallback, this)
				);
			}
			else
			{
				this.coords = null;
			}

			this.dispatcher.callChangeEvent();
		}
	};

	BX.Fileman.UserField.AddressSearchField.prototype.displayChanged = function(result)
	{
		this.tmpCoords = result;

		this.dispatcher.getGeoCoder().search(
			result,
			BX.proxy(this.coordsSearchCallback, this)
		);
	};

	BX.Fileman.UserField.AddressSearchField.prototype.searchCallback = function(result)
	{
		if(result.length <= 0)
		{
			this.coords = null;

			this.dispatcher.callChangeEvent();

			this.dispatcher.getResultDisplay().display(this, result, BX.proxy(this.saveValue, this), BX.proxy(this.displayChanged, this));
		}
		else
		{
			this.lastResult = result;
			this.dispatcher.getGeoCoder().search(
				result[0].place_id,
				BX.proxy(this.searchCoordsCallback, this)
			);
		}
	};

	BX.Fileman.UserField.AddressSearchField.prototype.searchCoordsCallback = function(result)
	{
		if(result.length <= 0)
		{
			this.coords = null;
		}
		else
		{
			this.coords = result[0].coords;
			this.lastResult[0].coords = this.coords;
		}

		this.dispatcher.getResultDisplay().display(this, this.lastResult, BX.proxy(this.saveValue, this), BX.proxy(this.displayChanged, this));

		this.dispatcher.callChangeEvent();
	};

	BX.Fileman.UserField.AddressSearchField.prototype.coordsSearchCallback = function(result)
	{
		this.dispatcher.getResultDisplay().setContent(this, [{
			text: result[0].text,
			coords: this.tmpCoords
		}], BX.proxy(this.saveValue, this));

		this.tmpCoords = null;
	};

	BX.Fileman.UserField.AddressSearchField.prototype.saveValue = function(value)
	{
		this.text = value.text;
		this.coords = value.coords;

		this.input.value = this.text;

		this.dispatcher.getResultDisplay().close();

		this.dispatcher.callChangeEvent(this, [this.text, this.coords]);
	};

	/**
	 * Result display - marker and marker popup
	 * @param dispatcher
	 * @constructor
	 */

	BX.Fileman.UserField.AddressSearchResultDisplay = function(dispatcher)
	{
		this.dispatcher = dispatcher;
		this.resultNode = null;
	};

	BX.Fileman.UserField.AddressSearchResultDisplay.prototype.display = function(source, result, callback, displayChangeCallback)
	{
		if(result.length > 0)
		{
			if(!result[0].local)
			{
				var menuItemList = [];
				for(var i = 0; i < result.length; i++)
				{
					menuItemList.push(this.createResultRow(result[i], source.getNode(), callback, displayChangeCallback))
				}

				BX.Fileman.UserField.addressSearchResultDisplayList.show(source.getNode(), menuItemList);
			}

			if(this.dispatcher.isMapEnabled() && result[0].coords)
			{
				BX.Fileman.UserField.addressSearchResultDisplayMap.show(source.getNode(), result[0], callback, displayChangeCallback);
			}
			else
			{
				BX.Fileman.UserField.addressSearchResultDisplayMap.close();
			}
		}
		else
		{
			BX.Fileman.UserField.addressSearchResultDisplayList.show(source.getNode(), [{text: BX.message('UF_ADDRESS_NO_RESULT')}]);

			BX.Fileman.UserField.addressSearchResultDisplayMap.close();
		}
	};

	BX.Fileman.UserField.AddressSearchResultDisplay.prototype.setContent = function(source, result, callback)
	{
		if(result.length > 0)
		{
			BX.Fileman.UserField.addressSearchResultDisplayMap.setContent(result[0].text);
		}
	};

	BX.Fileman.UserField.AddressSearchResultDisplay.prototype.createResultRow = function(item, bindNode, callback, displayChangeCallback)
	{
		return {
			text: item.text,
			className: 'uf-search-result-list-item',
			events: {
				onMouseEnter: BX.delegate(this.resultHoverHandler(item, bindNode, callback, displayChangeCallback), this)
			},
			onclick: BX.delegate(this.resultClickHandler(item, callback), this)
		}
	};


	BX.Fileman.UserField.AddressSearchResultDisplay.prototype.resultClickHandler = function(item, callback)
	{
		return function(e)
		{
			callback(item);
			return (e || window.event).preventDefault();
		}
	};


	BX.Fileman.UserField.AddressSearchResultDisplay.prototype.resultHoverHandler = function(item, bindNode, callback, displayChangeCallback)
	{
		var geocoder = this.dispatcher.getGeoCoder();

		return function(e)
		{
			if(!!item.coords)
			{
				BX.Fileman.UserField.addressSearchResultDisplayMap.show(bindNode, item, callback, displayChangeCallback);
			}
			else if(!!item.place_id)
			{
				geocoder.search(item.place_id, function(result){
					item.coords = result[0].coords;
					BX.Fileman.UserField.addressSearchResultDisplayMap.show(bindNode, item, callback, displayChangeCallback);
				});
			}
		}
	};

	BX.Fileman.UserField.AddressSearchResultDisplay.prototype.close = function()
	{
		BX.Fileman.UserField.addressSearchResultDisplayList.close();
		BX.Fileman.UserField.addressSearchResultDisplayMap.close();
	};


	BX.Fileman.UserField.AddressSearchResultDisplayList = function()
	{
		this.node = null;
		this.bindNode = null;
	};

	BX.Fileman.UserField.AddressSearchResultDisplayList.prototype.show = function(bindNode, menuItems)
	{
		this.bindNode = bindNode;

		BX.PopupMenu.destroy('uf_address_result_list');
		BX.PopupMenu.show('uf_address_result_list', this.bindNode, menuItems);

		BX.PopupMenu.getCurrentMenu().getPopupWindow().popupContainer.style.width = this.bindNode.offsetWidth + 'px';
	};

	BX.Fileman.UserField.AddressSearchResultDisplayList.prototype.close = function()
	{
		BX.PopupMenu.destroy('uf_address_result_list');
	};


	BX.Fileman.UserField.AddressSearchResultDisplayMap = function()
	{
		this.node = null;
		this.map = null;
		this.point = null;

		this.currentItem = null;

		this.bindNode = null;
		this.infoWindowContent = null;

		this.opened = false;

		this.animation = null;

		this.hoverMode = false;

		this.mapHoverHandler = null;
		this.mapHoutHandler = null;
	};

	BX.Fileman.UserField.AddressSearchResultDisplayMap.prototype.showDelayed = function(bindNode, item, changeCallback, searchCallback)
	{
		this.opened = true;
		this.bindNode = bindNode;

		setTimeout(BX.delegate(function(){
			if(this.opened && this.bindNode === bindNode)
			{
				this.show(bindNode, item, changeCallback, searchCallback);
			}
		}, this), 150);
	};

	BX.Fileman.UserField.AddressSearchResultDisplayMap.prototype.showHover = function(bindNode, item, changeCallback, searchCallback)
	{
		this.hoverMode = true;
		if(bindNode !== this.bindNode)
		{
			this.show(bindNode, item, changeCallback, searchCallback);
		}
		else
		{
			this.showDelayed(bindNode, item, changeCallback, searchCallback);
		}
	};

	BX.Fileman.UserField.AddressSearchResultDisplayMap.prototype.closeHover = function(bindNode)
	{
		this.closeDelayed();
	};

	BX.Fileman.UserField.AddressSearchResultDisplayMap.prototype.show = function(bindNode, item, changeCallback, searchCallback)
	{
		if(this.hoverMode && bindNode === this.bindNode && !BX.isNodeHidden(this.getNode()))
		{
			this.opened = true;
			return;
		}

		this.bindNode = bindNode;
		this.currentItem = item;

		this.adjustNode(this.bindNode);

		if(this.animation !== null)
		{
			this.animation.stop(true);
		}

		if(BX.isNodeHidden(this.getNode()) || this.hoverMode)
		{
			if(this.hoverMode)
			{
				if(this.mapHoverHandler)
				{
					BX.unbind(this.getNode(), 'mouseover', this.mapHoverHandler);
					BX.unbind(this.getNode(), 'mouseout', this.mapHoutHandler);
				}

				this.mapHoverHandler = BX.delegate(function()
				{
					this.showHover(bindNode, item, changeCallback, searchCallback);
				}, this);

				this.mapHoutHandler = BX.delegate(function()
				{
					this.closeHover(bindNode);
				}, this);

				BX.bind(this.getNode(), 'mouseover', this.mapHoverHandler);
				BX.bind(this.getNode(), 'mouseout', this.mapHoutHandler);
			}
			else
			{
				BX.unbind(this.getNode(), 'mouseover', this.mapHoverHandler);
				BX.unbind(this.getNode(), 'mouseout', this.mapHoutHandler);

				this.mapHoverHandler = null;
				this.mapHoutHandler = null;
			}

			if(BX.isNodeHidden(this.getNode()))
			{
				this.getNode().style.display = "block";
				this.getNode().style.opacity = 0;

				BX.Fileman.Google.Loader.init(BX.delegate(function()
				{
					this.animation = new BX.easing({
						duration: 300,
						start: {opacity: 0},
						finish: {opacity: 100},
						transition: BX.easing.transitions.linear,
						step: BX.delegate(function(state)
						{
							this.getNode().style.opacity = state.opacity / 100;
						}, this),
						complete: BX.delegate(function()
						{
							this.getNode().style.opacity = 1;
							this.animation = null;
						}, this)
					});

					this.animation.animate();
				}, this));
			}
		}


		if(this.map === null)
		{
			this.map = new BX.Fileman.Google.Map(this.getNode(), {
				zoom: 18,
				center: item.coords
			});
		}
		else if(!!item.coords)
		{
			this.map.panTo(item.coords);
		}

		if(this.point === null)
		{
			this.point = this.map.addPoint(item.coords);
		}
		else
		{
			this.point.moveTo(item.coords);
		}

		if(!!searchCallback)
		{
			this.point.setEvent('change', function(newCoords)
			{
				searchCallback(newCoords);
			});
		}
		else
		{
			this.point.setEvent('change', null);
		}

		this.point.setDraggable(!this.hoverMode);

		this.infoWindowContent = BX.create('SPAN', {
			text: item.text,
			props: {
				className: 'uf-address-search-onmap-result'
			},
			events: {
				click: function()
				{
					if(!!changeCallback && BX.type.isFunction(changeCallback))
					{
						changeCallback(item);
					}
				}
			}
		});

		this.point.setContent(this.infoWindowContent);

		if(!this.hoverMode)
		{
			BX.bind(this.bindNode, 'mouseup', BX.PreventDefault);
			BX.bind(document.body, 'mouseup', BX.proxy(this.closeDelayed, this));
		}
	};

	BX.Fileman.UserField.AddressSearchResultDisplayMap.prototype.setContent = function(text)
	{
		BX.adjust(this.infoWindowContent, {text: text});
		this.currentItem.text = text;
		this.currentItem.coords = this.point.getPosition();
	};

	BX.Fileman.UserField.AddressSearchResultDisplayMap.prototype.closeDelayed = function()
	{
		var bindNode = this.bindNode;
		this.opened = false;
		setTimeout(BX.delegate(function(){
			if(!this.opened && bindNode === this.bindNode)
			{
				this.close()
			}
		}, this), 200);
	};

	BX.Fileman.UserField.AddressSearchResultDisplayMap.prototype.close = function()
	{
		this.opened = false;
		this.hoverMode = false;

		if(this.animation !== null)
		{
			this.animation.stop(true);
		}

		if(!!this.node && !BX.isNodeHidden(this.node))
		{
			this.getNode().style.display = "block";
			this.getNode().style.opacity = 1;

			this.animation = new BX.easing({
				duration : 300,
				start:  { opacity: 100 },
				finish:  { opacity: 0 },
				transition : BX.easing.transitions.linear,
				step: BX.delegate(function(state) {
					this.getNode().style.opacity = state.opacity / 100;
				}, this),
				complete: BX.delegate(function() {
					BX.hide(this.node);
					this.animation = null;
				}, this)
			});

			this.animation.animate();
		}

		BX.unbind(this.bindNode, 'mouseup', BX.PreventDefault);
		BX.unbind(document.body, 'mouseup', BX.proxy(this.closeDelayed, this));
	};

	BX.Fileman.UserField.AddressSearchResultDisplayMap.prototype.getNode = function()
	{
		if(this.node === null)
		{
			this.node = BX.create('DIV', {
				props: {
					className: 'uf-address-search-map'
				},
				style: {
					display: 'none'
				},
				events: {
					mouseup: BX.PreventDefault
				}
			});
		}

		return this.node;
	};


	BX.Fileman.UserField.AddressSearchResultDisplayMap.prototype.adjustNode = function(bindNode)
	{
		var pos = BX.pos(bindNode);
		var windowSize = BX.GetWindowSize();

		var windowHeight = windowSize.scrollHeight;
		var windowScrollBottom = windowSize.scrollTop + windowSize.innerHeight;

		if(!!BX.Crm && !!BX.Crm.EntityEditor && !!BX.Crm.EntityEditor.defaultInstance && !!BX.Crm.EntityEditor.defaultInstance._toolPanel && !!BX.Crm.EntityEditor.defaultInstance._toolPanel.isVisible())
		{
			var panelWrapper = BX.Crm.EntityEditor.defaultInstance._toolPanel._wrapper;
			windowHeight -= panelWrapper.firstChild.offsetHeight;
		}

		this.getNode().style.position = 'absolute';
		this.getNode().style.top = Math.min(pos.top, windowHeight - 500, windowScrollBottom - 500) + 'px';
		this.getNode().style.left = (pos.left + pos.width + 2) + 'px';

		document.body.appendChild(this.getNode());
	};

	BX.Fileman.UserField.AddressRestriction = function()
	{
		this.bindNode = null;
		this.popup = null;
	};

	BX.Fileman.UserField.AddressRestriction.prototype.show = function(element)
	{
		this.bindNode = element;
		setTimeout(BX.proxy(this._show, this), 100);
	};

	BX.Fileman.UserField.AddressRestriction.prototype._show = function()
	{
		this.getPopup(this.bindNode).show();
		this.getPopup(this.bindNode).popupContainer.style.width = (this.bindNode.offsetWidth-20) + 'px';
	};


	BX.Fileman.UserField.AddressRestriction.prototype.getPopup = function(element)
	{
		if(this.popup === null)
		{
			this.popup = new BX.PopupWindow('uf_address_resriction', element, {
				content: this.getContent(),
				autoHide: true
			});
		}
		else if(!!element)
		{
			this.getPopup().setBindElement(element);
		}

		return this.popup;
	};

	BX.Fileman.UserField.AddressRestriction.prototype.getContent = function()
	{
		return '';
	};

	BX.Fileman.UserField.AddressSearchRestriction = function()
	{
		BX.Fileman.UserField.AddressSearchRestriction.superclass.constructor.apply(this, arguments);
	};
	BX.extend(BX.Fileman.UserField.AddressSearchRestriction, BX.Fileman.UserField.AddressRestriction);

	BX.Fileman.UserField.AddressSearchRestriction.prototype.getContent = function()
	{
		return '<span class="tariff-lock"></span>&nbsp;<span>'+BX.message('GOOGLE_MAP_TRIAL_HINT')+'</span> <a href="javascript:void(0)" onclick="BX.Fileman.UserField.addressSearchRestriction.showPopup()" class="uf-address-trial-more">'+ BX.message('GOOGLE_MAP_TRIAL_HINT_MORE')+'</a>';
	};

	BX.Fileman.UserField.AddressSearchRestriction.prototype.showPopup = function()
	{
		this.getPopup().close();

		B24.licenseInfoPopup.show(
			'uf_address',
			BX.message('GOOGLE_MAP_TRIAL_TITLE'),
			BX.message('GOOGLE_MAP_TRIAL')
		);
	};

	BX.Fileman.UserField.AddressKeyRestriction = function()
	{
		BX.Fileman.UserField.AddressKeyRestriction.superclass.constructor.apply(this, arguments);
	};
	BX.extend(BX.Fileman.UserField.AddressKeyRestriction, BX.Fileman.UserField.AddressRestriction);

	BX.Fileman.UserField.AddressKeyRestriction.prototype.getContent = function()
	{
		return BX.message('GOOGLE_MAP_API_KEY_HINT');
	};

	BX.Fileman.UserField.addressSearchResultDisplayList = new BX.Fileman.UserField.AddressSearchResultDisplayList();
	BX.Fileman.UserField.addressSearchResultDisplayMap = new BX.Fileman.UserField.AddressSearchResultDisplayMap();
	BX.Fileman.UserField.addressSearchRestriction = new BX.Fileman.UserField.AddressSearchRestriction();
	BX.Fileman.UserField.addressKeyRestriction = new BX.Fileman.UserField.AddressKeyRestriction();

	BX.Main.UF.Factory.setTypeHandler(BX.Fileman.UserField.TypeAddress.USER_TYPE_ID, BX.Fileman.UserField.TypeAddress);

})();