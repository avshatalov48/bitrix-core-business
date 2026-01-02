import { toValue } from 'ui.vue3';
import { useBlockDiagram } from './block-diagram';
import { HOOK_NAMES } from '../constants';
import { commandToArray, CommandPayload } from '../utils';

export type UseModelValue = { dispose: () => void };

export function useModelValue(emit): UseModelValue
{
	const {
		blocks,
		connections,
		historyCurrentState,
		hooks,
	} = useBlockDiagram();

	const handlersMap: { [$Values<typeof HOOK_NAMES>]: (command: CommandPayload) => void } = {
		[HOOK_NAMES.CHANGED_BLOCKS]: handleChangeBlocks,
		[HOOK_NAMES.CHANGED_CONNECTIONS]: handleChangeConnections,
	};

	Object.entries(handlersMap)
		.forEach(([hookName, handler]) => {
			hooks[hookName].on(handler);
		});

	function handleChangeBlocks(command: CommandPayload): void
	{
		commandToArray.runCommand(
			toValue(historyCurrentState.value.blocks),
			command,
			(value) => {
				historyCurrentState.value.blocks = value;
				emit('update:blocks', value);
			},
		);
	}

	function handleChangeConnections(command: CommandPayload): void
	{
		commandToArray.runCommand(
			toValue(historyCurrentState.value.connections),
			command,
			(value) => {
				historyCurrentState.value.connections = value;
				emit('update:connections', value);
			},
		);
	}

	function dispose(): void
	{
		Object.entries(handlersMap)
			.forEach(([hookName, handler]) => {
				hooks[hookName].off(handler);
			});
	}

	return {
		dispose,
	};
}
