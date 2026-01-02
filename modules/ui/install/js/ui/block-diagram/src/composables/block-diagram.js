import { getCurrentInstance, reactive, toRefs } from 'ui.vue3';
import { State } from '../types';
import { useState } from './state';
import { useActions, UseActions } from './actions';
import { useGetters, UseGetters } from './getters';
import { useHooks, UseHooks } from './hooks';

export type UseBlockDiagran = {
	...State,
	...UseGetters,
	...UseHooks,
	...UseActions,
};

export function useBlockDiagram(options): UseBlockDiagran
{
	const app = getCurrentInstance()?.appContext.app;
	const blockDiagramState = app?.config?.globalProperties?.$blockDiagram ?? null;

	if (blockDiagramState !== null)
	{
		return blockDiagramState;
	}

	const state = useState(options);
	const reactiveState = reactive(state);
	const getters = useGetters(reactiveState);
	const hooks = useHooks();
	const actions = useActions({ state: reactiveState, getters, hooks });

	if (options)
	{
		actions.setState(options);
	}

	app.config.globalProperties.$blockDiagram = {
		...toRefs(reactiveState),
		...getters,
		...actions,
		hooks,
	};

	app.config.globalProperties.$blockDiagramTestId = (id: string, ...args: Array<string>): string => {
		if (!id)
		{
			throw new Error('ui.block-diagram not found test id');
		}

		const preparedArgs = args.reduce((acc, arg) => {
			return `${acc}-${arg}`;
		}, '');

		return `${id}${preparedArgs}`;
	};

	return app.config.globalProperties?.$blockDiagram;
}
