import BX from '../../../../../../main/install/js/main/core/test/old/core/internal/bootstrap';
import '../../../../../../currency/install/js/currency/core_currency';

const Currency = window.BX.Currency;


global.BX = BX;
global.window.BX = BX;
window.BX = BX;

global.BX.Currency = Currency;