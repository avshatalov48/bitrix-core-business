import getFilesInDirectory from './get-files-in-directory';
import isDirectoryEntry from './is-directory-entry';
import isFileSystemItem from './is-file-system-item';

const getFilesFromItem = (item: DataTransferItem): Promise<File[]> => {
	return new Promise((resolve, reject): void => {
		if (isDirectoryEntry(item))
		{
			getFilesInDirectory(item.webkitGetAsEntry())
				.then(resolve)
				.catch(reject)
			;

			return;
		}

		resolve([item.getAsFile()]);
	});
};

export const getFilesFromDataTransfer = (dataTransfer: DataTransfer, browseFolders = true): Promise<File[]> => {
	return new Promise((resolve, reject): void => {
		if (!dataTransfer.items || dataTransfer.items.length === 0)
		{
			resolve(dataTransfer.files ? [...dataTransfer.files] : []);

			return;
		}

		const items: Promise[] = [...dataTransfer.items]
			.filter((item: DataTransferItem): boolean => {
				return browseFolders ? isFileSystemItem(item) : item.kind === 'file';
			})
			.map((item: DataTransferItem): Promise => {
				return getFilesFromItem(item);
			})
		;

		Promise.all(items)
			.then((fileGroups: Array<File[]>): void => {
				const files = [];
				fileGroups.forEach((group: File[]): void => {
					files.push(...group);
				});

				resolve(files);
			})
			.catch(reject)
		;
	});
};

export const hasDataTransferOnlyFiles = (dataTransfer: DataTransfer, browseFolders = true): Promise<File[]> => {
	return new Promise((resolve, reject): void => {
		if (!dataTransfer.items)
		{
			resolve(dataTransfer.files ? dataTransfer.files.length > 0 : false);

			return;
		}

		const success: boolean = [...dataTransfer.items].every((item: DataTransferItem): boolean => {
			return browseFolders ? isFileSystemItem(item) : item.kind === 'file' && !isDirectoryEntry(item);
		});

		resolve(success);
	});
};

export const isFilePasted = (dataTransfer: DataTransfer, browseFolders = true): boolean => {
	if (!dataTransfer.types.includes('Files'))
	{
		return false;
	}

	let files = 0;
	let texts = 0;
	const items: DataTransferItemList = dataTransfer.items;
	for (const item of items)
	{
		if (item.kind === 'string')
		{
			texts++;
		}
		else
		{
			const isFile = browseFolders ? isFileSystemItem(item) : item.kind === 'file' && !isDirectoryEntry(item);
			if (isFile)
			{
				files++;
			}
		}
	}

	return files >= texts;
};
