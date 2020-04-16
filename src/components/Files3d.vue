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
	<div id="threejs-container" />
</template>

<script>
// import Vue from 'vue'

import {
	PCFSoftShadowMap,
	PerspectiveCamera,
	Scene,
	Vector3,
	WebGLRenderer,
} from 'three'

export default {
	name: 'Files3d',
	props: {
		active: {
			type: Boolean,
			default: false,
		},
		basename: {
			type: String,
			required: true,
		},
		filename: {
			type: String,
			required: true,
		},
		mime: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			container: null,
			renderer: null,
			camera: null,
			scene: null,
			mesh: null,
		}
	},
	computed: {
	},
	watch: {
		active: function(val, old) {
			// the item was hidden before and is now the current view
			if (val === true && old === false) {
				// console.log('now active')
				console.error('now active')
			}
		},
	},
	mounted() {
		if (!this.container) {
			this.container = document.getElementById('threejs-container')

			this.renderer = new WebGLRenderer({
				antialias: true,
			})
			this.renderer.setSize(this.$el.naturalWidth, this.$el.naturalHeight)
			this.renderer.shadowMap.enabled = true
			this.renderer.shadowMap.type = PCFSoftShadowMap

			this.camera = new PerspectiveCamera(45, this.$el.naturalWidth / this.$el.naturalHeight, 0.1, 2000)
			this.camera.position.set(5, 0, 0)
			this.camera.lookAt(new Vector3(0, 0, 0))
			this.camera.up.set(0, 1, 0)

			this.scene = new Scene()

			this.container.appendChild(this.renderer.domElement)
			this.scene.add(this.camera)
			// console.log(this.mime, this.path)
			console.error(this.mime, this.filename, this.basename, this.active)
		}
	},
	destroyed() {
		for (let i = this.scene.children.length - 1; i >= 0; i--) {
			const obj = this.scene.children[i]
			if (obj.geometry) obj.geometry.dispose()
			if (obj.material) obj.material.dispose()
			this.scene.remove(obj)
		}
		this.renderer.forceContextLoss()
		this.renderer.dispose()
		this.renderer = null
		this.scene = null
		this.container = null
	},
	methods: {
		// Updates the dimensions of the modal
		updateImgSize() {
			this.naturalHeight = this.$el.naturalHeight
			this.naturalWidth = this.$el.naturalWidth
			this.updateHeightWidth()
			this.doneLoading()
		},
	},
}
</script>

<style scoped lang="scss">
</style>
