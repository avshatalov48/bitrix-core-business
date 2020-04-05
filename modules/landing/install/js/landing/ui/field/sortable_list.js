;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	var remove = BX.Landing.Utils.remove;
	var unbind = BX.Landing.Utils.unbind;
	var addClass = BX.Landing.Utils.addClass;
	var removeClass = BX.Landing.Utils.removeClass;
	var encodeDataValue = BX.Landing.Utils.encodeDataValue;
	var prepend = BX.Landing.Utils.prepend;
	var append = BX.Landing.Utils.append;
	var create = BX.Landing.Utils.create;
	var data = BX.Landing.Utils.data;
	var slice = BX.Landing.Utils.slice;
	var bind = BX.Landing.Utils.bind;

	/**
	 * @extends {BX.Landing.UI.Field.DragAndDropList}
	 * @param data
	 * @constructor
	 */
	BX.Landing.UI.Field.SortableList = function(data)
	{
		BX.Landing.UI.Field.DragAndDropList.apply(this, arguments);
		addClass(this.layout, "landing-ui-field-sortable-list");

		this.makePreview();

		prepend(this.preview, this.target);
	};


	BX.Landing.UI.Field.SortableList.prototype = {
		constructor: BX.Landing.UI.Field.SortableList,
		__proto__: BX.Landing.UI.Field.DragAndDropList.prototype,

		onDragStart: function(event)
		{
			BX.Landing.UI.Field.DragAndDropList.prototype.onDragStart.call(this, event);
			addClass(this.preview, "landing-ui-ondrag");
			addClass(this.dragItem, "landing-ui-ondrag");
		},

		onDragEnd: function(event)
		{
			BX.Landing.UI.Field.DragAndDropList.prototype.onDragEnd.call(this, event);
			removeClass(this.preview, "landing-ui-ondrag");
			removeClass(this.dragItem, "landing-ui-ondrag");
			this.makePreview();
		},

		onElementClick: function()
		{

		},

		makePreview: function()
		{
			if (!this.preview)
			{
				this.preview = create("div", {
					props: {className: "landing-ui-field-sortable-list-preview"}
				});
			}

			this.preview.innerHTML = "";

			this.getValue().forEach(function(value) {
				append(this.createPreviewItem(this.getItem(value).preview, value), this.preview);
			}, this);
		},

		createItem: function(item)
		{
			var element = BX.Landing.UI.Field.DragAndDropList.prototype.createItem.call(this, item);
			remove(element.querySelector(".landing-ui-field-dnd-list-item-remove"));
			unbind(element, "drag", this.onDrag);

			prepend(
				create("span", {
					props: {className: "landing-ui-field-dnd-list-item-drag"}
				}),
				element
			);

			bind(element, "mouseover", this.highlightByValue.bind(this, item.value));
			bind(element, "mouseout", this.removeHighLightByValue.bind(this, item.value));

			return element;
		},

		createPreviewItem: function(src, value)
		{
			return create("div", {
				props: {className: "landing-ui-field-sortable-list-preview-item"},
				attrs: {"data-value": encodeDataValue(value)},
				children: [
					create("img", {
						props: {src: src},
						events: {
							mouseover: this.highlightByValue.bind(this, value),
							mouseout: this.removeHighLightByValue.bind(this, value)
						}
					})
				]
			});
		},

		highlightByValue: function(value)
		{
			var preview = this.getPreviewByValue(value);
			var element = this.getElementByValue(value);

			if (preview)
			{
				addClass(preview, "landing-ui-active");
			}

			if (element)
			{
				addClass(element, "landing-ui-active");
			}
		},

		removeHighLightByValue: function(value)
		{
			var preview = this.getPreviewByValue(value);
			var element = this.getElementByValue(value);

			if (preview)
			{
				removeClass(preview, "landing-ui-active");
			}

			if (element)
			{
				removeClass(element, "landing-ui-active");
			}
		},

		getElementByValue: function(value)
		{
			return slice(this.valueArea.children).find(function(element) {
				// noinspection EqualityComparisonWithCoercionJS
				return data(element, "data-value") == value;
			});
		},

		getPreviewByValue: function(value)
		{
			return slice(this.preview.children).find(function(element) {
				// noinspection EqualityComparisonWithCoercionJS
				return data(element, "data-value") == value;
			});
		}
	};
})();