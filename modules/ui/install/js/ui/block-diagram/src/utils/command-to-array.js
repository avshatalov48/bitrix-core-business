export const ARRAY_COMMANDS = Object.freeze({
	REPLACE: 'replace',
	PUSH: 'push',
	UPDATE_BY_INDEX: 'updateByIndex',
	DELETE_BY_INDEX: 'deleteByIndex',
});

export type CommandPayload = {
	commandType: $Values<typeof ARRAY_COMMANDS>,
	payload?: Array<{...}> | {...},
	index?: number,
};

const commandExecMap = {
	[ARRAY_COMMANDS.REPLACE]: ({ payload }) => {
		return [...payload];
	},
	[ARRAY_COMMANDS.PUSH]: ({ source, payload }) => {
		const result = [...source];
		result.push(payload);

		return result;
	},
	[ARRAY_COMMANDS.UPDATE_BY_INDEX]: ({ source, payload, index }) => {
		const result = [...source];
		result[index] = {
			...result[index],
			...payload,
		};

		return result;
	},
	[ARRAY_COMMANDS.DELETE_BY_INDEX]: ({ source, index }) => {
		const result = [...source];
		result.splice(index, 1);

		return result;
	},
};

function command(
	commandType: $Values<typeof ARRAY_COMMANDS>,
	args: { payload: Array<{...}> | {...}, index: number },
): CommandReturnType<P, I>
{
	return {
		commandType,
		...args,
	};
}

export function commandReplace(payload: Array<{...}>): CommandPayload
{
	return command(ARRAY_COMMANDS.REPLACE, { payload });
}

export function commandPush(payload: {...}): CommandPayload
{
	return command(ARRAY_COMMANDS.PUSH, { payload });
}

export function commandUpdateByIndex(index: number, payload: {...}): CommandPayload
{
	return command(ARRAY_COMMANDS.UPDATE_BY_INDEX, { index, payload });
}

export function commandDeleteByIndex(index: number): CommandPayload
{
	return command(ARRAY_COMMANDS.DELETE_BY_INDEX, { index });
}

export function runCommand(
	sourceArray: Array<{...}>,
	commandPayload: CommandPayload,
	callback: (value: Array<{...}>) => any,
): void
{
	const { commandType, payload, index } = commandPayload;
	const result = commandExecMap[commandType]({
		source: sourceArray,
		payload,
		index,
	});

	callback(result);
}
