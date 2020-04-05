BX.namespace("BX.Admin.DraggableTab");

BX.Admin.DraggableTab = (function()
{
	var DraggableTab = function(arParams)
	{
		if (typeof arParams !== "object")
			return;

		this.moduleId = arParams.moduleId;
		this.tabId = arParams.tabId;
		this.optionName = arParams.optionName;
		this.hidden = arParams.hidden;

		this.dragObjects = BX(this.tabId).querySelectorAll('[data-role="dragObj"]');

		if (typeof this.dragObjects !== "object")
			return;

		BX.loadScript("/bitrix/js/main/dd.js");

		for(var i=0; i<this.dragObjects.length; i++)
		{
			if (!this.dragObjects[i].hasAttribute("data-onlydest"))
			{
				var dragHeader = BX.findChild(this.dragObjects[i], {className:"draggable"}, true, false);
				dragHeader.onbxdragstart = BX.proxy(this.sectionDragStart, this);
				dragHeader.onbxdrag = BX.proxy(this.sectionDragMove, this);
				dragHeader.onbxdragstop = BX.proxy(this.sectionDragStop, this);
				dragHeader.onbxdraghover = BX.proxy(this.sectionDragHover, this);
				jsDD.registerObject(dragHeader);

				var toggleObj = this.dragObjects[i].querySelector('[data-role="toggleObj"]');
				BX.bind(toggleObj, "click", BX.proxy(function(){
					this.self.toggleHandler(this.dragObj);
				}, {self:this, dragObj:this.dragObjects[i]}));
			}
			jsDD.registerDest(this.dragObjects[i], 200);
		}
	};

	DraggableTab.prototype.sectionDragStart = function()
	{
		var dragElement = BX.proxy_context.parentNode.parentNode.parentNode;

		this.bxSectParent = dragElement.parentNode;
		this.bxSectParentHeight = dragElement.parentNode.offsetHeight;
		this.bxSectParent.style.height = this.bxSectParentHeight + "px";
		this.objSectHeight = dragElement.offsetHeight;
		var allDarElStyles = getComputedStyle(dragElement);
		var marginTop = allDarElStyles.marginTop;
		marginTop = marginTop.replace("px","");
		var marginBottom = allDarElStyles.marginBottom;
		marginBottom = marginBottom.replace("px","");
		this.objSectMarginHeight = Number(this.objSectHeight) + Number(marginTop) + Number(marginBottom);

		this.bxSectBlank = this.bxSectParent.insertBefore(BX.create('DIV', {style: {height: '0px'}}), dragElement);
		this.bxSectBlank1 = BX.create('DIV', {style: {height: this.objSectMarginHeight + "px"}}); //empty div
		jsDD.disableDest(this.bxSectParent);

		this.bxSectBlock = BX.create('DIV', {             //div to move
			style: {
				position: 'absolute',
				zIndex: '100',
				height: this.objSectHeight+"px",
				width: dragElement.offsetWidth+"px"
			},
			children: [dragElement]
		});

		this.bxSectPos = BX.pos(this.bxSectParent);
		this.bxSectParent.appendChild(this.bxSectBlock);
	};

	DraggableTab.prototype.sectionDragMove = function(x, y)
	{
		y -= this.bxSectPos.top;

		if (y < 0)
			y = 0;

		if (y > this.bxSectParentHeight - this.objSectHeight)
			y = this.bxSectParentHeight - this.objSectHeight;

		this.bxSectBlock.style.top = y + 'px';
	};

	DraggableTab.prototype.sectionDragHover = function(dest, x, y)
	{
		var dragElement = BX.proxy_context.parentNode.parentNode.parentNode;

		if (dest == dragElement)
		{
			this.bxSectParent.insertBefore(this.bxSectBlank1, this.bxSectBlank);
		}
		else if (dest.parentNode == this.bxSectParent)
		{
			if (dest.nextSibling)
				this.bxSectParent.insertBefore(this.bxSectBlank1, dest.nextSibling);
			else
				this.bxSectParent.appendChild(this.bxSectBlank1);
		}
	};

	DraggableTab.prototype.sectionDragStop = function()
	{
		var dragElement = BX.proxy_context.parentNode.parentNode.parentNode;

		if (this.bxSectBlank1 && this.bxSectBlank1.parentNode == this.bxSectParent)
		{
			this.bxSectParent.replaceChild(dragElement, this.bxSectBlank1);
		}
		else
		{
			this.bxSectParent.replaceChild(dragElement, this.bxSectBlank);
		}

		this.bxSectParent.style.height = "";

		BX.remove(this.bxSectBlock);
		BX.remove(this.bxSectBlank);
		BX.remove(this.bxSectBlank1);

		jsDD.enableDest(dragElement);

		var allDragObj = this.bxSectParent.querySelectorAll('[data-role="dragObj"]');

		var objOrder = [];
		for(var i=0; i<allDragObj.length; i++)
		{
			if (!allDragObj[i].hasAttribute("data-onlydest"))
			{
				if (allDragObj[i].hasAttribute("data-id"))
				{
					objOrder.push(allDragObj[i].getAttribute("data-id"));
				}
			}
		}

		this.dragHandler(objOrder);

		this.bxSectBlock = null;
		this.bxSectBlank = null;
		this.bxSectBlank1 = null;
		this.bxSectParent = null;
		this.objSectHeight = 0;
		jsDD.refreshDestArea();
	};

	DraggableTab.prototype.dragHandler = function(objOrder)
	{
		BX.userOptions.save(this.moduleId, this.optionName, "order", objOrder);
	};

	DraggableTab.prototype.toggleHandler = function(dragObj)
	{
		var dataId = dragObj.getAttribute("data-id");
		var newHidden = [];

		if (BX.hasClass(dragObj, "hidden"))
		{
			BX.removeClass(dragObj, "hidden");

			for (var i in this.hidden)
			{
				if (this.hidden[i] != dataId)
				{
					newHidden.push(this.hidden[i]);
				}
				else
				{
					delete this.hidden[i];
				}
			}
		}
		else
		{
			BX.addClass(dragObj, "hidden");

			var found = false;
			for (var i in this.hidden)
			{
				if (this.hidden[i] == dataId)
				{
					found = true;
					break;
				}
			}
			if (!found)
				this.hidden.push(dataId);

			newHidden = this.hidden;
		}

		BX.userOptions.save(this.moduleId, this.optionName, "hidden", newHidden);
	};

	return DraggableTab;
})();