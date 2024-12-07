import { ConfigProvider } from './providers/config-provider';
import { EventType } from './providers/event-type';
import { Loader } from 'main.loader';

export type FormConfig = {
	config: string
}

export class AppForm
{
	#url = '/marketplace/app/settings/';
	#width = 575;
	#options: FormConfig;

	constructor(options: FormConfig)
	{
		this.#options = options;
	}

	show(): void
	{
		top.BX.SidePanel.Instance.open(this.#url, {
			width: this.#width,
			requestMethod: 'post',
			requestParams: this.#options,
			allowChangeHistory: false,
		});
	}

	static sliderLoader()
	{
		top.BX.SidePanel.Instance.open('rest:app-form.loader', {
			width: 575,
			contentCallback: (slider) => {
				const loader = new Loader({
					target: slider.getFrameWindow(),
				});

				return loader.show();
			},
			requestMethod: 'post',
			allowChangeHistory: false,
		});
	}

	static buildByApp(clientId: string, eventType: EventType): Promise
	{
		const provider = new ConfigProvider(clientId, eventType);

		return provider.fetch().then((response) => {
			return (new AppForm(response.data));
		});
	}

	static buildByAppWithLoader(clientId: string, eventType: EventType): Promise
	{
		const provider = new ConfigProvider(clientId, eventType);
		AppForm.sliderLoader();

		return provider.fetch().then((response) => {
			top.BX.SidePanel.Instance.close(true);
			top.BX.SidePanel.Instance.destroy('loader');

			return (new AppForm(response.data));
		});
	}
}

export {
	EventType,
};
