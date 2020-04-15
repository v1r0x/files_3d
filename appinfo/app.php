<?php

/**
 * @copyright Copyright (c) 2018, 2019 Vinzenz Rosenkranz <vinzenz.rosenkranz@posteo.de>
 *
 * @author Vinzenz Rosenkranz <vinzenz.rosenkranz@posteo.de>
 *
 * @license GNU AGPL version 3 or any later version
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

 $eventDispatcher = \OC::$server->getEventDispatcher();
 $eventDispatcher->addListener(
 	'OCA\Files::loadAdditionalScripts',
 	function() {
 		\OCP\Util::addScript('files_3d', 'files3d');
 	}
 );

use OC\Files\Type\Detection;

$mimeTypeDetector = \OC::$server->getMimeTypeDetector();
if ($mimeTypeDetector instanceof Detection) {
    /** registerType without getAllMappings will prevent loading nextcloud's default mappings. */
    $mimeTypeDetector->getAllMappings();
    $mimeTypeDetector->registerType('dae', 'model/vnd.collada+xml', null);
    $mimeTypeDetector->registerType('fbx', 'model/fbx-dummy', null);
    $mimeTypeDetector->registerType('gltf', 'model/gltf-binary', 'model/gltf+json');
    $mimeTypeDetector->registerType('obj', 'model/obj-dummy', null);
}
