// Repository guardrails (no WordPress needed): syntax, no secrets, no external
// calls/resources, locked data only in config, no automatic deploy.
//   node tests/static/repo-check.mjs
import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

const REPO = path.resolve(path.dirname(new URL(import.meta.url).pathname), '../..');
const files = execFileSync('git', ['-C', REPO, 'ls-files', '--cached', '--others', '--exclude-standard'], { encoding: 'utf8' })
  .split('\n').filter(f => f && fs.existsSync(path.join(REPO, f)) && !f.startsWith('dist/'));
const read = f => fs.readFileSync(path.join(REPO, f), 'utf8');
const text = files.filter(f => /\.(php|js|cjs|mjs|css|json|txt|md|html|sh|py|xml|dist|yml)$/.test(f));
let failed = 0, passed = 0;
const check = (name, ok, detail = '') => { ok ? passed++ : failed++; console.log(`  ${ok ? '✔' : '✖'} ${name}${ok || !detail ? '' : `\n      ${detail}`}`); };

console.log('Syntax');
const php = files.filter(f => f.endsWith('.php'));
const phpErrors = php.filter(f => { try { execFileSync('php', ['-l', path.join(REPO, f)], { stdio: 'pipe' }); return false; } catch { return true; } });
check(`php -l on ${php.length} PHP files`, !phpErrors.length, phpErrors.join(', '));
const js = files.filter(f => /^assets\/js\/.*\.js$/.test(f) || /\.(mjs|cjs)$/.test(f));
const jsErrors = js.filter(f => { try { execFileSync('node', ['--check', path.join(REPO, f)], { stdio: 'pipe' }); return false; } catch { return true; } });
check(`node --check on ${js.length} JS files`, !jsErrors.length, jsErrors.join(', '));
for (const f of files.filter(f => f.endsWith('.json'))) { let ok = true; try { JSON.parse(read(f)); } catch { ok = false; } check(`valid JSON ${f}`, ok); }

console.log('Secrets');
const secretPatterns = [/AKIA[0-9A-Z]{16}/, /-----BEGIN [A-Z ]*PRIVATE KEY-----/, /gh[pousr]_[A-Za-z0-9]{30,}/, /xox[baprs]-[A-Za-z0-9-]{10,}/, /sk-[A-Za-z0-9]{32,}/,
  /AIza[0-9A-Za-z_-]{35}/, /(?:password|passwd|secret|api[_-]?key|auth[_-]?token)\s*[:=]\s*['"][^'"\s]{8,}['"]/i, /Authorization:\s*(?:Bearer|Basic)\s+[A-Za-z0-9+/=._-]{16,}/];
const leaks = text.flatMap(f => secretPatterns.filter(p => p.test(read(f))).map(p => `${f} ~ ${p}`));
check('no credentials, tokens or private keys in tracked files', !leaks.length, leaks.join('\n      '));
check('no wp-config.php, .env, SQL dump or key file tracked', !files.some(f => /(^|\/)(wp-config\.php|\.env(\..*)?)$|\.(sql|sql\.gz|pem|key)$/.test(f) && !f.endsWith('.env.example')));

console.log('Plugin: no external services or third-party front-end resources');
const plugin = files.filter(f => f.startsWith('plugins/rentbikehanoi-native-vi/'));
const pluginCode = plugin.filter(f => f.endsWith('.php')).map(f => [f, read(f)]);
const network = pluginCode.filter(([, s]) => /wp_remote_|wp_safe_remote_|curl_|fsockopen|stream_socket|file_get_contents\(\s*['"]https?:|download_url\(|WP_Http/.test(s)).map(([f]) => f);
check('no outbound HTTP API in plugin code', !network.length, network.join(', '));
const urls = pluginCode.flatMap(([f, s]) => [...s.matchAll(/https?:\/\/[^\s'"<>)]+/g)].map(m => `${f}: ${m[0]}`))
  .filter(u => !/(config\/business\.php: https:\/\/(zalo\.me|wa\.me|share\.google)\/|gnu\.org\/licenses)/.test(u));
check('only the shop contact links appear as URLs (in config/business.php)', !urls.length, urls.join('\n      '));
const enqueue = pluginCode.filter(([, s]) => /\b(wp_enqueue|wp_register)_(script|style)\s*\(/.test(s)).map(([f]) => f);
check('plugin enqueues no front-end assets (theme owns presentation)', !enqueue.length, enqueue.join(', '));
check('no translation/AI service, cookie or JS language switching in plugin', !pluginCode.some(([, s]) => /translate\.googleapis|deepl|openai|anthropic|setcookie\(|\$_COOKIE|document\.cookie/i.test(s)));
check('no IP/country/Accept-Language redirect', !pluginCode.some(([, s]) => /HTTP_ACCEPT_LANGUAGE|geoip|REMOTE_ADDR/i.test(s)));
const css = read('assets/css/language-switch.css');
check('language-switch.css loads nothing external', !/@import|url\(/i.test(css));

console.log('Locked business data lives in one place');
const literalNap = plugin.filter(f => !f.endsWith('config/business.php') && !f.endsWith('readme.txt'))
  .filter(f => /0334|334 699|334699969|Nguyễn Văn Cừ|\d{3}\.000đ|09:00–21:00/.test(read(f)));
check('no NAP/price literal outside plugins/rentbikehanoi-native-vi/config/business.php', !literalNap.length, literalNap.join(', '));

console.log('Release safety');
const workflowSteps = f => read(f).split('\n').filter(l => !l.trim().startsWith('#')).join('\n');
check('no automatic deploy workflow or webhook', !files.some(f => /^\.github\/workflows\//.test(f) && /ftp|ssh-action|ssh |scp |rsync|lftp|wp-json|deploy-action|rentbikehanoi\.com|secrets\./i.test(workflowSteps(f))));
check('no -wpvibe-draft theme activation in code', !text.some(f => !f.endsWith('.md') && f !== 'tests/static/repo-check.mjs' && /switch_theme\(|activate.*wpvibe-draft/.test(read(f))));
check('theme still has no metadata/schema output of its own', !['functions.php', 'header.php', 'inc/navigation.php', 'inc/seo.php'].some(f => /rel="canonical"|og:title|application\/ld\+json"\s*>/.test(read(f).replace(/~<script\[\^>\]\+application\/ld\\\+json/g, ''))));

console.log('English presentation unchanged since the deployed release');
// Theme 1.3.0 / plugin 1.0.0 were deployed from this commit (docs/vi/DEPLOYMENT-2026-10-23.md).
const DEPLOYED = process.env.RBH_DEPLOYED_REF || 'e81fbd7';
let deployedKnown = true;
try { execFileSync('git', ['-C', REPO, 'cat-file', '-e', DEPLOYED + '^{commit}'], { stdio: 'ignore' }); } catch { deployedKnown = false; }
if (deployedKnown) {
  const changed = execFileSync('git', ['-C', REPO, 'diff', '--name-only', DEPLOYED, '--', 'front-page.php', 'header.php', 'footer.php', 'index.php', 'page.php', 'single.php', '404.php',
    'searchform.php', 'sidebar.php', 'inc', 'assets', 'template-parts', 'theme.json'], { encoding: 'utf8' }).split('\n').filter(Boolean)
    .filter(f => f !== 'template-parts/landing.php');
  check(`English templates, CSS and JS identical to deployed ${DEPLOYED} (only the new landing part added)`, !changed.length, changed.join(', '));
} else {
  check(`deployed commit ${DEPLOYED} available for comparison`, false, 'fetch full history (git fetch --unshallow)');
}

console.log(`\n${passed} passed, ${failed} failed`);
if (failed) process.exitCode = 1;
