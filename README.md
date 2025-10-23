# Printable Labels PDF

A PHP library to generate printable labels as PDF using mPDF.

## Installation

### 1. Add the package to your project

If you want to install directly from GitHub, run:

```
php composer.phar require miceno/printable-labels-pdf:main --repository='{"type":"vcs","url":"https://github.com/miceno/printable_labels_pdf"}'
```

Or, add the repository to your project's `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/your-vendor/printable_labels_pdf"
    }
  ],
  "require": {
    "your-vendor/printable-labels-pdf": "dev-main"
  }
}
```

Then run:

```
php composer.phar update
```

### 2. Autoloading

Make sure to include Composer's autoloader in your PHP scripts:

```php
require_once __DIR__ . '/vendor/autoload.php';
```

### 3. Usage Example

See `example.php` for a complete usage example:

```php
use PrintableLabelsPdf\PrintableLabelsPdf;

$labels_config = [
    // ...configuration options...
];

$labels = new PrintableLabelsPdf($labels_config);
$labels->draw_border(true);
$labels->write_label('<b>Label 1</b>');
$labels->get_labels_pdf('test.pdf', 'F');
```

### 4. Requirements

- PHP 7.4 or higher
- [mPDF](https://mpdf.github.io/) library (installed automatically via Composer)

### 5. License

This project is licensed under the GNU General Public License v3.0.
