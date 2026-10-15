const fs = require('fs');
const js = fs.readFileSync('mockData.js', 'utf8');

// Find all export statements or variable names
const exportRegex = /export\s*\{([^}]+)\}/g;
let m;
while ((m = exportRegex.exec(js)) !== null) {
  console.log('EXPORTS:', m[1]);
}

// Find variable definitions: const <name> = [ ... ]
const constRegex = /(?:const|let|var)\s+([A-Za-z0-9_]+)\s*=\s*(\[|\{)/g;
const varNames = [];
while ((m = constRegex.exec(js)) !== null) {
  varNames.push(m[1]);
}
console.log('DATA VARIABLES:', varNames);

// Let's print the first 3000 chars of mockData.js to understand data models
console.log('--- SAMPLE MOCKDATA ---');
console.log(js.substring(0, 3000));
