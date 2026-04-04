# Email Finder - PHP Library

[![Packagist Version](https://img.shields.io/packagist/v/enrow/email-finder)](https://packagist.org/packages/enrow/email-finder)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)

Find verified professional email addresses from a name and company. Integrate email discovery into your sales pipeline, CRM sync, or lead generation workflow.

Powered by [Enrow](https://enrow.io) -- works on catch-all domains, only charged when an email is found.

## Installation

```bash
composer require enrow/email-finder
```

Requires PHP 8.1+ and Guzzle 7.

## Simple Usage

```php
use EmailFinder\EmailFinder;

$search = EmailFinder::find('your_api_key', [
    'fullName' => 'Tim Cook',
    'companyDomain' => 'apple.com',
]);

$result = EmailFinder::get('your_api_key', $search['id']);

echo $result['email'];         // tcook@apple.com
echo $result['qualification']; // valid
```

`find()` returns a search ID. The search runs asynchronously -- call `get()` to retrieve the result once it's ready. You can also pass a `webhook` URL to get notified automatically.

## Search by company name

If you don't have the domain, you can search by company name instead. The `countryCode` parameter helps narrow down results when company names are ambiguous.

```php
$search = EmailFinder::find('your_api_key', [
    'fullName' => 'Tim Cook',
    'companyName' => 'Apple Inc.',
    'countryCode' => 'US',
]);
```

## Bulk search

```php
use EmailFinder\EmailFinder;

$batch = EmailFinder::findBulk('your_api_key', [
    'searches' => [
        ['fullName' => 'Tim Cook', 'companyDomain' => 'apple.com'],
        ['fullName' => 'Satya Nadella', 'companyDomain' => 'microsoft.com'],
        ['fullName' => 'Jensen Huang', 'companyName' => 'NVIDIA'],
    ],
]);

// $batch['batchId'], $batch['total'], $batch['status']

$results = EmailFinder::getBulk('your_api_key', $batch['batchId']);
// $results['results'] -- array of email results
```

Up to 5,000 searches per batch. Pass a `webhook` URL to get notified when the batch completes.

## Error handling

```php
try {
    EmailFinder::find('bad_key', [
        'fullName' => 'Test',
        'companyDomain' => 'test.com',
    ]);
} catch (\RuntimeException $e) {
    // $e->getMessage() contains the API error description
    // Common errors:
    // - "Invalid or missing API key" (401)
    // - "Your credit balance is insufficient." (402)
    // - "Rate limit exceeded" (429)
}
```

## Getting an API key

Register at [app.enrow.io](https://app.enrow.io) to get your API key. You get **50 free credits** (= 50 emails) with no credit card required.

Paid plans start at **$17/mo** for 1,000 emails up to **$497/mo** for 100,000 emails. See [pricing](https://enrow.io/pricing).

## Documentation

- [Enrow API documentation](https://docs.enrow.io)
- [Full Enrow SDK](https://github.com/enrow/enrow-php) -- includes email verifier, phone finder, reverse email lookup, and more

## License

MIT -- see [LICENSE](LICENSE) for details.
