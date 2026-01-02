import { createHook, CreateHook } from '../utils';
import { HOOK_NAMES } from '../constants';

export type UseHooks = Record<$Values<HOOK_NAMES>, CreateHook>;

export function useHooks(): UseHooks
{
	return {
		[HOOK_NAMES.START_DRAG_BLOCK]: createHook(),
		[HOOK_NAMES.MOVE_DRAG_BLOCK]: createHook(),
		[HOOK_NAMES.END_DRAG_BLOCK]: createHook(),
		[HOOK_NAMES.ADD_BLOCK]: createHook(),
		[HOOK_NAMES.UPDATE_BLOCK]: createHook(),
		[HOOK_NAMES.DELETE_BLOCK]: createHook(),
		[HOOK_NAMES.CREATE_CONNECTION]: createHook(),
		[HOOK_NAMES.DELETE_CONNECTION]: createHook(),
		[HOOK_NAMES.CHANGED_BLOCKS]: createHook(),
		[HOOK_NAMES.CHANGED_CONNECTIONS]: createHook(),
		[HOOK_NAMES.BLOCK_TRANSITION_START]: createHook(),
		[HOOK_NAMES.BLOCK_TRANSITION_END]: createHook(),
		[HOOK_NAMES.CONNECTION_TRANSITION_START]: createHook(),
		[HOOK_NAMES.CONNECTION_TRANSITION_END]: createHook(),
		[HOOK_NAMES.DROP_NEW_BLOCK]: createHook(),
	};
}
