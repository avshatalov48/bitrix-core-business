;(function()
{
	"use strict";

	BX.namespace("BX.Landing");
	BX.Landing.EmbedForms = function()
	{
		/**
		 * @type {BX.Landing.EmbedFormEntry[]}
		 */
		this.forms = [];
	}

	BX.Landing.EmbedForms.formsData = {};

	BX.Landing.EmbedForms.prototype = {
		add: function(formNode)
		{
			var form = new BX.Landing.EmbedFormEntry(formNode);
			this.forms.push(form);
		},

		remove: function(formNode)
		{
			var formToRemove = this.getFormByNode(formNode);
			if (formToRemove)
			{
				formToRemove.unload();

				this.forms = this.forms.filter(function(form)
				{
					return form !== formToRemove;
				});
			}
		},

		reload: function(formNode)
		{
			this.remove(formNode);
			this.add(formNode);
		},

		getFormByNode: function(formNode)
		{
			var result = null;
			this.forms.forEach(function(form)
			{
				if (formNode === form.getNode())
				{
					result = form;
					return true;
				}
			});

			return result;
		}
	}

	BX.Landing.EmbedFormEntry = function(formNode)
	{
		this.node = formNode;
		this.formObject = null;
		this.init();
	};

	BX.Landing.EmbedFormEntry.ATTR_FORM_ID = 'b24form';
	BX.Landing.EmbedFormEntry.ATTR_FORM_ID_STR = 'data-b24form';
	BX.Landing.EmbedFormEntry.ATTR_USE_STYLE = 'b24formUseStyle';
	BX.Landing.EmbedFormEntry.ATTR_USE_STYLE_STR = 'data-b24form-use-style';
	BX.Landing.EmbedFormEntry.ATTR_DESIGN = 'b24formDesign';
	BX.Landing.EmbedFormEntry.ATTR_IS_CONNECTOR = 'b24formConnector';
	BX.Landing.EmbedFormEntry.FORM_ID_MATCHER = /^#crmFormInline(\d+)$/i;
	BX.Landing.EmbedFormEntry.PRIMARY_OPACITY_MATCHER = /--primary([\da-fA-F]{2})/;

	BX.Landing.EmbedFormEntry.prototype = {
		init: function()
		{
			// todo: add loader

			// check ERRORS
			var formParams = this.node.dataset[BX.Landing.EmbedFormEntry.ATTR_FORM_ID];
			if(!formParams)
			{
				this.showNoFormsMessage();
				return;
			}
			formParams = formParams.split('|');
			if(
				formParams.length !== 1
				&& formParams.length !== 3
			)
			{
				this.showNoFormsMessage();
				return;
			}

			// LOAD by two variant - full params on with ajax load by marker
			this.useStyle = (this.node.dataset[BX.Landing.EmbedFormEntry.ATTR_USE_STYLE] === 'Y');
			let designAttr = this.node.dataset[BX.Landing.EmbedFormEntry.ATTR_DESIGN];
			this.design = designAttr
				? JSON.parse(designAttr.replaceAll('&quot;', '"'))
				: {};

			if(formParams.length === 1)
			{
				// can't ajax load params in public
				if(BX.Landing.getMode() === 'view')
				{
					return;
				}

				var idMarker = formParams[0].match(BX.Landing.EmbedFormEntry.FORM_ID_MATCHER);
				if(idMarker && idMarker.length === 2)
				{
					this.loadParamsById(idMarker[1])
						.then(this.load.bind(this))
						.catch(this.showNoFormsMessage.bind(this));
				}
				else
				{
					this.showNoFormsMessage();
				}
			}
			else if (formParams.length === 3)
			{
				this.id = formParams[0];
				this.sec = formParams[1];
				this.url = formParams[2];
				this.load();
			}
		},

		loadParamsById: function(formId)
		{
			if(!(formId in BX.Landing.EmbedForms.formsData))
			{
				BX.Landing.EmbedForms.formsData[formId] = BX.Landing.Backend.getInstance().action(
					"Form::getById",
					{formId: formId}
				);
			}
			return BX.Landing.EmbedForms.formsData[formId]
				.then(function(result) {
					if (Object.keys(result).length > 0)
					{
						this.id = result.ID;
						this.sec = result.SECURITY_CODE;
						this.url = result.URL;
					}
					else
					{
						return Promise.reject();
					}
				}.bind(this));
		},

		showNoFormsMessage: function()
		{
			if(BX.Landing.getMode() !== 'view')
			{
				var error = BX.message('LANDING_BLOCK_WEBFORM_NO_FORM');
				var desc =
					(
						this.node.dataset[BX.Landing.EmbedFormEntry.ATTR_IS_CONNECTOR]
						&& this.node.dataset[BX.Landing.EmbedFormEntry.ATTR_IS_CONNECTOR] === 'Y'
					)
						? BX.message('LANDING_BLOCK_WEBFORM_NO_FORM_BUS_NEW')
						: BX.message('LANDING_BLOCK_WEBFORM_NO_FORM_CP')
				;
				this.node.innerHTML = this.createErrorMessage(BX.message('LANDING_BLOCK_WEBFORM_NO_FORM'), desc);
				BX.onCustomEvent('BX.Landing.BlockAssets.Form:addScript', [{
					node: this.node,
					error: error
				}]);
			}
		},

		createErrorMessage: function (title, message)
		{
			// show alert only in edit mode
			if (BX.Landing.getMode() === "view")
			{
				return;
			}

			if (title === undefined || title === null || !title)
			{
				title = BX.message('LANDING_BLOCK_WEBFORM_ERROR');
			}

			if (message === undefined || message === null || !message)
			{
				message = BX.message('LANDING_BLOCK_WEBFORM_CONNECT_SUPPORT');
			}

			var alertHtml = '<h2 class="u-form-alert-title"><i class="fa fa-exclamation-triangle g-mr-15"></i>'
				+ title
				+ '</h2><hr class="u-form-alert-divider">'
				+ '<p class="u-form-alert-text">' + message + '</p>';

			return '<div class="u-form-alert">' + alertHtml + '</div>';
		},

		load: function()
		{
			BX.onCustomEvent('BX.Landing.BlockAssets.Form:addScript', [{
				success: true,
				node: this.node,
				script: this.url
			}]);
			this.node.innerHTML = '';	//clear "no form" alert
			this.loadScript();
		},

		unload: function()
		{
			if (typeof b24form === 'undefined' || !b24form.App || !this.formObject)
			{
				return;
			}

			b24form.App.remove(this.formObject.getId());
		},

		getNode: function()
		{
			return this.node;
		},

		setFormObject: function(object)
		{
			this.formObject = object;
		},

		loadScript: function()
		{
			const cacheTime = (BX.Landing.getMode() === "edit")
				? Date.now() / 1000 | 0
				: Date.now() / 60000 | 0;
			const script = document.createElement('script');
			script.setAttribute('data-b24-form', 'inline/' + this.id + '/' + this.sec);
			script.setAttribute('data-skip-moving', 'true');
			script.innerText =
				"(function(w,d,u){" +
				"var s=d.createElement('script');s.async=true;s.src=u+'?'+(" + cacheTime + ");" +
				"var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);" +
				"})(window,document,'" + this.url + "')"
			;
			this.node.appendChild(script);
		},

		onFormLoad: function(formObject)
		{
			this.setFormObject(formObject);
			if (this.useStyle)
			{
				this.formObject.adjust(this.getParams());
			}
		},

		getParams: function()
		{
			const params = {
				design: {
					shadow: false,
					font: 'var(--landing-font-family)'
				}
			};

			const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim();
			let design = this.design;
			for (const property in design.color)
			{
				if (
					design.color.hasOwnProperty(property)
					&& (
						design.color[property] === '--primary'
						|| design.color[property].match(BX.Landing.EmbedFormEntry.PRIMARY_OPACITY_MATCHER) !== null
					)
				)
				{
					design.color[property] = design.color[property].replace('--primary', primaryColor);
				}
			}
			if (design.color.background !== undefined)
			{
				design.color.popupBackground =
					(design.color.background.length === 9)
						? design.color.background.slice(0,7) + 'FF'
						: design.color.background;
			}

			params.design = Object.assign(params.design, design);
			return params;
		}
	};

	const embedForms = new BX.Landing.EmbedForms();

	window.addEventListener('b24:form:init:before', function(event)
	{
		if (BX.Landing.getMode() === "edit")
		{
			// tell form that it's in edit mode
			event.detail.data.editMode = true;
		}
	});

	window.addEventListener('b24:form:init', function(event)
	{
		const form = embedForms.getFormByNode(event.detail.object.node.parentElement)
		if (!!form && event.detail.object)
		{
			form.onFormLoad(event.detail.object);
		}
	});

	BX.addCustomEvent("BX.Landing.Block:init", function(event)
	{
		const formNode = event.block.querySelector(event.makeRelativeSelector(".bitrix24forms"));
		if (formNode)
		{
			embedForms.add(formNode);
		}
	});

	BX.addCustomEvent("BX.Landing.Block:remove", function(event)
	{
		const formNode = event.block.querySelector(event.makeRelativeSelector(".bitrix24forms"));
		if (formNode)
		{
			embedForms.remove(formNode);
		}
	});

	BX.addCustomEvent("BX.Landing.Block:Node:updateAttr", function(event)
	{
		const formNode = event.block.querySelector(event.makeRelativeSelector(".bitrix24forms"));
		if (formNode)
		{
			embedForms.reload(formNode);
		}
	});
})();