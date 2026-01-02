/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports) {
	'use strict';

	var _intervalId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("intervalId");
	class GenerationObserver {
	  constructor(generationId) {
	    Object.defineProperty(this, _intervalId, {
	      writable: true,
	      value: null
	    });
	    this.generationId = generationId;
	    this.onGenerationRestart = this.onGenerationRestart.bind(this);
	  }
	  observe() {
	    this.restartObserve();
	    BX.PULL.subscribe({
	      type: BX.PullClient.SubscriptionType.Server,
	      moduleId: 'landing',
	      callback: event => {
	        if (event.params.generationId !== undefined && event.params.generationId !== this.generationId) {
	          return;
	        }
	        const command = event.command;
	        if (command === 'LandingCopilotGeneration:onStepExecute' || command === 'LandingCopilotGeneration:onPreviewImageCreate' || command === 'LandingCopilotGeneration:onCopilotImageCreate') {
	          this.restartObserve();
	        }
	        if (command === 'LandingCopilotGeneration:onCopilotTimeIsOver') {
	          this.onGenerationRestart();
	        }
	        if (command === 'LandingCopilotGeneration:onGenerationError' || command === 'LandingCopilotGeneration:onGenerationFinish') {
	          this.stopObserve();
	        }
	      }
	    });
	  }
	  restartObserve() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _intervalId)[_intervalId]) {
	      clearInterval(babelHelpers.classPrivateFieldLooseBase(this, _intervalId)[_intervalId]);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _intervalId)[_intervalId] = setInterval(this.onGenerationRestart, GenerationObserver.INTERVAL);
	  }
	  stopObserve() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _intervalId)[_intervalId]) {
	      clearInterval(babelHelpers.classPrivateFieldLooseBase(this, _intervalId)[_intervalId]);
	    }
	  }
	  onGenerationRestart() {
	    BX.ajax.runAction('landing.api.copilot.executeGeneration', {
	      data: {
	        generationId: this.generationId
	      }
	    });
	    this.restartObserve();
	  }
	}
	GenerationObserver.INTERVAL = 30000;

	exports.GenerationObserver = GenerationObserver;

}((this.BX.Landing.Copilot = this.BX.Landing.Copilot || {})));
//# sourceMappingURL=generation-observer.bundle.js.map
