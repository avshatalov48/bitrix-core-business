import { watch, effectScope, markRaw } from 'ui.vue3';
import { useBlockDiagram } from './block-diagram';

export type UseWatchProps = {
	dispose: () => void,
};

export function useWatchProps(props): UseWatchProps
{
	const {
		blocks,
		connections,
		historyCurrentState,
		zoom,
		isDisabled,
	} = useBlockDiagram();
	const scope = effectScope(true);

	scope.run(() => {
		watch([() => props.blocks, () => props.blocks.length], ([newBlocks]) => {
			if (newBlocks && Array.isArray(newBlocks))
			{
				historyCurrentState.value.blocks = markRaw(JSON.parse(JSON.stringify(newBlocks)));
				blocks.value = newBlocks;
			}
		}, { immediate: true, deep: true });

		watch([() => props.connections, () => props.connections.length], ([newConnections]) => {
			historyCurrentState.value.connections = markRaw(JSON.parse(JSON.stringify(newConnections)));
			connections.value = [...newConnections];
		}, { immediate: true, deep: true });

		watch(() => props.zoom, (newZoom) => {
			zoom.value = newZoom;
		}, { immediate: true });

		watch(() => props.minZoom, (newMinZoom) => {
			zoom.value = newMinZoom;
		}, { immediate: true });

		watch(() => props.maxZoom, (newMaxZoom) => {
			zoom.value = newMaxZoom;
		}, { immediate: true });

		watch(() => props.disabled, (disabled) => {
			isDisabled.value = disabled;
		}, { immediate: true });
	});

	function dispose(): void
	{
		scope.stop();
	}

	return {
		dispose,
	};
}
