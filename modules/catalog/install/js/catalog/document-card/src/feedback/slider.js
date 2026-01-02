import { ajax as Ajax, Runtime, Type } from 'main.core';

export default class Slider
{
	static openFeedbackForm()
	{
		BX.UI.Feedback.Form.open(Slider.getFeedbackParams());
	}

	static async openIntegrationRequestForm(event): void
	{
		if (event && Type.isFunction(event.preventDefault))
		{
			event.preventDefault();
		}

		try
		{
			const response = await Ajax.runComponentAction(
				'bitrix:catalog.feedback',
				'getFormParams',
				{
					mode: 'class',
				},
			);

			const { Form } = await Runtime.loadExtension(['ui.feedback.form']);
			const formIdNumber = Math.round(Math.random() * 1000);
			const data = response.data;
			data.id += formIdNumber.toString();
			Form.open(data);
		}
		catch (err)
		{
			await console.error(err);
		}
	}

	static open(url, rawOptions): Promise
	{
		let options = Type.isPlainObject(rawOptions)
			? rawOptions
			: {}
		;
		options = { cacheable: false, allowChangeHistory: false, events: {}, ...options };

		return new Promise((resolve) => {
			if (Type.isString(url) && url.length > 1)
			{
				options.events.onClose = function(event)
				{
					resolve(event.getSlider());
				};
				BX.SidePanel.Instance.open(url, rawOptions);
			}
			else
			{
				resolve();
			}
		});
	}

	static getFeedbackParams(): Object
	{
		return {
			id: `catalog-feedback-${parseInt(Math.random() * 1000, 10)}`,
			forms: [
				{ id: 384, lang: 'ru', sec: '0pskpd', zones: ['ru', 'by', 'kz'] },
				{ id: 392, lang: 'en', sec: 'siqjqa', zones: ['en', 'ua'] },
				{ id: 388, lang: 'es', sec: '53t2bu', zones: ['es'] },
				{ id: 390, lang: 'de', sec: 'mhglfc', zones: ['de'] },
				{ id: 386, lang: 'com.br', sec: 't6tdpy', zones: ['com.br'] },
			],
		};
	}
}
