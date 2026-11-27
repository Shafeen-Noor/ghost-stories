const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const context = {window: {}};
vm.runInNewContext(fs.readFileSync('public/js/map-clusters.js', 'utf8'), context);
const cluster = context.window.groupMapPins;

test('retains every story when ten thousand points overlap', () => {
    const points = Array.from({length: 10000}, (_, id) => ({x: 100, y: 100, pin: {id}}));
    const result = cluster(points);
    assert.equal(result.length, 1);
    assert.equal(result[0].members.length, 10000);
    assert.equal(new Set(result[0].members.map(pin => pin.id)).size, 10000);
});
test('separates stories as projected distances increase with zoom', () => {
    const close = [{x: 100, y: 100, pin: {id: 1}}, {x: 120, y: 100, pin: {id: 2}}];
    assert.equal(cluster(close).length, 1);
    assert.equal(cluster(close.map(point => ({...point, x: point.x * 10}))).length, 2);
});
test('groups neighbours across grid boundaries and handles empty input', () => {
    assert.equal(cluster([{x: 55, y: 0}, {x: 57, y: 0}]).length, 1);
    assert.equal(cluster([]).length, 0);
});
