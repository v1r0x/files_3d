<?php

/**
 * @copyright Copyright (c) 2018, 2019 Vinzenz Rosenkranz <vinzenz.rosenkranz@gmail.com>
 *
 * @author Vinzenz Rosenkranz <vinzenz.rosenkranz@gmail.com>
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

OCP\Util::addStyle('files_3d', 'style');
OCP\Util::addScript('files_3d', 'vendor/three.min');
OCP\Util::addScript('files_3d', 'vendor/controls/OrbitControls');
OCP\Util::addScript('files_3d', 'vendor/libs/inflate.min');
OCP\Util::addScript('files_3d', 'vendor/loaders/LoaderSupport');
OCP\Util::addScript('files_3d', 'vendor/loaders/ColladaLoader');
OCP\Util::addScript('files_3d', 'vendor/loaders/FBXLoader');
OCP\Util::addScript('files_3d', 'vendor/loaders/GLTFLoader');
OCP\Util::addScript('files_3d', 'vendor/loaders/OBJLoader2');
OCP\Util::addScript('files_3d', 'vendor/loaders/MTLLoader');
OCP\Util::addScript('files_3d', 'vendor/loaders/STLLoader');
OCP\Util::addScript('files_3d', 'loader');
