this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core) {
    'use strict';

    let isStop = false;
    class Circle {
      constructor(domNode, perimetr, progressBar, fixCounter, withoutWaves) {
        this.domNode = domNode;
        this.perimetr = perimetr;
        this.radius = perimetr / 2;
        this.progressBar = Number(progressBar) > 100 ? 100 : progressBar;
        this.progressBg = null;
        this.number = null;
        this.waves = null;
        this.leftWave = null;
        this.rightWave = null;
        this.fixCounter = fixCounter ? fixCounter : null;
        this.withoutWaves = withoutWaves ? withoutWaves : null;
      }
      getCircumFerence() {
        return (this.radius - 10) * 2 * 3.14;
      }
      getCircumProgress() {
        return this.getCircumFerence() - this.getCircumFerence() / 100 * this.progressBar;
      }
      createCircle() {
        this.svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        this.svg.setAttributeNS(null, 'class', 'ui-graph-circle-bar');
        this.svg.setAttributeNS(null, 'viewport', '0 0 ' + this.radius + ' ' + this.radius);
        this.svg.setAttributeNS(null, 'width', this.perimetr);
        this.svg.setAttributeNS(null, 'height', this.perimetr);
        this.progressBg = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        this.progressBg.setAttributeNS(null, 'r', this.radius - 10);
        this.progressBg.setAttributeNS(null, 'cx', this.radius);
        this.progressBg.setAttributeNS(null, 'cy', this.radius);
        this.progressBg.setAttributeNS(null, 'class', 'ui-graph-circle-bar-bg');
        this.progressMove = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        this.progressMove.setAttributeNS(null, 'r', this.radius - 10);
        this.progressMove.setAttributeNS(null, 'cx', this.radius);
        this.progressMove.setAttributeNS(null, 'cy', this.radius);
        this.progressMove.setAttributeNS(null, 'stroke-dasharray', this.getCircumFerence());
        this.progressMove.setAttributeNS(null, 'stroke-dashoffset', this.getCircumFerence());
        this.progressMove.setAttributeNS(null, 'class', 'ui-graph-circle-bar-progress');
        main_core.Dom.append(this.progressBg, this.svg);
        main_core.Dom.append(this.progressMove, this.svg);
        return this.svg;
      }
      animateFixedBar() {
        this.svg.setAttributeNS(null, 'class', 'ui-graph-circle-bar ui-graph-circle-bar-animate ui-graph-circle-bar-without-animate');
        this.progressMove.setAttributeNS(null, 'stroke-dashoffset', 0);
      }
      animateProgressBar() {
        this.svg.setAttributeNS(null, 'class', 'ui-graph-circle-bar ui-graph-circle-bar-animate');
        this.progressMove.setAttributeNS(null, 'stroke-dashoffset', this.getCircumProgress());
      }
      createNumberBlock() {
        this.number = main_core.Dom.create('div', {
          attrs: {
            className: 'ui-graph-circle-number',
            'data-progress': this.progressBar
          }
        });
        return this.number;
      }
      createWavesBlock() {
        return main_core.Dom.create('div', {
          attrs: {
            className: 'ui-graph-circle-waves-wrapper'
          },
          children: [this.waves = main_core.Dom.create('div', {
            attrs: {
              className: 'ui-graph-circle-waves'
            },
            children: [this.leftWave = main_core.Dom.create('div', {
              attrs: {
                className: 'ui-graph-circle-waves-left'
              }
            }), this.rightWave = main_core.Dom.create('div', {
              attrs: {
                className: 'ui-graph-circle-waves-right'
              }
            })]
          })]
        });
      }
      animateWavesBlock(fixCounter) {
        let progress = this.progressBar;
        if (fixCounter) {
          if (progress <= 50) {
            progress = 45;
          }
          if (progress > 50) {
            progress = 85;
          }
          this.progressBar <= 25 ? progress = 25 : null;
          this.waves.style.transform = 'translateY(-' + progress + '%)';
        }
        this.progressBar <= 25 ? progress = 25 : null;
        this.waves.style.transform = 'translateY(-' + progress + '%)';
      }
      animateBothWaves() {
        let currentPosWaveLeft = 0;
        let currentPosWaveRight = 50;
        let fps = 15;
        let now;
        let then = Date.now();
        let interval = 1000 / fps;
        let delta;
        function draw() {
          if (isStop) {
            return;
          }
          requestAnimationFrame(draw);
          now = Date.now();
          delta = now - then;
          if (delta > interval) {
            then = now - delta % interval;
            const leftWave = document.querySelector('.ui-graph-circle-waves-left');
            const rightWave = document.querySelector('.ui-graph-circle-waves-right');
            currentPosWaveLeft += 1;
            currentPosWaveRight -= 1;
            leftWave.style.transform = 'translate3d(' + currentPosWaveLeft + '%, 0, 0)';
            rightWave.style.transform = 'translate3d(' + currentPosWaveRight + '%, 0, 0)';
            if (parseInt(currentPosWaveLeft, 10) >= 50) {
              currentPosWaveLeft = 0;
            }
            if (parseInt(currentPosWaveRight, 10) <= 0) {
              currentPosWaveRight = 50;
            }
          }
        }
        draw();
      }
      createWrapper() {
        this.graph = main_core.Dom.create('div', {
          attrs: {
            className: 'ui-graph-circle-wrapper'
          }
        });
        main_core.Dom.append(this.createCircle(), this.graph);
        main_core.Dom.append(this.createNumberBlock(), this.graph);
        main_core.Dom.append(this.createWavesBlock(), this.graph);
        return this.graph;
      }
      addWrapperClass() {
        main_core.Dom.addClass(this.graph, 'ui-graph-circle-wrapper-animate');
        if (this.fixCounter) {
          main_core.Dom.addClass(this.graph, 'ui-graph-circle-counter');
        }
      }
      animateNumber(fixCounter) {
        let progress = this.progressBar;
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
          this.number.innerHTML.length >= 4 ? this.graph.classList.add('ui-graph-font-sm') : null;
        } else {
          let i = 0;
          let time = 1000 / progress;
          let interval = setInterval(function () {
            i++;
            this.number.innerHTML = i + ' <span>%</span>';
            i === Number(progress) ? clearInterval(interval) : null;
          }.bind(this), time);
        }
      }
      updateCounter(counter, fixCounter) {
        this.progressBar = counter;
        if (fixCounter) {
          this.progressMove.setAttributeNS(null, 'stroke-dashoffset', 0);
        } else {
          this.progressMove.setAttributeNS(null, 'stroke-dashoffset', this.getCircumProgress());
        }
        this.animateNumber(fixCounter);
        this.animateWavesBlock(fixCounter);
      }
      show() {
        isStop = false;
        main_core.Dom.append(this.createWrapper(), this.domNode);
        setTimeout(function () {
          this.addWrapperClass();
          this.animateNumber(this.fixCounter);
          if (this.fixCounter) {
            this.animateFixedBar();
          } else {
            this.animateProgressBar();
          }
          if (this.withoutWaves) {
            return;
          } else {
            this.animateBothWaves();
            this.animateWavesBlock(this.fixCounter);
          }
        }.bind(this), 500);
      }
      stop() {
        isStop = true;
      }
    }

    exports.Circle = Circle;

}((this.BX.UI.Graph = this.BX.UI.Graph || {}),BX));
//# sourceMappingURL=ui.circle.bundle.js.map
