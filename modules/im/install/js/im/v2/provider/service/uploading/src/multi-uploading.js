import { Utils } from 'im.v2.lib.utils';
import { Type } from 'main.core';

import { UploadingService } from './uploading';

import type { UploaderFile } from 'ui.uploader.core';
import type { UploadFilesParams, UploadParams } from './types/uploading';

type AddFilesResult = {
	uploaderFiles: Array<UploaderFile>,
	uploaderId: string,
	loadAllComplete: Promise<any>,
	uploadAllComplete: Promise<any>,
};

type MakeChunksOptions<T> = {
	files: Array<T>,
	chunkSize?: number,
	maxFilesCount?: number,
};

export type MultiUploadingResult = {
	uploadingId: string,
	uploaderIds: Array<string>,
	sourceFilesCount: number,
	loadAllComplete: Promise<any>,
	uploadAllComplete: Promise<any>,
};

const MAX_FILES_COUNT_IN_ONE_MESSAGE = 10;
const MAX_FILES_COUNT = 100;
const MAX_PARALLEL_LOADS = 10;
const MAX_PARALLEL_UPLOADS = 3;

export class MultiUploadingService
{
	static makeChunks<T>(options: MakeChunksOptions): Array<Array<T>>
	{
		const {
			files,
			chunkSize = MAX_FILES_COUNT_IN_ONE_MESSAGE,
			maxFilesCount = MAX_FILES_COUNT,
		}: MakeChunksOptions = options;

		const chunks: Array<Array<T>> = [];
		if (Type.isArray(files))
		{
			const preparedFiles: Array<T> = files.slice(0, maxFilesCount);
			for (let i = 0; i < preparedFiles.length; i += chunkSize)
			{
				const chunk = preparedFiles.slice(i, i + chunkSize);
				chunks.push(chunk);
			}
		}

		return chunks;
	}

	static getMaxParallelLoads<T>(chunks: Array<Array<T>>): number
	{
		return Math.floor(MAX_PARALLEL_LOADS / chunks.length);
	}

	#getUploadingService(): UploadingService
	{
		return UploadingService.getInstance();
	}

	#createUploadingId(): string
	{
		return Utils.text.getUuidV4();
	}

	async #addFiles(params: UploadFilesParams): Promise<AddFilesResult>
	{
		return this.#getUploadingService().addFiles(params);
	}

	async upload({ files, dialogId, autoUpload, sendAsFile }: UploadParams): Promise<MultiUploadingResult>
	{
		const chunks = MultiUploadingService.makeChunks({
			files,
		});

		const addFilesResults: Array<AddFilesResult> = await Promise.all(
			chunks.map((chunk: Array<File>) => {
				return this.#addFiles({
					files: chunk,
					maxParallelLoads: MultiUploadingService.getMaxParallelLoads(chunks),
					maxParallelUploads: MAX_PARALLEL_UPLOADS,
					dialogId,
					autoUpload,
					sendAsFile,
				});
			}),
		);

		const uploadingId: string = this.#createUploadingId();
		const uploaderIds: Array<string> = [];
		const loadCompletePromises: Array<Promise<any>> = [];
		const uploadCompletePromises: Array<Promise<any>> = [];

		addFilesResults.forEach(({ uploaderId, uploaderFiles, loadAllComplete, uploadAllComplete }) => {
			if (Type.isArrayFilled(uploaderFiles))
			{
				uploaderIds.push(uploaderId);
				loadCompletePromises.push(loadAllComplete);
				uploadCompletePromises.push(uploadAllComplete);
			}
		});

		const loadAllComplete: Promise<any> = Promise.all(loadCompletePromises);
		const uploadAllComplete: Promise<any> = Promise.all(uploadCompletePromises);
		const sourceFilesCount: number = files.length;

		return {
			uploadingId,
			uploaderIds,
			sourceFilesCount,
			loadAllComplete,
			uploadAllComplete,
		};
	}
}
