import { Loc } from 'main.core';

const SIZE_LOWER_THRESHOLD = 1024 * 1024 * 2;

export const formatProgressLabel = (progress: number, totalBytes: number): string => {
	if (totalBytes < SIZE_LOWER_THRESHOLD)
	{
		return Loc.getMessage('IM_ELEMENTS_PROGRESSBAR_UPLOAD_LOADING');
	}

	const bytesSent = (totalBytes / 100) * progress;
	const megaBytesSent = `${convertBytesToMegaBytes(bytesSent)} ${Loc.getMessage('IM_ELEMENTS_PROGRESSBAR_SIZE_MB')}`;
	const megaBytesTotal = `${convertBytesToMegaBytes(totalBytes)} ${Loc.getMessage('IM_ELEMENTS_PROGRESSBAR_SIZE_MB')}`;

	return `${megaBytesSent} / ${megaBytesTotal}`;
};

const convertBytesToMegaBytes = (bytes: number): number => {
	return (bytes / 1024 / 1024).toFixed(2);
};
