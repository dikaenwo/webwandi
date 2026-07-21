import fs from 'fs';
import path from 'path';

const file = 'resources/views/layouts/app.blade.php';

const colorMap = {
    '#4B2E2B': 'var(--c-dk)',
    '#6F4E37': 'var(--c-md)',
    '#D9B99B': 'var(--c-lt)',
    '#F5E6D3': 'var(--c-bg)',
    '#EDD9C2': 'var(--c-ac)',
    '#8B6347': 'var(--c-md-lt)'
};

const baseDir = 'c:/laragon/www/skena-coffe';
const filePath = path.join(baseDir, file);
if (fs.existsSync(filePath)) {
    let content = fs.readFileSync(filePath, 'utf8');
    for (const [hex, cssVar] of Object.entries(colorMap)) {
        const regex = new RegExp(hex, 'gi');
        content = content.replace(regex, cssVar);
    }
    fs.writeFileSync(filePath, content, 'utf8');
    console.log(`Processed: ${file}`);
}
