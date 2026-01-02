import { Dom, Type, Text } from 'main.core';
import {
	NodeFormatter,
	type ConvertCallbackOptions,
	type NodeFormatterOptions,
} from 'ui.bbcode.formatter';
import type { HtmlFormatter } from 'ui.bbcode.formatter.html-formatter';
import type { UploaderFileInfo } from 'ui.uploader.core';
import { createImageNode } from '../../helpers/create-image-node';
import { createVideoNode } from '../../helpers/create-video-node';

export class FileNodeFormatter extends NodeFormatter
{
	constructor(options: NodeFormatterOptions = {})
	{
		const formatter: HtmlFormatter = options.formatter;
		const fileMode = formatter.getFileMode();
		const viewerGroupBy = Text.getRandom();

		super({
			name: fileMode || '__unknown__',
			convert({ node, data }: ConvertCallbackOptions): HTMLElement {
				if (fileMode === null)
				{
					return node;
				}
				// [DISK FILE ID=n14194]
				// [DISK FILE ID=14194]

				// [FILE ID=5b87ba3b-edb1-49df-a840-50d17b6c3e8c.fbbdd477d5ff19d61...a875e731fa89cfd1e1]
				// [FILE ID=14194]
				const serverFileId = node.getAttribute('id');
				const createTextNode = () => {
					return document.createTextNode(node.toString());
				};

				if (
					!Type.isStringFilled(serverFileId)
					|| (fileMode === 'disk' && !/^n?\d+$/i.test(serverFileId))
					|| (fileMode === 'file' && !/^(\d+|[\da-f-]{36}\.[\da-f]{32,})$/i.test(serverFileId))
				)
				{
					return createTextNode();
				}

				const info: UploaderFileInfo = data?.files?.find((file: UploaderFileInfo): boolean => {
					return file.serverFileId.toString() === serverFileId.toString()
						|| `n${file.customData?.objectId.toString()}` === serverFileId.toString()
					;
				});

				if (!info)
				{
					return createTextNode();
				}

				const viewerAttrsExist = Type.isPlainObject(info.viewerAttrs);
				const viewerAttrs = viewerAttrsExist ? { ...info.viewerAttrs } : {};
				if (
					viewerAttrsExist
					&& !Type.isStringFilled(viewerAttrs.viewerGroupBy)
					&& Type.isUndefined(viewerAttrs.viewerSeparateItem)
				)
				{
					viewerAttrs.viewerGroupBy = viewerGroupBy;
				}

				if (info.isImage)
				{
					let width = Text.toInteger(node.getAttribute('width'));
					let height = Text.toInteger(node.getAttribute('height'));

					width = Type.isNumber(width) && width > 0 ? Math.round(width) : info.serverPreviewWidth;
					height = Type.isNumber(height) && height > 0 ? Math.round(height) : info.serverPreviewHeight;

					return createImageNode({
						width,
						height,
						src: info.serverPreviewUrl,
						viewerAttrs,
					});
				}

				if (info.isVideo)
				{
					let width = Number(node.getAttribute('width'));
					let height = Number(node.getAttribute('height'));
					width = Type.isNumber(width) && width > 0 ? Math.round(width) : 600;
					height = Type.isNumber(height) && height > 0 ? Math.round(height) : null;

					return createVideoNode({
						url: info.downloadUrl,
						viewerAttrs,
						width,
						height,
					});
				}

				return Dom.create({
					tag: 'a',
					attrs: {
						href: info.downloadUrl,
						className: 'ui-typography-link',
					},
					text: info.name || 'unknown',
					dataset: {
						fileId: info.serverFileId,
						fileInfo: JSON.stringify(info),
						...viewerAttrs,
					},
				});
			},
			...options,
		});
	}
}
