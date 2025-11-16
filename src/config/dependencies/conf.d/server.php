<?php

declare(strict_types=1);

namespace EricFortmeyer\ActivityLog\Http\RequestProcessors;

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
    ServerInterface::class => static fn(ContainerInterface $container) => new Server(
        interface: [
            new Target(
                location: "/",
                method: HttpMethod::Get,
                representations: new Representations([
                    MimeType::TextHtml,
                ]),
                requestProcessor: $container->get(GetTimeEntries::class),
            ),
            new Target(
                location: "/time-entry/{id}",
                method: HttpMethod::Get,
                representations: new Representations([
                    MimeType::TextHtml,
                ]),
                requestProcessor: $container->get(GetTimeEntry::class),
            ),
            new Target(
                location: "/time-entry/add",
                method: HttpMethod::Post,
                representations: new Representations([
                    MimeType::TextHtml,
                ]),
                requestProcessor: $container->get(SubmitTimeEntry::class),
            ),
            new Target(
                location: "/time-entry/delete/{id}",
                method: HttpMethod::Post,
                representations: new Representations([
                    MimeType::TextHtml,
                ]),
                requestProcessor: $container->get(DeleteTimeEntry::class),
            ),
            new Target(
                location: "/remarks-for-month",
                method: HttpMethod::Post,
                representations: new Representations([
                    MimeType::TextHtml,
                ]),
                requestProcessor: $container->get(SaveRemarksForMonth::class),
            ),
            new Target(
                location: "/credit-hours",
                method: HttpMethod::Post,
                representations: new Representations([
                    MimeType::TextHtml,
                ]),
                requestProcessor: $container->get(SaveCreditHours::class),
            ),
            new Target(
                location: "/export-data",
                method: HttpMethod::Get,
                representations: new Representations(
                    [MimeType::TextHtml],
                ),
                requestProcessor: $container->get(DownloadDataExport::class),
            ),
            new Target(
                location: "/report/send",
                method: HttpMethod::Post,
                representations: new Representations(
                    [MimeType::TextHtml],
                ),
                requestProcessor: $container->get(EmailReportForMonth::class),
            ),
        ]
    ),
];
