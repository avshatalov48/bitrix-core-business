const isDirectoryEntry = (item: DataTransferItem): boolean => {
	return 'webkitGetAsEntry' in item && (item.webkitGetAsEntry() || {}).isDirectory === true;
};

export default isDirectoryEntry;
