<template>
<div>

  <div v-if="!products[0]" class="loader__container">
    <div class="loader__loading">
      <div class='loader__loading-dot'></div>
      <div class='loader__loading-dot'></div>
      <div class='loader__loading-dot'></div>
      <div class='loader__loading-dot'></div>
      <div class='loader__loading-dot'></div>
      <div class='loader__loading-dot'></div>
      <div class='loader__loading-text'></div>
    </div>
  </div>

  <div v-if="products[0]"
    class="productCategory__filterBar">
    <div>
      Narrow By:
    </div>

    <div class="productCategory__pill">
      <select v-model="selectedProductLine">
        <option selected value="">All Product Lines</option>
        <option v-for="tag in productLines.tags"
        v-bind:value="tag">
        {{ tag.name }}
        </option>
      </select>
    </div>

    <div v-for="tagGroup in filterGroups"
         class="productCategory__pill">
         {{ tagGroup.name }}
         <button v-on:click="togglePill(tagGroup)">
           {{ isPillSelected(tagGroup) ? 'v' : '&gt;' }}
         </button>

         <div class="productCategory__pill-dropdownContent"
              v-if="isPillSelected(tagGroup)">

           <div class="productCategory__pill-check"
                v-for="tag in tagGroup.tags">
             <input type="checkbox"
                    id="filterSelect-{{ tag.id }}"
                    v-bind:value="tag"
                    v-model="selectedTags">

             <label for="filterSelect-{{ tag.id }}">{{ tag.name }}</label>
           </div>

         </div>
    </div>
    <button v-if="isFiltered" v-on:click="resetFilters">
      CLEAR ALL
    </button>
    
  </div>

  <div class="productCategory__container">

    <div class="productCategory__productsListContainer">
      <div class="productCategory__productsList">

        <div class="productCategory__productCard"
          v-for="product in filteredProducts">
          <div class="productCategory__productLine">
            {{ productLineFor(product).name }}
          </div>
          <div>
          <img v-bind:src="product.imageUrl">
          </div>
          <div class="productCategory__productTitle">
            {{ product.title }}
          </div>
          <a href="/{{ product.uri }}">
            view product
          </a>
        </div>
      </div>
    </div>

  </div>
</div>
</template>

<script>
'use strict';

var ProductsService = require('../products-service.js');
var category
try {
  category = app.data.category
} catch(e) {
  category = 'strength'
}

module.exports = {

  created: function () {
    var self = this
    ProductsService.fetch().then(function (res) {
      self.allProducts = res.data;
    })
  },

  data: function () {
    return {
      allProducts: [],
      category: category,
      openPills: [],
      selectedProductLine: null,
      selectedTags: [],
    }
  },

  computed: {

    selectedTagIds: function () {
      return this.selectedTags.map(function (tag) { return tag.id + "" })
    },

    productLines: function () {
      return this.tagGroups.filter(function (group) {
        return group.handle === "productLines"
      })[0] || {}
    },

    filterGroups: function () {
      return this.tagGroups.filter(function (group) {
        return group.handle !== "productLines"
      })
    },

    isFiltered: function () {
      return this.selectedTagIds.length > 0 || this.selectedProductLine
    },

    products: function () {
      var self = this
      return self.allProducts.filter(function (product) {
        return product.productCategory == self.category;
      })
      .map(function (product) {
        product.tags = product.tags || [];
        return product
      })
    },

    filteredProducts: function () {
      var self = this;

      return self.products.filter(function (product) {
        var isValidProductLine = true
        var isValidTag = true

        // If a product line is selected, check to see the product
        // is a member of that product line.
        if (self.selectedProductLine) {
          isValidProductLine = self.productLineFor(product).id == self.selectedProductLine.id
        }

        // If no tags are selected, only display the product if
        // isValidProductLine is still true
        if (self.selectedTagIds.length === 0) {
          return isValidProductLine
        }

        // If 1 or more tags are selected, loop through the product's tags
        // Only display the product if the product contains ANY selected
        // AND if isValidProductLine is true
        return product.tags.filter(function (tag) {
          isValidTag = self.selectedTagIds.indexOf(tag.id) > -1
          return isValidProductLine && isValidTag
        })[0]
      })
    },
    // Computed function that transforms tags into tagGroups
    // The tag groups come back from the API nested under individual
    // tags because of either inexperience of limitations of Craft.
    // From the API
    // [ { id: 1, slug: "air350", groupId: 52, group: { id: 52, handle: "productLines", ...}}]
    // This function returns
    // { id: 52, handle: "productLine, tags: [{ id: 1, slug: "air350", ... }, ]}
    tagGroups: function () {
      var self = this;

      var uniqueTagGroups = self.products.reduce(function (memo, product) {

        // Add each tag's group to memo if it doesn't already exist
        // Push each tag object into the new tagGroup.tag array
        product.tags.forEach(function(tag) {

          // Initialize tag group if it doesn't exist
          if (!memo[tag.groupId]) {
            // Create a copy of the tag.group object to avoid circcular refrences
            // since each tag group will have a tags array. This might not be
            // important.
            memo[tag.groupId] = Object.keys(tag.group).reduce(function(memo, key) {
              memo[key] = tag.group[key]
              return memo
            }, { tags: [] })
          }

          // Here the tag group located at memo[tag.groupId] now exists,
          // lets give it a better name

          var tagGroup = memo[tag.groupId]

          // Check to see if the product's tag exists within our tag group's
          // tag array
          var isExistingTag = tagGroup.tags.some(function(existingTag) {
            return existingTag.id == tag.id
          })

          // If we've seen the tag before, no reason to contune
          if (isExistingTag) {
            return
          }

          // If we've not seen the tag before, push it onto the tagGroup's
          // tag array
          tagGroup.tags.push(tag)
        })

        //Our mutating `forEach` is over, now we return the memo

        return memo;
      }, {})

      // Turn our uniqueTagGroups object into an array and return it
      return Object.keys(uniqueTagGroups).map(function(key) {
        return uniqueTagGroups[key];
      });
    },
  },

  methods: {
    productLineFor: function (product) {
      var self = this
      return product.tags.filter(function (tag) {
        return self.productLines.id == tag.groupId
      })[0] || {}
    },

    resetFilters: function () {
      var self = this;
      self.selectedProductLine = "";
      self.openPills.splice(0, self.openPills.length)
      self.selectedTags.forEach(function(tag) {
        self.toggleTagFilter(tag, false)
      })
    },

    togglePill: function (tagGroup) {
      var self = this;
      var index = self.openPills.indexOf(tagGroup.id)
      if (index === -1) {
        self.openPills.push(tagGroup.id)
        return
      }
      self.openPills.splice(index, 1)
      return
    },

    isPillSelected: function (tagGroup) {
      return this.openPills.indexOf(tagGroup.id) > -1
    },

    toggleTagFilter: function (tag, override) {
      var self = this
      var index = self.selectedTags.reduce(function (memo, selectedTag, index) {
        if (selectedTag.id == tag.id) {
          return index
        }
        return memo
      }, -1)

      if (index === -1) {
        if (override === false) {
          return
        }

        // If the override isn't set to false, AND
        // the tag isn't already selected, only then
        // push it onto our selected tags array.
        self.selectedTags.push(tag)
        return
      }

      if (override === true) {
        return
      }

      // If the override isn't set to true, AND
      // the tag is already selected, only then
      // remove the tag from our selected tags array.
      self.selectedTags.splice(index, 1)
    },
  }
}
</script>
