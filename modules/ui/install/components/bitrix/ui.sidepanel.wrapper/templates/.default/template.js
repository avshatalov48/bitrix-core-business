;(function ()
{

	BX.namespace('BX.UI');
	if (BX.UI.SidePanelWrapper)
	{
		return;
	}

	function Wrapper (parameters)
	{
	}
	Wrapper.prototype.init = function (parameters)
	{
		this.container = BX(parameters.containerId);
		this.isCloseAfterSave = parameters.isCloseAfterSave || false;
		this.isReloadGridAfterSave = parameters.isReloadGridAfterSave || false;
		this.isReloadPageAfterSave = parameters.isReloadPageAfterSave || false;
		this.useLinkTargetsReplacing = parameters.useLinkTargetsReplacing || false;
		this.notification = parameters.notification || {};

		this.initEditableTitle(parameters);

		if (this.hasSidePanel())
		{
			parent.BX.addCustomEvent(
				parent.BX.SidePanel.Instance.getTopSlider(),
				"SidePanel.Slider:onReload",
				this.onSidePanelReload.bind(this)
			);
		}

		if (this.useLinkTargetsReplacing)
		{
			this.initLinkTargetsReplacing();
		}

		this.isNotificationWillShowed();
	};
	Wrapper.prototype.initEditableTitle = function (parameters)
	{
		if (!parameters.title || !parameters.title.selector || !parameters.title.defaultTitle)
		{
			return;
		}

		var dataContainer = this.container.querySelector(parameters.title.selector);
		if (!dataContainer)
		{
			return;
		}

		var titleDataNode = dataContainer.querySelector('input[type="text"]');
		if (!titleDataNode)
		{
			return;
		}

		dataContainer.style.display = 'none';
		TitleEditor.init({
			dataContainer: dataContainer,
			dataNode: titleDataNode,
			defaultTitle: parameters.title.defaultTitle
		});
	};
	Wrapper.prototype.initLinkTargetsReplacing = function ()
	{
		this.replaceLinkTargets();
		if (!window.MutationObserver)
		{
			return;
		}

		var observer = new MutationObserver(this.domMutationHandler.bind(this));
		observer.observe(this.container, {childList: true, subtree: true});
	};
	Wrapper.prototype.domMutationHandler = function (mutations)
	{
		mutations.forEach(function (mutation) {
			for (var i = 0; i < mutation.addedNodes.length; ++i)
			{
				var node = mutation.addedNodes.item(i);
				if (!node)
				{
					continue;
				}

				this.replaceLinkTargets(node);
			}
		}, this);
	};
	Wrapper.prototype.replaceLinkTargets = function (context)
	{
		if (!context)
		{
			context = document.body;
		}

		var list = [];
		if (context.tagName === 'A')
		{
			list = [context];
		}
		else if (context.nodeName !== '#text')
		{
			list = BX.convert.nodeListToArray(context.querySelectorAll('a'))
		}

		if (list.length === 0)
		{
			return;
		}

		BX.convert.nodeListToArray(list).filter(function (a) {
			return !a.target;
		}).forEach(function (a) {
			a.target = '_top';
		});
	};
	Wrapper.prototype.hasSidePanel = function ()
	{
		return parent && parent.BX && parent.BX.SidePanel;
	};
	Wrapper.prototype.onSidePanelReload = function ()
	{
		if (!this.hasSidePanel())
		{
			return;
		}

		if (this.isReloadGridAfterSave)
		{
			this.reloadGridOnParentPage();
		}

		if (this.isCloseAfterSave)
		{
			var handler;
			if (this.isNotificationWillShowed())
			{
				handler = this.showNotification.bind(this);
			}
			else if (this.isReloadPageAfterSave)
			{
				handler = function () {
					parent.window.location.reload();
				};
			}

			parent.BX.SidePanel.Instance.close(false, handler);
		}
	};
	Wrapper.prototype.isNotificationWillShowed = function ()
	{
		if (!this.notification.content || !parent.BX)
		{
			return false;
		}

		if (!this.hasSidePanel())
		{
			return false;
		}

		if (this.sidePanelParam() === true)
		{
			return true;
		}

		var isShow = window.location.search.substr(1)
			.split('&')
			.filter(function(item) {
				return item === 'notifyAfterSave=N';
			})
			.length === 0;

		this.sidePanelParam(isShow);
		return isShow;
	};
	Wrapper.prototype.sidePanelParam = function (val)
	{
		var slider = parent.BX.SidePanel.Instance.getTopSlider();
		if (!slider)
		{
			return null;
		}

		var dictionary = slider.getData();
		if (typeof (val) === "undefined")
		{
			return dictionary.get('isNotificationWillShowed');
		}
		else
		{
			return dictionary.set('isNotificationWillShowed', val);
		}
	};
	Wrapper.prototype.showNotification = function ()
	{
		if (!parent.BX.UI || !parent.BX.UI.Notification)
		{
			return;
		}

		parent.BX.UI.Notification.Center.notify(this.notification);
		this.sidePanelParam(null);
	};
	Wrapper.prototype.reloadGridOnParentPage = function ()
	{
		var id = BX.type.isString(this.isReloadGridAfterSave) ? this.isReloadGridAfterSave : null;
		if (!parent.BX.Main || !parent.BX.Main.gridManager)
		{
			return;
		}

		if (!id && parent.BX.Main.gridManager.data)
		{
			var grids = parent.BX.Main.gridManager.data;
			id = grids.length > 0 ? grids[0].id : null;
		}

		if(!id)
		{
			return;
		}

		var grid = parent.BX.Main.gridManager.getById(id);
		if (!grid)
		{
			return;
		}
		grid.instance.reload();
	};

	var TitleEditor = {
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

			this.changeDisplay(this.buttonNode, !isDisable);
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

			this.changeDisplay(this.titleNode, false);
			this.changeDisplay(this.buttonNode, false);
			this.changeDisplay(this.inputNode, true);

			this.inputNode.focus();
		},
		endEdit: function ()
		{
			this.dataNode.value = this.inputNode.value;
			this.titleNode.textContent = this.inputNode.value;

			this.changeDisplay(this.inputNode, false);
			this.changeDisplay(this.buttonNode, true);
			this.changeDisplay(this.titleNode, true);
		},
		changeDisplay: function (node, isShow)
		{
			return node.style.display = isShow ? '' : 'none';
		}
	};

	BX.UI.SidePanelWrapper = new Wrapper;

})();