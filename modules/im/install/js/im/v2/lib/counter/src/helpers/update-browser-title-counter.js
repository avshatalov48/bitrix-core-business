export const updateBrowserTitleCounter = (newCounter: number) => {
	const MAX_COUNTER_VALUE = 99;
	const MAX_COUNTER_TEXT = `${MAX_COUNTER_VALUE}+`;

	const regexp = /^\((?<currentCounter>\d+|\d+\+)\)\s(?<text>.*)/;
	const matchResult: ?RegExpMatchArray = document.title.match(regexp);
	const displayCounter = newCounter > MAX_COUNTER_VALUE ? MAX_COUNTER_TEXT : newCounter;

	if (matchResult?.groups.currentCounter)
	{
		const currentCounter = Number.parseInt(matchResult.groups.currentCounter, 10);
		if (newCounter !== currentCounter)
		{
			const counterPrefix = newCounter > 0 ? `(${displayCounter}) ` : '';
			document.title = `${counterPrefix}${matchResult.groups.text}`;
		}
	}
	else if (newCounter > 0)
	{
		document.title = `(${displayCounter}) ${document.title}`;
	}
};
