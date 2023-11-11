const compareBuffers = (dataView: DataView, dest: ArrayLike, start: number): boolean => {
	for (let i = start, j = 0; j < dest.length;)
	{
		if (dataView.getUint8(i++) !== dest[j++])
		{
			return false;
		}
	}

	return true;
};

export default compareBuffers;
