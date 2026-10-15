const fs = require('fs');
const js = fs.readFileSync('index.js', 'utf8');

// 1. Extract entities
const entityRegex = /entities\.([A-Za-z0-9_]+)/g;
const entities = new Set();
let m;
while ((m = entityRegex.exec(js)) !== null) {
  entities.add(m[1]);
}
console.log('=== BASE44 ENTITIES (' + entities.size + ') ===');
console.log(Array.from(entities).sort());

// 2. Extract chunk map
const mapStart = js.indexOf('__vite__mapDeps');
const chunkRegex = /assets\/([A-Za-z0-9_\-]+\.js)/g;
const chunks = new Set();
while ((m = chunkRegex.exec(js)) !== null) {
  chunks.add(m[1]);
}
console.log('\n=== CHUNK FILES (' + chunks.size + ') ===');
console.log(Array.from(chunks).sort());

// 3. Extract all routes and page components
// Look for route definitions e.g. path: "/...", element: ...
const routeRegex = /path:\s*["']([^"']+)["']/g;
const routes = new Set();
while ((m = routeRegex.exec(js)) !== null) {
  routes.add(m[1]);
}
console.log('\n=== ROUTES (' + routes.size + ') ===');
console.log(Array.from(routes).sort());
