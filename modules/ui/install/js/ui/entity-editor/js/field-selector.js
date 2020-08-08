/**
 * @author Grigoriy Zavodov <zavodov@gmail.com>
 * @version 1.0
 * @copyright Bitrix Inc. 2019
 */

BX.namespace("BX.UI");

//region FIELD SELECTOR
if(typeof(BX.UI.EntityEditorFieldSelector) === "undefined")
{
	BX.UI.EntityEditorFieldSelector = function()
	{
		this._id = "";
		this._settings = {};
		this._scheme = null;
		this._excludedNames = null;
		this._emitter = null;
		this._contentWrapper = null;
		this._popup = null;
	};

	BX.UI.EntityEditorFieldSelector.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = id;
			this._settings = settings ? settings : {};
			this._scheme = BX.prop.get(this._settings, "scheme", null);
			if(!this._scheme)
			{
				throw "BX.UI.EntityEditorFieldSelector. Parameter 'scheme' is not found.";
			}
			this._excludedNames = BX.prop.getArray(this._settings, "excludedNames", []);
			this._emitter = new BX.Event.EventEmitter();
		},
		getMessage: function(name)
		{
			return BX.prop.getString(BX.UI.EntityEditorFieldSelector.messages, name, name);
		},
		isSchemeElementEnabled: function(schemeElement)
		{
			var name = schemeElement.getName();
			for(var i = 0, length = this._excludedNames.length; i < length; i++)
			{
				if(this._excludedNames[i] === name)
				{
					return false;
				}
			}
			return true;
		},
		addClosingListener: function(listener)
		{
			this._emitter.subscribe("BX.UI.EntityEditorFieldSelector:close", listener);
		},
		removeClosingListener: function(listener)
		{
			this._emitter.unsubscribe("BX.UI.EntityEditorFieldSelector:close", listener);
		},
		isOpened: function()
		{
			return this._popup && this._popup.isShown();
		},
		open: function()
		{
			if(this.isOpened())
			{
				return;
			}

			this._popup = new BX.PopupWindow(
				this._id,
				null,
				{
					autoHide: false,
					draggable: true,
					bindOptions: { forceBindPosition: false },
					closeByEsc: true,
					closeIcon: {},
					zIndex: 1,
					titleBar: BX.prop.getString(this._settings, "title", ""),
					content: this.prepareContent(),
					lightShadow : true,
					contentNoPaddings: true,
					buttons: [
						new BX.PopupWindowButton(
							{
								text : BX.message("UI_ENTITY_EDITOR_SELECT"),
								className : "ui-btn ui-btn-success",
								events:
									{
										click: BX.delegate(this.onAcceptButtonClick, this)
									}
							}
						),
						new BX.PopupWindowButtonLink(
							{
								text : BX.message("UI_ENTITY_EDITOR_CANCEL"),
								className : "ui-btn ui-btn-link",
								events:
									{
										click: BX.delegate(this.onCancelButtonClick, this)
									}
							}
						)
					],
					events: {
						onPopupClose: this.onPopupClose.bind(this),
					}
				}
			);

			this._popup.show();
		},
		close: function()
		{
			if(!(this._popup && this._popup.isShown()))
			{
				return;
			}

			this._popup.close();
		},
		prepareContent: function()
		{
			this._contentWrapper = BX.create("div", {
				props: { className: "ui-entity-editor-popup-field-selector" }
			});
			var container = BX.create("div", {
				props: { className: "ui-entity-editor-popup-field-selector-list" }
			});

			var columns = this._scheme.getElements();
			for(var i = 0, columnCount = columns.length; i < columnCount; i++)
			{
				var column = columns[i];
				if(!this.isSchemeElementEnabled(column))
				{
					continue;
				}

				var sections = column.getElements();
				for(var k = 0, sectionCount = sections.length; k < sectionCount; k++)
				{
					var section = sections[k];
					if(!this.isSchemeElementEnabled(section))
					{
						continue;
					}

					var effectiveElements = [];
					var elementChildren = section.getElements();
					var childElement;
					for(var j = 0; j < elementChildren.length; j++)
					{
						childElement = elementChildren[j];
						if(childElement.isTransferable() && childElement.getName() !== "")
						{
							effectiveElements.push(childElement);
						}
					}

					if(effectiveElements.length === 0)
					{
						continue;
					}

					var parentName = section.getName();
					var parentTitle = section.getTitle();

					this._contentWrapper.appendChild(
						BX.create(
							"div",
							{
								attrs: { className: "ui-entity-editor-popup-field-selector-list-caption" },
								text: parentTitle
							}
						)
					);

					for(var j = 0; j < effectiveElements.length; j++)
					{
						childElement = effectiveElements[j];

						var childElementName = childElement.getName();
						var childElementTitle = childElement.getTitle();

						var itemId = parentName + "\\" + childElementName;
						var itemWrapper = BX.create(
							"div",
							{
								attrs: { className: "ui-entity-editor-popup-field-selector-list-item" }
							}
						);
						container.appendChild(itemWrapper);

						itemWrapper.appendChild(
							BX.create(
								"input",
								{
									attrs:
										{
											id: itemId,
											type: "checkbox",
											className: "ui-entity-editor-popup-field-selector-list-checkbox"
										}
								}
							)
						);

						itemWrapper.appendChild(
							BX.create(
								"label",
								{
									attrs:
										{
											for: itemId,
											className: "ui-entity-editor-popup-field-selector-list-label"
										},
									text: childElementTitle
								}
							)
						);
					}
				}
			}

			this._contentWrapper.appendChild(container);

			return this._contentWrapper;
		},
		getSelectedItems: function()
		{
			if(!this._contentWrapper)
			{
				return [];
			}

			var results = [];
			var checkBoxes = this._contentWrapper.querySelectorAll("input.ui-entity-editor-popup-field-selector-list-checkbox");
			for(var i = 0, length = checkBoxes.length; i < length; i++)
			{
				var checkBox = checkBoxes[i];
				if(checkBox.checked)
				{
					var parts = checkBox.id.split("\\");
					if(parts.length >= 2)
					{
						results.push({ sectionName: parts[0], fieldName: parts[1] });
					}
				}
			}

			return results;
		},
		onAcceptButtonClick: function()
		{
			this._emitter.emit(
				"BX.UI.EntityEditorFieldSelector:close",
				{ sender: this, isCanceled: false, items: this.getSelectedItems() }
			);
			this.close();
		},
		onCancelButtonClick: function()
		{
			this._emitter.emit(
				"BX.UI.EntityEditorFieldSelector:close",
				{ sender: this, isCanceled: true }
			);
			this.close();
		},
		onPopupClose: function()
		{
			if(this._popup)
			{
				this._contentWrapper = null;
				this._popup.destroy();
			}
		},
		onPopupDestroy: function()
		{
			if(!this._popup)
			{
				return;
			}

			this._contentWrapper = null;
			this._popup = null;
		}
	};

	if(typeof(BX.UI.EntityEditorFieldSelector.messages) === "undefined")
	{
		BX.UI.EntityEditorFieldSelector.messages = {};
	}

	BX.UI.EntityEditorFieldSelector.create = function(id, settings)
	{
		var self = new BX.UI.EntityEditorFieldSelector(id, settings);
		self.initialize(id, settings);
		return self;
	}
}
//endregion