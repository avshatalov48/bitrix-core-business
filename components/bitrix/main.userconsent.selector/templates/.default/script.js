var MainUserConsentSelectorManager = function(params)
{
	this.selectors = [];
	this.init = function (params)
	{
		this.actionRequestUrl = params.actionRequestUrl;
		this.template = params.template ?? null;
		if (this.template)
		{
			this.template = BX.Tag.render([this.template]);
			var selector = this.template.querySelector('select[data-bx-selector]');
			this.selectors.push(selector);
		}
		this.inputName = params.inputName;
		this.initSlider();

		var contexts = document.querySelectorAll('[data-bx-user-consent-selector]');
		contexts = BX.convert.nodeListToArray(contexts);
		contexts.forEach(this.initByContext, this);
	};

	this.initByBlockContext = function(block)
	{
		var selector = block.querySelector('select[data-bx-selector]');
		var linkEdit = block.querySelector('a[data-bx-link-edit]');
		var linkView = block.querySelector('a[data-bx-link-view]');

		if (!selector || !linkEdit || !linkView)
		{
			return;
		}

		var deleteButton = block.querySelector('[data-bx-delete]');
		BX.bind(deleteButton, 'click', this.removeBlock.bind(this, block));

		this.selectors.push(selector);
		BX.bind(selector, 'change', this.onChange.bind(this, selector, linkEdit, linkView));
		this.onChange(selector, linkEdit, linkView);
	};

	this.initByContext = function(context)
	{
		var blocks = context.querySelectorAll('[data-bx-user-consent-selector-block]');
		blocks = BX.convert.nodeListToArray(blocks);
		blocks.forEach(this.initByBlockContext, this);

		this.initSliderButtons(context);

		var linkAdd = context.querySelector('a[data-bx-link-add]');
		if (linkAdd)
		{
			BX.bind(linkAdd, 'click', this.onAddClick.bind(this, context));
		}
	};

	this.onAddClick = function(context)
	{
		var blocksContainer = context.querySelector('[data-bx-user-consent-selector-blocks-container]');
		if (blocksContainer)
		{
			var newBlock = this.template.cloneNode(true);
			var blocks = blocksContainer.querySelectorAll('[data-bx-user-consent-selector-block]');
			var newBlockIndex = blocks.length;

			this.updateBlockInputNames(newBlock, newBlockIndex);
			blocksContainer.appendChild(newBlock);
			this.initByBlockContext(newBlock);
			this.initSliderButtons(newBlock);
			blocks = blocksContainer.querySelectorAll('[data-bx-user-consent-selector-block]');
			if (blocks.length === 2)
			{
				var deleteButton = blocks[0].querySelector('[data-bx-delete]');
				deleteButton.style.display = 'inline';
			}
		}
	};

	this.removeBlock = function(removedBlock)
	{
		var blocksContainer = removedBlock.parentNode;
		var blocks = blocksContainer.querySelectorAll('[data-bx-user-consent-selector-block]');
		if (blocks.length > 1)
		{
			removedBlock.remove();
		}

		blocks = blocksContainer.querySelectorAll('[data-bx-user-consent-selector-block]');
		if (blocks.length > 1)
		{
			blocks.forEach((block, index) => {
				this.updateBlockInputNames(block, index);
			});
		}
		else
		{
			var deleteButton = blocks[0].querySelector('[data-bx-delete]');
			deleteButton.style.display = 'none';
		}
	};

	this.updateBlockInputNames = function(block, index)
	{
		var selector = block.querySelector('[data-bx-selector]');
		selector.name = this.inputName + '[' + index + ']' + '[ID]';

		var defaultInputs = block.querySelectorAll('[data-bx-default-input]');
		defaultInputs.forEach((defaultInput) => {
			defaultInput.name = this.inputName + '[' + index + ']' + '[CHECKED]';
		});

		var requiredInputs = block.querySelectorAll('[data-bx-required-input]');
		requiredInputs.forEach((requiredInput) => {
			requiredInput.name = this.inputName + '[' + index + ']' + '[REQUIRED]';
		});
	};

	this.onChange = function(selector, linkEdit, linkView)
	{
		linkEdit.style.display = selector.value ? '' : 'none';
		linkView.style.display = selector.value ? '' : 'none';

		this.fillHrefByTemplate(linkEdit, selector.value);
		this.fillHrefByTemplate(linkView, selector.value);
	};

	this.fillHrefByTemplate = function(a, value)
	{
		var path = a.getAttribute('data-bx-link-tmpl');
		if (!path)
		{
			return;
		}
		a.href = path.replace(new RegExp('#id#', 'g'), value);
	};

	this.fillDropDownControl = function(node, items, skipExistedFirstElement)
	{
		items = items || [];
		var firstChildElement = node.children[0];
		node.innerHTML = '';

		if (skipExistedFirstElement && firstChildElement)
		{
			node.appendChild(firstChildElement);
		}

		items.forEach(function(item){
			if(!item || !item.caption)
			{
				return;
			}

			var option = document.createElement('option');
			option.value = item.value;
			option.selected = !!item.selected;
			option.innerText = item.caption;
			node.appendChild(option);
		});
	};

	this.initSliderButtons = function(context)
	{
		var list = context.querySelectorAll('[data-bx-slider-href]');
		list = BX.convert.nodeListToArray(list);
		list.forEach(this.slider.bindOpen, this.slider);
	};

	this.initSlider = function()
	{
		this.slider.caller = this;
		top.BX.addCustomEvent(top, 'main-user-consent-to-list', function () {
			top.BX.SidePanel.Instance.close();
		});
	};

	this.onSliderClose = function()
	{
		this.sendActionRequest('getAgreements', {}, function (data) {
			if (!data.list)
			{
				return;
			}
			this.selectors.forEach(function (selectorNode) {
				var selectedValue = selectorNode.value;
				if (!selectedValue)
				{
					selectedValue = data.list[0].ID;
				}
				var items = data.list.map(function (item) {
					return {
						caption: item.NAME,
						value: item.ID,
						selected: (item.ID || '').toString() === selectedValue
					};
				});
				this.fillDropDownControl(selectorNode, items, true);
				BX.fireEvent(selectorNode, 'change');
			}, this);
		});
	};

	this.slider = {
		caller: null,
		init: function (params)
		{
			top.BX.SidePanel.Instance.bindAnchors({
				rules: [
					{
						condition: params.condition,
						loader: params.loader,
						stopParameters: []
					}
				]
			});
		},
		onSaved: function ()
		{
			this.onClose();
			top.BX.SidePanel.Instance.close();
		},
		onClose: function ()
		{
			if (this.caller)
			{
				this.caller.onSliderClose();
			}
		},
		bindOpen: function (element)
		{
			BX.bind(element, 'click', this.openHref.bind(this, element));
		},
		openHref: function (a, e)
		{
			e.preventDefault();
			this.open(a.getAttribute('href'), a.getAttribute('data-bx-slider-reload'));
		},
		open: function (url, reloadAfterClosing)
		{
			top.BX.SidePanel.Instance.open(url, {
				cacheable: false,
				events: {
					//onClose: reloadAfterClosing ? this.onClose.bind(this) : null
				}
			});

			if (reloadAfterClosing)
			{
				top.BX.addCustomEvent(top, 'main-user-consent-saved', this.onSaved.bind(this));
			}
		}
	};

	this.sendActionRequest = function (action, sendData, callbackSuccess, callbackFailure)
	{
		callbackSuccess = callbackSuccess || null;
		callbackFailure = callbackFailure || null;

		sendData.action = action;
		sendData.sessid = BX.bitrix_sessid();
		sendData.action = action;

		BX.ajax({
			url: this.actionRequestUrl,
			method: 'POST',
			data: sendData,
			timeout: 10,
			dataType: 'json',
			processData: true,
			onsuccess: BX.proxy(function(data){
				data = data || {};
				if(data.error)
				{
					callbackFailure.apply(this, [data]);
				}
				else if(callbackSuccess)
				{
					callbackSuccess.apply(this, [data]);
				}
			}, this),
			onfailure: BX.proxy(function(){
				var data = {'error': true, 'text': ''};
				if (callbackFailure)
				{
					callbackFailure.apply(this, [data]);
				}
			}, this)
		});
	};

	this.init(params);
};