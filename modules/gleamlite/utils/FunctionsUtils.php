<?php

namespace gleamlite\utils;

// if (!function_exists('functionNotExists')) {
//   function functionNotExists(string $functionName): bool
//   {
//     return !function_exists($functionName);
//   }
// }

/**-----------------------------------------------------------------------------------*/



/**-----------------------------------------------------------------------------------*/

// if (functionNotExists('buildCustomUrl')) {
//   function buildCustomUrl(string $section): string
//   {
//     $baseUrl = '';
//     if (stringStartsWith(URL, HttpMode::HTTP)) {
//       $baseUrl = str_replace(HttpMode::HTTP, '', URL);
//     } else if (stringStartsWith(URL, HttpMode::HTTPS)) {
//       $baseUrl = str_replace(HttpMode::HTTPS, '', URL);
//     }

//     return "{$baseUrl}{$section}";
//   }
// }

// if (functionNotExists('buildUrl')) {
//   function buildUrl(string $baseUrl): string
//   {
//     if (stringStartsWith($baseUrl, HttpMode::HTTP) || stringStartsWith($baseUrl, HttpMode::HTTPS)) {
//       return $baseUrl;
//     }
//     $httpMode = (stringNotEquals(getServerVar('HTTPS', 'DEFAULT'), 'DEFAULT')) ? HttpMode::HTTPS : HttpMode::HTTP;
//     return "{$httpMode}{$baseUrl}";
//   }
// }

// if (functionNotExists('composeUrl')) {
//   function composeUrl(string $section): string
//   {
//     return buildUrl(buildCustomUrl($section));
//   }
// }
