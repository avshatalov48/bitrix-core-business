/* eslint-disable @bitrix24/bitrix24-rules/no-native-dom-methods */

import { Type } from 'main.core';
import { $createParagraphNode, $isElementNode, $isRootNode, $isTextNode } from 'ui.lexical.core';
import { type ElementNode, type LexicalNode } from 'ui.lexical.core';
import { type TextEditor } from './text-editor';
import { type SchemeNodeValidation, type SchemeValidationOptions } from './types/scheme-validation-options';

export default class SchemeValidation
{
	#editor: TextEditor = null;
	#nodeTypeToBBCodeType: Map<string, string> = new Map();
	#nodeValidation: Map<string, Object> = new Map();

	constructor(editor: TextEditor)
	{
		this.#editor = editor;

		this.#initNodeValidation();
	}

	isNodeAllowed(parent: LexicalNode | string, child: LexicalNode | string): boolean
	{
		const parentCode = Type.isString(parent) ? parent : this.#nodeTypeToBBCodeType.get(parent.getType());
		const childCode = Type.isString(child) ? child : this.#nodeTypeToBBCodeType.get(child.getType());

		if (!parentCode)
		{
			// eslint-disable-next-line no-console
			console.warn(`TextEditor: parent node (${parent.getType()}) doesn't have a bbcode tag.`);
		}

		if (!childCode)
		{
			// eslint-disable-next-line no-console
			console.warn(`TextEditor: child node (${child.getType()}) doesn't have a bbcode tag.`);
		}

		return this.#editor.getBBCodeScheme().isChildAllowed(parentCode, childCode);
	}

	findAllowedParent(node: LexicalNode): ElementNode | null
	{
		let parent: ElementNode = node.getParent();
		while (parent !== null)
		{
			if (this.isNodeAllowed(parent, node))
			{
				return parent;
			}

			parent = parent.getParent();
		}

		return null;
	}

	#initNodeValidation(): void
	{
		const handleNodeTransform = this.#handleNodeTransform.bind(this);
		for (const [, plugin] of this.#editor.getPlugins())
		{
			const validation: SchemeValidationOptions | null = plugin.validateScheme();
			if (!Type.isPlainObject(validation))
			{
				continue;
			}

			if (Type.isArrayFilled(validation.nodes))
			{
				validation.nodes.forEach((nodeValidation: SchemeNodeValidation) => {
					this.#editor.registerNodeTransform(nodeValidation.nodeClass, handleNodeTransform);
					if (Type.isFunction(nodeValidation.validate))
					{
						this.#nodeValidation.set(nodeValidation.nodeClass.getType(), { validate: nodeValidation.validate });
					}
				});
			}

			if (Type.isPlainObject(validation.bbcodeMap))
			{
				for (const [nodeType, bbcodeTag] of Object.entries(validation.bbcodeMap))
				{
					this.#nodeTypeToBBCodeType.set(nodeType, bbcodeTag);
				}
			}
		}
	}

	#handleNodeTransform(node: LexicalNode | ElementNode): void
	{
		const { validate = null } = this.#nodeValidation.get(node.getType()) || {};
		if (validate !== null && validate(node, this) === true)
		{
			return;
		}

		const parent: ElementNode = node.getParent();
		if (this.isNodeAllowed(parent, node))
		{
			return;
		}

		// eslint-disable-next-line no-console
		console.warn(`TextEditor: ${node.getType()} is not allowed in ${parent.getType()}`);

		this.moveToNextParent(node);
	}

	moveToNextParent(node: LexicalNode | ElementNode, removeOnFail: boolean = true): boolean
	{
		let parent: ElementNode = node.getParent();
		let targetNode: ElementNode = null;
		while (parent.getParent() !== null)
		{
			if (this.isNodeAllowed(parent.getParent(), node))
			{
				targetNode = parent;

				break;
			}

			parent = parent.getParent();
		}

		if (targetNode === null)
		{
			if (removeOnFail)
			{
				node.remove();
			}

			return false;
		}

		if ($isRootNode(targetNode.getParent()) && ($isTextNode(node) || ($isElementNode(node) && node.isInline())))
		{
			targetNode.insertBefore($createParagraphNode().append(node));

			return true;
		}

		targetNode.insertBefore(node);

		return true;
	}
}
