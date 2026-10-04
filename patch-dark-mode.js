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
    // Dashboard old classes
    { regex: /dark:bg-\[#3D3530\](\/\d+)?/g, replace: 'dark:bg-[#272422]' },
    { regex: /dark:hover:bg-\[#4A3F38\]/g, replace: 'dark:hover:bg-[#363230]' },
    
    // Add dark modes to whites
    { regex: /bg-white\/90(?! dark:)/g, replace: 'bg-white/90 dark:bg-[#272422]/90 dark:border-stone-700/40' },
    { regex: /bg-white\/80(?! dark:)/g, replace: 'bg-white/80 dark:bg-[#272422]/80 dark:border-stone-700/40' },
    { regex: /bg-white\/50(?! dark:)/g, replace: 'bg-white/50 dark:bg-[#1A1817]/80' },
    
    // Add dark modes to slates
    { regex: /bg-slate-50(?! dark:)/g, replace: 'bg-slate-50 dark:bg-[#1A1817] dark:text-stone-300' },
    { regex: /border-slate-100(?! dark:)/g, replace: 'border-slate-100 dark:border-stone-700/50' },
    { regex: /border-slate-200(?! dark:)/g, replace: 'border-slate-200 dark:border-stone-700/50' },
    { regex: /border-white\/60(?! dark:)/g, replace: 'border-white/60 dark:border-stone-700/40' },
    { regex: /text-slate-800(?! dark:)/g, replace: 'text-slate-800 dark:text-stone-100' },
    { regex: /text-slate-700(?! dark:)/g, replace: 'text-slate-700 dark:text-stone-200' },
    { regex: /text-slate-600(?! dark:)/g, replace: 'text-slate-600 dark:text-stone-300' },
    { regex: /text-slate-500(?! dark:)/g, replace: 'text-slate-500 dark:text-stone-400' },
    { regex: /text-slate-400(?! dark:)/g, replace: 'text-slate-400 dark:text-stone-500' }
];

let updatedCount = 0;

files.forEach(file => {
    // Skip app layout as we manually perfected it
    if (file.includes('layouts\\app.blade.php') || file.includes('layouts/app.blade.php')) return;
    
    let content = fs.readFileSync(file, 'utf8');
    let original = content;

    replacements.forEach(rule => {
        content = content.replace(rule.regex, rule.replace);
    });

    if (content !== original) {
        fs.writeFileSync(file, content, 'utf8');
        console.log('Patched: ' + file);
        updatedCount++;
    }
});

console.log(`Total files patched: ${updatedCount}`);
