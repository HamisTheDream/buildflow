<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SafeFile implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail('The :attribute must be a valid uploaded file.');
            return;
        }

        $filename = strtolower($value->getClientOriginalName());
        $extension = strtolower($value->getClientOriginalExtension());
        $mimeType = strtolower($value->getMimeType());

        // 1. Block dangerous extensions anywhere in the filename (e.g., shell.php.jpg)
        $dangerousExtensions = [
            'php',
            'php3',
            'php4',
            'php5',
            'php7',
            'php8',
            'phtml',
            'phar',
            'exe',
            'sh',
            'bat',
            'cmd',
            'com',
            'vbs',
            'ps1',
            'svg',
            'html',
            'htm',
            'js',
            'jsp',
            'cgi',
            'pl',
            'py'
        ];

        // Check for double extensions
        $parts = explode('.', $filename);
        if (count($parts) > 2) {
            foreach ($parts as $part) {
                if (in_array($part, $dangerousExtensions)) {
                    $fail('The :attribute has a blocked file extension within its name.');
                    return;
                }
            }
        }

        // 2. Block exact terminal extension
        if (in_array($extension, $dangerousExtensions)) {
            $fail('The :attribute contains a forbidden file type.');
            return;
        }

        // 3. Block dangerous MIME types
        $dangerousMimes = [
            'application/x-httpd-php',
            'text/html',
            'image/svg+xml',
            'application/javascript',
            'application/x-sh',
            'application/x-executable'
        ];

        if (in_array($mimeType, $dangerousMimes)) {
            $fail('The :attribute contains a forbidden encoded file type.');
            return;
        }
    }
}
