import editText from '../action/edit-text';
import editEmbed from '../action/edit-embed';
import editMap from '../action/edit-map';
import editImage from '../action/edit-image';
import editIcon from '../action/edit-icon';
import editLink from '../action/edit-link';
import sortBlock from '../action/sort-block';
import addBlock from '../action/add-block';
import removeBlock from '../action/remove-block';
import addCard from '../action/add-card';
import removeCard from '../action/remove-card';
import addNode from '../action/add-node';
import removeNode from '../action/remove-node';
import editStyle from '../action/edit-style';
import updateBlockState from '../action/update-block-state';
import updateContent from '../action/update-content';

import Command from '../history-command';
import {UNDO, REDO} from './constants';

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
			undo: editText.bind(null, UNDO),
			redo: editText.bind(null, REDO),
		}),
	);

	history.registerCommand(
		new Command({
			id: 'editEmbed',
			undo: editEmbed.bind(null, UNDO),
			redo: editEmbed.bind(null, REDO),
		}),
	);

	history.registerCommand(
		new Command({
			id: 'editMap',
			undo: editMap.bind(null, UNDO),
			redo: editMap.bind(null, REDO),
		}),
	);

	history.registerCommand(
		new Command({
			id: 'editImage',
			undo: editImage.bind(null, UNDO),
			redo: editImage.bind(null, REDO),
		}),
	);

	history.registerCommand(
		new Command({
			id: 'editIcon',
			undo: editIcon.bind(null, UNDO),
			redo: editIcon.bind(null, REDO),
		}),
	);

	history.registerCommand(
		new Command({
			id: 'editLink',
			undo: editLink.bind(null, UNDO),
			redo: editLink.bind(null, REDO),
		}),
	);

	history.registerCommand(
		new Command({
			id: 'sortBlock',
			undo: sortBlock.bind(null, UNDO),
			redo: sortBlock.bind(null, REDO),
		}),
	);

	history.registerCommand(
		new Command({
			id: 'addBlock',
			undo: removeBlock.bind(null, UNDO),
			redo: addBlock.bind(null, REDO),
		}),
	);

	history.registerCommand(
		new Command({
			id: 'removeBlock',
			undo: addBlock.bind(null, UNDO),
			redo: removeBlock.bind(null, REDO),
		}),
	);

	history.registerCommand(
		new Command({
			id: 'updateStyle',
			undo: editStyle.bind(null, UNDO),
			redo: editStyle.bind(null, REDO),
		}),
	);

	history.registerCommand(
		new Command({
			id: 'addCard',
			undo: removeCard.bind(null, UNDO),
			redo: addCard.bind(null, REDO),
		}),
	);

	history.registerCommand(
		new Command({
			id: 'removeCard',
			undo: addCard.bind(null, UNDO),
			redo: removeCard.bind(null, REDO),
		}),
	);

	history.registerCommand(
		new Command({
			id: 'addNode',
			undo: removeNode.bind(null, UNDO),
			redo: addNode.bind(null, REDO),
		}),
	);

	history.registerCommand(
		new Command({
			id: 'removeNode',
			undo: addNode.bind(null, UNDO),
			redo: removeNode.bind(null, REDO),
		}),
	);

	history.registerCommand(
		new Command({
			id: 'updateBlockState',
			undo: updateBlockState.bind(null, UNDO),
			redo: updateBlockState.bind(null, REDO),
		}),
	);

	history.registerCommand(
		new Command({
			id: 'updateContent',
			undo: updateContent.bind(null, UNDO),
			redo: updateContent.bind(null, REDO),
		}),
	);

	return Promise.resolve(history);
}