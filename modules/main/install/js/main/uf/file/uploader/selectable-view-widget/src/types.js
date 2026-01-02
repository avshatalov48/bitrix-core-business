import { ButtonIcon } from 'ui.buttons';

export type SelectableViewWidgetOptions = {
	fileItems: FileItemOptions[],
	viewId: ?string,
};

export type FileItemOptions = {
	name: string,
	extension: string,
	isImage: boolean,
	url: string,
	urlForViewer: string,
	attributes: Object,
};

export type ViewOptions = {
	id: string,
	title: string,
	icon: $Values<typeof ButtonIcon>,
};
