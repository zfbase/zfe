# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

ZFE (`zfbase/zfe` on Composer, `zfe` on npm) is a framework library for building editorial/admin CRUD interfaces. It is **not a runnable application**: it is installed into a host app under `vendor/zfbase/zfe` and relies on that app's configuration, database, and a few app-defined base classes. Stack: Zend Framework 1 (`zfbase/zf1-future`), Doctrine 1 ORM (`zfbase/doctrine1`), Bootstrap 3, jQuery, optional Sphinx full-text search. It runs on PHP 7.4–8.x. Comments, docblocks, and UI strings are in Russian, so keep new ones in Russian too.

The repository ships two packages:
- **PHP** (Composer): `library/`, `resources/`, `bin/`. `src/` and `tests/` are excluded from the Composer archive.
- **JS/SCSS** (npm): `src/`, with entry point `src/js/zfe.js` and typings in `src/types.d.ts`. The host app bundles it with its own webpack. There is no build step in this repo.

Branches are per minor version (`1.27` … `1.36`), with `master` as the main branch. Recent commits use conventional-commit prefixes (`feat:`, `fix:`).

## Commands

```bash
composer lint          # php-cs-fixer dry run with diff (config in .php_cs; v2-style Config::create())
composer fix           # apply php-cs-fixer
npm run lint           # eslint src
```

php-cs-fixer and phpunit are not Composer dependencies, so they must be installed globally. The test bootstrap requires `../vendor/autoload.php` relative to the working directory, so run the tests from `tests/`:

```bash
cd tests && phpunit                               # full suite (tests/phpunit.xml)
cd tests && phpunit ZFE/AclTest.php               # single file
cd tests && phpunit --filter testMethodName ZFE/AclTest.php
```

Test coverage is minimal (ACL and Debug only). Most of the code needs a bootstrapped host application and database.

Every PHP file, `.phtml` included, must start with the header comment enforced by `.php_cs`:
```php
/*
 * ZFE – платформа для построения редакторских интерфейсов.
 */
```

## Architecture

### Library ↔ host application contract
Classes use PSR-0 autoloading (`ZFE_` → `library/`). There are no namespaces; class names use ZF1 underscore style. Several library classes **extend classes the host app must define**, and `assets/mocks/Application/` shows the minimal versions:
- `ZFE_Controller_AbstractResource extends Controller_Abstract`, where the app's `Controller_Abstract extends ZFE_Controller_Abstract`
- `ZFE_Controller_Default_*` extend `Controller_AbstractResource`, which comes from the app
- Default models (`Editors`, `History`, `Tasks`, `Files`) are app classes extending `ZFE_Model_Default_*`. Library code refers to them by their bare names (`Editors::find(...)`, `History::$globalRealtimeWhiteHistory`).
- The app defines the constants `APPLICATION_ENV`, `APPLICATION_PATH`, `ZFE_PATH`, `GENERAL_CONFIG`, and others (see `assets/mocks/constants.php`). `bin/*` scripts load `constants.php` from the app root.

`assets/schema/` holds reference schemas (SQL/YAML) for the default tables.

### Bootstrap and config
`ZFE_Bootstrap` (ZF1 `_init*` resources) builds the config. It merges `application.ini` with the optional `configs/{doctrine,acl,menu,forms,ckeditor,sphinx}.ini`, then `configs/local.ini`, and stores the result in `Zend_Registry`. It also sets up Doctrine: custom `ZFE_Query`, `ZFE_Model_Table`, and `ZFE_Model_Collection` classes, quoted identifiers, DQL callbacks, and `ONLY_FULL_GROUP_BY` disabled on MySQL. It resolves the current user into `Zend_Registry::get('user')`; in CLI the user comes from `cli.userId`/`cli.userLogin`. Finally it sets up ACL, views, and layouts. The global helpers `config('dot.path', $default)` and `env()` come from `library/global.php` and are used everywhere to read config.

### Resource controllers (the core CRUD pattern)
`ZFE_Controller_AbstractResource` provides standard actions for one model: index/search, edit, delete/undelete, history, merge, search-duplicates, view, and autocomplete. The actions are split across the traits in `Controller/AbstractResource/*`. A concrete controller is mostly static configuration:
- `$_modelName`, `$_searchFormName`, `$_searchAdvancedFormName`, `$_searcherName`, `$_editFormName`
- `$_enableActions`, `$_readonly`, `$_canCreate`/`$_canDelete`/`$_canMerge`/`$_canRestore`, `$_enableViewAction`

Permissions combine these flags, model capabilities (`isRemovable()`, `isMergeable()`), and ACL (`isAllowedMe($resource, $privilege)`, where the resource is `Model::getControllerName()`).

View scripts resolve through a fallback chain: the app's own view, then ZFE's `resources/scripts/<controller>/`, then `resources/scripts/_abstract/<action>.phtml`. See the `AbstractView` action helper and the `abstractRender`/`abstractPartial` view helpers. Add generic CRUD UI to `_abstract/`.

### Models
`ZFE_Model_AbstractRecord extends Doctrine_Record` and is assembled from traits in `Model/AbstractRecord/*`: URLs, files, getters, hot selects, autocomplete, multi-check, service fields, history-hidden fields, duplicates, Sphinx, and declension. Models declare metadata as statics, such as `$nameSingular`, `$namePlural`, and `$saveHistory`. Doctrine templates in `Model/Template/` add behavior:
- `History`: writes change history plus created/updated by/at, version, and deleted flag. Each of these columns is optional.
- `SoftDelete`
- `BaseZfeFields`

`ZFE_Model_Merge` holds the duplicate-merge logic.

### Search
`ZFE_Searcher_*` (Default/Doctrine/Sphinx) plus `Searcher/QueryBuilder/*` turn search form params into queries. The alternative Sphinx path goes through `ZFE_Controller_AbstractResourceSphinx`, `ZFE_Sphinx*`, and `zfbase/sphinxql-query-builder`.

### Forms
`ZFE_Form` extends Bootstrap-3 ZF1 forms. `ZFE_Form_Edit_AutoGeneration` builds edit forms from model columns. Custom elements (autocomplete, multi-autocomplete, datelist, duration, files) pair with view helpers in `View/Helper/Form*` and JS components in `src/components/`.

### Console and background tasks
- `bin/zfe-tools <command> [args]` runs through `ZFE_Console_Tools` and `ZFE_Console_CommandBroker`. Commands live in `ZFE_Console_Command_*`, and the app can register more via prefix paths. Built-in commands are the classes in `library/ZFE/Console/Command/` (Help, Config, Migrate, Models, ApplySchema, DoctrineCli, SphinxIndexer, UserAdd, Task*).
- `bin/zfe-manage-tasks [--part-size N] [--trait i/n] [performerCodes...]` is an endless worker loop over the `Tasks` table. Performers subclass `ZFE_Tasks_Performer` and are registered in config (`tasks.performers[] = "Class"`). A performer's code is the last PSR-0 segment of its class name. Throw `ZFE_Tasks_Performer_Exception_Permanent` for failures that must not be retried.

### Frontend (`src/`)
`src/js/zfe.js` exports a `ZFE` object with a list of `init*` methods run per page, plus controller/action matching. Components under `src/components/*` are jQuery plugins with SCSS. File upload uses React (`initZfeFileElement.jsx`, `zfe-files`). jQuery and React are peer dependencies.
