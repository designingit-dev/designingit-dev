'use strict'

var apiKey = 'RiIAG0PJ5kSvgjAqYyZL2Kc3PMDmbOO2O1GLmcE1';

module.exports = {
  get(addressObj) {
    if (!addressObj) return console.error('addressObj missing for address-service#get')

    var addressStr = $.param(addressObj);

    var url = 'https://8143g4pk99.execute-api.us-west-2.amazonaws.com/prod/address?' + addressStr

    return $.ajax({
      url: url,
      method: 'GET',
      headers: { "x-api-key": apiKey }
    })
  },
};
