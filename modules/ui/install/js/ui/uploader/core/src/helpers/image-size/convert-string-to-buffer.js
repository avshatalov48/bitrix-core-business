const convertStringToBuffer = (str: string): ArrayLike => {
	const result = [];
	for (let i = 0; i < str.length; i++)
	{
		result.push(str.charCodeAt(i) & 0xFF);
	}

	return result;
};
export default convertStringToBuffer;