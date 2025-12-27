<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

use EricFortmeyer\ActivityLog\DI\ServiceProvider;
use PhpCommonEnums\HttpMethod\Enumeration\HttpMethodEnum as HttpMethod;
use PhpCommonEnums\MimeType\Enumeration\MimeTypeEnum as MimeType;
use Phpolar\Phpolar\Http\{
    Representations,
    Server,
    ServerInterface,
    Target
};
use Psr\Container\ContainerInterface;

return [
    ServerInterface::class => static fn(ContainerInterface $container)
    =>
    new Server(
        interface: [
            new Target(
                location: "/",
                method: HttpMethod::Get,
                representations: new Representations([
                    MimeType::TextHtml,
                ]),
                requestProcessor: new ServiceProvider($container)->getTimeEntries,
            ),
            new Target(
                location: "/time-entry/{id}",
                method: HttpMethod::Get,
                representations: new Representations([
                    MimeType::TextHtml,
                ]),
                requestProcessor: new ServiceProvider($container)->getTimeEntry,
            ),
            new Target(
                location: "/time-entry/add",
                method: HttpMethod::Post,
                representations: new Representations([
                    MimeType::TextHtml,
                ]),
                requestProcessor: new ServiceProvider($container)->submitTimeEntry,
            ),
            new Target(
                location: "/time-entry/delete/{id}",
                method: HttpMethod::Post,
                representations: new Representations([
                    MimeType::TextHtml,
                ]),
                requestProcessor: new ServiceProvider($container)->deleteTimeEntry,
            ),
            new Target(
                location: "/remarks-for-month",
                method: HttpMethod::Post,
                representations: new Representations([
                    MimeType::TextHtml,
                ]),
                requestProcessor: new ServiceProvider($container)->saveRemarksForMonth,
            ),
            new Target(
                location: "/credit-hours",
                method: HttpMethod::Post,
                representations: new Representations([
                    MimeType::TextHtml,
                ]),
                requestProcessor: new ServiceProvider($container)->saveCreditHours,
            ),
            new Target(
                location: "/export-data",
                method: HttpMethod::Get,
                representations: new Representations(
                    [MimeType::TextHtml],
                ),
                requestProcessor: new ServiceProvider($container)->downloadDataExport,
            ),
            new Target(
                location: "/delete-data",
                method: HttpMethod::Post,
                representations: new Representations(
                    [MimeType::TextHtml],
                ),
                requestProcessor: new ServiceProvider($container)->deleteAccountData,
            ),
            new Target(
                location: "/report/send",
                method: HttpMethod::Post,
                representations: new Representations(
                    [MimeType::TextHtml],
                ),
                requestProcessor: new ServiceProvider($container)->emailReportForMonth,
            ),
        ]
    ),
];
