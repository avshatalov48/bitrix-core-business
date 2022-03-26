import * as htmlToImage from 'html-to-image';
import {PageObject} from 'landing.pageobject';
import {Dom, Text, Type, Uri, Event, Reflection} from 'main.core';
import {fetchBlockImageNodes} from './internal/fetch-block-image-nodes';

import './css/style.css';

const proxyPath = '/bitrix/tools/landing/proxy.php';

/**
 * @memberOf BX.Landing
 */
export class Screenshoter
{
	static makeBlockScreenshot(blockId: number): Promise<File>
	{
		const editorWindow = PageObject.getEditorWindow();
		if (editorWindow !== window)
		{
			return editorWindow.BX.Landing.Screenshoter.makeBlockScreenshot(blockId);
		}

		const blockNode = document.querySelector(`#block${blockId}`);
		Dom.addClass(blockNode, 'landing-hide-ui-controls');

		const imagesMap = new Map();

		const animationHelper = Reflection.getClass('BX.Landing.OnscrollAnimationHelper');
		const animatedElements = animationHelper.getBlockAnimatedElements(blockNode);
		const animationCompleted = (() => {
			if (Type.isArrayFilled(animatedElements))
			{
				return Promise.all(
					animatedElements.map((element) => {
						return animationHelper.animateElement(element);
					}),
				);
			}

			return Promise.resolve();
		})();

		return animationCompleted
			.then(() => {
				return htmlToImage
					.toJpeg(blockNode, {backgroundColor: '#ffffff', cacheBust: true})
					.then((encodedImage) => {
						Dom.removeClass(blockNode, 'landing-hide-ui-controls');
						return fetch(encodedImage, {mode: 'no-cors'})
							.then((result) => {
								return result.blob();
							})
							.then((blob: Blob) => {
								imagesMap.forEach((imageValue, imageNode) => {
									imageNode.setValue(imageValue.sourceValue, true, true);
								});

								return new File(
									[blob],
									`block-${blockId}-preview.jpg`,
									{type: 'image/jpg'},
								);
							});
					});
			});
	}

	static makeElementScreenshot(element: HTMLElement): Promise<File>
	{
		const editorWindow = PageObject.getEditorWindow();
		if (editorWindow !== window)
		{
			return editorWindow.BX.Landing.Screenshoter.makeElementScreenshot(element);
		}

		return htmlToImage.toBlob(element).then((blob) => {
			return new File([blob], `screenshot-${Text.getRandom(16)}.png`, {type: 'image/png'});
		});
	}
}