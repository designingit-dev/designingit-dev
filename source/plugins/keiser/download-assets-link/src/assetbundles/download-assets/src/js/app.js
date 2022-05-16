import './polyfills';
import '../scss/app.scss';

const axios = require('axios').default;

class DownloadAssetsPlugin {
  constructor() {
    // Set Variables

    // Functions
    this.onMatrixBlockAddButtonClick = this.matrixBlockAddButtonClick.bind(this);
    this.onAjaxComplete = this.ajaxComplete.bind(this);
    this.onElementSelectAddButtonClick = this.elementSelectAddButtonClick.bind(this);
    this.onElementSelected = this.elementSelected.bind(this);

    this.init();
  }

  init() {
    this.addEvents();
    this.render();
  }

  addEvents() {
    // Add event handlers
    Garnish.$doc
      .on('click', '.matrix .btn.add, .matrix .btn[data-type]', this.onMatrixBlockAddButtonClick)
      .on('click', '.field .input .elementselect .btn.add', this.onElementSelectAddButtonClick)
      .ajaxComplete(this.onAjaxComplete);
  }

  rebindAssetEvents() {
    Garnish.$doc.off('click', '.field .input .elementselect .btn.add', this.onElementSelectAddButtonClick);
    Garnish.$doc.on('click', '.field .input .elementselect .btn.add', this.onElementSelectAddButtonClick);
  }

  elementSelectAddButtonClick() {
    setTimeout(() => {
      if (Garnish.Modal.visibleModal) {
        const submit = Garnish.Modal.visibleModal.$container[0].querySelector('.modal.elementselectormodal .btn.submit');
        const elements = Garnish.Modal.visibleModal.$container[0].querySelector('.modal.elementselectormodal .elements');
        if (submit) {
          submit.removeEventListener('click', this.onElementSelected);
          submit.addEventListener('click', this.onElementSelected);
        }
        if (elements) {
          elements.removeEventListener('dblclick', this.onElementSelected);
          elements.addEventListener('dblclick', this.onElementSelected);
        }
      }
    }, 1000);
  }

  elementSelected() {
    setTimeout(() => {
      this.render();
    }, 1000);
  }

  matrixBlockAddButtonClick() {
    Garnish.requestAnimationFrame(() => {
      this.rebindAssetEvents();
      this.render();
    });
  }

  ajaxComplete() {
    this.render();
  }

  render() {
    const elementSelectFields = document.querySelectorAll('.field .input .elementselect .element:not(.linked)');
    const assetFields = [...elementSelectFields].filter(field => field.dataset.type === 'craft\\elements\\Asset');

    assetFields.forEach((field) => {
      const a = document.createElement('a');
      const label = field.querySelector('.label');
      if (field && label) {
        a.setAttribute('target', '_blank');
        a.classList.add('download');
        a.classList.add('icon');
        field.insertBefore(a, label);
        field.classList.add('linked');
        a.addEventListener('click', () => {
          window.downloadAssetFromLink(`${field.dataset.url}?mtime=${Date.now()}`); // cachebusting
        });
      }
    });
  }
}

window.downloadAssetFromLink = function (assetLink) {
  /* A random custom header is used here only to trigger the pre-flight request as otherwise the browser considers
     this request as a 'simple' request and doesn't send a pre-flight OPTIONS failing which the
      Access-Control-Allow-Origin header is not sent by S3 which results in a CORS erros in fetching the asset
     */
  axios({
    url: assetLink,
    method: 'GET',
    headers: {
      'X-CraftCMS-Fetch': 'asset'
    },
    responseType: 'blob',
  })
    .then((response) => {
      const url = window.URL
        .createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', assetLink.split('/').pop().split('#')[0].split('?')[0]);
      document.body.appendChild(link);
      link.click();
    });
};

window.addEventListener('DOMContentLoaded', () => {
  new DownloadAssetsPlugin();
});
