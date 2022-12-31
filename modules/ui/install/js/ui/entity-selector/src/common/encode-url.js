const regexp = /^data:((?:\w+\/(?:(?!;).)+)?)((?:;[\w\W]*?[^;])*),(.+)$/;

const isDataUri = (str: string): boolean => {
	return typeof(str) === 'string' ? str.match(regexp) : false;
};

export default function encodeUrl(url: string): string
{
	if (isDataUri(url))
	{
		return url;
	}

	return encodeURI(url);
}