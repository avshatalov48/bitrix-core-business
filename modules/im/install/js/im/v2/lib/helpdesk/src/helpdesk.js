export const openHelpdeskArticle = (articleCode: string): void => {
	BX.Helper?.show(`redirect=detail&code=${articleCode}`);
};
