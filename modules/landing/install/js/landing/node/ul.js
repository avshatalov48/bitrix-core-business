(function() {

"use strict";

BX.namespace("BX.Landing");

/**
 * DOMNode of Block (Ul).
 * @param {nodeOptions} options
 */
BX.Landing.Block.Node.Ul = function(options)
{
	BX.Landing.Block.Node.apply(this, arguments);

	this.popup = null;
	this.nodeEditSubmit = null;
	this.nodeEditLi = [];
	this.content = [];

	// get all LI with BIU tags
	var ulLi = BX.findChildren(this.node, {tag: "li"});
	for (var i = 0, c = ulLi.length; i < c; i++)
	{
		var liContent = "";
		for (var j = 0, cc = ulLi[i].childNodes.length; j < cc; j++)
		{
			if (
				ulLi[i].childNodes[j].nodeType === 1 &&
				(
					ulLi[i].childNodes[j].tagName === "B" ||
					ulLi[i].childNodes[j].tagName === "I" ||
					ulLi[i].childNodes[j].tagName === "U"
				)
			)
			{
				liContent += ulLi[i].childNodes[j].outerHTML;
			}
			else if (
					ulLi[i].childNodes[j].nodeType === 3 &&
					BX.util.trim(ulLi[i].childNodes[j].textContent) !== ""
			)
			{
				liContent += " #VAL# ";
			}
		}
		this.content.push({
			content: BX.util.trim(ulLi[i].textContent),
			original: liContent
		});
	}

	BX.bind(this.node, "click", BX.delegate(this.onClick, this));
};


BX.Landing.Block.Node.Ul.prototype = {
	__proto__: BX.Landing.Block.Node.prototype,
	constructor: BX.Landing.Block.Node.Ul,

	/**
	 * Save content for Node.
	 * @returns {void}
	 */
	saveContent: function ()
	{
		var wasChanged = false;
		var isNulled = true;
		// change any li or not
		for (var i = 0, c = this.nodeEditLi.length; i < c; i++)
		{
			if (this.nodeEditLi[i] !== null)
			{
				var value = BX.util.trim(this.nodeEditLi[i].value);
				isNulled = false;
				if (
					typeof this.content[i] === "undefined" ||
					this.content[i].content !== value
				)
				{
					wasChanged = true;
					break;
				}
			}
			else
			{
				wasChanged = true;
				break;
			}
		}
		// save content
		if (!isNulled && wasChanged)
		{
			BX.cleanNode(this.node);
			this.content = [];
			for (var i = 0, c = this.nodeEditLi.length; i < c; i++)
			{
				if (this.nodeEditLi[i] !== null)
				{
					var value = BX.util.trim(this.nodeEditLi[i].value);
					var original = BX.data(this.nodeEditLi[i], "original");
					this.content.push({
						content: value,
						original: original
					});
					this.node.appendChild(BX.create("li", {
						html: original.replace("#VAL#", BX.util.htmlspecialchars(value))
					}));
				}
				else
				{
					this.content.push(false);
				}
			}
			this.markAsChanged();
		}
	},

	/**
	 * Return element for add new li item.
	 * @returns {DOMNode}
	 */
	getAddLiButton: function (i)
	{
		return BX.create("input", {
			attrs: {
				type: "button",
				value: "+"
			},
			dataset: {
				i: i
			},
			events: {
				click: BX.delegate(function ()
				{
					var button = BX.proxy_context;
					var i = parseInt(BX.data(button, "i"));
					var newLi = BX.create("input", {
						dataset: {
							original: this.content[i].original
						},
						attrs: {
							type: "text"
						}
					});
					BX.insertAfter(BX.create("div", {
						children: [
							newLi,
							this.getAddLiButton(i + 1),
							this.getRemoveLiButton(i + 1)
						]
					}), button.parentNode);
					this.nodeEditLi.splice(i + 1, 0, newLi);
					BX.focus(newLi);
				}, this)
			}
		});
	},

	/**
	 * Return element for remove li item.
	 * @returns {DOMNode}
	 */
	getRemoveLiButton: function (i)
	{
		return BX.create("input", {
			attrs: {
				type: "button",
				value: "-"
			},
			dataset: {
				i: i
			},
			events: {
				click: BX.delegate(function ()
				{
					var button = BX.proxy_context;
					this.nodeEditLi[BX.data(button, "i")] = null;
					BX.remove(button.parentNode);
				}, this)
			}
		});
	},

	/**
	 * Rerturn nodes for edit content.
	 * @param {Boolean} showbutton False if not show save button.
	 * @returns {Array of DOMNode}
	 */
	getEditNodes: function (showbutton)
	{
		var li, editLi = [];

		this.nodeEditLi = [];

		// edit li
		for (var i = 0, c = this.content.length; i < c; i++)
		{
			li = BX.create("input", {
				dataset: {
					original: this.content[i].original
				},
				attrs: {
					type: "text",
					value: BX.util.trim(this.content[i].content)
				}
			});
			this.nodeEditLi.push(li);

			editLi.push(BX.create("div", {
				children: [
					li,
					this.getAddLiButton(i),
					this.getRemoveLiButton(i)
				]
			}));
		}

		// save button
		if (showbutton !== false)
		{
			this.nodeEditSubmit = BX.create("input", {
				attrs: {
					type: "button",
					value: "Save"
				},
				events: {
					click: function ()
					{
						this.saveContent();
						this.popup.close();
					}.bind(this)
				}
			});
		}

		if (showbutton !== false)
		{
			editLi.push(this.nodeEditSubmit);
		}

		return editLi;
	},

	/**
	 * Click on field - edit mode.
	 * @param {MouseEvent} e
	 * @returns {void}
	 */
	onClick: function (e)
	{
		this.popup = BX.PopupWindowManager.create(
			"landing_node_img",
			BX.proxy_context,
			{
				closeIcon: false,
				autoHide: true,
				closeByEsc: true,
				contentColor: "white",
				angle: true,
				offsetLeft: 15,
				overlay: {
					backgroundColor: "#cdcdcd",
					opacity: ".1"
				},
				events: {
					onPopupClose: function ()
					{
						this.popup.destroy();
					}.bind(this)
				}
			}
		);

		// popup content
		this.popup.setContent(BX.create("div", {
			children: this.getEditNodes()
		}));

		this.popup.show();

		return BX.PreventDefault(e);
	},

	/*
	 * Get tags for show Node in settings form.
	 * @returns {Array}
	 */
	getSettingsForm: function ()
	{
		return [{
			name: this.getName(),
			node: BX.create("div", {
				children: this.getEditNodes(false)
			})
		}];
	},

	/*
	 * Callback on save settings form.
	 * @returns {void}
	 */
	saveSettingsForm: function ()
	{
		this.saveContent();
	},

	getValue: function ()
	{

	},

	setValue: function ()
	{

	},

	getField: function()
	{
		return new BX.Landing.UI.Field.BaseField({
			selector: this.selector,
			title: this.manifest.name
		});
	}
};

})();