export type FormConfig = {
	config: string
}

export class AppForm
{
	#url = '/app/settings/';
	#width = 575;
	#options: FormConfig;

	constructor(options: FormConfig)
	{
		this.#options = options;
	}

	show()
	{
		top.BX.SidePanel.Instance.open(this.#url, {
			width: this.#width,
			requestMethod: 'post',
			requestParams: this.#options,
			allowChangeHistory: false,
		});
	}
}