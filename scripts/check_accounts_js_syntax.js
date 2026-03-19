const fs = require('fs');
const path = 'c:\\laragon\\www\\logistic\\resources\\views\\accounts\\account_page.blade.php';
const src = fs.readFileSync(path, 'utf8');
const m = src.match(/@push\('scripts'\)([\s\S]*)@endpush/);
if(!m){ console.error('No @push scripts block found'); process.exit(2); }
let block = m[1];
// remove <script> and </script> wrappers
block = block.replace(/<\/script>/g,'\n');
block = block.replace(/<script[\s\S]*?>/g,'\n');
// strip Blade directives to avoid PHP tokens interfering
block = block.replace(/{{[\s\S]*?}}/g,'');
block = block.replace(/@[^\n\r]*/g,'');
// attempt to parse by creating a new Function
try{
    new Function(block);
    console.log('OK: JS parsed without syntax errors');
}catch(e){
    console.error('PARSE ERROR:', e && e.message);
    console.error(e && e.stack);
    process.exit(3);
}
