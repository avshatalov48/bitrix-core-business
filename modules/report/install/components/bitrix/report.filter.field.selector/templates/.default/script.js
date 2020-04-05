BX.namespace("BX.Report");

if (typeof(BX.Report.setSelectValue) === "undefined")
{
	BX.Report.setSelectValue = function (select, value)
	{
		var i, j;
		var bFirstSelected = false;
		var bMultiple = !!(select.getAttribute('multiple'));
		if (!(value instanceof Array)) value = [value];
		for (i=0; i<select.options.length; i++)
		{
			for (j in value)
			{
				if (select.options[i].value == value[j])
				{
					if (!bFirstSelected) {bFirstSelected = true; select.selectedIndex = i;}
					select.options[i].selected = true;
					break;
				}
			}
			if (!bMultiple && bFirstSelected) break;
		}
	};
}
if (typeof(BX.Report.rebuildSelect) === "undefined")
{
	BX.Report.rebuildSelect = function (select, items, value)
	{
		var opt, el, i, j;
		var setSelected = false;
		var bMultiple;

		if (!(value instanceof Array))
			value = [value];
		if (select)
		{
			bMultiple = !!(select.getAttribute('multiple'));
			while (opt = select.lastChild)
				select.removeChild(opt);
			for (i = 0; i < items.length; i++)
			{
				el = document.createElement("option");
				el.value = items[i]['id'];
				el.innerHTML = items[i]['title'];
				try
				{
					// for IE earlier than version 8
					select.add(el, select.options[null]);
				}
				catch (e)
				{
					el = document.createElement("option");
					el.text = items[i]['title'];
					select.add(el, null);
				}
				if (!setSelected || bMultiple)
				{
					for (j = 0; j < value.length; j++)
					{
						if (items[i]['id'] == value[j])
						{
							el.selected = true;
							if (!setSelected)
							{
								setSelected = true;
								select.selectedIndex = i;
							}
							break;
						}
					}
				}
			}
		}
	};
}

if (typeof(BX.Report.FilterFieldSelectorManagerClass) === "undefined")
{
	BX.Report.FilterFieldSelectorManagerClass = (function ()
	{
		var FilterFieldSelectorClass = function (settings)
		{
			this._selectors = {};
		};

		FilterFieldSelectorClass.prototype = {
			addSelector: function(settings)
			{
				var selector = null,
					entityType = "",
					entityId = "",
					fieldName = "";

				if (settings["USER_TYPE_ID"])
					entityType = settings["USER_TYPE_ID"];
				if (settings["ENTITY_ID"])
					entityId = settings["ENTITY_ID"];
				if (settings["FIELD_NAME"])
					fieldName = settings["FIELD_NAME"];

				if (entityType && entityId && fieldName
					&& (!this._selectors[entityId] || !this._selectors[entityId][fieldName]))
				{
					switch (entityType)
					{
						case "crm":
							selector = new BX.Report.CrmFilterFieldSelectorClass(settings);
							if (selector)
							{
								if (!this._selectors[entityId])
									this._selectors[entityId] = {};
								this._selectors[entityId][fieldName] = selector;
							}
							break;
						case "crm_status":
							selector = new BX.Report.CrmStatusFilterFieldSelectorClass(settings);
							if (selector)
							{
								if (!this._selectors[entityId])
									this._selectors[entityId] = {};
								this._selectors[entityId][fieldName] = selector;
							}
							break;
						case "iblock_element":
							selector = new BX.Report.IblockElementFilterFieldSelectorClass(settings);
							if (selector)
							{
								if (!this._selectors[entityId])
									this._selectors[entityId] = {};
								this._selectors[entityId][fieldName] = selector;
							}
							break;
						case "iblock_section":
							selector = new BX.Report.IblockSectionFilterFieldSelectorClass(settings);
							if (selector)
							{
								if (!this._selectors[entityId])
									this._selectors[entityId] = {};
								this._selectors[entityId][fieldName] = selector;
							}
							break;
					}
				}

				return selector;
			},
			getSelector: function(entityId, fieldName)
			{
				var selector = null;

				if (this._selectors[entityId] && this._selectors[entityId][fieldName])
					selector = this._selectors[entityId][fieldName];

				return selector;
			}
		};

		return FilterFieldSelectorClass;
	})();
}

if (typeof(BX.Report.CrmFilterFieldSelectorClass) === "undefined")
{
	BX.Report.CrmFilterFieldSelectorClass = (function ()
	{
		var FilterFieldSelectorClass = function (settings)
		{
			this._settings = settings;
			this.entityType = settings["USER_TYPE_ID"] || "";
			this.entityId = settings["ENTITY_ID"] || "";
			this.fieldName = settings["FIELD_NAME"] || "";

			this.crmId = [];
			this.crmName = [];
		};

		FilterFieldSelectorClass.prototype = {
			getSetting: function (name, dafaultval)
			{
				return typeof(this._settings[name]) != 'undefined' ? this._settings[name] : dafaultval;
			},
			getMessage: function (messageName)
			{
				var msg = "";

				if (BX.type.isString(messageName) && messageName.length > 0
					&& this._settings["messages"] && this._settings["messages"][messageName])
				{
					msg = this._settings["messages"][messageName];
				}

				return msg;
			},
			makeFilterField: function (container, nextSibling, name)
			{
				var aNode, fieldNode, crmId, controlId, selectorIndex;

				if (!this.entityType || !this.entityId || !this.fieldName
					|| !container || !BX.type.isDomNode(container))
				{
					return null;
				}

				selectorIndex = this.crmId.length;
				controlId = this.entityId + "_" + this.fieldName + "[" + selectorIndex + "]";
				aNode = BX.create(
					'A',
					{
						"attrs": {
							"id": "crm-" + controlId + "-open",
							"class": "report-select-popup-link",
							"style": "cursor: pointer;",
							"href": ""
						},
						"text": this.getMessage("choice")
					}
				);
				fieldNode = BX.create(
					'SPAN',
					{
						"attrs": {
							"id": "crm-" + controlId + "-box",
							"name": "report-filter-value-control-crm",
							"ufSelectorIndex": selectorIndex.toString()
						},
						"children": [
							BX.create(
								'DIV',
								{
									"attrs": {"class": "crm-button-open"},
									"children": [aNode]
								}
							)
						]
					}
				);

				if (BX.type.isDomNode(nextSibling) && nextSibling.parentNode === container)
					container.insertBefore(fieldNode, nextSibling);
				else
					container.appendChild(fieldNode);

				if (!BX.type.isString(name) || name.length <= 0)
					name = controlId;

				crmId = CRM.Set(
					aNode,
					name,
					"",
					this.getSetting("ELEMENT", []),
					this.getSetting("PREFIX", "Y") === "Y",
					this.getSetting("MULTIPLE", "N") === "Y",
					this.getSetting("ENTITY_TYPE", {}),
					this.getSetting("MESSAGES", {})
				);
				this.crmId.push(crmId);
				this.crmName.push(controlId);
				BX.bind(aNode, "click", BX.delegate(obCrm[crmId].Open, obCrm[crmId]));

				return fieldNode;
			},
			getFilterValue: function (selectorIndex)
			{
				var value, crmId, crm, crmName, inputBox, valElements;

				value = [];
				crmId = this.crmId[selectorIndex];
				crm = obCrm[crmId];
				crmName = this.crmName[selectorIndex];
				inputBox = BX("crm-" + crmId + "_" + crmName + "-input-box");
				if (inputBox)
				{
					valElements = BX.findChildren(inputBox, {"tag": "input", "attr": {"type": "text"}});
					if (valElements instanceof Array && valElements.length > 0)
					{
						for (var i in valElements)
						{
							if (valElements.hasOwnProperty(i))
								value.push(valElements[i].value);
						}
					}
				}

				switch (value.length)
				{
					case 0:
						value = "";
						break;
					case 1:
						value = value[0];
						break;
				}

				return value;
			},
			setFilterValue: function (selectorIndex, value)
			{
				var crmId, crm, i;

				crmId = this.crmId[selectorIndex];
				crm = obCrm[crmId];
				if (crm)
				{
					if (BX.type.isArray(value))
					{
						for (i = 0; i < value.length; i++)
							crm.PopupSetItem(value[i]);
					}
					else
					{
						crm.PopupSetItem(value);
					}
				}
			}
		};

		return FilterFieldSelectorClass;
	})();
}

if (typeof(BX.Report.CrmStatusFilterFieldSelectorClass) === "undefined")
{
	BX.Report.CrmStatusFilterFieldSelectorClass = (function ()
	{
		var FilterFieldSelectorClass = function (settings)
		{
			this._settings = settings;
			this.entityType = settings["USER_TYPE_ID"] || "";
			this.entityId = settings["ENTITY_ID"] || "";
			this.fieldName = settings["FIELD_NAME"] || "";

			this.selectId = [];
		};

		FilterFieldSelectorClass.prototype = {
			getSetting: function (name, dafaultval)
			{
				return typeof(this._settings[name]) != 'undefined' ? this._settings[name] : dafaultval;
			},
			getMessage: function (messageName)
			{
				var msg = "";

				if (BX.type.isString(messageName) && messageName.length > 0
					&& this._settings["messages"] && this._settings["messages"][messageName])
				{
					msg = this._settings["messages"][messageName];
				}

				return msg;
			},
			makeFilterField: function (container, nextSibling, name)
			{
				var selectNode, fieldNode, controlId, selectorIndex;

				if (!this.entityType || !this.entityId || !this.fieldName
					|| !container || !BX.type.isDomNode(container))
				{
					return null;
				}

				selectorIndex = this.selectId.length;
				controlId = this.entityId + "_" + this.fieldName + "[" + selectorIndex + "]";

				if (!BX.type.isString(name) || name.length <= 0)
					name = controlId;

				fieldNode = BX.create(
					'SPAN',
					{
						"attrs": {
							"name": "report-filter-value-control-" + controlId,
							"class": "report-filter-vcc",
							"ufSelectorIndex": selectorIndex.toString()
						},
						"children": [
							selectNode = BX.create(
								'SELECT',
								{
									"attrs": {
										"id": controlId,
										"class": "reports-filter-select-small",
										"name": name + "[]",
										"multiple": "multiple",
										"size": this.getSetting("LIST_HEIGHT", 3),
										"style": "width: 225px;"
									}
								}
							)
						]
					}
				);

				if (selectNode)
					BX.Report.rebuildSelect(selectNode, this.getSetting("ITEMS"), "");

				if (BX.type.isDomNode(nextSibling) && nextSibling.parentNode === container)
					container.insertBefore(fieldNode, nextSibling);
				else
					container.appendChild(fieldNode);

				this.selectId.push(controlId);

				return fieldNode;
			},
			getFilterValue: function (selectorIndex)
			{
				var opts, optIndex, vals, valIndex,
					selectNode = BX(this.selectId[selectorIndex]),
					value = "";

				if (selectNode)
				{
					if (selectNode.tagName === "SELECT" && selectNode.getAttribute("multiple") === "multiple")
					{
						opts = selectNode.options;
						vals = [];
						valIndex = 0;
						for (optIndex = 0; optIndex < opts.length; optIndex++)
						{
							if (opts[optIndex].selected)
								vals[valIndex++] = opts[optIndex].value;
						}
						value =  (vals.length > 0) ? vals : "";
					}
					else
					{
						value = selectNode.value;
					}
				}

				return value;
			},
			setFilterValue: function (selectorIndex, value)
			{
				var selectNode = BX(this.selectId[selectorIndex]);
				if (selectNode)
					BX.Report.setSelectValue(selectNode, value);
			}
		};

		return FilterFieldSelectorClass;
	})();
}

if (typeof(BX.Report.IblockElementFilterFieldSelectorClass) === "undefined")
{
	BX.Report.IblockElementFilterFieldSelectorClass = (function ()
	{
		var FilterFieldSelectorClass = function (settings)
		{
			this._settings = settings;
			this.entityType = settings["USER_TYPE_ID"] || "";
			this.entityId = settings["ENTITY_ID"] || "";
			this.fieldName = settings["FIELD_NAME"] || "";

			this.selectId = [];
		};

		FilterFieldSelectorClass.prototype = {
			getSetting: function (name, dafaultval)
			{
				return typeof(this._settings[name]) != 'undefined' ? this._settings[name] : dafaultval;
			},
			getMessage: function (messageName)
			{
				var msg = "";

				if (BX.type.isString(messageName) && messageName.length > 0
					&& this._settings["messages"] && this._settings["messages"][messageName])
				{
					msg = this._settings["messages"][messageName];
				}

				return msg;
			},
			makeFilterField: function (container, nextSibling, name)
			{
				var selectNode, fieldNode, controlId, selectorIndex;

				if (!this.entityType || !this.entityId || !this.fieldName
					|| !container || !BX.type.isDomNode(container))
				{
					return null;
				}

				selectorIndex = this.selectId.length;
				controlId = this.entityId + "_" + this.fieldName + "[" + selectorIndex + "]";

				if (!BX.type.isString(name) || name.length <= 0)
					name = controlId;

				fieldNode = BX.create(
					'SPAN',
					{
						"attrs": {
							"name": "report-filter-value-control-" + controlId,
							"class": "report-filter-vcc",
							"ufSelectorIndex": selectorIndex.toString()
						},
						"children": [
							selectNode = BX.create(
								'SELECT',
								{
									"attrs": {
										"id": controlId,
										"class": "reports-filter-select-small",
										"name": name + "[]",
										"multiple": "multiple",
										"size": this.getSetting("LIST_HEIGHT", 3),
										"style": "width: 225px;"
									}
								}
							)
						]
					}
				);

				if (selectNode)
					BX.Report.rebuildSelect(selectNode, this.getSetting("ITEMS"), "");

				if (BX.type.isDomNode(nextSibling) && nextSibling.parentNode === container)
					container.insertBefore(fieldNode, nextSibling);
				else
					container.appendChild(fieldNode);

				this.selectId.push(controlId);

				return fieldNode;
			},
			getFilterValue: function (selectorIndex)
			{
				var opts, optIndex, vals, valIndex,
					selectNode = BX(this.selectId[selectorIndex]),
					value = "";
				
				if (selectNode)
				{
					if (selectNode.tagName === "SELECT" && selectNode.getAttribute("multiple") === "multiple")
					{
						opts = selectNode.options;
						vals = [];
						valIndex = 0;
						for (optIndex = 0; optIndex < opts.length; optIndex++)
						{
							if (opts[optIndex].selected)
								vals[valIndex++] = opts[optIndex].value;
						}
						value =  (vals.length > 0) ? vals : "";
					}
					else
					{
						value = selectNode.value;
					}
				}

				return value;
			},
			setFilterValue: function (selectorIndex, value)
			{
				var selectNode = BX(this.selectId[selectorIndex]);
				if (selectNode)
					BX.Report.setSelectValue(selectNode, value);
			}
		};

		return FilterFieldSelectorClass;
	})();
}

if (typeof(BX.Report.IblockSectionFilterFieldSelectorClass) === "undefined")
{
	BX.Report.IblockSectionFilterFieldSelectorClass = (function ()
	{
		var FilterFieldSelectorClass = function (settings)
		{
			this._settings = settings;
			this.entityType = settings["USER_TYPE_ID"] || "";
			this.entityId = settings["ENTITY_ID"] || "";
			this.fieldName = settings["FIELD_NAME"] || "";

			this.selectId = [];
		};

		FilterFieldSelectorClass.prototype = {
			getSetting: function (name, dafaultval)
			{
				return typeof(this._settings[name]) != 'undefined' ? this._settings[name] : dafaultval;
			},
			getMessage: function (messageName)
			{
				var msg = "";

				if (BX.type.isString(messageName) && messageName.length > 0
					&& this._settings["messages"] && this._settings["messages"][messageName])
				{
					msg = this._settings["messages"][messageName];
				}

				return msg;
			},
			makeFilterField: function (container, nextSibling, name)
			{
				var selectNode, fieldNode, controlId, selectorIndex;

				if (!this.entityType || !this.entityId || !this.fieldName
					|| !container || !BX.type.isDomNode(container))
				{
					return null;
				}

				selectorIndex = this.selectId.length;
				controlId = this.entityId + "_" + this.fieldName + "[" + selectorIndex + "]";

				if (!BX.type.isString(name) || name.length <= 0)
					name = controlId;

				fieldNode = BX.create(
					'SPAN',
					{
						"attrs": {
							"name": "report-filter-value-control-" + controlId,
							"class": "report-filter-vcc",
							"ufSelectorIndex": selectorIndex.toString()
						},
						"children": [
							selectNode = BX.create(
								'SELECT',
								{
									"attrs": {
										"id": controlId,
										"class": "reports-filter-select-small",
										"name": name + "[]",
										"multiple": "multiple",
										"size": this.getSetting("LIST_HEIGHT", 3),
										"style": "width: 225px;"
									}
								}
							)
						]
					}
				);

				if (selectNode)
					BX.Report.rebuildSelect(selectNode, this.getSetting("ITEMS"), "");

				if (BX.type.isDomNode(nextSibling) && nextSibling.parentNode === container)
					container.insertBefore(fieldNode, nextSibling);
				else
					container.appendChild(fieldNode);

				this.selectId.push(controlId);

				return fieldNode;
			},
			getFilterValue: function (selectorIndex)
			{
				var opts, optIndex, vals, valIndex,
					selectNode = BX(this.selectId[selectorIndex]),
					value = "";

				if (selectNode)
				{
					if (selectNode.tagName === "SELECT" && selectNode.getAttribute("multiple") === "multiple")
					{
						opts = selectNode.options;
						vals = [];
						valIndex = 0;
						for (optIndex = 0; optIndex < opts.length; optIndex++)
						{
							if (opts[optIndex].selected)
								vals[valIndex++] = opts[optIndex].value;
						}
						value =  (vals.length > 0) ? vals : "";
					}
					else
					{
						value = selectNode.value;
					}
				}

				return value;
			},
			setFilterValue: function (selectorIndex, value)
			{
				var selectNode = BX(this.selectId[selectorIndex]);
				if (selectNode)
					BX.Report.setSelectValue(selectNode, value);
			}
		};

		return FilterFieldSelectorClass;
	})();
}
