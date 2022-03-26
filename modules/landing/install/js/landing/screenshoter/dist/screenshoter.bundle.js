this.BX = this.BX || {};
(function (exports,landing_pageobject,main_core) {
    'use strict';

    var __awaiter = undefined && undefined.__awaiter || function (thisArg, _arguments, P, generator) {
      function adopt(value) {
        return value instanceof P ? value : new P(function (resolve) {
          resolve(value);
        });
      }

      return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) {
          try {
            step(generator.next(value));
          } catch (e) {
            reject(e);
          }
        }

        function rejected(value) {
          try {
            step(generator["throw"](value));
          } catch (e) {
            reject(e);
          }
        }

        function step(result) {
          result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected);
        }

        step((generator = generator.apply(thisArg, _arguments || [])).next());
      });
    };

    var WOFF = 'application/font-woff';
    var JPEG = 'image/jpeg';
    var mimes = {
      woff: WOFF,
      woff2: WOFF,
      ttf: 'application/font-truetype',
      eot: 'application/vnd.ms-fontobject',
      png: 'image/png',
      jpg: JPEG,
      jpeg: JPEG,
      gif: 'image/gif',
      tiff: 'image/tiff',
      svg: 'image/svg+xml'
    };
    var uuid = function uuid() {
      // generate uuid for className of pseudo elements.
      // We should not use GUIDs, otherwise pseudo elements sometimes cannot be captured.
      var counter = 0; // ref: http://stackoverflow.com/a/6248722/2519373

      var random = function random() {
        return "0000".concat((Math.random() * Math.pow(36, 4) << 0).toString(36)).slice(-4);
      };

      return function () {
        counter += 1;
        return "u".concat(random()).concat(counter);
      };
    }();
    function getExtension(url) {
      var match = /\.([^./]*?)$/g.exec(url);
      return match ? match[1] : '';
    }
    function getMimeType(url) {
      var ext = getExtension(url).toLowerCase();
      return mimes[ext] || '';
    }
    function delay(ms) {
      return function (args) {
        return new Promise(function (resolve) {
          setTimeout(function () {
            resolve(args);
          }, ms);
        });
      };
    }
    function isDataUrl(url) {
      return url.search(/^(data:)/) !== -1;
    }
    function toDataURL(content, mimeType) {
      return "data:".concat(mimeType, ";base64,").concat(content);
    }
    function getDataURLContent(dataURL) {
      return dataURL.split(/,/)[1];
    }

    function toBlob(canvas) {
      return new Promise(function (resolve) {
        var binaryString = window.atob(canvas.toDataURL().split(',')[1]);
        var len = binaryString.length;
        var binaryArray = new Uint8Array(len);

        for (var i = 0; i < len; i += 1) {
          binaryArray[i] = binaryString.charCodeAt(i);
        }

        resolve(new Blob([binaryArray], {
          type: 'image/png'
        }));
      });
    }

    function canvasToBlob(canvas) {
      if (canvas.toBlob) {
        return new Promise(function (resolve) {
          return canvas.toBlob(resolve);
        });
      }

      return toBlob(canvas);
    }
    function toArray(arrayLike) {
      var result = [];

      for (var i = 0, l = arrayLike.length; i < l; i += 1) {
        result.push(arrayLike[i]);
      }

      return result;
    }

    function px(node, styleProperty) {
      var val = window.getComputedStyle(node).getPropertyValue(styleProperty);
      return parseFloat(val.replace('px', ''));
    }

    function getNodeWidth(node) {
      var leftBorder = px(node, 'border-left-width');
      var rightBorder = px(node, 'border-right-width');
      return node.clientWidth + leftBorder + rightBorder;
    }
    function getNodeHeight(node) {
      var topBorder = px(node, 'border-top-width');
      var bottomBorder = px(node, 'border-bottom-width');
      return node.clientHeight + topBorder + bottomBorder;
    }
    function getPixelRatio() {
      var ratio;
      var FINAL_PROCESS;

      try {
        FINAL_PROCESS = process;
      } catch (e) {}

      var val = FINAL_PROCESS && FINAL_PROCESS.env ? FINAL_PROCESS.env.devicePixelRatio : null;

      if (val) {
        ratio = parseInt(val, 10);

        if (isNaN(ratio)) {
          ratio = 1;
        }
      }

      return ratio || window.devicePixelRatio || 1;
    }
    function createImage(url) {
      return new Promise(function (resolve, reject) {
        var image = new Image();

        image.onload = function () {
          return resolve(image);
        };

        image.onerror = reject;
        image.crossOrigin = 'anonymous';
        image.src = url;
      });
    }
    function svgToDataURL(svg) {
      return __awaiter(this, void 0, void 0, /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                return _context.abrupt("return", Promise.resolve().then(function () {
                  return new XMLSerializer().serializeToString(svg);
                }).then(encodeURIComponent).then(function (html) {
                  return "data:image/svg+xml;charset=utf-8,".concat(html);
                }));

              case 1:
              case "end":
                return _context.stop();
            }
          }
        }, _callee);
      }));
    }

    /* tslint:disable:max-line-length */
    // -----------
    // Can not handle redirect-url, such as when access 'http://something.com/avatar.png'
    // will redirect to 'http://something.com/65fc2ffcc8aea7ba65a1d1feda173540'

    var TIMEOUT = 30000;
    var cache = {};

    function isFont(filename) {
      return /ttf|otf|eot|woff2?/i.test(filename);
    }

    function getBlobFromURL(url, options) {
      var href = url;

      if (BX.Type.isStringFilled(url) && url.startsWith('http') && !url.startsWith(window.location.origin) && (url.endsWith('.svg') || url.endsWith('.png') || url.endsWith('.jpg') || url.endsWith('.gif'))) {
        url = BX.Uri.addParam('/bitrix/tools/landing/proxy.php', {
          sessid: BX.bitrix_sessid(),
          url: url
        });
      }

      if (isFont(href)) {
        href = href.replace(/.*\//, '');
      }

      if (cache[href]) {
        return cache[href];
      } // cache bypass so we dont have CORS issues with cached images
      // ref: https://developer.mozilla.org/en/docs/Web/API/XMLHttpRequest/Using_XMLHttpRequest#Bypassing_the_cache


      if (options.cacheBust) {
        // tslint:disable-next-line
        url += (/\?/.test(url) ? '&' : '?') + new Date().getTime();
      }

      var failed = function failed(reason) {
        var placeholder = '';

        if (options.imagePlaceholder) {
          var parts = options.imagePlaceholder.split(/,/);

          if (parts && parts[1]) {
            placeholder = parts[1];
          }
        }

        var msg = "Failed to fetch resource: ".concat(url);

        if (reason) {
          msg = typeof reason === 'string' ? reason : reason.message;
        }

        if (msg) {
          console.error(msg);
        }

        return placeholder;
      };

      var deferred = window.fetch ? window.fetch(url, {
        mode: 'no-cors'
      }).then(function (res) {
        return res.blob().then(function (blob) {
          return {
            blob: blob,
            contentType: res.headers.get('Content-Type') || ''
          };
        });
      }).then(function (_ref) {
        var blob = _ref.blob,
            contentType = _ref.contentType;
        return new Promise(function (resolve, reject) {
          var reader = new FileReader();

          reader.onloadend = function () {
            return resolve({
              contentType: contentType,
              blob: reader.result
            });
          };

          reader.onerror = reject;
          reader.readAsDataURL(blob);
        });
      }).then(function (_ref2) {
        var blob = _ref2.blob,
            contentType = _ref2.contentType;
        return {
          contentType: contentType,
          blob: getDataURLContent(blob)
        };
      }) : new Promise(function (resolve, reject) {
        var req = new XMLHttpRequest();

        var timeout = function timeout() {
          reject(new Error("Timeout of ".concat(TIMEOUT, "ms occured while fetching resource: ").concat(url)));
        };

        var done = function done() {
          if (req.readyState !== 4) {
            return;
          }

          if (req.status !== 200) {
            reject(new Error("Failed to fetch resource: ".concat(url, ", status: ").concat(req.status)));
            return;
          }

          var encoder = new FileReader();

          encoder.onloadend = function () {
            resolve({
              blob: getDataURLContent(encoder.result),
              contentType: req.getResponseHeader('Content-Type') || ''
            });
          };

          encoder.readAsDataURL(req.response);
        };

        req.onreadystatechange = done;
        req.ontimeout = timeout;
        req.responseType = 'blob';
        req.timeout = TIMEOUT;
        req.open('GET', url, true);
        req.send();
      });
      var promise = deferred.catch(failed); // cache result

      cache[href] = promise;
      return promise;
    }

    var Pseudo;

    (function (Pseudo) {
      function clonePseudoElement(nativeNode, clonedNode, pseudo) {
        var style = window.getComputedStyle(nativeNode, pseudo);
        var content = style.getPropertyValue('content');

        if (content === '' || content === 'none') {
          return;
        }

        var className = uuid(); // fix: Cannot assign to read only property 'className' of object '#<…

        try {
          clonedNode.className = "".concat(clonedNode.className, " ").concat(className);
        } catch (err) {
          return;
        }

        var styleElement = document.createElement('style');
        styleElement.appendChild(getPseudoElementStyle(className, pseudo, style));
        clonedNode.appendChild(styleElement);
      }

      Pseudo.clonePseudoElement = clonePseudoElement;

      function getPseudoElementStyle(className, pseudo, style) {
        var selector = ".".concat(className, ":").concat(pseudo);
        var cssText = style.cssText ? formatCssText(style) : formatCssProperties(style);
        return document.createTextNode("".concat(selector, "{").concat(cssText, "}"));
      }

      function formatCssText(style) {
        var content = style.getPropertyValue('content');
        return "".concat(style.cssText, " content: '").concat(content.replace(/'|"/g, ''), "';");
      }

      function formatCssProperties(style) {
        return toArray(style).map(function (name) {
          var value = style.getPropertyValue(name);
          var priority = style.getPropertyPriority(name);
          return "".concat(name, ": ").concat(value).concat(priority ? ' !important' : '', ";");
        }).join(' ');
      }
    })(Pseudo || (Pseudo = {}));

    function clonePseudoElements(nativeNode, clonedNode) {
      var pseudos = [':before', ':after'];
      pseudos.forEach(function (pseudo) {
        return Pseudo.clonePseudoElement(nativeNode, clonedNode, pseudo);
      });
    }

    var __awaiter$1 = undefined && undefined.__awaiter || function (thisArg, _arguments, P, generator) {
      function adopt(value) {
        return value instanceof P ? value : new P(function (resolve) {
          resolve(value);
        });
      }

      return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) {
          try {
            step(generator.next(value));
          } catch (e) {
            reject(e);
          }
        }

        function rejected(value) {
          try {
            step(generator["throw"](value));
          } catch (e) {
            reject(e);
          }
        }

        function step(result) {
          result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected);
        }

        step((generator = generator.apply(thisArg, _arguments || [])).next());
      });
    };

    function cloneSingleNode(node, options) {
      return __awaiter$1(this, void 0, void 0, /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
        var dataURL;
        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                if (!(node instanceof HTMLCanvasElement)) {
                  _context.next = 5;
                  break;
                }

                dataURL = node.toDataURL();

                if (!(dataURL === 'data:,')) {
                  _context.next = 4;
                  break;
                }

                return _context.abrupt("return", Promise.resolve(node.cloneNode(false)));

              case 4:
                return _context.abrupt("return", createImage(dataURL));

              case 5:
                if (!(node instanceof HTMLVideoElement && node.poster)) {
                  _context.next = 7;
                  break;
                }

                return _context.abrupt("return", Promise.resolve(node.poster).then(function (url) {
                  return getBlobFromURL(url, options);
                }).then(function (data) {
                  return toDataURL(data.blob, getMimeType(node.poster) || data.contentType);
                }).then(function (dataURL) {
                  return createImage(dataURL);
                }));

              case 7:
                return _context.abrupt("return", Promise.resolve(node.cloneNode(false)));

              case 8:
              case "end":
                return _context.stop();
            }
          }
        }, _callee);
      }));
    }

    function cloneChildren(nativeNode, clonedNode, options) {
      var _a;

      return __awaiter$1(this, void 0, void 0, /*#__PURE__*/regeneratorRuntime.mark(function _callee2() {
        var children;
        return regeneratorRuntime.wrap(function _callee2$(_context2) {
          while (1) {
            switch (_context2.prev = _context2.next) {
              case 0:
                children = toArray(((_a = nativeNode.shadowRoot) !== null && _a !== void 0 ? _a : nativeNode).childNodes);

                if (!(children.length === 0)) {
                  _context2.next = 3;
                  break;
                }

                return _context2.abrupt("return", Promise.resolve(clonedNode));

              case 3:
                return _context2.abrupt("return", children.reduce(function (done, child) {
                  return done.then(function () {
                    return cloneNode(child, options);
                  }).then(function (clonedChild) {
                    if (clonedChild) {
                      clonedNode.appendChild(clonedChild);
                    }
                  });
                }, Promise.resolve()).then(function () {
                  return clonedNode;
                }));

              case 4:
              case "end":
                return _context2.stop();
            }
          }
        }, _callee2);
      }));
    }

    function decorate(nativeNode, clonedNode) {
      return __awaiter$1(this, void 0, void 0, /*#__PURE__*/regeneratorRuntime.mark(function _callee3() {
        return regeneratorRuntime.wrap(function _callee3$(_context3) {
          while (1) {
            switch (_context3.prev = _context3.next) {
              case 0:
                if (clonedNode instanceof Element) {
                  _context3.next = 2;
                  break;
                }

                return _context3.abrupt("return", clonedNode);

              case 2:
                return _context3.abrupt("return", Promise.resolve().then(function () {
                  return cloneCssStyle(nativeNode, clonedNode);
                }).then(function () {
                  return clonePseudoElements(nativeNode, clonedNode);
                }).then(function () {
                  return cloneInputValue(nativeNode, clonedNode);
                }).then(function () {
                  return clonedNode;
                }));

              case 3:
              case "end":
                return _context3.stop();
            }
          }
        }, _callee3);
      }));
    }

    function cloneCssStyle(nativeNode, clonedNode) {
      var source = window.getComputedStyle(nativeNode);
      var target = clonedNode.style;

      if (!target) {
        return;
      }

      if (source.cssText) {
        target.cssText = source.cssText;
      } else {
        toArray(source).forEach(function (name) {
          target.setProperty(name, source.getPropertyValue(name), source.getPropertyPriority(name));
        });
      }
    }

    function cloneInputValue(nativeNode, clonedNode) {
      if (nativeNode instanceof HTMLTextAreaElement) {
        clonedNode.innerHTML = nativeNode.value;
      }

      if (nativeNode instanceof HTMLInputElement) {
        clonedNode.setAttribute('value', nativeNode.value);
      }
    }

    function cloneNode(nativeNode, options, isRoot) {
      return __awaiter$1(this, void 0, void 0, /*#__PURE__*/regeneratorRuntime.mark(function _callee4() {
        return regeneratorRuntime.wrap(function _callee4$(_context4) {
          while (1) {
            switch (_context4.prev = _context4.next) {
              case 0:
                if (!(!isRoot && options.filter && !options.filter(nativeNode))) {
                  _context4.next = 2;
                  break;
                }

                return _context4.abrupt("return", Promise.resolve(null));

              case 2:
                return _context4.abrupt("return", Promise.resolve(nativeNode).then(function (clonedNode) {
                  return cloneSingleNode(clonedNode, options);
                }).then(function (clonedNode) {
                  return cloneChildren(nativeNode, clonedNode, options);
                }).then(function (clonedNode) {
                  return decorate(nativeNode, clonedNode);
                }));

              case 3:
              case "end":
                return _context4.stop();
            }
          }
        }, _callee4);
      }));
    }

    var URL_REGEX = /url\((['"]?)([^'"]+?)\1\)/g;
    var URL_WITH_FORMAT_REGEX = /url\([^)]+\)\s*format\((["'])([^"']+)\1\)/g;
    var FONT_SRC_REGEX = /src:\s*(?:url\([^)]+\)\s*format\([^)]+\)[,;]\s*)+/g;
    function shouldEmbed(string) {
      return string.search(URL_REGEX) !== -1;
    }
    function embedResources(cssString, baseUrl, options) {
      if (!shouldEmbed(cssString)) {
        return Promise.resolve(cssString);
      }

      var filteredCssString = filterPreferredFontFormat(cssString, options);
      return Promise.resolve(filteredCssString).then(parseURLs).then(function (urls) {
        return urls.reduce(function (done, url) {
          return done.then(function (ret) {
            return embed(ret, url, baseUrl, options);
          });
        }, Promise.resolve(filteredCssString));
      });
    }
    function filterPreferredFontFormat(str, _ref) {
      var preferredFontFormat = _ref.preferredFontFormat;
      return !preferredFontFormat ? str : str.replace(FONT_SRC_REGEX, function (match) {
        while (true) {
          var _ref2 = URL_WITH_FORMAT_REGEX.exec(match) || [],
              _ref3 = babelHelpers.slicedToArray(_ref2, 3),
              src = _ref3[0],
              format = _ref3[2];

          if (!format) {
            return '';
          }

          if (format === preferredFontFormat) {
            return "src: ".concat(src, ";");
          }
        }
      });
    }
    function parseURLs(str) {
      var result = [];
      str.replace(URL_REGEX, function (raw, quotation, url) {
        result.push(url);
        return raw;
      });
      return result.filter(function (url) {
        return !isDataUrl(url);
      });
    }
    function embed(cssString, resourceURL, baseURL, options, get) {
      var resolvedURL = baseURL ? resolveUrl(resourceURL, baseURL) : resourceURL;
      return Promise.resolve(resolvedURL).then(function (url) {
        return get ? get(url) : getBlobFromURL(url, options);
      }).then(function (data) {
        if (typeof data === 'string') {
          return toDataURL(data, getMimeType(resourceURL));
        }

        return toDataURL(data.blob, getMimeType(resourceURL) || data.contentType);
      }).then(function (dataURL) {
        return cssString.replace(urlToRegex(resourceURL), "$1".concat(dataURL, "$3"));
      }).then(function (content) {
        return content;
      }, function () {
        return resolvedURL;
      });
    }

    function resolveUrl(url, baseUrl) {
      // url is absolute already
      if (url.match(/^[a-z]+:\/\//i)) {
        return url;
      } // url is absolute already, without protocol


      if (url.match(/^\/\//)) {
        return window.location.protocol + url;
      } // dataURI, mailto:, tel:, etc.


      if (url.match(/^[a-z]+:/i)) {
        return url;
      }

      var doc = document.implementation.createHTMLDocument();
      var base = doc.createElement('base');
      var a = doc.createElement('a');
      doc.head.appendChild(base);
      doc.body.appendChild(a);

      if (baseUrl) {
        base.href = baseUrl;
      }

      a.href = url;
      return a.href;
    }

    function urlToRegex(url) {
      return new RegExp("(url\\(['\"]?)(".concat(escape(url), ")(['\"]?\\))"), 'g');
    }

    function escape(url) {
      return url.replace(/([.*+?^${}()|\[\]\/\\])/g, '\\$1');
    }

    var __awaiter$2 = undefined && undefined.__awaiter || function (thisArg, _arguments, P, generator) {
      function adopt(value) {
        return value instanceof P ? value : new P(function (resolve) {
          resolve(value);
        });
      }

      return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) {
          try {
            step(generator.next(value));
          } catch (e) {
            reject(e);
          }
        }

        function rejected(value) {
          try {
            step(generator["throw"](value));
          } catch (e) {
            reject(e);
          }
        }

        function step(result) {
          result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected);
        }

        step((generator = generator.apply(thisArg, _arguments || [])).next());
      });
    };
    function embedImages(clonedNode, options) {
      return __awaiter$2(this, void 0, void 0, /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                if (clonedNode instanceof Element) {
                  _context.next = 2;
                  break;
                }

                return _context.abrupt("return", Promise.resolve(clonedNode));

              case 2:
                return _context.abrupt("return", Promise.resolve(clonedNode).then(function (node) {
                  return embedBackground(node, options);
                }).then(function (node) {
                  return embedImageNode(node, options);
                }).then(function (node) {
                  return embedChildren(node, options);
                }));

              case 3:
              case "end":
                return _context.stop();
            }
          }
        }, _callee);
      }));
    }

    function embedBackground(clonedNode, options) {
      var _a;

      return __awaiter$2(this, void 0, void 0, /*#__PURE__*/regeneratorRuntime.mark(function _callee2() {
        var background;
        return regeneratorRuntime.wrap(function _callee2$(_context2) {
          while (1) {
            switch (_context2.prev = _context2.next) {
              case 0:
                background = (_a = clonedNode.style) === null || _a === void 0 ? void 0 : _a.getPropertyValue('background');

                if (background) {
                  _context2.next = 3;
                  break;
                }

                return _context2.abrupt("return", Promise.resolve(clonedNode));

              case 3:
                return _context2.abrupt("return", Promise.resolve(background).then(function (cssString) {
                  return embedResources(cssString, null, options);
                }).then(function (cssString) {
                  clonedNode.style.setProperty('background', cssString, clonedNode.style.getPropertyPriority('background'));
                  return clonedNode;
                }));

              case 4:
              case "end":
                return _context2.stop();
            }
          }
        }, _callee2);
      }));
    }

    function embedImageNode(clonedNode, options) {
      if (!(clonedNode instanceof HTMLImageElement) || isDataUrl(clonedNode.src)) {
        return Promise.resolve(clonedNode);
      }

      var src = clonedNode.src;
      return Promise.resolve(src).then(function (url) {
        return getBlobFromURL(url, options);
      }).then(function (data) {
        return toDataURL(data.blob, getMimeType(src) || data.contentType);
      }).then(function (dataURL) {
        return new Promise(function (resolve, reject) {
          clonedNode.onload = resolve;
          clonedNode.onerror = reject;
          clonedNode.srcset = '';
          clonedNode.src = dataURL;
        });
      }).then(function () {
        return clonedNode;
      }, function () {
        return clonedNode;
      });
    }

    function embedChildren(clonedNode, options) {
      return __awaiter$2(this, void 0, void 0, /*#__PURE__*/regeneratorRuntime.mark(function _callee3() {
        var children, deferreds;
        return regeneratorRuntime.wrap(function _callee3$(_context3) {
          while (1) {
            switch (_context3.prev = _context3.next) {
              case 0:
                children = toArray(clonedNode.childNodes);
                deferreds = children.map(function (child) {
                  return embedImages(child, options);
                });
                return _context3.abrupt("return", Promise.all(deferreds).then(function () {
                  return clonedNode;
                }));

              case 3:
              case "end":
                return _context3.stop();
            }
          }
        }, _callee3);
      }));
    }

    var __awaiter$3 = undefined && undefined.__awaiter || function (thisArg, _arguments, P, generator) {
      function adopt(value) {
        return value instanceof P ? value : new P(function (resolve) {
          resolve(value);
        });
      }

      return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) {
          try {
            step(generator.next(value));
          } catch (e) {
            reject(e);
          }
        }

        function rejected(value) {
          try {
            step(generator["throw"](value));
          } catch (e) {
            reject(e);
          }
        }

        function step(result) {
          result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected);
        }

        step((generator = generator.apply(thisArg, _arguments || [])).next());
      });
    };
    var cssFetchPromiseStore = {};
    function parseWebFontRules(clonedNode) {
      return __awaiter$3(this, void 0, void 0, /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                return _context.abrupt("return", new Promise(function (resolve, reject) {
                  if (!clonedNode.ownerDocument) {
                    reject(new Error('Provided element is not within a Document'));
                  }

                  resolve(toArray(clonedNode.ownerDocument.styleSheets));
                }).then(function (styleSheets) {
                  return getCssRules(styleSheets);
                }).then(getWebFontRules));

              case 1:
              case "end":
                return _context.stop();
            }
          }
        }, _callee);
      }));
    }
    function embedWebFonts(clonedNode, options) {
      return __awaiter$3(this, void 0, void 0, /*#__PURE__*/regeneratorRuntime.mark(function _callee2() {
        return regeneratorRuntime.wrap(function _callee2$(_context2) {
          while (1) {
            switch (_context2.prev = _context2.next) {
              case 0:
                return _context2.abrupt("return", (options.fontEmbedCss != null ? Promise.resolve(options.fontEmbedCss) : getWebFontCss(clonedNode, options)).then(function (cssString) {
                  var styleNode = document.createElement('style');
                  var sytleContent = document.createTextNode(cssString);
                  styleNode.appendChild(sytleContent);

                  if (clonedNode.firstChild) {
                    clonedNode.insertBefore(styleNode, clonedNode.firstChild);
                  } else {
                    clonedNode.appendChild(styleNode);
                  }

                  return clonedNode;
                }));

              case 1:
              case "end":
                return _context2.stop();
            }
          }
        }, _callee2);
      }));
    }
    function getWebFontCss(node, options) {
      return __awaiter$3(this, void 0, void 0, /*#__PURE__*/regeneratorRuntime.mark(function _callee3() {
        return regeneratorRuntime.wrap(function _callee3$(_context3) {
          while (1) {
            switch (_context3.prev = _context3.next) {
              case 0:
                return _context3.abrupt("return", parseWebFontRules(node).then(function (rules) {
                  return Promise.all(rules.map(function (rule) {
                    var baseUrl = rule.parentStyleSheet ? rule.parentStyleSheet.href : null;
                    return embedResources(rule.cssText, baseUrl, options);
                  }));
                }).then(function (cssStrings) {
                  return cssStrings.join('\n');
                }));

              case 1:
              case "end":
                return _context3.stop();
            }
          }
        }, _callee3);
      }));
    }
    function getCssRules(styleSheets) {
      return __awaiter$3(this, void 0, void 0, /*#__PURE__*/regeneratorRuntime.mark(function _callee4() {
        var ret, promises;
        return regeneratorRuntime.wrap(function _callee4$(_context4) {
          while (1) {
            switch (_context4.prev = _context4.next) {
              case 0:
                ret = [];
                promises = []; // First loop inlines imports

                styleSheets.forEach(function (sheet) {
                  if ('cssRules' in sheet) {
                    try {
                      toArray(sheet.cssRules).forEach(function (item, index) {
                        if (item.type === CSSRule.IMPORT_RULE) {
                          var importIndex = index + 1;
                          promises.push(fetchCSS(item.href, sheet).then(embedFonts).then(function (cssText) {
                            var parsed = parseCSS(cssText);
                            parsed.forEach(function (rule) {
                              try {
                                sheet.insertRule(rule, rule.startsWith('@import') ? importIndex = importIndex + 1 : sheet.cssRules.length);
                              } catch (error) {
                                console.log('Error inserting rule from remote css', {
                                  rule: rule,
                                  error: error
                                });
                              }
                            });
                          }).catch(function (e) {
                            console.log('Error loading remote css', e.toString());
                          }));
                        }
                      });
                    } catch (e) {
                      var inline = styleSheets.find(function (a) {
                        return a.href === null;
                      }) || document.styleSheets[0];

                      if (sheet.href != null) {
                        promises.push(fetchCSS(sheet.href, inline).then(embedFonts).then(function (cssText) {
                          var parsed = parseCSS(cssText);
                          parsed.forEach(function (rule) {
                            inline.insertRule(rule, sheet.cssRules.length);
                          });
                        }).catch(function (e) {
                          console.log('Error loading remote stylesheet', e.toString());
                        }));
                      }

                      console.log('Error inlining remote css file', e.toString());
                    }
                  }
                });
                return _context4.abrupt("return", Promise.all(promises).then(function () {
                  // Second loop parses rules
                  styleSheets.forEach(function (sheet) {
                    if ('cssRules' in sheet) {
                      try {
                        toArray(sheet.cssRules).forEach(function (item) {
                          ret.push(item);
                        });
                      } catch (e) {
                        console.log("Error while reading CSS rules from ".concat(sheet.href), e.toString());
                      }
                    }
                  });
                  return ret;
                }));

              case 4:
              case "end":
                return _context4.stop();
            }
          }
        }, _callee4);
      }));
    }

    function getWebFontRules(cssRules) {
      return cssRules.filter(function (rule) {
        return rule.type === CSSRule.FONT_FACE_RULE;
      }).filter(function (rule) {
        return shouldEmbed(rule.style.getPropertyValue('src'));
      });
    }

    function parseCSS(source) {
      if (source === undefined) {
        return [];
      }

      var cssText = source;
      var css = [];
      var cssKeyframeRegex = '((@.*?keyframes [\\s\\S]*?){([\\s\\S]*?}\\s*?)})';
      var combinedCSSRegex = '((\\s*?(?:\\/\\*[\\s\\S]*?\\*\\/)?\\s*?@media[\\s\\S]' + '*?){([\\s\\S]*?)}\\s*?})|(([\\s\\S]*?){([\\s\\S]*?)})'; // to match css & media queries together

      var cssCommentsRegex = /(\/\*[\s\S]*?\*\/)/gi;
      var importRegex = /@import[\s\S]*?url\([^)]*\)[\s\S]*?;/gi; // strip out comments

      cssText = cssText.replace(cssCommentsRegex, '');
      var keyframesRegex = new RegExp(cssKeyframeRegex, 'gi');
      var arr;

      while (true) {
        arr = keyframesRegex.exec(cssText);

        if (arr === null) {
          break;
        }

        css.push(arr[0]);
      }

      cssText = cssText.replace(keyframesRegex, ''); // unified regex

      var unified = new RegExp(combinedCSSRegex, 'gi');

      while (true) {
        arr = importRegex.exec(cssText);

        if (arr === null) {
          arr = unified.exec(cssText);

          if (arr === null) {
            break;
          } else {
            importRegex.lastIndex = unified.lastIndex;
          }
        } else {
          unified.lastIndex = importRegex.lastIndex;
        }

        css.push(arr[0]);
      }

      return css;
    }

    function fetchCSS(url, sheet) {
      if (cssFetchPromiseStore[url]) {
        return cssFetchPromiseStore[url];
      }

      var promise = fetch(url, {
        mode: 'no-cors'
      }).then(function (res) {
        return {
          url: url,
          cssText: res.text()
        };
      }, function (e) {
        console.log('ERROR FETCHING CSS: ', e.toString());
      });
      cssFetchPromiseStore[url] = promise;
      return promise;
    }

    function embedFonts(data) {
      return __awaiter$3(this, void 0, void 0, /*#__PURE__*/regeneratorRuntime.mark(function _callee5() {
        return regeneratorRuntime.wrap(function _callee5$(_context5) {
          while (1) {
            switch (_context5.prev = _context5.next) {
              case 0:
                return _context5.abrupt("return", data.cssText.then(function (resolved) {
                  var cssText = resolved;
                  var regexUrlFind = /url\(["']?([^"')]+)["']?\)/g;
                  var fontLocations = cssText.match(/url\([^)]+\)/g) || [];
                  var fontLoadedPromises = fontLocations.map(function (location) {
                    var url = location.replace(regexUrlFind, '$1');

                    if (!url.startsWith('https://')) {
                      var source = data.url;
                      url = new URL(url, source).href;
                    }

                    return new Promise(function (resolve, reject) {
                      fetch(url, {
                        mode: 'no-cors'
                      }).then(function (res) {
                        return res.blob();
                      }).then(function (blob) {
                        var reader = new FileReader();
                        reader.addEventListener('load', function (res) {
                          // Side Effect
                          cssText = cssText.replace(location, "url(".concat(reader.result, ")"));
                          resolve([location, reader.result]);
                        });
                        reader.readAsDataURL(blob);
                      }).catch(reject);
                    });
                  });
                  return Promise.all(fontLoadedPromises).then(function () {
                    return cssText;
                  });
                }));

              case 1:
              case "end":
                return _context5.stop();
            }
          }
        }, _callee5);
      }));
    }

    function createSvgDataURL(clonedNode, width, height) {
      var xmlns = 'http://www.w3.org/2000/svg';
      var svg = document.createElementNS(xmlns, 'svg');
      var foreignObject = document.createElementNS(xmlns, 'foreignObject');
      svg.setAttributeNS('', 'width', "".concat(width));
      svg.setAttributeNS('', 'height', "".concat(height));
      svg.setAttributeNS('', 'viewBox', "0 0 ".concat(width, " ").concat(height));
      foreignObject.setAttributeNS('', 'width', '100%');
      foreignObject.setAttributeNS('', 'height', '100%');
      foreignObject.setAttributeNS('', 'x', '0');
      foreignObject.setAttributeNS('', 'y', '0');
      foreignObject.setAttributeNS('', 'externalResourcesRequired', 'true');
      svg.appendChild(foreignObject);
      foreignObject.appendChild(clonedNode);
      return svgToDataURL(svg);
    }

    function applyStyleWithOptions(clonedNode, options) {
      var style = clonedNode.style;

      if (options.backgroundColor) {
        style.backgroundColor = options.backgroundColor;
      }

      if (options.width) {
        style.width = "".concat(options.width, "px");
      }

      if (options.height) {
        style.height = "".concat(options.height, "px");
      }

      var manual = options.style;

      if (manual != null) {
        Object.keys(manual).forEach(function (key) {
          // @ts-expect-error
          style[key] = manual[key];
        });
      }

      return clonedNode;
    }

    var __awaiter$4 = undefined && undefined.__awaiter || function (thisArg, _arguments, P, generator) {
      function adopt(value) {
        return value instanceof P ? value : new P(function (resolve) {
          resolve(value);
        });
      }

      return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) {
          try {
            step(generator.next(value));
          } catch (e) {
            reject(e);
          }
        }

        function rejected(value) {
          try {
            step(generator["throw"](value));
          } catch (e) {
            reject(e);
          }
        }

        function step(result) {
          result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected);
        }

        step((generator = generator.apply(thisArg, _arguments || [])).next());
      });
    };

    function getImageSize(domNode) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var width = options.width || getNodeWidth(domNode);
      var height = options.height || getNodeHeight(domNode);
      return {
        width: width,
        height: height
      };
    }

    function toSvg(domNode) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      return __awaiter$4(this, void 0, void 0, /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
        var _getImageSize, width, height;

        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                _getImageSize = getImageSize(domNode, options), width = _getImageSize.width, height = _getImageSize.height;
                return _context.abrupt("return", cloneNode(domNode, options, true).then(function (clonedNode) {
                  return embedWebFonts(clonedNode, options);
                }).then(function (clonedNode) {
                  return embedImages(clonedNode, options);
                }).then(function (clonedNode) {
                  return applyStyleWithOptions(clonedNode, options);
                }).then(function (clonedNode) {
                  return createSvgDataURL(clonedNode, width, height);
                }));

              case 2:
              case "end":
                return _context.stop();
            }
          }
        }, _callee);
      }));
    }
    function toCanvas(domNode) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      return __awaiter$4(this, void 0, void 0, /*#__PURE__*/regeneratorRuntime.mark(function _callee2() {
        return regeneratorRuntime.wrap(function _callee2$(_context2) {
          while (1) {
            switch (_context2.prev = _context2.next) {
              case 0:
                return _context2.abrupt("return", toSvg(domNode, options).then(createImage).then(delay(100)).then(function (image) {
                  var canvas = document.createElement('canvas');
                  var context = canvas.getContext('2d');
                  var ratio = options.pixelRatio || getPixelRatio();

                  var _getImageSize2 = getImageSize(domNode, options),
                      width = _getImageSize2.width,
                      height = _getImageSize2.height;

                  var canvasWidth = options.canvasWidth || width;
                  var canvasHeight = options.canvasHeight || height;
                  canvas.width = canvasWidth * ratio;
                  canvas.height = canvasHeight * ratio;
                  canvas.style.width = "".concat(canvasWidth);
                  canvas.style.height = "".concat(canvasHeight);

                  if (options.backgroundColor) {
                    context.fillStyle = options.backgroundColor;
                    context.fillRect(0, 0, canvas.width, canvas.height);
                  }

                  context.drawImage(image, 0, 0, canvas.width, canvas.height);
                  return canvas;
                }));

              case 1:
              case "end":
                return _context2.stop();
            }
          }
        }, _callee2);
      }));
    }
    function toJpeg(domNode) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      return __awaiter$4(this, void 0, void 0, /*#__PURE__*/regeneratorRuntime.mark(function _callee5() {
        return regeneratorRuntime.wrap(function _callee5$(_context5) {
          while (1) {
            switch (_context5.prev = _context5.next) {
              case 0:
                return _context5.abrupt("return", toCanvas(domNode, options).then(function (canvas) {
                  return canvas.toDataURL('image/jpeg', options.quality || 1);
                }));

              case 1:
              case "end":
                return _context5.stop();
            }
          }
        }, _callee5);
      }));
    }
    function toBlob$1(domNode) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      return __awaiter$4(this, void 0, void 0, /*#__PURE__*/regeneratorRuntime.mark(function _callee6() {
        return regeneratorRuntime.wrap(function _callee6$(_context6) {
          while (1) {
            switch (_context6.prev = _context6.next) {
              case 0:
                return _context6.abrupt("return", toCanvas(domNode, options).then(canvasToBlob));

              case 1:
              case "end":
                return _context6.stop();
            }
          }
        }, _callee6);
      }));
    }

    /**
     * @memberOf BX.Landing
     */

    var Screenshoter = /*#__PURE__*/function () {
      function Screenshoter() {
        babelHelpers.classCallCheck(this, Screenshoter);
      }

      babelHelpers.createClass(Screenshoter, null, [{
        key: "makeBlockScreenshot",
        value: function makeBlockScreenshot(blockId) {
          var editorWindow = landing_pageobject.PageObject.getEditorWindow();

          if (editorWindow !== window) {
            return editorWindow.BX.Landing.Screenshoter.makeBlockScreenshot(blockId);
          }

          var blockNode = document.querySelector("#block".concat(blockId));
          main_core.Dom.addClass(blockNode, 'landing-hide-ui-controls');
          var imagesMap = new Map();
          var animationHelper = main_core.Reflection.getClass('BX.Landing.OnscrollAnimationHelper');
          var animatedElements = animationHelper.getBlockAnimatedElements(blockNode);

          var animationCompleted = function () {
            if (main_core.Type.isArrayFilled(animatedElements)) {
              return Promise.all(animatedElements.map(function (element) {
                return animationHelper.animateElement(element);
              }));
            }

            return Promise.resolve();
          }();

          return animationCompleted.then(function () {
            return toJpeg(blockNode, {
              backgroundColor: '#ffffff',
              cacheBust: true
            }).then(function (encodedImage) {
              main_core.Dom.removeClass(blockNode, 'landing-hide-ui-controls');
              return fetch(encodedImage, {
                mode: 'no-cors'
              }).then(function (result) {
                return result.blob();
              }).then(function (blob) {
                imagesMap.forEach(function (imageValue, imageNode) {
                  imageNode.setValue(imageValue.sourceValue, true, true);
                });
                return new File([blob], "block-".concat(blockId, "-preview.jpg"), {
                  type: 'image/jpg'
                });
              });
            });
          });
        }
      }, {
        key: "makeElementScreenshot",
        value: function makeElementScreenshot(element) {
          var editorWindow = landing_pageobject.PageObject.getEditorWindow();

          if (editorWindow !== window) {
            return editorWindow.BX.Landing.Screenshoter.makeElementScreenshot(element);
          }

          return toBlob$1(element).then(function (blob) {
            return new File([blob], "screenshot-".concat(main_core.Text.getRandom(16), ".png"), {
              type: 'image/png'
            });
          });
        }
      }]);
      return Screenshoter;
    }();

    exports.Screenshoter = Screenshoter;

}((this.BX.Landing = this.BX.Landing || {}),BX.Landing,BX));
//# sourceMappingURL=screenshoter.bundle.js.map
