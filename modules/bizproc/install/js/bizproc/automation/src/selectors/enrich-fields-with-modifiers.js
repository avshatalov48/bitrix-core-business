import { Loc, Runtime, Type } from 'main.core';

const createNewField = (oldField: Field, newField: {}, objectId: string, modifier: string): Field => {
	const systemExpression = `{=${objectId}:${oldField.Id} ${modifier}}`;

	let expression = oldField.Expression;
	if (expression.startsWith('{{') && expression.endsWith('}}'))
	{
		expression = expression.replace(/^{{/, '').replace(/}}$/, '');
		if (expression.includes('#'))
		{
			expression = expression.slice(0, expression.indexOf('#')); // cut comment
		}

		expression = `{{${expression} ${modifier}}}`;
	}
	else
	{
		expression = systemExpression;
	}

	return {
		...Runtime.clone(oldField),
		...newField,
		ObjectId: objectId,
		Type: 'string',
		SystemExpression: systemExpression,
		Expression: expression,
	};
};

const modifiersMap = {
	friendly: '> friendly',
	printable: '> printable',
	server: '> server',
	responsible: '> responsible',
	shortLink: '> shortlink',
};

export default function enrichFieldsWithModifiers(
	fields: Array<Field>,
	objectId: string,
	useModifiers?: {
		friendly?: boolean,
		printable?: boolean,
		server?: boolean,
		responsible?: boolean,
		shortLink?: boolean,
	},
): Array<Field>
{
	const canUseModifier = (value) => Type.isNil(value) || value === true;

	const printablePrefix = Loc.getMessage('BIZPROC_AUTOMATION_CMP_MOD_PRINTABLE_PREFIX');
	const names = fields.map((field) => field.Name).join('\n');

	const result = [];
	fields.forEach((field) => {
		const printableName = `${field.Name} ${printablePrefix}`;
		const isCustomField = field.BaseType === 'string' && field.Type !== 'string';

		if (!isCustomField)
		{
			result.push({
				...Runtime.clone(field),
				ObjectId: objectId,
			});
		}

		if (field.Type === 'user' && canUseModifier(useModifiers?.friendly) && !names.includes(printableName))
		{
			result.push(
				createNewField(field, { Name: printableName }, objectId, modifiersMap.friendly),
			);
		}

		if (
			(['bool', 'file'].includes(field.Type) || isCustomField)
			&& canUseModifier(useModifiers?.printable)
			&& !names.includes(printableName)
		)
		{
			result.push(
				createNewField(field, { Name: printableName }, objectId, modifiersMap.printable),
			);
		}

		if (['date', 'datetime', 'time'].includes(field.BaseType))
		{
			if (canUseModifier(useModifiers?.server))
			{
				const name = `${field.Name} ${Loc.getMessage('BIZPROC_AUTOMATION_CMP_MOD_DATE_BY_SERVER')}`;
				result.push(
					createNewField(field, { Name: name }, objectId, modifiersMap.server),
				);
			}

			if (canUseModifier(useModifiers?.responsible))
			{
				const name = `${field.Name} ${Loc.getMessage('BIZPROC_AUTOMATION_CMP_MOD_DATE_BY_RESPONSIBLE')}`;
				result.push(
					createNewField(field, { Name: name }, objectId, modifiersMap.responsible),
				);
			}
		}

		if (field.Type === 'file' && canUseModifier(useModifiers?.shortLink))
		{
			result.push(
				createNewField(field, { Id: `${field.Id}_shortlink` }, objectId, modifiersMap.shortLink),
			);
		}
	});

	return result;
}
