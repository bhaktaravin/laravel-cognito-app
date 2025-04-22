window._ = require('lodash');

try {
    require('bootstrap');

} catch(e) {}



window.axios = require('axios');

window.axios.default.headers.common['X-Requested-With'] = 'XMLHttpRequest';
