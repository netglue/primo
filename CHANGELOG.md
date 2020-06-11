# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 0.3.0 - TBD

### Added

- Nothing.

### Changed

- The document resolver middleware now adds a Last-Modified header to the response using the resolved document's last publication date.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.2.2 - 2020-06-10

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- The default link resolver is now listed in the container using `Prismic\LinkResolver` which is what most consumers will want…

## 0.2.1 - 2020-06-10

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#3](https://github.com/netglue/primo/pull/3) Fixes delegation of the Prismic specific http client by creating a factory for the client that can actually be delegated by consumers. Aliases don't trigger delegation in Laminas Service Manager which is a pain in the ass and something that is very likely we'll want to do.

## 0.2.0 - 2020-06-10

### Added

- [#2](https://github.com/netglue/primo/pull/2) Adds `Primo\Http\PrismicHttpClient` interface. This interface is just aliased to the PSR Http Client interface but the Api client factory targets the new interface in the container so that it is easier to change the client used specifically for the Prismic API. Typically, you'd want to wrap the standard client in a caching client…

### Changed

- [#2](https://github.com/netglue/primo/pull/2) Adds some suggest items to composer.json

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#2](https://github.com/netglue/primo/pull/2) Corrects the config provider class name in composer.json

## 0.1.0 - 2020-06-08

### Added

- Everything.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
