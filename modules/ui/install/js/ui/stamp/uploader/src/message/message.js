import {Cache, Tag, Loc} from 'main.core';

import './css/style.css';

export default class Message
{
	cache = new Cache.MemoryCache();

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="ui-stamp-uploader-message">
					<div class="ui-stamp-uploader-message-icon"></div>
					<div class="ui-stamp-uploader-message-text">
						<div class="ui-stamp-uploader-message-text-header">
							${Loc.getMessage('UI_STAMP_UPLOADER_SLIDER_MESSAGE_TITLE')}
						</div>
						<div class="ui-stamp-uploader-message-text-description">
							${Loc.getMessage('UI_STAMP_UPLOADER_SLIDER_MESSAGE_DESCRIPTION')}
						</div>
					</div>
				</div>
			`;
		});
	}
}