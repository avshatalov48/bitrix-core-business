import { getCurrentInstance } from 'ui.vue3';

export type UseLoc = {
	getMessage: (messageId: string, replacements: string) => string,
};

export function useLoc(): UseLoc
{
	const app = getCurrentInstance()?.appContext.app;
	const bitrix = app?.config?.globalProperties?.$bitrix ?? null;

	return {
		getMessage: (messageId: string, replacements: string): string => {
			return bitrix?.Loc?.getMessage(messageId, replacements);
		},
	};
}
