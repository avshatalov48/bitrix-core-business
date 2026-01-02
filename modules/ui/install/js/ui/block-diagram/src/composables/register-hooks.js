import { Type } from 'main.core';
import { useBlockDiagram } from './block-diagram';

export type UseRegisterHooks = {
	dispose: () => void,
};

export function useRegisterHooks(...hooksMaps): UseRegisterHooks
{
	const { hooks } = useBlockDiagram();

	const mergedHookMaps = ([...hooksMaps])
		.reduce((accHookMaps, hookMap) => {
			const result = { ...accHookMaps };

			Object.entries(hookMap)
				.forEach(([hookName, handler]) => {
					if (hookName in accHookMaps)
					{
						result[hookName].push(
							...(Type.isFunction(handler) ? [handler] : handler),
						);
					}
					else
					{
						result[hookName] = [
							...(Type.isFunction(handler) ? [handler] : handler),
						];
					}
				});

			return result;
		}, {});

	Object.entries(mergedHookMaps)
		.forEach(([hookName, handlers]) => {
			handlers.forEach((handler) => {
				hooks?.[hookName]?.on(handler);
			});
		});

	function dispose()
	{
		Object.entries(mergedHookMaps)
			.forEach(([hookName, handlers]) => {
				handlers.forEach((handler) => {
					hooks?.[hookName]?.off(handler);
				});
			});
	}

	return {
		dispose,
	};
}
