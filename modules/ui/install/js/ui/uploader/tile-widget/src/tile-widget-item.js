import type { UploaderFileInfo } from 'ui.uploader.core';

export type TileWidgetItem = UploaderFileInfo & {
	tileWidgetData: {
		selected?: boolean,
	}
};