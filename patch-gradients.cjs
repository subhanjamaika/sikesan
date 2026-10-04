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
    // Patch light gradients on table headers and filter bars
    { 
        regex: /bg-gradient-to-r from-violet-50(\/\d+)? to-teal-50(\/\d+)?(?! dark:from-)/g, 
        replace: 'bg-gradient-to-r from-violet-50$1 to-teal-50$2 dark:from-[#272422] dark:to-[#272422] dark:border-stone-700/40' 
    },
    { 
        regex: /bg-gradient-to-r from-amber-50(\/\d+)? to-orange-50(\/\d+)?(?! dark:from-)/g, 
        replace: 'bg-gradient-to-r from-amber-50$1 to-orange-50$2 dark:from-[#272422] dark:to-[#272422] dark:border-stone-700/40' 
    },
    // Also patch border-violet-100/60
    {
        regex: /border-violet-100(\/\d+)?(?! dark:border-)/g,
        replace: 'border-violet-100$1 dark:border-stone-700/40'
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
        console.log('Patched gradients: ' + file);
        updatedCount++;
    }
});

console.log(`Total gradient files patched: ${updatedCount}`);
