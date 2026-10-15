const fs = require('fs');
const https = require('https');

const chunksToFetch = [
  'Radius-KQrJphfA.js',
  'APIGateways-DW_y1dh3.js',
  'WhatsApp-DA5M-xqE.js',
  'SMSGateways-G_Ng27tn.js',
  'Reminders-BM0r4aZM.js',
  'RoutingConfig-CAvT0u69.js',
  'RouterDetection-B6JfmQ5e.js',
  'CRM-BcTkmldv.js',
  'Inventory-DcaXuZ3O.js',
  'AdvancedReports-DOnsKKJ9.js',
  'PredictiveMaintenance-DjfFAABu.js'
];

async function download(file) {
  return new Promise((resolve, reject) => {
    const url = `https://smartohtechnologies.base44.app/assets/${file}`;
    https.get(url, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        fs.writeFileSync(file, data);
        console.log(`Downloaded ${file} (${data.length} bytes)`);
        resolve(data);
      });
    }).on('error', reject);
  });
}

(async () => {
  for (const c of chunksToFetch) {
    try {
      const data = await download(c);
      // Find titles and headings
      const headings = (data.match(/children:\s*["']([^"']{3,50})["']/g) || []).slice(0, 10);
      console.log(`[${c}] Headings:`, headings.map(h => h.replace(/children:\s*["']/, '').replace(/["']$/, '')));
    } catch (e) {
      console.error(`Error downloading ${c}:`, e.message);
    }
  }
})();
