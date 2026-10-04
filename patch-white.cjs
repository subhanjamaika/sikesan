const fs = require('fs');
const path = require('path');

function walk(dir) {
    let results = [];
    const list = fs.readdirSync(dir);
    list.forEach(function(file) {
        file = path.join(dir, file);
        const stat = fs.statSync(file);
        if (stat && stat.isDirectory()) { 
            results = results.concat(walk(file));
        } else { 
            if(file.endsWith('.blade.php')) results.push(file);
        }
    });
    return results;
}

const viewsDir = path.join(__dirname, 'resources', 'views');
const files = walk(viewsDir);

const replacements = [
    // Patch raw bg-white that was missed previously
    { 
        // match bg-white not followed by / or dark:
        regex: /bg-white(?!\/| dark:)/g, 
        replace: 'bg-white dark:bg-[#272422]' 
    },
    // Also patch divide-slate-100 which makes table row dividers invisible in dark mode
    {
        regex: /divide-slate-100(?! dark:)/g,
        replace: 'divide-slate-100 dark:divide-stone-700/40'
    }
];

let updatedCount = 0;

files.forEach(file => {
    // Skip app layout
    if (file.includes('layouts\\app.blade.php') || file.includes('layouts/app.blade.php')) return;
    
    let content = fs.readFileSync(file, 'utf8');
    let original = content;

    replacements.forEach(rule => {
        content = content.replace(rule.regex, rule.replace);
    });

    if (content !== original) {
        fs.writeFileSync(file, content, 'utf8');
        console.log('Patched bg-white: ' + file);
        updatedCount++;
    }
});

console.log(`Total white-bg files patched: ${updatedCount}`);
