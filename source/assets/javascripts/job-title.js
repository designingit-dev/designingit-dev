var job = {
  debug: false,
  state: {
    message: ''
  },
  setMessageAction (val) {
    this.state.message = val
  },
  clearMessageAction () {
    this.state.message = 'action B triggered'
  }
}

module.exports = {}