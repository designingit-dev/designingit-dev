'use strict'

module.exports = {
  get() {
    var url = '/checkout/orderstatus.json'

    return $.ajax({
      url: url,
      method: 'GET',
    })
  },
};
