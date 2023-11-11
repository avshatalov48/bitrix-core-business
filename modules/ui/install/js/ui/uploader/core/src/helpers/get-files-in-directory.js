const getFilesInDirectory = (entry: FileSystemDirectoryEntry): Promise<File[]> => {
	return new Promise((resolve, reject): void => {
		const files = [];
		let dirCounter = 0;
		let fileCounter = 0;

		const resolveIfDone = (): void => {
			if (fileCounter === 0 && dirCounter === 0)
			{
				resolve(files);
			}
		};

		const readEntries = (dirEntry: FileSystemDirectoryEntry): void => {
			dirCounter++;
			const directoryReader: FileSystemDirectoryReader = dirEntry.createReader();
			const readBatch = (): void => {
				directoryReader.readEntries((entries: FileSystemEntry[]): void => {
					if (entries.length === 0)
					{
						dirCounter--;
						resolveIfDone();

						return;
					}

					entries.forEach((fileEntry: FileSystemFileEntry | FileSystemDirectoryEntry): void => {
						if (fileEntry.isDirectory)
						{
							readEntries(fileEntry);
						}
						else
						{
							fileCounter++;
							fileEntry.file((file: File): void => {
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

export default getFilesInDirectory;
