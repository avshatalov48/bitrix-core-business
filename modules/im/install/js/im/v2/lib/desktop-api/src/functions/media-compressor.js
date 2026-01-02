import { Type } from 'main.core';

interface MediaCompressor
{
	compress(file: File | Blob): Promise<any>;
	cancel(): void;
	removeCompressedFile(): void;
}

export const mediaCompressorFunctions = {
	isMediaCompressorAvailable(): boolean
	{
		return this.getApiVersion() >= 88 && Type.isFunction(window.BXMediaCompressor);
	},

	createMediaCompressor(): MediaCompressor
	{
		return new window.BXMediaCompressor();
	},
};
