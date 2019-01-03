var loaderCtx = {
    three: {
        initThreeJs: function() {
            var deferred = $.Deferred();
            if(this.inited) {
                this.startup();
                deferred.resolve();
            } else {
                this.inited = true;
                var threeScript = OC.filePath('files_3d', 'js/vendor', 'three.min.js');
                var script = document.createElement('script');
                script.src = threeScript;
                script.setAttribute('nonce', btoa(OC.requestToken));
                script.onload = function() {
                    var orbitCtrl = OC.filePath('files_3d', 'js/vendor/controls', 'OrbitControls.js');
                    var ocScr = document.createElement('script');
                    ocScr.src = orbitCtrl;
                    ocScr.setAttribute('nonce', btoa(OC.requestToken));
                    ocScr.onload = function() {
                        var vm = loaderCtx.three;
                        vm.renderedTmpl = $(vm.template);
                        vm.renderedTmpl.appendTo('body');
                        vm.container = document.getElementById('files-3d-container');
                        vm.startup();
                        deferred.resolve();
                    };
                    document.head.appendChild(ocScr);
                };
                document.head.appendChild(script);
            }

            return deferred;
        },
        startup: function() {
            this.renderedTmpl.fadeIn('fast');
            this.container.style.display = 'block';

            window.addEventListener('resize', this.onResize, false);

            this.sizes.x = this.container.clientWidth;
            this.sizes.y = this.container.clientHeight;

            this.camera = new THREE.PerspectiveCamera(45, this.sizes.x/this.sizes.y, 0.1, 2000);
            this.camera.position.set(0, 0, -10);
            this.camera.lookAt(0, 0, 0);
            this.scene = new THREE.Scene();
            this.lights.al = new THREE.AmbientLight(0xcccccc, 0.4);
            this.scene.add(this.lights.al);

            this.lights.hl = new THREE.HemisphereLight(0x808080, 0x606060);
            this.lights.dl = new THREE.DirectionalLight(0xffffff);
            this.lights.dl.position.set(this.camera.position);
            this.lights.dl.castShadow = true;
            this.lights.dl.shadow.camera.top = 2;
            this.lights.dl.shadow.camera.bottom = -2;
            this.lights.dl.shadow.camera.right = 2;
            this.lights.dl.shadow.camera.left = -2;
            this.lights.dl.shadow.mapSize.set(4096, 4096);
            this.scene.add(this.lights.hl);
            this.scene.add(this.lights.dl);

            this.scene.add(new THREE.GridHelper(10, 20));
            var controls = new THREE.OrbitControls(this.camera);

            this.renderer = new THREE.WebGLRenderer({
                antialias: true
            });
            this.renderer.setPixelRatio(window.devicePixelRatio);
            this.renderer.setSize(this.sizes.x, this.sizes.y);
            this.renderer.shadowMap.enabled = true;
            this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            this.renderer.gammaInput = true;
            this.renderer.gammaOutput = true;
            this.container.appendChild(this.renderer.domElement);
        },
        shutdown: function() {
            this.renderedTmpl.fadeOut('fast');
            this.container.style.display = 'none';

            window.removeEventListener('resize', this.onResize, false);

            this.sizes.x = 0;
            this.sizes.y = 0;

            this.camera = null;
            this.scene = null;
            this.lights.al = null;

            this.lights.hl = null;
            this.lights.dl = null;

            this.container.removeChild(this.renderer.domElement);
            this.renderer = null;
        },
        inited: false,
        sizes: {
            x: 0,
            y: 0
        },
        renderedTmpl: null,
        container: null,
        camera: null,
        scene: null,
        lights: {
            al: null,
            dl: null,
            hl: null
        },
        renderer: null,
        model: null,
        template: '<div id="files-3d-outer"><div id="files-3d-container" style="display: none;"></div></div>',
        showModel: function(model) {
            model.castShadow = true;
            model.receiveShadow = true;
            this.scene.add(model);
            this.animate();
        },
        animate: function() {
            requestAnimationFrame(loaderCtx.three.animate);
            loaderCtx.three.render();
        },
        render: function() {
            if(!this.renderer) return;
            this.renderer.render(this.scene, this.camera);
        },
        onResize: function() {
            this.sizes.x = this.renderer.domElement.parentElement.clientWidth;
            this.sizes.y = this.renderer.domElement.parentElement.clientHeight;

            this.camera.aspect = this.sizes.x / this.sizes.y;
			this.camera.updateProjectionMatrix();
			this.renderer.setSize(this.sizes.x, this.sizes.y);
        },
        hide: function() {
            this.shutdown();
        }
    },
    onView: function(file, data) {
        loaderCtx.file = file;
        loaderCtx.dir = data.dir;
        loaderCtx.load(data.fileList.getDownloadUrl(loaderCtx.file, loaderCtx.dir), data.$file.attr('data-mime'));
    },
    load: function(file, mime) {
        loaderCtx.location = file;
        loaderCtx.mime = mime;
        loaderCtx.showThree();
    },
    getLoader: function(mime) {
        if(!mime) {
            return $.when();
        } else {
            var name, loader;
            switch(mime) {
                case 'model/vnd.collada+xml':
                    name = 'ColladaLoader.js';
                    break;
                case 'model/gltf-binary':
                case 'model/gltf+json':
                    name = 'GLTFLoader.js';
                    break;
                default:
                    return $.when();
            }
            var loaderScript = OC.filePath('files_3d', 'js/vendor/loaders', name);
            var deferred = $.Deferred();
            var script = document.createElement('script');
            script.src = loaderScript;
            script.setAttribute('nonce', btoa(OC.requestToken));
            script.onload = function() {
                try {
                    switch(mime) {
                        case 'model/vnd.collada+xml':
                            loader = new THREE.ColladaLoader();
                            break;
                        case 'model/gltf-binary':
                        case 'model/gltf+json':
                            loader = new THREE.GLTFLoader();
                            break;
                    }
                } catch(e) {
                    console.log(e);
                }
                deferred.resolve(loader);
            };
            document.head.appendChild(script);

            return deferred;
        }
    },
    showThree: function() {
        loaderCtx.three.initThreeJs().then(function() {
            return loaderCtx.getLoader(loaderCtx.mime);
        }).then(function(loader) {
            switch(loaderCtx.mime) {
                case 'model/vnd.collada+xml':
                case 'model/gltf-binary':
                case 'model/gltf+json':
                    loader.load(loaderCtx.location, function(model) {
                        loaderCtx.three.showModel(model.scene);
                    }, null, function(e) {
                        console.log(e);
                    });
                    break;
            }
        });
    },
    hideThree: function() {
        loaderCtx.three.hide();
    },
    file: null,
    dir: null,
    location: null,
    inline: null,
    mime: null,
    mimeTypes: [
        'model/vnd.collada+xml',
        'model/gltf-binary',
        'model/gltf+json'
    ]
};

$(document).ready(function() {
    $(document).keyup(function(e) {
        if(e.keyCode == 27) {
            loaderCtx.hideThree();
        }
    });

    if(typeof FileActions !== 'undefined') {
        for(var i=0; i<loaderCtx.mimeTypes.length; i++) {
            var m = loaderCtx.mimeTypes[i];
            OCA.Files.fileActions.register(m, 'View', OC.PERMISSION_READ, '', loaderCtx.onView);
            OCA.Files.fileActions.setDefault(m, 'View');
        }
    }
});
