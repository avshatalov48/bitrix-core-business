;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Form");

	var throttle = BX.Landing.Utils.throttle;
	var append = BX.Landing.Utils.append;
	var create = BX.Landing.Utils.create;
	var bind = BX.Landing.Utils.bind;
	var unbind = BX.Landing.Utils.unbind;
	var proxy = BX.Landing.Utils.proxy;
	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var hasClass = BX.Landing.Utils.hasClass;
	var style = BX.Landing.Utils.style;
	var slice = BX.Landing.Utils.slice;
	var findParent = BX.Landing.Utils.findParent;
	var offsetTop = BX.Landing.Utils.offsetTop;
	var offsetLeft = BX.Landing.Utils.offsetLeft;
	var rect = BX.Landing.Utils.rect;
	var remove = BX.Landing.Utils.remove;
	var onTransitionEnd = BX.Landing.Utils.onTransitionEnd;
	var random = BX.Landing.Utils.random;
	var clone = BX.Landing.Utils.clone;
	var isString = BX.Landing.Utils.isString;
	var isArray = BX.Landing.Utils.isArray;
	var isEmpty = BX.Landing.Utils.isEmpty;
	var isPlainObject = BX.Landing.Utils.isPlainObject;
	var join = BX.Landing.Utils.join;
	var onCustomEvent = BX.Landing.Utils.onCustomEvent;

	var FormCollection = BX.Landing.UI.Collection.FormCollection;
	var BaseButton = BX.Landing.UI.Button.BaseButton;


	/**
	 * Implements interface for works with cards form
	 *
	 * @extends {BX.Landing.UI.Form.BaseForm}
	 * @param {{
	 * 		[title]: string,
	 * 		[presets]: object,
	 * 		[sync]: string|string[],
	 * 		[forms]: []
	 * 	}} data
	 * @constructor
	 */
	BX.Landing.UI.Form.CardsForm = function(data)
	{
		BX.Landing.UI.Form.BaseForm.apply(this, arguments);
		this.layout.classList.add("landing-ui-form-cards");
		this.type = "cards";
		this.code = data.code;
		this.presets = data.presets;
		this.childForms = new FormCollection();
		this.presetForm = new FormCollection();
		this.sync = data.sync;
		this.forms = data.forms;

		this.onItemClick = throttle(this.onItemClick, 200, this);
		this.onRemoveItemClick = proxy(this.onRemoveItemClick, this);
		this.onRemoveItemMouseenter = proxy(this.onRemoveItemMouseenter, this);
		this.onRemoveItemMouseleave = proxy(this.onRemoveItemMouseleave, this);
		this.onAddCardClick = proxy(this.onAddCardClick, this);

		this.addButton = this.createAddButton();
		this.wheelEventName = this.getWheelEventName();

		setTimeout(function() {
			this.value = this.serialize();
		}.bind(this));

		this.adjustLastFormState();
		append(this.addButton.layout, this.footer);
	};

	/**
	 * Wraps form
	 * @param {BX.Landing.UI.Form.CardForm} form
	 * @param {BX.Landing.UI.Form.CardsForm} parentForm
	 * @return {HTMLElement}
	 */
	function wrapForm(form, parentForm)
	{
		return create("div", {
			props: {className: "landing-ui-form-cards-item"},
			children: [
				create("div", {
					children: [
						create("div", {
							props: {className: "landing-ui-form-card-item-header"},
							events: {click: parentForm.onItemClick},
							children: [
								create("div", {
									props: {className: "landing-ui-form-card-item-header-left"},
									children: [
										create("div", {
											props: {className: "landing-ui-form-card-item-header-left-inner"},
											children: [
												create("span", {props: {className: "landing-ui-form-card-item-header-drag landing-ui-drag"}}),
												create("span", {props: {className: "landing-ui-form-card-item-header-title"}, children: [form.label]})
											]
										}),
										create("span", {
											props: {className: "landing-ui-form-card-item-header-edit"},
											children: [create("span", {props: {className: "fa fa-pencil"}})]
										})
									]
								}),
								create("div", {
									children: [
										create("span", {
											props: {className: "landing-ui-form-card-item-header-remove"},
											children: [create("span", {props: {className: "fa fa-remove"}})],
											events: {
												click: parentForm.onRemoveItemClick,
												mouseenter: parentForm.onRemoveItemMouseenter,
												mouseleave: parentForm.onRemoveItemMouseleave
											}
										})
									]
								})
							]
						}),
						form.layout
					]
				})
			]
		});
	}


	/**
	 * Makes item as draggable
	 * @param {HTMLElement} item
	 * @param {BX.Landing.UI.Form.CardsForm} parentForm
	 */
	function makeDraggable(item, parentForm)
	{
		var dragButton = item.querySelector(".landing-ui-form-card-item-header-drag");
		dragButton.onbxdragstart = onDragStart.bind(this);
		dragButton.onbxdrag = onDrag.bind(this);
		dragButton.onbxdragstop = onDragEnd.bind(this);

		jsDD.registerObject(dragButton);
		bind(dragButton, "mousedown", onDragButtonMousedown);
		bind(dragButton, "mouseup", onDragButtonMouseup);
		bind(dragButton, "click", onDragButtonClick);

		var scrollContainer = findParent(item, {className: "landing-ui-panel-content-body-content"});
		jsDD.setScrollWindow(scrollContainer);

		var itemStartRect;
		var startOffsetY;
		var startScroll;
		var dragIndex;
		var animationOffset;
		var targetItem;
		var currentItem;
		var minOffset;
		var maxOffset;

		function onDragStart()
		{
			scrollContainer = findParent(item, {className: "landing-ui-panel-content-body-content"});
			itemStartRect = rect(item);
			startOffsetY = Math.max(Math.abs(jsDD.start_y - itemStartRect.top), 0);
			startScroll = scrollContainer.scrollTop;
			dragIndex = [].indexOf.call(slice(item.parentElement.children), item);
			currentItem = findParent(jsDD.current_node, {className: "landing-ui-form-cards-item"});

			var itemComputedStyle = getComputedStyle(item);
			var marginTop = parseInt(itemComputedStyle.getPropertyValue("margin-top"));
			marginTop = marginTop === marginTop ? marginTop : 0;
			var marginBottom = parseInt(itemComputedStyle.getPropertyValue("margin-bottom"));
			marginBottom = marginBottom === marginBottom ? marginBottom : 0;
			animationOffset = itemStartRect.height + ((marginTop + marginBottom) / 2);
			minOffset = -offsetTop(currentItem, currentItem.parentElement);
			var parentRect = rect(currentItem.parentElement);
			maxOffset = parentRect.height - offsetTop(currentItem, currentItem.parentElement) - itemStartRect.height;
		}

		function onDrag(x, y)
		{
			var scrollOffset = startScroll - scrollContainer.scrollTop;
			var dragItemOffset = (y - itemStartRect.top - startOffsetY - scrollOffset);

			dragItemOffset = Math.min(Math.max(dragItemOffset, minOffset), maxOffset);

			void style(item, {
				zIndex: 999,
				transform: "translateY("+dragItemOffset+"px)"
			});

			slice(item.parentElement.children).forEach(function(current, index) {
				if (current !== item)
				{
					var currentRect = current.getBoundingClientRect();
					var currentMiddle = currentRect.top + BX.scrollTop(window) + (currentRect.height / 2);

					if (index > dragIndex && y > currentMiddle &&
						current.style.transform !== 'translate3d(0px, '+(-animationOffset)+'px, 0px)' &&
						current.style.transform !== '')
					{
						targetItem = current;

						void style(current, {
							'transform': 'translate3d(0px, '+(-animationOffset)+'px, 0px)',
							'transition': '300ms'
						});
					}

					if (index < dragIndex && y < currentMiddle &&
						current.style.transform !== 'translate3d(0px, '+(animationOffset)+'px, 0px)' &&
						current.style.transform !== '')
					{
						targetItem = current;

						void style(current, {
							'transform': 'translate3d(0px, '+(animationOffset)+'px, 0px)',
							'transition': '300ms'
						});
					}

					if (((index < dragIndex && y > currentMiddle) ||
							(index > dragIndex && y < currentMiddle)) &&
						current.style.transform !== 'translate3d(0px, 0px, 0px)')
					{
						if (currentMiddle > y)
						{
							targetItem = current.previousElementSibling;
						}
						else
						{
							targetItem = current.nextElementSibling;
						}

						void style(current, {
							'transform': 'translate3d(0px, 0px, 0px)',
							'transition': '300ms'
						});
					}
				}
			});
		}

		function onDragEnd()
		{
			slice(item.parentElement.children).forEach(function(currentItem) {
				void style(currentItem, {
					zIndex: null,
					transform: null,
					transition: null
				});
				setTimeout(function() {
					removeClass(currentItem, "landing-ui-form-card-item-draggable");
				}, 50);
			});

			if (currentItem && targetItem && currentItem !== targetItem && currentItem.parentNode === targetItem.parentNode)
			{
				var currentIndex = [].indexOf.call(targetItem.parentElement.children, currentItem);
				var targetIndex = [].indexOf.call(targetItem.parentElement.children, targetItem);

				if (targetItem.parentElement.children.length === targetIndex)
				{
					targetItem.parentElement.appendChild(target);
				}

				if (currentIndex > targetIndex)
				{
					targetItem.parentElement.insertBefore(currentItem, targetItem);
				}

				if (currentIndex < targetIndex && targetItem.parentElement.children.length !== targetIndex)
				{
					targetItem.parentElement.insertBefore(currentItem, targetItem.nextElementSibling);
				}

				if (parentForm.sync)
				{
					var syncSelectors = parentForm.sync;

					if (isString(parentForm.sync))
					{
						syncSelectors = [parentForm.sync];
					}

					if (isArray(syncSelectors))
					{
						syncSelectors.forEach(function(syncSelector) {
							var syncForm = parentForm.forms.find(function(currentForm) {
								return currentForm.code === syncSelector;
							});

							if (syncForm)
							{
								var syncCurrentItem = syncForm.body.children[currentIndex];
								var syncTargetItem = syncForm.body.children[targetIndex];

								if (syncTargetItem.parentElement.children.length === targetIndex)
								{
									syncTargetItem.parentElement.appendChild(target);
								}

								if (currentIndex > targetIndex)
								{
									syncTargetItem.parentElement.insertBefore(syncCurrentItem, syncTargetItem);
								}

								if (currentIndex < targetIndex && syncTargetItem.parentElement.children.length !== targetIndex)
								{
									syncTargetItem.parentElement.insertBefore(syncCurrentItem, syncTargetItem.nextElementSibling);
								}
							}

							syncForm.childForms.forEach(function(currentSyncForms) {
								var index = [].indexOf.call(
									currentSyncForms.layout.parentElement.parentElement.parentElement.children,
									currentSyncForms.layout.parentElement.parentElement
								);

								currentSyncForms.oldIndex = getSelectorIndex(currentSyncForms.selector);
								currentSyncForms.selector = join(currentSyncForms.selector.split("@")[0], "@", index);
							});

							syncForm.childForms.sort(function(a, b) {
								return parseInt(a.selector.split("@")[1]) < parseInt(b.selector.split("@")[1]) ? -1 : 1;
							});
						});
					}
				}
			}

			parentForm.childForms.forEach(function(form) {
				var index = [].indexOf.call(
					form.layout.parentElement.parentElement.parentElement.children,
					form.layout.parentElement.parentElement
				);

				form.oldIndex = getSelectorIndex(form.selector);
				form.selector = join(form.selector.split("@")[0], "@", index);
			});

			parentForm.childForms.sort(function(a, b) {
				return parseInt(a.selector.split("@")[1]) < parseInt(b.selector.split("@")[1]) ? -1 : 1;
			});
		}

		function onDragButtonClick(event)
		{
			event.preventDefault();
			event.stopPropagation();
		}

		function onDragButtonMousedown()
		{
			addClass(item, "landing-ui-form-card-item-draggable");
		}

		function onDragButtonMouseup()
		{
			removeClass(item, "landing-ui-form-card-item-draggable");
		}
	}


	/**
	 * @param {BX.Landing.UI.Form.CardForm} form
	 */
	function initBindings(form)
	{
		var labelSelectors = [];

		if (isString(form.labelBindings))
		{
			labelSelectors.push(form.labelBindings);
		}
		else if (isArray(form.labelBindings))
		{
			labelSelectors = labelSelectors.concat(form.labelBindings);
		}

		form.fields.forEach(function(field) {
			field.layout.hidden = true;
			field.reset();
			field.layout.hidden = false;
		});

		var textItemIndex = -1;

		labelSelectors.forEach(function(selector) {
			var field = form.fields.find(function(currentField) {
				return currentField.selector.split("@")[0] === selector;
			});

			if (field)
			{
				var item = findParent(form.layout, {className: "landing-ui-form-cards-item"});
				var labelContainer;

				if (field instanceof BX.Landing.UI.Field.Link)
				{
					labelContainer = item.querySelector(".landing-card-title-link");
					labelContainer.innerHTML = BX.message("LANDING_CARDS_FORM_ITEM_PLACEHOLDER_TEXT");

					onCustomEvent(field, "BX.Landing.UI.Field:change", function(value) {
						labelContainer.innerHTML = value.text;
					});

					return;
				}

				if (field instanceof BX.Landing.UI.Field.Icon)
				{
					labelContainer = item.querySelector(".landing-card-title-icon").firstElementChild;
					labelContainer.className = "landing-card-title-icon";
					onCustomEvent(field, "BX.Landing.UI.Field:change", function(value) {
						labelContainer.className = "landing-card-title-icon " + value.classList.join(" ");
					});

					return;
				}

				if (field instanceof BX.Landing.UI.Field.Image)
				{
					labelContainer = item.querySelector(".landing-card-title-img");
					labelContainer.style.backgroundColor = "#fafafa";
					labelContainer.innerHTML = "";

					onCustomEvent(field, "BX.Landing.UI.Field:change", function(value) {
						labelContainer.innerHTML = "";
						labelContainer.appendChild(create('img', {props: {src: value.src}}));
					});

					return;
				}

				if (field instanceof BX.Landing.UI.Field.Text)
				{
					textItemIndex += 1;
					var labelContainers = item.querySelectorAll(".landing-card-title-text");
					labelContainer = labelContainers[textItemIndex];

					onCustomEvent(field, "BX.Landing.UI.Field:change", function(value) {
						labelContainer.innerHTML = create("div", {html: value}).innerText;
					});

					if (labelContainer === labelContainers[0])
					{
						labelContainer.innerHTML = BX.message("LANDING_CARDS_FORM_ITEM_PLACEHOLDER_TEXT");
						field.setValue(BX.message("LANDING_CARDS_FORM_ITEM_PLACEHOLDER_TEXT"));
					}
					else
					{
						labelContainer.innerHTML = "";
					}
				}
			}
		});
	}

	/**
	 * Gets code from entity selector
	 * @param selector
	 * @return {string}
	 */
	function getCodeFromSelector(selector)
	{
		return isString(selector) ? selector.split("@")[0] : "";
	}

	/**
	 * Gets selector index
	 * @param selector
	 * @return {*}
	 */
	function getSelectorIndex(selector)
	{
		return isString(selector) ? selector.split("@")[1] : "";
	}

	/**
	 * Makes selector from code with index
	 * @param {string} code
	 * @param {int} index
	 * @return {string}
	 */
	function makeSelector(code, index)
	{
		return join(code.split("@")[0], "@", index);
	}


	BX.Landing.UI.Form.CardsForm.prototype = {
		constructor: BX.Landing.UI.Form.CardsForm,
		__proto__: BX.Landing.UI.Form.BaseForm.prototype,


		/**
		 * Gets wheel event name
		 * @return {string}
		 */
		getWheelEventName: function()
		{
			return !!window.onwheel ? "wheel" : "mousewheel";
		},


		/**
		 * Create "add button"
		 * @return {BX.Landing.UI.Button.BaseButton}
		 */
		createAddButton: function()
		{
			return new BaseButton("add-card-" + random(), {
				className: "landing-ui-card-add-button",
				text: BX.message("LANDING_CARDS_FORM_ADD_BUTTON"),
				onClick: this.onAddCardClick
			});
		},


		/**
		 * Handles item click event
		 * @param event
		 */
		onItemClick: function(event)
		{
			event.preventDefault();

			var target = findParent(event.currentTarget, {className: "landing-ui-form-cards-item"});

			if (!!target && !hasClass(target, "landing-ui-form-card-item-draggable"))
			{
				if (!hasClass(target, "landing-ui-form-cards-item-expand"))
				{
					addClass(target, "landing-ui-form-cards-item-expand");

					onTransitionEnd(target).then(function() {
						void style(target, {
							overflow: "visible"
						});
					});

					void style(target, {
						"height": "auto"
					});
				}
				else
				{
					removeClass(target, "landing-ui-form-cards-item-expand");
					void style(target, null);
				}
			}
		},

		onRemoveItemClick: function(event, preventSync)
		{
			event.stopPropagation();
			if (this.body.children.length > 1)
			{
				var item = findParent(event.currentTarget, {className: "landing-ui-form-cards-item"});
				remove(item);

				var formNode = item.querySelector(".landing-ui-form-card");
				var form = this.childForms.find(function(currentForm) {
					return currentForm.layout === formNode;
				});

				this.childForms.remove(form);

				if (preventSync !== true)
				{
					if (this.sync)
					{
						var syncSelectors = this.sync;

						if (isString(this.sync))
						{
							syncSelectors = [this.sync];
						}

						if (isArray(syncSelectors))
						{
							syncSelectors.forEach(function(syncSelector) {
								var syncedForm = this.forms.find(function(currentForm) {
									return currentForm.code === syncSelector;
								});

								if (syncedForm)
								{
									var childForm = syncedForm.childForms.find(function(currentChildForm) {
										return currentChildForm.selector.split("@")[1] === form.selector.split("@")[1];
									});

									syncedForm.onRemoveItemClick({
										currentTarget: childForm.layout,
										stopPropagation: (function() {})
									}, true);
								}
							}, this);
						}
					}
				}
			}

			this.adjustLastFormState();
		},

		onRemoveItemMouseenter: function(event)
		{
			event.stopPropagation();
			event.preventDefault();

			var header = findParent(event.currentTarget, {className: "landing-ui-form-card-item-header"});
			addClass(header, "landing-ui-form-card-item-header-onremove");
		},

		onRemoveItemMouseleave: function(event)
		{
			var header = findParent(event.currentTarget, {className: "landing-ui-form-card-item-header"});
			removeClass(header, "landing-ui-form-card-item-header-onremove");
		},

		addChildForm: function(form)
		{
			this.childForms.add(form);

			var formWrapper = wrapForm(form, this);
			append(formWrapper, this.body);
			makeDraggable(formWrapper, this);
			this.adjustLastFormState();
		},

		addPresetForm: function(form)
		{
			this.presetForm.add(form);
			var formWrapper = wrapForm(form, this);
			formWrapper.hidden = true;
			append(formWrapper, this.body);
			makeDraggable(formWrapper, this);
			this.adjustLastFormState();
		},


		onAddCardClick: function(preventSync)
		{
			if (isPlainObject(this.presets) && !isEmpty(this.presets))
			{
				this.showPresetsPopup();
			}
			else
			{
				this.addEmptyCard();

				if (preventSync !== true)
				{
					if (this.sync)
					{
						var syncSelectors = this.sync;

						if (isString(this.sync))
						{
							syncSelectors = [this.sync];
						}

						if (isArray(syncSelectors))
						{
							syncSelectors.forEach(function(syncSelector) {
								var form = this.forms.find(function(currentForm) {
									return currentForm.code === syncSelector;
								});

								if (form)
								{
									form.onAddCardClick(true);
								}
							}, this);
						}
					}
				}
			}
		},

		onPresetItemClick: function(presetId)
		{
			var preset = this.presets[presetId];

			var newForm = this.presetForm.find(function(form) {
				return form.preset.id === presetId;
			}).clone();

			newForm.selector = join(newForm.selector.split("@")[0], "@", this.childForms.length);
			newForm.oldIndex = this.childForms.length;
			newForm.preset = clone(preset);
			newForm.preset.id = presetId;
			this.addChildForm(newForm);
			initBindings(newForm);
			this.adjustLastFormState();
			this.popup.close();

			if (isPlainObject(preset.values))
			{
				newForm.fields.forEach(function(field) {
					var code = field.selector.split("@")[0];

					if (code in preset.values)
					{
						field.setValue(preset.values[code]);

						if (field instanceof BX.Landing.UI.Field.Text)
						{
							BX.fireEvent(field.input, "input");
						}
					}

					if (isArray(preset.disallow))
					{
						var isDisallow = !!preset.disallow.find(function(fieldCode) {
							return code === fieldCode;
						});

						if (isDisallow)
						{
							field.layout.hidden = true;
						}
					}
				});
			}
		},

		showPresetsPopup: function()
		{
			if (!this.popup)
			{
				this.popup = new BX.Landing.UI.Tool.Menu({
					id: "catalog_blocks_list",
					bindElement: this.addButton.layout,
					items: Object.keys(this.presets).map(function(preset) {
						return {
							text: this.presets[preset].name,
							className: "landing-ui-form-cards-preset-popup-item menu-popup-no-icon",
							onclick: this.onPresetItemClick.bind(this, preset)
						}
					}, this),
					autoHide: true,
					maxHeight: 176,
					minHeight: 87
				});

				bind(this.popup.popupWindow.popupContainer, "mouseover", this.onMouseOver.bind(this));
				bind(this.popup.popupWindow.popupContainer, "mouseleave", this.onMouseLeave.bind(this));
				bind(top.document, "click", this.onDocumentClick.bind(this));
				append(
					this.popup.popupWindow.popupContainer,
					findParent(this.addButton.layout, {className: "landing-ui-panel-content-body-content"})
				);
			}

			if (this.popup.popupWindow.isShown())
			{
				this.popup.popupWindow.close();
			}
			else
			{
				this.popup.popupWindow.show();
			}

			this.adjustPopupPosition();
		},


		/**
		 * Handles mouse over event
		 */
		onMouseOver: function()
		{
			bind(this.popup.popupWindow.popupContainer, this.wheelEventName, this.onMouseWheel.bind(this));
			bind(this.popup.popupWindow.popupContainer, "touchmove", this.onMouseWheel.bind(this));
		},


		/**
		 * Handles mouse leave event
		 */
		onMouseLeave: function()
		{
			unbind(this.popup.popupWindow.popupContainer, this.wheelEventName, this.onMouseWheel.bind(this));
			unbind(this.popup.popupWindow.popupContainer, "touchmove", this.onMouseWheel.bind(this));
		},


		/**
		 * Handle mouse wheel event
		 * @param event
		 */
		onMouseWheel: function(event)
		{
			event.stopPropagation();
			event.preventDefault();

			var delta = BX.Landing.UI.Panel.Content.getDeltaFromEvent(event);
			var scrollTop = this.popup.popupWindow.contentContainer.scrollTop;

			requestAnimationFrame(function() {
				this.popup.popupWindow.contentContainer.scrollTop = scrollTop - delta.y;
			}.bind(this));
		},


		/**
		 * Handles document click event
		 */
		onDocumentClick: function()
		{
			if (this.popup.popupWindow)
			{
				this.popup.popupWindow.close();
			}
		},


		/**
		 * Adjusts popup position
		 */
		adjustPopupPosition: function()
		{
			if (this.popup.popupWindow)
			{
				requestAnimationFrame(function() {
					var offsetParent = findParent(this.addButton.layout, {className: "landing-ui-panel-content-body-content"});

					var buttonTop = offsetTop(this.addButton.layout, offsetParent);
					var buttonLeft = offsetLeft(this.addButton.layout, offsetParent);
					var buttonRect = this.addButton.layout.getBoundingClientRect();
					var popupRect = this.popup.popupWindow.popupContainer.getBoundingClientRect();

					var yOffset = 14;

					this.popup.popupWindow.popupContainer.style.top = buttonTop + buttonRect.height + yOffset + "px";
					this.popup.popupWindow.popupContainer.style.left = buttonLeft - (popupRect.width / 2) + (buttonRect.width / 2) + "px";
					this.popup.popupWindow.setAngle({
						offset: 83,
						position: "top"
					})
				}.bind(this));
			}
		},


		/**
		 * Adds empty card form
		 */
		addEmptyCard: function()
		{
			var newData = clone(this.childForms[0].data);
			var newSelector = join(newData.selector.split("@")[0], "@", this.childForms.length);
			newData.selector = newSelector;
			var newForm = this.childForms[0].clone(newData);
			newForm.oldIndex = this.childForms.length;
			newForm.selector = newSelector;
			this.addChildForm(newForm);
			initBindings(newForm);
			this.adjustLastFormState();
		},


		/**
		 * Adjusts last form state
		 */
		adjustLastFormState: function()
		{
			if (this.body.children.length === 1)
			{
				addClass(this.body.firstElementChild, "landing-ui-disallow-remove");
				return;
			}

			slice(this.body.children).forEach(function(item) {
				removeClass(item, "landing-ui-disallow-remove");
			});
		},


		/**
		 * Serialize forms
		 * @return {object}
		 */
		serialize: function()
		{
			return this.childForms.map(function(form) {
				return form.serialize();
			});
		},


		/**
		 * Gets indexes map
		 * @return {Object}
		 */
		getIndexesMap: function()
		{
			return this.childForms
				.reduce(function(res, form, index) {
					return res[index] = form.oldIndex, res;
				}, {});
		},


		/**
		 * Gets used presets
		 * @return {object}
		 */
		getUsedPresets: function()
		{
			return this.childForms
				.reduce(function(res, form) {
					if (isPlainObject(form.preset))
					{
						res[getSelectorIndex(form.selector)] = form.preset.id;
					}

					return res;
				}, {});
		},

		isChanged: function()
		{
			return JSON.stringify(this.value) !== JSON.stringify(this.serialize());
		}
	};
})();