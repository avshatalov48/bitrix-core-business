export const commonFunctions = {
	prepareResourcePath(source: string): string
	{
		let result = '';

		try
		{
			const url = new URL(source, location.origin);
			result = url.href;
		}
		catch
		{
			// empty
		}

		return result;
	},
};
