(function(){
	BX.namespace("BX.Report.Analytics");

	BX.Report.Analytics.Feedback = function(options)
	{
		this.button = options.button;
		this.feedbackContainer = options.feedbackContainer;
		this.init();
	};

	BX.Report.Analytics.Feedback.prototype = {
		init: function()
		{
			this.formContainer = BX.create('div', {
				style: 'width: 600px; height: 673px; overflow-y: auto;'
			});

			this.feedbackContainer.appendChild(this.formContainer);


			BX.bind(this.button, 'click', this.handleFeedbackButtonClick.bind(this));
		},
		handleFeedbackButtonClick: function ()
		{
			this.openFeedbackSlider();
		},
		openFeedbackSlider: function()
		{
			this.sidePanel = BX.SidePanel.Instance;
			this.sidePanel.open("analytic:feedback-for-board-", {
				cacheable: false,
				contentCallback: function() {
					var promise = new BX.Promise();
					promise.fulfill();
					return promise;
				},
				animationDuration: 100,
				events: {
					onLoad: function(event)
					{
						var slider = event.getSlider();


						this.initForm();
						slider.layout.content.appendChild(this.feedbackContainer);
						this.feedbackContainer.style.display = 'block';
					}.bind(this)
				},
				width: 600
			});
		},
		initForm: function()
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
			})(window,document,'https://product-feedback.bitrix24.com/bitrix/js/crm/form_loader.js','b24form');

			var id, lang, sec;
			this.config = {};
			this.config.b24_zone = 'ru';
			this.config.b24_plan = 'company';
			switch (this.config.b24_zone)
			{
				case 'ru':
				case 'ua':
				case 'kz':
				case 'by':
					id = '68';
					lang = 'ru';
					sec = 'h6thh2';
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
				"node": this.formContainer,
				"presets": {
					"b24_plan": this.config.b24_plan
				}
			});
		}
	};

})();