import 'sidepanel';

export class Router
{
	static init()
	{
		if (top !== window)
		{
			top.BX.Runtime.loadExtension('bizproc.router').then(({ Router }) => {
				Router.init();
			}).catch(e => console.error(e));

			return;
		}

		this.#bind();
	}

	static #bind()
	{
		top.BX.SidePanel.Instance.bindAnchors({
			rules:
				[
					{
						condition: [
							'/rpa/task/',
						],
						options: {
							width: 580,
							cacheable: false,
							allowChangeHistory: false,
						},
					},
					{
						condition: [
							'/company/personal/bizproc/([a-zA-Z0-9\\.]+)/',
						],
						options: {
							cacheable: false,
							loader: 'bizproc:workflow-info',
							width: this.#detectSliderWidth(),
						},
					},
				],
		});
	}

	static #detectSliderWidth(): number
	{
		if (window.innerWidth < 1500)
		{
			return null; // default slider width
		}

		return 1500 + Math.floor((window.innerWidth - 1500) / 3);
	}
}
