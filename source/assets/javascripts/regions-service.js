'use strict'

module.exports = {
  fetch() {

    var url = '/api/regions'
    return $.ajax({
      url: url,
      method: 'GET',
    })
  },
};
