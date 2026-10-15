const fs = require('fs');
const js = fs.readFileSync('mockData.js', 'utf8');

// Find all const <letter> = [
const regex = /const\s+([a-zA-Z0-9_]+)\s*=\s*\[/g;
let m;
while ((m = regex.exec(js)) !== null) {
  const varName = m[1];
  const start = m.index;
  const sample = js.substring(start, start + 300);
  console.log(`\n=== Variable "${varName}" ===`);
  console.log(sample);
}
