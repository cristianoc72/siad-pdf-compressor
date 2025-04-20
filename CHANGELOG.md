# Changelog
All notable changes to `cristianoc72/siad-pdf-compressor` project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0] - unrealased
### Added
Add `excludes` options, to exclude some sub-directories from the document search path.

### Changed
- Move the configuration file format to YAML, via `symfony/yaml` component.
- Bump the minum PHP version to 8.4
- Move the quality checkerfrom [Codeclimate(https://codeclimate.com/)] to [Qlty](https://qlty.sh/).

## [1.3] - 2025-02-21
### Added
Add the possibility to revert only documents in given directories.

### Changed
Init command now have default values.

## [1.2.1] - 2025-02-17
### Changed
Fix backup step: make the document backup before all the processes.

## [1.2] - 2025-02-17
### Added
Add ConformitaFirmata documents.

## [1.1.1] - 2024-07-11
### Changed
Fix version number and add a reminder composer script, for future versions.

## [1.1] - 2024-07-11
### Changed
- Fix issue #1: move the size limit of compressed files to 290kb. This avoid to re-compress some multiple pages file.
- Fix issue #2: add a copy of compressed file, named {subdir_name}.PDF. It can speed up the creation of pre-invoices.
- Fix issue #3: fix Psalm issues.

## [1.0] - 2024-07-05
### Added
- Add a GitHub actions workflow, to automatically build the phar archive and deploy it in release assets.
- Add the version number, to display at command line, when command is run with `--version` option.
- Add a copy of compressed file, named '{folder}.PDF' by default, for pre-invoices
- Add the possibility to customize the file name of pre-invoices

### Changed
- Fix Psalm issues.
- Decrease technical debt via CodeClimate suggestions
- Migrate test suite from Phpunit to Pest

## [0.2] - 2022-04-07
### Changed
Refactor the naming strategy of backup files.
Before this release, DFSPA software (to generate Italian invoices) includes also backup files as invoice attachment.
This release fixes that behavior, by naming backup files in _snake case_, i.e.:

|               |File name|Backup file name|
|----------|----------|-----------------|
|__Previous__|PraticaCollaudata_1.PDF|Original_PraticaCollaudata_1.PDF|
|__Current__|PraticaCollaudata_1.PDF|Original_pratica_collaudata_1.PDF|

## [0.1] - 2022-04-04
First release: fully functional application.
