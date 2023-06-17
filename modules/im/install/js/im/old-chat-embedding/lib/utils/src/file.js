import {Loc} from 'main.core';

import {FileType} from 'im.old-chat-embedding.const';

export const FileUtil = {
	getFileExtension(fileName: string): string
	{
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

		switch(extension.toString())
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
		}

		return icon;
	},

	getFileTypeByExtension(extension: string): string
	{
		let type = FileType.file;

		switch (extension)
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
		}

		return type;
	},

	formatFileSize(fileSize: number): string
	{
		if (!fileSize || fileSize <= 0)
		{
			fileSize = 0;
		}

		const sizes = ['BYTE', 'KB', 'MB', 'GB', 'TB'];
		const KILOBYTE_SIZE = 1024;

		let position = 0;
		while (fileSize >= KILOBYTE_SIZE && position < sizes.length - 1)
		{
			fileSize /= KILOBYTE_SIZE;
			position++;
		}

		const phrase = Loc.getMessage(`IM_UTILS_FILE_SIZE_${sizes[position]}`);
		const roundedSize = Math.round(fileSize);

		return `${roundedSize} ${phrase}`;
	},

	getShortFileName(fileName: string, maxLength: number): string
	{
		if (!fileName || fileName.length < maxLength)
		{
			return fileName;
		}

		const DOT_LENGTH = 1;
		const SYMBOLS_TO_TAKE_BEFORE_EXTENSION = 10;

		const extension = this.getFileExtension(fileName);
		const symbolsToTakeFromEnd = extension.length + DOT_LENGTH + SYMBOLS_TO_TAKE_BEFORE_EXTENSION;
		const secondPart = fileName.slice(-symbolsToTakeFromEnd);
		const firstPart = fileName.slice(0, maxLength - secondPart.length - DOT_LENGTH * 3);

		return `${firstPart.trim()}...${secondPart.trim()}`;
	},

	getViewerDataAttributes(viewerAttributes): Object
	{
		if (!viewerAttributes)
		{
			return {};
		}

		return {
			'data-viewer': true,
			'data-viewer-type': viewerAttributes.viewerType,
			'data-object-id': viewerAttributes.objectId,
			'data-src': viewerAttributes.src,
			'data-viewer-group-by': viewerAttributes.viewerGroupBy,
			'data-title': viewerAttributes.title,
			'data-actions': viewerAttributes.actions,
		};
	},

	isImage(fileName: string): boolean
	{
		const extension = FileUtil.getFileExtension(fileName);
		const fileType = FileUtil.getFileTypeByExtension(extension);

		return fileType === FileType.image;
	}
};