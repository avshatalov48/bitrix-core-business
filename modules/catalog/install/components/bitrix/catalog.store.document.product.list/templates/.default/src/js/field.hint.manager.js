// @flow

import {Event} from 'main.core';
import {Guide} from 'ui.tour';
import 'spotlight';

export class FieldHintManager
{
	fieldHintIsBusy: boolean = false;
	activeHintGuide: Guide|null = null;

	#gridGetter: Function;
	#contentContainer: HTMLElement;

	constructor(contentContainer: HTMLElement, gridGetter: Function)
	{
		this.#contentContainer = contentContainer;
		this.#gridGetter = gridGetter;
	}

	processFieldTour(fieldNode: HTMLElement, tourData: Object, endTourHandler: Function, addictedFieldNodes: Array<HTMLElement> = []): void
	{
		if (this.fieldHintIsBusy)
		{
			return;
		}

		this.fieldHintIsBusy = true;
		// When click action in progress tour will be closed -> 'onClose' tour method will be executed
		tourData.events = {
			onClose: () => {
				endTourHandler();
				this.fieldHintIsBusy = false;
				this.activeHintGuide = null;
			}
		}

		if (this.#fieldNodeIsInGridVision(fieldNode))
		{
			let tourObject = this.#tieTourToNode(fieldNode, tourData);
			this.#freezeGridContainer(() => {
				tourObject.close();
			});
		}
		else
		{
			const gridContainer = this.#gridGetter().getContainer();
			const leftArrow = gridContainer.querySelector('.main-grid-ear-left');
			const rightArrow = gridContainer.querySelector('.main-grid-ear-right');

			const fieldPos = fieldNode.getClientRects()[0].x;
			const gridPos = gridContainer.getClientRects()[0].x;

			let spotlight = null;
			if (fieldPos > gridPos) {
				spotlight = this.#bindSpotlightToNode(rightArrow);
			}
			else
			{
				spotlight = this.#bindSpotlightToNode(leftArrow);
			}

			this.#bindGridNodeVisionChange(fieldNode, () => {
				spotlight.close();
				let tourObject = this.#tieTourToNode(fieldNode, tourData);
				this.#freezeGridContainer(() => {
					tourObject.close();
				})
			}, [], addictedFieldNodes);
		}
	}

	#bindGridNodeVisionChange(observedNode: HTMLElement, onSuccessVisionCallback: Function, callbackParams: Array = [], addictedNodes: Array<HTMLElement> = [])
	{
		const observedNodes = this.#getPossibleToValidateFieldNodes(observedNode, ...addictedNodes);
		const observer = (event: Event) => {
			if (this.#fieldNodeIsInGridVision(...observedNodes))
			{
				Event.unbind(
					this.#gridGetter().getScrollContainer(),
					'scroll',
					observer
				);
				Event.unbind(
					window,
					'resize',
					observer
				);

				onSuccessVisionCallback(...callbackParams);
			}
		}

		Event.bind(
			this.#gridGetter().getScrollContainer(),
			'scroll',
			observer
		);
		Event.bind(
			window,
			'resize',
			observer
		);
	}

	#getPossibleToValidateFieldNodes(mainNode: HTMLElement, ...addictedNodes: Array<HTMLElement>): Array<HTMLElement>
	{
		const nodesTuple = [];
		for (const addictedNode: HTMLElement of addictedNodes)
		{
			nodesTuple.push({
				node: addictedNode,
				nodeRect: addictedNode.getClientRects()[0],
			});
		}

		const mainNodeTupleEl = {
			node: mainNode,
			nodeRect: mainNode.getClientRects()[0],
		};
		nodesTuple.push(mainNodeTupleEl);

		nodesTuple.sort((firstEl, secondEl) => {
			const {x: firstX} = firstEl.nodeRect;
			const {x: secondX} = secondEl.nodeRect;

			if (firstX < secondX)
			{
				return -1;
			}
			else if (firstX > secondX)
			{
				return 1;
			}
			else
			{
				return 0;
			}
		});

		const gridRect = this.#gridGetter()?.getContainer().getClientRects()?.[0];
		function widthIsValid(leftPos: number, rightPos: number)
		{
			return Math.abs(leftPos - rightPos) < gridRect.width;
		}

		while (nodesTuple.length > 1 && !widthIsValid(nodesTuple[0].nodeRect.x,nodesTuple[nodesTuple.length-1].nodeRect.x))
		{
			const firstEl = nodesTuple[0];
			const lastEl = nodesTuple[nodesTuple.length-1];
			if (firstEl === mainNodeTupleEl)
			{
				nodesTuple.pop();
			}
			else if (lastEl === mainNodeTupleEl)
			{
				nodesTuple.shift();
			}
			else
			{
				const firstElDistance = mainNodeTupleEl.nodeRect.x - firstEl.nodeRect.x;
				const lastElDistance = lastEl.nodeRect.x - mainNodeTupleEl.nodeRect.x;
				if (firstElDistance >= lastElDistance)
				{
					nodesTuple.shift();
				}
				else
				{
					nodesTuple.pop();
				}
			}
		}

		return nodesTuple.map((el) => el.node);
	}

	#fieldNodeIsInGridVision(...fieldNodes: Array<HTMLElement>) {
		const gridRect = this.#gridGetter()?.getContainer().getClientRects()?.[0];

		if (gridRect === undefined)
		{
			return false;
		}
		const gridLeftEdge = gridRect.x;
		const gridRightEdge = gridRect.x + gridRect.width;

		for (const fieldNode: HTMLElement of fieldNodes)
		{
			const fieldRect = fieldNode.getClientRects()?.[0];
			if (fieldRect === undefined)
			{
				return false;
			}

			const fieldLeftEdge = fieldRect.x;
			const fieldRightEdge = fieldRect.x + fieldRect.width;

			if (fieldLeftEdge < gridLeftEdge || fieldRightEdge > gridRightEdge)
			{
				return false;
			}
		}

		return true;
	}

	#bindSpotlightToNode(targetNode: HTMLElement)
	{
		const spotlight = new BX.SpotLight(
			{
				id: 'arrow_spotlight',
				targetElement: targetNode,
				autoSave: true,
				targetVertex: "middle-center",
				zIndex: 200,
			}
		);
		spotlight.show();
		spotlight.container.style.pointerEvents = "none";

		return spotlight;
	}

	#freezeGridContainer(onCloseCallback: Function, callbackParams: Array = [])
	{
		const gridContainer = this.#gridGetter().getContainer();
		const leftArrow = gridContainer.querySelector('.main-grid-ear-left');
		const rightArrow = gridContainer.querySelector('.main-grid-ear-right');

		gridContainer.style.pointerEvents = "none";
		leftArrow.style.pointerEvents = "none";
		rightArrow.style.pointerEvents = "none";

		const clickObserver = (event) => {
			gridContainer.style.pointerEvents = "auto";
			leftArrow.style.pointerEvents = "auto";
			rightArrow.style.pointerEvents = "auto";
			Event.unbind(
				this.#contentContainer,
				'click',
				clickObserver
			);

			onCloseCallback(...callbackParams);
		}
		setTimeout(() => {
			Event.bind(
				this.#contentContainer,
				'click',
				clickObserver
			);
		}, 500);
	}

	#tieTourToNode(tourTarget: HTMLElement, tourData: Object): Guide
	{
		const guide = new Guide({
			steps: [
				Object.assign({target: tourTarget}, tourData),
			],
			onEvents: true,
		});
		this.activeHintGuide = guide;

		guide.showNextStep();
		return guide;
	}

	getActiveHint(): Guide|null
	{
		if (!this.fieldHintIsBusy)
		{
			return null;
		}
		else if (this.activeHintGuide instanceof Guide)
		{
			return this.activeHintGuide;
		}

		return null;
	}
}
