(function() {

	"use strict";

	BX.namespace("BX.UI");

	/**
	 *
	 * @param {object} options
	 * @param {Element} options.targetElement
	 * @param {Element} options.inputElement
	 * @param {Array} options.items
	 * @param {String} [options.ajaxUrl]
	 * @param {object} [options.events<string, function>]
	 * @constructor
	 */
	BX.UI.Dropdown = function(options)
	{
		this.popupWindow = null;
		this.items = [];
		this.defaultItems = [];
		this.itemsContainer = null;
		this.popupConatiner = null;
		this.footerItems = null;
		this.targetElement = options.targetElement;
		this.CurrentItem = null;

		this.id = BX.prop.getString(options, "id", BX.Text.getRandom());
		this.searchAction = BX.prop.getString(options, "searchAction", "");
		this.searchOptions = BX.prop.getObject(options, "searchOptions", {});
		this.previousSearchQuery = null;
		this.isLastSearchComplete = null;

		this.messages = BX.prop.getObject(options, "messages", {});

		this.isChanged = false;
		this.isDisabled = BX.prop.getBoolean(options, "isDisabled", false);
		this.enableCreation = BX.prop.getBoolean(options, "enableCreation", false);
		this.enableCreationOnBlur = BX.prop.getBoolean(options, "enableCreationOnBlur", true);

		this.isEmbedded = BX.prop.getBoolean(BX.prop.getObject(options, "context", {}), "isEmbedded", false);
		this.quickForm = document.querySelectorAll('.crm-kanban-quick-form');

		this.ajaxRequestTimer = null;
		this.autocompleteDelay = BX.prop.getInteger(options, "autocompleteDelay", 0);
		this.minSearchStringLength = BX.prop.getInteger(options, "minSearchStringLength", 2);

		this.documentClickHandler = BX.delegate(this.onDocumentClick, this);

		this.emptyValueEventHandle = 0;

		this.events = options.events || {};
		this.bindEvents();

		this.updateItemsList(options.items);
		this.setDefaultItems(options.items);
		this.setFooterItems(options.footerItems);

		if (this.targetElement !== 'undefined' && (typeof this.targetElement !== 'undefined'))
		{
			this.init();
		}
	};
	BX.UI.Dropdown.prototype =
		{
			bindEvents: function()
			{
				if (this.events)
				{
					for (var eventName in this.events)
					{
						if (BX.type.isFunction(this.events[eventName]))
						{
							BX.addCustomEvent(this, "BX.UI.Dropdown:" + eventName, this.events[eventName]);
						}
					}
				}
			},
			init: function ()
			{
				setTimeout(function() {
					BX.bind(this.targetElement, "input", function()
					{
						if (this.isDisabled)
						{
							return;
						}

						if (this.targetElement.value.length === 0)
						{
							this.enableTargetElement();
							return;
						}

						this.showPopupWindow();

					}.bind(this));
				}.bind(this), 100);

				this.targetElement.addEventListener("click", function(e)
				{
					this.destroyPopupWindow();

                    if (this.isDisabled)
                    {
                        return;
                    }

					if(this.CurrentItem && this.CurrentItem.title.length === this.targetElement.value.length)
					{
						this.disableTargetElement();
					}

					else if(this.targetElement.value.length > 0)
					{
						this.disableTargetElement();
					}

					if(this.popupWindow)
					{
						BX.PreventDefault(e);
					}
					this.showPopupWindow();
				}.bind(this), true);

				setTimeout(function() {
					this.targetElement.addEventListener("focus", function()
					{
						if (this.isDisabled)
						{
							return;
						}

						this.showPopupWindow();

						if(!this.popupAlertContainer)
						{
							return;
						}
					}.bind(this), true);
				}.bind(this), 100);

				BX.bind(
					this.targetElement,
					"keyup",
					BX.throttle(
						function(e) {
                            if (this.isDisabled)
                            {
                                return;
                            }

							if (this.targetElement.value === '')
							{
								return;
							}

							if (e.key === "Escape")
							{
                                if (this.isDisabled)
                                {
                                    return;
                                }

								if (this.targetElement.value !== '' && !this.popupWindow)
								{
									this.showPopupWindow();
								}
								else {
									BX.PreventDefault(e);
								}
							}
						}.bind(this),
						1000
					)
				);

				this.targetElement.addEventListener("keyup", function(e)
				{
                    if (this.isDisabled)
                    {
                        return;
                    }

					if (e.keyCode === 40)
					{
						this.handleDownArrow();
					}
					else if (e.keyCode === 38)
					{
						this.handleUpArrow();
					}
					else if (e.keyCode === 13)
					{
						if (this.highlightedItem)
						{
							this.handleItemClick(this.highlightedItem);
							this.getPopupWindow().close();
						}

						if (this.highlightedItem && this.CurrentItem === this.popupAlertContainer)
						{
                            this.onEmptyValueEvent();
                            return;
						}

                        if ((this.CurrentItem && this.CurrentItem.title.length !== this.targetElement.value.length)
                            || (!this.CurrentItem && this.targetElement.value.length > 0))
                        {
                            this.onEmptyValueEvent();
                            return;
                        }

                        if (this.popupWindow && this.enableCreation)
                        {
                            this.destroyPopupWindow();
                        }
					}
					else
					{
						this.handleTypeInField();
					}

					if(this.targetElement.value !== '' && !this.popupWindow)
					{
						this.showPopupWindow();
						return;
					}

					if(e.keyCode === 9 && !this.popupWindow)
					{
						this.showPopupWindow();
						return;
					}

					if(e.keyCode === 9 && this.targetElement.value === '' && !this.popupWindow)
					{
						this.showPopupWindow();
					}

					if(!this.enableCreation) {
						this.targetElement.addEventListener("keyup", function(e)
						{
							if(e.key === "Escape")
							{
								this.resetInputValue();
							}
						}.bind(this));

						BX.bind(document, "click", this.documentClickHandler);
					}
				}.bind(this));

				this.targetElement.addEventListener('keydown', function(e)
				{
                    if (this.isDisabled)
                    {
                        return;
                    }

					if ((e.keyCode === 9 && this.CurrentItem && this.CurrentItem.title.length !== this.targetElement.value.length)
						|| (e.keyCode === 9 && !this.CurrentItem && this.targetElement.value.length > 0))
					{
						this.onEmptyValueEvent();
						return;
					}

					if (e.keyCode === 9 && !this.popupWindow)
					{
						this.showPopupWindow();
						return;
					}
					if (e.keyCode === 9 && this.targetElement.value === '' && !this.popupWindow)
					{
						this.showPopupWindow();
						return;
					}

					if (e.keyCode === 9 && this.popupWindow)
					{
						this.destroyPopupWindow();
					}
				}.bind(this));
			},
			onDocumentClick: function()
			{
				if(this.isTargetElementChanged())
				{
					if(!this.enableCreationOnBlur)
					{
						this.resetInputValue();
						this.setItems(this.getDefaultItems());
						BX.cleanNode(this.popupAlertContainer);
						this.newAlertContainer = null;
					}
					else
					{
						this.onEmptyValueEvent();
					}
				}
			},
			isTargetElementChanged: function()
			{
				return (this.CurrentItem && this.CurrentItem.title.length !== this.targetElement.value.length)
					|| (!this.CurrentItem && this.targetElement.value.length > 0);
			},
			getDefaultItems: function()
			{
				return this.defaultItems;
			},
			setDefaultItems: function(defaultItems)
			{
				this.defaultItems = defaultItems;
			},
			getItems: function()
			{
				return this.items;
			},
			updateItemsList: function (items)
			{
				this.setDefaultItems(items);
				this.setItems(items);
			},
			setItems: function(items)
			{
				this.items = items;
				if (this.popupWindow)
				{
					this.renderItemsToInnerContainer();

					this.popupWindow.adjustPosition({
						forceBindPosition: true
					});
				}
			},
			handleTypeInField: function()
			{
				if(!this.isChanged) {
					this.isChanged = true;
				}

				var searchQuery = this.targetElement.value.trim();
				var isRequestsFlowAllowed =
					this.autocompleteDelay === 0
						? true
						: this.isLastSearchComplete !== false
				;
				var loader = this.getItemsListContainer();

				if(!searchQuery)
				{
					this.setItems(this.getDefaultItems());
					loader.classList.remove('ui-dropdown-loader-active');
					BX.cleanNode(this.popupAlertContainer);
					this.newAlertContainer = null;
					this.previousSearchQuery = '';

					BX.onCustomEvent(this, "BX.UI.Dropdown:onReset", [this]);
				}
				else if(
					searchQuery.length >= this.minSearchStringLength
					&& searchQuery !== this.previousSearchQuery
					&& isRequestsFlowAllowed
				) {
					BX.onCustomEvent(this, "BX.UI.Dropdown:onBeforeSearchStart", [this, searchQuery]);
					clearTimeout(this.ajaxRequestTimer);
					this.ajaxRequestTimer = setTimeout(this.searchItemsByStrDelayed.bind(this), this.autocompleteDelay);

					loader.classList.add('ui-dropdown-loader-active');
				}
			},
			searchItemsByStrDelayed: function()
			{
				this.isLastSearchComplete = false;
				var loader = this.getItemsListContainer();
				var searchQuery = this.targetElement.value.trim();
				var eventData = {
					searchQuery: searchQuery
				};
				BX.onCustomEvent(this, "BX.UI.Dropdown:onSearchStart", [this, eventData]);

				this.searchItemsByStr(eventData.searchQuery)
					.then(
						function (items)
						{
							BX.onCustomEvent(this, "BX.UI.Dropdown:onSearchComplete", [this, items]);

							return items;
						}.bind(this))
					.then(
						function (items)
						{
							if(!this.newAlertContainer && this.enableCreation &&
								BX.Type.isDomNode(this.popupAlertContainer))
							{
								var newAlertContainer = this.getNewAlertContainer(items);
								if (BX.Type.isDomNode(newAlertContainer))
								{
									this.popupAlertContainer.appendChild(newAlertContainer);
								}
								BX.bind(document, "click", this.documentClickHandler);
							}
							this.setItems(items);
							loader.classList.remove('ui-dropdown-loader-active');

							this.showPopupWindow();

							this.previousSearchQuery = searchQuery;
							this.isLastSearchComplete = true;
							this.handleTypeInField();
						}.bind(this)
				);
			},
			searchItemsByStr: function(target)
			{
				return BX.ajax.runAction(
					this.searchAction,
					{ data: { searchQuery: target, options: this.searchOptions } }
				).then(this.onSearchRequestSuccess.bind(this));
			},
			onSearchRequestSuccess: function(results)
			{
				return BX.prop.getArray(BX.prop.getObject(results, "data", {}), "items", []);
			},
			getFooterItems: function()
			{
				return this.footerItems;
			},
			setFooterItems: function(items)
			{
				if (Array.isArray(items))
				{
					this.footerItems = items;
				}
			},
			showPopupWindow: function()
			{
				var popupAlertContainer = this.getPopupAlertContainer();
				if (this.getItems().length || this.footerItems || (popupAlertContainer && popupAlertContainer.textContent.trim().length))
				{
					this.getPopupWindow().show();
				}
			},
			getPopupWindow: function()
			{
				if (!this.popupWindow)
				{
					this.popupWindow = new BX.PopupWindow("dropdown_" + this.id, this.targetElement, {
						autoHide: true,
						content : this.popupConatiner ? this.popupContainer : this.getPopupContainer(),
						contentColor : "white",
						closeByEsc: true,
						className: "ui-dropdown-window",
						events: {
							onPopupClose: function()
							{
								this.popupWindow.destroy();
								this.popupWindow = null;
								this.itemListContainer = null;
								this.itemListInnerContainer = null;
								this.newAlertContainer = null;
								this.popupAlertContainer = null;
							}.bind(this)
						}
					});

					if(this.isEmbedded && this.quickForm)
					{
						this.popupWindow.setOffset({
							offsetLeft: this.getQuickFormOffsetWidth(),
						});
					}
				}

				this.setWidthPopup();
				BX.bind(window, "resize", this.setWidthPopup.bind(this));

				return this.popupWindow;
			},
			getQuickFormOffsetWidth: function()
			{
				var currentElement = BX.findParent(this.getPopupWindow().bindElement, {className: 'crm-kanban-quick-form'});

				if (!currentElement)
				{
					return 0;
				}
				this.popupWindow.popupContainer.style.width = currentElement.offsetWidth + "px";
				var equalWidth = - (this.targetElement.getBoundingClientRect().left - currentElement.getBoundingClientRect().left);

				return equalWidth;
			},
			setWidthPopup: function()
			{
				if(!this.isEmbedded && this.quickForm)
				{
					if(this.popupWindow && this.targetElement)
					{
						this.popupWindow.popupContainer.style.width = this.targetElement.offsetWidth + "px"
					}
				}
			},
			onEmptyValueEvent: function()
			{
				this.emptyValueEventHandle = window.setTimeout(
					function()
					{
						this.emptyValueEventHandle = 0;
						if(this.enableCreation)
						{
							BX.onCustomEvent(this, "BX.UI.Dropdown:onAdd", [this, { title: this.targetElement.value }]);

							this.setItems(this.getDefaultItems());
							BX.cleanNode(this.popupAlertContainer);
							this.newAlertContainer = null;
						}
						else
						{
							this.resetInputValue();
						}
					}.bind(this),
					0
				);
			},
			resetInputValue: function() {
				this.targetElement.value = "";

				BX.onCustomEvent(this, "BX.UI.Dropdown:onReset", [this]);
			},
			destroyPopupWindow: function()
			{
				BX.unbind(document, "click", this.documentClickHandler);

				if(!this.popupWindow)
				{
					return;
				}

				this.popupWindow.close();
			},
			getPopupContainer: function()
			{
				this.popupContainer = this.getItemsListContainer();

				this.popupContainer.appendChild(this.getItemsListInnerContainer());
				this.popupContainer.appendChild(this.getPopupAlertContainer());

				this.renderItemsToInnerContainer();
				if (this.footerItems)
				{
					this.popupContainer.appendChild(this.getLoaderContainer());
					this.popupContainer.appendChild(this.getFooterContent());
				}

				return this.popupContainer;
			},
			getPopupAlertContainer: function()
			{
				if(!this.popupAlertContainer)
				{
					this.popupAlertContainer = BX.create("div", {
						attrs: { className: 'ui-dropdown-alert-container' }
					});
				}

				return this.popupAlertContainer;
			},
			getNewAlertContainer: function(items)
			{
				if(!this.newAlertContainer)
				{
					this.newAlertContainer = BX.create('div', {
						props: {
							className: 'ui-dropdown-alert'
						},
						children: [
							this.alertContainerValue = BX.create('div', {
								attrs: { className: 'ui-dropdown-alert-name' },
								text: this.targetElement.value
							}),
							BX.create('div', {
								attrs: { className: 'ui-dropdown-alert-text' },
								text: BX.prop.getString(this.messages, this.enableCreation ? "creationLegend" : "notFound", ""),
								events: {
									click: this.onEmptyValueEvent.bind(this)
								}
							})
						]
					});
					BX.onCustomEvent(this, "BX.UI.Dropdown:onGetNewAlertContainer", [this, this.newAlertContainer]);
					this.targetElement.addEventListener("input", function()
					{
						this.alertContainerValue.textContent = this.targetElement.value;
					}.bind(this));

					if(!this.enableCreation)
					{
						this.targetElement.addEventListener("input", function()
						{
							this.alertContainerValue.style.display = "none";
						}.bind(this));
					}
				}

				if(items.length > 0 && !this.enableCreation)
				{
					this.newAlertContainer.style.display = "none";
				}
				else
				{
					this.newAlertContainer.style.display = "";
				}

				return this.newAlertContainer;
			},
			getItemsListContainer: function()
			{
				if(!this.itemListContainer)
				{
					this.itemListContainer = BX.create('div', {
						attrs: {
							className: 'ui-dropdown-container'
						}
					});
				}
				return this.itemListContainer;
			},
			getItemsListInnerContainer: function()
			{
				if(!this.itemListInnerContainer)
				{
					this.itemListInnerContainer = BX.create('div', {
						attrs: {
							className: 'ui-dropdown-inner'
						}
					});
				}
				return this.itemListInnerContainer;
			},
			getItemNodeList: function()
			{
				var result = [];

				this.getItems().forEach(function(item)
				{
					var attrs = BX.prop.getObject(item, "attributes", {});

					var icon = BX.prop.getObject(attrs, "icon", null);
					var phones = BX.prop.getArray(attrs, "phone", []);
					var emails = BX.prop.getArray(attrs, "email", []);
					var webs = BX.prop.getArray(attrs, "web", []);

					var iconSrc = (icon !== null && BX.type.isNotEmptyString(icon.src) ? icon.src : '');
					var phone = phones.length > 0 ? phones[0].value : "";
					var email = emails.length > 0 ? emails[0].value : "";
					var web = (webs.length > 0 ? webs[0].value : '');

					item.node = BX.create('div', {
						attrs: {
							className: 'ui-dropdown-item' + (iconSrc !== '' ? ' ui-dropdown-item-node-with-icon' : '')
						},
						events: { click: this.handleItemClick.bind(this, item) },
						children: [
							iconSrc !== '' ? BX.create('span', {
								attrs: {
									className: 'ui-dropdown-item-icon',
									style: 'background-image: url(\'' + BX.util.htmlspecialchars(iconSrc) + '\')'
								},
								text: ''
							}) : null,
							BX.create('div', {
								attrs: {
									className: 'ui-dropdown-item-name'
								},
								text: item.title
							}),
							BX.create('div', {
								attrs: {
									className: 'ui-dropdown-item-subname'
								},
								text: item.subTitle || ''
							}),
							BX.create('div', {
								attrs: {
									className: 'ui-dropdown-contact-info'
								},
								children: [
									phone !== "" ? BX.create('div', {
										attrs: {
											className: 'ui-dropdown-contact-info-item ui-dropdown-item-phone'
										},
										text: phone
									}) : null,
									email !== "" ? BX.create('div', {
										attrs: {
											className: 'ui-dropdown-contact-info-item ui-dropdown-item-email'
										},
										text: email
									}) : null,
									web !== "" ? BX.create('div', {
										attrs: {
											className: 'ui-dropdown-contact-info-item ui-dropdown-item-web'
										},
										text: web
									}) : null
								]
							})
						]
					});
					result.push(item.node);
				}, this);

				return result;
			},
			renderItemsToInnerContainer: function()
			{
				var innerContainer = this.getItemsListInnerContainer();
				BX.cleanNode(innerContainer);

				var itemsNodeList = this.getItemNodeList();
				// var itemAlertNode = this.getPopupAlertContainer();

				for (var i = 0; i < itemsNodeList.length; i++)
				{
					innerContainer.appendChild(itemsNodeList[i]);
				}

				// innerContainer.appendChild(itemAlertNode);

				return innerContainer;
			},
			getLoaderContainer: function()
			{
				return BX.create('div', {
					props: {
						className: 'ui-dropdown-loader-container'
					},
					html: '<svg class="ui-dropdown-loader" viewBox="25 25 50 50"><circle class="ui-dropdown-loader-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"></circle><circle class="ui-dropdown-loader-inner-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"></circle></svg>'
				})
			},
			getFooterContent: function()
			{
				var footerContainer = BX.create('div', {

				});

				this.getFooterItems().forEach(function(footerItem)
				{

					var footer = BX.create('div', {
						attrs: {
							className: 'ui-dropdown-footer'
						},
						children: [
							BX.create('div', {
								attrs: {
									className: 'ui-dropdown-caption-box'
								},
								children: [
									BX.create('div', {
										attrs: {
											className: 'ui-dropdown-caption'
										},
										text: footerItem.caption
									})
								]
							})
						]
					});
					footerItem.buttons.forEach(function(button)
					{
						footer.appendChild(
							BX.create('div', {
								attrs: {
									className: 'ui-dropdown-button-add'
								},
								text: button.caption,
								events: button.events
							})
						);
					});
					footerContainer.appendChild(footer);

				});

				return footerContainer;
			},
			handleUpArrow: function()
			{
				if(this.newAlertContainer && this.popupAlertContainer.classList.contains('ui-dropdown-item-highlight'))
				{
					if (this.items && this.items.length > 0)
					{
						itemToHighlight = this.getItemByIndex(this.items.length - 1);
						this.popupAlertContainer.classList.remove('ui-dropdown-item-highlight');
						this.cleanHighlightingItem();
						this.highlightItem(itemToHighlight);
					}
					return;
				}

				if (!this.items)
				{
					return;
				}

				var highlightElementIndex = this.getHighlightItemIndex();
				var itemToHighlight = null;
				if (highlightElementIndex === null)
				{
					itemToHighlight = this.getLastItem();
				}
				else
				{
					if (this.items.length >= highlightElementIndex + 1)
					{
						itemToHighlight = this.getItemByIndex(highlightElementIndex-1);
					}
				}
				if (itemToHighlight)
				{
					this.cleanHighlightingItem();
					this.highlightItem(itemToHighlight);
				}
			},
			handleDownArrow: function()
			{
				var highlightElementIndex = this.getHighlightItemIndex();

				if(this.newAlertContainer && this.popupAlertContainer.classList.contains('ui-dropdown-item-highlight'))
				{
					return;
				}

				if (!this.items || this.items.length === 0)
				{
					if(this.newAlertContainer && !this.popupAlertContainer.classList.contains('ui-dropdown-item-highlight'))
					{
						this.popupAlertContainer.classList.add('ui-dropdown-item-highlight');
					}

					return;
				}

				if(highlightElementIndex === this.items.length - 1)
				{
					if(this.newAlertContainer && !this.popupAlertContainer.classList.contains('ui-dropdown-item-highlight'))
					{
						this.cleanHighlightingItem();
						this.popupAlertContainer.classList.add('ui-dropdown-item-highlight');
					}

					return;
				}

				var itemToHighlight = null;
				if (highlightElementIndex === null)
				{
					itemToHighlight = this.getFirstItem();
				}
				else
				{
					itemToHighlight = this.getItemByIndex(highlightElementIndex + 1);
				}
				if (itemToHighlight)
				{
					this.cleanHighlightingItem();
					this.highlightItem(itemToHighlight);
				}

			},
			scrollToItem: function()
			{
				var parent = this.getItemsListInnerContainer();
				var parentBounding = parent.getBoundingClientRect();
				var elemBounding = this.highlightedItem.node.getBoundingClientRect();
				var deltaBottom = parentBounding.bottom - elemBounding.bottom;
				var deltaTop = parentBounding.top - elemBounding.top;

				if(deltaBottom < 0)
				{
					parent.scrollTop = parent.scrollTop + Math.abs(deltaBottom)
				}
				else if(deltaTop > 0)
				{
					parent.scrollTop = parent.scrollTop - deltaTop
				}
			},
			highlightItem: function(item)
			{
				item.node.classList.add('ui-dropdown-item-highlight');
				// this.getItemsListInnerContainer().scrollTop = item.node.offsetTop;
				this.highlightedItem = item;

				this.scrollToItem();
			},
			cleanHighlightingItem: function()
			{
				if (this.highlightedItem)
				{
					this.highlightedItem.node.classList.remove('ui-dropdown-item-highlight');
				}

				this.highlightedItem = null;
			},
			getHighlightItemIndex: function()
			{
				var result = null;
				var items = this.getItems();
				for (var i = 0; i < items.length; i++) {
					if (items[i].node.classList.contains('ui-dropdown-item-highlight'))
					{
						return i
					}
				}
				return result;
			},
			getItemIndex: function(item)
			{
				var items = this.getItems();
				for (var i = 0; i < items.length; i++)
				{
					if (items[i] === item)
					{
						return i;
					}
				}
				return false;
			},
			getFirstItem: function()
			{
				if (!this.items || this.items.length === 0)
				{
					return null;
				}

				return this.items[0];
			},
			getLastItem: function()
			{
				var items = this.getItems();
				if (!items)
				{
					return;
				}

				return items[items.length - 1];
			},

			getItemByIndex: function(index)
			{
				var items = this.getItems();
				if (items[index])
				{
					return items[index];
				}
				return null;
			},

			handleItemClick: function(item, event)
			{
				if(this.emptyValueEventHandle > 0)
				{
					window.clearTimeout(this.emptyValueEventHandle);
					this.emptyValueEventHandle = 0;
				}
				this.CurrentItem = item;
				BX.onCustomEvent(this, "BX.UI.Dropdown:onSelect", [this, item]);
			},

			selectTargetElementValue: function()
			{
				this.targetElement.select();
				this.targetElement.focus();
			},

			disableTargetElement: function()
			{
				this.targetElement.select();
				this.targetElement.focus();

				this.targetElement.addEventListener("keyup", function(e)
				{
					var key = e.charCode || e.keyCode;

					if(this.targetElement === document.activeElement)
					{
						if(key === 8) {
							return true;
						}
						else if(key === 37 || key === 39)
						{
							if(this.targetElement.value.length > 0 && this.targetElement === document.activeElement)
							{
								this.selectTargetElementValue();
							}
						}
						else if(this.targetElement.value.length > 0 && this.targetElement === document.activeElement)
						{
							return true;
						}
						else
						{
							BX.PreventDefault(e);
						}
					}

				}.bind(this));
			},
			enableTargetElement: function()
			{
				this.targetElement.addEventListener("keyup", function() {return true;}.bind(this));
			}
		};


	BX.UI.DropdownUser = function(options)
	{
		BX.UI.Dropdown.call(this, options);
	};


	BX.UI.DropdownUser.prototype =
		{
			__proto__: BX.UI.Dropdown.prototype,

			renderItem: function(item)
			{

				itemsContainer.appendChild(BX.create('div', {
					attrs: {
						className: 'ui-dropdown-item ui-dropdown-item-user'
					},
					children: [
						BX.create('div', {
							attrs: {
								className: 'ui-dropdown-item-icon'
							},
							text: item.user
						}),
						BX.create('div', {
							attrs: {
								className: 'ui-dropdown-info-box'
							},
							children: [
								BX.create('div', {
									attrs: {
										className: 'ui-dropdown-item-name'
									},
									text: item.title
								}),
								BX.create('div', {
									attrs: {
										className: 'ui-dropdown-item-subname'
									},
									text: item.subtitle
								}),
								BX.create('div', {
									attrs: {
										className: 'ui-dropdown-item-value'
									},
									text: item.value
								})
							]
						})
					]
				}))

			}
		};

})();
