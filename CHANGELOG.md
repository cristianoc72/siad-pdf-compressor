# Changelog
All notable changes to `cristianoc72/siad-pdf-compressor` project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
