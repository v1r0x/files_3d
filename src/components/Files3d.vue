<!--
 - @copyright Copyright (c) 2019, 2020, 2021, 2022 Vinzenz Rosenkranz <vinzenz.rosenkranz@posteo.de>
 -
 - @author Vinzenz Rosenkranz <vinzenz.rosenkranz@posteo.de>
 -
 - @license AGPL-3.0-or-later
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
	<div :id="`threejs-${id}`" class="threejs-container" />
</template>

<script>
import {
	AmbientLight,
	AnimationMixer,
	Box3,
	Clock,
	Color,
	DirectionalLight,
	DoubleSide,
	GridHelper,
	HemisphereLight,
	Mesh,
	MeshPhongMaterial,
	// PCFSoftShadowMap,
	PerspectiveCamera,
	Scene,
	Vector3,
	WebGLRenderer,
} from 'three'
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js'
import { ColladaLoader } from 'three/examples/jsm/loaders/ColladaLoader.js'
import { FBXLoader } from 'three/examples/jsm/loaders/FBXLoader.js'
import { GCodeLoader } from 'three/examples/jsm/loaders/GCodeLoader.js'
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js'
import { MTLLoader } from 'three/examples/jsm/loaders/MTLLoader.js'
import { OBJLoader } from 'three/examples/jsm/loaders/OBJLoader.js'
import { PLYLoader } from 'three/examples/jsm/loaders/PLYLoader.js'
import { STLLoader } from 'three/examples/jsm/loaders/STLLoader.js'
import { VertexNormalsHelper } from 'three/examples/jsm/helpers/VertexNormalsHelper.js';
import { GUI } from 'lil-gui'

export default {
	name: 'Files3d',
	props: {
		etag: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			id: (new Date()).getTime(),
			container: null,
			renderer: null,
			controls: null,
			boundingBox: null,
			camera: null,
			scene: null,
			mesh: null,
			directionalLight: null,
			hemisphereLight: null,
			ambientLight: null,
			animationMixer: {},
			animationClock: new Clock(),
			gridHelper: new GridHelper(100, 10),
			normals: [],
			gui: null,
			guiParams: {
				edit: {
					backgroundColor: {r: 0, g: 0, b: 0},
					showGrid: true,
					showOutline: false,
				},
				normals: {
					show: false,
					color: {r: 0, g: 1, b: 0},
					length: 10,
				}
			},
		}
	},
	watch: {
		active(val, old) {
			// the item was hidden before and is now the current view
			if (val === true && old === false) {
				this.initContainer()
				this.showModel()
			}
		},
	},
	mounted() {
		this.initContainer()
		this.initGui();
		this.showModel()
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
		this.boundingBox = null
		this.container = null
		this.controls = null
		this.directionalLight = null
		this.hemisphereLight = null
		this.ambientLight = null
		this.animationMixer = {}
		this.animationClock = null
	},
	methods: {
		initGui() {
			const container = document.getElementById(`threejs-${this.id}`)
			if(!container || !container.classList.contains('viewer__file--active')) {
				return;
			}

			this.gui = new GUI({
				container: document.getElementById(`threejs-${this.id}`)
			})
			// Display GUI on right top of threejs container
			const guiDom = this.gui.domElement;
			guiDom.style.position = 'absolute'
			guiDom.style.top = '0px'
			guiDom.style.right = '0px'

			const editGroup = this.gui.addFolder('Edit Settings')
			editGroup.close()
			editGroup.addColor(this.guiParams.edit, 'backgroundColor').name('Background Color').onChange(value => this.setBackgroundColor(value))
			editGroup.add(this.guiParams.edit, 'showGrid').name('Show Grid').onChange(value => this.toggleGrid(value))
			const normalsGroup = this.gui.addFolder('Normals Settings')
			normalsGroup.close()
			normalsGroup.add(this.guiParams.normals, 'show').name('Show Normals').onChange(value => this.toggleNormals(value))
			normalsGroup.addColor(this.guiParams.normals, 'color').name('Normals Color').onChange(value => this.changeNormalProp('color', value))
			normalsGroup.add(this.guiParams.normals, 'length', 1, 250, 1).name('Normals Length').onChange(value => this.changeNormalProp('length', value))

			// Wait for rendering to update container's position (otherwise it's size is wrong)
			this.$nextTick(_ => {
				container.style.position = 'relative'
			})
		},
		setBackgroundColor(color) {
			this.scene.background = new Color(color.r, color.g, color.b)
		},
		toggleGrid(value) {
			this.gridHelper.visible = value;
		},
		addNormalsTo(list) {
			for(let i=0; i<list.length; i++) {
				const grpOrMesh = list[i];
				if(grpOrMesh.type == 'Group') {
					this.addNormalsTo(grpOrMesh.children);
				} else if(grpOrMesh.type == 'Mesh') {
					const color = new Color(this.guiParams.normals.color.r, this.guiParams.normals.color.g, this.guiParams.normals.color.b)
					const modelNormals = new VertexNormalsHelper(grpOrMesh, this.guiParams.normals.length, color)
					this.normals.push(modelNormals)
					this.scene.add(modelNormals)
				}
			}
		},
		toggleNormals(value) {
			if(value && this.normals.length == 0) {
				this.addNormalsTo(this.scene.children)
			} else {
				for(let i=0; i<this.normals.length; i++) {
					this.normals[i].visible = value
					// this.normals[i].update()
				}
			}
		},
		changeNormalProp(prop, value) {
			console.log(prop, value, this.normals);
			if(prop == 'color') {
				const color = new Color(value.r, value.g, value.b)
				for(let i=0; i<this.normals.length; i++) {
					this.normals[i].material.color = color
				}
			} else if(prop == 'length') {
				for(let i=0; i<this.normals.length; i++) {
					this.normals[i].size = value
					this.normals[i].update()
				}
			}
		},
		showModel() {
			if (!this.active) {
				return
			}
			switch (this.mime) {
			case 'model/vnd.collada+xml':
				this.showCollada(this.davPath)
				break
			case 'model/gltf-binary':
			case 'model/gltf+json':
				this.showGltf(this.davPath)
				break
			case 'model/obj-dummy':
				this.preloadMtl(this.davPath, this.basename)
				break
			case 'model/fbx-dummy':
				this.showFbx(this.davPath)
				break
			case 'application/sla':
				this.showStl(this.davPath)
				break
			case 'model/vnd.ply':
				this.showPly(this.davPath)
				break
			case 'text/x-gcode':
				this.showGcode(this.davPath)
				break
			}
		},
		showCollada(path) {
			const loader = new ColladaLoader()
			loader.load(path, collada => {
				const object = collada.scene
				if (object.rotation.x !== 0) {
					object.rotation.x = 0
				}
				this.addModelToScene(object)
			},
			event => { // onProgress
			},
			error => { // onError
				console.error(error)
			})
		},
		showGltf(path) {
			const loader = new GLTFLoader()
			loader.load(path, data => {
				const gltf = data
				const object = gltf.scene
				this.addModelToScene(object, gltf.animations)
			}, event => { // onProgress
			}, error => { // onError
				console.error(error)
			})
		},
		preloadMtl(path, filename) {
			// we assume that the mtl file has the same name as the obj file
			const filenameMtl = filename.replace('.obj', '.mtl')
			const parent = path.replace(filename, '')
			const mtlLoader = new MTLLoader()
			mtlLoader.setMaterialOptions({
				side: DoubleSide,
			})
			mtlLoader.setPath(parent)
			// try to load mtl file
			mtlLoader.load(filenameMtl, materials => {
				materials.preload()
				// load obj file with loaded materials
				this.showObj(parent, filename, materials)
			}, event => {
			}, event => {
				// onError: try to load obj without materials
				this.showObj(parent, filename)
			})
		},
		showObj(path, filename, materials) {
			const loader = new OBJLoader()
			if (materials) {
				loader.setMaterials(materials)
			}
			loader.load(path + filename,
				object => { // onSuccess
					this.addModelToScene(object)
				},
				event => { // onProgress
				},
				error => { // onError
					console.error(error)
				}
			)
		},
		showFbx(path) {
			const loader = new FBXLoader()
			loader.load(path, object => {
				this.addModelToScene(object)
			},
			event => { // onProgress
			},
			event => { // onError
			})
		},
		showStl(path) {
			const loader = new STLLoader()
			loader.load(path, geometry => {
				const material = new MeshPhongMaterial({
					color: 0xAA5555,
					specular: 0x111111,
					shininess: 200,
					vertexColors: !!geometry.hasColors,
					opacity: geometry.hasColors ? geometry.alpha : 1,
				})
				const mesh = new Mesh(geometry, material)
				mesh.castShadow = true
				mesh.receiveShadow = true
				this.addModelToScene(mesh)
			},
			event => { // onProgress
			},
			event => { // onError
			})
		},
		showPly(path) {
			const loader = new PLYLoader()
			loader.load(path, geometry => {
				geometry.computeVertexNormals()
				const material = new MeshPhongMaterial({
					color: 0xAA5555,
					specular: 0x111111,
					shininess: 200,
					vertexColors: !!geometry.hasColors,
					opacity: geometry.hasColors ? geometry.alpha : 1,
				})
				const mesh = new Mesh(geometry, material)
				mesh.castShadow = true
				mesh.receiveShadow = true
				this.addModelToScene(mesh)
			},
			event => { // onProgress
			},
			event => { // onError
			})
		},
		showGcode(path) {
			const loader = new GCodeLoader()
			loader.load(path, object => {
				// we don't define any materials here,
				// as the GCodeLoader defines the materials
				this.addModelToScene(object)
			},
			event => { // onProgress
			},
			event => { // onError
			})
		},
		addModelToScene(model, animations) {
			// Play first animation if available
			const anim = animations || model.animations
			if (anim && anim.length) {
				this.animationMixer = new AnimationMixer(model)
				this.animationMixer.clipAction(anim[0]).play()
			}
			for (let i = 0; i < model.children.length; i++) {
				const node = model.children[i]
				if (node.isMesh) {
					node.castShadow = true
					node.receiveShadow = true
					if (node.geometry) {
						node.geometry.computeBoundingBox()
					}
					if (node.material) {
						node.material.side = DoubleSide
					}
				}
			}

			this.boundingBox = new Box3().setFromObject(model)
			this.scene.add(model)
			this.doneLoading()
			this.updateViewerSize()
			this.pointCameraAtObject()
			this.animate()
		},
		pointCameraAtObject() {
			// We want to point the camera at the center of the object
			const center = new Vector3()
			this.boundingBox.getCenter(center)
			// We use `.controls.target` instead of `lookAt`,
			// because the OrbitControls will override value set by `lookAt`
			this.controls.target = center

			//                   x <-- new  camera position
			//
			//                              x <-- boundingBox.max * 2
			//
			//    #################x <-- boundingBox.max
			//    #                #
			//    #       x        #
			//    #                # <-- boundingBox of the object
			//    ##################
			this.camera.position = this.boundingBox.max.clone()
				.multiplyScalar(2)
				.applyAxisAngle(new Vector3(0, 1, 0), Math.PI / 4)

			this.camera.updateProjectionMatrix()
			this.controls.update()
			this.render()
		},
		animate() {
			requestAnimationFrame(this.animate)
			if (this.animationMixer.update) {
				this.animationMixer.update(this.animationClock.getDelta())
			}
			this.render()
		},
		render() {
			if (!this.renderer) {
				return
			}
			this.renderer.render(this.scene, this.camera)
		},
		initContainer() {
			this.disableSwipe()
			if (!this.container) {
				this.setViewerSize()
				this.container = document.getElementById(`threejs-${this.id}`)

				this.renderer = new WebGLRenderer({
					antialias: true,
				})
				this.renderer.setPixelRatio(window.devicePixelRatio)
				this.renderer.setSize(this.naturalWidth, this.naturalHeight)
				this.renderer.shadowMap.enabled = true

				this.camera = new PerspectiveCamera(45, this.naturalWidth / this.naturalHeight, 0.1, 2000)
				this.camera.position.set(5, 0, 0)
				this.camera.lookAt(new Vector3(0, 0, 0))
				this.camera.up.set(0, 1, 0)

				this.controls = new OrbitControls(this.camera, this.renderer.domElement)

				this.scene = new Scene()

				// Set (gui) editable values
				const c = this.guiParams.edit.backgroundColor;
				this.scene.background = new Color(c.r, c.g, c.b)
				this.gridHelper.visible = this.guiParams.edit.showGrid;

				this.ambientLight = new AmbientLight(0x404040)
				this.hemisphereLight = new HemisphereLight(0x808080, 0x606060)
				this.directionalLight = new DirectionalLight(0xffffff)
				this.directionalLight.position.set(1, 1, 1)
				this.directionalLight.castShadow = true
				this.directionalLight.shadow.camera.top = 2
				this.directionalLight.shadow.camera.bottom = -2
				this.directionalLight.shadow.camera.right = 2
				this.directionalLight.shadow.camera.left = -2
				this.directionalLight.shadow.camera.near = 1
				this.directionalLight.shadow.camera.far = 10
				this.directionalLight.shadow.bias = 0.002
				this.scene.add(this.ambientLight)
				this.scene.add(this.hemisphereLight)
				this.scene.add(this.directionalLight)
				this.scene.add(this.camera)
				this.scene.add(this.gridHelper)

				this.container.appendChild(this.renderer.domElement)
			}
		},
		setViewerSize() {
			this.naturalHeight = this.$el.offsetHeight
			this.naturalWidth = this.$el.offsetWidth
		},
		// Updates the dimensions of the modal
		updateViewerSize() {
			this.setViewerSize()
			this.updateHeightWidth()
			this.doneLoading()
		},
	},
}
</script>

<style scoped lang="scss">
	.threejs-container {
		width: 100vw;
		height: 90vh;
		align-self: center;
		justify-self: center;
	}
</style>
