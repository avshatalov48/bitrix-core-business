;(function() {

	var namespace = BX.namespace('BX.UI.Feedback');

	var list = [];
	var loadedList = {};
	var opened = false;

	namespace.Form = function(options)
	{
		this.init(options);
		list.push(this);
	};
	namespace.Form.getList = function ()
	{
		return list;
	};
	namespace.Form.getById = function (id)
	{
		return list.filter(function (item) {
			return item.id === id;
		})[0] || null;
	};
	namespace.Form.open = function (map)
	{
		if (opened)
		{
			return;
		}

		if (typeof loadedList[map.id] !== 'undefined')
		{
			loadedList[map.id].openPanel();
			return;
		}

		loadedList[map.id] = new namespace.Form({map: map});
		loadedList[map.id].openPanel();
	};
	namespace.Form.prototype = {
		init: function(options)
		{
			if (typeof options.map !== 'undefined')
			{
				this.map = options.map;
				return
			}

			this.id = options.id;
			this.portal = options.portal;
			this.presets = options.presets || {};
			this.form = options.form || {};
			this.title = options.title || '';

			if (options.button)
			{
				this.button = BX(options.button);
				BX.bind(this.button, 'click', this.openPanel.bind(this));
			}
		},
		appendPresets: function(presets)
		{
			for (var key in presets)
			{
				if (!presets.hasOwnProperty(key))
				{
					continue;
				}

				this.presets[key] = presets[key];
			}
		},
		openPanel: function()
		{
			opened = true;

			BX.SidePanel.Instance.open("ui:feedback-form-" + this.id, {
				cacheable: false,
				contentCallback: function() {
					var promise = new BX.Promise();
					promise.fulfill();
					return promise;
				},
				animationDuration: 200,
				events: {
					onLoad: this.checkSidePanelLoad.bind(this),
					onBeforeCloseComplete: this.checkSidePanelClosed.bind(this)
				},
				width: 600
			});
		},
		checkSidePanelClosed: function(event)
		{
			opened = false;
		},
		checkSidePanelLoad: function(event)
		{
			if (typeof this.map !== 'undefined' && typeof this.cached === 'undefined')
			{
				BX.ajax.runAction("ui.feedback.loadData" , {
					json: {
						title: this.map.title || null,
						id: this.map.id || null,
						presets: this.map.presets || null,
						portalUri: this.map.portalUri || null,
						forms: this.map.forms || null,
						defaultForm: this.map.defaultForm || null,
					},
				}).then(
					function(response)
					{
						var params = response.data.params;
						this.id = params.id;
						this.title = params.title;
						this.form = params.form;
						this.presets = params.presets;
						this.portal = params.portal;
						this.cached = true;

						this.onSidePanelLoad(event);
					}.bind(this)
				);
				return;
			}

			this.onSidePanelLoad(event);
		},
		onSidePanelLoad: function(event)
		{
			this.formNode = document.createElement('div');
			var titleNode = document.createElement('div');
			titleNode.style = 'margin-bottom: 25px; font: 26px/26px "OpenSans-Light", Helvetica, Arial, sans-serif;';
			titleNode.textContent = this.title;
			var slider = event.getSlider();

			var containerNode = document.createElement('div');
			containerNode.style = 'padding: 20px; overflow-y: auto;';
			containerNode.appendChild(titleNode);
			containerNode.appendChild(this.formNode);

			if (!slider)
			{
				return;
			}

			slider.layout.content.appendChild(containerNode);
			setTimeout(function () {
				slider.showLoader();
			}, 0);

			this.loadForm(this.checkLoader.bind(this, slider));
		},

		checkLoader: function (slider) {
			setTimeout(function () {
				slider.closeLoader();
			},100);
		},
		loadForm: function(callback)
		{
			var form = this.form;
			if (!form || !form.id || !form.lang || !form.sec)
			{
				return;
			}

			if (form.presets)
			{
				this.appendPresets(form.presets);
			}

			(function(w,d,u,b){
				w['Bitrix24FormObject']=b;w[b] = w[b] || function(){arguments[0].ref=u;
					(w[b].forms=w[b].forms||[]).push(arguments[0])}; if(w[b]['forms']) return;
				var s=d.createElement('script');
				var r=1*new Date(); s.async=1;s.src=u+'?'+r;
				var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
			})(top,top.document, this.portal + '/bitrix/js/crm/form_loader.js','b24form');

			top.b24form({
				"id": form.id,
				"lang": form.lang,
				"sec": form.sec,
				"type": "inline",
				"node": this.formNode,
				"presets": this.presets,
				"handlers": {
					"load": callback
				}
			});
		}
	};

})();