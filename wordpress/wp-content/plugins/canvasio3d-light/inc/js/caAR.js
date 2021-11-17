var caAR = {
	divID:'',
	arID:0,
	maxArID:0,
	path:'',
	upPath:'',
	ajaxurl:'',
	texturePath:'',
	newSizeOn:[],
	lastError:[],
	windowWidth:[],
	windowHeight:[],
	window:[],
	camera:[],
	cubeCamera:[],
	cubeMapPath:'',
	cubeCamOn:[],
	cubeScene:[],
	scene:[],
	rootNode:[],
	renderer:[],
	control:[],
	entryData:[],
	handOn:[],
	mousePan:false,
	isLoaded:false,
	envCubeMap:[],
	shadowToggle:[],
	light0:[],
	light1:[],
	handPointerFlag:true,
	fullscreenFlag:false,
	fullScreen:[],
	fullscreenFix:[],
	backColor:[],
	msgTimeOut:8000,
	timeOutVar:null,
	windowInitFlag:[],
	wireframe:[],
	galleryID:-1,
	galleryLastID:-1,
	galleryWindow:0,
	objEditOn:false,
	meshMarker:null,
	matTemp:new THREE.MeshStandardMaterial({depthTest:true,depthWrite:true,transparent:true,opacity:1.0,envMapIntensity:1.0}),
	matReset:[],
	mouse:{'x':'0','y':'0'},
	objSelectFlag:false,
	raycaster:new THREE.Raycaster(),
	matEntryStag:[],
	selectMatName:'',
	ambientLight:new THREE.AmbientLight(0x404040),
	selectObjName:'',
	objTemp:null,
	screen:{x:0,x_p:'0px',y:0,y_p:'0px'},
	animState:[],
	bright:[],
	postbox:'postbox closed',
	boxHelper:null
};
//
caAR.init=function(_path, _upPath, _ajaxurl, _ver){
	caAR.path = _path;
	caAR.upPath = _upPath;
	caAR.ajaxurl = _ajaxurl;
	caAR.texturePath = _path + '/assets/map/';
	caAR.modus='front';
	//
	document.addEventListener('fullscreenchange', caAR.onFullscreenChange, false);
	document.addEventListener('mozfullscreenchange', caAR.onFullscreenChange, false);
	document.addEventListener('webkitfullscreenchange', caAR.onFullscreenChange, false);
	document.addEventListener('webkitRequestFullscreen', caAR.onFullscreenChange, false);
	document.addEventListener('msfullscreenchange', caAR.onFullscreenChange, false);
	//
	caAR.loop(); caAR.debug('Canvasio3D Light - Version: ' + _ver); caAR.jQuery('sendMsg',null, 'Canvasio3D Light - Version: ' + _ver +'<br>'+'Welcome!'); caAR.jQuery('menuHolderOn');
}
//
caAR.makeThumbnail=function(_sceneID){
	jQuery(document).ready(function ($){
		var imgString=document.getElementById('canvas_0').toDataURL(),imgName=$('#dataListVal').val(),thumbW=$('#canvas_0').width()/2,thumbH=$('#canvas_0').height()/2;if(caAR.modus!='back') return;
		$.ajax({
			url:caAR.path+'/caData.inc.php?set=3GhaOsT7&uploadType=thumbnail',type:'POST',data:{file:imgString,imgName:imgName,w:thumbW,h:thumbH,sceneID:_sceneID},
			success:function (_out){
				//success message: _out
			},
			error:function(_e){
				//error message _e.error
			}
		});
	});
}
//
caAR.switchModus=function(_m){
	caAR.modus=_m;
}
//
caAR.onFullscreenChange=function(_e){
	var tempID = _e.target.id.split('_'), arID = tempID[1];
	//
	if (!caAR.fullscreenFlag){
		caAR.fullscreenFlag=true;
		caAR.fullScreen[arID].flag=true;
	}else{
		caAR.fullscreenFlag=false;
		caAR.fullScreen[arID].flag=false;
	}
}
//
caAR.phpConnect=function(_cmd,_val,_id,_arID){
	var dataIn = {'action':'caARConnect','cmd':_cmd,'val':_val,'id':_id},temp=[]; caAR.isLoaded=false;
	//
	jQuery.ajax({
		type: 'POST',
		url: caAR.ajaxurl,
		data: dataIn,
		success: function(_back){
			_back=_back.split('#0').join('');
			//
			try{
				switch(_cmd){
					case 'checkUpdate':
						if(_back=='New Update!'){
							caAR.jQuery('showUpdateBtn',true);
						}else{
							caAR.jQuery('showUpdateBtn',false);
						}
					break;
					case 'requestKey':
						location.reload();
					break;
					case 'setWindow':
						temp=_back.split('||');
						caAR.entryData[_arID]=JSON.parse(temp[0]);
						caAR.setWindow(_arID);
					break;
					case 'getEntry':
						temp=_back.split('||'); caAR.entryData[_arID]=JSON.parse(temp[0]);
						caAR.entryData[_arID].loaded=false; caAR.arID=_arID; caAR.entryData[_arID].threeOK=true;
						caAR.setBackground(caAR.entryData[_arID].backGround,caAR.arID,caAR.entryData[_arID].imageURL, caAR.entryData[_arID].backColor);
						caAR.setBtnFullscreen(_arID,caAR.entryData[_arID].buttonOBJ.fullscreen);
						caAR.setBtnWireframe(_arID,caAR.entryData[_arID].buttonOBJ.wireframe);
						caAR.setBtnBack(_arID,caAR.entryData[_arID].buttonOBJ.back);
						caAR.setBtnRotate(_arID,caAR.entryData[_arID].buttonOBJ.rotate);
						//
						caAR.modelLoad(_arID);
					break;
					case 'getListEntry':
						temp=_back.split('||');
						caAR.entryData[_arID]=JSON.parse(temp[0]); caOpt.setupEntry(_back);
					break;					
					case 'getList':
						caOpt.showList(_back);
					break;
					case 'getNewEntry':
						caOpt.setupEntry(_back);
					break;
					case 'saveEntry':
						caOpt.saveInfo(_back); caAR.makeThumbnail(_id);
					break;
					case'deleteEntry':
						if(_back=='1')caOpt.delteOption();
					break;
				}
			}catch(_e){
				caAR.error(_arID, '1', _e);
			}
		},
		error: function(_MLHttpRequest, _textStatus, _e){
			caAR.error(_arID, '2', _e);
		}
	});
}
//
caAR.setWindow=function(_arID){
	//
	if(!caAR.windowInitFlag[_arID]){
		caAR.newSizeOn[_arID]=true; window.addEventListener('resize', function(_e){caAR.newSizeOn[_arID]=true;}, false);
		//
		if(caAR.entryData[_arID].buttonOBJ.fullscreen)caAR.icon(_arID, 'fullscreen',true);
		if(caAR.entryData[_arID].buttonOBJ.wireframe)caAR.icon(_arID, 'wireframe',true);
		if(caAR.entryData[_arID].buttonOBJ.rotate)caAR.icon(_arID, 'rotate',true);
		if(caAR.entryData[_arID].buttonOBJ.back)caAR.icon(_arID, 'back',true);
		if(caAR.entryData[_arID].buttonOBJ.glasses)caAR.icon(_arID, 'glasses',true);
		if(caAR.entryData[_arID].buttonOBJ.play)caAR.icon(_arID,'play',true);
		if(caAR.entryData[_arID].buttonOBJ.pause)caAR.icon(_arID, 'pause',true);				
		//
		caAR.handOn[_arID]=true; caAR.arID=_arID; caAR.jQuery('windowSize', _arID, {'width':caAR.entryData[_arID].windowWidth,'height':caAR.entryData[_arID].windowHeight}); caAR.jQuery('setCursor', _arID, 'grab');
		//
		caAR.fullscreenFix[_arID]={"arID":_arID, "x":0, "y":0, "flag":false};
		caAR.fullScreen[_arID]={'divID':null,'request':null,'cancel':null,'winDoc':null,'flag':false};
		caAR.fullScreen[_arID].divID=document.getElementById('caAR_'+_arID); caAR.fullScreen[_arID].divID.allowfullscreen=true; caAR.fullScreen[_arID].winDoc=window.document;
		caAR.fullScreen[_arID].request=caAR.fullScreen[_arID].divID.requestFullscreen || caAR.fullScreen[_arID].divID.mozRequestFullScreen || caAR.fullScreen[_arID].divID.webkitRequestFullscreen;
		caAR.fullScreen[_arID].cancel=caAR.fullScreen[_arID].winDoc.exitFullscreen || caAR.fullScreen[_arID].winDoc.mozCancelFullScreen || caAR.fullScreen[_arID].winDoc.webkitExitFullscreen || caAR.fullScreen[_arID].winDoc.msExitFullscreen;
		//
		caAR.shadowToggle[_arID]=true; caAR.windowInitFlag[_arID]=true; caAR.wireframe[_arID]=false;
		//
		caAR.insertThree(_arID);caAR.maxArID++;
	}
}
//
caAR.modelLoad=function(_arID){
	var type='', loader=null, mat=null, mtlLoader=null, rootPath='', temPath=caAR.entryData[_arID].modelURL.split('/'), mtlPath='', modelPath='';
	if(!caAR.entryData[_arID].modelURL){caAR.error(_arID, '5'); caAR.entryData[_arID].loaded=true; return};
	caAR.entryData[_arID].loaded=true; caAR.jQuery('fadeOut',_arID);
	//
	var newProgress=function(_xhr){
		var p=Math.round(_xhr.loaded / _xhr.total * 100, 2);
		//
		jQuery(document).ready(function($){
			$('#caProgress_'+_arID).css('opacity',p/100);
			$('#caProgress_'+_arID).css('width',p+'%');
			if(p>=100){$('#caProgress_'+_arID).css('opacity',0);}
		});
	}
	//
	var error=function(_e){
		loader=new THREE.GLTFLoader(); loader.setPath(caAR.path+'/assets/model/'); loader.arID=_arID;
		//
		loader.load('canvasio3d_404.glb', function(_modelData){
			caAR.clearThree(caAR.rootNode[_arID]);_obj=_modelData.scene; caAR.rootNode[_arID].add(_obj); caAR.jQuery('progressOff',_arID); 
			caAR.rootNode[_arID].scale.set(2,2,2); caAR.rootNode[_arID].position.y=0.5; caAR.camera[_arID].position.y=1; caAR.camera[_arID].updateProjectionMatrix(); caAR.control[_arID].update();
			caAR.lightSettings(_arID,"0",1.5); caAR.jQuery('fadeIn',_arID);caAR.newSizeOn[_arID]=true;
		});
		//
		caAR.error(_arID,'4', _e); return false;
	}	
	//
	caAR.loadCubeMap(_arID,caAR.entryData[_arID].cubeMap,caAR.entryData[_arID].cubeMapOn);
	//
	caAR.matEntryStag=[]; mat=caAR.entryData[_arID].matOBJ; caAR.matEntryStag=mat;
	//
	for(i=0; i<temPath.length-1;i++){rootPath=rootPath+temPath[i]+'/';}; modelPath=temPath[temPath.length-1];
	//
	if(modelPath.toLowerCase().search('.glb') != -1 || modelPath.toLowerCase().search('.gltf') != -1){
		loader=new THREE.GLTFLoader(); loader.setPath(rootPath); loader.arID=_arID;
		//
		loader.load(modelPath, function(_modelData){			
			//
			caAR.modelLoaded(loader.arID, _modelData.scene, mat);
		},newProgress,error);
		//
	}else if(modelPath.toLowerCase().search('.obj') != -1){
		mtlLoader=new THREE.MTLLoader(); mtlLoader.setPath(rootPath); mtlPath=modelPath.split('.obj').join('.mtl');
		//
		mtlLoader.load(mtlPath, function(_materials){
			_materials.preload();
			//
			loader=new THREE.OBJLoader(); loader.setPath(rootPath); loader.arID=_arID;
			loader.setPath(rootPath);
			loader.setMaterials(_materials)
			loader.load(modelPath, function(_modelData){
				try{caOpt.settings({'id':'caAnimation'},false);}catch(_e){};
				caAR.modelLoaded(loader.arID, _modelData, mat);
			},newProgress,error);
		});
		//
	}else if(modelPath.toLowerCase().search('.stl') != -1){
		loader=new THREE.STLLoader(); loader.setPath(rootPath); loader.arID=_arID;
		loader.load(modelPath, function(_geometry){
			var material = new THREE.MeshStandardMaterial({color: 0xff5533, specular: 0x111111, shininess: 200}); material.name="STL-Default";
			var obj=new THREE.Mesh(_geometry, material);obj.name="STL-Default";
			obj.rotation.set( -Math.PI / 2, 0, 0);
			try{caOpt.settings({'id':'caAnimation'},false);}catch(_e){};
			caAR.modelLoaded(_arID, obj, mat);
		},newProgress,error);
	}else{
		type=modelPath.split('.');
		caAR.error(_arID, '3', type[1]);
	}
}
//
caAR.modelLoaded=function(_arID, _obj, _mat){
	caAR.matReset=[]; caAR.control[_arID].reset(); caAR.control[_arID].autoRotate=caAR.entryData[_arID].autoRotate;
	caAR.clearThree(caAR.rootNode[_arID]); caAR.rootNode[_arID].add(_obj); caAR.setScale(_arID,caAR.entryData[_arID].modelScale);
	//
	caAR.bright[_arID]=caAR.entryData[_arID].bright; if(!caAR.bright[_arID])caAR.bright[_arID]=0;
	caAR.lightSettings(_arID,caAR.entryData[_arID].lightSet,caAR.bright[_arID]);
	//
	caAR.rootNode[_arID].traverse(function(_c){
		//
		if(_c.isMesh){
			//
			if(_c.material.materials){
				//
				for (i=0; i<_c.material.materials.length; i++) {
					m=_c.material.materials[i];
					caAR.matReset[m.material.name]=new THREE.MeshStandardMaterial(m.material);
					//
					m.material.envMap=caAR.envCubeMap[_arID].envMap;
					m.castShadow=caAR.entryData[_arID].shadowToggle; m.receiveShadow=caAR.entryData[_arID].shadowToggle;
				}
			}else{
				caAR.matReset[_c.material.name]=new THREE.MeshStandardMaterial(_c.material);			
				//
				_c.material.envMap=caAR.envCubeMap[_arID].envMap;
				_c.castShadow=caAR.entryData[_arID].shadowToggle; _c.receiveShadow=caAR.entryData[_arID].shadowToggle;
			}
			//
		}
		//
	});
	//
	caAR.autoCenter(_arID); caAR.setPosY(_arID,caAR.entryData[_arID].modelPosY);
	//
	if(caAR.handOn[_arID]) caAR.jQuery('handOn',_arID);
	caAR.jQuery('fadeIn',_arID);
}
//
caAR.autoCenter=function(_arID){
	caAR.rootNode[_arID].position.x=0;caAR.rootNode[_arID].position.y=0;caAR.rootNode[_arID].position.z=0;
	const box = new THREE.Box3().setFromObject(caAR.rootNode[_arID]);
	const center = box.getCenter(new THREE.Vector3());
	caAR.rootNode[_arID].position.x += (caAR.rootNode[_arID].position.x - center.x);
	caAR.rootNode[_arID].position.y += (caAR.rootNode[_arID].position.y - center.y);
	caAR.rootNode[_arID].position.z += (caAR.rootNode[_arID].position.z - center.z);
	//caAR.rootNode[_arID].updateMatrix();
}
//
caAR.setScale=function(_arID,_val){
	caAR.rootNode[_arID].scale.set(_val,_val,_val);
	caAR.autoCenter(_arID);
}
//
caAR.setPosY=function(_arID,_val){
	 caAR.autoCenter(_arID); caAR.rootNode[_arID].position.y=_val;
}
//
caAR.setAutoRotate=function(_arID,_flag){
	caAR.control[_arID].autoRotate=_flag;
}
//
caAR.setBtnFullscreen=function(_arID,_flag){
	caAR.icon(_arID, 'fullscreen', _flag);
}
//
caAR.setBtnWireframe=function(_arID,_flag){
	caAR.icon(_arID, 'wireframe', _flag);
}
//
caAR.setBtnAnim=function(_arID,_flag){
	if(_flag){
		if(caAR.animState[_arID]){
			caAR.icon(_arID, 'play', false);
			caAR.icon(_arID, 'pause', true);
		}else{
			caAR.icon(_arID, 'play', true);
			caAR.icon(_arID, 'pause', false);
		}
	}else{
		caAR.icon(_arID, 'play', false);
		caAR.icon(_arID, 'pause', false);
	}
}
//
caAR.setBtnBack=function(_arID,_flag){
	caAR.icon(_arID, 'back', _flag);
}
//
caAR.setBtnRotate=function(_arID,_flag){
	caAR.icon(_arID, 'rotate', _flag);
}
//
caAR.clearThree=function(_obj){
	var count=0,i=0;
	//
	if(_obj.children.length > 0){
		//
		_obj.traverse(function(_obj){
			if(_obj) count++;
		});
		//
		for(i=count;i>0;i--){
			_obj.remove(_obj.children[i]);
		}
		//
		_obj.remove(_obj.children[0]);
	}
	//
	if(_obj.geometry) _obj.geometry.dispose();
	if(_obj.material) _obj.material.dispose();
	if(_obj.texture) _obj.texture.dispose();
};
//
caAR.icon=function(_arID,_val,_flag){
	var img = document.createElement('img'); img.setAttribute("class", "caIcon");
	//
	switch(_val){			
		case 'fullscreen':
			img.src = caAR.path + '/assets/icons/caFullscreen.png';
			img.id = "caFullscreen_"+_arID;
			img.title = "Fullscreen";
		break;
		case 'wireframe':
			img.src = caAR.path + '/assets/icons/caWireframe.png';
			img.id = "caWireframe_"+_arID;
			img.title = "Show wireframe";
		break;		
		case 'rotate':
			img.src = caAR.path + '/assets/icons/caRotate.png';
			img.id = "caRotate_"+_arID;
			img.title = "Auto Rotate";
		break;
		case 'back':
			img.src = caAR.path + '/assets/icons/caBack.png';
			img.id = "caBack_"+_arID;
			img.title = "Camera back";
		break;		
		case 'glasses':
			img.src = caAR.path + '/assets/icons/caGlasses.png';
			img.id = "caGlasses_"+_arID;
			img.title = "Head mounted display";
		break;
		case 'play':
			img.src = caAR.path + '/assets/icons/caPlay.png';
			img.id = "caPlay_"+_arID;
			img.title = "Animation Play";
		break;
		case 'pause':
			img.src = caAR.path + '/assets/icons/caPause.png';
			img.id = "caPause_"+_arID;
			img.title = "Animation Pause";		
		break;					
	}
	//
	jQuery(document).ready(function($){
		if(_flag){
			if(!document.getElementById(img.id)){
				img.onmousedown = function(_e){caAR.button(_e)};
				$('#caOverlay_'+_arID).append(img);
			}
		}else{
			$('#'+img.id).remove();
		}
	});
}
//
caAR.loadCubeMap=function(_arID,_path,_flag){
	var texLoader = new THREE.TextureLoader(); if(!_path || _path=='') _path=caAR.texturePath+'refMap.jpg';
	//
	texLoader.load( _path, function (_texture){
		caAR.cubeCamOn[_arID]=_flag;
		//
		caAR.cubeScene[_arID].background = new THREE.WebGLRenderTargetCube(2048, 2048, {anisotropy:8, generateMipmaps: true, minFilter: THREE.LinearMipmapLinearFilter, magFilter: THREE.LinearFilter}).fromEquirectangularTexture(caAR.renderer[_arID], _texture);
	});	
	//
}
//
caAR.loop=function(){
	if(caAR.postbox == 'postbox closed'){requestAnimationFrame(caAR.loop);return false;};
	//
	for(var i=0; i<caAR.maxArID; i++){
		if(caAR.entryData[i] && caAR.entryData[i].threeOK){
			if(caAR.newSizeOn[i]){caAR.jQuery('resizeCanvas', i); caAR.newSizeOn[i]=false;}
			//
			if(!caAR.entryData[i].loaded){
				caAR.modelLoad(i);
			}else{
				caAR.rootNode[i].visible=false; caAR.cubeCamera[i].update(caAR.renderer[i], caAR.cubeScene[i]);caAR.rootNode[i].visible=true;
				caAR.control[i].update(); caAR.renderer[i].render(caAR.scene[i], caAR.camera[i]);
			}
			//
		}
	}
	//
	requestAnimationFrame(caAR.loop);
}
//
caAR.debug=function(_s){
	console.log(_s);
}
//
caAR.insertThree=function(_arID){
	caAR.rootNode[_arID] = new THREE.Object3D(); caAR.fullScreen[_arID].flag=false;
	//
	caAR.fullscreenFix[_arID].x=0;
	caAR.fullscreenFix[_arID].y=0;
	caAR.fullscreenFix[_arID].flag=false;
	//
	caAR.camera[_arID] = new THREE.PerspectiveCamera(50, caAR.windowWidth[_arID] / caAR.windowHeight[_arID], 1, 2048);
	caAR.camera[_arID].position.set(0,0,10);
	//
	caAR.scene[_arID] = new THREE.Scene();
	caAR.cubeScene[_arID] = new THREE.Scene();
	caAR.scene[_arID].add(caAR.rootNode[_arID]);
	//
	caAR.cubeCamera[_arID] = new THREE.CubeCamera(0.1, 1024, 1024);
	caAR.cubeCamera[_arID].renderTarget.texture.generateMipmaps = true;
	caAR.cubeCamera[_arID].renderTarget.texture.magFilter = THREE.NearestFilter;
	caAR.cubeCamera[_arID].renderTarget.texture.minFilter = THREE.NearestFilter;
	caAR.cubeCamera[_arID].renderTarget.texture.format = THREE.RGBAFormat;
	caAR.cubeCamera[_arID].needsUpdate = true;
	caAR.cubeScene[_arID].add(caAR.cubeCamera[_arID]);
	//
	caAR.envCubeMap[_arID] = new THREE.MeshStandardMaterial({
		envMap: caAR.cubeCamera[_arID].renderTarget.texture
	});
	//
	caAR.renderer[_arID] = new THREE.WebGLRenderer({antialias: true, preserveDrawingBuffer: true, alpha: true});
	caAR.renderer[_arID].setPixelRatio(1);
	caAR.renderer[_arID].shadowMap.enabled = true;
	caAR.renderer[_arID].shadowMap.type = THREE.PCFSoftShadowMap;
	caAR.renderer[_arID].toneMapping = THREE.Uncharted2ToneMapping;
	caAR.renderer[_arID].toneMappingExposure = 2;
	caAR.renderer[_arID].toneMappingWhitePoint = 2;
	caAR.renderer[_arID].gammaOutput=false;
	caAR.renderer[_arID].gammaInput=false;
	//
	caAR.renderer[_arID].domElement.id='canvas_' + _arID;
	caAR.renderer[_arID].domElement.setAttribute("class", 'arCanvas');
	//---- lights ----
	caAR.scene[_arID].add(caAR.ambientLight);
	//
	caAR.light0[_arID] = new THREE.HemisphereLight(0xffffbb,0x080820,0.5);
	caAR.light0[_arID].position.set(0,256,0);
	caAR.scene[_arID].add(caAR.light0[_arID]);
	//
	caAR.light1[_arID] = new THREE.SpotLight(0xf6f6f6,0.5);
	caAR.light1[_arID].position.set(0,10,50);
	caAR.light1[_arID].castShadow = true;
	caAR.light1[_arID].shadow.mapSize.width = 128;
	caAR.light1[_arID].shadow.mapSize.height = 128;
	caAR.light1[_arID].penumbra = 0.1;
	caAR.light1[_arID].decay = 2;
	caAR.light1[_arID].shadow.camera.near = 0.5;
	caAR.light1[_arID].shadow.camera.far = 2048;
	caAR.light1[_arID].shadow.camera.fov = 50;
	caAR.scene[_arID].add(caAR.light1[_arID]);
	//
	caAR.window[_arID] = document.getElementById('caAR_'+_arID);
	caAR.window[_arID].appendChild(caAR.renderer[_arID].domElement);
	//
	caAR.window[_arID].ontouchstart=function(_e){caAR.jQuery('setCursor', _arID, 'grabbing');};
	caAR.window[_arID].ontouchend=function(_e){caAR.jQuery('setCursor', _arID, 'grab');};
	caAR.window[_arID].ontouchcancel=function(_e){caAR.jQuery('setCursor', _arID, 'grab');};
	caAR.window[_arID].onmousedown=function(_e){caAR.jQuery('setCursor', _arID, 'grabbing');};
	caAR.window[_arID].onmouseup=function(_e){caAR.jQuery('setCursor', _arID, 'grab');};
	caAR.window[_arID].onmouseout=function(_e){caAR.jQuery('setCursor', _arID, 'grab');};
	//
	caAR.control[_arID]=new THREE.OrbitControls(caAR.camera[_arID], caAR.renderer[_arID].domElement);
	caAR.control[_arID].enableKeys=false;
	caAR.control[_arID].minDistance=3;
	caAR.control[_arID].maxDistance=128;
	caAR.control[_arID].correctForDepth=0.5;
	caAR.control[_arID].enablePan=caAR.entryData[_arID].caCamPan;
	caAR.control[_arID].autoRotate=true;
	caAR.control[_arID].wireframe=true;
	caAR.control[_arID].screenSpacePanning=true;
	//
	caAR.setBackground(caAR.entryData[_arID].backGround, _arID, caAR.entryData[_arID].imageURL, caAR.entryData[_arID].backColor); caAR.entryData[_arID].threeOK=true;
}
//
caAR.error=function(_arID, _nr, _e){
	var str=''; if(caAR.lastError[0] == _arID && caAR.lastError[1]==_nr) return;
	//
	switch(_nr){
		case '1':
			str='DB - ' + _e;
		break;		
		case '2':
			str='Ajax - ' + _e;
		break;
		case '3':
			str='File format not supported!';
		break;
		case '4':
			if(_e.currentTarget && _e.currentTarget.responseURL){
				str='File not found (404) - ' + _e.currentTarget.responseURL;
			}else{
				str='File '+ (_arID+1) +' loading Error: ' + _e;
			}
		break;
		case '5':
			str='Loading data error - ID: ' + (_arID+1);
		break;
	}
	//
	caAR.jQuery('sendMsg',null,str);
	//
	caAR.lastError[0]=_arID; caAR.lastError[1]=_nr;
}
//
caAR.button=function(_e){
	var c = _e.target.id.split('_'), arID=c[1];
	switch(c[0]){
		case 'caPlay':
			if(caAR.animClips[arID]){
				caAR.animClips[arID].forEach(function (_clip){
					caAR.animMixer[arID].clipAction(_clip).paused=false;
					caAR.animMixer[arID].clipAction(_clip).play();
				});
				caAR.animState[arID]=true; caAR.setBtnAnim(arID,true);
			}
		break;
		case 'caPause':
			if(caAR.animClips[arID]){
				caAR.animClips[arID].forEach(function (_clip){
					caAR.animMixer[arID].clipAction(_clip).paused=true;
				});
				caAR.animState[arID]=false; caAR.setBtnAnim(arID,true);
			}
		break;
		case 'caBack':
			caAR.debug('back ...');
		break;
		case 'caRotate':
			if(caAR.control[arID].autoRotate){
				caAR.control[arID].autoRotate=false;
			}else{
				caAR.control[arID].autoRotate=true;
			}
		break;		
		case 'caFullscreen':
			if(!caAR.fullScreen[arID].winDoc.fullscreenElement && !caAR.fullScreen[arID].winDoc.mozFullScreenElement && !caAR.fullScreen[arID].winDoc.webkitFullscreenElement &&  !caAR.fullScreen[arID].flag) {
				caAR.fullScreen[arID].request.call(caAR.fullScreen[arID].divID); caAR.fullScreen[arID].flag=true;
			} else {
				caAR.fullScreen[arID].cancel.call(caAR.fullScreen[arID].winDoc);
			}
		break;
	}
}
//
caAR.setBackground=function(_cmd,_arID,_imgUrl,_col){
	//
	if(_col && _cmd=='col'){
		caAR.renderer[_arID].setClearColor(String(_col));
		caAR.jQuery('image', _arID, '');
	}
	//
	if(_imgUrl && _cmd=='img'){
		caAR.renderer[_arID].setClearColor( 0x000000, 0 );
		caAR.jQuery('image', _arID, _imgUrl);
	}
}
//
caAR.lightSettings=function(_arID,_val,_valB){
	if(_val!=0)return;caAR.bright[_arID]=Number(_valB);
	//
	switch(_val){
		case "0":
			caAR.light0[_arID].intensity=caAR.bright[_arID];//hemi
			caAR.light0[_arID].position.set(0,256,0);
			//
			caAR.light1[_arID].intensity=caAR.bright[_arID];//spot
			caAR.light1[_arID].position.set(-0.2,5,20);
			caAR.light1[_arID].castShadow=true;
		break;
	}
	//
	caAR.renderer[_arID].toneMappingWhitePoint = 2.8 - (caAR.bright[_arID]/2);
}
//
caAR.jQuery=function(_cmd, _arID, _val){
	var temp=[],str1=[],str2=[];
	//
	jQuery(document).ready(function($){
		$('.toggle-indicator').mouseup(function(_e){caAR.newSizeOn[_arID]=true;});if(caAR.modus=="back"){$("#canvas_0").mouseover(function(){$('#caOptions').css('display','none');}).mouseout(function(){$('#caOptions').css('display','block');});};
		//
		$('input[type=file]').on('change', function(_e){
			var fd=new FormData(),ty = '',tmp=[],uploadType='model',data='',i,t,ID="<?php get_the_ID();?>";if($('#caModelUpload')[0].files[0]==undefined)return;
			fd.append('userfile', $('#caModelUpload')[0].files[0]); $('#modelUpload').prop("disabled", true); $("#caModelUpload").val('');
			if(caAR.modus=='front'){$('#caUpload_'+caAR.arID).css('pointer-events','none');$('#caUpload_'+caAR.arID).css('opacity',0.3);}
			caAR.jQuery('sendMsg',0,'Model upload - Please wait ...');
			//
			$.ajax({
				url:caAR.path+'/caData.inc.php?set=3GhaOsT7&uploadType=model&modus='+caAR.modus+'&post_ID='+ID,
				data: fd,
				processData:false,
				contentType:false,
				type:'POST',
				success:function (_data){
					//
					$('#modelUpload').prop("disabled", false);data=_data.split('|#|');
					if(data!="NoTypeMatch"){
						//
						i=caAR.upPath.length-1; t=caAR.upPath.charAt(i);
						if(t!='/'){
							caAR.entryData[caAR.arID].modelURL=caAR.upPath+'/'+data[0];
							if(caAR.modus=='back')caOpt.tempEntry.modelURL=caAR.upPath+'/'+data[0];
						}else{
							caAR.entryData[caAR.arID].modelURL=caAR.upPath+data[0];
							if(caAR.modus=='back')caOpt.tempEntry.modelURL=caAR.upPath+data[0];
						}
						if(caAR.modus=='front'){$('#caUpload_'+caAR.arID).css('pointer-events','auto');$('#caUpload_'+caAR.arID).css('opacity',0.6);}
						$('#caObjName').html(data[0]);
						caAR.jQuery('sendMsg',0,'Loading Model');caAR.modelLoad(caAR.arID);if(caAR.modus=='back')caOpt.saveInfoOn(true);
						//
					}else{
						caAR.jQuery('sendMsg',0,'<?php arClass::msg(26);?>');
					}
				},error:function(_e){
					if(caAR.modus=='front'){$('#caUpload_'+caAR.arID).css('pointer-events','auto');$('#caUpload_'+caAR.arID).css('opacity',0.6);}
					$('#modelUpload').prop("disabled", false);
					caAR.jQuery('sendMsg',0,'Upload Error: ' + _e);
				}
			});
			//
		});		
		//
		$('.caImage').mousedown(function(_e){
			//
			if(_e){
				temp=_e.target.getAttribute('alt').split(','); if(!temp || !temp[0])return;
				//
				if(temp[1]){
					str1=temp[0].split('=');
					str2=temp[1].split('=');
					//
					if(str1[0].toLowerCase().search('area') == 0 && str1[1]){
						caAR.galleryWindow=parseInt(str1[1])-1;
						caAR.galleryID=str2[1];
					}else if(str1[0].toLowerCase().search('scene') == 0 && str2[1]){
						caAR.galleryWindow=parseInt(str2[1])-1;
						caAR.galleryID=str1[1];
					}					
				}else{
					str1=temp[0].split('=');
					caAR.galleryWindow=0;
					caAR.galleryID=str1[1];
				}
				//
				if(caAR.galleryID != caAR.galleryLastID){
					caAR.galleryLastID=caAR.galleryID;
					caAR.phpConnect('getEntry',undefined,caAR.galleryID,caAR.galleryWindow);
				}
			}
		})
		//
		switch(_cmd){
			case 'menuHolderOn':
				$('#caOptionsDiv').appendTo('#menuHolder');
			break;
			case 'showUpdateBtn':
				if(_arID){
					$('#tdUpdate').css('display','fetch');
					$('#tdUpdate').css('max-width','74px');
					$('#tdUpdate').css('width','74px');
					$('#caGetUpdate').css('display','block');
					$('#caGetUpdate').prop('disabled', false);
				}else{
					$('#tdUpdate').css('display','none');
					$('#tdUpdate').css('max-width','1px');
					$('#tdUpdate').css('width','1px');
					$('#caGetUpdate').css('display','none');
					$('#caGetUpdate').prop('disabled', true);
				}
			break;
			case 'handOn':
				$('#caOverlayDiv_'+_arID).css('display','block');
				$('#caHpIcon_' + _arID).css('display','block');
				$('#caOptionsDiv').css('display','block');
			break;
			case 'backGroundSwitch':
				caAR.debug('BackgroundSwitch: ' + _val);
			break;			
			case 'sendMsg':
				temp=$('#caMsg').html(); if(temp){str=temp.split('<br>'); if(str[0] && str[1]) temp=str[1]+'<br>';}
				$('#caMsg').html(temp  + _val + '<br>');
			break;
			case 'setCursor':
				if(caAR.handOn[_arID] && _val=='grabbing' && !caAR.objSelectFlag){
					$('#caHpIcon_'+_arID).fadeOut(777); caAR.handOn[_arID]=false;
				}else if(caAR.objSelectFlag){
					_val='crosshair';
				}
				$('#caAR_'+_arID).css('cursor', _val);
			break;
			case 'fadeOut':
				$('#caProgress_'+_arID).css('width','0px'); $('#caProgress_'+_arID).css('opacity','0');
				$('#canvas_'+_arID).fadeOut(0);
			break;			
			case 'fadeIn':
				$('#canvas_'+_arID).fadeIn(1000);
			break;			
			case 'image':
				$('#caAR_'+_arID).css("background", "url('"+_val+"') no-repeat center center");
			break;
			case 'windowSize':
				if(caAR.modus=='back'){
					caAR.windowHeight[_arID]='640px';
					caAR.windowWidth[_arID]='100%';
					caAR.postbox = $('#Canvasio3D_Options').attr('class');
				}else{
					caAR.windowWidth[_arID]=_val.width; caAR.windowHeight[_arID]=_val.height;
					caAR.postbox ='';
				}
				caAR.screen.x = $('#arWrap_0').width();
			break;
			case 'resizeCanvas':
				//
				if(!caAR.fullScreen[_arID].flag){
					//
					if(caAR.windowWidth[_arID].search('%')!=-1){
						caAR.screen.x = $('#arWrap_'+_arID).width();
						caAR.screen.x_p = caAR.screen.x + 'px';
					}else{
						document.getElementById('arWrap_'+_arID).style='max-width:'+caAR.windowWidth[_arID];
						//
						caAR.screen.x = $('#arWrap_'+_arID).width();
						caAR.screen.x_p = caAR.screen.x + 'px';
					}
					//
					if(caAR.windowHeight[_arID].search('%')!=-1){
						caAR.screen.y = $(window).height();
						caAR.screen.y_p = caAR.screen.y + 'px';
					}else{
						document.getElementById('arWrap_'+_arID).style='max-height:'+caAR.windowHeight[_arID];
						document.getElementById('arWrap_'+_arID).style='height:'+caAR.windowHeight[_arID];
						//
						caAR.screen.y = $('#arWrap_'+_arID).height();
						caAR.screen.y_p = caAR.screen.y + 'px';
					}
					//
					if(window.innerWidth<caAR.screen.x){caAR.screen.x=window.innerWidth;caAR.screen.x_p=window.innerWidth+'px';}
					//
					$('#caAR_'+_arID).css("width", caAR.screen.x_p);
					$('#caProgress_'+_arID).css("max-width", caAR.screen.x_p);
					$('#caAR_'+_arID).css("backgroundSize", "auto auto");
					//
				}else{
					caAR.screen.x = $(window).width();
					caAR.screen.x_p = caAR.screen.x + 'px';
					//				
					caAR.screen.y = $(window).height();
					caAR.screen.y_p = caAR.screen.y + 'px';
					//
					$('#caAR_'+_arID).css("width", '100%');
					$('#caProgress_'+_arID).css("max-width",'100%');
					$('#caAR_'+_arID).css("backgroundSize", "100% auto");
				}
				//
				caAR.camera[_arID].aspect = caAR.screen.x / caAR.screen.y;
				caAR.camera[_arID].updateProjectionMatrix();
				caAR.renderer[_arID].setSize(caAR.screen.x, caAR.screen.y);
				//
				if(caAR.handPointerFlag){
					$('#caHpIcon_' + _arID).css('left',((caAR.screen.x/2)-55)+'px');
					$('#caHpIcon_' + _arID).css('top',((caAR.screen.y/2)-36)+'px');
				}
				//
				$('#canvas_'+_arID).css('display','block');
				$('#caOverlayDiv_'+_arID).css('width', caAR.screen.x_p);
				if(caAR.modus=='back')caAR.postbox = $('#Canvasio3D_Options').attr('class');
			break;
		}
		//
	});
	//	
}