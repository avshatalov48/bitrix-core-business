const getFilesFromDataTransfer = (dataTransfer: DataTransfer) => {
	return new Promise((resolve, reject) => {
		if (!dataTransfer.items)
		{
			resolve(dataTransfer.files ? Array.from(dataTransfer.files) : []);

			return;
		}

		const items = Array.from(dataTransfer.items)
			.filter(item => isFileSystemItem(item))
			.map(item => getFilesFromItem(item))
		;

		Promise.all(items)
			.then((fileGroups: Array<File[]>) => {
				const files = [];
				fileGroups.forEach((group: File[]) => {
					files.push.apply(files, group);
				});

				resolve(files);
			})
			.catch(reject)
		;
	});
};

export default getFilesFromDataTransfer;

const isFileSystemItem = (item: DataTransferItem) => {
	if ('webkitGetAsEntry' in item)
	{
		const entry = item.webkitGetAsEntry();
		if (entry)
		{
			return entry.isFile || entry.isDirectory;
		}
	}

	return item.kind === 'file';
};

const getFilesFromItem = (item: DataTransferItem) => {
	return new Promise((resolve, reject) => {
		if (isDirectoryEntry(item))
		{
			getFilesInDirectory(getAsEntry(item))
				.then(resolve)
				.catch(reject)
			;

			return;
		}

		resolve([item.getAsFile()]);
	});
};

const getFilesInDirectory = entry => {
	return new Promise((resolve, reject) => {
		const files = [];
		let dirCounter = 0;
		let fileCounter = 0;

		const resolveIfDone = () => {
			if (fileCounter === 0 && dirCounter === 0)
			{
				resolve(files);
			}
		};

		const readEntries = dirEntry => {
			dirCounter++;
			const directoryReader = dirEntry.createReader();
			const readBatch = () => {
				directoryReader.readEntries(entries => {
					if (entries.length === 0)
					{
						dirCounter--;
						resolveIfDone();
						return;
					}

					entries.forEach(entry => {
						if (entry.isDirectory)
						{
							readEntries(entry);
						}
						else
						{
							fileCounter++;
							entry.file(file => {
								files.push(file);
								fileCounter--;
								resolveIfDone();
							});
						}
					});

					readBatch();
				}, reject);
			};

			readBatch();
		};

		readEntries(entry);
	});
};

const isDirectoryEntry = item => isEntry(item) && (getAsEntry(item) || {}).isDirectory;
const isEntry = item => 'webkitGetAsEntry' in item;
const getAsEntry = item => item.webkitGetAsEntry();