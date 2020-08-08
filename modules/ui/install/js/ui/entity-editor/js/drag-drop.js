BX.namespace("BX.UI");

//region D&D
if(typeof BX.UI.EditorDragScope === "undefined")
{
	BX.UI.EditorDragScope =
	{
		intermediate: 0,
		parent: 1,
		form: 2,
		getDefault: function()
		{
			return this.form;
		}
	};
}

if(typeof BX.UI.EditorDragObjectType === "undefined")
{
	BX.UI.EditorDragObjectType =
	{
		intermediate: "",
		field: "F",
		section: "S"
	};
}

//region Base Drag & Drop Controller
if(typeof(BX.UI.BaseDragController) === "undefined")
{
	BX.UI.BaseDragController = function()
	{
		this._id = "";
		this._settings = {};
		this._node = null;
		this._ghostNode = null;
		this._ghostOffset = { x: 0, y: 0 };

		this._previousPos = null;
		this._currentPos = null;

		this._enableDrag = true;
		this._isInDragMode = false;
		this._emitter = null;
		this._preserveDocument = false;
		this._bodyOverflow = "";
	};
	BX.UI.BaseDragController.prototype =
	{
		initialize: function(id, settings)
		{
			if(typeof(jsDD) === "undefined")
			{
				throw "UI.CustomDragItem: Could not find jsDD API.";
			}

			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(8);
			this._settings = settings ? settings : {};

			this._node = this.getSetting("node");
			if(!this._node)
			{
				throw "UI.CustomDragItem: The 'node' parameter is not defined in settings or empty.";
			}

			this._enableDrag = this.getSetting("enableDrag", true);
			this._ghostOffset = this.getSetting("ghostOffset", { x: 0, y: 0 });

			this._emitter = new BX.Event.EventEmitter();

			this.doInitialize();
			this.bindEvents();
		},
		doInitialize: function()
		{
		},
		release: function()
		{
			this.doRelease();
			this.unbindEvents();
		},
		doRelease: function()
		{
		},
		getId: function()
		{
			return this._id;
		},
		getSetting: function (name, defaultval)
		{
			return this._settings.hasOwnProperty(name) ? this._settings[name] : defaultval;
		},
		bindEvents: function()
		{
			this._node.onbxdragstart = BX.delegate(this._onDragStart, this);
			this._node.onbxdrag = BX.delegate(this._onDrag, this);
			this._node.onbxdragstop = BX.delegate(this._onDragStop, this);
			this._node.onbxdragrelease = BX.delegate(this._onDragRelease, this);

			jsDD.registerObject(this._node);

			this.doBindEvents();
		},
		doBindEvents: function()
		{
		},
		unbindEvents: function()
		{
			delete this._node.onbxdragstart;
			delete this._node.onbxdrag;
			delete this._node.onbxdragstop;
			delete this._node.onbxdragrelease;

			if(BX.type.isFunction(jsDD.unregisterObject))
			{
				jsDD.unregisterObject(this._node);
			}

			this.doUnbindEvents();
		},
		doUnbindEvents: function()
		{
		},
		createGhostNode: function()
		{
			throw "UI.CustomDragItem: The 'createGhostNode' function is not implemented.";
		},
		getGhostNode: function()
		{
			return this._ghostNode;
		},
		removeGhostNode: function()
		{
			throw "UI.CustomDragItem: The 'removeGhostNode' function is not implemented.";
		},
		processDragStart: function()
		{
		},
		processDragPositionChange: function(position)
		{
		},
		processDrag: function(x, y)
		{
		},
		processDragStop: function()
		{
		},
		addDragListener: function(listener)
		{
			this._emitter.subscribe("BX.UI.BaseDragController:drag", listener);
		},
		removeDragListener: function(listener)
		{
			this._emitter.unsubscribe("BX.UI.BaseDragController:drag", listener);
		},
		getContextId: function()
		{
			return "";
		},
		getContextData: function()
		{
			return {};
		},
		getScrollTop: function()
		{
			var html = document.documentElement;
			var body = document.body;

			var scrollTop = html.scrollTop || body && body.scrollTop || 0;
			scrollTop -= html.clientTop;

			return scrollTop;
		},
		getScrollHeight: function()
		{
			var html = document.documentElement;
			var body = document.body;

			return html.scrollHeight || body && body.scrollHeight || 0;
		},
		isDragDropBinEnabled: function()
		{
			return true;
		},
		_onDragStart: function()
		{
			if(!this._enableDrag)
			{
				return;
			}

			this.createGhostNode();

			var pos = BX.pos(this._node);
			this._ghostNode.style.top = pos.top + "px";
			this._ghostNode.style.left = pos.left + "px";

			this._currentPos = this._previousPos = null;

			this._isInDragMode = true;
			BX.UI.BaseDragController.currentDragged = this;

			this.processDragStart();

			window.setTimeout(BX.delegate(this._prepareDocument, this), 0);
		},
		_onDrag: function(x, y)
		{
			if(!this._isInDragMode)
			{
				return;
			}

			var pos = { x: x, y: y };
			this.processDragPositionChange(pos);

			if(this._ghostNode)
			{
				this._ghostNode.style.top = (pos.y + this._ghostOffset.y) + "px";
				this._ghostNode.style.left = (pos.x + this._ghostOffset.x) + "px";
			}

			this._currentPos = pos;
			if(!this._previousPos)
			{
				this._previousPos = pos;
			}

			this._scrollIfNeed();

			this.processDrag(pos.x, pos.y);
			this._emitter.emit("BX.UI.BaseDragController:drag", { item: this, x: pos.x, y: pos.y });

			this._previousPos = this._currentPos;
		},
		_onDragStop: function(x, y)
		{
			if(!this._isInDragMode)
			{
				return;
			}

			this.removeGhostNode();

			this._isInDragMode = false;
			if(BX.UI.BaseDragController.currentDragged === this)
			{
				BX.UI.BaseDragController.currentDragged = null;
			}

			this._currentPos = this._previousPos = null;

			this.processDragStop();

			window.setTimeout(BX.delegate(this._resetDocument, this), 0);
		},
		_onDragRelease: function(x, y)
		{
		},
		_prepareDocument: function()
		{
			if(!this._preserveDocument)
			{
				this._bodyOverflow = document.body.style.overflow;
				document.body.style.overflow = "hidden";
			}
		},
		_resetDocument: function()
		{
			if(!this._preserveDocument)
			{
				document.body.style.overflow = this._bodyOverflow;
			}
		},
		_scrollIfNeed: function()
		{
			if(!this._ghostNode)
			{
				return;
			}

			var html = window.document.documentElement;
			var borderTop = html.clientTop;
			var borderBottom = html.clientTop + html.clientHeight;
			var scrollHeight = this.getScrollHeight();

			var offsetY = this._currentPos.y - this._previousPos.y;
			//var offsetX = this._currentPos.x - this._previousPos.x;
			//console.log("offsetY: %d", offsetY);
			if(offsetY === 0)
			{
				return;
			}

			var previousScrollTop = -1;
			for(;;)
			{
				var scrollTop = this.getScrollTop();
				var clientRect = this._ghostNode.getBoundingClientRect();
				//console.log("scrollTop:%d, { top: %d, bottom: %d }, border: { top: %d, bottom: %d }", scrollTop, clientRect.top, clientRect.bottom, borderTop, borderBottom);

				if(offsetY > 0 && ((clientRect.bottom > borderBottom) || (borderBottom - clientRect.bottom) < 64))
				{
					if(scrollTop >= scrollHeight || previousScrollTop === scrollTop)
					{
						break;
					}

					previousScrollTop = scrollTop;
					scrollTop += 1;
					window.scrollTo(0, scrollTop < scrollHeight ? scrollTop : scrollHeight);
					//console.log("scroll bottom: %d->%d", previousScrollTop, scrollTop);
				}
				else if(offsetY < 0 && ((borderTop > clientRect.top) || (clientRect.top - borderTop) < 64))
				{
					if(scrollTop <= 0 || previousScrollTop === scrollTop)
					{
						break;
					}

					previousScrollTop = scrollTop;
					scrollTop -= 1;
					window.scrollTo(0, scrollTop > 0 ? scrollTop : 0);
					//console.log("scroll bottom: %d->%d", previousScrollTop, scrollTop);
				}
				else
				{
					break;
				}
			}
		}
	};
	BX.UI.BaseDragController.currentDragged = null;
	BX.UI.BaseDragController.emulateDrag = function()
	{
		jsDD.refreshDestArea();
		if(jsDD.current_node)
		{
			//Emulation of drag event on previous drag position
			jsDD.drag({ clientX: (jsDD.x - jsDD.wndSize.scrollLeft), clientY: (jsDD.y - jsDD.wndSize.scrollTop) });
		}
	};
}

if(typeof(BX.UI.BaseDropController) === "undefined")
{
	BX.UI.BaseDropController = function()
	{
		this._id = "";
		this._settings = {};
		this._node = null;
		this._itemDragHandler = BX.delegate(this._onItemDrag, this);
		this._draggedItem = null;
		this._emitter = null;
		this._enabled = true;
	};
	BX.UI.BaseDropController.prototype =
	{
		initialize: function(id, settings)
		{
			if(typeof(jsDD) === "undefined")
			{
				throw "BX.UI.BaseDropController: Could not find jsDD API.";
			}

			this._id = BX.type.isNotEmptyString(id) ? id : BX.util.getRandomString(8);
			this._settings = settings ? settings : {};

			this._node = this.getSetting("node");
			if(!this._node)
			{
				throw "BX.UI.BaseDropController: The 'node' parameter is not defined in settings or empty.";
			}

			this._emitter = new BX.Event.EventEmitter();
			this.doInitialize();
			this.bindEvents();
		},
		doInitialize: function()
		{
		},
		release: function()
		{
			this.doRelease();
			this.unbindEvents();
		},
		doRelease: function()
		{
		},
		getId: function()
		{
			return this._id;
		},
		getSetting: function (name, defaultval)
		{
			return this._settings.hasOwnProperty(name) ? this._settings[name] : defaultval;
		},
		bindEvents: function()
		{
			this._node.onbxdestdraghover = BX.delegate(this._onDragOver, this);
			this._node.onbxdestdraghout = BX.delegate(this._onDragOut, this);
			this._node.onbxdestdragfinish = BX.delegate(this._onDragFinish, this);
			this._node.onbxdragstop = BX.delegate(this._onDragStop, this);
			this._node.onbxdragrelease = BX.delegate(this._onDragRelease, this);

			jsDD.registerDest(this._node, this.getPriority());

			this.doBindEvents();
		},
		doBindEvents: function()
		{
		},
		unbindEvents: function()
		{
			delete this._node.onbxdestdraghover;
			delete this._node.onbxdestdraghout;
			delete this._node.onbxdestdragfinish;
			delete this._node.onbxdragstop;
			delete this._node.onbxdragrelease;

			if(BX.type.isFunction(jsDD.unregisterDest))
			{
				jsDD.unregisterDest(this._node);
			}

			this.doUnbindEvents();
		},
		doUnbindEvents: function()
		{
		},
		createPlaceHolder: function(pos)
		{
			throw "BX.UI.BaseDropController: The 'createPlaceHolder' function is not implemented.";
		},
		removePlaceHolder: function()
		{
			throw "BX.UI.BaseDropController: The 'removePlaceHolder' function is not implemented.";
		},
		initializePlaceHolder: function(pos)
		{
			this.createPlaceHolder(pos);
			this.refresh();
		},
		releasePlaceHolder: function()
		{
			this.removePlaceHolder();
			this.refresh();
		},
		getPriority: function()
		{
			return BX.UI.BaseDropController.defaultPriority;
		},
		addDragFinishListener: function(listener)
		{
			this._emitter.subscribe("BX.UI.BaseDropController:dragFinish", listener);
		},
		removeDragFinishListener: function(listener)
		{
			this._emitter.unsubscribe("BX.UI.BaseDropController:dragFinish", listener);
		},
		getDraggedItem: function()
		{
			return this._draggedItem;
		},
		setDraggedItem: function(draggedItem)
		{
			if(this._draggedItem === draggedItem)
			{
				return;
			}

			if(this._draggedItem)
			{
				this._draggedItem.removeDragListener(this._itemDragHandler);
			}

			this._draggedItem = draggedItem;

			if(this._draggedItem)
			{
				this._draggedItem.addDragListener(this._itemDragHandler);
			}
		},
		isAllowedContext: function(contextId)
		{
			return true;
		},
		isEnabled: function()
		{
			return this._enabled;
		},
		enable: function(enable)
		{
			enable = !!enable;
			if(this._enabled === enable)
			{
				return;
			}

			this._enabled = enable;
			if(enable)
			{
				jsDD.enableDest(this._node);
			}
			else
			{
				jsDD.disableDest(this._node);
			}
		},
		refresh: function()
		{
			jsDD.refreshDestArea(this._node.__bxddeid);
		},
		processDragOver: function(pos)
		{
			this.initializePlaceHolder(pos);
		},
		processDragOut: function()
		{
			this.releasePlaceHolder();
		},
		processDragStop: function()
		{
			this.releasePlaceHolder();
		},
		processDragRelease: function()
		{
			this.releasePlaceHolder();
		},
		processItemDrop: function()
		{
				this.releasePlaceHolder();
		},
		_onDragOver: function(node, x, y)
		{
			var draggedItem = BX.UI.BaseDragController.currentDragged;
			if(!draggedItem)
			{
				return;
			}

			if(!this.isAllowedContext(draggedItem.getContextId()))
			{
				return;
			}

			this.setDraggedItem(draggedItem);
			this.processDragOver({ x: x, y: y });
		},
		_onDragOut: function(node, x, y)
		{
			if(!this._draggedItem)
			{
				return;
			}

			this.processDragOut();
			this.setDraggedItem(null);
		},
		_onDragFinish: function(node, x, y)
		{
			if(!this._draggedItem)
			{
				return;
			}

			this._emitter.emit(
				"BX.UI.BaseDropController:dragFinish",
				{ dropContainer: this, draggedItem: this._draggedItem, x: x, y: y }
			);

			this.processItemDrop();
			this.setDraggedItem(null);

			BX.UI.BaseDropController.refresh();
		},
		_onDragRelease: function(node, x, y)
		{
			if(!this._draggedItem)
			{
				return;
			}

			this.processDragRelease();
			this.setDraggedItem(null);
		},
		_onDragStop: function(node, x, y)
		{
			if(!this._draggedItem)
			{
				return;
			}

			this.processDragStop();
			this.setDraggedItem(null);
		},
		_onItemDrag: function(event)
		{
			if(!this._draggedItem)
			{
				return;
			}

			this.initializePlaceHolder({ x: event.data["x"], y: event.data["y"] });
		}
	};
	BX.UI.BaseDropController.defaultPriority = 100;
	BX.UI.BaseDropController.refresh = function()
	{
		jsDD.refreshDestArea();
	};
}
//endregion

if(typeof(BX.UI.EditorDragItem) === "undefined")
{
	BX.UI.EditorDragItem = function()
	{
	};
	BX.UI.EditorDragItem.prototype =
	{
		getType: function()
		{
			return BX.UI.EditorDragObjectType.intermediate;
		},
		getContextId: function()
		{
			return "";
		},
		createGhostNode: function()
		{
			return null;
		},
		processDragStart: function()
		{
		},
		processDragPositionChange: function(pos, ghostRect)
		{
		},
		processDragStop: function()
		{
		}
	};
}

if(typeof(BX.UI.EditorFieldDragItem) === "undefined")
{
	BX.UI.EditorFieldDragItem = function()
	{
		BX.UI.EditorFieldDragItem.superclass.constructor.apply(this);
		this._scope = BX.UI.EditorDragScope.undefined;
		this._control = null;
		this._contextId = "";
	};
	BX.extend(BX.UI.EditorFieldDragItem, BX.UI.EditorDragItem);
	BX.UI.EditorFieldDragItem.prototype.initialize = function(settings)
	{
		this._control = BX.prop.get(settings, "control");
		if(!this._control)
		{
			throw "UI.EditorFieldDragItem: The 'control' parameter is not defined in settings or empty.";
		}
		this._scope = BX.prop.getInteger(settings, "scope", BX.UI.EditorDragScope.getDefault());
		this._contextId = BX.prop.getString(settings, "contextId", "");
	};
	BX.UI.EditorFieldDragItem.prototype.getType = function()
	{
		return BX.UI.EditorDragObjectType.field;
	};
	BX.UI.EditorFieldDragItem.prototype.getControl = function()
	{
		return this._control;
	};
	BX.UI.EditorFieldDragItem.prototype.getContextId = function()
	{
		return this._contextId !== "" ? this._contextId : BX.UI.EditorFieldDragItem.contextId;
	};
	BX.UI.EditorFieldDragItem.prototype.createGhostNode = function()
	{
		return this._control.createGhostNode();
	};
	BX.UI.EditorFieldDragItem.prototype.processDragStart = function()
	{
		window.setTimeout(
			function()
			{
				//Ensure Field drag controllers are enabled.
				BX.UI.EditorDragContainerController.enable(BX.UI.EditorFieldDragItem.contextId, true);
				//Disable Section drag controllers for the avoidance of collisions.
				BX.UI.EditorDragContainerController.enable(BX.UI.EditorSectionDragItem.contextId, false);
				//Refresh all drag&drop destination areas.
				BX.UI.EditorDragContainerController.refreshAll();
			}
		);
		// this._control.getWrapper().style.opacity = "0.2";
		this._control.getWrapper().style.display = "none";
	};
	BX.UI.EditorFieldDragItem.prototype.processDragPositionChange = function(pos, ghostRect)
	{
		var parentPos = this._scope === BX.UI.EditorDragScope.parent
			? this._control.getParentPosition()
			: this._control.getRootContainerPosition();

		if(pos.y < parentPos.top)
		{
			pos.y = parentPos.top;
		}
		if((pos.y + ghostRect.height) > parentPos.bottom)
		{
			pos.y = parentPos.bottom - ghostRect.height;
		}
		if(pos.x < parentPos.left)
		{
			pos.x = parentPos.left;
		}
		if((pos.x + ghostRect.width) > parentPos.right)
		{
			pos.x = parentPos.right - ghostRect.width;
		}
	};
	BX.UI.EditorFieldDragItem.prototype.processDragStop = function()
	{
		window.setTimeout(
			function()
			{
				//Returning Section drag controllers to work.
				BX.UI.EditorDragContainerController.enable(BX.UI.EditorSectionDragItem.contextId, true);
				//Refresh all drag&drop destination areas.
				BX.UI.EditorDragContainerController.refreshAll();
			}
		);
		// this._control.getWrapper().style.opacity = "1";
		this._control.getWrapper().style.display = "";
	};
	BX.UI.EditorFieldDragItem.contextId = "editor_field";
	BX.UI.EditorFieldDragItem.create = function(settings)
	{
		var self = new BX.UI.EditorFieldDragItem();
		self.initialize(settings);
		return self;
	};
}

if(typeof(BX.UI.EditorSectionDragItem) === "undefined")
{
	BX.UI.EditorSectionDragItem = function()
	{
		BX.UI.EditorSectionDragItem.superclass.constructor.apply(this);
		this._control = null;
	};
	BX.extend(BX.UI.EditorSectionDragItem, BX.UI.EditorDragItem);
	BX.UI.EditorSectionDragItem.prototype.initialize = function(settings)
	{
		this._control = BX.prop.get(settings, "control");
		if(!this._control)
		{
			throw "UI.EditorSectionDragItem: The 'control' parameter is not defined in settings or empty.";
		}
	};
	BX.UI.EditorSectionDragItem.prototype.getType = function()
	{
		return BX.UI.EditorDragObjectType.section;
	};
	BX.UI.EditorSectionDragItem.prototype.getControl = function()
	{
		return this._control;
	};
	BX.UI.EditorSectionDragItem.prototype.getContextId = function()
	{
		return BX.UI.EditorSectionDragItem.contextId;
	};
	BX.UI.EditorSectionDragItem.prototype.createGhostNode = function()
	{
		return this._control.createGhostNode();
	};
	BX.UI.EditorSectionDragItem.prototype.processDragStart = function()
	{
		BX.addClass(document.body, "ui-entity-cards-drag");

		var control = this._control;

		var wrapperContent = control.getWrapper().querySelector(".ui-entity-editor-section-content");

		wrapperContent.style.height = BX.pos(wrapperContent).height + "px";

		BX.addClass(control.getWrapper(), "ui-entity-item-ghost");

		window.setTimeout(
			function()
			{
				wrapperContent.style.height = 0;
				wrapperContent.style.padding = 0;
			},
			0
		);

		// console.log(wrapperContent, wrapperContentHeight);


		window.setTimeout(
			function()
			{
				//Ensure Section drag controllers are enabled.
				BX.UI.EditorDragContainerController.enable(BX.UI.EditorSectionDragItem.contextId, true);
				//Disable Field drag controllers for the avoidance of collisions.
				BX.UI.EditorDragContainerController.enable(BX.UI.EditorFieldDragItem.contextId, false);
				//Refresh all drag&drop destination areas.
				BX.UI.EditorDragContainerController.refreshAll();

				window.setTimeout(
					function()
					{
						var firstControl = control.getSiblingByIndex(0);
						if(firstControl !== null && firstControl !== control)
						{
							firstControl.getWrapper().scrollIntoView();
						}
					},
					200
				);
			}
		);
	};
	BX.UI.EditorSectionDragItem.prototype.processDragStop = function()
	{
		//crm-entity-widgets-drag -> ui-entity-cards-drag
		BX.removeClass(document.body, "ui-entity-cards-drag");
		window.setTimeout(
			function()
			{
				//Returning Field drag controllers to work.
				BX.UI.EditorDragContainerController.enable(BX.UI.EditorFieldDragItem.contextId, true);
				//Refresh all drag&drop destination areas.
				BX.UI.EditorDragContainerController.refreshAll();
			}
		);

		var control = this._control;

		var wrapperContent = control.getWrapper().querySelector(".ui-entity-editor-section-content");
		BX.removeClass(control.getWrapper(), "ui-entity-item-ghost");
		wrapperContent.style = "";
	};
	BX.UI.EditorSectionDragItem.contextId = "editor_section";
	BX.UI.EditorSectionDragItem.create = function(settings)
	{
		var self = new BX.UI.EditorSectionDragItem();
		self.initialize(settings);
		return self;
	};
}

if(typeof(BX.UI.EditorDragItemController) === "undefined")
{
	BX.UI.EditorDragItemController = function()
	{
		BX.UI.EditorDragItemController.superclass.constructor.apply(this);
		this._charge = null;
		this._emitter = null;
		this._preserveDocument = true;
	};

	BX.extend(BX.UI.EditorDragItemController, BX.UI.BaseDragController);
	BX.UI.EditorDragItemController.prototype.doInitialize = function()
	{
		this._charge = this.getSetting("charge");
		if(!this._charge)
		{
			throw "UI.EditorDragItemController: The 'charge' parameter is not defined in settings or empty.";
		}

		this._emitter = new BX.Event.EventEmitter();
		this._ghostOffset = { x: 0, y: 0 };
	};
	BX.UI.EditorDragItemController.prototype.addStartListener = function(listener)
	{
		this._emitter.subscribe("BX.UI.EditorDragItemController:dragStart", listener);
	};
	BX.UI.EditorDragItemController.prototype.removeStartListener = function(listener)
	{
		this._emitter.unsubscribe("BX.UI.EditorDragItemController:dragStart", listener);
	};
	BX.UI.EditorDragItemController.prototype.addStopListener = function(listener)
	{
		this._emitter.subscribe("BX.UI.EditorDragItemController:dragStop", listener);
	};
	BX.UI.EditorDragItemController.prototype.removeStopListener = function(listener)
	{
		this._emitter.unsubscribe("BX.UI.EditorDragItemController:dragStop", listener);
	};
	BX.UI.EditorDragItemController.prototype.getCharge = function()
	{
		return this._charge;
	};
	BX.UI.EditorDragItemController.prototype.createGhostNode = function()
	{
		if(this._ghostNode)
		{
			return this._ghostNode;
		}

		this._ghostNode = this._charge.createGhostNode();
		document.body.appendChild(this._ghostNode);
	};
	BX.UI.EditorDragItemController.prototype.getGhostNode = function()
	{
		return this._ghostNode;
	};
	BX.UI.EditorDragItemController.prototype.removeGhostNode = function()
	{
		if(this._ghostNode)
		{
			document.body.removeChild(this._ghostNode);
			this._ghostNode = null;
		}
	};
	BX.UI.EditorDragItemController.prototype.getContextId = function()
	{
		return this._charge.getContextId();
	};
	BX.UI.EditorDragItemController.prototype.getContextData = function()
	{
		return ({ contextId: this._charge.getContextId(), charge: this._charge });
	};
	BX.UI.EditorDragItemController.prototype.processDragStart = function()
	{
		BX.UI.EditorDragItemController.current = this;
		this._charge.processDragStart();
		BX.UI.EditorDragContainerController.refresh(this._charge.getContextId());

		//var event = new BX.Event.BaseEvent({ data: { } });
		this._emitter.emit("BX.UI.EditorDragItemController:dragStart", {});
	};
	BX.UI.EditorDragItemController.prototype.processDrag = function(x, y)
	{
	};
	BX.UI.EditorDragItemController.prototype.processDragPositionChange = function(pos)
	{
		this._charge.processDragPositionChange(pos, BX.pos(this.getGhostNode()));
	};
	BX.UI.EditorDragItemController.prototype.processDragStop = function()
	{
		BX.UI.EditorDragItemController.current = null;
		this._charge.processDragStop();
		BX.UI.EditorDragContainerController.refreshAfter(this._charge.getContextId(), 300);

		//var event = new BX.Event.BaseEvent({ data: { } });
		this._emitter.emit("BX.UI.EditorDragItemController:dragStop", {});
	};
	BX.UI.EditorDragItemController.current = null;
	BX.UI.EditorDragItemController.create = function(id, settings)
	{
		var self = new BX.UI.EditorDragItemController();
		self.initialize(id, settings);
		return self;
	};
}

if(typeof(BX.UI.EditorDragContainer) === "undefined")
{
	BX.UI.EditorDragContainer = function()
	{
	};
	BX.UI.EditorDragContainer.prototype =
	{
		getContextId: function()
		{
			return "";
		},
		getPriority: function()
		{
			return 100;
		},
		hasPlaceHolder: function()
		{
			return false;
		},
		createPlaceHolder: function(index)
		{
			return null;
		},
		getPlaceHolder: function()
		{
			return null;
		},
		removePlaceHolder: function()
		{
		},
		getChildNodes: function()
		{
			return [];
		},
		getChildNodeCount: function()
		{
			return 0;
		}
	}
}

if(typeof(BX.UI.EditorFieldDragContainer) === "undefined")
{
	BX.UI.EditorFieldDragContainer = function()
	{
		BX.UI.EditorFieldDragContainer.superclass.constructor.apply(this);
		this._section = null;
		this._context = "";
	};
	BX.extend(BX.UI.EditorFieldDragContainer, BX.UI.EditorDragContainer);
	BX.UI.EditorFieldDragContainer.prototype.initialize = function(settings)
	{
		this._section = BX.prop.get(settings, "section");
		if(!this._section)
		{
			throw "UI.EditorSectionDragContainer: The 'section' parameter is not defined in settings or empty.";
		}

		this._context = BX.prop.getString(settings, "context", "");
	};
	BX.UI.EditorFieldDragContainer.prototype.getSection = function()
	{
		return this._section;
	};
	BX.UI.EditorFieldDragContainer.prototype.getContextId = function()
	{
		return this._context !== "" ? this._context : BX.UI.EditorFieldDragItem.contextId;
	};
	BX.UI.EditorFieldDragContainer.prototype.getPriority = function()
	{
		return 10;
	};
	BX.UI.EditorFieldDragContainer.prototype.hasPlaceHolder = function()
	{
		return this._section.hasPlaceHolder();
	};
	BX.UI.EditorFieldDragContainer.prototype.createPlaceHolder = function(index)
	{
		return this._section.createPlaceHolder(index);
	};
	BX.UI.EditorFieldDragContainer.prototype.getPlaceHolder = function()
	{
		return this._section.getPlaceHolder();
	};
	BX.UI.EditorFieldDragContainer.prototype.removePlaceHolder = function()
	{
		this._section.removePlaceHolder();
	};
	BX.UI.EditorFieldDragContainer.prototype.getChildNodes = function()
	{
		var nodes = [];
		var items = this._section.getChildren();
		for(var i = 0, length = items.length; i < length; i++)
		{
			nodes.push(items[i].getWrapper());
		}
		return nodes;
	};
	BX.UI.EditorFieldDragContainer.prototype.getChildNodeCount = function()
	{
		return this._section.getChildCount();
	};
	BX.UI.EditorFieldDragContainer.create = function(settings)
	{
		var self = new BX.UI.EditorFieldDragContainer();
		self.initialize(settings);
		return self;
	};
}

if(typeof(BX.UI.EditorSectionDragContainer) === "undefined")
{
	BX.UI.EditorSectionDragContainer = function()
	{
		BX.UI.EditorSectionDragContainer.superclass.constructor.apply(this);
		this._column = null;
	};
	BX.extend(BX.UI.EditorSectionDragContainer, BX.UI.EditorDragContainer);
	BX.UI.EditorSectionDragContainer.prototype.initialize = function(settings)
	{
		this._column = BX.prop.get(settings, "column");
		if(!this._column)
		{
			throw "UI.EditorSectionDragContainer: The 'column' parameter is not defined in settings or empty.";
		}
	};
	BX.UI.EditorSectionDragContainer.prototype.getColumn = function()
	{
		return this._column;
	};
	BX.UI.EditorSectionDragContainer.prototype.getEditor = function()
	{
		return this.getColumn().getParent();
	};
	BX.UI.EditorSectionDragContainer.prototype.getContextId = function()
	{
		return BX.UI.EditorSectionDragItem.contextId;
	};
	BX.UI.EditorSectionDragContainer.prototype.getPriority = function()
	{
		return 20;
	};
	BX.UI.EditorSectionDragContainer.prototype.hasPlaceHolder = function()
	{
		return this._column.hasPlaceHolder();
	};
	BX.UI.EditorSectionDragContainer.prototype.createPlaceHolder = function(index)
	{
		return this._column.createPlaceHolder(index);
	};
	BX.UI.EditorSectionDragContainer.prototype.getPlaceHolder = function()
	{
		return this._column.getPlaceHolder();
	};
	BX.UI.EditorSectionDragContainer.prototype.removePlaceHolder = function()
	{
		this._column.removePlaceHolder();
	};
	BX.UI.EditorSectionDragContainer.prototype.getChildNodes = function()
	{
		var nodes = [];
		var items = this._column.getChildren();
		for(var i = 0, length = items.length; i < length; i++)
		{
			nodes.push(items[i].getWrapper());
		}
		return nodes;
	};
	BX.UI.EditorSectionDragContainer.prototype.getChildNodeCount = function()
	{
		return this._column.getControlCount();
	};
	BX.UI.EditorSectionDragContainer.create = function(settings)
	{
		var self = new BX.UI.EditorSectionDragContainer();
		self.initialize(settings);
		return self;
	};
}

if(typeof(BX.UI.EditorDragContainerController) === "undefined")
{
	BX.UI.EditorDragContainerController = function()
	{
		BX.UI.EditorDragContainerController.superclass.constructor.apply(this);
		this._charge = null;
	};
	BX.extend(BX.UI.EditorDragContainerController, BX.UI.BaseDropController);
	BX.UI.EditorDragContainerController.prototype.doInitialize = function()
	{
		this._charge = this.getSetting("charge");
		if(!this._charge)
		{
			throw "UI.EditorDragContainerController: The 'charge' parameter is not defined in settings or empty.";
		}
	};
	BX.UI.EditorDragContainerController.prototype.getCharge = function()
	{
		return this._charge;
	};
	BX.UI.EditorDragContainerController.prototype.createPlaceHolder = function(pos)
	{
		var ghostRect = BX.pos(BX.UI.EditorDragItemController.current.getGhostNode());
		var ghostTop = ghostRect.top, ghostBottom = ghostRect.top + 40;
		var ghostMean = Math.floor((ghostTop + ghostBottom) / 2);

		var rect, mean;
		var placeholder = this._charge.getPlaceHolder();
		if(placeholder)
		{
			rect = placeholder.getPosition();
			mean = Math.floor((rect.top + rect.bottom) / 2);
			if(
				(ghostTop <= rect.bottom && ghostTop >= rect.top) ||
				(ghostBottom >= rect.top && ghostBottom <= rect.bottom) ||
				Math.abs(ghostMean - mean) <= 8
			)
			{
				if(!placeholder.isActive())
				{
					placeholder.setActive(true);
				}
				return;
			}
		}

		var nodes = this._charge.getChildNodes();
		for(var i = 0; i < nodes.length; i++)
		{
			rect = BX.pos(nodes[i]);
			mean = Math.floor((rect.top + rect.bottom) / 2);
			if(
				(ghostTop <= rect.bottom && ghostTop >= rect.top) ||
				(ghostBottom >= rect.top && ghostBottom <= rect.bottom) ||
				Math.abs(ghostMean - mean) <= 8
			)
			{
				this._charge.createPlaceHolder((ghostMean - mean) <= 0 ? i : (i + 1)).setActive(true);
				return;
			}
		}

		this._charge.createPlaceHolder(-1).setActive(true);
		this.refresh();
	};
	BX.UI.EditorDragContainerController.prototype.removePlaceHolder = function()
	{
		if(this._charge.hasPlaceHolder())
		{
			this._charge.removePlaceHolder();
			this.refresh();
		}
	};
	BX.UI.EditorDragContainerController.prototype.getContextId = function()
	{
		return this._charge.getContextId();
	};
	BX.UI.EditorDragContainerController.prototype.getPriority = function()
	{
		return this._charge.getPriority();
	};
	BX.UI.EditorDragContainerController.prototype.isAllowedContext = function(contextId)
	{
		return contextId === this._charge.getContextId();
	};
	BX.UI.EditorDragContainerController.refresh = function(contextId)
	{
		for(var k in this.items)
		{
			if(!this.items.hasOwnProperty(k))
			{
				continue;
			}
			var item = this.items[k];
			if(item.getContextId() === contextId)
			{
				item.refresh();
			}
		}
	};
	BX.UI.EditorDragContainerController.refreshAfter = function(contextId, interval)
	{
		interval = parseInt(interval);
		if(interval > 0)
		{
			window.setTimeout(function() { BX.UI.EditorDragContainerController.refresh(contextId); }, interval);
		}
		else
		{
			this.refresh(contextId);
		}
	};
	BX.UI.EditorDragContainerController.refreshAll = function()
	{
		for(var k in this.items)
		{
			if(!this.items.hasOwnProperty(k))
			{
				continue;
			}
			this.items[k].refresh();
		}
	};
	BX.UI.EditorDragContainerController.enable = function(contextId, enable)
	{
		for(var k in this.items)
		{
			if(!this.items.hasOwnProperty(k))
			{
				continue;
			}
			var item = this.items[k];
			if(item.getContextId() === contextId)
			{
				item.enable(enable);
			}
		}
	};
	BX.UI.EditorDragContainerController.items = {};
	BX.UI.EditorDragContainerController.create = function(id, settings)
	{
		var self = new BX.UI.EditorDragContainerController();
		self.initialize(id, settings);
		this.items[self.getId()] = self;
		return self;
	};
}

if(typeof(BX.UI.EditorDragPlaceholder) === "undefined")
{
	BX.UI.EditorDragPlaceholder = function()
	{
		this._settings = null;
		this._container = null;
		this._node = null;
		this._isDragOver = false;
		this._isActive = false;
		this._index = -1;
		this._timeoutId = null;
	};
	BX.UI.EditorDragPlaceholder.prototype =
	{
		initialize: function(settings)
		{
			this._settings = settings ? settings : {};
			this._container = this.getSetting("container", null);

			this._isActive = this.getSetting("isActive", false);
			this._index = parseInt(this.getSetting("index", -1));
		},
		getSetting: function (name, defaultval)
		{
			return this._settings.hasOwnProperty(name) ? this._settings[name] : defaultval;
		},
		getContainer: function()
		{
			return this._container;
		},
		setContainer: function(container)
		{
			this._container = container;
		},
		isDragOver: function()
		{
			return this._isDragOver;
		},
		isActive: function()
		{
			return this._isActive;
		},
		setActive: function(active, interval)
		{
			if(this._timeoutId !== null)
			{
				window.clearTimeout(this._timeoutId);
				this._timeoutId = null;
			}

			interval = parseInt(interval);
			if(interval > 0)
			{
				var self = this;
				window.setTimeout(function(){ if(self._timeoutId === null) return; self._timeoutId = null; self.setActive(active, 0); }, interval);
				return;
			}

			active = !!active;
			if(this._isActive === active)
			{
				return;
			}

			this._isActive = active;
			if(this._node)
			{
				//this._node.className = active ? "crm-lead-header-drag-zone-bd" : "crm-lead-header-drag-zone-bd-inactive";
			}
		},
		getIndex: function()
		{
			return this._index;
		},
		prepareNode: function()
		{
			return null;
		},
		layout: function()
		{
			this._node = this.prepareNode();
			var anchor = this.getSetting("anchor", null);
			if(anchor)
			{
				this._container.insertBefore(this._node, anchor);
			}
			else
			{
				this._container.appendChild(this._node);
			}

			BX.bind(this._node, "dragover", BX.delegate(this._onDragOver, this));
			BX.bind(this._node, "dragleave", BX.delegate(this._onDragLeave, this));
		},
		clearLayout: function()
		{
			if(this._node)
			{
				this._node = BX.remove(this._node);
				// this._node.style.height = 0;
				// setTimeout(BX.proxy(function (){this._node = BX.remove(this._node);}, this), 100);
			}
		},
		getPosition: function()
		{
			return BX.pos(this._node);
		},
		_onDragOver: function(e)
		{
			e = e || window.event;
			this._isDragOver = true;
			return BX.eventReturnFalse(e);
		},
		_onDragLeave: function(e)
		{
			e = e || window.event;
			this._isDragOver = false;
			return BX.eventReturnFalse(e);
		}
	}
}

if(typeof(BX.UI.EditorDragFieldPlaceholder) === "undefined")
{
	BX.UI.EditorDragFieldPlaceholder = function()
	{
	};

	BX.extend(BX.UI.EditorDragFieldPlaceholder, BX.UI.EditorDragPlaceholder);
	BX.UI.EditorDragFieldPlaceholder.prototype.prepareNode = function()
	{
		//crm-entity-widget-content-block-place -> ui-entity-editor-content-block-place
		return BX.create("div", { attrs: { className: "ui-entity-editor-content-block-place" } });
	};
	BX.UI.EditorDragFieldPlaceholder.create = function(settings)
	{
		var self = new BX.UI.EditorDragFieldPlaceholder();
		self.initialize(settings);
		return self;
	};
}

if(typeof(BX.UI.EditorDragSectionPlaceholder) === "undefined")
{
	BX.UI.EditorDragSectionPlaceholder = function()
	{
	};

	BX.extend(BX.UI.EditorDragSectionPlaceholder, BX.UI.EditorDragPlaceholder);
	BX.UI.EditorDragSectionPlaceholder.prototype.prepareNode = function()
	{
		//crm-entity-card-widget -> ui-entity-editor-section
		//crm-entity-card-widget-place -> ui-entity-editor-section-place
		return BX.create("div", { attrs: { className: "ui-entity-editor-section ui-entity-editor-section-place" } });
	};
	BX.UI.EditorDragSectionPlaceholder.create = function(settings)
	{
		var self = new BX.UI.EditorDragSectionPlaceholder();
		self.initialize(settings);
		return self;
	};
}
//endregion