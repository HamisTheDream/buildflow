<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap.';

    public function handle()
    {
        // In a real scenario with spatie/laravel-sitemap installed:
        // Sitemap::create()
        //    ->add(Url::create('/'))
        //    ->add(Url::create('/contact'))
        //    ->add(Url::create('/terms'))
        //    ->add(Url::create('/privacy'))
        //    ->add(Url::create('/blog'))
        //    ->writeToFile(public_path('sitemap.xml'));

        // For this demo without the package, we'll manually write a basic XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $routes = ['/', '/contact', '/terms', '/privacy', '/blog'];
        $baseUrl = config('app.url');

        foreach ($routes as $route) {
            $xml .= '<url>';
            $xml .= '<loc>' . $baseUrl . $route . '</loc>';
            $xml .= '<lastmod>' . now()->toAtomString() . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        file_put_contents(public_path('sitemap.xml'), $xml);

        $this->info('Sitemap generated successfully.');
    }
}
