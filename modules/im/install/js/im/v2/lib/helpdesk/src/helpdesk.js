export const openHelpdeskArticle = (articleCode: string): void => {
	BX.Helper?.show(`redirect=detail&code=${articleCode}`);
};

export const getHelpdeskStringCallback = (articleCode: string): string => {
	return `BX.Helper?.show('redirect=detail&code=${articleCode}')`;
};
