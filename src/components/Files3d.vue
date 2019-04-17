<!--
 - @copyright Copyright (c) 2019 Vinzenz Rosenkranz <vinzenz.rosenkranz@posteo.de>
 -
 - @author Vinzenz Rosenkranz <vinzenz.rosenkranz@posteo.de>
 -
 - @license GNU AGPL version 3 or any later version
 -
 - This program is free software: you can redistribute it and/or modify
 - it under the terms of the GNU Affero General Public License as
 - published by the Free Software Foundation, either version 3 of the
 - License, or (at your option) any later version.
 -
 - This program is distributed in the hope that it will be useful,
 - but WITHOUT ANY WARRANTY; without even the implied warranty of
 - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 - GNU Affero General Public License for more details.
 -
 - You should have received a copy of the GNU Affero General Public License
 - along with this program. If not, see <http://www.gnu.org/licenses/>.
 -
 -->

<template>
    <renderer :size="{w: 600, h: 400}">
        <scene>
            <camera :position="{ z: 5 }"></camera>
            <mesh :obj="path"></mesh>
        </scene>
    </renderer>
</template>

<script>
// import axios from 'axios'
import Vue from 'vue'
import VueThreejs from 'vue-threejs'
Vue.use(VueThreejs)

export default {
	name: 'Files3d',
    props: ['mime', 'path'],
	data() {
		return {
		}
	},
	computed: {
	},
	watch: {
		active: function(val, old) {
			// the item was hidden before and is now the current view
			if (val === true && old === false) {

			}
		}
	},
	mounted() {
	},
	methods: {
		// Updates the dimensions of the modal
		updateImgSize() {
			this.naturalHeight = this.$el.naturalHeight
			this.naturalWidth = this.$el.naturalWidth
			this.updateHeightWidth()
			this.doneLoading()
		},
	}
}
</script>

<style scoped lang="scss">
</style>
