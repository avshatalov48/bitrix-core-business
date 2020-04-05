;(function(window){
	if (typeof(BX.DragDrop) !== "undefined") return;

	BX.DragDrop = function(params)
	{
		this.dragItemControlClassName = params.dragItemControlClassName || null;
		this.dragNodeList =  document.body.querySelectorAll('.' + params.dragItemClassName);
		this.dragStartCallback = params.dragStart || null;
		this.dragCallback = params.drag || null;
		this.dragOverCallback = params.dragOver || null;
		this.dragEnterCallback = params.dragEnter || null;
		this.dragLeaveCallback = params.dragLeave || null;
		this.dragDropCallback = params.dragDrop || null;
		this.dragEndCallback = params.dragEnd || null;
		this.dragActiveClass = params.dragActiveClass || null; /*className for node which drag*/
		this.activeDragElement = null;
		this.isDocDragover = false;
		this.isAutoScroll = false;
		this.scrollIntervalID = 0;
		this.isScrollInterval = false;
		this.isSortable = false;
		this.isSortableActive = false;

		if(typeof(params.dropZoneList) == 'string')
			this.dropZoneNodeList = document.body.querySelectorAll('.' +params.dropZoneList);
		else if(BX.type.isArray(params.dropZoneList))
			this.dropZoneNodeList = params.dropZoneList;
		else if(BX.type.isDomNode(params.dropZoneList))
			this.dropZoneNodeList = [params.dropZoneList];

		if(typeof params.sortable == 'object')
		{
			var nodes = params.sortable.className
				? document.body.querySelectorAll('.' + params.sortable.className) : this.dragNodeList;
			var sortableList = BX.convert.nodeListToArray(nodes);

			this.sortable =
			{
				rootElem : params.sortable.rootElem,
				gagClass : params.sortable.gagClass,
				gagHtml : params.sortable.gagHtml || '',
				list : sortableList,
				floating : /left|right|inline/.test(BX.style(sortableList[0], 'float') + BX.style(sortableList[0], 'display')),
				node : params.sortable.node || null,
				isNode : false
			};

			if(this.sortable.node)
				this.sortable.isNode = true;

			this.isSortable = true;
		}

		this.dragEventX = 0;
		this.dragEventY = 0;

		this.scroll =
		{
			edgeBottom : 0,
			edgeTop : 0,
			edgeLeft : 0,
			edgeRight : 0,
			height : 0,
			width : 0,
			visibleHeight : 0,
			visibleWidth : 0,
			speedX : 10,
			speedY : 10,
			prevPosDragEvY : -1,
			prevPosDragEvX : -1
		};

		this.isIE = (document.documentMode && document.documentMode <=9) || false;
		this._bind();
	};

	BX.DragDrop.prototype =
	{
		_bind : function()
		{
			/*if(this.isIE)
				BX.bind(document, 'selectstart', BX.proxy(this.onSelect, this));*/

			if(this.dragNodeList && this.dragNodeList.length > 0)
				this.bindDragItem(this.dragNodeList);

			if(this.dropZoneNodeList && this.dropZoneNodeList.length > 0)
				this.bindCatcher(this.dropZoneNodeList);
		},
		bindDragItem : function(dragList)
		{
			var _this = this;
			var dragItemControl = null;
			for(var i=dragList.length-1; i>=0; i--)
			{
				dragItemControl = null;
				if(this.dragItemControlClassName)
				{
					dragItemControl = dragList[i].querySelector('.' + this.dragItemControlClassName);
					dragItemControl = BX(dragItemControl);
				}
				if(!dragItemControl)
				{
					dragItemControl = dragList[i];
				}

				dragItemControl.draggable = true;

				if(this.isIE){
					BX.bind(dragItemControl, 'selectstart', function(event)
					{
						event = event || window.event;
						BX.PreventDefault(event);
						_this.onSelect(event, this);
					});
				}

				BX.bind(dragItemControl, 'dragstart', BX.delegate(function(event)
				{
					event = event || window.event;
					_this.ondragStart(event, this);
				}, dragList[i]));
				BX.bind(dragItemControl, 'drag', BX.proxy(this.ondrag, this));
				BX.bind(dragItemControl, 'dragend', BX.delegate(function(event)
				{
					event = event || window.event;
					_this.ondragEnd(event, this);
				}, dragList[i]));
			}
		},
		bindCatcher : function(catcherList)
		{
			var _this = this;
			for(var i = catcherList.length-1; i>=0; i--)
			{
				BX.bind(catcherList[i], 'dragover', function(event)
				{
					if(_this.isAutoScroll){
						event = event || window.event;
						_this.dragEventX = event.clientX;
						_this.dragEventY = event.clientY;
					}

					BX.PreventDefault(event);
					event.dataTransfer.dropEffect = 'move';

					_this.ondragOver(event, this);
				});
				BX.bind(catcherList[i], 'dragenter',function(event)
				{
					event = event || window.event;
					BX.PreventDefault(event);
					_this.ondragEnter(event, this);
				});

				BX.bind(catcherList[i], 'dragleave', function(event)
				{
					event = event || window.event;
					BX.PreventDefault(event);
					_this.ondragLeave(event, this);
				});

				BX.bind(catcherList[i], 'drop', function(event)
				{
					event = event || window.event;
					BX.PreventDefault(event);
					_this.ondragDrop(event, this);
				});
			}
		},
		addSortableItem : function(sortablItem)
		{
			this.sortable.list.push(sortablItem);
		},
		removeSortableItem : function(sortablItem)
		{
			for(var i = 0; i<this.sortable.list.length; i++)
			{
				if(this.sortable.list[i] == sortablItem)
					delete this.sortable.list[i]
			}
		},
		addDragItem : function(dragList)
		{
			this.bindDragItem(dragList)
		},
		addCatcher : function(catcherItem) /* dynamically add catcher */
		{
			this.dropZoneNodeList.push(catcherItem);
			this.bindCatcher([catcherItem]);
		},
		removeCatcher : function(catcherItem)
		{
			for(var i = this.dropZoneNodeList.length-1; i>=0; i--)
			{
				if(this.dropZoneNodeList[i] == catcherItem)
					delete this.dropZoneNodeList[i]
			}
		},
		onSelect : function(event, eventObj)
		{
			eventObj.dragDrop();
			return false;
		},
		ondragStart : function(event, eventObj)
		{
			event = event || window.event;
			event.dataTransfer.setData('text', ''); /*for FF*/
			event.dataTransfer.effectAllowed = 'move';
			this.activeDragElement = eventObj;
			if(document.body.scrollHeight > document.body.clientHeight || document.body.scrollWidth > document.body.clientWidth)
				this.setAutoScroll(event.clientX, event.clientY);

			if(this.dragActiveClass)
			{
				setTimeout(
					BX.proxy(
						function(){ BX.addClass(this.activeDragElement, this.dragActiveClass) },
						this
					),
					10
				)
			}

			var params =
			{
				dragElement : this.activeDragElement,
				sortableElement : (this.sortable && this.sortable.node) ? this.sortable.node : null,
				event : event
			};

			if(this.isSortable && !this.isSortableActive)
			{
				if(!this.sortable.isNode)
				{
					this.sortable.node = this.activeDragElement;
					this.sortable.gagClass ? setTimeout( BX.proxy(function(){BX.addClass(this.sortable.node, this.sortable.gagClass)},this) , 50) : null;
				}

				this.isSortableActive = true;
				this.sortableInterval = setInterval(BX.proxy(this.sortableMove, this),100);
			}


			if(typeof(this.dragStartCallback) == 'function')
				this.dragStartCallback(params);

		},
		setAutoScroll : function(x,y)
		{
			var elemPos = BX.pos(this.activeDragElement),
				bottomPos = elemPos.bottom,
				topPos = elemPos.top,
				leftPos = elemPos.left,
				rightPos = elemPos.right,
				scrollTop = document.documentElement.scrollTop || document.body.scrollTop,
				scrollLeft = document.documentElement.scrollLeft || document.body.scrollLeft;

			this.scroll.visibleHeight = document.body.clientHeight;
			this.scroll.visibleWidth = document.body.clientWidth;

			this.scroll.edgeBottom = this.scroll.visibleHeight + scrollTop  - (bottomPos - y);
			this.scroll.edgeTop = scrollTop  + (y - topPos);
			this.scroll.edgeLeft = scrollLeft  + (x - leftPos);
			this.scroll.edgeRight = this.scroll.visibleWidth + scrollLeft - (rightPos - x);

			this.scroll.height = document.body.scrollHeight;
			this.scroll.width = document.body.scrollWidth;

			this.scroll.prevPosDragEvY = y;
			this.scroll.prevPosDragEvX = x;

			this.isAutoScroll = true;
		},
		sortableMove : function()
		{
			var elementFromPoint = document.elementFromPoint(this.dragEventX, this.dragEventY);
			if(elementFromPoint === null)
			{
				return;
			}

			var target = this.sortable.node; /*element over which draggable elem*/

			while(elementFromPoint !== null &&elementFromPoint != document.body)
			{
				for(var i = this.sortable.list.length-1; i>=0; i--)
				{
					if (elementFromPoint == this.sortable.list[i])
					{
						target = this.sortable.list[i];
						break;
					}
				}
				elementFromPoint = elementFromPoint.parentNode;
			}
			var back = false, /*move back*/
				forward = false, /* move forward*/
				nextNode = target,
				prevNode = target,
				nextSibling = nextElementSibling(target), /*last or not*/
				self = target == this.sortable.node,
				targetPos = target.getBoundingClientRect(),
				sortableElemPos = this.sortable.node.getBoundingClientRect(),
				range = 0,
				eventPos;

			while((nextNode || prevNode) && !self)
			{
				if(nextNode)
					nextNode = nextElementSibling(nextNode);

				if(prevNode)
					prevNode = previousElementSibling(prevNode);

				if(nextNode == this.sortable.node || prevNode == this.sortable.node)
				{
					back = nextNode == this.sortable.node;
					forward = prevNode == this.sortable.node;
					break
				}
			}

			if(!self && forward)
			{
				if(this.sortable.floating)
				{
					range = targetPos.right - (sortableElemPos.right - sortableElemPos.left);
					eventPos = this.dragEventX;
				}
				else
				{
					range = targetPos.bottom - (sortableElemPos.bottom - sortableElemPos.top);
					eventPos = this.dragEventY;
				}

				if(nextSibling && eventPos > range)
				{
					this.sortable.rootElem.insertBefore(this.sortable.node, target.nextSibling);
				}
				else if(!nextSibling && eventPos > range)
				{
					this.sortable.rootElem.appendChild(this.sortable.node);
				}

			}
			else if(!self && back)
			{
				if(this.sortable.floating)
				{
					range = targetPos.left + (sortableElemPos.right - sortableElemPos.left);
					eventPos = this.dragEventX;
				}
				else
				{
					range = targetPos.top + (sortableElemPos.bottom - sortableElemPos.top);
					eventPos = this.dragEventY;
				}

				if(nextSibling && eventPos < range)
				{
					this.sortable.rootElem.insertBefore(this.sortable.node, target);
				}
				else if(!nextSibling && eventPos < range)
				{
					this.sortable.rootElem.appendChild(this.sortable.node);
				}
			}
		},
		ondrag : function (event)
		{
			var ev = event || window.event;
			var isNotEventPos = (ev.clientY === 0 || ev.clientX === 0) || false;

			if(!this.isDocDragover && isNotEventPos)
			{
				BX.bind(document, 'dragover', BX.proxy(this._ondrag, this));
				this.isDocDragover = true;
			}
			else if(ev.clientY > 0 || ev.clientX > 0)
			{
				this.dragEventX = ev.clientX;
				this.dragEventY = ev.clientY;

				if(!this.isScrollInterval)
				{
					this.scrollIntervalID = setInterval(BX.proxy(this.autoScrollScroll, this),50);
					this.isScrollInterval = true;
				}
			}

			if(typeof(this.dragCallback) == 'function')
			{
				setTimeout(BX.proxy(function()
				{
					if(isNotEventPos)  /*fix for FF drag event.client */
					{
						ev.clientFFX = this.dragEventX;
						ev.clientFFY = this.dragEventY;

					}
					this.dragCallback(this.activeDragElement, this.sortable ? this.sortable.node : null, ev);

				}, this),0)

			}

		},
		_ondrag : function(event)
		{
			this.dragEventX = event.clientX;
			this.dragEventY = event.clientY;

			if(!this.isScrollInterval && this.isAutoScroll)
			{
				this.scrollIntervalID = setInterval(BX.proxy(this.autoScrollScroll, this), 50);
				this.isScrollInterval = true;
			}

		},
		autoScrollScroll : function()
		{
			var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
			var scrollLeft = document.documentElement.scrollLeft || document.body.scrollLeft;

			var moveLeft = this.scroll.prevPosDragEvX >= this.dragEventX || false;
			var moveRight = this.scroll.prevPosDragEvX <= this.dragEventX || false;
			var moveTop = this.scroll.prevPosDragEvY >= this.dragEventY || false;
			var moveBottom = this.scroll.prevPosDragEvY <= this.dragEventY || false;

			if(scrollLeft > 0 &&
				this.dragEventX <= this.scroll.edgeLeft &&
					moveLeft)
			{
				scrollLeft -= this.scroll.speedX;
				window.scrollTo(scrollLeft,scrollTop);
				if(this.scroll.speedX < 100)
					this.scroll.speedX += 1;

				this.scroll.prevPosDragEvX = this.dragEventX;
			}
			else if((scrollLeft + this.scroll.visibleWidth) < this.scroll.width &&
					this.dragEventX >= this.scroll.edgeRight &&
					moveRight)
			{
				scrollLeft += this.scroll.speedX;
				window.scrollTo(scrollLeft,scrollTop);
				if(this.scroll.speedX < 100)
					this.scroll.speedX += 1;

				this.scroll.prevPosDragEvX = this.dragEventX;
			}
			else
			{
				this.scroll.speedX = 15;
			}

			if(scrollTop > 0 && this.dragEventY <= this.scroll.edgeTop &&
				moveTop)
			{
				scrollTop -= this.scroll.speedY;
				window.scrollTo(scrollLeft,scrollTop);
				if(this.scroll.speedY < 100)
					this.scroll.speedY += 1;

				this.scroll.prevPosDragEvY = this.dragEventY;
			}
			else if((scrollTop + this.scroll.visibleHeight) < this.scroll.height &&
					this.dragEventY >= this.scroll.edgeBottom &&
					moveBottom)
			{
				scrollTop += this.scroll.speedY;
				window.scrollTo(scrollLeft,scrollTop);
				if(this.scroll.speedY < 100)
					this.scroll.speedY += 1;

				this.scroll.prevPosDragEvY = this.dragEventY;
			}
			else
			{
				this.scroll.speedY = 10;
			}

		},
		ondragOver : function(event, eventObj)
		{
			if(typeof(this.dragOverCallback) == 'function')
				this.dragOverCallback(eventObj, this.activeDragElement, event);
		},
		ondragEnter : function(event, eventObj)
		{
			if(typeof(this.dragEnterCallback) == 'function')
				this.dragEnterCallback(eventObj, this.activeDragElement, event);
		},
		ondragLeave : function(event, eventObj)
		{
			if(typeof(this.dragLeaveCallback) == 'function')
				this.dragLeaveCallback(eventObj, this.activeDragElement, event);

		},
		ondragDrop : function (event, catcher)
		{
			if(typeof(this.dragDropCallback) == 'function')
				this.dragDropCallback(catcher, this.activeDragElement, event);
		},
		ondragEnd : function (event, eventObj)
		{
			clearInterval(this.scrollIntervalID);
			this.isScrollInterval = false;

			this.isSortableActive = false;

			if(this.sortable && this.sortable.gagClass)
				BX.removeClass(this.sortable.node, this.sortable.gagClass);

			BX.unbind(document, 'dragover', BX.proxy(this._ondrag, this));
			this.isDocDragover = false;

			if(typeof(this.dragEndCallback) == 'function')
			{
				this.dragEndCallback(eventObj, this.sortable ? this.sortable.node : null, event);
			}

			if(this.dragActiveClass)
			{
				setTimeout(
					BX.proxy(
						function(){ BX.removeClass(this.activeDragElement, this.dragActiveClass) },
						this
					),
					0
				)
			}

			clearInterval(this.sortableInterval);
		}
	};
	BX.DragDrop.create = function(params)
	{
		return new BX.DragDrop(params)
	};

	function nextElementSibling(elem)
	{
		if( document.documentElement.nextElementSibling !== undefined)
		{
			return elem.nextElementSibling
		}
		else
		{
			var current = elem.nextSibling;
			while(current && current.nodeType != 1) {
			  current = current.nextSibling;
			}
			return current;
		}
	}
	function previousElementSibling(elem)
	{
		if( document.documentElement.previousElementSibling !== undefined)
		{
			return elem.previousElementSibling
		}
		else
		{
			var current = elem.previousSibling;
			while(current && current.nodeType != 1) {
			  current = current.previousSibling;
			}
			return current;
		}
	}
})(window);