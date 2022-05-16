'use strict';

module.exports = {
  fetch() {
    var uri = '/api/products';
    return $.ajax({
      url: '/api/products',
      method: 'GET',
    })
  },
};
