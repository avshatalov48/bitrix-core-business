export class Mode
{
	static experimental: ModeOptions = {
		id: 0,
		code: 'experimental',
	};

	static interception: ModeOptions = {
		id: 1,
		code: 'interception',
	};

	static isMode(modeId: number): boolean
	{
		return [0, 1].includes(modeId);
	}

	static getMode(modeId: number): ModeOptions | null
	{
		if (modeId === 0)
		{
			return Mode.experimental;
		}
		else if (modeId === 1)
		{
			return Mode.interception;
		}

		return null;
	}

	static getAllModes(): object<number, ModeOptions>
	{
		return {
			0: Mode.experimental,
			1: Mode.interception
		};
	}
}

export type ModeOptions = {
	id: number,
	code: string,
}