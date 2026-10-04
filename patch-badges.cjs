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
    // NIS background patch
    { 
        regex: /bg-slate-100 px-2 py-0.5 rounded/g, 
        replace: 'bg-slate-100 dark:bg-[#1A1817] px-2 py-0.5 rounded' 
    },
    // Inline badges to semantic badges
    { 
        regex: /inline-flex items-center gap-1 text-xs font-medium text-indigo-700 bg-indigo-50 px-2\.5 py-1 rounded-lg border border-indigo-100/g, 
        replace: 'badge badge-indigo flex-inline items-center gap-1' 
    },
    { 
        regex: /inline-flex items-center gap-1 text-xs font-medium text-teal-700 bg-teal-50 px-2\.5 py-1 rounded-lg border border-teal-100/g, 
        replace: 'badge badge-teal flex-inline items-center gap-1' 
    }
];

let updatedCount = 0;

files.forEach(file => {
    let content = fs.readFileSync(file, 'utf8');
    let original = content;

    replacements.forEach(rule => {
        content = content.replace(rule.regex, rule.replace);
    });

    if (content !== original) {
        fs.writeFileSync(file, content, 'utf8');
        console.log('Patched badges: ' + file);
        updatedCount++;
    }
});

console.log(`Total badge files patched: ${updatedCount}`);
