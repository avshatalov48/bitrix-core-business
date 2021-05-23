import { Event } from 'main.core';
import type ItemNode from '../item/item-node';
import type Tab from './tabs/tab';
import type Dialog from './dialog';

export default class Navigation
{
	dialog: Dialog = null;
	lockedTab: Tab = null;
	enabled: boolean = false;

	// IE/Edge compatible event names
	static keyMap: Object<string, string> = {
		'Down': 'ArrowDown',
		'Up': 'ArrowUp',
		'Left': 'ArrowLeft',
		'Right': 'ArrowRight',
		'Spacebar': 'Space',
		' ': 'Space' // For all browsers
	};

	constructor(dialog: Dialog)
	{
		this.dialog = dialog;

		this.dialog.subscribe('onShow', this.handleDialogShow.bind(this));
		this.dialog.subscribe('onHide', this.handleDialogHide.bind(this));
		this.dialog.subscribe('onDestroy', this.handleDialogDestroy.bind(this));

		this.handleDocumentKeyDown = this.handleDocumentKeyDown.bind(this);
		this.handleDocumentMouseMove = this.handleDocumentMouseMove.bind(this);
	}

	getDialog(): Dialog
	{
		return this.dialog;
	}

	enable(): void
	{
		if (!this.isEnabled())
		{
			this.bindEvents();
		}

		this.enabled = true;
	}

	disable(): void
	{
		if (this.isEnabled())
		{
			this.unbindEvents();
			this.unlockTab();
		}

		this.enabled = false;
	}

	isEnabled(): boolean
	{
		return this.enabled;
	}

	bindEvents(): void
	{
		Event.bind(document, 'keydown', this.handleDocumentKeyDown);
	}

	unbindEvents(): void
	{
		Event.unbind(document, 'keydown', this.handleDocumentKeyDown);
	}

	getNextNode(): ?ItemNode
	{
		if (!this.getActiveNode())
		{
			return null;
		}

		let nextNode = null;
		let currentNode = this.getActiveNode();

		if (currentNode.hasChildren() && currentNode.isOpen())
		{
			nextNode = currentNode.getFirstChild();
		}

		while (nextNode === null && currentNode !== null)
		{
			nextNode = currentNode.getNextSibling();
			if (nextNode)
			{
				break;
			}

			currentNode = currentNode.getParentNode();
		}

		return nextNode;
	}

	getPreviousNode(): ?ItemNode
	{
		if (!this.getActiveNode())
		{
			return null;
		}

		let previousNode = this.getActiveNode().getPreviousSibling();
		if (previousNode)
		{
			while (previousNode.hasChildren() && previousNode.isOpen())
			{
				const lastChild = previousNode.getLastChild();
				if (lastChild === null)
				{
					break;
				}

				previousNode = lastChild;
			}
		}
		else
		{
			if (this.getActiveNode().getParentNode() && !this.getActiveNode().getParentNode().isRoot())
			{
				previousNode = this.getActiveNode().getParentNode();
			}
		}

		return previousNode;
	}

	getFirstNode(): ?ItemNode
	{
		const tab = this.getDialog().getActiveTab();
		return tab && tab.getRootNode().getFirstChild();
	}

	getLastNode(): ?ItemNode
	{
		const tab = this.getDialog().getActiveTab();
		if (!tab)
		{
			return null;
		}

		let lastNode = tab.getRootNode().getLastChild();
		if (lastNode !== null)
		{
			while (lastNode.hasChildren() && lastNode.isOpen())
			{
				const lastChild = lastNode.getLastChild();
				if (lastChild === null)
				{
					break;
				}

				lastNode = lastChild;
			}
		}

		return lastNode;
	}

	getActiveNode(): ?ItemNode
	{
		return this.getDialog().getFocusedNode();
	}

	focusOnNode(node: ItemNode): void
	{
		if (node)
		{
			node.focus();
			node.scrollIntoView();
		}
	}

	lockTab(): void
	{
		const activeTab = this.getDialog().getActiveTab();
		if (this.lockedTab === activeTab)
		{
			return;
		}
		else if (this.lockedTab !== null)
		{
			this.unlockTab();
		}

		this.lockedTab = activeTab;
		this.lockedTab.lock();

		Event.bind(document, 'mousemove', this.handleDocumentMouseMove);
	}

	unlockTab(): void
	{
		if (this.lockedTab === null)
		{
			return;
		}

		this.lockedTab.unlock();
		this.lockedTab = null;

		Event.unbind(document, 'mousemove', this.handleDocumentMouseMove);
	}

	handleDialogShow(): void
	{
		this.enable();
	}

	handleDialogHide(): void
	{
		this.disable();
	}

	handleDialogDestroy(): void
	{
		this.disable();
	}

	handleDocumentMouseMove(): void
	{
		this.unlockTab();
	}

	handleDocumentKeyDown(event: KeyboardEvent): void
	{
		if (!this.getDialog().isOpen())
		{
			this.unbindEvents();
			return;
		}

		if (event.metaKey || event.ctrlKey || event.altKey)
		{
			return;
		}

		const activeTab = this.getDialog().getActiveTab();
		if (!activeTab)
		{
			return;
		}

		const keyName = this.constructor.keyMap[event.key] || event.key;
		if (activeTab === this.getDialog().getSearchTab() && ['ArrowLeft', 'ArrowRight'].includes(keyName))
		{
			return;
		}

		const handler: ?Function = this[`handle${keyName}Press`];
		if (handler)
		{
			handler.call(this, event);
			this.lockTab(activeTab);
			event.preventDefault();
		}
	}

	handleArrowDownPress(): void
	{
		if (!this.getActiveNode())
		{
			const firstNode = this.getFirstNode();
			this.focusOnNode(firstNode);
		}
		else
		{
			const nextNode = this.getNextNode();
			if (nextNode)
			{
				this.focusOnNode(nextNode);
			}
			else
			{
				const firstNode = this.getFirstNode();
				this.focusOnNode(firstNode);
			}
		}
	}

	handleArrowUpPress(): void
	{
		if (!this.getActiveNode())
		{
			const lastNode = this.getLastNode();
			this.focusOnNode(lastNode);
		}
		else
		{
			const previousNode = this.getPreviousNode();
			if (previousNode)
			{
				this.focusOnNode(previousNode);
			}
			else
			{
				const lastNode = this.getLastNode();
				this.focusOnNode(lastNode);
			}
		}
	}

	handleArrowRightPress(): void
	{
		if (this.getActiveNode())
		{
			this.getActiveNode().expand();
		}
	}

	handleArrowLeftPress(): void
	{
		if (!this.getActiveNode())
		{
			return;
		}

		if (this.getActiveNode().isOpen())
		{
			this.getActiveNode().collapse();
		}
		else
		{
			const parentNode = this.getActiveNode().getParentNode();
			if (parentNode && !parentNode.isRoot())
			{
				this.focusOnNode(parentNode);
			}
		}
	}

	handleEnterPress(): void
	{
		if (this.getActiveNode())
		{
			this.getActiveNode().click();
		}
	}

	/*handleSpacePress(event: KeyboardEvent): void
	{
		const searchQuery = this.getDialog().getTagSelector() && this.getDialog().getTagSelector().getTextBoxValue();
		if (this.getActiveNode() && !Type.isStringFilled(searchQuery))
		{
			this.getActiveNode().click();
			event.preventDefault();
		}
	}*/

	handleTabPress(event: KeyboardEvent): void
	{
		const activeTab = this.getDialog().getActiveTab();
		if (!activeTab)
		{
			this.getDialog().selectFirstTab();
			return;
		}

		if (event.shiftKey)
		{
			const previousTab = this.getDialog().getPreviousTab();
			if (previousTab)
			{
				this.getDialog().selectTab(previousTab.getId());
			}
			else
			{
				this.getDialog().selectLastTab();
			}
		}
		else
		{
			const nextTab = this.getDialog().getNextTab();
			if (nextTab)
			{
				this.getDialog().selectTab(nextTab.getId());
			}
			else
			{
				this.getDialog().selectFirstTab();
			}
		}
	}
}