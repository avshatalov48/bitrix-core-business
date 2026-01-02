import 'main.polyfill.intersectionobserver';
import { toValue } from 'ui.vue3';
import { useBlockDiagram } from './block-diagram';

type UseInitAppElements = {
	onMountedAppElements: () => void;
	onUnmountedAppElements: () => void;
}

type Options = {
	blockDiagramRef: HTMLElement;
}

export function useInitAppElements(options: Options): UseInitAppElements
{
	const {
		blockDiagramRef: newBlockDiagramRef,
	} = options;
	const {
		blockDiagramRef,
		blockDiagramTop,
		blockDiagramLeft,
	} = useBlockDiagram();

	let observer: typeof IntersectionObserver | null = null;

	function handleInterObserver(entries): void
	{
		entries.forEach((entry) => {
			const { top, left } = entry.boundingClientRect;

			blockDiagramTop.value = top;
			blockDiagramLeft.value = left;
		});
	}

	function onMountedAppElements()
	{
		blockDiagramRef.value = toValue(newBlockDiagramRef);

		const { left, top } = toValue(newBlockDiagramRef).getBoundingClientRect();

		blockDiagramTop.value = top;
		blockDiagramLeft.value = left;

		observer = new IntersectionObserver(handleInterObserver);
		observer.observe(toValue(blockDiagramRef));
	}

	function onUnmountedAppElements(): void
	{
		observer.unobserve(toValue(blockDiagramRef));
	}

	return {
		onMountedAppElements,
		onUnmountedAppElements,
	};
}
