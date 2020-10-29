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
		$mimes = [
			'dae' => 'model/vnd.collada+xml',
			'fbx' => 'model/fbx-dummy',
			'gltf' => 'model/gltf-binary',
			'obj' => 'model/obj-dummy',
			'stl' => 'application/sla',
			'ply' => 'model/vnd.ply',
		];
		foreach($mimes as $ext => $mime) {
			$mimetypeId = $this->mimeTypeLoader->getId($mime);
			$this->mimeTypeLoader->updateFilecache($ext, $mimetypeId);
        	$output->info("Added custom $ext => $mime mimetype to filecache.");
		}
    }
}
