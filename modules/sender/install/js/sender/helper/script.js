;(function (window)
{

	BX.namespace('BX.Sender');
	if (BX.Sender.Helper)
	{
		return;
	}

	var Helper = {
		hint: {
			attributeName: 'data-hint',
			attributeInitName: 'data-hint-init',
			popup: null,
			content: null,
			init: function (context)
			{
				context = context || document.body;
				var nodes = context.querySelectorAll('[' + this.attributeName + ']');
				nodes = BX.convert.nodeListToArray(nodes);
				nodes.forEach(this.initNode, this);
			},
			initNode: function (node)
			{
				if (node.getAttribute(this.attributeInitName))
				{
					return;
				}

				node.setAttribute(this.attributeInitName, 'y');

				var text = node.getAttribute(this.attributeName);
				if (text === '' || text === null)
				{
					return;
				}

				BX.addClass(node, 'sender-hint');
				node.innerHTML = '<div class="sender-hint-icon"></div>';

				BX.bind(node, 'mouseenter', this.show.bind(this, node, text));
				BX.bind(node, 'mouseleave', this.hide.bind(this, node));
			},
			show: function (node, text)
			{
				if (this.content === null)
				{
					this.content= document.createElement('div');
					this.content.style.margin = '7px';
					this.content.style.maxWidth = '400px';
					this.popup = new BX.PopupWindow(
						'sender-helper-hint',
						node,
						{
							'zIndex': 1000,
							'darkMode': true,
							'content': this.content
						}
					);
				}

				this.content.innerHTML = text;
				this.popup.setBindElement(node);
				this.popup.show();
			},
			hide: function ()
			{
				if (!this.popup)
				{
					return;
				}

				this.popup.close();
			}
		},
		tag: {
			attributeName: 'data-tag',
			attributeInitName: 'data-tag-init',
			popup: null,
			init: function (context, target)
			{
				if (!context || !target)
				{
					return;
				}

				context = context || document.body;
				var nodes = context.querySelectorAll('[' + this.attributeName + ']');
				nodes = BX.convert.nodeListToArray(nodes);
				nodes.forEach(this.initNode.bind(this, target));
			},
			initNode: function (target, node)
			{
				if (node.getAttribute(this.attributeInitName))
				{
					return;
				}

				node.setAttribute(this.attributeInitName, 'y');

				var items = node.getAttribute(this.attributeName);
				if (!items)
				{
					return;
				}

				BX.addClass(node, 'sender-tag');
				node.innerHTML = '<span class="sender-tag-icon"></span>';

				try
				{
					items = JSON.parse(items);
				}
				catch ($e)
				{
					items = null;
				}

				if (!items)
				{
					return;
				}

				new BX.Sender.PersonalizationSelector({
					button: node,
					targetInput: target,
					fields: items
				});
			},
			onClick: function (target, node, item)
			{
				target.value = target.value + item.id;
				this.hide();
			},
			show: function (target, node, items)
			{
				if (!this.popup)
				{
					this.popup = new BX.PopupMenuWindow(
						'sender-helper-hint-tag-' + (target.name || target.id),
						node,
						items.map(function (childItem) {
							var _this = this;
							if(typeof childItem.items !== 'undefined' && childItem.items.length !== 0)
							{
								childItem.items.map(function(item) {
									item.onclick = _this.onClick.bind(_this, target, node, item);
									return item;
								})
							}
							else
							{
								childItem.onclick = this.onClick.bind(this, target, node, childItem);
							}

							return childItem;
						}, this),
						{
							autoHide: true,
							autoClose: true
						},
						{
							events: {
								onclick: function () {}
							}
						}
					);
				}

				this.popup.bindElement = node;
				this.popup.show();
			},
			hide: function ()
			{
				if (!this.popup)
				{
					return;
				}

				this.popup.close();
			}
		},
		titleEditor:
		{
			isInit: false,
			init: function (params)
			{
				// init nodes
				this.dataNode = params.dataNode;
				this.titleNode = BX('pagetitle');
				this.inputNode = BX('pagetitle_input');
				this.buttonNode = BX('pagetitle_edit');

				this.initialTitle = this.titleNode.textContent;
				this.defaultTitle = params.defaultTitle;

				// init bindings
				BX.bind(this.dataNode, 'bxchange', this.onDataNodeChange.bind(this));
				BX.bind(this.buttonNode, 'click', this.startEdit.bind(this));

				BX.bind(this.inputNode, 'keyup', this.onKeyUp.bind(this));
				BX.bind(this.inputNode, 'blur', this.endEdit.bind(this));

				this.isInit = true;

				// init state
				if (!params.disabled)
				{
					this.enable();
				}

				if (!this.dataNode.value)
				{
					this.dataNode.value = this.defaultTitle;
				}
			},
			enable: function (isDisable)
			{
				isDisable = isDisable || false;
				if (!this.isInit)
				{
					return;
				}

				Helper.changeDisplay(this.buttonNode, !isDisable);
				this.titleNode.textContent = !isDisable
					?
					this.dataNode.value ? this.dataNode.value : this.defaultTitle
					:
					this.initialTitle;
			},
			disable: function ()
			{
				this.enable(true);
			},
			onDataNodeChange: function ()
			{
				this.titleNode.textContent = this.dataNode.value;
			},
			onKeyUp: function (event)
			{
				event = event || window.event;
				if ((event.keyCode === 0xA)||(event.keyCode === 0xD))
				{
					this.endEdit();
					event.preventDefault();
					return false;
				}
			},
			getTitle: function ()
			{
				var title = this.dataNode.value;
				if (!title)
				{
					title = this.titleNode.textContent;
				}

				return title;
			},
			startEdit: function ()
			{
				this.inputNode.value = this.getTitle();

				Helper.changeDisplay(this.titleNode, false);
				Helper.changeDisplay(this.buttonNode, false);
				Helper.changeDisplay(this.inputNode, true);

				this.inputNode.focus();
			},
			endEdit: function ()
			{
				this.dataNode.value = this.inputNode.value;
				this.titleNode.textContent = this.inputNode.value;

				Helper.changeDisplay(this.inputNode, false);
				Helper.changeDisplay(this.buttonNode, true);
				Helper.changeDisplay(this.titleNode, true);
			}
		},
		getObjectByKey:  function (list, key, value)
		{
			var filtered = list.filter(function (item) {
				return (item.hasOwnProperty(key) && item[key] === value);
			});
			return filtered.length > 0 ? filtered[0] : null;
		},
		changeClass: function (node, className, isAdd)
		{
			if (!node)
			{
				return;
			}

			isAdd ? BX.addClass(node, className) : BX.removeClass(node, className);
		},
		replace: function (text, data, isDataSafe)
		{
			data = data || {};
			isDataSafe = isDataSafe || false;

			if (!text)
			{
				return '';
			}

			for (var key in data)
			{
				if (!data.hasOwnProperty(key))
				{
					continue;
				}

				var value = data[key];
				value = value || '';
				if (!isDataSafe && value)
				{
					value = BX.util.htmlspecialchars(value);
				}
				text = text.replace(new RegExp('%' + key + '%', 'g'), value);
			}
			return text;
		},
		getNode: function (role, context)
		{
			var nodes = this.getNodes(role, context);
			return nodes.length > 0 ? nodes[0] : null;
		},
		getNodes: function (role, context)
		{
			if (!BX.type.isDomNode(context))
			{
				return [];
			}
			return BX.convert.nodeListToArray(context.querySelectorAll('[data-role="' + role + '"]'));
		},
		safe: function (text)
		{
			return BX.util.htmlspecialchars(text);
		},
		getTemplate: function (templateNode, replaceData, isDataSafe)
		{
			if (!templateNode)
			{
				return null;
			}

			return Helper.replace(templateNode.innerHTML, replaceData, isDataSafe);
		},
		/*
		getTemplatedNodes: function (templateNode, replaceDataList, isDataSafe)
		{
			return replaceDataList.map(function (replaceData) {
				return this.getTemplatedNode(templateNode, replaceData, isDataSafe);
			}, this);
		},
		*/
		getTemplatedNode: function (template, replaceData, isDataSafe)
		{
			if (!template)
			{
				return null;
			}

			if (BX.type.isDomNode(template))
			{
				template = this.getTemplate(template, replaceData, isDataSafe);
			}

			if (!template)
			{
				return null;
			}

			var node = document.createElement('div');
			node.innerHTML = template;

			return node.children.length > 0 ? node.children[0] : null;
		},
		handleKeyEnter: function (inputNode, callback)
		{
			if (!callback)
			{
				return;
			}

			var handler = function (event)
			{
				event = event || window.event;
				if ((event.keyCode === 0xA)||(event.keyCode === 0xD))
				{
					event.preventDefault();
					event.stopPropagation();
					callback();
					return false;
				}
			};
			BX.bind(inputNode, 'keyup', handler);
		},
		changeDisplay: function (node, isShow)
		{
			return this.display.change(node, isShow, true);
		},
		animate: {
			numbers: function (node, value)
			{
				value = BX.type.isString(value) ? value.replace(/[^0-9]/, '') : value;
				var initialValue = parseInt(node.tagName === 'INPUT' ? node.value : node.textContent.replace(/[^0-9]/, ''));
				if (isNaN(initialValue))
				{
					initialValue = 0;
				}

				var easing = new BX.easing({
					duration : 500,
					start : {num: initialValue},
					finish : {num: value},
					transition : BX.easing.transitions.quart,
					step : function(state)
					{
						var num = BX.util.number_format(state.num, 0, '.', ' ');
						if (node.tagName === 'INPUT')
						{
							node.value = num;
						}
						else
						{
							node.textContent = num;
						}

					},
					complete : function()
					{
					}
				});
				easing.animate();
			}
		},
		display: {
			animateShowing: function (node, useOpacity)
			{
				useOpacity = useOpacity || false;

				var easing = new BX.easing({
					duration : 500,
					start : { height: 0, opacity: useOpacity ? 30 : 100 },
					finish : { height: 100000, opacity: 100 },
					transition : BX.easing.transitions.quart,
					step : function(state)
					{
						node.style.opacity = state.opacity/100;
						node.style.maxHeight = state.height + "px";
						node.style.height = null;
						node.style.display = "";

						var val = BX.pos(node);
						if (val.height < state.height)
						{
							easing.stop(true);
						}
					},
					complete : function()
					{
						//node.style.maxHeight = null;
					}
				});
				easing.animate();
			},
			animateHiding: function (node, useOpacity, callback)
			{
				useOpacity = useOpacity || false;

				var val = BX.pos(node);
				var easing = new BX.easing({
					duration : 300,
					start : { height: val.height, opacity: 80 },
					finish : { height: 0, opacity: useOpacity ? 30 : 100 },
					transition : BX.easing.transitions.quart,
					step : function(state)
					{
						node.style.maxHeight = state.height + "px";
						node.style.height = null;
						node.style.opacity = state.opacity/100;
					},
					complete : function()
					{
						node.style.display = "none";
						node.style.opacity = 0;
						if (callback)
						{
							callback.apply(this);
						}
					}
				});
				easing.animate();
			},
			change: function (node, isShow, isSimple)
			{
				isSimple = BX.type.isBoolean(isSimple) ? isSimple : false;

				if (!node)
				{
					return;
				}

				if (isSimple)
				{
					node.style.display = isShow ? '' : 'none';
					return;
				}

				if (isShow === this.isShowed(node))
				{
					return;
				}

				var useOpacity = true;
				if (!isShow)
				{
					this.animateHiding(node, useOpacity);
				}
				else
				{
					this.animateShowing(node, useOpacity);
				}
			},
			toggle: function (node, isSimple)
			{
				if (!node)
				{
					return;
				}

				this.change(node, !this.isShowed(node), isSimple);
			},
			isShowed: function (node)
			{
				if (!node)
				{
					return false;
				}
				return !(node.style.display === 'none');
			}
		}
	};

	BX.Sender.Helper = Helper;

})(window);