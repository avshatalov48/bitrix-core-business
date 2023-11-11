import { isResizableImage, resizeImage } from 'ui.uploader.core';

import { Logger } from 'im.v2.lib.logger';
import { RestMethod } from 'im.v2.const';
import { runAction } from 'im.v2.lib.rest';
import { Utils } from 'im.v2.lib.utils';

const MAX_AVATAR_SIZE = 180;

export class UpdateService
{
	async prepareAvatar(avatarFile: File): Promise<File>
	{
		if (!isResizableImage(avatarFile))
		{
			// eslint-disable-next-line no-console
			return Promise.reject(new Error('UpdateService: prepareAvatar: incorrect image'));
		}

		const { preview: resizedAvatar } = await resizeImage(avatarFile, {
			width: MAX_AVATAR_SIZE,
			height: MAX_AVATAR_SIZE,
		});

		return resizedAvatar;
	}

	async changeAvatar(chatId: number, avatarFile: File): Promise
	{
		Logger.warn('ChatService: changeAvatar', chatId, avatarFile);
		const avatarInBase64 = await Utils.file.getBase64(avatarFile);

		return runAction(RestMethod.imV2ChatUpdate, {
			data: {
				id: chatId,
				fields: { avatar: avatarInBase64 },
			},
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('ChatService: changeAvatar error:', error);
			throw new Error(error);
		});
	}
}
