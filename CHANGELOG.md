# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 0.6.0 - 2021-07-15

### Added

- [#31](https://github.com/netglue/primo/pull/31) Adds a route name for both the preview and webhook routes. The routes are opt-in anyway, but you would typically execute `(new RouteProvider())($app, $container);` in your `routes.php` configuration file to benefit from these features. Adding the route name enables you to identify these routes and target them more easily. Both route names are identified with public constants that you can get from the RouteProvider with:
    - `\Primo\RouteProvider::PREVIEW_ROUTE_NAME`
    - `\Primo\RouteProvider::WEBHOOK_ROUTE_NAME`

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#32](https://github.com/netglue/primo/pull/32) adds a workflow using [Composer Require Checker](https://github.com/maglnet/ComposerRequireChecker) to make sure that all dependencies used in the code are declared in `composer.json`… and also declares the ones that were missing.
- [#33](https://github.com/netglue/primo/pull/33) adds vimeo/psalm static analysis tool to dev dependencies and CI

## 0.5.4 - 2021-07-05

### Added

- Nothing.

### Changed

- Update constraints for [netglue/prismic-client](https://github.com/netglue/prismic-client) to `>= 0.5 < 1.0` so that minor versions in the ^0 series will get updated.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.5.3 - 2021-03-29

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#15](https://github.com/netglue/primo/pull/15) fixes [#14](https://github.com/netglue/primo/issues/14).
  When a route provides a document type _and_ an identifier, use that identifier to narrow down the result set to
  a single document.

## 0.5.2 - 2021-03-25

### Added

- Nothing.

### Changed

- Update `netglue/prismic-client` to `0.6.0`

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.5.1 - 2021-03-03

### Added

- Nothing.

### Changed

- [#8](https://github.com/netglue/primo/pull/8) Improves CI by using [the matrix action from the Laminas project](https://github.com/laminas/laminas-ci-matrix-action).
- [#8](https://github.com/netglue/primo/pull/8) Also updates dependencies, fixing test failures on 8 _(Out of date PHPUnit)_ and fixing a test failure due to copy/paste of a duplicate route name.
- [#9](https://github.com/netglue/primo/pull/9) Cleans up CS so it's the same as the adopted coding standard.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.5.0 - 2020-09-08

### Added

- Nothing.

### Changed

- Changed the `DocumentResolver` middleware so that it does not set a `Last-Modified` header in the response after successfully resolving the requested document. It's not the responsibility of the resolver to do this, and, it's incorrect to assume that *nothing* has changed since the document was last published.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.4.0 - 2020-06-22

### Added

- `Primo\Cache\PrismicApiCache` marker interface for retrieving api client specific cache pool.

### Changed

- Minimum ApiClient lib version bumped to 0.4.0
- Hydrating result set updated to match changes in api client lib
- Api client factory now injects cache item pool if configured

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.3.0 - 2020-06-17

### Added

- Added middleware that sets a Cache-Control header in the response to 'no-cache' when preview mode is active.
- Added middleware that will expire the preview cookie when the request has an attribute for `\Prismic\Exception\PreviewTokenExpired`. This optional middleware should be placed after the preview handler to kill dead cookies and redirect when a preview token has expired.
- Added `\Primo\Exception\RoutingError` to describe errors specific to routing configuration
- Methods to the RouteMatcher to find routes that are specific to a particular tag. 

### Changed

- The document resolver middleware now adds a Last-Modified header to the response using the resolved document's last publication date.
- Renamed the PrismicTemplateHandler to PrismicTemplate. It is now middleware rather than a request handler and only returns a response when a document has been successfully resolved, otherwise, it delegates to the next handler. This is a better way of dealing with CMS 404's whilst preserving the default behaviour of the Mezzio NotFoundHandler. 
- Removed automatic registration of routes and renamed the `PipelineAndRoutesDelegator` to `RouteProvider`. Now, consumers will need to add `(new RouteProvider())($application, $container);` to their route configuration file.
- Improved handling of invalid preview tokens so that junk is ignored, passing through to the next middleware (Likely a 404)
- Improved handling of expired preview tokens so that these pass through with a modified request with an attribute containing the expiry exception from the api.
- Improved error message for a mapped type when the target class does not exist
- The document resolver helper will throw exceptions when route configuration breaks certain rules
- You can now route on type alone, i.e. Prismic singletons.
- You can now write more specific routes with the same document type in multiple routes by specifying a tag.
- Removed LIFO parameter to RouteMatcher constructor that was unused and poorly conceived.

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
