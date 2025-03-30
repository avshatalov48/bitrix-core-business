import { Type } from 'main.core';
import { BitrixVue, VueCreateAppResult } from 'ui.vue3';
import { Main, AppContext } from './components/main';

declare type AppConstructorParams = {
	containerId: string,
	context: AppContext,
	value: Array,
};

export class App
{
	#controlId: string;
	#container: HTMLElement;
	#context: AppContext;
	#value: Array;

	#app: ?VueCreateAppResult = null;

	constructor(params: AppConstructorParams)
	{
		this.#controlId = params.controlId;
		this.#container = document.getElementById(params.containerId);

		if (!Type.isDomNode(this.#container))
		{
			throw new Error('container not found');
		}
		
		this.#context = params.context;
		this.#value = params.value.map((value) => { return parseInt(value); });
	}

	start(): void
	{
		this.#app = BitrixVue.createApp(
			{
				...Main
			},
			{
				controlId: this.#controlId,
				container: this.#container,
				context: this.#context,
				filledValues: this.#value,
			},
		);

		this.#app.mount(this.#container);
	}

	stop(): void
	{
		this.#app.unmount();

		this.#app = null;
	}
}
