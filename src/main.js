/**
 * @copyright Copyright (c) 2019 Vinzenz Rosenkranz <vinzenz.rosenkranz@posteo.de>
 *
 * @author Vinzenz Rosenkranz <vinzenz.rosenkranz@posteo.de>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

import Files3d from './components/Files3d.vue'

document.addEventListener('DOMContentLoaded', function() {
	if (OCA.Viewer) {
		OCA.Viewer.registerHandler({
			// unique id
			id: 'files_3d',

			// optional, it will group every view of this group and
			// use the proper view when building the file list
			// of the slideshow.
			// e.g. you open an image/jpeg that have the `media` group
			// you will be able to see the video/mpeg from the `video` handler
			// files that also have the `media` group set.
			group: '3d',

			// the list of mimes your component is able to display
			mimes: [
				'model/vnd.collada+xml',
				'model/gltf-binary',
				'model/gltf+json',
				// OBJ has no mime
				'model/obj-dummy',
				// FBX has no mime
				'model/fbx-dummy',
			],

			// your vue component view
			component: Files3d,
		})
	}
})
