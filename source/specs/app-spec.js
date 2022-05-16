import App from '../assets/javascripts/App.js'

describe('App.js\'s methods', () => {
  let app = null
  beforeEach(() => {
    app = Object.create(App.data)
  })

  describe('#openModal',() => {
    let openModal = null
    beforeEach(() => {
      openModal = App.methods.openModal.bind(app)
    })

    it('Sets activeModal',() => {
      openModal('foo')
      expect(app.activeModal).toBe('foo')
    })

    it('Does not set the modal name twice',() => {
      openModal('foo')
      openModal('bar')
      expect(app.activeModal).not.toBe('bar')
    })
  })

  describe('#openMenu', () => {
    let openMenu = null
    beforeEach(() => {
      openMenu = App.methods.openMenu.bind(app)
    })

    it('Sets menuOpen to true', () => {
      openMenu(null, { preventDefault() {} })
      expect(app.menuOpen).toBe(true)
    })
  })

  describe('#close-modal', () => {
    let closeModal = null
    beforeEach(() => {
      closeModal = App.events['close-modal'].bind(app)
      app.activeModal = 'bar'
    })

    it('Unsets activeModal', () => {
      closeModal()
      expect(app.activeModal).toBe('')
    })
  })
})
