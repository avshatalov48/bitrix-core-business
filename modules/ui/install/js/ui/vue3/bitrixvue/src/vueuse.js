/*!
 * Utilities from VueUse collection
 * (c) 2019-2022 Anthony Fu
 * Released under the MIT License.
 *
 * @source: https://github.com/vueuse/vueuse/blob/main/packages/shared/tryOnScopeDispose/index.ts
 * @source: https://github.com/vueuse/vueuse/blob/main/packages/rxjs/useObservable/index.ts
 */

/**
 * Modify list for integration with Bitrix Framework:
 * - remove vue-demi library from global import, replace to 'ui.vue3';
 * - replace TypeScript to ECMAScript
 */

import { ref, getCurrentScope, onScopeDispose } from 'ui.vue3';

export function tryOnScopeDispose(fn)
{
	if (getCurrentScope()) {
		onScopeDispose(fn)
		return true
	}
	return false
}

export function useObservable(observable, options) {
	const value = ref(options?.initialValue)
	const subscription = observable.subscribe({
		next: val => (value.value = val),
		error: options?.onError
	})
	tryOnScopeDispose(() => {
		subscription.unsubscribe()
	})
	return value
}
