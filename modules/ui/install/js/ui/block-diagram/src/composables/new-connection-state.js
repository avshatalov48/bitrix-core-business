import { computed, toValue } from 'ui.vue3';
import { getBeziePath, getLinePath } from '../utils';
import type { PathInfo } from '../utils';
import { useBlockDiagram } from './block-diagram';
import { CONNECTION_VIEW_TYPE } from '../constants';
import type { DiagramConnectionViewType } from '../types';

export type useNewConnectionStateOptions = {
	viewType: DiagramConnectionViewType;
};

export type UseNewConnectionState = {
	hasNewConnection: boolean;
	newConnectionPathInfo: PathInfo;
};

export function useNewConnectionState(options: useNewConnectionStateOptions): UseNewConnectionState
{
	const { newConnection } = useBlockDiagram();
	const { viewType } = options;

	const hasNewConnection = computed((): boolean => {
		return toValue(newConnection) !== null;
	});

	const newConnectionPathInfo = computed((): PathInfo => {
		if (!toValue(hasNewConnection))
		{
			return {
				path: '',
				center: {
					x: 0,
					y: 0,
				},
			};
		}

		if (toValue(viewType) === CONNECTION_VIEW_TYPE.BEZIER)
		{
			return getBeziePath(
				toValue(newConnection).start,
				toValue(newConnection).end,
			);
		}

		return getLinePath(
			toValue(newConnection).start,
			toValue(newConnection).end,
		);
	});

	return {
		hasNewConnection,
		newConnectionPathInfo,
	};
}
