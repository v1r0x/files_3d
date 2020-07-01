<?php

/**
 * @copyright Copyright (c) 2020 Vinzenz Rosenkranz <vinzenz.rosenkranz@posteo.de>
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

namespace OCA\Files3d\AppInfo;

use OCA\Files3d\Listener\LoadFiles3dScript;
use OCA\Viewer\Event\LoadViewer;

use OCP\AppFramework\App;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\Files\IMimeTypeDetector;

class Application extends App {

	const APP_ID = 'files_3d';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register() {
		$server = $this->getContainer()->getServer();

		/** @var IMimeTypeDetector $mimeTypeDetector */
		$mimeTypeDetector = $server->query(IMimeTypeDetector::class);

		/** @var IEventDispatcher $eventDispatcher */
		$eventDispatcher = $server->query(IEventDispatcher::class);

		// registerType without getAllMappings will prevent loading nextcloud's default mappings.
		$mimeTypeDetector->getAllMappings();
		$mimeTypeDetector->registerType('dae', 'model/vnd.collada+xml', null);
		$mimeTypeDetector->registerType('fbx', 'model/fbx-dummy', null);
		$mimeTypeDetector->registerType('gltf', 'model/gltf-binary', 'model/gltf+json');
		$mimeTypeDetector->registerType('obj', 'model/obj-dummy', null);
		$mimeTypeDetector->registerType('stl', 'application/sla', null);

		// Watch Viewer load event
		$eventDispatcher->addServiceListener(LoadViewer::class, LoadFiles3dScript::class);
	}
}
