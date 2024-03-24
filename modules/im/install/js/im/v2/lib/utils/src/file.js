import { Text, Loc, Dom, Event, Type } from 'main.core';
import { FileType } from 'im.v2.const';

export const FileUtil = {
	getFileExtension(fileName: string): string
	{
		if (!Type.isStringFilled(fileName))
		{
			return '';
		}

		return fileName.split('.').splice(-1)[0];
	},

	getIconTypeByFilename(fileName: string): string
	{
		const extension = this.getFileExtension(fileName);

		return this.getIconTypeByExtension(extension);
	},

	getIconTypeByExtension(extension: string): string
	{
		let icon = 'empty';

		switch (extension.toString())
		{
			case 'png':
			case 'jpe':
			case 'jpg':
			case 'jpeg':
			case 'gif':
			case 'heic':
			case 'bmp':
			case 'webp':
				icon = 'img';
				break;

			case 'mp4':
			case 'mkv':
			case 'webm':
			case 'mpeg':
			case 'hevc':
			case 'avi':
			case '3gp':
			case 'flv':
			case 'm4v':
			case 'ogg':
			case 'wmv':
			case 'mov':
				icon = 'mov';
				break;

			case 'txt':
				icon = 'txt';
				break;

			case 'doc':
			case 'docx':
				icon = 'doc';
				break;

			case 'xls':
			case 'xlsx':
				icon = 'xls';
				break;

			case 'php':
				icon = 'php';
				break;

			case 'pdf':
				icon = 'pdf';
				break;

			case 'ppt':
			case 'pptx':
				icon = 'ppt';
				break;

			case 'rar':
				icon = 'rar';
				break;

			case 'zip':
			case '7z':
			case 'tar':
			case 'gz':
			case 'gzip':
				icon = 'zip';
				break;

			case 'set':
				icon = 'set';
				break;

			case 'conf':
			case 'ini':
			case 'plist':
				icon = 'set';
				break;
			default:
				icon = 'empty';
		}

		return icon;
	},

	getFileTypeByExtension(extension: string): string
	{
		let type = FileType.file;
		const normalizedExtension = extension.toLowerCase();

		switch (normalizedExtension)
		{
			case 'png':
			case 'jpe':
			case 'jpg':
			case 'jpeg':
			case 'gif':
			case 'heic':
			case 'bmp':
			case 'webp':
				type = FileType.image;
				break;

			case 'mp4':
			case 'mkv':
			case 'webm':
			case 'mpeg':
			case 'hevc':
			case 'avi':
			case '3gp':
			case 'flv':
			case 'm4v':
			case 'ogg':
			case 'wmv':
			case 'mov':
				type = FileType.video;
				break;

			case 'mp3':
				type = FileType.audio;
				break;
			default:
				type = FileType.file;
		}

		return type;
	},

	formatFileSize(fileSize: number): string
	{
		let resultFileSize = fileSize;

		if (!resultFileSize || resultFileSize <= 0)
		{
			resultFileSize = 0;
		}

		const sizes = ['BYTE', 'KB', 'MB', 'GB', 'TB'];
		const KILOBYTE_SIZE = 1024;

		let position = 0;
		while (resultFileSize >= KILOBYTE_SIZE && position < sizes.length - 1)
		{
			resultFileSize /= KILOBYTE_SIZE;
			position++;
		}

		const phrase = Loc.getMessage(`IM_UTILS_FILE_SIZE_${sizes[position]}`);
		const roundedSize = Math.round(resultFileSize);

		return `${roundedSize} ${phrase}`;
	},

	getShortFileName(fileName: string, maxLength: number): string
	{
		if (!fileName || fileName.length < maxLength)
		{
			return fileName;
		}

		const DELIMITER = '...';
		const DOT_LENGTH = 1;
		const SYMBOLS_TO_TAKE_BEFORE_EXTENSION = 2;

		const extension = this.getFileExtension(fileName);
		const extensionLength = extension.length + DOT_LENGTH;
		const fileNameWithoutExtension = fileName.slice(0, -extensionLength);

		if (fileNameWithoutExtension.length <= maxLength)
		{
			return fileName;
		}

		const availableLength = maxLength - SYMBOLS_TO_TAKE_BEFORE_EXTENSION - DELIMITER.length;
		if (availableLength <= 0)
		{
			return fileName;
		}

		const firstPart = fileNameWithoutExtension.slice(0, availableLength).trim();
		const secondPart = fileNameWithoutExtension.slice(-SYMBOLS_TO_TAKE_BEFORE_EXTENSION).trim();

		return `${firstPart}${DELIMITER}${secondPart}.${extension}`;
	},

	getViewerDataAttributes(viewerAttributes): Object
	{
		if (!viewerAttributes)
		{
			return {};
		}

		const dataAttributes = {
			'data-viewer': true,
		};

		Object.entries(viewerAttributes).forEach(([key, value]) => {
			dataAttributes[`data-${Text.toKebabCase(key)}`] = value;
		});

		return dataAttributes;
	},

	createDownloadLink(text: string, urlDownload: string, fileName: string): HTMLAnchorElement
	{
		const anchorTag = Dom.create('a', { text });

		Dom.style(anchorTag, 'display', 'block');
		Dom.style(anchorTag, 'color', 'inherit');
		Dom.style(anchorTag, 'text-decoration', 'inherit');

		anchorTag.setAttribute('href', urlDownload);
		anchorTag.setAttribute('download', fileName);

		return anchorTag;
	},

	isImage(fileName: string): boolean
	{
		const extension = FileUtil.getFileExtension(fileName);
		const fileType = FileUtil.getFileTypeByExtension(extension);

		return fileType === FileType.image;
	},

	getBase64(file: File): Promise<string>
	{
		const reader = new FileReader();

		return new Promise((resolve) => {
			Event.bind(reader, 'load', () => {
				const fullBase64 = reader.result;
				const commaPosition = fullBase64.indexOf(',');
				const cutBase64 = fullBase64.slice(commaPosition + 1);
				resolve(cutBase64);
			});

			reader.readAsDataURL(file);
		});
	},

	resizeToFitMaxSize(width: number, height: number, maxSize: number): {width: number, height: number}
	{
		const aspectRatio = width / height;
		let newWidth = width;
		let newHeight = height;

		if (newHeight > maxSize)
		{
			newHeight = maxSize;
			newWidth = newHeight * aspectRatio;
		}

		if (newWidth > maxSize)
		{
			newWidth = maxSize;
			newHeight = newWidth / aspectRatio;
		}

		return { height: newHeight, width: newWidth };
	},
};
