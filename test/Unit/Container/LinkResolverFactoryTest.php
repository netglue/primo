<?php
declare(strict_types=1);

namespace PrimoTest\Unit\Container;

use Mezzio\Helper\UrlHelper;
use Mezzio\Router\RouteCollector;
use Primo\Container\LinkResolverFactory;
use Primo\Router\RouteMatcher;
use Primo\Router\RouteParams;
use PrimoTest\Unit\TestCase;
use Prismic\ApiClient;
use Prismic\Json;
use Prismic\Value\ApiData;
use Psr\Container\ContainerInterface;

class LinkResolverFactoryTest extends TestCase
{
    private function routeMatcher() : RouteMatcher
    {
        return new RouteMatcher(
            RouteParams::fromArray([]),
            $this->createMock(RouteCollector::class)
        );
    }

    public function testFactory() : void
    {
        $data = ApiData::factory(Json::decodeObject('{
            "refs": [
                {
                    "id": "master",
                    "ref": "master-ref",
                    "label": "Master",
                    "isMasterRef": true
                }
            ],
            "bookmarks": {
                "some-bookmark": "bookmarked-document-id",
                "other-bookmark": "other-document-id"
            },
            "types": {
                "basic-document": "Basic Document Type"
            },
            "languages": [
                {
                    "id": "en-gb",
                    "name": "English - Great Britain"
                }
            ],
            "tags": [],
            "forms": {
                "everything": {
                    "method": "GET",
                    "enctype": "application/x-www-form-urlencoded",
                    "action": "https://repo.cdn.prismic.io/api/v2/documents/search",
                    "fields": {
                        "ref": {
                            "type": "String",
                            "multiple": false
                        },
                        "q": {
                            "type": "String",
                            "multiple": true
                        },
                        "lang": {
                            "type": "String",
                            "multiple": false
                        },
                        "page": {
                            "type": "Integer",
                            "multiple": false,
                            "default": "1"
                        },
                        "pageSize": {
                            "type": "Integer",
                            "multiple": false,
                            "default": "20"
                        },
                        "after": {
                            "type": "String",
                            "multiple": false
                        },
                        "fetch": {
                            "type": "String",
                            "multiple": false
                        },
                        "fetchLinks": {
                            "type": "String",
                            "multiple": false
                        },
                        "orderings": {
                            "type": "String",
                            "multiple": false
                        },
                        "referer": {
                            "type": "String",
                            "multiple": false
                        }
                    }
                }
            }
        }'));
        $api = $this->createMock(ApiClient::class);
        $api->expects(self::once())
            ->method('data')
            ->willReturn($data);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::exactly(4))
            ->method('get')
            ->willReturnMap([
                [RouteParams::class, RouteParams::fromArray([])],
                [RouteMatcher::class, $this->routeMatcher()],
                [UrlHelper::class, $this->createMock(UrlHelper::class)],
                [ApiClient::class, $api],
            ]);

        $factory = new LinkResolverFactory();
        $factory->__invoke($container);
    }
}
