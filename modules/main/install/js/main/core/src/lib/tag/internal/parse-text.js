import Type from '../../type';
import matchers from './matchers';

type TextResult = {
	type: 'text' | 'placeholder',
	content?: string,
	uid?: number,
};

export default function parseText(input: string): Array<TextResult>
{
	const preparedText = input.replace(/[\n\r\t]$/, '');
	const placeholders = preparedText.match(matchers.placeholder);
	return preparedText.split(matchers.placeholder).reduce((acc, item, index) => {
		if (Type.isStringFilled(item))
		{
			acc.push(
				...item.split(/\n/).reduce((textAcc, text) => {
					const preparedItemText = text.replace(/[\t\r]/g, '');
					if (Type.isStringFilled(preparedItemText))
					{
						textAcc.push({
							type: 'text',
							content: preparedItemText,
						});
					}

					return textAcc;
				}, []),
			);
		}

		if (placeholders && placeholders[index])
		{
			acc.push({
				type: 'placeholder',
				uid: parseInt(placeholders[index].replace(/{{uid|}}/, '')),
			});
		}

		return acc;
	}, []);
}