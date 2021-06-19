this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var UiCircle = /*#__PURE__*/function () {
	  function UiCircle(domNode, perimetr, progressBar, settings) {
	    babelHelpers.classCallCheck(this, UiCircle);
	    this.domNode = domNode;
	    this.progressBar = Number(progressBar) > 100 ? 100 : progressBar;
	    this.progressBg = null;
	    this.number = null;
	    this.fixCounter = settings.fixCounter ? settings.fixCounter : null;
	    this.color1 = settings.color1;
	    this.color2 = settings.color2;
	    this.x = 0;
	    this.flat = 400;
	    this.speed = 3;
	    this.rate = 0;
	    this.wave = 35;
	    this.distance = 200;
	  }

	  babelHelpers.createClass(UiCircle, [{
	    key: "createCanvas",
	    value: function createCanvas() {
	      this.canvas = main_core.Dom.create('canvas', {
	        attrs: {
	          className: 'ui-graph-circle-canvas' // 'data-progress': this.progressBar

	        }
	      });
	      return this.canvas;
	    }
	  }, {
	    key: "createNumberBlock",
	    value: function createNumberBlock() {
	      this.number = main_core.Dom.create('div', {
	        attrs: {
	          className: 'ui-graph-circle-number',
	          'data-progress': this.progressBar
	        }
	      });
	      return this.number;
	    }
	  }, {
	    key: "drawCircle",
	    value: function drawCircle(ctx, mW, color) {
	      ctx.mozImageSmoothingEnabled = false;
	      ctx.imageSmoothingEnabled = false;
	      ctx.beginPath();
	      ctx.arc(mW / 2, mW / 2, mW / 2 - 1, 0, 2 * Math.PI);
	      ctx.strokeStyle = color;
	      ctx.stroke();
	      ctx.beginPath();
	      ctx.arc(mW / 2, mW / 2, mW / 2 - 2, 0, 2 * Math.PI);
	      ctx.clip();
	    }
	  }, {
	    key: "drawSin",
	    value: function drawSin(ctx, mW, color1, color2, wav, dY) {
	      ctx.mozImageSmoothingEnabled = false;
	      ctx.imageSmoothingEnabled = false;
	      ctx.save();
	      ctx.beginPath();
	      ctx.moveTo(0, mW);
	      ctx.lineTo(0, dY);
	      ctx.quadraticCurveTo(mW / 4, dY - mW * (wav / 200), mW / 2, dY);
	      ctx.lineTo(mW / 2, dY);
	      ctx.quadraticCurveTo(mW * 3 / 4, dY + mW * (wav / 200), mW, dY);
	      ctx.lineTo(mW, mW);
	      ctx.lineTo(0, mW);
	      ctx.fillStyle = color1;
	      ctx.fill();
	      ctx.restore();
	    }
	  }, {
	    key: "init",
	    value: function init(mW) {
	      var canvas1 = this.canvas;
	      canvas1.style.height = mW;
	      canvas1.width = canvas1.height = mW;
	      var canvas2 = document.createElement('canvas'),
	          ctx2 = canvas2.getContext('2d');
	      canvas2.width = mW;
	      canvas2.height = mW;
	      var canvas3 = document.createElement('canvas'),
	          ctx3 = canvas3.getContext('2d');
	      canvas3.width = mW;
	      canvas3.height = mW;
	      var x = this.x;
	      var flat = this.flat;
	      var speed = this.speed;
	      var rate = this.rate;
	      var wave = this.wave;
	      var distance = this.distance;
	      var ctx1 = canvas1.getContext('2d');

	      if (!this.color1 && !this.color2) {
	        this.drawSin(ctx2, mW, 'rgba(183, 235, 129, .45)', 'rgba(183, 235, 129, .45)', wave, mW - mW * rate);
	        this.drawSin(ctx3, mW, 'rgba(178, 232, 0, .29)', 'rgba(178, 232, 0, .29)', wave, mW - mW * rate);
	      } else {
	        this.drawSin(ctx2, mW, this.color1, this.color1, wave, mW - mW * rate);
	        this.drawSin(ctx3, mW, this.color2, this.color2, wave, mW - mW * rate);
	      }

	      function animation() {
	        ctx1.clearRect(0, 0, mW, mW);
	        ctx1.drawImage(canvas2, x, 0, mW + flat, mW);
	        ctx1.drawImage(canvas2, x - mW - flat, 0, mW + flat, mW);
	        ctx1.drawImage(canvas3, x - distance, 0, mW + flat, mW);
	        ctx1.drawImage(canvas3, x - mW - distance - flat, 0, mW + flat, mW);
	        x >= mW - speed + flat ? x = 0 : x += speed;
	        requestAnimationFrame(animation);
	      }

	      animation();
	    }
	  }, {
	    key: "animateWavesBlock",
	    value: function animateWavesBlock(fixCounter) {
	      this.progress = this.progressBar;

	      if (fixCounter) {
	        if (this.progress <= 50) {
	          this.rate = 0.45;
	        }

	        if (this.progress > 50) {
	          this.rate = 0.75;
	        }

	        this.progressBar <= 25 ? this.rate = 0.25 : null;
	      } else {
	        if (this.progress <= 50) {
	          this.rate = 0.50;
	        }

	        if (this.progress > 50) {
	          this.rate = 0.85;
	        }
	      }

	      this.progressBar <= 25 ? this.progress = 25 : null;
	    }
	  }, {
	    key: "createWrapper",
	    value: function createWrapper() {
	      this.graph = main_core.Dom.create('div', {
	        attrs: {
	          className: 'ui-graph-circle-wrapper'
	        }
	      });
	      main_core.Dom.append(this.createCanvas(), this.graph);
	      main_core.Dom.append(this.createNumberBlock(), this.graph);
	      return this.graph;
	    }
	  }, {
	    key: "addWrapperClass",
	    value: function addWrapperClass() {
	      main_core.Dom.addClass(this.graph, 'ui-graph-circle-wrapper-animate');

	      if (this.fixCounter) {
	        main_core.Dom.addClass(this.graph, 'ui-graph-circle-counter');
	      }
	    }
	  }, {
	    key: "animateNumber",
	    value: function animateNumber(fixCounter) {
	      var progress = this.progressBar;

	      if (fixCounter) {
	        progress = fixCounter;
	      }

	      if (Number(progress) <= 0) {
	        if (fixCounter) {
	          this.number.innerHTML = '0';
	        } else {
	          this.number.innerHTML = '0' + ' <span>%</span>';
	        }

	        return;
	      }

	      if (fixCounter) {
	        this.number.innerHTML = fixCounter;
	        this.number.innerHTML.length >= 4 ? main_core.Dom.addClass(this.graph, 'ui-graph-font-sm') : null;
	      } else {
	        var i = 0;
	        var time = 1000 / progress;
	        var interval = setInterval(function () {
	          i++;
	          this.number.innerHTML = i + ' <span>%</span>';
	          i === Number(progress) ? clearInterval(interval) : null;
	        }.bind(this), time);
	      }
	    }
	  }, {
	    key: "updateCounter",
	    value: function updateCounter(counter, fixCounter) {
	      this.progressBar = counter;

	      if (fixCounter) {
	        this.animateNumber(fixCounter);
	        this.animateWavesBlock(fixCounter);
	      } else {
	        this.animateNumber();
	        this.animateWavesBlock();
	      }
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      main_core.Dom.append(this.createWrapper(), this.domNode);
	      setTimeout(function () {
	        this.addWrapperClass();
	        this.animateNumber(this.fixCounter);
	        this.animateWavesBlock(this.fixCounter);
	        this.init(200);
	      }.bind(this), 500);
	    }
	  }]);
	  return UiCircle;
	}();

	exports.UiCircle = UiCircle;

}((this.BX.UI = this.BX.UI || {}),BX));
//# sourceMappingURL=ui.circle.bundle.js.map
