import { Loc, Text, Type } from 'main.core';
import type { AccessRightItem, Variable, VariableCollection } from './store/model/access-rights-model';

export function shouldRowBeRendered(accessRightItem: AccessRightItem): boolean
{
	if (!accessRightItem.isShown)
	{
		return false;
	}

	return !accessRightItem.group || accessRightItem.isGroupExpanded;
}

export function getSelectedVariables(
	variables: VariableCollection,
	selected: Set<string>,
	isAllSelected: boolean,
): VariableCollection
{
	if (isAllSelected)
	{
		return variables;
	}

	const selectedVariables = new Map();

	for (const [variableId, variable] of variables)
	{
		if (selected.has(variableId))
		{
			selectedVariables.set(variableId, variable);
		}
	}

	return selectedVariables;
}

export function getMultipleSelectedVariablesTitle(selectedVariables: VariableCollection): string
{
	const lastVariable: Variable = [...selectedVariables.values()].pop();

	if (selectedVariables.size === 1)
	{
		return lastVariable.title;
	}

	return Loc.getMessage(
		'JS_UI_ACCESSRIGHTS_V2_HAS_SELECTED_ITEMS',
		{
			'#FIRST_ITEM_NAME#': cutLongTitle(lastVariable.title),
			'#COUNT_REST_ITEMS#': selectedVariables.size - 1,
		},
	);
}

function cutLongTitle(title: string): string
{
	const VARIABLE_TITLE_MAX_LENGTH = 15;

	if (title.length > VARIABLE_TITLE_MAX_LENGTH)
	{
		return `${title.slice(0, VARIABLE_TITLE_MAX_LENGTH)}...`;
	}

	return title;
}

export function getMultipleSelectedVariablesHintHtml(
	selectedVariables: VariableCollection,
	hintTitle: string,
	allVariables: VariableCollection,
): string
{
	if (selectedVariables.size < 2)
	{
		return '';
	}

	let listItems = '';
	for (const value of makeSortedVariablesArray(selectedVariables, allVariables))
	{
		listItems += `<li>${Text.encode(value.title)}</li>`;
	}

	return `
		<p>${Text.encode(hintTitle)}</p>
		<ul>${listItems}</ul>
	`;
}

function makeSortedVariablesArray(toSort: VariableCollection, example: VariableCollection): Variable[]
{
	const orderMap = new Map();

	let index = 0;
	for (const [variableId] of example)
	{
		orderMap.set(variableId, index);

		index++;
	}

	return [...toSort.values()].sort((a, b) => {
		const indexA = orderMap.get(a.id);
		const indexB = orderMap.get(b.id);

		if (Type.isNil(indexA))
		{
			return 1;
		}

		if (Type.isNil(indexB))
		{
			return -1;
		}

		return indexA - indexB;
	});
}

export const DEFAULT_ALIAS_SEPARATOR = '|';

export function parseAliasKey(key: string, separator = DEFAULT_ALIAS_SEPARATOR): Set<string>
{
	const parts = key.split(separator);

	return new Set(parts);
}

export function compileAliasKey(parts: Set<string>, separator = DEFAULT_ALIAS_SEPARATOR): string
{
	const sortedParts = [...parts].sort();

	return sortedParts.join(separator);
}

export function normalizeAliasKey(key: string, separator = DEFAULT_ALIAS_SEPARATOR): string
{
	const parsed = parseAliasKey(key, separator);

	return compileAliasKey(parsed, separator);
}
