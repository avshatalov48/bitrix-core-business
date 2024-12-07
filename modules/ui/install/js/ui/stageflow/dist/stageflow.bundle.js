/* eslint-disable */
this.BX = this.BX || {};
(function (exports,ui_buttons,main_core,main_popup) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2;
	class Stage {
	  constructor(params) {
	    this.backgroundImage = "url('data:image/svg+xml;charset=UTF-8,%3csvg width=%27295%27 height=%2732%27 viewBox=%270 0 295 32%27 fill=%27none%27 xmlns=%27http://www.w3.org/2000/svg%27%3e%3cmask id=%27mask0_2_11%27 style=%27mask-type:alpha%27 maskUnits=%27userSpaceOnUse%27 x=%270%27 y=%270%27 width=%27295%27 height=%2732%27%3e%3cpath fill=%27#COLOR2#%27 d=%27M0 2.9961C0 1.3414 1.33554 0 2.99805 0L285.905 7.15256e-07C287.561 7.15256e-07 289.366 1.25757 289.937 2.80757L295 16.5505L290.007 29.2022C289.397 30.7474 287.567 32 285.905 32H2.99805C1.34227 32 0 30.6657 0 29.0039V2.9961Z%27/%3e%3c/mask%3e%3cg mask=%27url(%23mask0_2_11)%27%3e%3cpath fill=%27#COLOR2#%27 d=%27M0 2.9961C0 1.3414 1.33554 0 2.99805 0L285.905 7.15256e-07C287.561 7.15256e-07 289.366 1.25757 289.937 2.80757L295 16.5505L290.007 29.2022C289.397 30.7474 287.567 32 285.905 32H2.99805C1.34227 32 0 30.6657 0 29.0039V2.9961Z%27/%3e%3cpath d=%27M0 30H295V32H0V30Z%27 fill=%27#COLOR1#%27/%3e%3c/g%3e%3c/svg%3e') 3 10 3 3 fill repeat";
	    this.id = params.id;
	    this.name = params.name;
	    this.color = params.color;
	    this.backgroundColor = params.backgroundColor;
	    this.isFilled = params.isFilled;
	    this.events = params.events;
	    this.success = params.isSuccess;
	    this.fail = params.isFail;
	    this.fillingColor = params.fillingColor;
	    this.isDisable = params.isDisable;
	  }
	  static create(data) {
	    if (main_core.Type.isPlainObject(data) && data.id && data.name && data.color && data.backgroundColor) {
	      data.id = main_core.Text.toInteger(data.id);
	      data.name = data.name.toString();
	      data.color = data.color.toString();
	      data.backgroundColor = data.backgroundColor.toString();
	      data.events = main_core.Type.isPlainObject(data.events) ? data.events : {};
	      data.isFilled = main_core.Type.isBoolean(data.isFilled) ? data.isFilled : false;
	      data.isDisable = main_core.Type.isBoolean(data.isDisable) ? data.isDisable : false;
	      if (data.id > 0) {
	        return new Stage(data);
	      }
	    }
	    return null;
	  }
	  getId() {
	    return this.id;
	  }
	  getName() {
	    return this.name;
	  }
	  setName(name) {
	    this.name = name;
	    if (this.textNode) {
	      this.textNode.innerText = this.name;
	    }
	    return this;
	  }
	  isSuccess() {
	    return this.success === true;
	  }
	  isFail() {
	    return this.fail === true;
	  }
	  isFinal() {
	    return this.isFail() || this.isSuccess();
	  }
	  isDisabled() {
	    return this.isDisable;
	  }
	  setDisable(isDisable = true) {
	    if (this.isDisable === isDisable) {
	      return this;
	    }
	    if (this.node) {
	      main_core.Dom.toggleClass(this.node, '--disabled');
	    }
	    this.isDisable = isDisable;
	    return this;
	  }
	  getColor() {
	    return this.color;
	  }
	  setColor(color) {
	    this.color = color;
	    return this;
	  }
	  render() {
	    if (this.node) {
	      this.textNode.style.backgroundImage = this.getBackgroundImage();
	    } else {
	      const disableClass = this.isDisabled() ? '--disabled' : '';
	      this.textNode = main_core.Tag.render(_t || (_t = _`<div style="border-image: ${0};" class="ui-stageflow-stage-item-text">${0}</div>`), this.getBackgroundImage(), main_core.Text.encode(this.getName()));
	      this.node = main_core.Tag.render(_t2 || (_t2 = _`<div 
					class="ui-stageflow-stage ${0}" 
					data-stage-id="${0}" 
					onmouseenter="${0}" 
					onmouseleave="${0}"
					onclick="${0}"
				>
				<div class="ui-stageflow-stage-item">
					${0}
				</div>
			</div>`), disableClass, this.getId(), this.onMouseEnter.bind(this), this.onMouseLeave.bind(this), this.onClick.bind(this), this.textNode);
	    }
	    this.textNode.style.color = Stage.calculateTextColor('#' + (this.isFilled ? this.color : this.backgroundColor));
	    return this.node;
	  }
	  getBackgroundImage(color = null, isFilled = null) {
	    if (!color) {
	      if (this.isFilled && this.fillingColor) {
	        color = this.fillingColor;
	      } else {
	        color = this.getColor();
	      }
	    }
	    if (main_core.Type.isNull(isFilled)) {
	      isFilled = this.isFilled;
	    }
	    let image = this.backgroundImage.replaceAll('#COLOR1#', encodeURIComponent('#' + color));
	    if (isFilled) {
	      image = image.replaceAll('#COLOR2#', encodeURIComponent('#' + color));
	    } else {
	      image = image.replaceAll('#COLOR2#', encodeURIComponent('#' + this.backgroundColor));
	    }
	    return image;
	  }
	  onMouseEnter() {
	    if (main_core.Type.isFunction(this.events.onMouseEnter)) {
	      this.events.onMouseEnter(this);
	    }
	  }
	  onMouseLeave() {
	    if (main_core.Type.isFunction(this.events.onMouseLeave)) {
	      this.events.onMouseLeave(this);
	    }
	  }
	  onClick() {
	    if (main_core.Type.isFunction(this.events.onClick)) {
	      this.events.onClick(this);
	    }
	  }
	  addBackLight(color) {
	    if (this.textNode) {
	      this.textNode.style.borderImage = this.getBackgroundImage(color, true);
	      this.textNode.style.color = Stage.calculateTextColor('#' + color);
	    }
	  }
	  removeBackLight() {
	    if (this.textNode) {
	      this.textNode.style.borderImage = this.getBackgroundImage();
	      this.textNode.style.color = Stage.calculateTextColor('#' + (this.isFilled ? this.fillingColor : this.backgroundColor));
	    }
	  }
	  getMinWidthForFullNameVisibility() {
	    if (!this.textNode) {
	      return 0;
	    }
	    const {
	      clientWidth,
	      offsetWidth,
	      scrollWidth
	    } = this.textNode;
	    return scrollWidth + (offsetWidth - clientWidth) + 2;
	  }
	  isNameCropped() {
	    if (!this.textNode) {
	      return false;
	    }
	    return this.textNode.offsetWidth < this.textNode.scrollWidth;
	  }
	  static calculateTextColor(baseColor) {
	    var r, g, b;
	    if (baseColor.length > 7 && baseColor.indexOf('(') >= 0 && baseColor.indexOf(')') >= 0) {
	      var hexComponent = baseColor.split("(")[1].split(")")[0];
	      hexComponent = hexComponent.split(",");
	      r = parseInt(hexComponent[0]);
	      g = parseInt(hexComponent[1]);
	      b = parseInt(hexComponent[2]);
	    } else {
	      if (/^#([A-Fa-f0-9]{3}){1,2}$/.test(baseColor)) {
	        var c = baseColor.substring(1).split('');
	        if (c.length === 3) {
	          c = [c[0], c[0], c[1], c[1], c[2], c[2]];
	        }
	        c = '0x' + c.join('');
	        r = c >> 16 & 255;
	        g = c >> 8 & 255;
	        b = c & 255;
	      }
	    }
	    var y = 0.21 * r + 0.72 * g + 0.07 * b;
	    return y < 145 ? "#fff" : "#333";
	  }
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3,
	  _t4,
	  _t5,
	  _t6;
	const semanticSelectorPopupId = 'ui-stageflow-select-semantic-popup';
	const finalStageSelectorPopupId = 'ui-stageflow-select-final-stage-popup';
	const FinalStageDefaultData = {
	  id: 'final',
	  color: '7BD500',
	  isFilled: false
	};
	const defaultFinalStageLabels = {
	  finalStageName: main_core.Loc.getMessage('UI_STAGEFLOW_FINAL_STAGE_NAME'),
	  finalStagePopupTitle: main_core.Loc.getMessage('UI_STAGEFLOW_FINAL_STAGE_POPUP_TITLE'),
	  finalStagePopupFail: main_core.Loc.getMessage('UI_STAGEFLOW_FINAL_STAGE_POPUP_FAIL'),
	  finalStageSelectorTitle: main_core.Loc.getMessage('UI_STAGEFLOW_FINAL_STAGE_SELECTOR_TITLE')
	};
	class Chart {
	  constructor(params, stages = []) {
	    this.currentStage = 0;
	    this.isActive = false;
	    this.labels = defaultFinalStageLabels;
	    if (main_core.Type.isPlainObject(params)) {
	      if (main_core.Type.isString(params.backgroundColor) && params.backgroundColor.length === 6) {
	        this.backgroundColor = params.backgroundColor;
	      }
	      if (params.currentStage) {
	        this.currentStage = main_core.Text.toInteger(params.currentStage);
	      }
	      if (main_core.Type.isBoolean(params.isActive)) {
	        this.isActive = params.isActive;
	      }
	      if (main_core.Type.isFunction(params.onStageChange)) {
	        this.onStageChange = params.onStageChange;
	      }
	      if (main_core.Type.isPlainObject(params.labels)) {
	        this.labels = {
	          ...this.labels,
	          ...params.labels
	        };
	      }
	    }
	    FinalStageDefaultData.name = this.labels.finalStageName;
	    if (main_core.Type.isArray(stages)) {
	      let fillingColor = null;
	      if (this.currentStage > 0) {
	        stages.forEach(data => {
	          if (main_core.Text.toInteger(data.id) === main_core.Text.toInteger(this.currentStage)) {
	            fillingColor = data.color;
	          }
	        });
	      }
	      this.fillStages(stages, fillingColor);
	    }
	    if (!this.currentStage && this.stages.length > 0) {
	      this.currentStage = this.stages.keys().next().value;
	    }
	  }
	  setCurrentStageId(stageId) {
	    stageId = main_core.Text.toInteger(stageId);
	    const currentStage = this.getStageById(stageId);
	    if (!currentStage) {
	      return;
	    }
	    this.currentStage = stageId;
	    const finalStage = this.getFinalStage();
	    if (finalStage) {
	      if (currentStage.isFinal()) {
	        finalStage.setColor(currentStage.getColor()).setName(currentStage.getName());
	      } else {
	        finalStage.setColor(FinalStageDefaultData.color).setName(FinalStageDefaultData.name);
	      }
	    }
	    this.stages.forEach(stage => {
	      if (!stage.isFinal()) {
	        stage.fillingColor = currentStage.getColor();
	      }
	    });
	    this.addBackLightUpToStage();
	    return this;
	  }
	  fillStages(stages, fillingColor) {
	    let isFilled = this.currentStage > 0;
	    const finalStageOptions = {};
	    this.stages = new Map();
	    stages.forEach(data => {
	      data.isFilled = isFilled;
	      data.backgroundColor = this.backgroundColor;
	      data.fillingColor = fillingColor;
	      data.events = {
	        onMouseEnter: this.onStageMouseHover.bind(this),
	        onMouseLeave: this.onStageMouseLeave.bind(this),
	        onClick: this.onStageClick.bind(this)
	      };
	      const stage = Stage.create(data);
	      if (stage) {
	        this.stages.set(stage.getId(), stage);
	      }
	      if (stage.isSuccess()) {
	        FinalStageDefaultData.color = stage.getColor();
	      }
	      if (stage.isFinal()) {
	        finalStageOptions.isFilled = isFilled;
	        if (stage.getId() === this.currentStage) {
	          finalStageOptions.name = stage.getName();
	          finalStageOptions.color = stage.getColor();
	        }
	      } else if (isFilled && stage.getId() === this.currentStage) {
	        isFilled = false;
	      }
	    });
	    if (this.getFailStages().length <= 0) {
	      FinalStageDefaultData.name = finalStageOptions.name = this.getSuccessStage().getName();
	    }
	    this.addFinalStage(finalStageOptions);
	  }
	  addFinalStage(data) {
	    this.stages.set(FinalStageDefaultData.id, new Stage({
	      ...{
	        backgroundColor: this.backgroundColor,
	        events: {
	          onMouseEnter: this.onStageMouseHover.bind(this),
	          onMouseLeave: this.onStageMouseLeave.bind(this),
	          onClick: this.onFinalStageClick.bind(this)
	        }
	      },
	      ...FinalStageDefaultData,
	      ...data
	    }));
	  }
	  getFinalStage() {
	    return this.getStageById(FinalStageDefaultData.id);
	  }
	  getStages() {
	    return this.stages;
	  }
	  getFirstFailStage() {
	    let failStage = null;
	    this.stages.forEach(stage => {
	      if (stage.isFail() && !failStage) {
	        failStage = stage;
	      }
	    });
	    return failStage;
	  }
	  getFailStages() {
	    const failStages = [];
	    this.stages.forEach(stage => {
	      if (stage.isFail()) {
	        failStages.push(stage);
	      }
	    });
	    return failStages;
	  }
	  getSuccessStage() {
	    let finalStage = null;
	    this.stages.forEach(stage => {
	      if (stage.isSuccess()) {
	        finalStage = stage;
	      }
	    });
	    return finalStage;
	  }
	  getStageById(id) {
	    return this.stages.get(id);
	  }
	  render() {
	    const container = this.renderContainer();
	    this.getStages().forEach(stage => {
	      if (stage.isFinal()) {
	        return;
	      }
	      container.appendChild(stage.render());
	    });
	    this.addBackLightUpToStage();
	    return container;
	  }
	  renderContainer() {
	    if (this.container) {
	      main_core.Dom.clean(this.container);
	      return this.container;
	    }
	    this.container = main_core.Tag.render(_t$1 || (_t$1 = _$1`<div class="ui-stageflow-container"></div>`));
	    return this.container;
	  }
	  onStageMouseHover(stage) {
	    if (!this.isActive) {
	      return;
	    }
	    this.hoverStageId = stage.getId();
	    for (let [id, currentStage] of this.stages) {
	      currentStage.addBackLight(stage.getColor());
	      if (id === stage.getId()) {
	        break;
	      }
	    }
	    this.increaseStageWidthForNameVisibility(stage);
	  }
	  onStageMouseLeave(stage) {
	    if (!this.isActive) {
	      return;
	    }
	    main_core.Dom.style(stage.node, {
	      flexBasis: null,
	      flexGrow: null
	    });
	    for (let [id, currentStage] of this.stages) {
	      currentStage.removeBackLight();
	      if (id === stage.getId()) {
	        break;
	      }
	    }
	  }
	  onStageClick(stage) {
	    if (!this.isActive) {
	      return;
	    }
	    if (stage.getId() !== this.currentStage && main_core.Type.isFunction(this.onStageChange)) {
	      this.onStageChange(stage);
	    }
	    const popup = this.getSemanticSelectorPopup();
	    if (popup.isShown()) {
	      popup.close();
	    }
	  }
	  onFinalStageClick(stage) {
	    if (!this.isActive) {
	      return;
	    }
	    if (this.getFailStages().length <= 0) {
	      this.onStageClick(this.getSuccessStage());
	    } else {
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
	  addBackLightUpToStage(stageId = null, color = null) {
	    if (!stageId) {
	      stageId = this.currentStage;
	    }
	    const currentStage = this.getStageById(stageId);
	    if (currentStage && !color) {
	      color = currentStage.getColor();
	    }
	    let isFilled = !!stageId;
	    this.stages.forEach(stage => {
	      stage.isFilled = isFilled;
	      if (stage.isFilled) {
	        stage.addBackLight(color ? color : stage.getColor());
	      } else {
	        stage.removeBackLight();
	      }
	      if (!stage.isFinal() && isFilled && stage.getId() === stageId) {
	        isFilled = false;
	      }
	    });
	  }
	  getSemanticSelectorPopup() {
	    let popup = main_popup.PopupManager.getPopupById(semanticSelectorPopupId);
	    if (!popup) {
	      popup = main_popup.PopupManager.create({
	        id: semanticSelectorPopupId,
	        autoHide: true,
	        closeByEsc: true,
	        closeIcon: true,
	        maxWidth: 420,
	        content: main_core.Tag.render(_t2$1 || (_t2$1 = _$1`<div class="ui-stageflow-popup-title">${0}</div>`), this.labels.finalStagePopupTitle),
	        buttons: [this.getSemanticPopupSuccessButton(), this.getSemanticPopupFailureButton()],
	        events: {
	          onClose: () => {
	            this.setCurrentStageId(this.currentStage);
	            this.isActive = true;
	          }
	        }
	      });
	    }
	    return popup;
	  }
	  getSemanticPopupSuccessButton() {
	    return new BX.UI.Button({
	      color: BX.UI.Button.Color.SUCCESS,
	      text: this.getSuccessStage().getName(),
	      onclick: () => {
	        this.isActive = true;
	        this.onStageClick(this.getSuccessStage());
	      }
	    });
	  }
	  getSemanticPopupFailureButton() {
	    const failureSemanticText = this.getFailStageName();
	    if (!failureSemanticText) {
	      return null;
	    }
	    return new BX.UI.Button({
	      color: BX.UI.Button.Color.DANGER,
	      text: failureSemanticText,
	      onclick: () => {
	        var _PopupManager$getPopu;
	        (_PopupManager$getPopu = main_popup.PopupManager.getPopupById(semanticSelectorPopupId)) == null ? void 0 : _PopupManager$getPopu.close();
	        const finalStagePopup = this.getFinalStageSelectorPopup();
	        finalStagePopup.show();
	        this.isActive = false;
	      }
	    });
	  }
	  getFinalStageSemanticSelector(isSuccess = null) {
	    if (!this.finalStageSemanticSelector) {
	      this.finalStageSemanticSelector = main_core.Tag.render(_t3 || (_t3 = _$1`<div class="ui-stageflow-stage-selector-option ui-stageflow-stage-selector-option-fail" onclick="${0}"></div>`), this.onSemanticSelectorClick.bind(this));
	    }
	    if (main_core.Type.isBoolean(isSuccess)) {
	      let realFinalStage = null;
	      let failStageName = this.getFailStageName();
	      if (isSuccess || !failStageName) {
	        this.finalStageSemanticSelector.classList.add('ui-stageflow-stage-selector-option-success');
	        this.finalStageSemanticSelector.classList.remove('ui-stageflow-stage-selector-option-fail');
	        this.finalStageSemanticSelector.innerText = this.getSuccessStage().getName();
	        realFinalStage = this.getSuccessStage();
	      } else {
	        this.finalStageSemanticSelector.classList.add('ui-stageflow-stage-selector-option-fail');
	        this.finalStageSemanticSelector.classList.remove('ui-stageflow-stage-selector-option-success');
	        this.finalStageSemanticSelector.innerText = failStageName;
	        realFinalStage = this.getFirstFailStage();
	      }
	      const finalStage = this.getFinalStage();
	      if (finalStage && realFinalStage) {
	        finalStage.setColor(realFinalStage.getColor()).setName(realFinalStage.getName());
	      }
	      this.addBackLightUpToStage(finalStage.getId(), finalStage.getColor());
	    }
	    return this.finalStageSemanticSelector;
	  }
	  getFinalStageSelectorPopup(isSuccess = false) {
	    let popup = main_popup.PopupManager.getPopupById(finalStageSelectorPopupId);
	    if (!popup) {
	      popup = main_popup.PopupManager.create({
	        id: finalStageSelectorPopupId,
	        autoHide: false,
	        closeByEsc: true,
	        closeIcon: true,
	        width: 420,
	        titleBar: true,
	        buttons: [new BX.UI.SaveButton({
	          onclick: () => {
	            popup.close();
	            const stage = this.getSelectedFinalStage();
	            if (stage) {
	              this.onStageClick(stage);
	            }
	          }
	        }), new BX.UI.CancelButton({
	          onclick: () => {
	            popup.close();
	          }
	        })],
	        events: {
	          onClose: () => {
	            this.setCurrentStageId(this.currentStage);
	            this.isActive = true;
	          }
	        }
	      });
	    }
	    popup.setContent(this.getFinalStagePopupFailStagesWrapper(isSuccess));
	    popup.setTitleBar(this.getFinalStagePopupTitleBar(isSuccess));
	    return popup;
	  }
	  getFinalStagePopupFailStagesWrapper(isSuccess = false) {
	    const failStageListWrapper = main_core.Tag.render(_t4 || (_t4 = _$1`<div class="ui-stageflow-final-fail-stage-list-wrapper"></div>`));
	    if (isSuccess) {
	      return failStageListWrapper;
	    }
	    const failStages = this.getFailStages();
	    if (failStages.length > 1) {
	      failStages.forEach(stage => {
	        main_core.Dom.append(this.getFinalStagePopupFailStage(stage), failStageListWrapper);
	      });
	      this.setCheckedStageInFailStagesWrapper(failStageListWrapper);
	    }
	    return failStageListWrapper;
	  }
	  setCheckedStageInFailStagesWrapper(failStageListWrapper) {
	    const failStagesNodeList = this.extractFinalStagePopupFailStages(failStageListWrapper);
	    if (!main_core.Type.isArrayFilled(failStagesNodeList)) {
	      return;
	    }
	    const firstFailStageInput = failStagesNodeList[0].querySelector('input');
	    if (firstFailStageInput) {
	      firstFailStageInput.checked = true;
	    }
	  }
	  extractFinalStagePopupFailStages(failStageListWrapper) {
	    var _failStageListWrapper;
	    return (_failStageListWrapper = failStageListWrapper.querySelectorAll('.ui-stageflow-final-fail-stage-list-section')) != null ? _failStageListWrapper : [];
	  }
	  getFinalStagePopupFailStage(stage) {
	    return main_core.Tag.render(_t5 || (_t5 = _$1`
			<div class="ui-stageflow-final-fail-stage-list-section">
				<input
					data-stage-id="${0}"
					id="ui-stageflow-final-fail-stage-${0}"
					name="ui-stageflow-final-fail-stage-input"
					class="crm-list-fail-deal-button"
					type="radio"
				>
				<label for="ui-stageflow-final-fail-stage-${0}">${0}</label>
			</div>
		`), stage.getId(), stage.getId(), stage.getId(), stage.getName());
	  }
	  getFinalStagePopupTitleBar(isSuccess = false) {
	    const titleBar = {};
	    titleBar.content = main_core.Tag.render(_t6 || (_t6 = _$1`
			<div class="ui-stageflow-stage-selector-block">
				<span>${0}</span>
				${0}
			</div>
		`), this.labels.finalStageSelectorTitle, this.getFinalStageSemanticSelector(isSuccess));
	    return titleBar;
	  }
	  onSemanticSelectorClick() {
	    const failStageName = this.getFailStageName();
	    const menu = main_popup.MenuManager.create({
	      id: 'ui-stageflow-final-stage-semantic-selector',
	      bindElement: this.getFinalStageSemanticSelector(),
	      items: [{
	        text: this.getSuccessStage().getName(),
	        onclick: () => {
	          this.getFinalStageSelectorPopup(true);
	          menu.close();
	        }
	      }, failStageName ? {
	        text: failStageName,
	        onclick: () => {
	          this.getFinalStageSelectorPopup(false);
	          menu.close();
	        }
	      } : null]
	    });
	    menu.show();
	  }
	  getSelectedFinalStage() {
	    const finalStageSemanticSelector = this.getFinalStageSemanticSelector();
	    if (finalStageSemanticSelector.classList.contains('ui-stageflow-stage-selector-option-success')) {
	      return this.getSuccessStage();
	    } else {
	      const failStages = this.getFailStages();
	      if (failStages.length > 1) {
	        const finalStageSelectorPopupContainer = document.getElementById(finalStageSelectorPopupId);
	        if (finalStageSelectorPopupContainer) {
	          const selectedInput = finalStageSelectorPopupContainer.querySelector('input:checked');
	          if (selectedInput) {
	            const failStage = this.getStageById(main_core.Text.toInteger(selectedInput.dataset.stageId));
	            if (failStage) {
	              return failStage;
	            }
	          }
	        }
	      }
	      return this.getFirstFailStage();
	    }
	  }
	  getFailStageName() {
	    const failStagesLength = this.getFailStages().length;
	    if (failStagesLength <= 0) {
	      return null;
	    } else if (failStagesLength === 1) {
	      return this.getFirstFailStageName();
	    } else {
	      return this.labels.finalStagePopupFail;
	    }
	  }
	  getFirstFailStageName() {
	    var _this$getFirstFailSta;
	    return (_this$getFirstFailSta = this.getFirstFailStage()) == null ? void 0 : _this$getFirstFailSta.getName();
	  }
	  increaseStageWidthForNameVisibility(stage) {
	    if (!stage.isNameCropped()) {
	      return;
	    }
	    main_core.Dom.style(stage.node, {
	      flexGrow: 0,
	      flexBasis: `${stage.getMinWidthForFullNameVisibility()}px`
	    });
	  }
	}

	const StageFlow = {
	  Chart,
	  Stage
	};

	exports.StageFlow = StageFlow;

}((this.BX.UI = this.BX.UI || {}),BX.UI,BX,BX.Main));
//# sourceMappingURL=stageflow.bundle.js.map
