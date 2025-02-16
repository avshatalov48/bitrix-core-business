import { test as base, expect } from '@playwright/test';
import { defaultTheme } from '../../../src/themes/default-theme';
import { selectAll } from './keyboard';

export const E2E_BROWSER = process.env.E2E_BROWSER;
export const IS_MAC = process.platform === 'darwin';
export const IS_WINDOWS = process.platform === 'win32';
export const IS_LINUX = !IS_MAC && !IS_WINDOWS;

export const test = base.extend({
	isPlainText: false,
	isRichText: true,
});

export { expect } from '@playwright/test';

export async function initializeTest({
	page,
	editorOptions = {},
	demoContent = false,
	lang = 'en',
})
{
	const options = encodeURI(JSON.stringify(editorOptions));
	let url = 'http://localhost/dev/ui/text-editor/e2e/full-featured.php';

	url += `?lang=${lang}`;

	if (demoContent)
	{
		url += '&demoContent=true';
	}

	url += `&editorOptions=${options}`;

	const response = await page.goto(url);
	if (response.status() === 500)
	{
		// eslint-disable-next-line no-param-reassign
		page.__retryCount = typeof(page.__retryCount) === 'undefined' ? 1 : page.__retryCount + 1;

		if (page.__retryCount > 5)
		{
			throw new Error('Too many retries!');
		}
		else
		{
			await initializeTest({ page, editorOptions, demoContent, lang });
		}
	}
}

export async function focusEditor(page)
{
	const editor = await page.locator('[data-lexical-editor="true"]');
	await editor.first().focus();

	return editor;
}

export function stripIndent(source)
{
	return source.replaceAll(/[\t\n]+/gm, '');
}

function wrapAndSlowDown(method, delay)
{
	return async function() {
		await new Promise((resolve) => setTimeout(resolve, delay));

		return method.apply(this, arguments);
	};
}

export async function repeat(times, cb)
{
	for (let i = 0; i < times; i++)
	{
		await cb();
	}
}

export async function clickSelectors(page, selectors)
{
	for (let i = 0; i < selectors.length; i++)
	{
		await click(page, selectors[i]);
	}
}

export async function assertHTML(
	page,
	expectedHtml,
	{ ignoreClasses = false, ignoreInlineStyles = false, preserveTab = false } = {},
	actualHtmlModificationsCallback = (actualHtml) => actualHtml,
)
{
	const expected = prettifyHTML(
		preserveTab ? expectedHtml.replaceAll(/\n+/gm, '') : expectedHtml.replaceAll(/[\t\n]+/gm, ''),
		{
			ignoreClasses,
			ignoreInlineStyles,
		},
	);

	await expect(async () => {
		const actualHtml = await page
			.locator('[data-lexical-editor="true"]')
			.first()
			.innerHTML()
		;

		let actual = prettifyHTML(actualHtml.replaceAll(/\n/gm, ''), { ignoreClasses, ignoreInlineStyles });
		actual = actualHtmlModificationsCallback(actual);

		expect(actual, 'innerHTML of contenteditable in page did not match').toEqual(expected);
	}).toPass({ intervals: [100, 250, 500], timeout: 2000 });
}

export async function assertSelection(page, expected)
{
	// Assert the selection of the editor matches the snapshot
	const selection = await page.evaluate(() => {
		const rootElement = document.querySelector('[data-lexical-editor="true"]');

		const getPathFromNode = (node) => {
			const path = [];
			if (node === rootElement)
			{
				return [];
			}

			while (node !== null)
			{
				const parent = node.parentNode;
				if (parent === null || node === rootElement)
				{
					break;
				}
				path.push([...parent.childNodes].indexOf(node));
				node = parent;
			}

			return path.reverse();
		};

		const { anchorNode, anchorOffset, focusNode, focusOffset } = window.getSelection();

		return {
			anchorOffset,
			anchorPath: getPathFromNode(anchorNode),
			focusOffset,
			focusPath: getPathFromNode(focusNode),
		};
	}, expected);

	expect(selection.anchorPath).toEqual(expected.anchorPath);
	expect(selection.focusPath).toEqual(expected.focusPath);

	if (Array.isArray(expected.anchorOffset))
	{
		const [start, end] = expected.anchorOffset;
		expect(selection.anchorOffset).toBeGreaterThanOrEqual(start);
		expect(selection.anchorOffset).toBeLessThanOrEqual(end);
	}
	else
	{
		expect(selection.anchorOffset).toEqual(expected.anchorOffset);
	}

	if (Array.isArray(expected.focusOffset))
	{
		const [start, end] = expected.focusOffset;
		expect(selection.focusOffset).toBeGreaterThanOrEqual(start);
		expect(selection.focusOffset).toBeLessThanOrEqual(end);
	}
	else
	{
		expect(selection.focusOffset).toEqual(expected.focusOffset);
	}
}

export async function isMac(page)
{
	return page.evaluate(
		() =>
			typeof window !== 'undefined'
			&& /Mac|iPod|iPhone|iPad/.test(window.navigator.platform),
	);
}

export async function supportsBeforeInput(page)
{
	return page.evaluate(() => {
		if ('InputEvent' in window)
		{
			return 'getTargetRanges' in new window.InputEvent('input');
		}

		return false;
	});
}

export async function keyDownCtrlOrMeta(page)
{
	if (await isMac(page))
	{
		await page.keyboard.down('Meta');
	}
	else
	{
		await page.keyboard.down('Control');
	}
}

export async function keyUpCtrlOrMeta(page)
{
	if (await isMac(page))
	{
		await page.keyboard.up('Meta');
	}
	else
	{
		await page.keyboard.up('Control');
	}
}

export async function keyDownCtrlOrAlt(page)
{
	if (await isMac(page))
	{
		await page.keyboard.down('Alt');
	}
	else
	{
		await page.keyboard.down('Control');
	}
}

export async function keyUpCtrlOrAlt(page)
{
	if (await isMac(page))
	{
		await page.keyboard.up('Alt');
	}
	else
	{
		await page.keyboard.up('Control');
	}
}

export async function copyToClipboard(page)
{
	return page.evaluate(() => {
		const clipboardData = {};
		const editor = document.querySelector('[data-lexical-editor="true"]');
		const copyEvent = new ClipboardEvent('copy');
		Object.defineProperty(copyEvent, 'clipboardData', {
			value: {
				setData(type, value)
				{
					clipboardData[type] = value;
				},
			},
		});

		editor.dispatchEvent(copyEvent);

		return clipboardData;
	});
}

async function pasteWithClipboardDataFromPage(page, clipboardData)
{
	const canUseBeforeInput = await supportsBeforeInput(page);

	await page.evaluate(
		async ({
			clipboardData: _clipboardData,
			canUseBeforeInput: _canUseBeforeInput,
		}) => {
			const files = [];
			const items = [];
			for (const [clipboardKey, clipboardValue] of Object.entries(_clipboardData))
			{
				if (clipboardKey.startsWith('playwright/base64'))
				{
					delete _clipboardData[clipboardKey];
					const [base64, type] = clipboardValue;
					const res = await fetch(base64);
					const blob = await res.blob();
					files.push(new File([blob], 'file', { type }));
				}
				else
				{
					items.push({
						type: clipboardKey,
						kind: 'string',
					});
				}
			}

			let eventClipboardData;
			if (files.length > 0)
			{
				eventClipboardData = {
					files,
					getData(type, value)
					{
						return _clipboardData[type];
					},
					types: [...Object.keys(_clipboardData), 'Files'],
					items,
				};
			}
			else
			{
				eventClipboardData = {
					files,
					getData(type, value)
					{
						return _clipboardData[type];
					},
					types: Object.keys(_clipboardData),
					items,
				};
			}

			const editor = document.querySelector('[data-lexical-editor="true"]');
			const pasteEvent = new ClipboardEvent('paste', { bubbles: true, cancelable: true });

			Object.defineProperty(pasteEvent, 'clipboardData', { value: eventClipboardData });
			editor.dispatchEvent(pasteEvent);

			if (!pasteEvent.defaultPrevented && _canUseBeforeInput)
			{
				const inputEvent = new InputEvent('beforeinput', { bubbles: true, cancelable: true });
				Object.defineProperty(inputEvent, 'inputType', { value: 'insertFromPaste' });
				Object.defineProperty(inputEvent, 'dataTransfer', { value: eventClipboardData });
				editor.dispatchEvent(inputEvent);
			}
		},
		{ canUseBeforeInput, clipboardData },
	);
}

/**
 * @param {import('@playwright/test').Page} page
 */
export async function pasteFromClipboard(page, clipboardData)
{
	if (clipboardData === undefined)
	{
		await keyDownCtrlOrMeta(page);
		await page.keyboard.press('v');
		await keyUpCtrlOrMeta(page);

		return;
	}

	await pasteWithClipboardDataFromPage(page, clipboardData);
}

export async function sleep(delay)
{
	await new Promise((resolve) => {
		setTimeout(resolve, delay);
	});
}

// Fair time for the browser to process a newly inserted image
export async function sleepInsertImage(count = 1)
{
	await sleep(1000 * count);
}

export async function getHTML(page, selector = 'div[contenteditable="true"]')
{
	return await locate(page, selector).innerHTML();
}

export async function waitForSelector(page, selector, options)
{
	await page.waitForSelector(selector, options);
}

export function locate(page, selector)
{
	return page.locator(selector);
}

export async function selectorBoundingBox(page, selector)
{
	await locate(page, selector).boundingBox();
}

export async function click(page, selector, options)
{
	const frame = page;
	await frame.waitForSelector(selector, options);
	await frame.click(selector, options);
}

export async function doubleClick(page, selector, options)
{
	const frame = page;
	await frame.waitForSelector(selector, options);
	await frame.dblclick(selector, options);
}

export async function focus(page, selector, options)
{
	await locate(page, selector).focus(options);
}

export async function clearEditor(page)
{
	await selectAll(page);
	await page.keyboard.press('Backspace');
	await page.keyboard.press('Backspace');
}

export async function clickToolbarButton(page, buttonId)
{
	await page.locator(`.ui-text-editor-toolbar-button:has(> .--${buttonId})`).click();
}

export async function insertSampleImage(page)
{
	await clickToolbarButton(page, 'incert-image');

	await page.getByTestId('image-dialog-textbox').fill('https://i.pinimg.com/564x/3d/d8/3f/3dd83fc6cfce54d3ad2bcc992cd5ed18.jpg');
	await page.getByTestId('image-dialog-save-btn').click();

	await waitForSelector(page, `.${defaultTheme.image.img}`);
}

export async function insertYoutubeVideo(page)
{
	await clickToolbarButton(page, 'insert-video');

	await page.getByTestId('video-dialog-textbox').fill('https://www.youtube.com/watch?v=_6j7HRZFXP4');
	await page.getByTestId('video-dialog-save-btn').click();
}

export async function insertTable(page, rows = 2, columns = 3)
{
	await clickToolbarButton(page, 'table-editor');
	await page.locator(`[data-column='${columns}'][data-row='${rows}']`).click();
}

export async function insertCodeBlock(page)
{
	await clickToolbarButton(page, 'enclose-text-in-code-tag');
}

export async function toggleCodeBlock(page)
{
	await clickToolbarButton(page, 'enclose-text-in-code-tag');
}

export async function insertSpoiler(page)
{
	await clickToolbarButton(page, 'insert-spoiler');
}

export async function toggleLink(page, link)
{
	await clickToolbarButton(page, 'link-3');
	await page.getByTestId('link-textbox-input').fill(link);
	await page.getByTestId('save-link-btn').click();
}

export async function mouseMoveToSelector(page, selector)
{
	const { x, width, y, height } = await selectorBoundingBox(page, selector);
	await page.mouse.move(x + width / 2, y + height / 2);
}

export async function dragMouse(
	page,
	fromBoundingBox,
	toBoundingBox,
	positionStart = 'middle',
	positionEnd = 'middle',
	mouseUp = true,
	slow = false,
)
{
	let fromX = fromBoundingBox.x;
	let fromY = fromBoundingBox.y;
	if (positionStart === 'middle')
	{
		fromX += fromBoundingBox.width / 2;
		fromY += fromBoundingBox.height / 2;
	}
	else if (positionStart === 'end')
	{
		fromX += fromBoundingBox.width;
		fromY += fromBoundingBox.height;
	}
	await page.mouse.move(fromX, fromY);
	await page.mouse.down();

	let toX = toBoundingBox.x;
	let toY = toBoundingBox.y;
	if (positionEnd === 'middle')
	{
		toX += toBoundingBox.width / 2;
		toY += toBoundingBox.height / 2;
	}
	else if (positionEnd === 'end')
	{
		toX += toBoundingBox.width;
		toY += toBoundingBox.height;
	}

	if (slow)
	{
		//simulate more than 1 mouse move event to replicate human slow dragging
		await page.mouse.move((fromX + toX) / 2, (fromY + toY) / 2);
	}

	await page.mouse.move(toX, toY);

	if (mouseUp)
	{
		await page.mouse.up();
	}
}

export function prettifyHTML(string, { ignoreClasses, ignoreInlineStyles } = {})
{
	let output = string;

	if (ignoreClasses)
	{
		output = output.replaceAll(/\sclass="([^"]*)"/g, '');
	}

	if (ignoreInlineStyles)
	{
		output = output.replaceAll(/\sstyle="([^"]*)"/g, '');
	}

	output = output.replaceAll(/\sdir="([^"]*)"/g, '');
	output = output.replaceAll(/\sui-text-editor__ltr/g, '');
	output = output.replaceAll(/\sui-text-editor__rtl/g, '');
	output = output.replaceAll(/\sdata-placeholder="([^"]*)"/g, '');
	output = output.replaceAll(/\s__playwright_target__="[^"]+"/g, '');

	return output.trim();
}

export async function enableCompositionKeyEvents(page)
{
	const targetPage = page;
	await targetPage.evaluate(() => {
		window.addEventListener(
			'compositionstart',
			() => {
				document.activeElement.dispatchEvent(
					new KeyboardEvent('keydown', {
						bubbles: true,
						cancelable: true,
						key: 'Unidentified',
						keyCode: 220,
					}),
				);
			},
			true,
		);
	});
}

export async function pressToggleBold(page)
{
	await keyDownCtrlOrMeta(page);
	await page.keyboard.press('b');
	await keyUpCtrlOrMeta(page);
}

export async function pressToggleItalic(page)
{
	await keyDownCtrlOrMeta(page);
	await page.keyboard.press('i');
	await keyUpCtrlOrMeta(page);
}

export async function pressToggleUnderline(page)
{
	await keyDownCtrlOrMeta(page);
	await page.keyboard.press('u');
	await keyUpCtrlOrMeta(page);
}

export async function dragDraggableMenuTo(
	page,
	toSelector,
	positionStart = 'middle',
	positionEnd = 'middle',
)
{
	await dragMouse(
		page,
		await selectorBoundingBox(page, '.draggable-block-menu'),
		await selectorBoundingBox(page, toSelector),
		positionStart,
		positionEnd,
	);
}
