QUnit.test("Sanity Test", function(assert) {
	assert.ok(1 == "1", "Passed!" );
});

QUnit.test("Basic Mesh test", function(assert) {
	var mesh = NBL.buildMesh("1x1");
	assert.ok(mesh);
	assert.equal(36, mesh._geometry._indices.length);
});
