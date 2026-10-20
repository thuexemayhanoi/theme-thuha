const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(__dirname+'/../assets/js/assistant-seo-20261019.js','utf8');
const prefix = source.slice(0,source.indexOf('  function append('));
let requests = [], fail = false;
const element = {querySelector:()=>({}),textContent:''};
const context = {URL, AbortSignal, location:{origin:'https://rentbikehanoi.com'},
 document:{getElementById:()=>element,querySelector:selector=>selector.includes('api.w.org')?{href:'https://rentbikehanoi.com/wp-json/'}:null},
 fetch: async url=>{requests.push(String(url));if(fail)throw Error('offline');return {ok:true,json:async()=>[
  {title:'A rental scooter tool kit',url:'https://rentbikehanoi.com/tool-kit/'},
  {title:'External tool kit',url:'https://example.com/tool-kit/'}
 ]};}
};
vm.createContext(context);
vm.runInContext(prefix+'globalThis.answer=answer;})();',context);
(async()=>{
 let r=await context.answer('extension fee');assert.match(r.text,/20,000/);assert.match(r.text,/more than 6 hours/);assert.equal(requests.length,0);
 r=await context.answer('rental prices');assert.match(r.text,/reference prices/i);assert.equal(requests.length,0);
 r=await context.answer('person@example.com');assert.match(r.text,/personal details/);assert.equal(requests.length,0);
 r=await context.answer('tool kit');assert.equal(r.sources[0].url,'https://rentbikehanoi.com/tool-kit/');
 const url=new URL(requests[0]);assert.equal(url.pathname,'/wp-json/wp/v2/search');assert.equal(url.searchParams.get('per_page'),'12');assert.equal(url.searchParams.get('search'),'tool kit');
 await context.answer('tool kit');assert.equal(requests.length,1);
 fail=true;r=await context.answer('rain suit');assert.match(r.text,/temporarily unavailable/);
 fail=false;r=await context.answer('rain suit');assert.equal(r.sources[0].url,'https://rentbikehanoi.com/tool-kit/');
 console.log('PASS: fee boundary, local answers, privacy, bounded search, same-origin sources, cache, failure and retry');
})().catch(e=>{console.error(e);process.exitCode=1;});
