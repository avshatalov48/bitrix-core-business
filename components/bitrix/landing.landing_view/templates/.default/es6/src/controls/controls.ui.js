import { Event, Tag } from 'main.core';
import { Menu, MenuItem, MenuManager } from 'main.popup';

import { Loc } from './controls.loc';

type LeftContainerOptions = {
	designerBlockClick: ?() => {},
	styleBlockClick: ?() => {},
	editBlockClick: ?() => {},
};

type RightContainerOptions = {
	blockId: number,
	state: boolean,
	sortDownBlockClick: ?() => {},
	sortUpBlockClick: ?() => {},
	removeBlockClick: ?() => {},
	onOpenAdditionalMenu: ?() => {},
	changeStateClick: ?() => {},
	cutClick: ?() => {},
	copyClick: ?() => {},
	pasteClick: ?() => {},
	feedbackClick: ?() => {},
	saveInLibrary: ?() => {},
};

type AdditionalActions = {
	state: boolean,
	onOpenAdditionalMenu: ?() => {},
	changeStateClick: ?() => {},
	cutClick: ?() => {},
	copyClick: ?() => {},
	pasteClick: ?() => {},
	feedbackClick: ?() => {},
	saveInLibrary: ?() => {},
};

export class UI
{
	static pendingMenuItems = {};

	/**
	 * Till Menu for this block not show, sets predefined prop value for menu item.
	 *
	 * @param {number} blockId
	 * @param {string} itemCode
	 * @param {string} itemProp
	 * @param {mixed} value
	 */
	static setPendingMenuItemValue(blockId: number, itemCode: string, itemProp: string, value)
	{
		if (!UI.pendingMenuItems[blockId])
		{
			UI.pendingMenuItems[blockId] = {};
		}
		if (!UI.pendingMenuItems[blockId][itemCode])
		{
			UI.pendingMenuItems[blockId][itemCode] = {};
		}

		UI.pendingMenuItems[blockId][itemCode][itemProp] = value;
	}

	/**
	 * Returns predefined prop value for menu item (if exists).
	 *
	 * @param {number} blockId
	 * @param {string} itemCode
	 * @param {string} itemProp
	 */
	static getPendingMenuItemValue(blockId: number, itemCode: string, itemProp: string)
	{
		if (UI.pendingMenuItems[blockId] && UI.pendingMenuItems[blockId][itemCode])
		{
			return UI.pendingMenuItems[blockId][itemCode][itemProp] || null;
		}

		return null;
	}

	/**
	 * Returns Designer Button.
	 *
	 * @param {() => {}} onClick Click handler.
	 * @return {HTMLButtonElement}
	 */
	static getDesignerBlockButton(onClick: ?() => {}): HTMLButtonElement
	{
		const title = Loc.getMessage('LANDING_TPL_EXT_BUTTON_DESIGNER_BLOCK');
		const button = Tag.render`
			<button class="landing-ui-button landing-ui-button-action --separate${onClick ? '' : ' landing-ui-disabled'}" type="button" title="${title}">
				<span class="landing-ui-button-text">${title}</span>
			</button>
		`;

		if (onClick)
		{
			Event.bind(button, 'click', onClick);
		}

		return button;
	}

	/**
	 * Returns Style Block Button.
	 *
	 * @param {() => {}} onClick Click handler.
	 * @return {HTMLButtonElement}
	 */
	static getStyleBlockButton(onClick: ?() => {}): HTMLButtonElement
	{
		const label = Loc.getMessage('LANDING_TPL_EXT_BUTTON_STYLE_BLOCK');
		const title = Loc.getMessage('LANDING_TPL_EXT_BUTTON_STYLE_BLOCK_TITLE');
		const button = Tag.render`
			<button class="landing-ui-button landing-ui-button-action --separate${onClick ? '' : ' landing-ui-disabled'}" type="button" title="${title}">
				<span class="landing-ui-button-text">${label}</span>
			</button>
		`;

		if (onClick)
		{
			Event.bind(button, 'click', onClick);
		}

		return button;
	}

	/**
	 * Returns Edit Block Button.
	 *
	 * @param {() => {}} onClick Click handler.
	 * @return {HTMLButtonElement}
	 */
	static getEditBlockButton(onClick: ?() => {}): HTMLButtonElement
	{
		//data-id="content"
		const label = Loc.getMessage('LANDING_TPL_EXT_BUTTON_EDIT_BLOCK');
		const title = Loc.getMessage('LANDING_TPL_EXT_BUTTON_EDIT_BLOCK_TITLE');
		const button = Tag.render`
			<button class="landing-ui-button landing-ui-button-action --separate${onClick ? '' : ' landing-ui-disabled'}" type="button" title="${title}" data-id="content">
				<span class="landing-ui-button-text">${label}</span>
			</button>
		`;

		if (onClick)
		{
			Event.bind(button, 'click', onClick);
		}

		return button;
	}

	/**
	 * Returns left container for block's actions.
	 *
	 * @param {LeftContainerOptions} options Options for left container.
	 * @return {HTMLDivElement}
	 */
	static getLeftContainer(options: LeftContainerOptions): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-ui-external-left-container">
				<div class="landing-ui-external-left-top-hr"></div>
				<div class="landing-ui-external-body">
					<div class="landing-ui-external-panel">
						${UI.getDesignerBlockButton(options.designerBlockClick)}
						${UI.getStyleBlockButton(options.styleBlockClick)}
						${UI.getEditBlockButton(options.editBlockClick)}
					</div>
				</div>
				<div class="landing-ui-external-left-bottom-hr"></div>
			</div>
		`;
	}

	/**
	 * Returns Sort Down Button.
	 *
	 * @param {() => {}} onClick Click handler.
	 * @return {HTMLButtonElement}
	 */
	static getSortDownBlockButton(onClick: ?() => {}): HTMLButtonElement
	{
		const title = Loc.getMessage('LANDING_TPL_EXT_BUTTON_DOWN_BLOCK');
		const button = Tag.render`
			<button class="landing-ui-button landing-ui-button-action${onClick ? '' : ' landing-ui-disabled'}" type="button" data-id="down" title="${title}"><span class="landing-ui-button-text">&nbsp;</span></button>
		`;

		if (onClick)
		{
			Event.bind(button, 'click', onClick);
		}

		return button;
	}

	/**
	 * Returns Sort Up Button.
	 *
	 * @param {() => {}} onClick Click handler.
	 * @return {HTMLButtonElement}
	 */
	static getSortUpBlockButton(onClick: ?() => {}): HTMLButtonElement
	{
		const title = Loc.getMessage('LANDING_TPL_EXT_BUTTON_UP_BLOCK');
		const button = Tag.render`
			<button class="landing-ui-button landing-ui-button-action${onClick ? '' : ' landing-ui-disabled'}" type="button" data-id="up" title="${title}"><span class="landing-ui-button-text">&nbsp;</span></button>
		`;

		if (onClick)
		{
			Event.bind(button, 'click', onClick);
		}

		return button;
	}

	/**
	 * Returns Additional Items Menu for Block.
	 *
	 * @param {number} blockId Block id.
	 * @return {Menu}
	 */
	static getBlockAdditionalMenu(blockId: number): Menu
	{
		return MenuManager.getMenuById('block_actions_' + blockId);
	}

	/**
	 * Closes Additional Items Menu for Block.
	 *
	 * @param {number} blockId Block id.
	 */
	static closeBlockAdditionalMenu(blockId: number)
	{
		const menu = UI.getBlockAdditionalMenu(blockId);
		if (menu)
		{
			menu.close();
		}
	}

	/**
	 * Change state for Additional Menu Item 'Activate'.
	 *
	 * @param {number} blockId Block id.
	 * @param {boolean} state State.
	 */
	static changeStateMenuItem(blockId: number, state: boolean)
	{
		const menu = UI.getBlockAdditionalMenu(blockId);
		const title = Loc.getMessage(
			!state
				? 'LANDING_TPL_EXT_BUTTON_ACTIONS_SHOW'
				: 'LANDING_TPL_EXT_BUTTON_ACTIONS_HIDE'
		);

		if (menu)
		{
			BX.Landing.Utils.setTextContent(menu.getMenuItem('show_hide').getLayout()['text'], title);
		}
		else
		{
			UI.setPendingMenuItemValue(blockId, 'show_hide', 'state', state);
		}
	}

	/**
	 * Enables/disables paste-item.
	 *
	 * @param {number} blockId Block id.
	 * @param {boolean} enablePaste Flag.
	 */
	static changePasteMenuItem(blockId: number, enablePaste: boolean)
	{
		const menu = UI.getBlockAdditionalMenu(blockId);
		if (menu)
		{
			const item = menu.getMenuItem('paste');
			if (item)
			{
				if (enablePaste)
				{
					item.enable();
				}
				else
				{
					item.disable();
				}
			}
		}
		else
		{
			UI.setPendingMenuItemValue(blockId, 'paste', 'disabled', !enablePaste);
		}
	}

	/**
	 * Returns List of Actions for Block.
	 *
	 * @param {number} blockId Block id.
	 * @param {AdditionalActions} actions Additional actions for Block.
	 * @return {HTMLButtonElement}
	 */
	static getActionsList(blockId: number, actions: AdditionalActions): HTMLButtonElement
	{
		const label = Loc.getMessage('LANDING_TPL_EXT_BUTTON_ACTIONS_BLOCK');
		const title = Loc.getMessage('LANDING_TPL_EXT_BUTTON_ACTIONS_BLOCK_TITLE');

		const actionButton = Tag.render`
			<button class="landing-ui-button landing-ui-button-action" type="button" data-id="actions" title="${title}">
				<span class="landing-ui-button-text">${label}</span>
			</button>
		`;

		// when click is occurred open exists menu or create new one
		Event.bind(actionButton, 'click', (event) => {

			if (actions.onOpenAdditionalMenu)
			{
				actions.onOpenAdditionalMenu(blockId);
				event.stopPropagation();
			}

			const menu = UI.getBlockAdditionalMenu(blockId);
			if (menu)
			{
				menu.show();
				return;
			}

			MenuManager.create({
				id: 'block_actions_' + blockId,
				bindElement: actionButton,
				className: 'landing-ui-block-actions-popup',
				angle: { position: 'top', offset: 95 },
				offsetTop: -6,
				offsetLeft: -26,
				items: [
					new MenuItem({
						id: 'show_hide',
						disabled: !actions.changeStateClick,
						text: Loc.getMessage(
							(actions.state || UI.getPendingMenuItemValue(blockId, 'show_hide', 'state'))
								? 'LANDING_TPL_EXT_BUTTON_ACTIONS_HIDE'
								: 'LANDING_TPL_EXT_BUTTON_ACTIONS_SHOW'
						),
						onclick: () => {
							actions.changeStateClick();
						}
					}),
					new MenuItem({
						id: 'cut',
						disabled: !actions.cutClick,
						text: Loc.getMessage('LANDING_TPL_EXT_BUTTON_ACTIONS_CUT'),
						onclick: () => {
							actions.cutClick();
						}
					}),
					new MenuItem({
						id: 'copy',
						disabled: !actions.copyClick,
						text: Loc.getMessage('LANDING_TPL_EXT_BUTTON_ACTIONS_COPY'),
						onclick: () => {
							actions.copyClick();
						}
					}),
					new MenuItem({
						id: 'paste',
						disabled: !actions.pasteClick || UI.getPendingMenuItemValue(blockId, 'paste', 'disabled'),
						text: Loc.getMessage('LANDING_TPL_EXT_BUTTON_ACTIONS_PASTE'),
						onclick: () => {
							actions.pasteClick();
						}
					}),
					new MenuItem({
						id: 'feedback',
						disabled: !actions.feedbackClick,
						text: Loc.getMessage('LANDING_TPL_EXT_BUTTON_ACTIONS_FEEDBACK'),
						onclick: () => {
							actions.feedbackClick();
						}
					}),
					actions.saveInLibrary
						? new MenuItem({ delimiter: true })
						: null,
					new MenuItem({
						id: 'save_in_library',
						disabled: !actions.saveInLibrary,
						text: Loc.getMessage('LANDING_TPL_EXT_BUTTON_ACTIONS_SAVE_IN_LIBRARY'),
						onclick: () => {
							actions.saveInLibrary();
						}
					}),
				],
			}).show();
		});

		return actionButton;
	}

	/**
	 * Returns Remove Button.
	 *
	 * @param {() => {}} onClick Click handler.
	 * @return {HTMLButtonElement}
	 */
	static getRemoveBlockButton(onClick: ?() => {}): HTMLButtonElement
	{
		const title = Loc.getMessage('LANDING_TPL_EXT_BUTTON_REMOVE_BLOCK');
		const button = Tag.render`
			<button class="landing-ui-button landing-ui-button-action${onClick ? '' : ' landing-ui-disabled'}" type="button" data-id="remove" title="${title}"><span class="landing-ui-button-text">&nbsp;</span></button>
		`;

		if (onClick)
		{
			Event.bind(button, 'click', onClick);
		}

		return button;
	}

	/**
	 * Returns right container for block's actions.
	 *
	 * @param {RightContainerOptions} options Options for right container.
	 * @return {HTMLDivElement}
	 */
	static getRightContainer(options: RightContainerOptions): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-ui-external-right-container">
				<div class="landing-ui-external-right-top-hr"></div>
				<div class="landing-ui-external-body">
					<div class="landing-ui-external-panel">
						${UI.getSortDownBlockButton(options.sortDownBlockClick)}
						${UI.getSortUpBlockButton(options.sortUpBlockClick)}
						${UI.getActionsList(options.blockId, options)}
						${UI.getRemoveBlockButton(options.removeBlockClick)}
					</div>
				</div>
				<div class="landing-ui-external-right-bottom-hr"></div>
			</div>
		`;
	}
}
