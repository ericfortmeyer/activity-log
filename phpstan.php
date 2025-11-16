<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Class EricFortmeyer\\\\ActivityLog\\\\Infrastructure\\\\Auth\\\\AbstractRedirectMiddleware has an uninitialized readonly property \\$responseFactory\\. Assign it in the constructor\\.$#',
	'identifier' => 'property.uninitializedReadonly',
	'count' => 1,
	'path' => __DIR__ . '/src/Infrastructure/Auth/AbstractRedirectMiddleware.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
