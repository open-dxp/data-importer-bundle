# Update Notes

## 1.1.0
* [ENHANCEMENT] The installer creates the table of the import queue. Before, the queue created it on its first use
* [BUGFIX] The operator `StaticText` appends its text to the value `0` as well. It treated `0` as an empty value
* [CHORE] Replace Codeception with Pest and `open-dxp/test-foundation`
* [CHORE] Require `open-dxp/opendxp` ^1.5

## Migration from `pimcore/data-importer-bundle` to `open-dxp/data-importer-bundle`
* Renamed composer package to `open-dxp/data-importer-bundle`
* Renamed top-level PHP namespace to `OpenDxp\Bundle\DataImporterBundle`
* Renamed top-level config node to `opendxp_data_importer`
