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

use OCA\Viewer\Event\LoadViewer;
use OCA\Files3d\Listener\LoadFiles3dScript;
use OCP\AppFramework\App;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\Files\IMimeTypeDetector;

class Application extends App {

    const APP_ID = 'files_3d';
    private $mimeTypeDetector;

    public function __construct(IMimeTypeDetector $mimeTypeDetector) {
        parent::__construct(self::APP_ID);

        $this->mimeTypeDetector = $mimeTypeDetector;

        $server = $this->getContainer()->getServer();
        $eventDispatcher = $server->query(IEventDispatcher::class);
        $eventDispatcher->addServiceListener(LoadViewer::class, LoadFiles3dScript::class);
    }

    public function register() {
        /** registerType without getAllMappings will prevent loading nextcloud's default mappings. */
        $this->mimeTypeDetector->getAllMappings();
        $this->mimeTypeDetector->registerType('dae', 'model/vnd.collada+xml', null);
        $this->mimeTypeDetector->registerType('fbx', 'model/fbx-dummy', null);
        $this->mimeTypeDetector->registerType('gltf', 'model/gltf-binary', 'model/gltf+json');
        $this->mimeTypeDetector->registerType('obj', 'model/obj-dummy', null);
    }
}