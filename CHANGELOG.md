# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 0.3.0 - TBD

### Added

- Added middleware that sets a Cache-Control header in the response to 'no-cache' when preview mode is active.
- Added middleware that will expire the preview cookie when the request has an attribute for `\Prismic\Exception\PreviewTokenExpired`. This optional middleware should be placed after the preview handler to kill dead cookies and redirect when a preview token has expired.

### Changed

- The document resolver middleware now adds a Last-Modified header to the response using the resolved document's last publication date.
- Renamed the PrismicTemplateHandler to PrismicTemplate. It is now middleware rather than a request handler and only returns a response when a document has been successfully resolved, otherwise, it delegates to the next handler. This is a better way of dealing with CMS 404's whilst preserving the default behaviour of the Mezzio NotFoundHandler. 
- Removed automatic registration of routes and renamed the `PipelineAndRoutesDelegator` to `RouteProvider`. Now, consumers will need to add `(new RouteProvider())($application, $container);` to their route configuration file.
- Improved handling of invalid preview tokens so that junk is ignored, passing through to the next middleware (Likely a 404)
- Improved handling of expired preview tokens so that these pass through with a modified request with an attribute containing the expiry exception from the api.
- Improved error message for a mapped type when the target class does not exist

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
