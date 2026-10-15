const fs = require('fs');
const js = fs.readFileSync('index.js', 'utf8');

// Look for 'NetFlow Sentinel' or logo or sidebar component
let idx = 0;
while ((idx = js.indexOf('NetFlow Sentinel', idx)) !== -1) {
  console.log(`--- NetFlow Sentinel at ${idx} ---`);
  console.log(js.substring(Math.max(0, idx - 200), idx + 400));
  idx += 16;
}

// Search for theme colors: Blue, Emerald, Violet, Rose, Amber, Cyan
const themeIdx = js.indexOf('Emerald');
if (themeIdx !== -1) {
  console.log('--- THEME CONTEXT ---');
  console.log(js.substring(themeIdx - 150, themeIdx + 350));
}

// Find header or topbar or sidebar JSX
const sidebarIdx = js.indexOf('ux({pathname');
if (sidebarIdx !== -1) {
  console.log('--- SIDEBAR COMPONENT ---');
  console.log(js.substring(sidebarIdx - 100, sidebarIdx + 1200));
}
