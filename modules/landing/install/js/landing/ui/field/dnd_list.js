;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var hasClass = BX.Landing.Utils.hasClass;
	var isBoolean = BX.Landing.Utils.isBoolean;
	var isArray = BX.Landing.Utils.isArray;
	var create = BX.Landing.Utils.create;
	var append = BX.Landing.Utils.append;
	var insertAfter = BX.Landing.Utils.insertAfter;
	var insertBefore = BX.Landing.Utils.insertBefore;
	var attr = BX.Landing.Utils.attr;
	var slice = BX.Landing.Utils.slice;
	var style = BX.Landing.Utils.style;
	var bind = BX.Landing.Utils.bind;
	var unbind = BX.Landing.Utils.unbind;
	var data = BX.Landing.Utils.data;
	var remove = BX.Landing.Utils.remove;
	var nextSibling = BX.Landing.Utils.nextSibling;
	var prevSibling = BX.Landing.Utils.prevSibling;
	var escapeHtml = BX.Landing.Utils.escapeHtml;
	var findParent = BX.Landing.Utils.findParent;
	var offsetTop = BX.Landing.Utils.offsetTop;
	var offsetLeft = BX.Landing.Utils.offsetLeft;
	var Popup = BX.Landing.UI.Tool.Popup;


	/**
	 * @extends {BX.Landing.UI.Field.BaseField}
	 * @param data
	 * @constructor
	 */
	BX.Landing.UI.Field.DragAndDropList = function(data)
	{
		BX.Landing.UI.Field.BaseField.apply(this, arguments);

		addClass(this.layout, "landing-ui-field-dnd-list");

		this.isMultiple = isBoolean(data.multiple) ? data.multiple : false;
		this.items = isArray(data.items) ? data.items : [];
		this.value = isArray(data.value) ? data.value : [];

		this.onDragStart = this.onDragStart.bind(this);
		this.onDragEnd = this.onDragEnd.bind(this);

		this.onDragOver = this.onDragOver.bind(this);
		this.onItemDragOver = this.onItemDragOver.bind(this);
		this.onItemDragLeave = this.onItemDragLeave.bind(this);
		this.onDragLeave = this.onDragLeave.bind(this);
		this.onDrop = this.onDrop.bind(this);
		this.onDrag = this.onDrag.bind(this);
		this.onRemoveClick = this.onRemoveClick.bind(this);
		this.onElementClick = this.onElementClick.bind(this);
		this.onMouseWheel = this.onMouseWheel.bind(this);
		this.onMouseOver = this.onMouseOver.bind(this);
		this.onMouseLeave = this.onMouseLeave.bind(this);

		if (typeof window.onwheel !== "undefined")
		{
			this.wheelEventName = "wheel";
		}
		else if (typeof window.onmousewheel !== "undefined")
		{
			this.wheelEventName = "mousewheel";
		}

		this.target = create("div", {
			props: {className: "landing-ui-field-dnd-target"},
			children: [
				create("div", {
					props: {className: "landing-ui-field-dnd-target-browser-header"},
					children: [
						create("span"),
						create("span"),
						create("span")
					]
				})
			]
		});


		this.addButton = new BX.Landing.UI.Button.BaseButton("add_catalog_item", {
			onClick: this.onAddItemClick.bind(this),
			className: "landing-ui-field-dnd-add-button"
		});

		this.valueArea = create("div", {
			props: {classList: "landing-ui-field-dnd-list-value"}
		});

		this.itemsArea = create("div", {
			props: {classList: "landing-ui-field-dnd-list-items"}
		});

		append(this.valueArea, this.target);
		append(this.target, this.input);
		append(this.addButton.layout, this.target);

		bind(this.valueArea, "dragover", this.onDragOver);
		bind(this.valueArea, "dragleave", this.onDragLeave);
		bind(this.valueArea, "drop", this.onDrop);

		this.items.forEach(function(item) {
			append(this.createItem(item), this.itemsArea);
		}, this);

		this.value.forEach(function(value) {
			var item = this.getItem(value);

			if (item)
			{
				append(this.createItem(item), this.valueArea);
			}
		}, this);

		this.adjustPlaceholder();

		this.value = this.getValue();
	};


	BX.Landing.UI.Field.DragAndDropList.prototype = {
		constructor: BX.Landing.UI.Field.DragAndDropList,
		__proto__: BX.Landing.UI.Field.BaseField.prototype,

		onDragStart: function(event)
		{
			this.dragItem = event.currentTarget;

			this.dragIndex = slice(this.valueArea.children).findIndex(function(item) {
				return item === event.currentTarget;
			});
			this.valueAreaRect = this.valueArea.getBoundingClientRect();
			addClass(this.dragItem, "landing-ui-ondrag");
			addClass(this.valueArea, "landing-ui-ondrag");
		},

		onDragEnd: function()
		{
			removeClass(this.valueArea, "landing-ui-over");
			removeClass(this.dragItem, "landing-ui-ondrag");
			removeClass(this.valueArea, "landing-ui-ondrag");

			slice(this.valueArea.children).forEach(function(item) {
				style(item, null);
			});

			this.cloned = null;
			this.dragItem = null;

			if (this.isChanged())
			{
				this.onValueChangeHandler(this);
				this.value = this.getValue();
			}
		},

		onDragOver: function(event)
		{
			event.preventDefault();
			event.dataTransfer.dropEffect = "copy";
			addClass(event.currentTarget, "landing-ui-over");
		},

		onItemDragOver: function(event)
		{
			var target = event.currentTarget;

			if (this.itemsArea.contains(this.dragItem) || this.valueArea.contains(this.dragItem))
			{
				if (this.valueArea.contains(target) && target !== this.cloned)
				{
					var targetRect = target.getBoundingClientRect();
					var targetMiddle = targetRect.top + (targetRect.height / 2);

					if (!this.cloned && !this.valueArea.contains(this.dragItem))
					{
						this.cloned = this.createItemFromElement(this.dragItem);
						append(this.cloned, this.valueArea);
					}

					if (!this.cloned && this.valueArea.contains(this.dragItem))
					{
						this.cloned = this.dragItem;
					}

					if (event.clientY > targetMiddle && nextSibling(target) !== this.cloned)
					{
						return insertAfter(this.cloned, target);
					}

					if (event.clientY < targetMiddle && prevSibling(target) !== this.cloned)
					{
						return insertBefore(this.cloned, target);
					}
				}
			}

			this.adjustPopupPosition();
		},

		onAddItemClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();

			if (!this.popup)
			{
				this.popup = new Popup({
					id: "catalog_blocks_list",
					bindElement: this.addButton.layout,
					content: this.itemsArea,
					autoHide: true,
					height: 176
				});

				this.popup.contentContainer.style.overflowX = "hidden";

				bind(this.popup.popupContainer, "mouseover", this.onMouseOver);
				bind(this.popup.popupContainer, "mouseleave", this.onMouseLeave);
				bind(top.document, "click", this.onDocumentClick.bind(this));
				append(
					this.popup.popupContainer,
					findParent(this.addButton.layout, {className: "landing-ui-panel-content-body-content"})
				);
			}

			if (this.popup.isShown())
			{
				this.popup.close();
			}
			else
			{
				this.popup.show();
			}

			this.adjustPopupPosition();
		},

		onMouseOver: function()
		{
			bind(this.popup.popupContainer, this.wheelEventName, this.onMouseWheel);
			bind(this.popup.popupContainer, "touchmove", this.onMouseWheel);
		},

		onMouseLeave: function()
		{
			unbind(this.popup.popupContainer, this.wheelEventName, this.onMouseWheel);
			unbind(this.popup.popupContainer, "touchmove", this.onMouseWheel);
		},

		onMouseWheel: function(event)
		{
			event.stopPropagation();
			event.preventDefault();

			var delta = BX.Landing.UI.Panel.Content.getDeltaFromEvent(event);
			var scrollTop = this.popup.contentContainer.scrollTop;

			requestAnimationFrame(function() {
				this.popup.contentContainer.scrollTop = scrollTop - delta.y;
			}.bind(this));
		},


		/**
		 * @todo refactoring
		 */
		adjustPopupPosition: function()
		{
			if (this.popup)
			{
				requestAnimationFrame(function() {
					var offsetParent = findParent(this.addButton.layout, {className: "landing-ui-panel-content-body-content"});

					var buttonTop = offsetTop(this.addButton.layout, offsetParent);
					var buttonLeft = offsetLeft(this.addButton.layout, offsetParent);
					var buttonRect = this.addButton.layout.getBoundingClientRect();
					var popupRect = this.popup.popupContainer.getBoundingClientRect();

					var yOffset = 14;

					this.popup.popupContainer.style.top = buttonTop + buttonRect.height + yOffset + "px";
					this.popup.popupContainer.style.left = buttonLeft - (popupRect.width / 2) + (buttonRect.width / 2) + "px";
					this.popup.setAngle({
						offset: 71,
						position: "top"
					})
				}.bind(this));
			}
		},

		createItemFromElement: function(element)
		{
			var img = element.querySelector("img");
			var options = {
				name: element.innerText,
				value: data(element, "value")
			};

			if (img)
			{
				options.image = img.src;
			}

			return this.createItem(options);
		},

		onDragLeave: function(event)
		{
			removeClass(event.currentTarget, "landing-ui-over");
		},

		onItemDragLeave: function(event)
		{
			event.stopPropagation();
		},

		onDrop: function(event)
		{
			event.stopPropagation();

			if (this.itemsArea.contains(this.dragItem) || this.valueArea.contains(this.dragItem))
			{
				if (!this.valueArea.contains(this.dragItem) && !this.valueArea.contains(this.cloned))
				{
					var item = this.createItemFromElement(this.dragItem);
					append(item, this.valueArea);
					this.cloned = null;

					this.adjustPlaceholder();
					this.adjustPopupPosition();
				}
			}

			if (this.isChanged())
			{
				this.onValueChangeHandler(this);
				this.value = this.getValue();
			}
		},

		onDrag: function(event)
		{
			if (this.cloned && this.valueArea.contains(this.cloned))
			{
				if (event.clientX > this.valueAreaRect.right)
				{
					remove(this.cloned);
					this.cloned = null;
					this.adjustPlaceholder();
					this.adjustPopupPosition();
				}
			}
		},

		onRemoveClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();
			remove(event.currentTarget.parentNode);

			this.adjustPlaceholder();
			this.adjustPopupPosition();

			if (this.isChanged())
			{
				this.onValueChangeHandler(this);
				this.value = this.getValue();
			}
		},

		onElementClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();

			if (this.itemsArea.contains(event.currentTarget))
			{
				var item = this.createItemFromElement(event.currentTarget);
				append(item, this.valueArea);
				this.adjustPlaceholder();
				this.adjustPopupPosition();
			}

			if (this.isChanged())
			{
				this.onValueChangeHandler(this);
				this.value = this.getValue();
			}
		},

		getSelectedItem: function()
		{
			return (
				slice(this.valueArea.children).find(function(item) {
					return hasClass(item, "landing-ui-selected");
				}) ||
				slice(this.itemsArea.children).find(function(item) {
					return hasClass(item, "landing-ui-selected");
				})
			)
		},

		onDocumentClick: function()
		{
			if (this.popup)
			{
				this.popup.close();
			}
		},

		adjustPlaceholder: function()
		{
			if (slice(this.valueArea.children).length === 0)
			{
				this.placeholder = this.createValuePlaceholder();
				append(this.placeholder, this.valueArea);
			}
			else if (this.placeholder)
			{
				remove(this.placeholder);
				this.placeholder = null;
			}
		},


		/**
		 * Gets item by value
		 * @param {*} value
		 * @return {?object}
		 */
		getItem: function(value)
		{
			return this.items.find(function(item) {
				// noinspection EqualityComparisonWithCoercionJS
				return item.value == value;
			});
		},

		createValuePlaceholder: function()
		{
			return create("div", {
				props: {className: "landing-ui-field-dnd-value-placeholder"},
				children: [
					create("span", {html: BX.message("LANDING_FIELD_CATALOG_CONSTRUCTOR_PLACEHOLDER_TEXT")})
				]
			})
		},

		/**
		 * Creates list element
		 * @param {{name: String, value: *, [image]: string}} item
		 * @return {HTMLElement}
		 */
		createItem: function(item)
		{
			var element = create("div", {
				props: {className: ("landing-ui-field-dnd-list-item" + (item.image ? " landing-ui-with-image" : ""))},
				attrs: {title: item.name},
				html: item.image ? create("img", {props: {src: item.image}}).outerHTML : escapeHtml(item.name)
			});
			var remove = create("span", {props: {className: "landing-ui-field-dnd-list-item-remove"}});

			append(remove, element);

			data(element, "data-value", item.value);
			attr(element, "draggable", "true");

			bind(element, "dragstart", this.onDragStart);
			bind(element, "dragend", this.onDragEnd);
			bind(element, "dragover", this.onItemDragOver);
			bind(element, "dragleave", this.onItemDragLeave);
			bind(element, "drag", this.onDrag);
			bind(remove, "click", this.onRemoveClick);
			bind(element, "click", this.onElementClick);

			return element;
		},

		isChanged: function()
		{
			return JSON.stringify(this.value) !== JSON.stringify(this.getValue());
		},

		/**
		 * Gets value
		 * @return {Array}
		 */
		getValue: function()
		{
			var items = slice(this.valueArea.children);
			items = items.filter(function(item) {
				return hasClass(item, "landing-ui-field-dnd-list-item");
			});

			return items.map(function(item) {
				return data(item, "data-value");
			});
		}
	};
})();