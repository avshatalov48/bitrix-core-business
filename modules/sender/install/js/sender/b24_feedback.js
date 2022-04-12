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
		show: function ()
		{
			BX.UI.Feedback.Form.open(
				{
					forms: [
						{zones: ['es'], id: 26, lang: 'la', sec: '5i211d'},
						{zones: ['de'], id: 24, lang: 'de', sec: 'ls8jym'},
						{zones: ['com.br'], id: 28, lang: 'br', sec: 'lqo2cl'},
						{zones: ['en', 'eu', 'in', 'uk', 'pl','la', 'co', 'mx'], id: 22, lang: 'en', sec: 'iv17oh'},
						{zones: ['ua'], id: 345, lang: 'ua', sec: 'ze1pz8'},
						{zones: ['ru', 'kz', 'by'], id: 20, lang: 'ru', sec: 'whjggl'},
					],
					id:'sender-configuration-help',
				}
			)
		},
	};

})(window);