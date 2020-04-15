<?php

namespace OCA\Files3d\Migration;

use OCP\Files\IMimeTypeLoader;
use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;

class AddMimetypeToFilecache implements IRepairStep {

    private $mimeTypeLoader;

    public function __construct(IMimeTypeLoader $mimeTypeLoader) {
        $this->mimeTypeLoader = $mimeTypeLoader;
    }

    public function getName() {
        return 'Add custom mimetype to filecache';
    }

    public function run(IOutput $output) {
        // And update the filecache for it.
        $mimetypeId = $this->mimeTypeLoader->getId('model/vnd.collada+xml');
        $this->mimeTypeLoader->updateFilecache('dae', $mimetypeId);
        $mimetypeId = $this->mimeTypeLoader->getId('model/fbx-dummy');
        $this->mimeTypeLoader->updateFilecache('fbx', $mimetypeId);
        $mimetypeId = $this->mimeTypeLoader->getId('model/gltf-binary');
        $this->mimeTypeLoader->updateFilecache('gltf', $mimetypeId);
        $mimetypeId = $this->mimeTypeLoader->getId('model/obj-dummy');
        $this->mimeTypeLoader->updateFilecache('obj', $mimetypeId);
        $output->info('Added custom mimetype to filecache.');
    }
}
