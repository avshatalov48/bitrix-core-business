import { VoteResultDisplay } from './components/result-display/result-display';
import type { BackendResultAll, VoteAttachedResultOptions } from './types';
import { BitrixVue, VueCreateAppResult } from 'ui.vue3';

export class VoteAttachedResult
{
	#application: VueCreateAppResult;
	#votedPageSize: number;

	constructor(options: VoteAttachedResultOptions)
	{
		this.#votedPageSize = options.votedPageSize || 10;
	}

	createApplicationWithResult(backendResult: BackendResultAll): VueCreateAppResult
	{
		return BitrixVue.createApp({
			name: 'VoteAttachedResultRoot',
			components: { VoteResultDisplay },
			props: {
				loaded: {
					type: Object,
					required: true,
				},
				votedPageSize: {
					type: Number,
					required: true,
				},
			},
			template: '<VoteResultDisplay :loadedData="loaded" :votedPageSize="votedPageSize"/>',
		}, {
			loaded: backendResult,
			votedPageSize: this.#votedPageSize,
		});
	}

	renderTo(backendResult: BackendResultAll, container: HTMLElement): void
	{
		this.#application = this.createApplicationWithResult(backendResult);
		this.#application.mount(container);
	}
}
