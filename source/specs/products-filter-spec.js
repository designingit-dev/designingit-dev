'use strict'

import Vue from 'vue';
import productsFixture from './fixtures/products-fixture.js';

const proxyquire = require('proxyquireify')(require);
const productsServiceDouble = {
  fetch: function () {
    return {
      then: function (callback) {
        return callback(productsFixture());
      }
    }
  }
}

describe('products-filter component', () => {
  let vm = null;

  beforeEach(() => {
    let ProductsFilter = proxyquire( '../assets/javascripts/products-filter/ProductsFilter.vue', {
      '../products-service.js': productsServiceDouble
    })

    let vueInstance = new Vue({
      template: '<div><products-filter></products-filter><div>',
      components: {
        productsFilter: ProductsFilter
      }
    }).$mount()

    vueInstance.$children.forEach((child) => {
      if (child.allProducts) {
        vm = child
      }
    });

    it('exists', () => {
      expect(vm).not.toBeNull();
    })

    it('does not filter products when no filters are selected', () => {
      expect(vm.filteredProducts.length).toBe(3);
    });
  })

  describe('product line select', () => {
    beforeEach(() => {
      vm.selectedProductLine = vm.productLines.tags.filter((tag) => {
        return tag.slug === 'air250';
      })[0];
    });

    it('filters products when selected', () => {
      expect(vm.filteredProducts.length).toBe(1);
    })

    it('clears filters when resetFilters is called',() => {
      vm.resetFilters();
      expect(vm.filteredProducts.length).toBe(3);
    })
  })


  describe('filter groups', () => {
    beforeEach(() => {
      let muscleGroupTag = vm.filterGroups[0].tags.filter((tag) => {
        return tag.slug == "chest";
      })[0];
      vm.toggleTagFilter(muscleGroupTag);
    });

    it('filters products when selected', () => {
      expect(vm.filteredProducts.length).toBe(2);
    })

    it('becomes more permissive as tags are added', () => {
      let muscleGroupTag = vm.filterGroups[0].tags.filter((tag) => {
        return tag.slug == "abductors";
      })[0];
      vm.toggleTagFilter(muscleGroupTag);
      expect(vm.filteredProducts.length).toBe(3);
    });

    it('clears filters when resetFilters is called',() => {
      vm.resetFilters();
      expect(vm.filteredProducts.length).toBe(3);
    })
  });

  describe('product line select with filter groups', () => {
    beforeEach(() => {
      vm.selectedProductLine = vm.productLines.tags.filter((tag) => {
        return tag.slug === 'air350';
      })[0];

      let muscleGroupTag = vm.filterGroups[0].tags.filter((tag) => {
        return tag.slug == "chest";
      })[0];

      vm.toggleTagFilter(muscleGroupTag);
    });

    it('Products MUST belong the selected product line, even if they have the right filter tags', () => {
      expect(vm.filteredProducts.length).toBe(1);
    });

    it('becomes more permissive as tags are added', () => {
      let muscleGroupTag = vm.filterGroups[0].tags.filter((tag) => {
        return tag.slug == "abductors";
      })[0];
      vm.toggleTagFilter(muscleGroupTag);
      expect(vm.filteredProducts.length).toBe(2);
    });


    it('clears filters when resetFilters is called',() => {
      vm.resetFilters();
      expect(vm.filteredProducts.length).toBe(3);
    })

  });

})
