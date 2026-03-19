const fs = require('fs');
const path = 'c:\\laragon\\www\\logistic\\resources\\views\\accounts\\account_page.blade.php';
const src = fs.readFileSync(path, 'utf8');
const m = src.match(/@push\('scripts'\)([\s\S]*)@endpush/);
if(!m){ console.error('No @push scripts block found'); process.exit(2); }
const scriptsBlock = m[1];
// extract only contents of <script>...</script>
const re = /<script[^>]*>([\s\S]*?)<\/script>/gi;
let match;
let parts = [];
while((match = re.exec(scriptsBlock)) !== null){
    parts.push(match[1]);
}
if(parts.length === 0){ console.error('No <script> tags found in @push scripts block'); process.exit(2); }
let block = parts.join('\n\n');
// strip Blade-only directives that would break JS parsing
block = block.replace(/{{[\s\S]*?}}/g,'');
block = block.replace(/@[^\n\r]*/g,'');
// diagnostics: check backtick balance
const backticks = (block.match(/`/g) || []).length;
console.log('Backticks count:', backticks);
if(backticks % 2 !== 0){
    console.error('Odd number of backticks — likely unterminated template literal.');
    const lastIndex = block.lastIndexOf('`');
    console.error('Snippet around last backtick:\n', block.slice(Math.max(0,lastIndex-200), lastIndex+200));
}
// incremental parse to find offending script part
let cum = '';
for(let i=0;i<parts.length;i++){
    let p = parts[i];
    // strip blade directives from this part too
    p = p.replace(/{{[\s\S]*?}}/g,'').replace(/@[^\n\r]*/g,'');
    // diagnostic: if this is part 2, dump char codes around 'textContent'
    if(i===2){
        const idx = p.indexOf('textContent');
        if(idx!==-1){
            console.log('--- char codes around textContent in part 2 ---');
            const snippet = p.slice(Math.max(0,idx-20), idx+20);
            console.log(snippet);
            console.log(Array.from(snippet).map(c=>c.charCodeAt(0)).join(' '));
        }
    }
    if(i===2){
        console.log('part[2] length:', p.length);
        console.log('part[2] tail:', p.slice(-200));
    }
    cum += '\n\n' + p;
    try{
        new Function(cum);
    }catch(err){
        console.error('Parse error detected when adding script part', i, 'message:', err.message);
        console.error('--- offending part snippet ---');
        console.error(p.slice(0,800));
        // continue to standalone testing instead of exiting here
        break;
    }
}

// write part[2] to disk for inspection
try{
    const fs = require('fs');
    fs.writeFileSync('scripts/part2_extracted.js', parts[2] || '', 'utf8');
    console.log('Wrote scripts/part2_extracted.js for inspection');
}catch(e){ console.error('Failed to write part2 file', e.message); }

// also test each part standalone
for(let i=0;i<parts.length;i++){
    let p = parts[i].replace(/{{[\s\S]*?}}/g,'').replace(/@[^\n\r]*/g,'');
    try{ new Function(p); }catch(e){ console.error('Standalone parse error in part', i, e.message); console.error(p.slice(0,400)); break; }
}
// attempt to parse by creating a new Function
try{
    new Function(block);
    console.log('OK: JS parsed without syntax errors');
}catch(e){
    console.error('PARSE ERROR:', e && e.message);
    // try to print a substring around the likely error by naive check: find last backtick
    console.error('--- snippet tail ---');
    console.error(block.slice(-600));
    process.exit(3);
}
