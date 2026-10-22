#!/usr/bin/env python3
"""Verify dist/ artifacts: content, exclusions, byte-identity with the committed
source, manifest and checksums.   python3 tests/static/package-test.py
"""
import hashlib
import json
import os
import re
import subprocess
import sys
import zipfile

REPO = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
DIST = os.path.join(REPO, 'dist')
failures = 0
passes = 0


def check(name, ok, detail=''):
    global failures, passes
    if ok:
        passes += 1
        print('  ✔ ' + name)
    else:
        failures += 1
        print('  ✖ ' + name + ('\n      ' + detail if detail else ''))


def sha_bytes(data):
    return hashlib.sha256(data).hexdigest()


manifest = json.load(open(os.path.join(DIST, 'manifest.json'), encoding='utf-8'))
sums = dict(reversed(line.split('  ')) for line in open(os.path.join(DIST, 'SHA256SUMS'), encoding='utf-8').read().splitlines())
check('manifest says not deployed to production', manifest['deployed_to_production'] is False)
check('manifest built from a clean working tree', manifest['working_tree_clean'] is True)
head = subprocess.check_output(['git', '-C', REPO, 'rev-parse', 'HEAD'], text=True).strip()
built_from = manifest['generated_from_commit']
check('artifacts built from a commit on this branch', subprocess.call(['git', '-C', REPO, 'merge-base', '--is-ancestor', built_from, head]) == 0)

FORBIDDEN = re.compile(r'/(tests|docs|dist|scripts|node_modules|vendor|\.git|\.github)/|/(AGENTS\.md|README\.md|CHANGELOG\.md|composer\.json|'
                       r'package\.json|phpcs[^/]*|\.env[^/]*|wp-config\.php|\.gitignore)$|\.(sh|py|mjs|cjs|sql|pem|key|log|png)$')
SECRET = re.compile(r'AKIA[0-9A-Z]{16}|-----BEGIN [A-Z ]*PRIVATE KEY|gh[pousr]_[A-Za-z0-9]{30,}|sk-[A-Za-z0-9]{32,}')
REQUIRED = {
    'theme': ['style.css', 'functions.php', 'header.php', 'footer.php', 'README.txt', 'theme.json', 'inc/navigation.php',
              'assets/css/language-switch.css', 'assets/js/assistant-seo-20261019.js', 'template-parts/menu.php'],
    'plugin': ['rentbikehanoi-native-vi.php', 'uninstall.php', 'readme.txt', 'config/business.php', 'config/pages.php',
               'includes/class-rbhvi-router.php', 'includes/class-rbhvi-admin.php', 'content/home.txt', 'content/faq-items.php'],
}

for artifact in manifest['artifacts']:
    path = os.path.join(DIST, artifact['file'])
    kind = artifact['type']
    print(artifact['file'])
    with open(path, 'rb') as handle:
        data = handle.read()
    check('checksum matches manifest and SHA256SUMS', sha_bytes(data) == artifact['sha256'] == sums.get(artifact['file']))
    archive = zipfile.ZipFile(path)
    names = archive.namelist()
    top = artifact['install_folder'] + '/'
    check('single top folder "%s"' % top, all(n.startswith(top) for n in names))
    for req in REQUIRED[kind]:
        check('contains ' + req, top + req in names)
    bad = [n for n in names if FORBIDDEN.search('/' + n)]
    check('no tests, docs, dev tooling, secrets files or screenshots', not bad, ', '.join(bad))
    if kind == 'theme':
        check('theme ZIP does not bundle the plugin', not any('/plugins/' in n for n in names))
    listed = {entry['path']: entry for entry in artifact['files']}
    check('manifest lists exactly the ZIP entries', sorted(listed) == sorted(names))
    mismatched, leaked = [], []
    for name in names:
        content = archive.read(name)
        source = listed[name]['source']
        committed = subprocess.check_output(['git', '-C', REPO, 'show', '%s:%s' % (built_from, source)])
        if content != committed or sha_bytes(content) != listed[name]['sha256']:
            mismatched.append(name)
        if SECRET.search(content.decode('utf-8', 'ignore')):
            leaked.append(name)
    check('every packaged file is byte-identical to the committed source', not mismatched, ', '.join(mismatched))
    check('no secret patterns inside packaged files', not leaked, ', '.join(leaked))
    php = [n for n in names if n.endswith('.php')]
    errors = []
    for name in php:
        proc = subprocess.run(['php', '-l'], input=archive.read(name), capture_output=True)
        if proc.returncode:
            errors.append(name)
    check('php -l passes for %d packaged PHP files' % len(php), not errors, ', '.join(errors))

print('\n%d passed, %d failed' % (passes, failures))
sys.exit(1 if failures else 0)
