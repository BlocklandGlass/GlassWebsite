var NBL = NBL || {};
(function ()
{
	//and unfortunate mix of camelCase and snake_case
	NBL.javascript_init = function ()
	{
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

		if(targetUrl === undefined)
		{
			this.loadDummyBlock();
			this.toggleMenu();
		}
		else
		{
			this.hidenavbar();

			$.get(targetUrl, function (data){
				NBL.loadBLSData(data);
			});
		}
	};

	NBL.handleFileSelect = function (evt)
	{
		var f = evt.target.files[0];
		var r = new FileReader();
		r.onload = function (e) {
			console.log("two");
			var contents = e.target.result;

			NBL.loadBLSData(contents);
		};
		//console.log("one");
		r.readAsText(f);
		//console.log("three");
	};

	NBL.loadBLSData = function (data)
	{
		var saveData = data.split("\n");

		//warning(1), descsize(1), desc(descsize), colortable(64), linecount(1)
		var line = parseInt(saveData[1]) + 2;
		var material = new Array(64);

		for(var i=0; i<64; i++)
		{
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

		for(; line<saveData.length; line++)
		{
			if(saveData[line].substr(0, 2) == "+-" || saveData[line] === "")
			{
				continue;
			}
			else
			{
				var quoteindex = saveData[line].indexOf("\"");

				if(quoteindex == -1)
				{
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

	NBL.buildMesh = function (uiname)
	{
		//var mesh = BABYLON.Mesh.CreateBox("mesh", 3, this.scene);

		if(this.brickData.hasOwnProperty(uiname))
		{
			var jsonobj = this.brickData[uiname];
			var mesh;

			//check if basic ramp
			if(uiname.indexOf("Ramp") != -1 && !jsonobj.n && !jsonobj.e && !jsonobj.w)
			{
				//console.log("Ramping up: " + uiname);
				mesh = new BABYLON.Mesh("mesh", this.scene);
				//console.log("part 1");
				var indicies = [];
				var positions = [];
				//var normals = [];
				//console.log("part 1.5");

				//bottom face
				positions.push(0.5, -0.5, 0.5); //0
				//normals.push(0.5, -0.5, 0.5);
				positions.push(0.5, -0.5, -0.5);
				//normals.push(0.5, -0.5, -0.5);
				positions.push(-0.5, -0.5, 0.5); //2
				//normals.push(-0.5, -0.5, 0.5);
				positions.push(-0.5, -0.5, -0.5);
				//normals.push(-0.5, -0.5, -0.5);
				indicies.push(0);indicies.push(1);indicies.push(2);
				indicies.push(3);indicies.push(2);indicies.push(1);
				//console.log("part 2");

				if(uiname.indexOf("Corner") != -1 && !jsonobj.s)
				{
					//corner piece
					//unfortunately a lot of duplication, I dug myself into this one

					//top face
					positions.push(-0.5 + 1/jsonobj.x, 0.5, -0.5 + 1/(jsonobj.y)); //4
					//normals.push(0.5, 0.5, -0.5 + 1/(jsonobj.y));
					positions.push(-0.5 + 1/jsonobj.x, 0.5, -0.5);
					//normals.push(0.5, 0.5, -0.5);
					positions.push(-0.5, 0.5, -0.5 + 1/(jsonobj.y)); //6
					//normals.push(-0.5, 0.5, -0.5 + 1/(jsonobj.y));
					positions.push(-0.5, 0.5, -0.5);
					//normals.push(-0.5, 0.5, -0.5);
					indicies.push(6);indicies.push(5);indicies.push(4);
					indicies.push(5);indicies.push(6);indicies.push(7);
					//console.log("part 3");

					////bevel on front
					positions.push(0.5, -0.4, 0.5); //8
					//normals.push(0.5, -0.4, 0.5);
					positions.push(-0.5, -0.4, 0.5); //9
					//normals.push(-0.5, -0.4, 0.5);
					positions.push(0.5, -0.4, -0.5); //might need to flip last sign
					indicies.push(0);indicies.push(9);indicies.push(8);
					indicies.push(0);indicies.push(2);indicies.push(9);
					indicies.push(10);indicies.push(1);indicies.push(0);
					indicies.push(8);indicies.push(10);indicies.push(0);
					//console.log("part 4");

					//slopes
					indicies.push(9);indicies.push(6);indicies.push(4);
					indicies.push(8);indicies.push(9);indicies.push(4);
					indicies.push(10);indicies.push(8);indicies.push(4);
					indicies.push(5);indicies.push(10);indicies.push(4);

					//sides
					indicies.push(3);indicies.push(7);indicies.push(6);
					indicies.push(2);indicies.push(3);indicies.push(6);
					indicies.push(2);indicies.push(6);indicies.push(9);
					indicies.push(7);indicies.push(3);indicies.push(5);
					indicies.push(3);indicies.push(1);indicies.push(5);
					indicies.push(5);indicies.push(1);indicies.push(10);
				}
				else
				{
					//top face
					positions.push(0.5, 0.5, -0.5 + 1/(jsonobj.y)); //4
					//normals.push(0.5, 0.5, -0.5 + 1/(jsonobj.y));
					positions.push(0.5, 0.5, -0.5);
					//normals.push(0.5, 0.5, -0.5);
					positions.push(-0.5, 0.5, -0.5 + 1/(jsonobj.y)); //6
					//normals.push(-0.5, 0.5, -0.5 + 1/(jsonobj.y));
					positions.push(-0.5, 0.5, -0.5);
					//normals.push(-0.5, 0.5, -0.5);
					indicies.push(6);indicies.push(5);indicies.push(4);
					indicies.push(5);indicies.push(6);indicies.push(7);
					//console.log("part 3");

					////bevel on front
					positions.push(0.5, -0.4, 0.5); //8
					//normals.push(0.5, -0.4, 0.5);
					positions.push(-0.5, -0.4, 0.5); //9
					//normals.push(-0.5, -0.4, 0.5);
					indicies.push(0);indicies.push(9);indicies.push(8);
					indicies.push(0);indicies.push(2);indicies.push(9);
					//console.log("part 4");

					//slope
					indicies.push(8);indicies.push(9);indicies.push(4);
					indicies.push(9);indicies.push(6);indicies.push(4);
					//console.log("part 5");

					//back
					indicies.push(5);indicies.push(7);indicies.push(1);
					indicies.push(7);indicies.push(3);indicies.push(1);
					//console.log("part 6");

					//sides
					indicies.push(1);indicies.push(4);indicies.push(5);
					indicies.push(3);indicies.push(7);indicies.push(6);
					indicies.push(0);indicies.push(4);indicies.push(1);
					indicies.push(2);indicies.push(3);indicies.push(6);
					indicies.push(0);indicies.push(8);indicies.push(4);
					indicies.push(2);indicies.push(6);indicies.push(9);
					//console.log("part 7");
				}
				mesh.setVerticesData(BABYLON.VertexBuffer.PositionKind, positions, false);
				mesh.setIndices(indicies);
				//mesh.setVerticesData(BABYLON.VertexBuffer.NormalKind, normals, true);
				//console.log("part 8");
			}
			else if(uiname.indexOf("Round") != -1 && !jsonobj.n && !jsonobj.e && !jsonobj.w && !jsonobj.s)
			{
				mesh = BABYLON.Mesh.CreateCylinder("cylinder", 1, 1, 1, 8, this.scene, false);
			}
			else
			{
				mesh = BABYLON.Mesh.CreateBox("mesh", 1, this.scene);
			}
			mesh.scaling.x = jsonobj.x / 2;
			mesh.scaling.y = jsonobj.z / 5;
			mesh.scaling.z = jsonobj.y / 2;
			return mesh;
		}
		else
		{
			console.log("missing datablock: " + uiname);
			return BABYLON.Mesh.CreateBox("mesh", 3, this.scene);
		}
	}

	NBL.onKeyDown = function (evt)
	{
		switch(evt.keyCode)
		{
			case 27:
				NBL.toggleMenu();
				break;
		}
	};

	NBL.hidenavbar = function ()
	{
		window.clearTimeout(this.navbar_tick);
		var element = document.getElementById("viewer_nav_container");

		if(element !== null) {
			if(element.tick_position > -64)
			{
				element.tick_position -= 2;
				element.style.top = (element.tick_position) + "px";
				this.navbar_tick = setTimeout(NBL.hidenavbar, 10);
			}
		}
	}

	NBL.shownavbar = function ()
	{
		window.clearTimeout(this.navbar_tick);
		var element = document.getElementById("viewer_nav_container");

		if(element !== null) {
			if(element.tick_position < 0)
			{
				element.tick_position += 2;
				element.style.top = (element.tick_position) + "px";
				this.navbar_tick = setTimeout(NBL.shownavbar, 10);
			}
		}
	}

	NBL.toggleMenu = function ()
	{
		var overlay = document.getElementById("overlay");

		if(overlay.style.display == "block")
		{
			this.pop_menu();
		}
		else
		{
			this.push_menu();
		}
	};

	NBL.push_menu = function ()
	{
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

	NBL.pop_menu = function ()
	{
		var overlay = document.getElementById("overlay");
		var overlay_info = document.getElementById("overlay_info");
		overlay.style.display = "none";
		overlay_info.style.display = "none";
		this.hidenavbar();
	};

	NBL.fade = function (element, val, callback)
	{
		window.clearTimeout(element.fade_var);
		var op = window.getComputedStyle(element).opacity;//element.style.opacity;

		if(op > val)
		{
			if(op < (val + 0.051))
			{
				element.style.opacity = parseFloat(val);

				if(callback != undefined)
					callback(element);
				return;
			}
			element.style.opacity = parseFloat(element.style.opacity) - 0.05;
			//console.error("decreased opacity to: " + (parseFloat(element.style.opacity) - 0.05));
		}
		else
		{
			if(op > (val - 0.051)) //for some reason 0.5 doesn't work here
			{
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

	NBL.render_init = function ()
	{
		this.frame = 0;
		this.canvas = document.getElementById("canvas");
		this.engine = new BABYLON.Engine(canvas, true);
		this.scene = (function ()
		{
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

	NBL.loaddummyblock = function ()
	{
			this.box = BABYLON.Mesh.CreateBox("mesh", 3, this.scene);
			var material = new BABYLON.StandardMaterial("std", this.scene);
			material.diffuseColor = new BABYLON.Color3(0.5, 0.5, 0.5);
			this.box.material = material;
	}

	NBL.renderLoop = function ()
	{
		if(NBL.box)
		{
			NBL.box.position.y = 3*Math.sin(NBL.frame / 50);
		}
		NBL.frame++;
		NBL.scene.render();
	};

	NBL.resizeFunc = function ()
	{
		overlay_info.style.top = (0.5*(overlay.offsetHeight - overlay_info.offsetHeight)) + "px";
		overlay_info.style.left = (0.5*(overlay.offsetWidth - overlay_info.offsetWidth)) + "px";
		NBL.engine.resize();
	};

	$.getJSON("res/brickdata.json", function (data) {
		NBL.brickData = data;
	});
})();
