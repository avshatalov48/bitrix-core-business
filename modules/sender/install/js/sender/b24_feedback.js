;(function () {

	BX.namespace('BX.Sender');

	BX.Sender.B24Feedback = {
		wasInit: false,
		config: null,
		init: function (params)
		{
			this.config = params || {};
			var button = BX('SENDER_BUTTON_FEEDBACK');
			if (button)
			{
				BX.bind(button, 'click', this.show.bind(this));
			}

			this.wasInit = true;
		},
		initForm: function ()
		{
			var senderPage = window.location.href.match(/\/marketing\/([\w]+)\/([\w]+)\//);
			if (senderPage)
			{
				senderPage = senderPage[1] + '-' + senderPage[2];
			}

			(function(w,d,u,b){
				w['Bitrix24FormObject']=b;w[b] = w[b] || function(){arguments[0].ref=u;
				(w[b].forms=w[b].forms||[]).push(arguments[0])}; if(w[b]['forms']) return;
				var s=d.createElement('script');
				var r=1*new Date(); s.async=1;s.src=u+'?'+r;
				var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
			})(window,document,'https://landing.bitrix24.ru/bitrix/js/crm/form_loader.js','b24form');

			var id, lang, sec;
			switch (this.config.b24_zone)
			{
				case 'ru':
				case 'ua':
				case 'kz':
				case 'by':
					id = '20';
					lang = 'ru';
					sec = 'whjggl';
					break;

				case 'es':
					id = '26';
					lang = 'la';
					sec = '5i211d';
					break;

				case 'de':
					id = '24';
					lang = 'de';
					sec = 'ls8jym';
					break;

				case 'com.br':
					id = '28';
					lang = 'br';
					sec = 'lqo2cl';
					break;

				default:
					id = '22';
					lang = 'en';
					sec = 'iv17oh';
					break;
			}

			if (!id)
			{
				return;
			}

			window.b24form({
				"id":id,
				"lang":lang,
				"sec":sec,
				"type":"inline",
				"node": this.dataContainer,
				"presets": {
					"b24_plan": this.config.b24_plan,
					"sender_page": senderPage
				}
			});
		},
		show: function ()
		{
			if (!this.wasInit)
			{
				this.init();
			}

			if (!this.dataContainer)
			{
				this.initPopup();
				this.initForm();
			}

			if (this.popup.isShown())
			{
				return;
			}

			this.popup.show();

		},
		initPopup: function ()
		{
			this.dataContainer = document.createElement('DIV');
			this.dataContainer.style = 'width: 600px; height: 673px; overflow-y: auto;';

			this.popup = BX.PopupWindowManager.create(
				'sender-b24-feedback-popup',
				this.button,
				{
					content: this.dataContainer,
					autoHide: true,
					lightShadow: false,
					overlay: {
						opacity: 500,
						backgroundColor: 'black'
					},
					closeByEsc: true,
					closeIcon: true,
					//contentColor: 'white',
					buttons: []
				}
			);
		}
	};

})(window);