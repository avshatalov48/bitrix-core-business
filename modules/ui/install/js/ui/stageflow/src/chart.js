import {Text, Dom, Tag, Type, Loc} from 'main.core';
import {Stage} from "./stage";

import {MenuManager, Popup, PopupManager} from 'main.popup';

const semanticSelectorPopupId = 'ui-stageflow-select-semantic-popup';
const finalStageSelectorPopupId = 'ui-stageflow-select-final-stage-popup';
const FinalStageDefaultData = {
	id: 'final',
	color: '7BD500',
	isFilled: false,
};

const defaultFinalStageLabels = {
	finalStageName: Loc.getMessage('UI_STAGEFLOW_FINAL_STAGE_NAME'),
	finalStagePopupTitle: Loc.getMessage('UI_STAGEFLOW_FINAL_STAGE_POPUP_TITLE'),
	finalStagePopupFail: Loc.getMessage('UI_STAGEFLOW_FINAL_STAGE_POPUP_FAIL'),
	finalStageSelectorTitle: Loc.getMessage('UI_STAGEFLOW_FINAL_STAGE_SELECTOR_TITLE'),
};

export class Chart
{
	backgroundColor;
	container;
	currentStage = 0;
	stages: Map;
	isActive = false;
	onStageChange;
	labels: {
		finalStageName: string,
		finalStagePopupTitle: string,
		finalStagePopupFail: string,
		finalStageSelectorTitle: string,
	};

	constructor(params: {
		backgroundColor: string,
		currentStage: number,
		isActive: boolean,
		onStageChange: ?Function,
		labels: ?{},
	}, stages = [])
	{
		this.labels = defaultFinalStageLabels;
		if(Type.isPlainObject(params))
		{
			if(Type.isString(params.backgroundColor) && params.backgroundColor.length === 6)
			{
				this.backgroundColor = params.backgroundColor;
			}
			if(params.currentStage)
			{
				this.currentStage = Text.toInteger(params.currentStage);
			}
			if(Type.isBoolean(params.isActive))
			{
				this.isActive = params.isActive;
			}
			if(Type.isFunction(params.onStageChange))
			{
				this.onStageChange = params.onStageChange;
			}
			if(Type.isPlainObject(params.labels))
			{
				this.labels = {...this.labels, ...params.labels};
			}
		}
		FinalStageDefaultData.name = this.labels.finalStageName;
		if(Type.isArray(stages))
		{
			let fillingColor = null;
			if(this.currentStage > 0)
			{
				stages.forEach((data) => {
					if(Text.toInteger(data.id) === Text.toInteger(this.currentStage))
					{
						fillingColor = data.color;
					}
				})
			}
			this.fillStages(stages, fillingColor);
		}
		if(!this.currentStage && this.stages.length > 0)
		{
			this.currentStage = this.stages.keys().next().value;
		}
	}

	setCurrentStageId(stageId: number): Chart
	{
		stageId = Text.toInteger(stageId);
		const currentStage = this.getStageById(stageId);
		if(!currentStage)
		{
			return;
		}
		this.currentStage = stageId;
		const finalStage = this.getFinalStage();
		if(finalStage)
		{
			if(currentStage.isFinal())
			{
				finalStage.setColor(currentStage.getColor()).setName(currentStage.getName());
			}
			else
			{
				finalStage.setColor(FinalStageDefaultData.color).setName(FinalStageDefaultData.name);
			}
		}
		this.stages.forEach((stage: Stage) =>
		{
			if(!stage.isFinal())
			{
				stage.fillingColor = currentStage.getColor();
			}
		});
		this.addBackLightUpToStage();

		return this;
	}

	fillStages(stages: Array, fillingColor: ?string)
	{
		let isFilled = (this.currentStage > 0);
		const finalStageOptions = {};
		this.stages = new Map();
		stages.forEach((data) =>
		{
			data.isFilled = isFilled;
			data.backgroundColor = this.backgroundColor;
			data.fillingColor = fillingColor;
			data.events = {
				onMouseEnter: this.onStageMouseHover.bind(this),
				onMouseLeave: this.onStageMouseLeave.bind(this),
				onClick: this.onStageClick.bind(this),
			};
			const stage = Stage.create(data);
			if(stage)
			{
				this.stages.set(stage.getId(), stage);
			}
			if(stage.isSuccess())
			{
				FinalStageDefaultData.color = stage.getColor();
			}
			if(stage.isFinal())
			{
				finalStageOptions.isFilled = isFilled;
				if(stage.getId() === this.currentStage)
				{
					finalStageOptions.name = stage.getName();
					finalStageOptions.color = stage.getColor();
				}
			}
			else if(isFilled && stage.getId() === this.currentStage)
			{
				isFilled = false;
			}
		});

		if(this.getFailStages().length <= 0)
		{
			FinalStageDefaultData.name = finalStageOptions.name = this.getSuccessStage().getName();
		}

		this.addFinalStage(finalStageOptions);
	}

	addFinalStage(data: {
		isFilled: ?boolean,
		name: ?string,
		color: ?string,
	})
	{
		this.stages.set(FinalStageDefaultData.id, new Stage({...{
			backgroundColor: this.backgroundColor,
			events: {
				onMouseEnter: this.onStageMouseHover.bind(this),
				onMouseLeave: this.onStageMouseLeave.bind(this),
				onClick: this.onFinalStageClick.bind(this),
			},
		}, ...FinalStageDefaultData, ...data}));
	}

	getFinalStage(): ?Stage
	{
		return this.getStageById(FinalStageDefaultData.id);
	}

	getStages(): Map
	{
		return this.stages;
	}

	getFirstFailStage(): ?Stage
	{
		let failStage = null;
		this.stages.forEach((stage: Stage) =>
		{
			if(stage.isFail() && !failStage)
			{
				failStage = stage;
			}
		});

		return failStage;
	}

	getFailStages(): Array
	{
		const failStages = [];
		this.stages.forEach((stage: Stage) =>
		{
			if(stage.isFail())
			{
				failStages.push(stage);
			}
		});

		return failStages;
	}

	getSuccessStage(): ?Stage
	{
		let finalStage = null;
		this.stages.forEach((stage: Stage) =>
		{
			if(stage.isSuccess())
			{
				finalStage = stage;
			}
		});

		return finalStage;
	}

	getStageById(id: number): ?Stage
	{
		return this.stages.get(id);
	}

	render(): Element
	{
		const container = this.renderContainer();

		this.getStages().forEach((stage: Stage) =>
		{
			if(stage.isFinal())
			{
				return;
			}
			container.appendChild(stage.render());
		});

		this.addBackLightUpToStage();

		return container;
	}

	renderContainer(): Element
	{
		if(this.container)
		{
			Dom.clean(this.container);
			return this.container;
		}

		this.container = Tag.render`<div class="ui-stageflow-container"></div>`;

		return this.container;
	}

	onStageMouseHover(stage: Stage)
	{
		if(!this.isActive)
		{
			return;
		}
		for(let [id, currentStage] of this.stages)
		{
			currentStage.addBackLight(stage.getColor());
			if(id === stage.getId())
			{
				break;
			}
		}
	}

	onStageMouseLeave(stage: Stage)
	{
		if(!this.isActive)
		{
			return;
		}
		for(let [id, currentStage] of this.stages)
		{
			currentStage.removeBackLight();
			if(id === stage.getId())
			{
				break;
			}
		}
	}

	onStageClick(stage: Stage)
	{
		if(!this.isActive)
		{
			return;
		}
		if(stage.getId() !== this.currentStage && Type.isFunction(this.onStageChange))
		{
			this.onStageChange(stage);
		}
		const popup = this.getSemanticSelectorPopup();
		if(popup.isShown())
		{
			popup.close();
		}
	}

	onFinalStageClick(stage: Stage)
	{
		if(!this.isActive)
		{
			return;
		}

		if(this.getFailStages().length <= 0)
		{
			this.onStageClick(this.getSuccessStage());
		}
		else
		{
			const popup = this.getSemanticSelectorPopup();
			popup.show();
			const currentStage = this.getStageById(this.currentStage);
			this.isActive = false;
			if (!currentStage.isFinal()) {
				const finalStage = this.getStageById(FinalStageDefaultData.id);
				if (finalStage) {
					this.addBackLightUpToStage(finalStage.getId(), finalStage.getColor());
				}
			}
		}
	}

	addBackLightUpToStage(stageId: number|string = null, color: string = null)
	{
		if(!stageId)
		{
			stageId = this.currentStage;
		}
		const currentStage = this.getStageById(stageId);
		if(currentStage && !color)
		{
			color = currentStage.getColor();
		}

		let isFilled = !!stageId;
		this.stages.forEach((stage: Stage) =>
		{
			stage.isFilled = isFilled;
			if(stage.isFilled)
			{
				stage.addBackLight(color ? color : stage.getColor());
			}
			else
			{
				stage.removeBackLight();
			}
			if(!stage.isFinal() && isFilled && stage.getId() === stageId)
			{
				isFilled = false;
			}
		});
	}

	getSemanticSelectorPopup(): Popup
	{
		let popup = PopupManager.getPopupById(semanticSelectorPopupId);

		if(!popup)
		{
			let failSemanticText = this.getFailStageName();

			popup = PopupManager.create({
				id: semanticSelectorPopupId,
				autoHide: true,
				closeByEsc: true,
				closeIcon: true,
				maxWidth: 420,
				content: Tag.render`<div class="ui-stageflow-popup-title">${this.labels.finalStagePopupTitle}</div>`,
				buttons: [
					new BX.UI.Button({
						color: BX.UI.Button.Color.SUCCESS,
						text: this.getSuccessStage().getName(),
						onclick: () =>
						{
							this.isActive = true;
							this.onStageClick(this.getSuccessStage());
						}
					}),
					(failSemanticText ? new BX.UI.Button({
						color: BX.UI.Button.Color.DANGER,
						text: failSemanticText,
						onclick: () =>
						{
							popup.close();
							const finalStagePopup = this.getFinalStageSelectorPopup();
							finalStagePopup.show();
							this.isActive = false;
						}
					}) : null),
				],
				events: {
					onClose: () =>
					{
						this.setCurrentStageId(this.currentStage);
						this.isActive = true;
					},
				},
			});
		}

		return popup;
	}

	getFinalStageSemanticSelector(isSuccess: boolean = null): Element
	{
		if(!this.finalStageSemanticSelector)
		{
			this.finalStageSemanticSelector = Tag.render`<div class="ui-stageflow-stage-selector-option ui-stageflow-stage-selector-option-fail" onclick="${this.onSemanticSelectorClick.bind(this)}"></div>`;
		}

		if(Type.isBoolean(isSuccess))
		{
			let realFinalStage = null;
			let failStageName = this.getFailStageName();
			if(isSuccess || !failStageName)
			{
				this.finalStageSemanticSelector.classList.add('ui-stageflow-stage-selector-option-success');
				this.finalStageSemanticSelector.classList.remove('ui-stageflow-stage-selector-option-fail');
				this.finalStageSemanticSelector.innerText = this.getSuccessStage().getName();
				realFinalStage = this.getSuccessStage();
			}
			else
			{
				this.finalStageSemanticSelector.classList.add('ui-stageflow-stage-selector-option-fail');
				this.finalStageSemanticSelector.classList.remove('ui-stageflow-stage-selector-option-success');
				this.finalStageSemanticSelector.innerText = failStageName;
				realFinalStage = this.getFirstFailStage();
			}
			const finalStage = this.getFinalStage();
			if(finalStage && realFinalStage)
			{
				finalStage.setColor(realFinalStage.getColor()).setName(realFinalStage.getName());
			}
			this.addBackLightUpToStage(finalStage.getId(), finalStage.getColor());
		}

		return this.finalStageSemanticSelector;
	}

	getFinalStageSelectorPopup(isSuccess: boolean = false): Popup
	{
		let titleBar = {};
		let content = Tag.render`<div class="ui-stageflow-final-fail-stage-list-wrapper"></div>`;
		if(!isSuccess)
		{
			const failStages = this.getFailStages();
			if(failStages.length > 1)
			{
				let isChecked = true;
				failStages.forEach((stage: Stage) =>
				{
					content.appendChild(Tag.render`<div class="ui-stageflow-final-fail-stage-list-section">
						<input data-stage-id="${stage.getId()}" id="ui-stageflow-final-fail-stage-${stage.getId()}" name="ui-stageflow-final-fail-stage-input" class="crm-list-fail-deal-button" type="radio" ${(isChecked ? 'checked="checked"' : '')}>
						<label for="ui-stageflow-final-fail-stage-${stage.getId()}">${stage.getName()}</label>
					</div>`);
					isChecked = false;
				});
			}
		}
		titleBar.content = Tag.render`<div class="ui-stageflow-stage-selector-block">
			<span>${this.labels.finalStageSelectorTitle} </span>
			${this.getFinalStageSemanticSelector(isSuccess)}
		</div>`;
		let popup = PopupManager.getPopupById(finalStageSelectorPopupId);
		if(!popup)
		{
			popup = PopupManager.create({
				id: finalStageSelectorPopupId,
				autoHide: false,
				closeByEsc: true,
				closeIcon: true,
				width: 420,
				titleBar: true,
				buttons: [
					new BX.UI.SaveButton({
						onclick: () =>
						{
							popup.close();
							const stage = this.getSelectedFinalStage();
							if(stage)
							{
								this.onStageClick(stage);
							}
						}
					}),
					new BX.UI.CancelButton({
						onclick: () =>
						{
							popup.close();
						}
					}),
				],
				events: {
					onClose: () =>
					{
						this.setCurrentStageId(this.currentStage);
						this.isActive = true;
					},
				}
			});
		}

		popup.setContent(content);
		popup.setTitleBar(titleBar);

		return popup;
	}

	onSemanticSelectorClick()
	{
		const failStageName = this.getFailStageName();
		const menu = MenuManager.create({
			id: 'ui-stageflow-final-stage-semantic-selector',
			bindElement: this.getFinalStageSemanticSelector(),
			items: [
				{
					text: this.getSuccessStage().getName(),
					onclick: () =>
					{
						this.getFinalStageSelectorPopup(true);
						menu.close();
					},
				},
				(failStageName ? {
					text: failStageName,
					onclick: () =>
					{
						this.getFinalStageSelectorPopup(false);
						menu.close();
					},
				} : null),
			]
		});

		menu.show();
	}

	getSelectedFinalStage(): ?Stage
	{
		const finalStageSemanticSelector = this.getFinalStageSemanticSelector();
		if(finalStageSemanticSelector.classList.contains('ui-stageflow-stage-selector-option-success'))
		{
			return this.getSuccessStage();
		}
		else
		{
			const failStages = this.getFailStages();
			if(failStages.length > 1)
			{
				const finalStageSelectorPopupContainer = document.getElementById(finalStageSelectorPopupId);
				if(finalStageSelectorPopupContainer)
				{
					const selectedInput = finalStageSelectorPopupContainer.querySelector('input:checked');
					if(selectedInput)
					{
						const failStage = this.getStageById(Text.toInteger(selectedInput.dataset.stageId));
						if(failStage)
						{
							return failStage;
						}
					}
				}
			}

			return this.getFirstFailStage();
		}
	}

	getFailStageName(): ?string
	{
		const failStagesLength = this.getFailStages().length;

		if(failStagesLength <= 0)
		{
			return null;
		}
		else if(failStagesLength === 1)
		{
			return this.getFirstFailStage().getName();
		}
		else
		{
			return this.labels.finalStagePopupFail;
		}
	}
}