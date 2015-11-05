var NBL = NBL || {};
(function () {
	//and unfortunate mix of camelCase and snake_case
	NBL.javascript_init = function () {
		window.addEventListener("keydown", this.onKeyDown);
		document.getElementById('files').addEventListener('change', this.handleFileSelect, false);
		window.addEventListener("resize", this.resizeFunc);
		if(document.getElementById("viewer_nav_container") !== null) {
			document.getElementById("viewer_nav_container").tick_position = 0;
		}
		window.addEventListener("mousemove", function (e) {
			if(e.pageY < 48 && !(e.buttons & 1))
				NBL.shownavbar();
			else if(e.pageY > 80 && document.getElementById("overlay").style.display != "block")
				NBL.hidenavbar();
		});
		this.render_init();

		if(targetUrl === undefined) {
			this.loadDummyBlock();
			this.toggleMenu();
		} else {
			this.hidenavbar();

			$.get(targetUrl, function (data){
				NBL.loadBLSData(data);
			});
		}
	};

	NBL.handleFileSelect = function (evt) {
		var f = evt.target.files[0];
		var r = new FileReader();
		r.onload = function (e) {
			console.log("two");
			var contents = e.target.result;

			NBL.loadBLSData(contents);
		};
		r.readAsText(f);
	};

	NBL.loadBLSData = function (data) {
		var saveData = data.split("\n");

		//warning(1), descsize(1), desc(descsize), colortable(64), linecount(1)
		var line = parseInt(saveData[1]) + 2;
		var material = new Array(64);

		for(var i=0; i<64; i++) {
			material[i] = new BABYLON.StandardMaterial("std", NBL.scene);
			var cols = saveData[line + i].split(" ");
			material[i].diffuseColor = new BABYLON.Color3(parseFloat(cols[0]), parseFloat(cols[1]), parseFloat(cols[2]));
			//console.debug(cols);
			//console.log(parseInt(cols[0]) + " " + parseInt(cols[1]) + " " + parseInt(cols[2]));
			material[i].alpha = parseFloat(cols[3]);
			//material[i].wireframe = true;
			material[i].specularColor = new BABYLON.Color3(0, 0, 0);
		}
		line += 65;

		var avgx = 0;
		var avgy = 0;
		var avgz = 0;
		var brickcount = 0;

		var maxx;
		var maxy;
		var maxz;

		for(; line<saveData.length; line++) {
			if(saveData[line].substr(0, 2) == "+-" || saveData[line] === "") {
				continue;
			} else {
				var quoteindex = saveData[line].indexOf("\"");

				if(quoteindex == -1) {
					//console.log("Found a weird line, skipping...");
					continue;
				}
				var uiname = saveData[line].substr(0, quoteindex);
				var rest = saveData[line].substr(quoteindex + 2);
				var subdata = rest.split(" ");
				var x = parseFloat(subdata[0]);
				var y = parseFloat(subdata[1]);
				var z = parseFloat(subdata[2]);
				var angleid = parseInt(subdata[3]);
				var isbaseplate = parseInt(subdata[4]);
				var colorid = parseInt(subdata[5]);
				//we really don't care about the stuff in between
				var isrendering = parseInt(subdata[rest.length - 1]);

				var mesh = this.buildMesh(uiname);

				mesh.material = material[colorid];
				//console.log(colorid);
				//console.debug(material[colorid]);
				mesh.position.x = x;
				mesh.position.y = z;
				mesh.position.z = y;
				mesh.rotation.y = angleid * Math.PI/2;
				mesh.renderOutline = true;
				//mesh.outlineWidth = 0.01
				mesh.outlineColor = new BABYLON.Color3(0, 0, 0);

				avgx += x;
				avgy += z;
				avgz += y;
				brickcount++;

				if(maxx === undefined || x > maxx)
					maxx = x;

				if(maxy === undefined || z > maxy)
					maxy = z;

				if(maxz === undefined || y > maxz)
					maxz = y;
			}
		}

		//look at average position in build
		avgx /= brickcount;
		avgy /= brickcount;
		avgz /= brickcount;
		//console.log("aiming camera at " + avgx + " " + avgy + " " + avgz);
		this.camera.position = new BABYLON.Vector3(maxx + 15, maxy + 15, maxz + 15);
		this.camera.setTarget(new BABYLON.Vector3(avgx, avgy, avgz));
	};

	NBL.buildMesh = function (uiname) {
		if(this.brickData.hasOwnProperty(uiname)) {
			var jsonobj = this.brickData[uiname];
			var mesh;

			//check if basic ramp
			if(uiname.indexOf("Ramp") != -1 && !jsonobj.n && !jsonobj.e && !jsonobj.w) {
				//console.log("Ramping up: " + uiname);
				mesh = new BABYLON.Mesh("mesh", this.scene);
				var indices = [];
				var positions = [];
				//var normals = [];

				//bottom face
				positions.push(0.5, -0.5, 0.5); //0
				positions.push(0.5, -0.5, -0.5);
				positions.push(-0.5, -0.5, 0.5); //2
				positions.push(-0.5, -0.5, -0.5);
				indices.push(0, 1, 2);
				indices.push(3, 2, 1);
				//console.log("part 2");

				if(uiname.indexOf("Corner") != -1 && !jsonobj.s) {
					//corner piece
					//unfortunately a lot of duplication, I dug myself into this one

					//top face
					positions.push(-0.5 + 1/jsonobj.x, 0.5, -0.5 + 1/(jsonobj.y)); //4
					positions.push(-0.5 + 1/jsonobj.x, 0.5, -0.5);
					positions.push(-0.5, 0.5, -0.5 + 1/(jsonobj.y)); //6
					positions.push(-0.5, 0.5, -0.5);
					indices.push(6, 5, 4);
					indices.push(5, 6, 7);

					////bevel on front
					positions.push(0.5, -0.4, 0.5); //8
					positions.push(-0.5, -0.4, 0.5); //9
					positions.push(0.5, -0.4, -0.5); //might need to flip last sign
					indices.push(0, 9, 8);
					indices.push(0, 2, 9);
					indices.push(10, 1, 0);
					indices.push(8, 10, 0);

					//slopes
					indices.push(9, 6, 4);
					indices.push(8, 9, 4);
					indices.push(10, 8, 4);
					indices.push(5, 10, 4);

					//sides
					indices.push(3, 7, 6);
					indices.push(2, 3, 6);
					indices.push(2, 6, 9);
					indices.push(7, 3, 5);
					indices.push(3, 1, 5);
					indices.push(5, 1, 10);
				} else {
					//top face
					positions.push(0.5, 0.5, -0.5 + 1/(jsonobj.y)); //4
					positions.push(0.5, 0.5, -0.5);
					positions.push(-0.5, 0.5, -0.5 + 1/(jsonobj.y)); //6
					positions.push(-0.5, 0.5, -0.5);
					indices.push(5, 6, 7);
					indices.push(5, 4, 6);

					////bevel on front
					positions.push(0.5, -0.4, 0.5); //8
					positions.push(-0.5, -0.4, 0.5); //9
					indices.push(0, 9, 8);
					indices.push(0, 2, 9);

					//slope
					indices.push(8, 9, 4);
					indices.push(9, 6, 4);

					//back
					indices.push(5, 7, 1);
					indices.push(7, 3, 1);

					//sides
					indices.push(1, 4, 5);
					indices.push(3, 7, 6);
					indices.push(0, 4, 1);
					indices.push(2, 3, 6);
					indices.push(0, 8, 4);
					indices.push(2, 6, 9);
				}

				if(uiname.charAt(0) === '-') {
					//upsidedown ramp
					//first we adjust the geometry to flip vertically
					for(var i=0; i<positions.length; i+=3) {
						positions[i+1] *= -1;
					}

					//then we reverse the direction of each face, so it isn't inside out
					for(var i=0; i<indices.length; i+=3) {
						var temp = indices[i];
						indices[i] = indices[i+2];
						indices[i+2] = temp;
					}
				}

				//scale positions by x z y
				for(var i=0; i<positions.length; i+=3) {
					positions[i] *= jsonobj.x / 2;
					positions[i+1] *= jsonobj.z / 5;
					positions[i+2] *= jsonobj.y / 2;
				}
				mesh.setVerticesData(BABYLON.VertexBuffer.PositionKind, positions, false);
				mesh.setIndices(indices);
				//mesh.setVerticesData(BABYLON.VertexBuffer.NormalKind, normals, true);
			} else if(uiname.indexOf("Round") != -1 && !jsonobj.n && !jsonobj.e && !jsonobj.w && !jsonobj.s) {
				mesh = BABYLON.Mesh.CreateCylinder("cylinder", jsonobj.z / 5, jsonobj.x / 2, jsonobj.x / 2, 8, this.scene, false);
			} else {
				mesh = new BABYLON.Mesh("mesh", this.scene);
				var vertexData = BABYLON.VertexData.CreateBox(1);
                
				//scale positions by x z y
				for(var i=0; i<vertexData.positions.length; i+=3) {
					vertexData.positions[i] *= jsonobj.x / 2;
					vertexData.positions[i+1] *= jsonobj.z / 5;
					vertexData.positions[i+2] *= jsonobj.y / 2;
				}
				vertexData.applyToMesh(mesh, false);
			}
			return mesh;
		} else {
			console.log("missing datablock: " + uiname);
			return BABYLON.Mesh.CreateBox("mesh", 3, this.scene);
		}
	}

	NBL.onKeyDown = function (evt) {
		switch(evt.keyCode) {
			case 27:
				NBL.toggleMenu();
				break;
		}
	};

	NBL.hidenavbar = function () {
		window.clearTimeout(this.navbar_tick);
		var element = document.getElementById("viewer_nav_container");

		if(element !== null) {
			if(element.tick_position > -64) {
				element.tick_position -= 2;
				element.style.top = (element.tick_position) + "px";
				this.navbar_tick = setTimeout(NBL.hidenavbar, 10);
			}
		}
	}

	NBL.shownavbar = function () {
		window.clearTimeout(this.navbar_tick);
		var element = document.getElementById("viewer_nav_container");

		if(element !== null) {
			if(element.tick_position < 0) {
				element.tick_position += 2;
				element.style.top = (element.tick_position) + "px";
				this.navbar_tick = setTimeout(NBL.shownavbar, 10);
			}
		}
	}

	NBL.toggleMenu = function () {
		var overlay = document.getElementById("overlay");

		if(overlay.style.display == "block") {
			this.pop_menu();
		} else {
			this.push_menu();
		}
	};

	NBL.push_menu = function () {
		var overlay = document.getElementById("overlay");
		var overlay_info = document.getElementById("overlay_info");
		overlay.style.opacity = 0.0; //to fade in later
		overlay_info.style.opacity = 0.0;

		overlay.style.display = "block";
		overlay_info.style.display = "block";
		overlay_info.style.maxHeight = 0.9*overlay.offsetHeight + "px";
		overlay_info.style.maxWidth = 0.8*overlay.offsetWidth + "px";

		overlay_info.style.top = (0.5*(overlay.offsetHeight - overlay_info.offsetHeight)) + "px";
		overlay_info.style.left = (0.5*(overlay.offsetWidth - overlay_info.offsetWidth)) + "px";

		this.fade(overlay, 0.7);
		this.fade(overlay_info, 1.0);
		this.shownavbar();
	};

	NBL.pop_menu = function () {
		var overlay = document.getElementById("overlay");
		var overlay_info = document.getElementById("overlay_info");
		overlay.style.display = "none";
		overlay_info.style.display = "none";
		this.hidenavbar();
	};

	NBL.fade = function (element, val, callback) {
		window.clearTimeout(element.fade_var);
		var op = window.getComputedStyle(element).opacity;//element.style.opacity;

		if(op > val) {
			if(op < (val + 0.051)) {
				element.style.opacity = parseFloat(val);

				if(callback != undefined)
					callback(element);
				return;
			}
			element.style.opacity = parseFloat(element.style.opacity) - 0.05;
			//console.error("decreased opacity to: " + (parseFloat(element.style.opacity) - 0.05));
		} else {
			//for some reason 0.5 doesn't work here
			if(op > (val - 0.051)) {
				element.style.opacity = parseFloat(val);

				if(callback != undefined)
					callback(element);
				return;
			}
			element.style.opacity = parseFloat(element.style.opacity) + 0.05;
			//console.error("increased opacity to: " + (parseFloat(element.style.opacity) + 0.05));
		}
		element.fade_var = setTimeout(function(){NBL.fade(element, val, callback);}, 10);
	};

	NBL.render_init = function () {
		this.frame = 0;
		this.canvas = document.getElementById("canvas");
		this.engine = new BABYLON.Engine(canvas, true);
		this.scene = (function () {
			var scene = new BABYLON.Scene(NBL.engine);
			scene.clearColor = new BABYLON.Color3(1, 1, 0.984);

			NBL.camera = new BABYLON.FreeCamera("Camera", new BABYLON.Vector3(0, 0, -7), scene);
			scene.activeCamera = NBL.camera;
			NBL.camera.attachControl(canvas, false);
			NBL.camera.keysUp.push(87); // W
			NBL.camera.keysLeft.push(65); // A
			NBL.camera.keysDown.push(83); // S
			NBL.camera.keysRight.push(68); // D
			NBL.camera.inertia = 0.6;
			NBL.camera.angularSensibility = 900;
			NBL.camera.maxCameraSpeed = 80;
			NBL.camera.cameraAcceleration = 0.1;

			NBL.light = new BABYLON.HemisphericLight("hemi", new BABYLON.Vector3(0, 1, 0), scene);
			NBL.light.groundColor = new BABYLON.Color3(1, 1, 0.984);

			return scene;
		})();
		this.engine.runRenderLoop(this.renderLoop);
	};

	NBL.loaddummyblock = function () {
			this.box = BABYLON.Mesh.CreateBox("mesh", 3, this.scene);
			var material = new BABYLON.StandardMaterial("std", this.scene);
			material.diffuseColor = new BABYLON.Color3(0.5, 0.5, 0.5);
			this.box.material = material;
	}

	NBL.renderLoop = function () {
		if(NBL.box) {
			NBL.box.position.y = 3*Math.sin(NBL.frame / 50);
		}
		NBL.frame++;
		NBL.scene.render();
	};

	NBL.resizeFunc = function () {
		overlay_info.style.top = (0.5*(overlay.offsetHeight - overlay_info.offsetHeight)) + "px";
		overlay_info.style.left = (0.5*(overlay.offsetWidth - overlay_info.offsetWidth)) + "px";
		NBL.engine.resize();
	};

	$.getJSON("res/brickdata.json", function (data) {
		NBL.brickData = data;
	});
})();
$(document).ready(NBL.javascript_init());
