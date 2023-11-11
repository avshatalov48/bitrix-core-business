import {Runtime, Dom, Tag} from 'main.core';

import {PageObject} from 'landing.pageobject';

import editText from '../action/edit-text';
import editEmbed from '../action/edit-embed';
import editMap from '../action/edit-map';
import editImage from '../action/edit-image';
import editIcon from '../action/edit-icon';
import editLink from '../action/edit-link';
import changeNodeName from '../action/change-node-name';
import sortBlock from '../action/sort-block';
import addBlock from '../action/add-block';
import removeBlock from '../action/remove-block';
import addCard from '../action/add-card';
import removeCard from '../action/remove-card';
import addNode from '../action/add-node';
import removeNode from '../action/remove-node';
import editStyle from '../action/edit-style';
import editAttributes from '../action/edit-attributes';
import updateContent from '../action/update-content';
import multiply from '../action/multiply';
import replaceLanding from '../action/replace-landing';
import changeAnchor from '../action/change-anchor';

import Command from '../history-command';

import type {History} from '../history';

/**
 * Registers base internal commands
 * @param {History} history
 * @return {Promise<History>}
 */
export default function registerBaseCommands(history: History)
{
	history.registerCommand(
		new Command({
			id: 'editText',
			command: editText,
		}),
	);

	history.registerCommand(
		new Command({
			id: 'editImage',
			command: editImage,
		}),
	);

	history.registerCommand(
		new Command({
			id: 'editEmbed',
			command: editEmbed,
		}),
	);

	history.registerCommand(
		new Command({
			id: 'editMap',
			command: editMap,
		}),
	);

	history.registerCommand(
		new Command({
			id: 'editIcon',
			command: editIcon,
		}),
	);

	history.registerCommand(
		new Command({
			id: 'editLink',
			command: editLink,
		}),
	);

	history.registerCommand(
		new Command({
			id: 'cnangeNodeName',
			command: changeNodeName,
		}),
	);

	history.registerCommand(
		new Command({
			id: 'sortBlock',
			command: sortBlock,
		}),
	);

	history.registerCommand(
		new Command({
			id: 'addBlock',
			command: addBlock,
		}),
	);

	history.registerCommand(
		new Command({
			id: 'removeBlock',
			command: removeBlock,
		}),
	);

	history.registerCommand(
		new Command({
			id: 'updateStyle',
			command: editStyle,
		}),
	);

	history.registerCommand(
		new Command({
			id: 'addCard',
			command: addCard,
		}),
	);

	history.registerCommand(
		new Command({
			id: 'removeCard',
			command: removeCard,
		}),
	);

	history.registerCommand(
		new Command({
			id: 'addNode',
			command: addNode,
		}),
	);

	history.registerCommand(
		new Command({
			id: 'removeNode',
			command: removeNode,
		}),
	);


	history.registerCommand(
		new Command({
			id: 'updateContent',
			command: updateContent,
		}),
	);

	history.registerCommand(
		new Command({
			id: 'replaceLanding',
			command: replaceLanding,
			onBeforeCommand: () => {
				return Runtime.loadExtension('main.loader')
					.then(() => {
						const editor = BX.Landing.PageObject.getEditorWindow();
						if (editor)
						{
							const container = Tag.render`<div class="landing-ui-modal"></div>`;
							Dom.append(container, editor.document.body);
							const loader = new BX.Loader({target: container});
							loader.show();
						}

						return Promise.resolve();
					});
			}
		}),
	);

	history.registerCommand(
		new Command({
			id: 'changeAnchor',
			command: changeAnchor,
		}),
	);

	history.registerCommand(
		new Command({
			id: 'editAttributes',
			command: editAttributes,
		}),
	);

	history.registerCommand(
		new Command({
			id: 'multiply',
			command: multiply,
		}),
	);

	return Promise.resolve(history);
}