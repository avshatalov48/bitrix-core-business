import {BookingUtil} from "calendar.resourcebooking";

export class ResourceSelectorFieldEditControl
{
	constructor(params)
	{
		this.params = params || {};
		this.editMode = !!this.params.editMode;
		this.id = this.params.id || 'resource-selector-' + Math.round(Math.random() * 100000);
		this.resourceList = BX.type.isArray(params.resourceList) ? params.resourceList : [];
		this.checkLimit = BX.type.isFunction(params.checkLimitCallback) ? params.checkLimitCallback : false;
		this.checkLimitForNew = BX.type.isFunction(params.checkLimitCallbackForNew) ? params.checkLimitCallbackForNew : false;

		this.selectedValues = [];
		this.selectedValuesIndex = {};

		this.selectedBlocks = [];
		this.newValues = [];

		this.DOM = {
			outerWrap: this.params.outerWrap,
			blocksWrap: this.params.blocksWrap || false,
			listWrap: this.params.listWrap
		};

		if (this.editMode)
		{
			this.DOM.controlsWrap = this.params.controlsWrap;
		}
		else
		{
			this.DOM.arrowNode = BX.create("span", {props: {className: "calendar-resourcebook-content-block-detail-icon calendar-resourcebook-content-block-detail-icon-arrow"}});
		}

		this.onChangeCallback = this.params.onChangeCallback || null;

		this.create();
		this.setValues(params.values);
	}


	create ()
	{
		BX.addClass(this.DOM.outerWrap, 'calendar-resourcebook-resource-list-wrap calendar-resourcebook-folding-block' + (this.params.shown !== false ? ' shown' : ''));

		if (this.editMode)
		{
			this.DOM.addButton = this.DOM.controlsWrap.appendChild(BX.create("span", {
				props: {className: "calendar-resource-content-block-add-link"},
				text: BX.message('USER_TYPE_RESOURCE_ADD'),
				events: {click: BX.delegate(this.addResourceBlock, this)}
			}));

			if (this.resourceList.length > 0)
			{
				this.DOM.selectButton = this.DOM.controlsWrap.appendChild(BX.create("span", {
					props: {className: "calendar-resource-content-block-add-link"},
					text: BX.message('USER_TYPE_RESOURCE_SELECT'),
					events: {click: BX.delegate(this.openResourcesPopup, this)}
				}));
			}
		}
		else
		{
			BX.bind(this.DOM.blocksWrap, 'click', BX.delegate(this.handleBlockClick, this));
		}
	}

	show ()
	{
		BX.addClass(this.DOM.outerWrap, 'shown');
	}

	hide ()
	{
		this.DOM.outerWrap.style.maxHeight = '';
		BX.removeClass(this.DOM.outerWrap, 'shown');
	}

	isShown ()
	{
		return BX.hasClass(this.DOM.outerWrap, 'shown');
	}

	handleBlockClick (e)
	{
		let target = e.target || e.srcElement;

		if (target)
		{
			let blockValue = target.getAttribute('data-bx-remove-block');
			if (blockValue)
			{
				// Remove from blocks
				this.selectedBlocks.find(function(element, index)
				{
					if (element.value === blockValue)
					{
						BX.removeClass(element.wrap, 'shown');
						setTimeout(BX.delegate(function ()
						{
							BX.remove(element.wrap)
						}, this), 300);

						this.selectedBlocks = BX.util.deleteFromArray(this.selectedBlocks, index);
					}
				}, this);

				// Remove from values
				this.selectedValues.find(function(element, index)
				{
					if (element.title === blockValue)
					{
						this.selectedValues = BX.util.deleteFromArray(this.selectedValues, index);
					}
				}, this);

				if (BX.type.isFunction(this.onChangeCallback))
				{
					setTimeout(BX.proxy(this.onChangeCallback, this), 100);
				}

				this.checkBlockWrapState();
			}

			if (!blockValue)
			{
				this.openResourcesPopup();
			}
		}
	}

	openResourcesPopup ()
	{
		if (!this.resourceList.length)
		{
			return this.addResourceBlock();
		}

		if (this.isResourcesPopupShown())
		{
			return;
		}

		let menuItems = [];

		this.resourceList.forEach(function(resource)
		{
			if (resource.deleted)
			{
				return;
			}

			menuItems.push({
				text: BX.util.htmlspecialchars(resource.title),
				dataset: {
					type: resource.type,
					id: resource.id,
					title: resource.title
				},
				onclick: BX.delegate(function(e, menuItem)
				{
					let
						selectAllcheckbox,
						target = e.target || e.srcElement,
						checkbox = menuItem.layout.item.querySelector('.menu-popup-item-resource-checkbox'),
						foundResource = this.resourceList.find(function(resource)
						{
							return parseInt(resource.id) === parseInt(menuItem.dataset.id)
								&& resource.type === menuItem.dataset.type;
						}, this);

					if (foundResource)
					{
						// Complete removing of the resource
						if (target && BX.hasClass(target, "calendar-resourcebook-content-block-control-delete"))
						{
							this.removeResourceBlock({
								resource: foundResource,
								trigerOnChange: true
							});
							this.selectedValues = this.getSelectedValues();
							this.checkResourceInputs();

							selectAllcheckbox = this.popupContainer.querySelector('.menu-popup-item-all-resources-checkbox');
							this.selectAllChecked = false;
							if (selectAllcheckbox)
							{
								selectAllcheckbox.checked = false;
							}

							let menuItemNode = BX.findParent(target, {className: 'menu-popup-item'});
							if (menuItemNode)
							{
								BX.addClass(menuItemNode, 'menu-popup-item-resource-remove-loader');

								menuItemNode.appendChild(BookingUtil.getLoader(25));
								let textNode = menuItemNode.querySelector('.menu-popup-item-text');
								if (textNode)
								{
									textNode.innerHTML = BX.message('USER_TYPE_RESOURCE_DELETING');
								}
							}

							foundResource.deleted = true;
							setTimeout(BX.delegate(function()
							{
								if (menuItemNode)
								{
									menuItemNode.style.maxHeight = '0';
								}

								if (!this.resourceList.find(function(resource){return !resource.deleted;}))
								{
									BX.PopupMenu.destroy(this.id);
									this.DOM.selectButton.style.opacity = 0;

									setTimeout(BX.delegate(function(){BX.remove(this.DOM.selectButton);}, this), 500);
								}
							}, this), 500);
						}
						else if (target && (BX.hasClass(target, "menu-popup-item") || BX.hasClass(target, "menu-popup-item-resource-checkbox") || BX.hasClass(target, "menu-popup-item-inner") ))
						{
							if (!BX.hasClass(target, "menu-popup-item-resource-checkbox"))
							{
								checkbox.checked = !checkbox.checked;
							}

							if (checkbox.checked)
							{
								this.addResourceBlock({
									resource: foundResource,
									value: foundResource.title,
									trigerOnChange: true
								});
								this.selectedValues = this.getSelectedValues();
							}
							else
							{
								this.removeResourceBlock({
									resource: foundResource,
									trigerOnChange: true
								});
								this.selectedValues = this.getSelectedValues();
								this.checkResourceInputs();

								selectAllcheckbox = this.popupContainer.querySelector('.menu-popup-item-all-resources-checkbox');
								this.selectAllChecked = false;
								if (selectAllcheckbox)
								{
									selectAllcheckbox.checked = false;
								}
							}
						}
					}
				}, this)
			});
		}, this);

		if (menuItems.length > 1)
		{
			menuItems.push({
				text: BX.message('USER_TYPE_RESOURCE_SELECT_ALL'),
				onclick: BX.delegate(function(e, menuItem)
				{
					let target = e.target || e.srcElement;
					if (target && (BX.hasClass(target, "menu-popup-item") || BX.hasClass(target, "menu-popup-item-resource-checkbox")))
					{
						let checkbox = menuItem.layout.item.querySelector('.menu-popup-item-resource-checkbox');

						if (BX.hasClass(target, "menu-popup-item"))
						{
							checkbox.checked = !checkbox.checked;
						}

						let i, checkboxes = this.popupContainer.querySelectorAll('input.menu-popup-item-resource-checkbox');
						this.selectAllChecked = checkbox.checked;

						for (i = 0; i < checkboxes.length; i++)
						{
							checkboxes[i].checked = this.selectAllChecked;
						}

						this.resourceList.forEach(function(resource){
							if (resource.deleted)
							{
								return;
							}

							if (this.selectAllChecked)
							{
								this.addResourceBlock({
									resource: resource,
									value: resource.title,
									trigerOnChange: true
								});
							}
							else
							{
								this.removeResourceBlock({
									resource: resource,
									trigerOnChange: true
								});
							}
						}, this);

						this.selectedValues = this.getSelectedValues();
						this.checkResourceInputs();
					}
				}, this)
			});
		}

		this.popup = BX.PopupMenu.create(
			this.id,
			this.DOM.selectButton || this.DOM.blocksWrap,
			menuItems,
			{
				className: 'popup-window-resource-select',
				closeByEsc : true,
				autoHide : false,
				offsetTop: 0,
				offsetLeft: 0
			}
		);

		this.popup.show(true);
		this.popupContainer = this.popup.popupWindow.popupContainer;
		if (!this.editMode)
		{
			this.popupContainer.style.width = parseInt(this.DOM.blocksWrap.offsetWidth) + 'px';
		}

		BX.addCustomEvent(this.popup.popupWindow, 'onPopupClose', BX.proxy(function(){BX.PopupMenu.destroy(this.id);}, this));

		this.popup.menuItems.forEach(function(menuItem)
		{
			let checked;
			if (menuItem.dataset && menuItem.dataset.type)
			{
				checked = this.selectedValues.find(function(item)
				{
					return parseInt(item.id) === parseInt(menuItem.dataset.id)
						&& item.type === menuItem.dataset.type;
				});
				menuItem.layout.item.className = 'menu-popup-item';
				menuItem.layout.item.innerHTML = '<div class="menu-popup-item-inner">' +
					'<div class="menu-popup-item-resource">' +
					'<input class="menu-popup-item-resource-checkbox" type="checkbox"' + (checked ? 'checked="checked"' : '') + ' id="' + menuItem.id + '">' +
					'<label class="menu-popup-item-text" for="' + menuItem.id + '">' + BX.util.htmlspecialchars(menuItem.dataset.title) + '</label>' +
					'</div>' +
					(this.editMode ? '<div class="calendar-resourcebook-content-block-control-delete"></div>' : '') +
					'</div>';
			}
			else
			{
				this.selectAllChecked = !this.resourceList.find(function(resource){
					return !this.selectedValues.find(function(item)
					{
						return parseInt(item.id) === parseInt(resource.id)
							&& item.type === resource.type
					});
				},this);

				menuItem.layout.item.className = 'menu-popup-item menu-popup-item-resource-all';
				menuItem.layout.item.innerHTML = '<div class="menu-popup-item-inner">' +
					'<div class="menu-popup-item-resource">' +
					'<input class="menu-popup-item-resource-checkbox menu-popup-item-all-resources-checkbox" type="checkbox"' + (this.selectAllChecked ? 'checked="checked"' : '') + ' id="' + menuItem.id + '">' +
					'<label class="menu-popup-item-text" for="' + menuItem.id + '">' + BX.message('USER_TYPE_RESOURCE_SELECT_ALL') + '</label>' +
					'</div>' +
					'</div>';
			}
		}, this);

		setTimeout(BX.delegate(function(){
			BX.bind(document, 'click', BX.proxy(this.handleClick, this));
		}, this), 50);
	}

	addResourceBlock(params)
	{
		if (!BX.type.isPlainObject(params))
		{
			params = {};
		}

		if ((params.resource && (this.checkLimit && !this.checkLimit() && window.B24))
			||
			(!params.resource && (this.checkLimitForNew && !this.checkLimitForNew() && window.B24)))
		{
			return BookingUtil.showLimitationPopup();
		}

		let
			_this = this,
			blockEntry;

		if (this.editMode)
		{
			if (params.resource && this.selectedValues.find(function(val)
			{
				return val.id && parseInt(val.id) === parseInt(params.resource.id)
					&& val.type === params.resource.type;
			}))
			{
				return;
			}

			if (!params.value)
			{
				params.value = '';
			}

			blockEntry = {
				value: params.value,
				wrap : this.DOM.listWrap
					.appendChild(BX.create("div", {props:{className: "calendar-resourcebook-content-block-detail calendar-resourcebook-outer-resource-wrap"}}))
					.appendChild(BX.create("div", {props:{className: "calendar-resourcebook-content-block-detail-resource"}}))
					.appendChild(BX.create("div", {props:{className: "calendar-resourcebook-content-block-detail-resource-inner calendar-resourcebook-content-block-detail-resource-inner-wide"}}))
			};

			blockEntry.input = blockEntry.wrap.appendChild(BX.create("input", {
				props:{
					className: "calendar-resourcebook-content-input",
					value: params.value,
					type: 'text',
					placeholder: BX.message('USER_TYPE_RESOURCE_NAME')
				},
				dataset: {
					resourceType: params.resource ? params.resource.type : '',
					resourceId: params.resource ? params.resource.id : ''
				}
			}));
			blockEntry.delButton = blockEntry.wrap.appendChild(BX.create("div", {
				props:{className: "calendar-resourcebook-content-block-control-delete"},
				events: {click(){
						BX.remove(BX.findParent(this, {className: 'calendar-resourcebook-outer-resource-wrap'}));
						_this.selectedValues = _this.getSelectedValues();
						_this.checkResourceInputs();
					}}
			}));

			if (params.focusInput !== false)
			{
				BX.focus(blockEntry.input);
			}
		}
		else
		{
			if (params.value && this.selectedBlocks.find(function(val){return val.value && val.value === params.value;}))
			{
				return;
			}

			blockEntry = {
				value: params.value,
				resource: params.resource || false,
				wrap : this.DOM.blocksWrap.appendChild(BX.create("div", {
					props:{
						className: "calendar-resourcebook-content-block-control-inner"
							+ (params.animation ? '' : ' shown')
							+ (params.transparent ? ' transparent' : '')
					},
					children: [
						BX.create("div", {
							props: {className: "calendar-resourcebook-content-block-control-text"},
							text: params.value || ''
						}),
						BX.create("div", {
							attrs: {'data-bx-remove-block': params.value},
							props: {className: "calendar-resourcebook-content-block-control-delete"}
						})
					]
				}))
			};

			this.selectedBlocks.push(blockEntry);

			// Show it with animation
			if (params.animation)
			{
				setTimeout(BX.delegate(function ()
				{
					BX.addClass(blockEntry.wrap, 'shown');
				}, this), 1);
			}

			if (params.trigerOnChange !== false && this.onChangeCallback && BX.type.isFunction(this.onChangeCallback))
			{
				setTimeout(BX.proxy(this.onChangeCallback, this), 100);
			}

			this.checkBlockWrapState();
		}

		// Adjust outer wrap max height
		if (this.DOM.listWrap && this.DOM.outerWrap)
		{
			if (BX.hasClass(this.DOM.outerWrap, 'shown'))
			{
				this.DOM.outerWrap.style.maxHeight = Math.max(10000, this.DOM.listWrap.childNodes.length * 45 + 100) + 'px';
			}
			else
			{
				this.DOM.outerWrap.style.maxHeight = '';
			}
		}

		return blockEntry;
	}

	removeResourceBlock(params)
	{
		if (this.editMode)
		{
			let
				resourceType, resourceId,
				i, inputs = this.DOM.listWrap.querySelectorAll('.calendar-resourcebook-content-input');

			for (i = 0; i < inputs.length; i++)
			{
				resourceType = inputs[i].getAttribute('data-resource-type');
				resourceId = inputs[i].getAttribute('data-resource-id');
				if (resourceType === params.resource.type && parseInt(resourceId) === parseInt(params.resource.id))
				{
					BX.remove(BX.findParent(inputs[i], {className: 'calendar-resourcebook-outer-resource-wrap'}));
				}
			}
		}
		else
		{
			if (params.resource)
			{
				this.selectedBlocks.find(function(element, index)
				{
					if (element.value === params.resource.title)
					{
						BX.removeClass(element.wrap, 'shown');
						setTimeout(BX.delegate(function ()
						{
							BX.remove(element.wrap)
						}, this), 300);

						this.selectedBlocks = BX.util.deleteFromArray(this.selectedBlocks, index);
					}
				}, this);
			}
			this.checkBlockWrapState();

			if (params.trigerOnChange !== false && this.onChangeCallback && BX.type.isFunction(this.onChangeCallback))
			{
				setTimeout(BX.proxy(this.onChangeCallback, this), 100);
			}
		}
	}

	checkResourceInputs()
	{
		if (this.editMode)
		{
			if (!this.selectedValues.length)
			{
				this.addResourceBlock({animation: true});
			}
		}
	}

	checkBlockWrapState()
	{
		if (!this.editMode)
		{
			if (!this.selectedBlocks.length)
			{
				if (!this.DOM.emptyPlaceholder)
				{
					this.DOM.emptyPlaceholder = this.DOM.blocksWrap.appendChild(
						BX.create("DIV", {
							props : {className : "calendar-resourcebook-content-block-control-empty"},
							html: '<span class="calendar-resourcebook-content-block-control-text">' + BX.message('USER_TYPE_RESOURCE_LIST_PLACEHOLDER') + '</span>'
						})
					);
				}
				else
				{
					this.DOM.emptyPlaceholder.className = "calendar-resourcebook-content-block-control-empty";
					this.DOM.blocksWrap.appendChild(this.DOM.emptyPlaceholder);
				}

				setTimeout(BX.delegate(function(){
					if (BX.isNodeInDom(this.DOM.emptyPlaceholder))
					{
						BX.addClass(this.DOM.emptyPlaceholder, 'show');
					}
				}, this), 50);
			}
			else if (this.DOM.emptyPlaceholder)
			{
				BX.remove(this.DOM.emptyPlaceholder);
			}
		}
	}

	handleClick(e)
	{
		let target = e.target || e.srcElement;
		if (this.isResourcesPopupShown() && !BX.isParentForNode(this.popupContainer, target)
		)
		{
			this.closeResourcesPopup({animation: true});
		}
	}

	isResourcesPopupShown()
	{
		return this.popup && this.popup.popupWindow &&
			this.popup.popupWindow.isShown && this.popup.popupWindow.isShown() &&
			this.popup.popupWindow.popupContainer &&
			BX.isNodeInDom(this.popup.popupWindow.popupContainer)
	}

	closeResourcesPopup(params)
	{
		if (this.popup)
		{
			this.popup.close();
			this.popupContainer.style.maxHeight = '';
			BX.unbind(document, 'click', BX.proxy(this.handleClick, this));
		}
	}

	getValues()
	{
		return this.resourceList;
	}

	addToSelectedValues(value)
	{
		if (!this.selectedValues.find(function(val){return parseInt(val.id) === parseInt(value.id) && val.type === value.type;}))
		{
			this.selectedValues.push(value);
		}
	}

	getSelectedValues()
	{
		this.selectedValues = [];
		if (this.editMode)
		{
			let
				resourceType, resourceId, i,
				inputs = this.DOM.listWrap.querySelectorAll('.calendar-resourcebook-content-input');

			for (i = 0; i < inputs.length; i++)
			{
				resourceType = inputs[i].getAttribute('data-resource-type');
				resourceId = inputs[i].getAttribute('data-resource-id');
				if (resourceType && resourceId)
				{
					this.selectedValues.push({type: resourceType, id: resourceId, title: inputs[i].value});
				}
				else
				{
					this.selectedValues.push({type: 'resource', title: inputs[i].value});
				}
			}
		}
		else
		{
			this.selectedBlocks.forEach(function(element){
				this.selectedValues.push({type: element.resource.type, id: element.resource.id});
			}, this);
		}

		return this.selectedValues;
	}

	getDeletedValues()
	{
		return this.resourceList.filter(function(resource){return resource.deleted;});
	}

	setValues(values, trigerOnChange)
	{
		this.selectedBlocks.forEach(function(element){BX.remove(element.wrap);});
		this.selectedBlocks = [];
		trigerOnChange = trigerOnChange !== false;

		if (BX.type.isArray(values))
		{
			values.forEach(function(value)
			{
				let foundResource = this.resourceList.find(function(resource)
				{
					return parseInt(resource.id) === parseInt(value.id) && resource.type === value.type;
				}, this);

				if (foundResource)
				{
					this.addResourceBlock({
						resource: foundResource,
						value: foundResource.title,
						trigerOnChange: trigerOnChange
					});
					this.addToSelectedValues(foundResource);
				}
			}, this);
		}

		if (this.editMode)
		{
			this.selectedValues = this.getSelectedValues();
			this.checkResourceInputs();
		}
		else
		{
			if (this.DOM.arrowNode)
			{
				this.DOM.blocksWrap.appendChild(this.DOM.arrowNode);
			}
		}

		this.checkBlockWrapState();
	}
}