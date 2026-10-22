#!/usr/bin/env python3
"""Build reproducible release artifacts into dist/.

  python3 scripts/package.py

Produces:
  dist/wp-theme-<version>.zip                 theme (top folder "wp-theme" = active stylesheet)
  dist/rentbikehanoi-native-vi-<version>.zip  plugin (top folder "rentbikehanoi-native-vi")
  dist/manifest.json                      every packaged file with its SHA-256
  dist/SHA256SUMS                         checksums of the ZIP files

Only files tracked by git are packaged, with fixed timestamps and sorted order,
so the same commit always gives byte-identical ZIPs. Nothing is uploaded.
"""
import hashlib
import json
import os
import re
import subprocess
import sys
import zipfile

REPO = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DIST = os.path.join(REPO, 'dist')
STAMP = (2026, 10, 22, 0, 0, 0)

# Development-only paths that never ship in the theme ZIP.
THEME_EXCLUDE = re.compile(r'^(plugins/|tests/|docs/|dist/|scripts/|\.github/|node_modules/|vendor/)|'
                           r'^(AGENTS\.md|README\.md|CHANGELOG\.md|\.gitignore|\.gitattributes|composer\.(json|lock)|'
                           r'package(-lock)?\.json|phpcs[^/]*\.xml(\.dist)?)$')
PLUGIN_DIR = 'plugins/rentbikehanoi-native-vi/'


def tracked():
    out = subprocess.check_output(['git', '-C', REPO, 'ls-files'], text=True)
    return sorted(f for f in out.splitlines() if f and os.path.isfile(os.path.join(REPO, f)))


def header_version(path, key):
    with open(os.path.join(REPO, path), encoding='utf-8') as handle:
        match = re.search(r'^\s*\*?\s*' + key + r':\s*([0-9.]+)', handle.read(), re.M)
    if not match:
        sys.exit('Version not found in ' + path)
    return match.group(1)


def sha(path):
    digest = hashlib.sha256()
    with open(path, 'rb') as handle:
        for chunk in iter(lambda: handle.read(65536), b''):
            digest.update(chunk)
    return digest.hexdigest()


def build(name, top, files, strip=''):
    target = os.path.join(DIST, name)
    entries = []
    with zipfile.ZipFile(target, 'w', zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
        for rel in files:
            inner = top + '/' + rel[len(strip):]
            info = zipfile.ZipInfo(inner, STAMP)
            info.external_attr = 0o644 << 16
            info.compress_type = zipfile.ZIP_DEFLATED
            with open(os.path.join(REPO, rel), 'rb') as handle:
                data = handle.read()
            archive.writestr(info, data)
            entries.append({'path': inner, 'source': rel, 'sha256': hashlib.sha256(data).hexdigest(), 'bytes': len(data)})
    return target, entries


def main():
    os.makedirs(DIST, exist_ok=True)
    files = tracked()
    theme_version = header_version('style.css', 'Version')
    plugin_version = header_version(PLUGIN_DIR + 'rentbikehanoi-native-vi.php', 'Version')
    theme_files = [f for f in files if not THEME_EXCLUDE.search(f)]
    plugin_files = [f for f in files if f.startswith(PLUGIN_DIR)]
    theme_zip, theme_entries = build('wp-theme-%s.zip' % theme_version, 'wp-theme', theme_files)
    plugin_zip, plugin_entries = build('rentbikehanoi-native-vi-%s.zip' % plugin_version, 'rentbikehanoi-native-vi', plugin_files, PLUGIN_DIR)
    commit = subprocess.check_output(['git', '-C', REPO, 'rev-parse', 'HEAD'], text=True).strip()
    dirty = bool(subprocess.check_output(['git', '-C', REPO, 'status', '--porcelain', '--untracked-files=no'], text=True).strip())
    manifest = {
        'generated_from_commit': commit,
        'working_tree_clean': not dirty,
        'deployed_to_production': False,
        'note': 'Built locally from git-tracked files. Not installed, activated or published on any WordPress site.',
        'artifacts': [
            {'file': os.path.basename(theme_zip), 'type': 'theme', 'install_folder': 'wp-theme', 'version': theme_version,
             'sha256': sha(theme_zip), 'files': theme_entries},
            {'file': os.path.basename(plugin_zip), 'type': 'plugin', 'install_folder': 'rentbikehanoi-native-vi', 'version': plugin_version,
             'sha256': sha(plugin_zip), 'files': plugin_entries},
        ],
    }
    with open(os.path.join(DIST, 'manifest.json'), 'w', encoding='utf-8') as handle:
        json.dump(manifest, handle, indent=2, ensure_ascii=False)
        handle.write('\n')
    with open(os.path.join(DIST, 'SHA256SUMS'), 'w', encoding='utf-8') as handle:
        for zip_path in (theme_zip, plugin_zip):
            handle.write('%s  %s\n' % (sha(zip_path), os.path.basename(zip_path)))
    for zip_path in (theme_zip, plugin_zip):
        print('%s  %s' % (sha(zip_path), os.path.relpath(zip_path, REPO)))
    if dirty:
        print('WARNING: uncommitted changes to tracked files are not reflected in the ZIPs.', file=sys.stderr)


if __name__ == '__main__':
    main()
