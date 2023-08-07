const isFileSystemItem = (item: DataTransferItem) => {
	if ('webkitGetAsEntry' in item)
	{
		const entry: ?FileSystemEntry = item.webkitGetAsEntry();
		if (entry)
		{
			return entry.isFile || entry.isDirectory;
		}
	}

	return item.kind === 'file';
};

export default isFileSystemItem;
