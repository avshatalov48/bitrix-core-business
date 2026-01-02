import { computed, toValue, nextTick, markRaw } from 'ui.vue3';
import { useBlockDiagram } from './block-diagram';
import { commandToArray } from '../utils';
import type { State, Snapshot } from '../types';

export type SnapshotHandler = (state: State) => {...};
export type RevertHandler = (snapshot: Snapshot) => void;

export type UseHistoryOptions = {
	snapshotHandler?: SnapshotHandler,
	revertHandler?: RevertHandler,
	maxCount?: number,
};

export type HandlerOptions = {
	snapshotHandler?: SnapshotHandler,
	revertHandler?: RevertHandler,
	emptyHistorySnapshot: {...},
};

export type UseHistory = {
	hasNext: boolean,
	hasPrev: boolean,
	setHandlers: (options: HandlerOptions) => void,
	makeSnapshot: (options: HandlerOptions) => void,
	next: () => void,
	prev: () => void,
	clear: () => void,
};

// eslint-disable-next-line max-lines-per-function
export function useHistory(options: UseHistoryOptions = {}): UseHistory
{
	const commonSnapshotHandler = (newState) => {
		return markRaw({
			blocks: markRaw(JSON.parse(JSON.stringify(newState.blocks))),
			connections: markRaw(JSON.parse(JSON.stringify(newState.connections))),
		});
	};

	const commonRevertHandler = (snapshot): void => {
		hooks.changedBlocks.trigger(
			commandToArray.commandReplace(snapshot.blocks),
		);
		hooks.changedConnections.trigger(
			commandToArray.commandReplace(snapshot.connections),
		);
	};

	const commonEmptyHistorySnapshot = {
		blocks: [],
		connections: [],
	};

	const instance = useBlockDiagram();
	const {
		headSnapshot,
		tailSnapshot,
		currentSnapshot,
		maxCountSnapshots,
		hooks,
		snapshotHandler,
		revertHandler,
		setHistoryHandlers,
		historyCurrentState,
	} = instance;

	const {
		snapshotHandler: newSnapshotHandler = null,
		revertHandler: newRevertHandler = null,
		emptyHistorySnapshot = commonEmptyHistorySnapshot,
		maxCount,
	} = options;
	setHandlers({ newSnapshotHandler, newRevertHandler });
	maxCountSnapshots.value = maxCount || toValue(maxCountSnapshots);

	const hasNext = computed(() => toValue(currentSnapshot) && toValue(currentSnapshot).next !== null);
	const hasPrev = computed(() => toValue(currentSnapshot) && toValue(currentSnapshot).prev !== null);

	function setHandlers(newHandlerOptions: HandlerOptions): void
	{
		const handlerOptions = {
			snapshotHandler: newHandlerOptions.snapshotHandler ?? toValue(snapshotHandler) ?? commonSnapshotHandler,
			revertHandler: newHandlerOptions.revertHandler ?? toValue(revertHandler) ?? commonRevertHandler,
		};

		setHistoryHandlers(handlerOptions);
	}

	function getCountSnapshots(): number
	{
		let count = 0;
		let current: Snapshot | null = toValue(headSnapshot);

		while (current)
		{
			current = current.next;
			count += 1;
		}

		return count;
	}

	function makeSnapshot(options: HandlerOptions = {}): void
	{
		const {
			snapshotHandler: newSnapshotHandler = null,
			revertHandler: newRevertHandler = null,
			emptySnapshot: newEmptySnapshot = null,
		} = options;

		const snapshotHistoryHandler = newSnapshotHandler || toValue(snapshotHandler);
		const revertHistoryHandler = newRevertHandler || toValue(revertHandler);
		const emptySnapshot = newEmptySnapshot || emptyHistorySnapshot;

		const newSnapshot = markRaw({
			snapshot: snapshotHistoryHandler(toValue(historyCurrentState)),
			revertHandler: revertHistoryHandler,
			emptySnapshot,
			next: null,
			prev: tailSnapshot.value,
		});

		if (toValue(currentSnapshot) && toValue(currentSnapshot)?.next !== null)
		{
			currentSnapshot.value.next = newSnapshot;
			newSnapshot.prev = currentSnapshot.value;
			tailSnapshot.value.prev = null;
			tailSnapshot.value.next = null;
			tailSnapshot.value = newSnapshot;
		}
		else if (toValue(headSnapshot) === null)
		{
			headSnapshot.value = newSnapshot;
			tailSnapshot.value = newSnapshot;
		}
		else
		{
			tailSnapshot.value.next = newSnapshot;
			tailSnapshot.value = newSnapshot;
		}

		currentSnapshot.value = newSnapshot;

		if (getCountSnapshots() <= toValue(maxCountSnapshots) + 1)
		{
			return;
		}

		const firstSnapshot = headSnapshot.value;
		headSnapshot.value = firstSnapshot.next;
		headSnapshot.value.prev = null;
		firstSnapshot.next = null;
	}

	async function revertState({ revertHandler, snapshot, emptySnapshot }): void
	{
		revertHandler(emptySnapshot);
		await nextTick();
		revertHandler(snapshot);
	}

	async function next(): void
	{
		if (toValue(currentSnapshot) === null || toValue(currentSnapshot).next === null)
		{
			return;
		}

		await revertState(toValue(currentSnapshot).next);
		currentSnapshot.value = toValue(currentSnapshot).next;
	}

	async function prev(): void
	{
		if (toValue(currentSnapshot) === null || toValue(currentSnapshot).prev === null)
		{
			return;
		}

		await revertState(toValue(currentSnapshot).prev);
		currentSnapshot.value = toValue(currentSnapshot).prev;
	}

	function clear(): void
	{
		headSnapshot.value = null;
		tailSnapshot.value = null;
		currentSnapshot.value = null;
	}

	return {
		hasNext,
		hasPrev,
		setHandlers,
		makeSnapshot: () => nextTick(() => makeSnapshot()),
		next,
		prev,
		clear,
		commonSnapshotHandler,
		commonRevertHandler,
	};
}
