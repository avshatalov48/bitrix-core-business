this.BX = this.BX || {};
(function (exports) {
    'use strict';

    var Stepper = /*#__PURE__*/function () {
      function Stepper() {
        var props = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
        babelHelpers.classCallCheck(this, Stepper);
        this.ownerId = !!props.ownerId ? props.ownerId : 0;
        this.ownerTypeId = !!props.ownerTypeId ? props.ownerTypeId : 0;
      }

      babelHelpers.createClass(Stepper, [{
        key: "progress",
        value: function progress(list) {
          var _this = this;

          var total = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
          var start = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 0;

          if (!list || list.length <= 0) {
            throw new Error('list must be defined');
          }

          Stepper.isSuccess = true;
          this.batchFetch(list, total, start).then(function (batch) {
            _this.activityAdds(batch).then(function () {
              return _this.clientAdds(batch).then(function () {
                return _this.dealUpdateContacts(_this.ownerId, batch).then(function () {
                  return Stepper.getFulfillPromise();
                }, function () {
                  throw new Error('batchFetch dealUpdateContacts Error');
                });
              }, function () {
                throw new Error('batchFetch clientAdds Error');
              });
            }, function (activity) {
              return _this.continueProcess(activity);
            }) // reject to call progress again
            .then(function () {
              return _this.nextBatch(batch).then(function () {}, function () {
                return Stepper.labelFinish(batch);
              });
            }, function () {
              throw new Error('progress Error');
            });
          }, function () {
            throw new Error('batchFetch Error');
          });
        }
      }, {
        key: "continueProcess",
        value: function continueProcess(response) {
          var errors = !!response.errors ? response.errors : {};

          if (Object.values(errors).length > 0) {
            throw new Error('continueProcess Error');
          } else {
            return Stepper.getFulfillPromise();
          }
        }
      }, {
        key: "batchFetch",
        value: function batchFetch(list, total, start) {
          return BX.ajax.runAction('sale.integration.stepper.activityBatch', {
            data: {
              list: list,
              total: total,
              start: start
            }
          });
        }
      }, {
        key: "activityAdds",
        value: function activityAdds(response) {
          var data = !!response.data ? response.data : {};
          Stepper.progressBar(data);
          Stepper.messageBar(data);

          if (!!data.process) {
            /*
            * Object.values(data.process.items).length = n && Object.values(data.process.list).length = 0 //start - one step
            * Object.values(data.process.items).length = 0 && Object.values(data.process.list).length = n // skip step
            *
            * */
            //
            if (Object.values(data.process.items).length > 0) {
              return BX.ajax.runAction('sale.integration.scenarios.activityAddsFromOrderList', {
                data: {
                  params: data.process.items
                }
              });
            } else if (Object.values(data.process.list).length > 0) {
              return Stepper.getRejectPromise();
            } else if (!!data.error && data.error.length > 0) {
              // продолжаем выполнение, т.к. текст ошибки на данном шаге выведен. пропускаем шаг
              return Stepper.getRejectPromise();
            }
          }

          throw new Error('activityAdds Error');
        }
      }, {
        key: "prepareContactFields",
        value: function prepareContactFields(response) {
          if (!!response.status && response.status === 'success') {
            var data = !!response.data ? response.data : {};

            if (!!data.process && Object.values(data.process.items).length > 0) {
              return BX.ajax.runAction('sale.integration.scenarios.resolveContactFieldsValuesFromOrderList', {
                data: {
                  params: data.process.items
                }
              });
            }
          }

          throw new Error('clientAdds Error');
        }
      }, {
        key: "clientAdds",
        value: function clientAdds(batch) {
          var _this2 = this;

          return this.prepareContactFields(batch) //->из исходных данных получили список по локальным пользователям
          .then(function (list) {
            return _this2.contactRelationVoid(list) //->возвращает локальный список пользователей у которых связь с удаленной сущностью отсутвует/не корректна
            .then(function (addList) {
              return _this2.contactAdds(addList) //->добавили контакты в удаленную сиситему, обновили связи на локальной. с этого момента для пользователей из batch локально храниться актальная таблица связок к удаленным сущностям
              .then(function () {
                return Stepper.getFulfillPromise();
              }, function () {
                throw new Error('clientAdds contactAdds Error');
              });
            }, function () {
              throw new Error('clientAdds contactRelationVoid Error');
            });
          }, function () {
            throw new Error('clientAdds prepareContactFields Error');
          });
        }
      }, {
        key: "contactRelationVoid",
        value: function contactRelationVoid(list) {
          if (!!list.status && list.status === 'success') {
            var data = !!list.data ? list.data : {}; //если данных нет пропускаем вызов и возвращаем fulfill promise

            if (!!data.result && Object.values(data.result).length > 0) {
              return BX.ajax.runAction('sale.integration.scenarios.resolveUserTypeIAfterComparingRemotelyRelationFromOrderList', {
                data: {
                  params: data.result
                }
              });
            } else {
              return Stepper.getFulfillPromise(list);
            }
          }

          throw new Error('contactRelationVoid Error');
        }
      }, {
        key: "contactAdds",
        value: function contactAdds(addList) {
          if (!!addList.status && addList.status === 'success') {
            var data = !!addList.data ? addList.data : {}; //если данных нет пропускаем вызов и возвращаем fulfill promise

            if (!!data.result && Object.values(data.result).length > 0) {
              return BX.ajax.runAction('sale.integration.scenarios.contactAddsFromOrderList', {
                data: {
                  params: data.result
                }
              });
            } else {
              return Stepper.getFulfillPromise();
            }
          }

          throw new Error('contactAdds Error');
        }
      }, {
        key: "dealUpdateContacts",
        value: function dealUpdateContacts(dealId, batch) {
          var _this3 = this;

          return this.prepareContactFields(batch) //->из исходных данных получили список по локальным пользователям
          .then(function (list) {
            return _this3.dealContactItemsUpdate(dealId, list);
          }, //->обновляем пользоватлей в сделке (обогощаем сделку контактами)
          function () {
            throw new Error('dealUpdateContacts prepareContactFields Error');
          });
        }
      }, {
        key: "dealContactItemsUpdate",
        value: function dealContactItemsUpdate(dealId, list) {
          var _this4 = this;

          if (!!list.status && list.status === 'success') {
            var data = !!list.data ? list.data : {}; //если данных нет пропускаем вызов и возвращаем fulfill promise
            // (например когда в заказе указана компания, а запрашиваются данные клинта-Контакта)

            if (!!data.result && Object.values(data.result).length > 0) {
              return this.dealContactItemsGet(dealId).then(function (items) {
                return _this4.dealContactAdds(dealId, {
                  list: list,
                  items: items
                });
              }, function () {
                throw new Error('dealUpdateContacts dealContactAdds Error');
              });
            } else {
              return Stepper.getFulfillPromise();
            }
          }

          throw new Error('dealUpdateContacts dealContactItemsUpdate Error');
        }
      }, {
        key: "dealContactAdds",
        value: function dealContactAdds(dealId, params) {
          // метод должен вызываться когда гарантровано есть список пльзоватлей из БУС для обогощения сделки
          // если у сделки есть контакты, то обогощаем их пользователями
          // если у сделки нет контактов добавляем всех пользователей
          var users = !!params.list ? params.list : {};
          var contacts = !!params.items ? params.items : {};

          if (!!users.status && users.status === 'success' && !!contacts.status && contacts.status === 'success') {
            var dataUsers = !!users.data ? users.data : {};
            var dataContacts = !!contacts.data ? contacts.data : {};

            if (!!dataUsers.result && !!dataContacts.result) {
              if (Object.values(dataContacts.result).length > 0) {
                return BX.ajax.runAction('sale.integration.scenarios.dealContactUpdates', {
                  data: {
                    id: dealId,
                    items: dataUsers.result,
                    contacts: dataContacts.result
                  }
                });
              } else {
                return BX.ajax.runAction('sale.integration.scenarios.dealContactAdds', {
                  data: {
                    id: dealId,
                    items: dataUsers.result
                  }
                });
              }
            }
          }

          throw new Error('dealContactAdds Error');
        }
      }, {
        key: "dealContactItemsGet",
        value: function dealContactItemsGet(dealId) {
          return BX.ajax.runAction('sale.integration.scenarios.dealContactItemsGet', {
            data: {
              id: dealId
            }
          });
        }
      }, {
        key: "dealUpdate",
        value: function dealUpdate(response) {
          if (!!response.status && response.status === 'success') {
            var data = !!response.data ? response.data : {};

            if (!!data.process && data.process.items.length > 0) {
              return BX.ajax.runAction('sale.integration.scenarios.dealupdatecontacts', {
                data: {
                  id: this.ownerId,
                  params: data.process.items
                }
              });
            }
          }

          throw new Error('dealUpdate Error');
        }
      }, {
        key: "nextBatch",
        value: function nextBatch(response) {
          if (!!response.status && response.status === 'success') {
            var data = !!response.data ? response.data : {};

            if (!!data.process && !!data.process.list && !!data.process.items && !!data.process.total && !!data.process.start) {
              if (Object.values(data.process.items).length > 0 && Object.values(data.process.list).length > 0) {
                this.progress(data.process.list, data.process.total, data.process.start);
                return Stepper.getFulfillPromise();
              }

              if (Object.values(data.process.items).length === 0 && Object.values(data.process.list).length > 0) {
                this.progress(data.process.list, data.process.total, data.process.start);
                return Stepper.getFulfillPromise();
              } else if (Object.values(data.process.list).length <= 0) {
                // finish process batch
                return Stepper.getRejectPromise();
              }
            }
          }

          throw new Error('nextBatch Error');
        }
      }], [{
        key: "progressBar",
        value: function progressBar(data) {
          if (!!data.progress) {
            BX.ajax.runAction('sale.integration.stepper.progressbar', {
              data: {
                value: data.progress
              }
            }).then(function (response) {
              return Stepper.render('progress', response.data);
            }, function () {
              throw new Error('ProgressBar failure!');
            });
          }
        }
      }, {
        key: "labelFinish",
        value: function labelFinish(response) {
          var data = !!response.data ? response.data : {};

          if (!!data.finish) {
            //BX.closeWait();
            BX.ajax.runAction('sale.integration.stepper.messageOK', {}).then(function (response) {
              Stepper.render('finish', response.data);

              if (Stepper.isSuccess) {
                Stepper.closeApplication();
              }
            }, function () {
              throw new Error('MessagebyType OK failure!');
            });
          }
        }
      }, {
        key: "messageBar",
        value: function messageBar(data) {
          if (!!data.error) {
            Stepper.isSuccess = false;
            BX.ajax.runAction('sale.integration.stepper.messagebytype', {
              data: {
                message: data.error,
                type: 'ERROR'
              }
            }).then(function (response) {
              var div = BX.create('DIV');
              div.innerHTML = response.data;
              BX('progress_error').appendChild(div);
            }, function () {
              throw new Error('MessagebyType ERROR failure!');
            });
          }
        }
      }, {
        key: "render",
        value: function render(element, result) {
          BX.adjust(BX(element), {
            html: result
          });
        }
      }, {
        key: "getFulfillPromise",
        value: function getFulfillPromise() {
          var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
          var promise = new BX.Promise();
          promise.fulfill(params);
          return promise;
        }
      }, {
        key: "getFulfillPromise_setTimeout",
        value: function getFulfillPromise_setTimeout() {
          var _this5 = this;

          var promise = new BX.Promise();
          setTimeout(function () {
            promise.fulfill(_this5);
          }, 2000);
          return promise;
        }
      }, {
        key: "getRejectPromise",
        value: function getRejectPromise() {
          var promise = new BX.Promise();
          promise.reject(this);
          return promise;
        }
      }, {
        key: "closeApplication",
        value: function closeApplication() {
          setTimeout(function () {
            BX24.closeApplication();
          }, 500);
        }
      }]);
      return Stepper;
    }();

    exports.Stepper = Stepper;

}((this.BX.Sale = this.BX.Sale || {})));
//# sourceMappingURL=b24integration.bundle.js.map
