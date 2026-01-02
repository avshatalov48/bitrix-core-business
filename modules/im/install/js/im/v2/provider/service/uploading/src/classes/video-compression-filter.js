import { Core } from 'im.v2.application.core';
import { FileStatus } from 'im.v2.const';
import { DesktopApi } from 'im.v2.lib.desktop-api';
import { type UploaderFile, Filter, FileEvent } from 'ui.uploader.core';

import type { Store } from 'ui.vue3.vuex';

export class UploaderVideoCompressionFilter extends Filter
{
	#store: Store = null;

	constructor(...args)
	{
		super(...args);
		this.#store = Core.getStore();
	}

	async apply(file: UploaderFile): Promise<void>
	{
		if (
			file.isVideo()
			&& !file.shouldTreatImageAsFile()
			&& DesktopApi.isMediaCompressorAvailable()
		)
		{
			await this.setModelFileStatus(file.getId(), FileStatus.preparing);
			const compressor = DesktopApi.createMediaCompressor();

			const cancelPromise: Promise<null> = new Promise((resolve) => {
				file.subscribeOnce(FileEvent.REMOVE_COMPLETE, () => {
					compressor.cancel();
					compressor.removeCompressedFile();
					file.remove();
					resolve(null);
				});
			});

			const compressedFile: ?File = await Promise.race([
				compressor.compress(file.getBinary()),
				cancelPromise,
			]);

			if (compressedFile)
			{
				file.setFile(compressedFile);
				file.setName(compressedFile.name);
				file.subscribeOnce(FileEvent.UPLOAD_COMPLETE, () => {
					compressor.removeCompressedFile();
				});
			}
		}
	}

	setModelFileStatus(id: string, status: $Values<FileStatus>): Promise<any>
	{
		return this.#store.dispatch('files/update', {
			id,
			fields: { status },
		});
	}
}
