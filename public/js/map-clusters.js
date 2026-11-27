/* Group nearby screen positions without changing the stored coordinates. */
window.groupMapPins = (points, radius = 56) => {
    const cells = new Map();
    const groups = [];
    for (const point of points) {
        const column = Math.floor(point.x / radius);
        const row = Math.floor(point.y / radius);
        let target;
        for (let dx = -1; dx <= 1 && !target; dx++) {
            for (let dy = -1; dy <= 1 && !target; dy++) {
                for (const group of cells.get(`${column + dx}:${row + dy}`) || []) {
                    if (Math.hypot(point.x - group.anchorX, point.y - group.anchorY) <= radius) {
                        target = group;
                        break;
                    }
                }
            }
        }
        if (!target) {
            target = { anchorX: point.x, anchorY: point.y, x: 0, y: 0, members: [] };
            const key = `${column}:${row}`;
            if (!cells.has(key)) cells.set(key, []);
            cells.get(key).push(target);
            groups.push(target);
        }
        target.members.push(point.pin);
        target.x += point.x;
        target.y += point.y;
    }
    return groups.map(group => ({ x: group.x / group.members.length, y: group.y / group.members.length, members: group.members }));
};
