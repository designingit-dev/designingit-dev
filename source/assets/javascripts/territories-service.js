'use strict'

module.exports = {
  fetch() {

    var url = '/api/territories'
    return $.ajax({
      url: url,
      method: 'GET',
    })
  },
};
